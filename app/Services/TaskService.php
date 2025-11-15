<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Notification;

/**
 * Task Service
 * Business logic for task management
 */
class TaskService
{
    private Task $taskModel;
    private User $userModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->taskModel = new Task();
        $this->userModel = new User();
        $this->notificationService = new NotificationService();
    }

    /**
     * Create task with notifications
     */
    public function createTaskWithNotifications(array $taskData, int $creatorId): array
    {
        try {
            // Create the task
            $result = $this->taskModel->createTask($taskData);
            
            if (!$result['success']) {
                return $result;
            }
            
            $taskId = $result['task_id'];
            
            // Send notifications to assigned users
            if (!empty($taskData['assigned_to'])) {
                $this->notifyAssignedUsers($taskId, $taskData['assigned_to'], $creatorId);
            }
            
            // Send notification to supervisors if it's a high priority task
            if ($taskData['priority'] === Task::PRIORITY_URGENT || $taskData['priority'] === Task::PRIORITY_HIGH) {
                $this->notifySupervisors($taskId, $taskData, $creatorId);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Create task with notifications error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create task with notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update task status with workflow validation
     */
    public function updateTaskStatusWithWorkflow(int $taskId, string $newStatus, int $userId): array
    {
        try {
            // Get current task
            $task = $this->taskModel->find($taskId);
            if (!$task) {
                return ['success' => false, 'message' => 'Task not found'];
            }
            
            // Validate status transition
            $validTransition = $this->validateStatusTransition($task['status'], $newStatus);
            if (!$validTransition['valid']) {
                return ['success' => false, 'message' => $validTransition['message']];
            }
            
            // Update status
            $result = $this->taskModel->updateTaskStatus($taskId, $newStatus, $userId);
            
            if ($result['success']) {
                // Handle status-specific actions
                $this->handleStatusChange($taskId, $task, $newStatus, $userId);
                
                // Send notifications
                $this->notifyStatusChange($taskId, $task, $newStatus, $userId);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Update task status with workflow error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update task status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get task dashboard data for user
     */
    public function getTaskDashboardData(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            $userScope = $this->getUserScope($user);
            
            // Get user's assigned tasks
            $myTasks = $this->taskModel->getTasksAssignedToUser($userId);
            
            // Get scope tasks
            $scopeTasks = $this->taskModel->getTasksByScope($userScope['scope'], $userScope['scope_id']);
            
            // Get statistics
            $statistics = $this->taskModel->getTaskStatistics($userScope['scope'], $userScope['scope_id']);
            
            // Get overdue tasks
            $overdueTasks = $this->getOverdueTasks($userId, $userScope);
            
            // Get upcoming deadlines
            $upcomingDeadlines = $this->getUpcomingDeadlines($userId, $userScope);
            
            // Get task completion trends
            $completionTrends = $this->getTaskCompletionTrends($userScope);
            
            return [
                'success' => true,
                'data' => [
                    'myTasks' => $myTasks,
                    'scopeTasks' => $scopeTasks,
                    'statistics' => $statistics,
                    'overdueTasks' => $overdueTasks,
                    'upcomingDeadlines' => $upcomingDeadlines,
                    'completionTrends' => $completionTrends,
                    'userScope' => $userScope
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Get task dashboard data error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get dashboard data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Auto-assign tasks based on workload and expertise
     */
    public function autoAssignTask(int $taskId, array $scopeUsers): array
    {
        try {
            $task = $this->taskModel->find($taskId);
            if (!$task) {
                return ['success' => false, 'message' => 'Task not found'];
            }
            
            // Calculate user workloads
            $userWorkloads = [];
            foreach ($scopeUsers as $user) {
                $userTasks = $this->taskModel->getTasksAssignedToUser($user['id'], ['status' => ['pending', 'in_progress']]);
                $userWorkloads[$user['id']] = [
                    'user' => $user,
                    'task_count' => count($userTasks),
                    'total_hours' => array_sum(array_column($userTasks, 'estimated_hours')),
                    'priority_score' => $this->calculatePriorityScore($userTasks)
                ];
            }
            
            // Find best candidate based on workload and expertise
            $bestCandidate = $this->findBestCandidateForTask($task, $userWorkloads);
            
            if ($bestCandidate) {
                // Assign task
                $result = $this->taskModel->assignTask($taskId, [$bestCandidate['id']], $task['created_by']);
                
                if ($result['success']) {
                    // Send notification
                    $this->notificationService->sendTaskAssignmentNotification(
                        $bestCandidate['id'],
                        $taskId,
                        $task['title']
                    );
                }
                
                return $result;
            }
            
            return ['success' => false, 'message' => 'No suitable candidate found'];
            
        } catch (\Exception $e) {
            error_log("Auto assign task error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to auto-assign task: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate task reports
     */
    public function generateTaskReport(array $filters): array
    {
        try {
            $reportData = [
                'summary' => $this->getTaskSummaryData($filters),
                'productivity' => $this->getProductivityData($filters),
                'delays' => $this->getDelayAnalysis($filters),
                'workload' => $this->getWorkloadAnalysis($filters),
                'completion_trends' => $this->getCompletionTrendData($filters)
            ];
            
            return [
                'success' => true,
                'data' => $reportData,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            error_log("Generate task report error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate status transition
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): array
    {
        $validTransitions = [
            Task::STATUS_PENDING => [Task::STATUS_IN_PROGRESS, Task::STATUS_CANCELLED, Task::STATUS_ON_HOLD],
            Task::STATUS_IN_PROGRESS => [Task::STATUS_UNDER_REVIEW, Task::STATUS_COMPLETED, Task::STATUS_ON_HOLD, Task::STATUS_CANCELLED],
            Task::STATUS_UNDER_REVIEW => [Task::STATUS_IN_PROGRESS, Task::STATUS_COMPLETED, Task::STATUS_CANCELLED],
            Task::STATUS_ON_HOLD => [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_CANCELLED],
            Task::STATUS_COMPLETED => [], // Cannot change from completed
            Task::STATUS_CANCELLED => [] // Cannot change from cancelled
        ];
        
        if (!isset($validTransitions[$currentStatus])) {
            return ['valid' => false, 'message' => 'Invalid current status'];
        }
        
        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            return ['valid' => false, 'message' => "Cannot change status from {$currentStatus} to {$newStatus}"];
        }
        
        return ['valid' => true, 'message' => 'Valid transition'];
    }

    /**
     * Handle status change actions
     */
    private function handleStatusChange(int $taskId, array $task, string $newStatus, int $userId): void
    {
        switch ($newStatus) {
            case Task::STATUS_COMPLETED:
                // Auto-complete subtasks if parent is completed
                $this->autoCompleteSubtasks($taskId);
                
                // Update project progress if task belongs to a project
                if ($task['project_id']) {
                    $this->updateProjectProgress($task['project_id']);
                }
                break;
                
            case Task::STATUS_IN_PROGRESS:
                // Set start date if not set
                if (!$task['start_date']) {
                    $this->taskModel->update($taskId, ['start_date' => date('Y-m-d')]);
                }
                break;
                
            case Task::STATUS_CANCELLED:
                // Cancel subtasks
                $this->cancelSubtasks($taskId);
                break;
        }
    }

    /**
     * Send notifications for status changes
     */
    private function notifyStatusChange(int $taskId, array $task, string $newStatus, int $userId): void
    {
        // Notify assigned users
        $assignedTo = json_decode($task['assigned_to'] ?? '[]', true);
        foreach ($assignedTo as $assignedUserId) {
            if ($assignedUserId != $userId) {
                $this->notificationService->sendTaskStatusChangeNotification(
                    $assignedUserId,
                    $taskId,
                    $task['title'],
                    $newStatus
                );
            }
        }
        
        // Notify task creator
        if ($task['created_by'] != $userId && !in_array($task['created_by'], $assignedTo)) {
            $this->notificationService->sendTaskStatusChangeNotification(
                $task['created_by'],
                $taskId,
                $task['title'],
                $newStatus
            );
        }
    }

    /**
     * Get overdue tasks
     */
    private function getOverdueTasks(int $userId, array $userScope): array
    {
        try {
            $query = "SELECT t.*, u.first_name, u.last_name
                      FROM tasks t
                      LEFT JOIN users u ON t.created_by = u.id
                      WHERE t.due_date < CURDATE() 
                      AND t.status NOT IN ('completed', 'cancelled')
                      AND (JSON_CONTAINS(t.assigned_to, :user_id) 
                           OR (t.level_scope = :scope AND t.scope_id = :scope_id))
                      ORDER BY t.due_date ASC";
            
            $stmt = $this->taskModel->db->prepare($query);
            $stmt->execute([
                'user_id' => json_encode([$userId]),
                'scope' => $userScope['scope'],
                'scope_id' => $userScope['scope_id']
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get overdue tasks error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming deadlines
     */
    private function getUpcomingDeadlines(int $userId, array $userScope): array
    {
        try {
            $query = "SELECT t.*, u.first_name, u.last_name
                      FROM tasks t
                      LEFT JOIN users u ON t.created_by = u.id
                      WHERE t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                      AND t.status NOT IN ('completed', 'cancelled')
                      AND (JSON_CONTAINS(t.assigned_to, :user_id) 
                           OR (t.level_scope = :scope AND t.scope_id = :scope_id))
                      ORDER BY t.due_date ASC";
            
            $stmt = $this->taskModel->db->prepare($query);
            $stmt->execute([
                'user_id' => json_encode([$userId]),
                'scope' => $userScope['scope'],
                'scope_id' => $userScope['scope_id']
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get upcoming deadlines error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get task completion trends
     */
    private function getTaskCompletionTrends(array $userScope): array
    {
        try {
            $query = "SELECT 
                        DATE(completed_date) as completion_date,
                        COUNT(*) as completed_count
                      FROM tasks 
                      WHERE status = 'completed' 
                      AND completed_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      AND level_scope = :scope
                      AND scope_id = :scope_id
                      GROUP BY DATE(completed_date)
                      ORDER BY completion_date ASC";
            
            $stmt = $this->taskModel->db->prepare($query);
            $stmt->execute([
                'scope' => $userScope['scope'],
                'scope_id' => $userScope['scope_id']
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get completion trends error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate priority score for user workload
     */
    private function calculatePriorityScore(array $tasks): int
    {
        $score = 0;
        foreach ($tasks as $task) {
            switch ($task['priority']) {
                case Task::PRIORITY_URGENT:
                    $score += 4;
                    break;
                case Task::PRIORITY_HIGH:
                    $score += 3;
                    break;
                case Task::PRIORITY_MEDIUM:
                    $score += 2;
                    break;
                case Task::PRIORITY_LOW:
                    $score += 1;
                    break;
            }
        }
        return $score;
    }

    /**
     * Find best candidate for task assignment
     */
    private function findBestCandidateForTask(array $task, array $userWorkloads): ?array
    {
        $bestCandidate = null;
        $lowestWorkload = PHP_INT_MAX;
        
        foreach ($userWorkloads as $userId => $workload) {
            $user = $workload['user'];
            
            // Skip if user doesn't have appropriate role/skills
            if (!$this->userHasSkillsForTask($user, $task)) {
                continue;
            }
            
            // Calculate combined workload score
            $workloadScore = $workload['task_count'] + ($workload['total_hours'] / 40) + ($workload['priority_score'] / 10);
            
            if ($workloadScore < $lowestWorkload) {
                $lowestWorkload = $workloadScore;
                $bestCandidate = $user;
            }
        }
        
        return $bestCandidate;
    }

    /**
     * Check if user has skills for task
     */
    private function userHasSkillsForTask(array $user, array $task): bool
    {
        // Basic implementation - can be enhanced with skill matching
        return $user['status'] === 'active';
    }

    /**
     * Auto-complete subtasks when parent is completed
     */
    private function autoCompleteSubtasks(int $parentTaskId): void
    {
        try {
            $subtasks = $this->taskModel->getSubtasks($parentTaskId);
            
            foreach ($subtasks as $subtask) {
                if ($subtask['status'] !== Task::STATUS_COMPLETED) {
                    $this->taskModel->updateTaskStatus(
                        $subtask['id'], 
                        Task::STATUS_COMPLETED, 
                        $subtask['created_by']
                    );
                }
            }
            
        } catch (\Exception $e) {
            error_log("Auto complete subtasks error: " . $e->getMessage());
        }
    }

    /**
     * Cancel subtasks when parent is cancelled
     */
    private function cancelSubtasks(int $parentTaskId): void
    {
        try {
            $subtasks = $this->taskModel->getSubtasks($parentTaskId);
            
            foreach ($subtasks as $subtask) {
                if (!in_array($subtask['status'], [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])) {
                    $this->taskModel->updateTaskStatus(
                        $subtask['id'], 
                        Task::STATUS_CANCELLED, 
                        $subtask['created_by']
                    );
                }
            }
            
        } catch (\Exception $e) {
            error_log("Cancel subtasks error: " . $e->getMessage());
        }
    }

    /**
     * Get user scope for task access
     */
    private function getUserScope(array $user): array
    {
        // This should match the logic from other controllers
        $scope = $user['level_scope'] ?? 'gurmu';
        $scopeId = null;
        
        switch ($scope) {
            case 'global':
                $scopeId = 1; // Global ID
                break;
            case 'godina':
                $scopeId = $user['godina_id'] ?? null;
                break;
            case 'gamta':
                $scopeId = $user['gamta_id'] ?? null;
                break;
            case 'gurmu':
                $scopeId = $user['gurmu_id'] ?? null;
                break;
        }
        
        return ['scope' => $scope, 'scope_id' => $scopeId];
    }

    /**
     * Notify assigned users about new task
     */
    private function notifyAssignedUsers(int $taskId, array $assignedUserIds, int $creatorId): void
    {
        foreach ($assignedUserIds as $userId) {
            if ($userId != $creatorId) {
                $this->notificationService->sendTaskAssignmentNotification($userId, $taskId);
            }
        }
    }

    /**
     * Notify supervisors about high priority tasks
     */
    private function notifySupervisors(int $taskId, array $taskData, int $creatorId): void
    {
        // Implementation for notifying supervisors
        // This would depend on the organizational hierarchy
    }
}