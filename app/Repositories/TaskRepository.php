<?php

namespace App\Repositories;

use PDO;

/**
 * TaskRepository - Task data access layer
 * 
 * Handles all database operations related to tasks including
 * assignments, status updates, and cross-level task management.
 * 
 * @package App\Repositories
 * @version 1.0.0
 */
class TaskRepository extends BaseRepository
{
    protected string $table = 'tasks';
    protected array $fillable = [
        'uuid', 'title', 'description', 'level_scope', 'scope_id', 'parent_task_id',
        'event_id', 'project_id', 'meeting_id', 'category', 'priority', 'status',
        'start_date', 'due_date', 'completed_date', 'estimated_hours', 'actual_hours',
        'completion_percentage', 'tags', 'attachments', 'created_by', 'assigned_to'
    ];
    protected array $casts = [
        'tags' => 'json',
        'attachments' => 'json',
        'assigned_to' => 'json',
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_date' => 'date',
        'estimated_hours' => 'int',
        'actual_hours' => 'int',
        'completion_percentage' => 'int'
    ];
    protected bool $cacheEnabled = true;

    /**
     * Get tasks by hierarchy level and scope
     */
    public function getByHierarchyLevel(string $level, int $scopeId, array $filters = []): array
    {
        $this->resetQuery();
        
        // Apply hierarchy filtering
        if ($level !== 'global') {
            $this->where('level_scope', $level)
                 ->where('scope_id', $scopeId);
        }
        
        return $this->applyFilters($filters)->get();
    }

    /**
     * Get tasks assigned to user
     */
    public function getAssignedToUser(int $userId, array $filters = []): array
    {
        $this->resetQuery();
        
        // Use JSON_CONTAINS for MySQL 5.7+
        $this->whereRaw("JSON_CONTAINS(assigned_to, ?)", [json_encode(['id' => $userId])]);
        
        return $this->applyFilters($filters)->get();
    }

    /**
     * Get tasks created by user
     */
    public function getCreatedByUser(int $userId, array $filters = []): array
    {
        $this->resetQuery();
        $this->where('created_by', $userId);
        
        return $this->applyFilters($filters)->get();
    }

    /**
     * Get overdue tasks
     */
    public function getOverdueTasks(string $level = 'global', ?int $scopeId = null): array
    {
        $this->resetQuery();
        
        $this->where('due_date', '<', date('Y-m-d'))
             ->whereNotIn('status', ['completed', 'cancelled']);
             
        if ($level !== 'global' && $scopeId) {
            $this->where('level_scope', $level)
                 ->where('scope_id', $scopeId);
        }
        
        return $this->orderBy('due_date')->get();
    }

    /**
     * Get task statistics
     */
    public function getStatistics(string $level = 'global', ?int $scopeId = null): array
    {
        $baseQuery = "SELECT status, COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        
        if ($level !== 'global' && $scopeId) {
            $baseQuery .= " AND level_scope = '{$level}' AND scope_id = {$scopeId}";
        }
        
        $baseQuery .= " GROUP BY status";
        
        $stmt = $this->db->prepare($baseQuery);
        $stmt->execute();
        $statusStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Priority statistics
        $priorityQuery = str_replace('status', 'priority', $baseQuery);
        $stmt = $this->db->prepare($priorityQuery);
        $stmt->execute();
        $priorityStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return [
            'by_status' => $statusStats,
            'by_priority' => $priorityStats,
            'total' => array_sum($statusStats),
            'overdue' => $this->getOverdueTasks($level, $scopeId)
        ];
    }

    /**
     * Apply common filters
     */
    private function applyFilters(array $filters): self
    {
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $this->whereIn('status', $filters['status']);
            } else {
                $this->where('status', $filters['status']);
            }
        }
        
        if (!empty($filters['priority'])) {
            $this->where('priority', $filters['priority']);
        }
        
        if (!empty($filters['category'])) {
            $this->where('category', $filters['category']);
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $this->whereLike('title', $search)
                 ->orWhereLike('description', $search);
        }
        
        return $this->orderBy('created_at', 'desc');
    }

    /**
     * Update task assignment
     */
    public function updateAssignment(int $taskId, array $assignees): bool
    {
        return $this->update($taskId, ['assigned_to' => json_encode($assignees)]);
    }

    /**
     * Update task status and completion
     */
    public function updateStatus(int $taskId, string $status, int $completionPercentage = null, int $actualHours = null): bool
    {
        $data = ['status' => $status];
        
        if ($completionPercentage !== null) {
            $data['completion_percentage'] = $completionPercentage;
        }
        
        if ($actualHours !== null) {
            $data['actual_hours'] = $actualHours;
        }
        
        if ($status === 'completed') {
            $data['completed_date'] = date('Y-m-d H:i:s');
            $data['completion_percentage'] = 100;
        }
        
        return $this->update($taskId, $data);
    }

    /**
     * Get task with full details including assignees and creator
     */
    public function getTaskWithDetails(int $taskId): ?array
    {
        $task = $this->find($taskId);
        if (!$task) {
            return null;
        }
        
        // Get creator details
        $creatorSql = "SELECT id, first_name, last_name, email FROM users WHERE id = ?";
        $stmt = $this->db->prepare($creatorSql);
        $stmt->execute([$task['created_by']]);
        $task['creator'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get assignee details
        if (!empty($task['assigned_to'])) {
            $assigneeIds = array_column($task['assigned_to'], 'id');
            if (!empty($assigneeIds)) {
                $placeholders = str_repeat('?,', count($assigneeIds) - 1) . '?';
                $assigneeSql = "SELECT id, first_name, last_name, email FROM users WHERE id IN ({$placeholders})";
                $stmt = $this->db->prepare($assigneeSql);
                $stmt->execute($assigneeIds);
                $task['assignees'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
        return $task;
    }
}