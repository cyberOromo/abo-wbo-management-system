<?php

declare(strict_types=1);

const DEFAULT_BASE_URL = 'https://staging.j-abo-wbo.org';
const DEFAULT_PASSWORD = 'Stage123!';

if (!function_exists('curl_init')) {
    fwrite(STDERR, "cURL is required to run this validator.\n");
    exit(2);
}

$baseUrl = rtrim(getenv('ABO_STAGE_BASE_URL') ?: DEFAULT_BASE_URL, '/');
$password = getenv('ABO_STAGE_PASSWORD') ?: DEFAULT_PASSWORD;

$accounts = [
    'global' => [
        'email' => getenv('ABO_STAGE_GLOBAL_EMAIL') ?: 'galana.b@j-abo-wbo.org',
        'expected_scopes' => ['global', 'godina', 'gamta', 'gurmu'],
    ],
    'gurmu' => [
        'email' => getenv('ABO_STAGE_GURMU_EMAIL') ?: 'tigist.d@j-abo-wbo.org',
        'expected_scopes' => ['gurmu'],
    ],
];

$failures = [];

foreach ($accounts as $label => $account) {
    $cookieFile = tempnam(sys_get_temp_dir(), 'abo-task-proof-');
    if ($cookieFile === false) {
        fwrite(STDERR, "Failed to create temporary cookie file.\n");
        exit(2);
    }

    try {
        login($baseUrl, $cookieFile, $account['email'], $password);
        $createPage = httpRequest('GET', $baseUrl . '/tasks/create', $cookieFile);
        $scopeOptions = extractSelectOptions($createPage['body'], 'level_scope');

        if ($scopeOptions !== $account['expected_scopes']) {
            $failures[] = sprintf(
                '%s scope options mismatch. expected [%s], got [%s]',
                $label,
                implode(', ', $account['expected_scopes']),
                implode(', ', $scopeOptions)
            );
        }

        echo sprintf(
            "%s scope options: [%s]%s",
            strtoupper($label),
            implode(', ', $scopeOptions),
            $scopeOptions === $account['expected_scopes'] ? ' PASS' : ' FAIL'
        ) . PHP_EOL;
    } catch (Throwable $throwable) {
        $failures[] = sprintf('%s create-form validation failed: %s', $label, $throwable->getMessage());
        echo strtoupper($label) . ' create-form FAIL: ' . $throwable->getMessage() . PHP_EOL;
    } finally {
        if (is_file($cookieFile)) {
            @unlink($cookieFile);
        }
    }
}

$creatorCookie = tempnam(sys_get_temp_dir(), 'abo-task-creator-');
$assigneeCookie = tempnam(sys_get_temp_dir(), 'abo-task-assignee-');

if ($creatorCookie === false || $assigneeCookie === false) {
    fwrite(STDERR, "Failed to create temporary cookie files.\n");
    exit(2);
}

try {
    $creatorEmail = $accounts['gurmu']['email'];
    login($baseUrl, $creatorCookie, $creatorEmail, $password);

    $createPage = httpRequest('GET', $baseUrl . '/tasks/create', $creatorCookie);
    $csrfToken = extractCsrfToken($createPage['body']);
    if ($csrfToken === null) {
        throw new RuntimeException('Could not extract CSRF token from task create page.');
    }

    $assigneeOptions = extractSelectOptions($createPage['body'], 'assigned_to[]', true);
    if (count($assigneeOptions) === 0) {
        throw new RuntimeException('No assignee options were available on the task create page.');
    }

    $assigneeOption = $assigneeOptions[0];
    $taskTitle = 'STG Task Proof ' . gmdate('YmdHis');
    $createResponse = httpRequest('POST', $baseUrl . '/tasks', $creatorCookie, [
        '_token' => $csrfToken,
        'title' => $taskTitle,
        'description' => 'Automated staging proof-chain validation for standalone task visibility.',
        'level_scope' => 'gurmu',
        'status' => 'pending',
        'category' => 'administrative',
        'priority' => 'medium',
        'completion_percentage' => '0',
        'assigned_to' => [$assigneeOption['value']],
    ]);

    if (!preg_match('#/tasks/(\d+)#', $createResponse['effective_url'], $matches)) {
        throw new RuntimeException('Could not resolve created task id from redirect URL: ' . $createResponse['effective_url']);
    }

    $taskId = (int) $matches[1];
    $detailPage = httpRequest('GET', $baseUrl . '/tasks/' . $taskId, $creatorCookie);
    $assigneeEmail = extractFirstEmail($detailPage['body']);
    if ($assigneeEmail === null) {
        throw new RuntimeException('Could not extract assigned user email from task detail page.');
    }

    login($baseUrl, $assigneeCookie, $assigneeEmail, $password);

    $assigneeTasks = httpRequest('GET', $baseUrl . '/tasks', $assigneeCookie);
    $listVisible = str_contains($assigneeTasks['body'], $taskTitle);
    echo 'ASSIGNEE list visibility: ' . ($listVisible ? 'PASS' : 'FAIL') . PHP_EOL;
    if (!$listVisible) {
        $failures[] = 'Assigned task was not visible on the assignee task list.';
    }

    $assigneeDetail = httpRequest('GET', $baseUrl . '/tasks/' . $taskId, $assigneeCookie);
    $detailVisible = str_contains($assigneeDetail['body'], $taskTitle);
    echo 'ASSIGNEE detail visibility: ' . ($detailVisible ? 'PASS' : 'FAIL') . PHP_EOL;
    if (!$detailVisible) {
        $failures[] = 'Assigned task detail route was not visible to the assignee.';
    }

    echo 'CREATED TASK: ' . $taskTitle . ' (#' . $taskId . ')' . PHP_EOL;
    echo 'ASSIGNEE EMAIL: ' . $assigneeEmail . PHP_EOL;
} catch (Throwable $throwable) {
    $failures[] = 'Assignment proof-chain failed: ' . $throwable->getMessage();
    echo 'ASSIGNMENT PROOF FAIL: ' . $throwable->getMessage() . PHP_EOL;
} finally {
    if (is_file($creatorCookie)) {
        @unlink($creatorCookie);
    }

    if (is_file($assigneeCookie)) {
        @unlink($assigneeCookie);
    }
}

