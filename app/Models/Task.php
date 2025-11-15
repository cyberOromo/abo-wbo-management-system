<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Task Model
 * Handles task management across organizational hierarchy
 */
class Task extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    
    // Task categories
    const CATEGORY_ADMINISTRATIVE = 'administrative';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_EDUCATIONAL = 'educational';
    const CATEGORY_SOCIAL = 'social';
    const CATEGORY_TECHNICAL = 'technical';
    
    // Task priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    // Task statuses
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ON_HOLD = 'on_hold';
    
    // Level scopes
    const SCOPE_GLOBAL = 'global';
    const SCOPE_GODINA = 'godina';
    const SCOPE_GAMTA = 'gamta';
    const SCOPE_GURMU = 'gurmu';
    const SCOPE_CROSS_LEVEL = 'cross_level';

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'level_scope',
        'scope_id',
        'parent_task_id',
        'event_id',
        'project_id',
        'meeting_id',
        'category',
        'priority',
        'status',
        'start_date',
        'due_date',
        'completed_date',
        'estimated_hours',
        'actual_hours',
        'completion_percentage',
        'tags',
        'attachments',
        'created_by',
        'assigned_to'
    ];

    protected $casts = [
        'tags' => 'json',
        'attachments' => 'json',
        'assigned_to' => 'json'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new task
     */
    public function createTask(array $data): array
    {
        try {
            // Generate UUID
            $data['uuid'] = $this->generateUUID();
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Ensure JSON fields are properly encoded
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = json_encode($data['tags']);
            }
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                $data['attachments'] = json_encode($data['attachments']);
            }
            if (isset($data['assigned_to']) && is_array($data['assigned_to'])) {
                $data['assigned_to'] = json_encode($data['assigned_to']);
            }

            $taskId = $this->create($data);
            
            if ($taskId) {
                // Log task creation
                $this->logTaskActivity($taskId, 'created', 'Task created', $data['created_by']);
                
                return [
                    'success' => true,
                    'task_id' => $taskId,
                    'message' => 'Task created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create task'];
            
        } catch (\Exception $e) {
            error_log("Task creation error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Task creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get tasks by hierarchical scope
     */
    public function getTasksByScope(string $scope, int $scopeId = null, array $filters = []): array
    {
        try {
            $query = "SELECT t.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name,
                             pt.title as parent_task_title
                      FROM {$this->table} t
                      LEFT JOIN users u ON t.created_by = u.id
                      LEFT JOIN tasks pt ON t.parent_task_id = pt.id
                      WHERE t.level_scope = :scope";
            
            $params = ['scope' => $scope];
            
            if ($scopeId) {
                $query .= " AND t.scope_id = :scope_id";
                $params['scope_id'] = $scopeId;
            }
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND t.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['priority'])) {
                $query .= " AND t.priority = :priority";
                $params['priority'] = $filters['priority'];
            }
            
            if (!empty($filters['category'])) {
                $query .= " AND t.category = :category";
                $params['category'] = $filters['category'];
            }
            
            if (!empty($filters['assigned_to'])) {
                $query .= " AND JSON_CONTAINS(t.assigned_to, :assigned_to)";
                $params['assigned_to'] = json_encode([(int)$filters['assigned_to']]);
            }
            
            if (!empty($filters['due_date_from'])) {
                $query .= " AND t.due_date >= :due_date_from";
                $params['due_date_from'] = $filters['due_date_from'];
            }
            
            if (!empty($filters['due_date_to'])) {
                $query .= " AND t.due_date <= :due_date_to";
                $params['due_date_to'] = $filters['due_date_to'];
            }

            $query .= " ORDER BY 
                       CASE t.priority 
                           WHEN 'urgent' THEN 1 
                           WHEN 'high' THEN 2 
                           WHEN 'medium' THEN 3 
                           WHEN 'low' THEN 4 
                       END,
                       t.due_date ASC,
                       t.created_at DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($tasks as &$task) {
                $task['tags'] = json_decode($task['tags'] ?? '[]', true);
                $task['attachments'] = json_decode($task['attachments'] ?? '[]', true);
                $task['assigned_to'] = json_decode($task['assigned_to'] ?? '[]', true);
            }
            
            return $tasks;
            
        } catch (\Exception $e) {
            error_log("Get tasks by scope error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get tasks assigned to a specific user
     */
    public function getTasksAssignedToUser(int $userId, array $filters = []): array
    {
        try {
            $query = "SELECT t.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name,
                             pt.title as parent_task_title
                      FROM {$this->table} t
                      LEFT JOIN users u ON t.created_by = u.id
                      LEFT JOIN tasks pt ON t.parent_task_id = pt.id
                      WHERE JSON_CONTAINS(t.assigned_to, :user_id)";
            
            $params = ['user_id' => json_encode([$userId])];
            
            // Apply status filter if provided
            if (!empty($filters['status'])) {
                $query .= " AND t.status = :status";
                $params['status'] = $filters['status'];
            }
            
            $query .= " ORDER BY 
                       CASE t.priority 
                           WHEN 'urgent' THEN 1 
                           WHEN 'high' THEN 2 
                           WHEN 'medium' THEN 3 
                           WHEN 'low' THEN 4 
                       END,
                       t.due_date ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($tasks as &$task) {
                $task['tags'] = json_decode($task['tags'] ?? '[]', true);
                $task['attachments'] = json_decode($task['attachments'] ?? '[]', true);
                $task['assigned_to'] = json_decode($task['assigned_to'] ?? '[]', true);
            }
            
            return $tasks;
            
        } catch (\Exception $e) {
            error_log("Get tasks assigned to user error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update task status
     */
    public function updateTaskStatus(int $taskId, string $status, int $userId): array
    {
        try {
            $validStatuses = [
                self::STATUS_PENDING,
                self::STATUS_IN_PROGRESS,
                self::STATUS_UNDER_REVIEW,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_ON_HOLD
            ];
            
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Set completion date if completed
            if ($status === self::STATUS_COMPLETED) {
                $updateData['completed_date'] = date('Y-m-d H:i:s');
                $updateData['completion_percentage'] = 100;
            }

            $result = $this->update($taskId, $updateData);
            
            if ($result) {
                // Log status change
                $this->logTaskActivity($taskId, 'status_changed', "Status changed to: {$status}", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Task status updated successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to update task status'];
            
        } catch (\Exception $e) {
            error_log("Update task status error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Status update failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Assign task to users
     */
    public function assignTask(int $taskId, array $userIds, int $assignedBy): array
    {
        try {
            $updateData = [
                'assigned_to' => json_encode($userIds),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->update($taskId, $updateData);
            
            if ($result) {
                // Log assignment
                $userIdsList = implode(', ', $userIds);
                $this->logTaskActivity(
                    $taskId, 
                    'assigned', 
                    "Task assigned to users: {$userIdsList}", 
                    $assignedBy
                );
                
                return [
                    'success' => true,
                    'message' => 'Task assigned successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to assign task'];
            
        } catch (\Exception $e) {
            error_log("Assign task error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Task assignment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get task statistics by scope
     */
    public function getTaskStatistics(string $scope, int $scopeId = null): array
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total_tasks,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_tasks,
                        SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_tasks,
                        SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_tasks,
                        SUM(CASE WHEN due_date < CURDATE() AND status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as overdue_tasks,
                        AVG(completion_percentage) as avg_completion
                      FROM {$this->table}
                      WHERE level_scope = :scope";
            
            $params = ['scope' => $scope];
            
            if ($scopeId) {
                $query .= " AND scope_id = :scope_id";
                $params['scope_id'] = $scopeId;
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate completion rate
            $stats['completion_rate'] = $stats['total_tasks'] > 0 
                ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 2) 
                : 0;
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Get task statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log task activity
     */
    private function logTaskActivity(int $taskId, string $action, string $description, int $userId): void
    {
        try {
            $query = "INSERT INTO task_activities (task_id, user_id, action, description, created_at) 
                     VALUES (:task_id, :user_id, :action, :description, :created_at)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'task_id' => $taskId,
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            error_log("Log task activity error: " . $e->getMessage());
        }
    }

    /**
     * Get task activity history
     */
    public function getTaskHistory(int $taskId): array
    {
        try {
            $query = "SELECT ta.*, 
                             u.first_name, 
                             u.last_name
                      FROM task_activities ta
                      LEFT JOIN users u ON ta.user_id = u.id
                      WHERE ta.task_id = :task_id
                      ORDER BY ta.created_at DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['task_id' => $taskId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get task history error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate UUID for task
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get subtasks
     */
    public function getSubtasks(int $parentTaskId): array
    {
        try {
            $query = "SELECT t.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name
                      FROM {$this->table} t
                      LEFT JOIN users u ON t.created_by = u.id
                      WHERE t.parent_task_id = :parent_task_id
                      ORDER BY t.created_at ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['parent_task_id' => $parentTaskId]);
            
            $subtasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($subtasks as &$task) {
                $task['tags'] = json_decode($task['tags'] ?? '[]', true);
                $task['attachments'] = json_decode($task['attachments'] ?? '[]', true);
                $task['assigned_to'] = json_decode($task['assigned_to'] ?? '[]', true);
            }
            
            return $subtasks;
            
        } catch (\Exception $e) {
            error_log("Get subtasks error: " . $e->getMessage());
            return [];
        }
    }
}