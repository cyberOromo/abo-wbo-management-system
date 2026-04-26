<?php

declare(strict_types=1);

use App\Core\Application;
use App\Services\InternalEmailGenerator;
use App\Utils\Database;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

Application::getInstance()->bootstrap();

final class ImmutableInternalEmailMigration
{
    private Database $db;
    private InternalEmailGenerator $generator;
    private bool $apply;
    private ?int $userId;
    private ?int $limit;

    public function __construct(array $options)
    {
        $this->db = Database::getInstance();
        $this->generator = new InternalEmailGenerator();
        $this->apply = isset($options['apply']);
        $this->userId = isset($options['user-id']) ? (int) $options['user-id'] : null;
        $this->limit = isset($options['limit']) ? (int) $options['limit'] : null;
    }

    public function run(): int
    {
        $users = $this->loadUsers();

        if (empty($users)) {
            echo "No users with internal email addresses were found." . PHP_EOL;
            return 0;
        }

        $plans = $this->buildPlans($users);
        $this->printPlanSummary($plans);

        if (!$this->apply) {
            echo "Dry run only. Re-run with --apply to persist changes." . PHP_EOL;
            return 0;
        }

        $this->db->beginTransaction();

        try {
            foreach ($plans as $plan) {
                $this->applyPlan($plan);
            }

            $this->db->commit();
            echo "Migration applied successfully." . PHP_EOL;
            return 0;
        } catch (Throwable $throwable) {
            if ($this->db->getPdo()->inTransaction()) {
                $this->db->rollback();
            }

            fwrite(STDERR, "Migration failed: {$throwable->getMessage()}" . PHP_EOL);
            return 1;
        }
    }

    private function loadUsers(): array
    {
        $conditions = ["u.internal_email IS NOT NULL", "u.internal_email != ''"];
        $params = [];

        if ($this->userId) {
            $conditions[] = 'u.id = ?';
            $params[] = $this->userId;
        }

        $sql = "
            SELECT
                u.id,
                u.first_name,
                u.last_name,
                u.internal_email,
                ie.id AS primary_record_id,
                ie.email_quota_mb AS primary_record_quota
            FROM users u
            LEFT JOIN internal_emails ie
                ON ie.user_id = u.id
               AND ie.internal_email = u.internal_email
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY u.id ASC
        ";

        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        return $this->db->fetchAll($sql, $params);
    }

    private function buildPlans(array $users): array
    {
        $occupiedEmails = $this->loadOccupiedEmails();
        $plans = [];

        foreach ($users as $user) {
            $currentEmail = strtolower((string) $user['internal_email']);
            $targetEmail = strtolower($this->generator->generateMigrationPrimaryEmail((int) $user['id'], $user, array_keys($occupiedEmails)));

            $plans[] = [
                'user_id' => (int) $user['id'],
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'current_email' => $currentEmail,
                'target_email' => $targetEmail,
                'action' => $currentEmail === $targetEmail ? 'ensure_primary' : 'migrate',
                'primary_quota_mb' => $user['primary_record_quota'] ?? 1024,
            ];

            $occupiedEmails[$targetEmail] = true;
        }

        return $plans;
    }

    private function loadOccupiedEmails(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT internal_email FROM users WHERE internal_email IS NOT NULL AND internal_email != ''
             UNION
             SELECT internal_email FROM internal_emails"
        );

        $occupied = [];

        foreach ($rows as $row) {
            $occupied[strtolower((string) $row['internal_email'])] = true;
        }

        return $occupied;
    }

    private function printPlanSummary(array $plans): void
    {
        $migrateCount = 0;
        $unchangedCount = 0;

        foreach ($plans as $plan) {
            if ($plan['action'] === 'migrate') {
                $migrateCount++;
            } else {
                $unchangedCount++;
            }

            echo json_encode($plan, JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }

        echo "Summary: {$migrateCount} migrate, {$unchangedCount} unchanged/ensure-primary." . PHP_EOL;
    }

    private function applyPlan(array $plan): void
    {
        $userId = $plan['user_id'];
        $targetEmail = $plan['target_email'];
        $currentEmail = $plan['current_email'];
        $quota = (int) ($plan['primary_quota_mb'] ?? 1024);

        $this->db->query(
            "UPDATE internal_emails
             SET email_type = 'alias',
                 email_quota_mb = 0,
                 auto_forward_to = ?,
                 status = 'active',
                 activated_at = COALESCE(activated_at, NOW())
             WHERE user_id = ?
               AND email_type = 'primary'
               AND internal_email != ?",
            [$targetEmail, $userId, $targetEmail]
        );

        $targetRecord = $this->db->fetch(
            "SELECT id FROM internal_emails WHERE user_id = ? AND internal_email = ?",
            [$userId, $targetEmail]
        );

        if (!$targetRecord) {
            $this->generator->createInternalEmailRecord($userId, $targetEmail, [
                'email_type' => 'primary',
                'quota_mb' => $quota,
                'created_by' => 1,
                'creation_method' => 'immutable_primary_migration',
            ]);
        }

        $this->db->query(
            "UPDATE internal_emails
             SET email_type = 'primary',
                 email_quota_mb = ?,
                 auto_forward_to = NULL,
                 status = 'active',
                 activated_at = COALESCE(activated_at, NOW())
             WHERE user_id = ?
               AND internal_email = ?",
            [$quota, $userId, $targetEmail]
        );

        if ($currentEmail !== $targetEmail) {
            $currentRecord = $this->db->fetch(
                "SELECT id FROM internal_emails WHERE user_id = ? AND internal_email = ?",
                [$userId, $currentEmail]
            );

            if (!$currentRecord) {
                $this->generator->createInternalEmailRecord($userId, $currentEmail, [
                    'email_type' => 'alias',
                    'quota_mb' => 0,
                    'forward_to' => $targetEmail,
                    'created_by' => 1,
                    'creation_method' => 'legacy_primary_alias_migration',
                ]);
            }

            $this->db->query(
                "UPDATE internal_emails
                 SET email_type = 'alias',
                     email_quota_mb = 0,
                     auto_forward_to = ?,
                     status = 'active',
                     activated_at = COALESCE(activated_at, NOW())
                 WHERE user_id = ?
                   AND internal_email = ?",
                [$targetEmail, $userId, $currentEmail]
            );
        }

        $this->db->query(
            "UPDATE users
             SET internal_email = ?,
                 internal_account_created_at = COALESCE(internal_account_created_at, NOW()),
                 internal_credentials_sent_at = COALESCE(internal_credentials_sent_at, NOW())
             WHERE id = ?",
            [$targetEmail, $userId]
        );
    }
}

$options = getopt('', ['apply', 'user-id::', 'limit::']);
$runner = new ImmutableInternalEmailMigration($options);
exit($runner->run());