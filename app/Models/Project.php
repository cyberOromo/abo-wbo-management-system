<?php

namespace App\Models;

use App\Core\Model;
use Exception;
use Throwable;
use App\Utils\Database;

class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'summary',
        'description',
        'project_code',
        'status',
        'priority',
        'project_type',
        'start_date',
        'target_date',
        'completion_percentage',
        'budget_amount',
        'owner_user_id',
        'created_by',
        'level_scope',
        'global_id',
        'godina_id',
        'gamta_id',
        'gurmu_id',
        'success_metrics',
        'delivery_notes',
        'metadata',
        'status_notes',
        'archived_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->ensureSchema();
    }

    public function ensureSchema(): void
    {
        $db = Database::getInstance();
        if (!$db->tableExists($this->table)) {
            $db->exec(
                "CREATE TABLE IF NOT EXISTS projects (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    summary VARCHAR(500) NULL,
                    description TEXT NULL,
                    project_code VARCHAR(60) NOT NULL,
                    status VARCHAR(50) NOT NULL DEFAULT 'proposed',
                    priority VARCHAR(50) NOT NULL DEFAULT 'medium',
                    project_type VARCHAR(50) NOT NULL DEFAULT 'initiative',
                    start_date DATE NULL,
                    target_date DATE NULL,
                    completion_percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    budget_amount DECIMAL(12,2) NULL,
                    owner_user_id INT UNSIGNED NULL,
                    created_by INT UNSIGNED NOT NULL,
                    level_scope VARCHAR(50) NOT NULL DEFAULT 'global',
                    global_id INT UNSIGNED NULL,
                    godina_id INT UNSIGNED NULL,
                    gamta_id INT UNSIGNED NULL,
                    gurmu_id INT UNSIGNED NULL,
                    success_metrics TEXT NULL,
                    delivery_notes TEXT NULL,
                    metadata JSON NULL,
                    status_notes TEXT NULL,
                    archived_at DATETIME NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    UNIQUE KEY unique_project_code (project_code),
                    KEY idx_projects_scope (level_scope, global_id, godina_id, gamta_id, gurmu_id),
                    KEY idx_projects_status (status),
                    KEY idx_projects_owner (owner_user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }

        if (!$db->columnExists($this->table, 'archived_at')) {
            $db->exec("ALTER TABLE projects ADD COLUMN archived_at DATETIME NULL AFTER status_notes");
        }

        $db->exec(
            "CREATE TABLE IF NOT EXISTS project_assignments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                project_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                assignment_role VARCHAR(50) NOT NULL DEFAULT 'contributor',
                assignment_scope VARCHAR(50) NULL,
                assigned_by INT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY unique_project_assignment (project_id, user_id),
                KEY idx_project_assignments_project (project_id),
                KEY idx_project_assignments_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS project_milestones (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                project_id INT UNSIGNED NOT NULL,
                title VARCHAR(255) NOT NULL,
                summary VARCHAR(500) NULL,
                due_date DATE NULL,
                status VARCHAR(50) NOT NULL DEFAULT 'planned',
                completion_percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
                sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_by INT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY idx_project_milestones_project (project_id),
                KEY idx_project_milestones_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS project_activity_log (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                project_id INT UNSIGNED NOT NULL,
                actor_user_id INT UNSIGNED NOT NULL,
                activity_type VARCHAR(80) NOT NULL,
                description VARCHAR(500) NOT NULL,
                metadata JSON NULL,
                created_at DATETIME NOT NULL,
                KEY idx_project_activity_project (project_id),
                KEY idx_project_activity_actor (actor_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        if ($db->tableExists('tasks') && !$db->columnExists('tasks', 'project_id')) {
            $db->exec("ALTER TABLE tasks ADD COLUMN project_id INT UNSIGNED NULL");
            $db->exec("ALTER TABLE tasks ADD INDEX idx_tasks_project (project_id)");
        }
    }

    public function getResolvedScope(int $userId): array
    {
        $scope = $this->db->fetch(
            "SELECT ua.*, go.name as global_name, gd.name as godina_name, ga.name as gamta_name, gu.name as gurmu_name
             FROM user_assignments ua
             LEFT JOIN globals go ON ua.global_id = go.id
             LEFT JOIN godinas gd ON ua.godina_id = gd.id
             LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
             LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
             WHERE ua.user_id = ? AND ua.status = 'active'
             ORDER BY FIELD(ua.level_scope, 'global', 'godina', 'gamta', 'gurmu')
             LIMIT 1",
            [$userId]
        ) ?: [];

        if (!empty($scope)) {
            $scope['scope_name'] = $scope['gurmu_name']
                ?? $scope['gamta_name']
                ?? $scope['godina_name']
                ?? $scope['global_name']
                ?? 'Current project scope';
        }

        return $scope;
    }

    public function getProjectsForScope(array $scope, int $limit = 100): array
    {
        $params = [];
        $sql = "SELECT p.*, u.first_name, u.last_name,
                       CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as owner_name,
                       COUNT(DISTINCT pa.user_id) as team_members,
                       COUNT(DISTINCT pm.id) as milestones_total,
                       COUNT(DISTINCT CASE WHEN pm.status = 'completed' THEN pm.id END) as milestones_completed,
                       COUNT(DISTINCT CASE WHEN t.parent_task_id IS NULL THEN t.id END) as total_tasks,
                       COUNT(DISTINCT CASE WHEN t.parent_task_id IS NULL AND COALESCE(t.status, '') IN ('pending', 'in_progress', 'under_review', 'on_hold') THEN t.id END) as open_tasks
                FROM {$this->table} p
                LEFT JOIN users u ON u.id = COALESCE(p.owner_user_id, p.created_by)
                LEFT JOIN project_assignments pa ON pa.project_id = p.id
                LEFT JOIN project_milestones pm ON pm.project_id = p.id
                LEFT JOIN tasks t ON t.project_id = p.id
                WHERE 1=1";

        $sql = $this->applyScopeVisibilityFilter($sql, $params, $scope);
        $sql .= " GROUP BY p.id ORDER BY FIELD(p.status, 'active', 'proposed', 'on_hold', 'completed', 'archived'), FIELD(p.priority, 'critical', 'high', 'medium', 'low'), p.updated_at DESC LIMIT {$limit}";

        return $this->db->fetchAll($sql, $params);
    }

    public function getProjectStats(array $scope): array
    {
        $params = [];
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN p.status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN p.status = 'proposed' THEN 1 ELSE 0 END) as proposed,
                    SUM(CASE WHEN p.status = 'on_hold' THEN 1 ELSE 0 END) as on_hold,
                    SUM(CASE WHEN p.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    ROUND(AVG(COALESCE(p.completion_percentage, 0)), 0) as avg_progress,
                    COALESCE(SUM(CASE WHEN p.status != 'archived' THEN (
                        SELECT COUNT(*) FROM project_milestones pm WHERE pm.project_id = p.id
                    ) ELSE 0 END), 0) as milestones_total,
                    COALESCE(SUM(CASE WHEN p.status != 'archived' THEN (
                        SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.parent_task_id IS NULL
                    ) ELSE 0 END), 0) as tasks_total
                FROM {$this->table} p
                WHERE 1=1";

        $sql = $this->applyScopeVisibilityFilter($sql, $params, $scope);

        return $this->db->fetch($sql, $params) ?: [];
    }

    public function getProject(int $id, array $scope): ?array
    {
        $params = [$id];
        $sql = "SELECT p.*, u.first_name, u.last_name,
                       CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as owner_name
                FROM {$this->table} p
                LEFT JOIN users u ON u.id = COALESCE(p.owner_user_id, p.created_by)
                WHERE p.id = ?";

        $sql = $this->applyScopeVisibilityFilter($sql, $params, $scope);
        return $this->db->fetch($sql, $params);
    }

    public function createProject(array $payload, array $teamUserIds = [], int $actorUserId = 0): int
    {
        return $this->db->transaction(function () use ($payload, $teamUserIds, $actorUserId): int {
            $payload['project_code'] = $payload['project_code'] ?: $this->generateProjectCode($payload);
            $payload['metadata'] = !empty($payload['metadata']) ? json_encode($payload['metadata']) : null;

            $projectId = $this->db->insert($this->table, $payload);
            $this->syncAssignments($projectId, (int) ($payload['owner_user_id'] ?? $payload['created_by'] ?? 0), $teamUserIds, $actorUserId ?: (int) ($payload['created_by'] ?? 0));
            $this->logActivity($projectId, $actorUserId ?: (int) ($payload['created_by'] ?? 0), 'project_created', 'Project created.');

            return $projectId;
        });
    }

    public function updateProject(int $projectId, array $payload, array $teamUserIds, int $actorUserId): bool
    {
        $existing = $this->db->fetch("SELECT * FROM {$this->table} WHERE id = ?", [$projectId]);
        if (!$existing) {
            throw new Exception('Project not found.');
        }

        return $this->db->transaction(function () use ($projectId, $payload, $teamUserIds, $actorUserId, $existing): bool {
            if (isset($payload['metadata']) && is_array($payload['metadata'])) {
                $payload['metadata'] = json_encode($payload['metadata']);
            }

            $updated = $this->db->update($this->table, $payload, ['id' => $projectId]) > 0;
            $this->syncAssignments($projectId, (int) ($payload['owner_user_id'] ?? $existing['owner_user_id'] ?? $existing['created_by']), $teamUserIds, $actorUserId);

            $activityType = ($existing['status'] ?? '') !== ($payload['status'] ?? $existing['status'])
                ? 'project_status_changed'
                : 'project_updated';

            $description = $activityType === 'project_status_changed'
                ? 'Project status updated to ' . str_replace('_', ' ', (string) ($payload['status'] ?? $existing['status'])) . '.'
                : 'Project details updated.';

            $this->logActivity($projectId, $actorUserId, $activityType, $description);

            return $updated;
        });
    }

    public function archiveProject(int $projectId, int $actorUserId): bool
    {
        $updated = $this->db->update($this->table, [
            'status' => 'archived',
            'archived_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $projectId]) > 0;

        if ($updated) {
            $this->logActivity($projectId, $actorUserId, 'project_archived', 'Project archived.');
        }

        return $updated;
    }

    public function getProjectAssignments(int $projectId): array
    {
        return $this->db->fetchAll(
            "SELECT pa.*, u.first_name, u.last_name, u.internal_email,
                    CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as full_name
             FROM project_assignments pa
             INNER JOIN users u ON u.id = pa.user_id
             WHERE pa.project_id = ?
             ORDER BY FIELD(pa.assignment_role, 'lead', 'sponsor', 'reviewer', 'contributor'), u.first_name ASC, u.last_name ASC",
            [$projectId]
        );
    }

    public function getProjectMilestones(int $projectId): array
    {
        return $this->db->fetchAll(
            "SELECT *
             FROM project_milestones
             WHERE project_id = ?
             ORDER BY FIELD(status, 'in_progress', 'planned', 'completed', 'blocked'), COALESCE(due_date, '9999-12-31') ASC, sort_order ASC, id ASC",
            [$projectId]
        );
    }

    public function getProjectActivities(int $projectId, int $limit = 12): array
    {
        return $this->db->fetchAll(
            "SELECT pal.*, u.first_name, u.last_name,
                    CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as actor_name
             FROM project_activity_log pal
             LEFT JOIN users u ON u.id = pal.actor_user_id
             WHERE pal.project_id = ?
             ORDER BY pal.created_at DESC
             LIMIT {$limit}",
            [$projectId]
        );
    }

    public function getProjectTaskOptions(int $projectId): array
    {
        return $this->db->fetchAll(
            "SELECT id, title, parent_task_id
             FROM tasks
             WHERE project_id = ?
             ORDER BY COALESCE(parent_task_id, 0) ASC, created_at ASC",
            [$projectId]
        );
    }

    public function getProjectTasksHierarchy(int $projectId): array
    {
        if (!$this->db->tableExists('tasks')) {
            return [];
        }

        $taskProgressSelect = $this->buildTaskProgressSelectExpression();

        $tasks = $this->db->fetchAll(
            "SELECT t.*, {$taskProgressSelect}, u.first_name, u.last_name,
                    CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as creator_name
             FROM tasks t
             LEFT JOIN users u ON u.id = t.created_by
             WHERE t.project_id = ?
             ORDER BY COALESCE(t.parent_task_id, 0) ASC, t.created_at ASC",
            [$projectId]
        );

        foreach ($tasks as &$task) {
            $task['assigned_to'] = json_decode((string) ($task['assigned_to'] ?? '[]'), true) ?: [];
            $task['children'] = [];
        }
        unset($task);

        $childrenByParent = [];
        foreach ($tasks as $task) {
            $parentId = $task['parent_task_id'] !== null ? (int) $task['parent_task_id'] : 0;
            $childrenByParent[$parentId][] = $task;
        }

        return $this->buildTaskTree($childrenByParent, 0);
    }

    public function getAssignableUsersForScope(array $scope): array
    {
        $params = [];
        $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.internal_email, ua.level_scope,
                       CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as full_name
                FROM user_assignments ua
                INNER JOIN users u ON u.id = ua.user_id
                WHERE ua.status = 'active' AND COALESCE(u.status, 'active') = 'active'";

        $sql .= $this->buildAssignableUsersClause('ua', $scope, $params);
        $sql .= ' ORDER BY u.first_name ASC, u.last_name ASC';

        return $this->db->fetchAll($sql, $params);
    }

    public function getAssignableUsersForProject(array $project): array
    {
        return $this->getAssignableUsersForScope([
            'level_scope' => $project['level_scope'] ?? null,
            'global_id' => $project['global_id'] ?? null,
            'godina_id' => $project['godina_id'] ?? null,
            'gamta_id' => $project['gamta_id'] ?? null,
            'gurmu_id' => $project['gurmu_id'] ?? null,
        ]);
    }

    public function getAvailableTaskScopes(array $project): array
    {
        $options = [];
        $levelScope = (string) ($project['level_scope'] ?? '');

        if ($levelScope === 'global' && !empty($project['global_id'])) {
            $globalId = (int) $project['global_id'];
            $global = $this->db->fetch('SELECT id, name FROM globals WHERE id = ?', [$globalId]);
            if ($global) {
                $options[] = ['value' => 'global:' . $global['id'], 'label' => 'Global - ' . $global['name']];
            }
            foreach ($this->db->fetchAll('SELECT id, name FROM godinas WHERE global_id = ? ORDER BY name ASC', [$globalId]) as $godina) {
                $options[] = ['value' => 'godina:' . $godina['id'], 'label' => 'Godina - ' . $godina['name']];
            }
            foreach ($this->db->fetchAll('SELECT ga.id, ga.name FROM gamtas ga INNER JOIN godinas gd ON ga.godina_id = gd.id WHERE gd.global_id = ? ORDER BY ga.name ASC', [$globalId]) as $gamta) {
                $options[] = ['value' => 'gamta:' . $gamta['id'], 'label' => 'Gamta - ' . $gamta['name']];
            }
            foreach ($this->db->fetchAll('SELECT gu.id, gu.name FROM gurmus gu INNER JOIN gamtas ga ON gu.gamta_id = ga.id INNER JOIN godinas gd ON ga.godina_id = gd.id WHERE gd.global_id = ? ORDER BY gu.name ASC', [$globalId]) as $gurmu) {
                $options[] = ['value' => 'gurmu:' . $gurmu['id'], 'label' => 'Gurmu - ' . $gurmu['name']];
            }
        } elseif ($levelScope === 'godina' && !empty($project['godina_id'])) {
            $godinaId = (int) $project['godina_id'];
            $godina = $this->db->fetch('SELECT id, name FROM godinas WHERE id = ?', [$godinaId]);
            if ($godina) {
                $options[] = ['value' => 'godina:' . $godina['id'], 'label' => 'Godina - ' . $godina['name']];
            }
            foreach ($this->db->fetchAll('SELECT id, name FROM gamtas WHERE godina_id = ? ORDER BY name ASC', [$godinaId]) as $gamta) {
                $options[] = ['value' => 'gamta:' . $gamta['id'], 'label' => 'Gamta - ' . $gamta['name']];
            }
            foreach ($this->db->fetchAll('SELECT gu.id, gu.name FROM gurmus gu INNER JOIN gamtas ga ON gu.gamta_id = ga.id WHERE ga.godina_id = ? ORDER BY gu.name ASC', [$godinaId]) as $gurmu) {
                $options[] = ['value' => 'gurmu:' . $gurmu['id'], 'label' => 'Gurmu - ' . $gurmu['name']];
            }
        } elseif ($levelScope === 'gamta' && !empty($project['gamta_id'])) {
            $gamtaId = (int) $project['gamta_id'];
            $gamta = $this->db->fetch('SELECT id, name FROM gamtas WHERE id = ?', [$gamtaId]);
            if ($gamta) {
                $options[] = ['value' => 'gamta:' . $gamta['id'], 'label' => 'Gamta - ' . $gamta['name']];
            }
            foreach ($this->db->fetchAll('SELECT id, name FROM gurmus WHERE gamta_id = ? ORDER BY name ASC', [$gamtaId]) as $gurmu) {
                $options[] = ['value' => 'gurmu:' . $gurmu['id'], 'label' => 'Gurmu - ' . $gurmu['name']];
            }
        } elseif ($levelScope === 'gurmu' && !empty($project['gurmu_id'])) {
            $gurmu = $this->db->fetch('SELECT id, name FROM gurmus WHERE id = ?', [(int) $project['gurmu_id']]);
            if ($gurmu) {
                $options[] = ['value' => 'gurmu:' . $gurmu['id'], 'label' => 'Gurmu - ' . $gurmu['name']];
            }
        }

        return $options;
    }

    public function createMilestone(int $projectId, array $payload, int $actorUserId): int
    {
        $payload['project_id'] = $projectId;
        $payload['created_by'] = $actorUserId;
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['updated_at'] = date('Y-m-d H:i:s');

        $milestoneId = $this->db->insert('project_milestones', $payload);
        $this->logActivity($projectId, $actorUserId, 'project_milestone_created', 'Milestone created: ' . $payload['title']);
        $this->recalculateProjectProgress($projectId);

        return $milestoneId;
    }

    public function createProjectTask(int $projectId, array $payload, int $actorUserId): array
    {
        $project = $this->db->fetch("SELECT * FROM {$this->table} WHERE id = ?", [$projectId]);
        if (!$project) {
            throw new Exception('Project not found.');
        }

        [$taskScope, $scopeId] = $this->resolveTaskScopeSelection($project, (string) ($payload['scope_selection'] ?? ''));

        $taskPayload = [
            'title' => trim((string) ($payload['title'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'level_scope' => $taskScope,
            'scope_id' => $scopeId,
            'parent_task_id' => !empty($payload['parent_task_id']) ? (int) $payload['parent_task_id'] : null,
            'project_id' => $projectId,
            'category' => trim((string) ($payload['category'] ?? Task::CATEGORY_ADMINISTRATIVE)),
            'priority' => trim((string) ($payload['priority'] ?? Task::PRIORITY_MEDIUM)),
            'status' => Task::STATUS_PENDING,
            'start_date' => ($payload['start_date'] ?? '') !== '' ? (string) $payload['start_date'] : null,
            'due_date' => ($payload['due_date'] ?? '') !== '' ? (string) $payload['due_date'] : null,
            'completion_percentage' => 0,
            'assigned_to' => $payload['assigned_to'] ?? [],
            'created_by' => $actorUserId,
        ];

        if ($taskPayload['title'] === '') {
            throw new Exception('Task title is required.');
        }

        $taskModel = new Task();
        $result = $taskModel->createTask($taskPayload);
        if (!($result['success'] ?? false)) {
            throw new Exception((string) ($result['message'] ?? 'Failed to create project task.'));
        }

        $description = $taskPayload['parent_task_id']
            ? 'Project subtask created: ' . $taskPayload['title']
            : 'Project task created: ' . $taskPayload['title'];

        $this->logActivity($projectId, $actorUserId, 'project_task_created', $description);
        $this->recalculateProjectProgress($projectId);

        return $result;
    }

    public function recalculateProjectProgress(int $projectId): void
    {
        $taskAverage = 0.0;
        $milestoneAverage = 0.0;

        if ($this->db->tableExists('tasks')) {
            $taskProgressColumn = $this->resolveTaskProgressColumn();

            if ($taskProgressColumn !== null) {
            $taskAverage = (float) ($this->db->fetchColumn(
                sprintf('SELECT ROUND(AVG(COALESCE(%s, 0)), 0) FROM tasks WHERE project_id = ?', $taskProgressColumn),
                [$projectId]
            ) ?? 0);
            }
        }

        $milestoneAverage = (float) ($this->db->fetchColumn(
            'SELECT ROUND(AVG(COALESCE(completion_percentage, 0)), 0) FROM project_milestones WHERE project_id = ?',
            [$projectId]
        ) ?? 0);

        $signals = 0;
        $total = 0.0;
        if ($taskAverage > 0 || $this->db->fetchColumn('SELECT COUNT(*) FROM tasks WHERE project_id = ?', [$projectId])) {
            $total += $taskAverage;
            $signals++;
        }
        if ($milestoneAverage > 0 || $this->db->fetchColumn('SELECT COUNT(*) FROM project_milestones WHERE project_id = ?', [$projectId])) {
            $total += $milestoneAverage;
            $signals++;
        }

        $completion = $signals > 0 ? (int) round($total / $signals) : 0;
        $status = $completion >= 100 ? 'completed' : null;

        $update = [
            'completion_percentage' => max(0, min(100, $completion)),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($status !== null) {
            $update['status'] = $status;
        }

        $this->db->update($this->table, $update, ['id' => $projectId]);
    }

    private function resolveTaskProgressColumn(): ?string
    {
        if (!$this->db->tableExists('tasks')) {
            return null;
        }

        if ($this->db->columnExists('tasks', 'completion_percentage')) {
            return 'completion_percentage';
        }

        if ($this->db->columnExists('tasks', 'progress_percentage')) {
            return 'progress_percentage';
        }

        return null;
    }

    private function buildTaskProgressSelectExpression(): string
    {
        $taskProgressColumn = $this->resolveTaskProgressColumn();

        if ($taskProgressColumn === null) {
            return '0 as completion_percentage';
        }

        if ($taskProgressColumn === 'completion_percentage') {
            return 'COALESCE(t.completion_percentage, 0) as completion_percentage';
        }

        return 'COALESCE(t.progress_percentage, 0) as completion_percentage';
    }

    private function generateProjectCode(array $payload): string
    {
        $prefix = strtoupper(substr((string) ($payload['level_scope'] ?? 'prj'), 0, 3));
        return sprintf('%s-%s', $prefix, date('YmdHis'));
    }

    private function applyScopeVisibilityFilter(string $sql, array &$params, array $scope): string
    {
        $levelScope = (string) ($scope['level_scope'] ?? '');
        if ($levelScope === '') {
            return $sql;
        }

        if ($levelScope === 'global' && !empty($scope['global_id'])) {
            $sql .= ' AND p.global_id = ?';
            $params[] = (int) $scope['global_id'];
        } elseif ($levelScope === 'godina' && !empty($scope['godina_id']) && !empty($scope['global_id'])) {
            $sql .= " AND (p.godina_id = ? OR (p.level_scope = 'global' AND p.global_id = ?))";
            $params[] = (int) $scope['godina_id'];
            $params[] = (int) $scope['global_id'];
        } elseif ($levelScope === 'gamta' && !empty($scope['gamta_id']) && !empty($scope['godina_id']) && !empty($scope['global_id'])) {
            $sql .= " AND (p.gamta_id = ? OR (p.level_scope = 'godina' AND p.godina_id = ?) OR (p.level_scope = 'global' AND p.global_id = ?))";
            $params[] = (int) $scope['gamta_id'];
            $params[] = (int) $scope['godina_id'];
            $params[] = (int) $scope['global_id'];
        } elseif ($levelScope === 'gurmu' && !empty($scope['gurmu_id']) && !empty($scope['gamta_id']) && !empty($scope['godina_id']) && !empty($scope['global_id'])) {
            $sql .= " AND (p.gurmu_id = ? OR (p.level_scope = 'gamta' AND p.gamta_id = ?) OR (p.level_scope = 'godina' AND p.godina_id = ?) OR (p.level_scope = 'global' AND p.global_id = ?))";
            $params[] = (int) $scope['gurmu_id'];
            $params[] = (int) $scope['gamta_id'];
            $params[] = (int) $scope['godina_id'];
            $params[] = (int) $scope['global_id'];
        }

        return $sql;
    }

    private function buildAssignableUsersClause(string $alias, array $scope, array &$params): string
    {
        $levelScope = (string) ($scope['level_scope'] ?? '');
        return match ($levelScope) {
            'global' => $this->appendScopedClause(" AND {$alias}.global_id = ?", [(int) ($scope['global_id'] ?? 0)], $params),
            'godina' => $this->appendScopedClause(" AND {$alias}.godina_id = ?", [(int) ($scope['godina_id'] ?? 0)], $params),
            'gamta' => $this->appendScopedClause(" AND {$alias}.gamta_id = ?", [(int) ($scope['gamta_id'] ?? 0)], $params),
            'gurmu' => $this->appendScopedClause(" AND {$alias}.gurmu_id = ?", [(int) ($scope['gurmu_id'] ?? 0)], $params),
            default => '',
        };
    }

    private function appendScopedClause(string $clause, array $values, array &$params): string
    {
        foreach ($values as $value) {
            $params[] = $value;
        }

        return $clause;
    }

    private function syncAssignments(int $projectId, int $ownerUserId, array $teamUserIds, int $actorUserId): void
    {
        $this->db->query('DELETE FROM project_assignments WHERE project_id = ?', [$projectId]);

        $now = date('Y-m-d H:i:s');
        $allUserIds = array_values(array_unique(array_filter(array_map('intval', array_merge([$ownerUserId], $teamUserIds)))));

        foreach ($allUserIds as $userId) {
            $this->db->insert('project_assignments', [
                'project_id' => $projectId,
                'user_id' => $userId,
                'assignment_role' => $userId === $ownerUserId ? 'lead' : 'contributor',
                'assignment_scope' => null,
                'assigned_by' => $actorUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function logActivity(int $projectId, int $actorUserId, string $activityType, string $description, array $metadata = []): void
    {
        try {
            $this->db->insert('project_activity_log', [
                'project_id' => $projectId,
                'actor_user_id' => $actorUserId,
                'activity_type' => $activityType,
                'description' => $description,
                'metadata' => !empty($metadata) ? json_encode($metadata) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable) {
        }
    }

    private function resolveTaskScopeSelection(array $project, string $selection): array
    {
        $selection = trim($selection);
        if ($selection === '') {
            return [(string) ($project['level_scope'] ?? 'global'), (int) ($project[$this->scopeColumnForLevel((string) ($project['level_scope'] ?? 'global'))] ?? 0)];
        }

        [$levelScope, $scopeId] = array_pad(explode(':', $selection, 2), 2, null);
        $levelScope = (string) $levelScope;
        $scopeId = (int) $scopeId;

        foreach ($this->getAvailableTaskScopes($project) as $option) {
            if (($option['value'] ?? '') === $levelScope . ':' . $scopeId) {
                return [$levelScope, $scopeId];
            }
        }

        throw new Exception('Selected project task scope is outside the current project chain.');
    }

    private function scopeColumnForLevel(string $levelScope): string
    {
        return match ($levelScope) {
            'global' => 'global_id',
            'godina' => 'godina_id',
            'gamta' => 'gamta_id',
            'gurmu' => 'gurmu_id',
            default => 'global_id',
        };
    }

    private function buildTaskTree(array $childrenByParent, int $parentId): array
    {
        $branch = [];
        foreach ($childrenByParent[$parentId] ?? [] as $task) {
            $task['children'] = $this->buildTaskTree($childrenByParent, (int) $task['id']);
            $branch[] = $task;
        }

        return $branch;
    }
}