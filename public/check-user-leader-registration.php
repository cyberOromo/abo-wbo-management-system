<?php
/**
 * Temporary debug script for the system-admin registration module.
 * Remove after diagnosing the staging failure.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);
define('STORAGE_PATH', APP_ROOT . '/storage');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

loadEnv(APP_ROOT . '/.env');

echo '<h2>User Leader Registration Debug</h2>';

$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'email' => 'admin@abo-wbo.org',
    'first_name' => 'System',
    'last_name' => 'Admin',
    'user_type' => 'system_admin',
    'role' => 'system_admin',
    'status' => 'active',
];
$_SESSION['_token'] = $_SESSION['_token'] ?? bin2hex(random_bytes(16));

try {
    $db = \App\Utils\Database::getInstance();
    $currentUser = $db->fetch('SELECT id, first_name, last_name, email, user_type, status FROM users WHERE id = ?', [1]);

    if ($currentUser) {
        $_SESSION['user_id'] = $currentUser['id'];
        $_SESSION['user'] = array_merge($_SESSION['user'], $currentUser);
    }

    echo '<p>Session user: <pre>' . htmlspecialchars(print_r($_SESSION['user'], true)) . '</pre></p>';

    $controller = new \App\Controllers\UserLeaderRegistrationController();
    echo '<p>Controller instantiated successfully.</p>';

    $reflection = new \ReflectionClass($controller);
    $checks = [
        'getGodinas',
        'getGamtas',
        'getGurmus',
        'getPositions',
        'getRecentRegistrations',
        'getRegistrationStatistics',
    ];

    foreach ($checks as $methodName) {
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        try {
            $result = $method->invoke($controller);
            $count = is_array($result) ? count($result) : 1;
            echo '<p><strong>' . htmlspecialchars($methodName) . '</strong>: success (' . $count . ')</p>';
        } catch (\Throwable $methodError) {
            echo '<p style="color:red;"><strong>' . htmlspecialchars($methodName) . '</strong>: ' . htmlspecialchars($methodError->getMessage()) . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($methodError->getFile()) . ' <strong>Line:</strong> ' . (int) $methodError->getLine() . '</p>';
        }
    }

    $render = $reflection->getMethod('render');
    $render->setAccessible(true);

    try {
        ob_start();
        $output = $render->invoke($controller, 'admin/user_leader_registration', [
            'title' => 'User & Leader Registration',
            'godinas' => [],
            'gamtas' => [],
            'gurmus' => [],
            'positions' => [],
            'recent_registrations' => [],
            'statistics' => [
                'total_users' => 0,
                'this_month' => 0,
                'this_week' => 0,
                'today' => 0,
                'active_assignments' => 0,
            ],
            'current_user' => $_SESSION['user'],
        ]);
        ob_end_clean();

        echo '<p><strong>render(admin/user_leader_registration)</strong>: success, output length ' . strlen((string) $output) . '</p>';
    } catch (\Throwable $renderError) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        echo '<p style="color:red;"><strong>render(admin/user_leader_registration)</strong>: ' . htmlspecialchars($renderError->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($renderError->getFile()) . ' <strong>Line:</strong> ' . (int) $renderError->getLine() . '</p>';
    }
} catch (\Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo '<p style="color:red;"><strong>Failure:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . (int) $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}