if (!empty($failures)) {
    echo PHP_EOL . 'Task proof-chain validation failed.' . PHP_EOL;
    foreach ($failures as $failure) {
        echo ' - ' . $failure . PHP_EOL;
    }
    exit(1);
}

echo PHP_EOL . 'Task proof-chain validation passed.' . PHP_EOL;
exit(0);

function login(string $baseUrl, string $cookieFile, string $email, string $password): void
{
    $loginPage = httpRequest('GET', $baseUrl . '/auth/login', $cookieFile);
    $csrfToken = extractCsrfToken($loginPage['body']);
    if ($csrfToken === null) {
        throw new RuntimeException('Could not extract CSRF token from login page.');
    }

    $loginResponse = httpRequest('POST', $baseUrl . '/auth/login', $cookieFile, [
        '_token' => $csrfToken,
        'internal_email' => $email,
        'password' => $password,
    ]);

    if (str_contains($loginResponse['effective_url'], '/auth/login')) {
        throw new RuntimeException('Login did not complete successfully for ' . $email . '.');
    }
}

function httpRequest(string $method, string $url, string $cookieFile, array $postFields = []): array
{
    $handle = curl_init($url);
    if ($handle === false) {
        throw new RuntimeException('Failed to initialize cURL.');
    }

    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'ABO-WBO Task Proof Validator/1.0',
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($method === 'POST') {
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($postFields));
    }

    $body = curl_exec($handle);
    if ($body === false) {
        $error = curl_error($handle);
        curl_close($handle);
        throw new RuntimeException('HTTP request failed: ' . $error);
    }

    $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $effectiveUrl = (string) curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
    curl_close($handle);

    if ($statusCode >= 400) {
        throw new RuntimeException('HTTP ' . $statusCode . ' for ' . $url);
    }

    return [
        'status' => $statusCode,
        'effective_url' => $effectiveUrl,
        'body' => $body,
    ];
}

function extractCsrfToken(string $html): ?string
{
    if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $matches) === 1) {
        return html_entity_decode($matches[1], ENT_QUOTES);
    }

    return null;
}

function extractSelectOptions(string $html, string $name, bool $withValues = false): array
{
    $document = new DOMDocument();
    @$document->loadHTML($html);

    $selects = $document->getElementsByTagName('select');
    foreach ($selects as $select) {
        if ($select->getAttribute('name') !== $name) {
            continue;
        }

        $options = [];
        foreach ($select->getElementsByTagName('option') as $option) {
            $label = trim(html_entity_decode($option->textContent, ENT_QUOTES));
            if ($label === '') {
                continue;
            }

            if ($withValues) {
                $options[] = [
                    'value' => $option->getAttribute('value'),
                    'label' => $label,
                ];
                continue;
            }

            $options[] = strtolower(trim((string) $option->getAttribute('value')));
        }

        return $options;
    }

    throw new RuntimeException('Select field not found: ' . $name);
}

function extractFirstEmail(string $html): ?string
{
    if (preg_match('/[A-Z0-9._%+-]+@j-abo-wbo\.org/i', $html, $matches) === 1) {
        return strtolower($matches[0]);
    }

    return null;
}