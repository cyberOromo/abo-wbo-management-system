<?php

declare(strict_types=1);

use App\Core\Application;
use App\Models\User;
use App\Utils\Database;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/InternalEmailSchemaBootstrap.php';

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

Application::getInstance()->bootstrap();

final class InternalEmailMigrationVerifier
{
    private Database $db;
    private InternalEmailSchemaBootstrap $schema;
    private User $userModel;
    private ?int $userId;
    private ?int $limit;

    public function __construct(array $options)
    {
        $this->db = Database::getInstance();
        $this->schema = new InternalEmailSchemaBootstrap($this->db);
        $this->userModel = new User();
        $this->userId = isset($options['user-id']) ? (int) $options['user-id'] : null;
        $this->limit = isset($options['limit']) ? (int) $options['limit'] : 25;
    }

    public function run(): int
    {
        if (!$this->schema->hasColumn('users', 'internal_email') || !$this->schema->hasTable('internal_emails')) {
            fwrite(STDERR, "Required internal email schema is not available for verification." . PHP_EOL);
            return 1;
        }

        $users = $this->loadUsers();

        if (empty($users)) {
            echo "No users found for verification." . PHP_EOL;
            return 0;
        }

        foreach ($users as $user) {
            $records = $this->db->fetchAll(
                'SELECT internal_email, email_type, status, auto_forward_to FROM internal_emails WHERE user_id = ? ORDER BY FIELD(email_type, "primary", "alias", "forwarding"), internal_email ASC',
                [$user['id']]
            );

            $primaryRecord = null;
            $aliases = [];

            foreach ($records as $record) {
                if ($record['email_type'] === 'primary' && $primaryRecord === null) {
                    $primaryRecord = $record;
                    continue;
                }

                if ($record['email_type'] === 'alias') {
                    $aliases[] = $record;
                }
            }

            $primaryLogin = $user['internal_email']
                ? $this->userModel->findByLoginEmail($user['internal_email'])
                : null;

            $aliasChecks = [];
            foreach ($aliases as $alias) {
                $resolved = $this->userModel->findByLoginEmail($alias['internal_email']);
                $aliasChecks[] = [
                    'alias' => $alias['internal_email'],
                    'forward_to' => $alias['auto_forward_to'],
                    'status' => $alias['status'],
                    'resolves_to_user_id' => $resolved['id'] ?? null,
                    'authenticated_email_type' => $resolved['authenticated_email_type'] ?? null,
                    'login_ok' => (int) (($resolved['id'] ?? null) === (int) $user['id']),
                ];
            }

            echo json_encode([
                'user_id' => (int) $user['id'],
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'primary' => $user['internal_email'],
                'primary_record' => $primaryRecord['internal_email'] ?? null,
                'primary_record_status' => $primaryRecord['status'] ?? null,
                'primary_login_ok' => (int) (($primaryLogin['id'] ?? null) === (int) $user['id']),
                'preserved_aliases' => $aliasChecks,
            ], JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }

        return 0;
    }

    private function loadUsers(): array
    {
        $conditions = ["u.internal_email IS NOT NULL", "u.internal_email != ''"];
        $params = [];

        if ($this->userId) {
            $conditions[] = 'u.id = ?';
            $params[] = $this->userId;
        }

        $sql = 'SELECT u.id, u.first_name, u.last_name, u.internal_email FROM users u WHERE ' . implode(' AND ', $conditions) . ' ORDER BY u.id ASC';

        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        return $this->db->fetchAll($sql, $params);
    }
}

$options = getopt('', ['user-id::', 'limit::']);
$runner = new InternalEmailMigrationVerifier($options);
exit($runner->run());