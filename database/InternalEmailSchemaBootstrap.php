<?php

declare(strict_types=1);

use App\Utils\Database;

final class InternalEmailSchemaBootstrap
{
    private Database $db;

    /**
     * @var array<string, array<string, array<string, mixed>>>
     */
    private array $columnCache = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function ensureInternalEmailSchema(): void
    {
        $this->ensureUserColumns();
        $this->ensureHybridSystemConfigTable();
        $this->ensureHybridSystemConfigDefaults();
        $this->backfillUserInternalEmailsFromPrimaryRecords();
    }

    public function hasTable(string $table): bool
    {
        $result = $this->db->fetch(
            'SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        );

        return (int) ($result['count'] ?? 0) > 0;
    }

    public function hasColumn(string $table, string $column): bool
    {
        return isset($this->getColumnMap($table)[$column]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getColumnMap(string $table): array
    {
        if (!isset($this->columnCache[$table])) {
            $rows = $this->db->fetchAll('SHOW COLUMNS FROM ' . $table);
            $map = [];

            foreach ($rows as $row) {
                $map[$row['Field']] = $row;
            }

            $this->columnCache[$table] = $map;
        }

        return $this->columnCache[$table];
    }

    public function resetColumnCache(string $table): void
    {
        unset($this->columnCache[$table]);
    }

    public function filterPayload(string $table, array $payload): array
    {
        $columns = $this->getColumnMap($table);

        return array_filter(
            $payload,
            static fn ($value, string $column): bool => array_key_exists($column, $columns),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function buildAssignmentUnitId(array $scope): int
    {
        return (int) match ($scope['level']) {
            'global' => $scope['global_id'],
            'godina' => $scope['godina_id'],
            'gamta' => $scope['gamta_id'],
            default => $scope['gurmu_id'],
        };
    }

    public function normalizePositionRecord(array $position, array $fallback): array
    {
        if (empty($position['code'])) {
            $position['code'] = $position['key_name'] ?? $fallback['code'] ?? $fallback['key_name'] ?? null;
        }

        if (empty($position['name'])) {
            $position['name'] = $position['name_en'] ?? $fallback['name'] ?? $fallback['key_name'] ?? 'Position';
        }

        if (empty($position['description'])) {
            $position['description'] = $position['description_en'] ?? $fallback['description'] ?? null;
        }

        return $position;
    }

    private function ensureUserColumns(): void
    {
        $definitions = [
            'internal_email' => 'ALTER TABLE users ADD COLUMN internal_email VARCHAR(255) NULL UNIQUE AFTER email',
            'internal_account_created_at' => 'ALTER TABLE users ADD COLUMN internal_account_created_at TIMESTAMP NULL DEFAULT NULL AFTER internal_email',
            'internal_credentials_sent_at' => 'ALTER TABLE users ADD COLUMN internal_credentials_sent_at TIMESTAMP NULL DEFAULT NULL AFTER internal_account_created_at',
        ];

        foreach ($definitions as $column => $sql) {
            if ($this->hasColumn('users', $column)) {
                continue;
            }

            $this->db->query($sql);
            $this->resetColumnCache('users');
        }
    }

    private function ensureHybridSystemConfigTable(): void
    {
        if ($this->hasTable('hybrid_system_config')) {
            return;
        }

        $this->db->query(
            "CREATE TABLE hybrid_system_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(100) NOT NULL UNIQUE,
                config_value TEXT NOT NULL,
                config_type ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
                category ENUM('email', 'approval', 'notification', 'registration', 'security') NOT NULL DEFAULT 'email',
                updated_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category (category),
                INDEX idx_updated_by (updated_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function ensureHybridSystemConfigDefaults(): void
    {
        if (!$this->hasTable('hybrid_system_config')) {
            return;
        }

        $defaults = [
            [
                'config_key' => 'internal_email_domain',
                'config_value' => 'j-abo-wbo.org',
                'config_type' => 'string',
                'category' => 'email',
            ],
            [
                'config_key' => 'default_email_quota_mb',
                'config_value' => '1024',
                'config_type' => 'integer',
                'category' => 'email',
            ],
        ];

        foreach ($defaults as $default) {
            $existing = $this->db->fetch(
                'SELECT id FROM hybrid_system_config WHERE config_key = ? LIMIT 1',
                [$default['config_key']]
            );

            if ($existing) {
                $this->db->update('hybrid_system_config', [
                    'config_value' => $default['config_value'],
                    'config_type' => $default['config_type'],
                    'category' => $default['category'],
                ], ['id' => $existing['id']]);
                continue;
            }

            $this->db->insert('hybrid_system_config', $default);
        }
    }

    private function backfillUserInternalEmailsFromPrimaryRecords(): void
    {
        if (!$this->hasTable('internal_emails') || !$this->hasColumn('users', 'internal_email')) {
            return;
        }

        $this->db->query(
            "UPDATE users u
             INNER JOIN internal_emails ie
                ON ie.user_id = u.id
               AND ie.email_type = 'primary'
             SET u.internal_email = ie.internal_email
             WHERE (u.internal_email IS NULL OR u.internal_email = '')"
        );
    }
}