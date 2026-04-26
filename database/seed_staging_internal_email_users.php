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

final class StagingInternalEmailSeeder
{
    private const SEED_TAG = 'staging_internal_email_validation';
    private const DEFAULT_PASSWORD = 'Stage123!';

    private Database $db;
    private InternalEmailGenerator $generator;
    private bool $apply;

    public function __construct(array $options)
    {
        $this->db = Database::getInstance();
        $this->generator = new InternalEmailGenerator();
        $this->apply = isset($options['apply']);
    }

    public function run(): int
    {
        $fixtures = $this->ensureHierarchyFixtures();
        $positions = $this->ensurePositions($fixtures);
        $plans = $this->buildUserPlans($fixtures, $positions);

        foreach ($plans as $plan) {
            echo json_encode([
                'email' => $plan['email'],
                'role' => $plan['role'],
                'scope' => $plan['scope']['level'],
                'position_code' => $plan['position']['code'],
            ], JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }

        if (!$this->apply) {
            echo "Dry run only. Re-run with --apply to create staging fixtures." . PHP_EOL;
            return 0;
        }

        $this->db->beginTransaction();

        try {
            foreach ($plans as $plan) {
                $this->createOrUpdateSeedUser($plan);
            }

            $this->db->commit();
            echo 'Seed data created successfully.' . PHP_EOL;
            echo 'Default password: ' . self::DEFAULT_PASSWORD . PHP_EOL;
            return 0;
        } catch (Throwable $throwable) {
            if ($this->db->getPdo()->inTransaction()) {
                $this->db->rollback();
            }

            fwrite(STDERR, "Seeder failed: {$throwable->getMessage()}" . PHP_EOL);
            return 1;
        }
    }

    private function ensureHierarchyFixtures(): array
    {
        $global = $this->findOrCreate('globals', ['code' => 'STGIMM-GLB'], [
            'name' => 'Staging Global Organization',
            'code' => 'STGIMM-GLB',
            'description' => 'Staging-only hierarchy fixture',
            'contact_email' => 'staging-global@example.test',
            'status' => 'active',
            'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
        ]);

        $godina = $this->findOrCreate('godinas', ['code' => 'STGIMM-GOD'], [
            'global_id' => $global['id'],
            'name' => 'Staging Godina',
            'code' => 'STGIMM-GOD',
            'description' => 'Staging-only Godina fixture',
            'contact_email' => 'staging-godina@example.test',
            'status' => 'active',
            'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
        ]);

        $gamta = $this->findOrCreate('gamtas', ['code' => 'STGIMM-GAM'], [
            'godina_id' => $godina['id'],
            'name' => 'Staging Gamta',
            'code' => 'STGIMM-GAM',
            'description' => 'Staging-only Gamta fixture',
            'contact_email' => 'staging-gamta@example.test',
            'status' => 'active',
            'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
        ]);

        $gurmu = $this->findOrCreate('gurmus', ['code' => 'STGIMM-GUR'], [
            'gamta_id' => $gamta['id'],
            'name' => 'Staging Gurmu',
            'code' => 'STGIMM-GUR',
            'description' => 'Staging-only Gurmu fixture',
            'contact_email' => 'staging-gurmu@example.test',
            'status' => 'active',
            'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
        ]);

        return compact('global', 'godina', 'gamta', 'gurmu');
    }

    private function ensurePositions(array $fixtures): array
    {
        return [
            'global_admin' => $this->findOrCreate('positions', ['code' => 'STGIMM-GADM'], [
                'key_name' => 'staging_global_admin',
                'name' => 'Staging Global Admin',
                'code' => 'STGIMM-GADM',
                'description' => 'Staging admin validation position',
                'hierarchy_type' => 'global',
                'hierarchy_id' => $fixtures['global']['id'],
                'level' => 1,
                'is_executive' => 1,
                'is_elected' => 0,
                'max_holders' => 2,
                'status' => 'active',
                'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
            ]),
            'godina_exec' => $this->findOrCreate('positions', ['code' => 'STGIMM-GODX'], [
                'key_name' => 'staging_godina_leader',
                'name' => 'Staging Godina Leader',
                'code' => 'STGIMM-GODX',
                'description' => 'Staging Godina validation position',
                'hierarchy_type' => 'godina',
                'hierarchy_id' => $fixtures['godina']['id'],
                'level' => 2,
                'is_executive' => 1,
                'is_elected' => 1,
                'max_holders' => 2,
                'status' => 'active',
                'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
            ]),
            'gamta_exec' => $this->findOrCreate('positions', ['code' => 'STGIMM-GAMX'], [
                'key_name' => 'staging_gamta_leader',
                'name' => 'Staging Gamta Leader',
                'code' => 'STGIMM-GAMX',
                'description' => 'Staging Gamta validation position',
                'hierarchy_type' => 'gamta',
                'hierarchy_id' => $fixtures['gamta']['id'],
                'level' => 3,
                'is_executive' => 1,
                'is_elected' => 1,
                'max_holders' => 2,
                'status' => 'active',
                'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
            ]),
            'gurmu_exec' => $this->findOrCreate('positions', ['code' => 'STGIMM-GURX'], [
                'key_name' => 'staging_gurmu_leader',
                'name' => 'Staging Gurmu Leader',
                'code' => 'STGIMM-GURX',
                'description' => 'Staging Gurmu leader validation position',
                'hierarchy_type' => 'gurmu',
                'hierarchy_id' => $fixtures['gurmu']['id'],
                'level' => 4,
                'is_executive' => 1,
                'is_elected' => 1,
                'max_holders' => 2,
                'status' => 'active',
                'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
            ]),
            'gurmu_member' => $this->findOrCreate('positions', ['code' => 'STGIMM-GURM'], [
                'key_name' => 'staging_gurmu_member',
                'name' => 'Staging Gurmu Member',
                'code' => 'STGIMM-GURM',
                'description' => 'Staging Gurmu member validation position',
                'hierarchy_type' => 'gurmu',
                'hierarchy_id' => $fixtures['gurmu']['id'],
                'level' => 5,
                'is_executive' => 0,
                'is_elected' => 0,
                'max_holders' => 10,
                'status' => 'active',
                'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
            ]),
        ];
    }

    private function buildUserPlans(array $fixtures, array $positions): array
    {
        return [
            [
                'first_name' => 'Bontu',
                'last_name' => 'Regassa',
                'email' => 'staging.bontu.regassa@example.test',
                'role' => 'admin',
                'position' => $positions['global_admin'],
                'scope' => ['level' => 'global', 'global_id' => $fixtures['global']['id']],
            ],
            [
                'first_name' => 'Bontu',
                'last_name' => 'Roba',
                'email' => 'staging.bontu.roba@example.test',
                'role' => 'executive',
                'position' => $positions['godina_exec'],
                'scope' => ['level' => 'godina', 'global_id' => $fixtures['global']['id'], 'godina_id' => $fixtures['godina']['id']],
            ],
            [
                'first_name' => 'Gamachu',
                'last_name' => 'Tola',
                'email' => 'staging.gamachu.tola@example.test',
                'role' => 'executive',
                'position' => $positions['gamta_exec'],
                'scope' => ['level' => 'gamta', 'global_id' => $fixtures['global']['id'], 'godina_id' => $fixtures['godina']['id'], 'gamta_id' => $fixtures['gamta']['id']],
            ],
            [
                'first_name' => 'Mulu',
                'last_name' => 'Bekele',
                'email' => 'staging.mulu.bekele@example.test',
                'role' => 'executive',
                'position' => $positions['gurmu_exec'],
                'scope' => ['level' => 'gurmu', 'global_id' => $fixtures['global']['id'], 'godina_id' => $fixtures['godina']['id'], 'gamta_id' => $fixtures['gamta']['id'], 'gurmu_id' => $fixtures['gurmu']['id']],
            ],
            [
                'first_name' => 'Saba',
                'last_name' => 'Lelisa',
                'email' => 'staging.saba.lelisa@example.test',
                'role' => 'member',
                'position' => $positions['gurmu_member'],
                'scope' => ['level' => 'gurmu', 'global_id' => $fixtures['global']['id'], 'godina_id' => $fixtures['godina']['id'], 'gamta_id' => $fixtures['gamta']['id'], 'gurmu_id' => $fixtures['gurmu']['id']],
            ],
        ];
    }

    private function createOrUpdateSeedUser(array $plan): void
    {
        $user = $this->db->fetch('SELECT * FROM users WHERE email = ?', [$plan['email']]);

        if (!$user) {
            $userId = $this->db->insert('users', [
                'first_name' => $plan['first_name'],
                'last_name' => $plan['last_name'],
                'email' => $plan['email'],
                'personal_email' => $plan['email'],
                'phone' => '555-0199',
                'personal_phone' => '555-0199',
                'password_hash' => password_hash(self::DEFAULT_PASSWORD, PASSWORD_DEFAULT),
                'role' => $plan['role'],
                'status' => 'active',
                'language' => 'en',
                'registration_source' => 'admin_created',
                'account_type' => 'internal_only',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'personal_email_verified' => 1,
                'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
            ]);

            $user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [$userId]);
        }

        $assignment = $this->db->fetch(
            'SELECT id FROM user_assignments WHERE user_id = ? AND position_id = ? AND level_scope = ? AND status = ? LIMIT 1',
            [$user['id'], $plan['position']['id'], $plan['scope']['level'], 'active']
        );

        if (!$assignment) {
            $this->db->insert('user_assignments', [
                'user_id' => $user['id'],
                'position_id' => $plan['position']['id'],
                'level_scope' => $plan['scope']['level'],
                'global_id' => $plan['scope']['global_id'] ?? null,
                'godina_id' => $plan['scope']['godina_id'] ?? null,
                'gamta_id' => $plan['scope']['gamta_id'] ?? null,
                'gurmu_id' => $plan['scope']['gurmu_id'] ?? null,
                'status' => 'active',
                'start_date' => date('Y-m-d'),
                'appointment_type' => 'appointed',
                'assignment_reason' => 'Staging internal email validation fixture',
                'metadata' => json_encode(['seed_tag' => self::SEED_TAG]),
            ]);
        }

        $userData = [
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
        ];

        $hierarchyData = [
            'level' => $plan['scope']['level'],
            'code' => $this->resolveScopeCode($plan['scope']['level']),
        ];

        $primaryEmail = $user['internal_email'] ?: $this->generator->generateInternalEmail($userData, null, null);

        if (empty($user['internal_email'])) {
            $this->generator->createInternalEmailRecord((int) $user['id'], $primaryEmail, [
                'email_type' => 'primary',
                'created_by' => 1,
                'creation_method' => 'staging_seed_primary',
            ]);

            $this->generator->createCPanelEmailAccount($primaryEmail, self::DEFAULT_PASSWORD, 1024);

            $this->db->update('users', [
                'internal_email' => $primaryEmail,
                'internal_account_created_at' => date('Y-m-d H:i:s'),
                'internal_credentials_sent_at' => date('Y-m-d H:i:s'),
            ], ['id' => $user['id']]);
        }

        if (in_array($plan['role'], ['admin', 'executive'], true)) {
            $preview = $this->generator->previewEmailGeneration($userData, $plan['position'], $hierarchyData);
            $expectedAlias = $preview['role_alias'] ?? null;

            if (!$expectedAlias) {
                return;
            }

            $existingAlias = $this->db->fetch(
                'SELECT id FROM internal_emails WHERE user_id = ? AND internal_email = ? LIMIT 1',
                [$user['id'], $expectedAlias]
            );

            if (!$existingAlias) {
                $this->generator->provisionRoleAlias((int) $user['id'], $userData, $plan['position'], $hierarchyData, [
                    'forward_to' => $primaryEmail,
                    'created_by' => 1,
                    'creation_method' => 'staging_seed_alias',
                ]);
            }
        }
    }

    private function resolveScopeCode(string $level): string
    {
        if ($level === 'global') {
            return 'global';
        }

        if ($level === 'godina') {
            return 'STGIMM-GOD';
        }

        if ($level === 'gamta') {
            return 'STGIMM-GAM';
        }

        return 'STGIMM-GUR';
    }

    private function findOrCreate(string $table, array $identity, array $payload): array
    {
        $where = [];
        $params = [];

        foreach ($identity as $column => $value) {
            $where[] = "{$column} = ?";
            $params[] = $value;
        }

        $existing = $this->db->fetch(
            'SELECT * FROM ' . $table . ' WHERE ' . implode(' AND ', $where) . ' LIMIT 1',
            $params
        );

        if ($existing) {
            return $existing;
        }

        $id = $this->db->insert($table, $payload);
        return $this->db->fetch('SELECT * FROM ' . $table . ' WHERE id = ?', [$id]);
    }
}

$options = getopt('', ['apply']);
$runner = new StagingInternalEmailSeeder($options);
exit($runner->run());