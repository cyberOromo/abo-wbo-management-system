<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Utils\Database;

/**
 * Enhanced Task Controller
 * 
 * Handles comprehensive task management operations with enterprise-grade features
 * for Global events, multiple task assignments, and hierarchical sub-task management
 * in ABO-WBO Management System.
 * 
 * @package App\Controllers
 * @version 2.0.0
 */
class TaskController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display enhanced task dashboard with hierarchical overview
     */
    public function index()
    {
        try {
            $user = $this->getAuthUser();
            if (!$user) {
                return $this->redirect('/auth/login');
            }
            
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            // Get basic task data
            $tasks = $this->getTasksForUserScope($userScope);
            $taskStats = $this->getTaskStatistics($userScope);
            
            return $this->render('tasks/index_modern', [
                'title' => 'Tasks Management',
                'tasks' => $tasks,
                'task_stats' => $taskStats,
                'user_scope' => $userScope,
                'can_create' => true
            ]);
            
        } catch (\Exception $e) {
            error_log("TaskController::index error: " . $e->getMessage());
            return $this->errorResponse('Failed to load tasks', 500);
        }
    }

    /**
     * Display enhanced create task form with Global event support
     */
    public function create()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserScope($user);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->handleTaskCreation($user, $userScope);
            }
            
            // GET request - show enhanced create form
            $availableUsers = $this->getAvailableUsersForScope($userScope);
            $organizationHierarchy = $this->getOrganizationHierarchy($user);
            $taskTemplates = $this->taskService->getTaskTemplates();
            $parentTasks = $this->taskService->getAvailableParentTasks($userScope);
            
            // Get assignment options for Global events
            $assignmentLevels = $this->getAssignmentLevels($user);
            $globalUsers = $this->getGlobalAssignmentUsers($user);
            
            // Get categories and priority options
            $categories = $this->taskService->getTaskCategories();
            $priorities = $this->taskService->getPriorityLevels();
            $scopes = $this->getAvailableScopes($user);
            
            $this->view('tasks/create', [
                'user' => $user,
                'userScope' => $userScope,
                'availableUsers' => $availableUsers,
                'organizationHierarchy' => $organizationHierarchy,
                'taskTemplates' => $taskTemplates,
                'parentTasks' => $parentTasks,
                'assignmentLevels' => $assignmentLevels,
                'globalUsers' => $globalUsers,
                'categories' => $categories,
                'priorities' => $priorities,
                'scopes' => $scopes,
                'csrf_token' => $this->generateCsrfToken(),
                'max_file_size' => $this->fileUploadService->getMaxFileSize(),
                'allowed_file_types' => $this->fileUploadService->getAllowedFileTypes()
            ]);
            
        } catch (\Exception $e) {
            error_log("Enhanced task create form error: " . $e->getMessage());
            $this->auditService->logError('task_create_form_error', $e->getMessage(), $user['id'] ?? null);
            $this->redirect('/tasks?error=create_form_failed');
        }
    }

    /**
     * Store new task
     */
    public function store(): void
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserScope($user);
            
            // Validate CSRF token
            if (!$this->validateCSRFToken()) {
                $this->redirect('/tasks/create?error=invalid_token');
                return;
            }
            
            // Get form data
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'level_scope' => $userScope['scope'],
                'scope_id' => $userScope['scope_id'],
                'parent_task_id' => !empty($_POST['parent_task_id']) ? (int)$_POST['parent_task_id'] : null,
                'category' => $_POST['category'] ?? Task::CATEGORY_ADMINISTRATIVE,
                'priority' => $_POST['priority'] ?? Task::PRIORITY_MEDIUM,
                'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'estimated_hours' => !empty($_POST['estimated_hours']) ? (int)$_POST['estimated_hours'] : null,
                'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [],
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : [],
                'created_by' => $user['id']
            ];
            
            // Validate required fields
            $validation = $this->validateTaskData($data);
            if (!$validation['valid']) {
                $this->redirect('/tasks/create?error=' . urlencode($validation['message']));
                return;
            }
            
            // Create task
            $result = $this->taskModel->createTask($data);
            
            if ($result['success']) {
                $this->redirect('/tasks?success=task_created');
            } else {
                $this->redirect('/tasks/create?error=' . urlencode($result['message']));
            }
            
        } catch (\Exception $e) {
            error_log("Task store error: " . $e->getMessage());
            $this->redirect('/tasks/create?error=creation_failed');
        }
    }

    /**
     * Display specific task
     */
    public function show(int $id): void
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserScope($user);
            
            // Get task details
            $task = $this->taskModel->find($id);
            if (!$task) {
                $this->redirect('/tasks?error=task_not_found');
                return;
            }
            
            // Check if user has access to this task
            if (!$this->canAccessTask($task, $user, $userScope)) {
                $this->redirect('/tasks?error=access_denied');
                return;
            }
            
            // Decode JSON fields
            $task['tags'] = json_decode($task['tags'] ?? '[]', true);
            $task['attachments'] = json_decode($task['attachments'] ?? '[]', true);
            $task['assigned_to'] = json_decode($task['assigned_to'] ?? '[]', true);
            
            // Get task history
            $history = $this->taskModel->getTaskHistory($id);
            
            // Get subtasks
            $subtasks = $this->taskModel->getSubtasks($id);
            
            // Get assigned users details
            $assignedUsers = [];
            if (!empty($task['assigned_to'])) {
                foreach ($task['assigned_to'] as $userId) {
                    $assignedUser = $this->userModel->find($userId);
                    if ($assignedUser) {
                        $assignedUsers[] = $assignedUser;
                    }
                }
            }
            
            $this->view('tasks/show', [
                'task' => $task,
                'history' => $history,
                'subtasks' => $subtasks,
                'assignedUsers' => $assignedUsers,
                'user' => $user,
                'userScope' => $userScope
            ]);
            
        } catch (\Exception $e) {
            error_log("Task show error: " . $e->getMessage());
            $this->redirect('/tasks?error=task_load_failed');
        }
    }

    /**
     * Display edit task form
     */
    public function edit(int $id): void
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserScope($user);
            
            // Get task details
            $task = $this->taskModel->find($id);
            if (!$task) {
                $this->redirect('/tasks?error=task_not_found');
                return;
            }
            
            // Check if user can edit this task
            if (!$this->canEditTask($task, $user, $userScope)) {
                $this->redirect('/tasks?error=edit_access_denied');
                return;
            }
            
            // Decode JSON fields
            $task['tags'] = json_decode($task['tags'] ?? '[]', true);
            $task['assigned_to'] = json_decode($task['assigned_to'] ?? '[]', true);
            
            // Get available users for assignment
            $availableUsers = $this->getAvailableUsersForScope($userScope);
            
            // Get parent tasks
            $parentTasks = $this->taskModel->getTasksByScope(
                $userScope['scope'], 
                $userScope['scope_id'],
                ['status' => ['pending', 'in_progress']]
            );
            
            $this->view('tasks/edit', [
                'task' => $task,
                'availableUsers' => $availableUsers,
                'parentTasks' => $parentTasks,
                'user' => $user,
                'userScope' => $userScope
            ]);
            
        } catch (\Exception $e) {
            error_log("Task edit form error: " . $e->getMessage());
            $this->redirect('/tasks?error=edit_form_failed');
        }
    }

    /**
     * Update task
     */
    public function update(int $id): void
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserScope($user);
            
            // Validate CSRF token
            if (!$this->validateCSRFToken()) {
                $this->redirect('/tasks/' . $id . '/edit?error=invalid_token');
                return;
            }
            
            // Get task
            $task = $this->taskModel->find($id);
            if (!$task || !$this->canEditTask($task, $user, $userScope)) {
                $this->redirect('/tasks?error=update_access_denied');
                return;
            }
            
            // Get update data
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'parent_task_id' => !empty($_POST['parent_task_id']) ? (int)$_POST['parent_task_id'] : null,
                'category' => $_POST['category'] ?? Task::CATEGORY_ADMINISTRATIVE,
                'priority' => $_POST['priority'] ?? Task::PRIORITY_MEDIUM,
                'status' => $_POST['status'] ?? $task['status'],
                'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'estimated_hours' => !empty($_POST['estimated_hours']) ? (int)$_POST['estimated_hours'] : null,
                'actual_hours' => !empty($_POST['actual_hours']) ? (int)$_POST['actual_hours'] : null,
                'completion_percentage' => !empty($_POST['completion_percentage']) ? (int)$_POST['completion_percentage'] : 0,
                'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [],
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : [],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Set completion date if completed
            if ($data['status'] === Task::STATUS_COMPLETED && $task['status'] !== Task::STATUS_COMPLETED) {
                $data['completed_date'] = date('Y-m-d H:i:s');
                $data['completion_percentage'] = 100;
            } elseif ($data['status'] !== Task::STATUS_COMPLETED) {
                $data['completed_date'] = null;
            }
            
            // Encode JSON fields
            $data['tags'] = json_encode($data['tags']);
            $data['assigned_to'] = json_encode($data['assigned_to']);
            
            // Validate data
            $validation = $this->validateTaskData($data, true);
            if (!$validation['valid']) {
                $this->redirect('/tasks/' . $id . '/edit?error=' . urlencode($validation['message']));
                return;
            }
            
            // Update task
            $result = $this->taskModel->update($id, $data);
            
            if ($result) {
                // Log activity
                $this->taskModel->logTaskActivity($id, 'updated', 'Task updated', $user['id']);
                $this->redirect('/tasks/' . $id . '?success=task_updated');
            } else {
                $this->redirect('/tasks/' . $id . '/edit?error=update_failed');
            }
            
        } catch (\Exception $e) {
            error_log("Task update error: " . $e->getMessage());
            $this->redirect('/tasks/' . $id . '/edit?error=update_failed');
        }
    }

    /**
     * Update task status
     */
    public function updateStatus(int $id): void
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserScope($user);
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            $status = $input['status'] ?? '';
            
            if (empty($status)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Status is required']);
                return;
            }
            
            // Get task
            $task = $this->taskModel->find($id);
            if (!$task || !$this->canAccessTask($task, $user, $userScope)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                return;
            }
            
            // Update status
            $result = $this->taskModel->updateTaskStatus($id, $status, $user['id']);
            
            http_response_code($result['success'] ? 200 : 400);
            header('Content-Type: application/json');
            echo json_encode($result);
            
        } catch (\Exception $e) {
            error_log("Update task status error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Get tasks API endpoint
     */
    public function api(): void
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserScope($user);
            
            // Get filters
            $filters = [
                'status' => $_GET['status'] ?? '',
                'priority' => $_GET['priority'] ?? '',
                'category' => $_GET['category'] ?? '',
                'assigned_to' => $_GET['assigned_to'] ?? '',
                'due_date_from' => $_GET['due_date_from'] ?? '',
                'due_date_to' => $_GET['due_date_to'] ?? ''
            ];
            
            // Remove empty filters
            $filters = array_filter($filters);
            
            // Get tasks
            $tasks = $this->taskModel->getTasksByScope($userScope['scope'], $userScope['scope_id'], $filters);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'tasks' => $tasks,
                'total' => count($tasks)
            ]);
            
        } catch (\Exception $e) {
            error_log("Task API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Validate task data
     */
    private function validateTaskData(array $data, bool $isUpdate = false): array
    {
        if (empty($data['title'])) {
            return ['valid' => false, 'message' => 'Task title is required'];
        }
        
        if (strlen($data['title']) > 255) {
            return ['valid' => false, 'message' => 'Task title is too long'];
        }
        
        if (!empty($data['due_date']) && !empty($data['start_date'])) {
            if (strtotime($data['due_date']) < strtotime($data['start_date'])) {
                return ['valid' => false, 'message' => 'Due date cannot be before start date'];
            }
        }
        
        if (!empty($data['estimated_hours']) && $data['estimated_hours'] < 0) {
            return ['valid' => false, 'message' => 'Estimated hours cannot be negative'];
        }
        
        if (!empty($data['actual_hours']) && $data['actual_hours'] < 0) {
            return ['valid' => false, 'message' => 'Actual hours cannot be negative'];
        }
        
        $validCategories = [
            Task::CATEGORY_ADMINISTRATIVE,
            Task::CATEGORY_FINANCIAL,
            Task::CATEGORY_EDUCATIONAL,
            Task::CATEGORY_SOCIAL,
            Task::CATEGORY_TECHNICAL
        ];
        
        if (!in_array($data['category'], $validCategories)) {
            return ['valid' => false, 'message' => 'Invalid task category'];
        }
        
        $validPriorities = [
            Task::PRIORITY_LOW,
            Task::PRIORITY_MEDIUM,
            Task::PRIORITY_HIGH,
            Task::PRIORITY_URGENT
        ];
        
        if (!in_array($data['priority'], $validPriorities)) {
            return ['valid' => false, 'message' => 'Invalid task priority'];
        }
        
        return ['valid' => true, 'message' => 'Valid'];
    }

    /**
     * Check if user can access task
     */
    private function canAccessTask(array $task, array $user, array $userScope): bool
    {
        // Admin can access all tasks
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Check if task is in user's scope
        if ($task['level_scope'] === $userScope['scope'] && $task['scope_id'] == $userScope['scope_id']) {
            return true;
        }
        
        // Check if user is assigned to the task
        $assignedTo = json_decode($task['assigned_to'] ?? '[]', true);
        if (in_array($user['id'], $assignedTo)) {
            return true;
        }
        
        // Check if user created the task
        if ($task['created_by'] == $user['id']) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can edit task
     */
    private function canEditTask(array $task, array $user, array $userScope): bool
    {
        // Admin can edit all tasks
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Task creator can edit
        if ($task['created_by'] == $user['id']) {
            return true;
        }
        
        // Executive in same scope can edit
        if ($user['role'] === 'executive' && 
            $task['level_scope'] === $userScope['scope'] && 
            $task['scope_id'] == $userScope['scope_id']) {
            return true;
        }
        
        return false;
    }

    /**
     * Get available users for task assignment based on scope
     */
    private function getAvailableUsersForScope(array $userScope): array
    {
        try {
            switch ($userScope['scope']) {
                case 'global':
                    return $this->userModel->getAllActiveUsers();
                    
                case 'godina':
                    return $this->userModel->getUsersByGodina($userScope['scope_id']);
                    
                case 'gamta':
                    return $this->userModel->getUsersByGamta($userScope['scope_id']);
                    
                case 'gurmu':
                    return $this->userModel->getUsersByGurmu($userScope['scope_id']);
                    
                default:
                    return [];
            }
        } catch (\Exception $e) {
            error_log("Get available users error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent task activities for scope
     */
    private function getRecentTaskActivities(array $userScope): array
    {
        try {
            $query = "SELECT ta.*, t.title as task_title, u.first_name, u.last_name
                      FROM task_activities ta
                      JOIN tasks t ON ta.task_id = t.id
                      JOIN users u ON ta.user_id = u.id
                      WHERE t.level_scope = :scope";
            
            $params = ['scope' => $userScope['scope']];
            
            if ($userScope['scope_id']) {
                $query .= " AND t.scope_id = :scope_id";
                $params['scope_id'] = $userScope['scope_id'];
            }
            
            $query .= " ORDER BY ta.created_at DESC LIMIT 10";
            
            $stmt = $this->taskModel->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get recent activities error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle enhanced task creation with Global events and multiple assignments
     */
    private function handleTaskCreation(array $user, array $userScope): void
    {
        try {
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                $this->setFlashMessage('error', __('common.errors.csrf_token_mismatch'));
                $this->redirect('/tasks/create');
                return;
            }
            
            // Get and validate form data
            $taskData = $this->getTaskFormData();
            $validation = $this->validator->validateTaskCreation($taskData, $user);
            
            if (!$validation['valid']) {
                $this->setFlashMessage('error', $validation['message']);
                $this->redirect('/tasks/create');
                return;
            }
            
            // Create main task
            $taskId = $this->taskService->createEnhancedTask($taskData, $user['id']);
            
            // Handle Global event multiple assignments
            if ($taskData['scope'] === 'global' && isset($taskData['global_assignments'])) {
                $this->handleGlobalTaskAssignments($taskId, $taskData['global_assignments'], $user['id']);
            }
            
            // Handle hierarchical sub-tasks
            if (isset($taskData['subtasks']) && is_array($taskData['subtasks'])) {
                $this->createHierarchicalSubTasks($taskId, $taskData['subtasks'], $user['id']);
            }
            
            // Handle file attachments
            if (isset($_FILES['attachments'])) {
                $this->handleTaskAttachments($taskId, $_FILES['attachments']);
            }
            
            // Handle task dependencies
            if (isset($taskData['dependencies'])) {
                $this->taskService->createTaskDependencies($taskId, $taskData['dependencies']);
            }
            
            // Send notifications to all assigned users
            $this->sendEnhancedTaskNotifications($taskId, 'created', $user['id']);
            
            // Log comprehensive audit trail
            $this->auditService->logActivity('enhanced_task_created', [
                'task_id' => $taskId,
                'created_by' => $user['id'],
                'task_data' => $taskData,
                'scope' => $taskData['scope'],
                'assignments_count' => count($taskData['global_assignments'] ?? []),
                'subtasks_count' => count($taskData['subtasks'] ?? [])
            ]);
            
            $this->setFlashMessage('success', __('tasks.task_created_successfully'));
            $this->redirect('/tasks/' . $taskId);
            
        } catch (\Exception $e) {
            error_log("Enhanced task creation error: " . $e->getMessage());
            $this->auditService->logError('task_creation_error', $e->getMessage(), $user['id']);
            $this->setFlashMessage('error', __('tasks.creation_failed'));
            $this->redirect('/tasks/create');
        }
    }

    /**
     * Handle Global task assignments across multiple organizational levels
     */
    private function handleGlobalTaskAssignments(int $taskId, array $assignments, int $createdBy): void
    {
        foreach ($assignments as $assignment) {
            // Validate assignment based on organizational hierarchy
            if ($this->validateGlobalAssignment($assignment)) {
                $assignmentData = [
                    'task_id' => $taskId,
                    'assigned_to' => $assignment['user_id'],
                    'organization_level' => $assignment['level'],
                    'organization_id' => $assignment['organization_id'],
                    'role' => $assignment['role'] ?? 'assignee',
                    'assigned_by' => $createdBy,
                    'assigned_at' => date('Y-m-d H:i:s'),
                    'due_date' => $assignment['due_date'] ?? null,
                    'priority' => $assignment['priority'] ?? 'normal',
                    'notes' => $assignment['notes'] ?? null,
                    'permissions' => json_encode($assignment['permissions'] ?? []),
                    'expected_hours' => $assignment['expected_hours'] ?? null
                ];
                
                $this->taskService->createTaskAssignment($assignmentData);
                
                // Create notification for assigned user
                $this->notificationService->sendGlobalAssignmentNotification($assignmentData);
            }
        }
    }

    /**
     * Create hierarchical sub-tasks with proper nesting
     */
    private function createHierarchicalSubTasks(int $parentTaskId, array $subTasks, int $createdBy, int $level = 1): void
    {
        foreach ($subTasks as $subTaskData) {
            $subTaskData['parent_task_id'] = $parentTaskId;
            $subTaskData['created_by'] = $createdBy;
            $subTaskData['hierarchy_level'] = $level;
            $subTaskData['scope'] = $subTaskData['scope'] ?? 'inherited';
            
            // Create sub-task
            $subTaskId = $this->taskService->createTask($subTaskData, $createdBy);
            
            // Handle sub-task assignments
            if (isset($subTaskData['assignments'])) {
                foreach ($subTaskData['assignments'] as $assignment) {
                    $this->taskService->createTaskAssignment([
                        'task_id' => $subTaskId,
                        'assigned_to' => $assignment['user_id'],
                        'assigned_by' => $createdBy,
                        'role' => $assignment['role'] ?? 'assignee',
                        'due_date' => $assignment['due_date'] ?? null
                    ]);
                }
            }
            
            // Recursively create nested sub-tasks (max 5 levels deep)
            if (isset($subTaskData['subtasks']) && is_array($subTaskData['subtasks']) && $level < 5) {
                $this->createHierarchicalSubTasks($subTaskId, $subTaskData['subtasks'], $createdBy, $level + 1);
            }
        }
    }

    /**
     * Handle task file attachments with security validation
     */
    private function handleTaskAttachments(int $taskId, array $files): void
    {
        try {
            $uploadedFiles = [];
            
            foreach ($files['name'] as $key => $fileName) {
                if ($files['size'][$key] > 0) {
                    $fileData = [
                        'name' => $fileName,
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'size' => $files['size'][$key],
                        'error' => $files['error'][$key]
                    ];
                    
                    // Upload file with security validation
                    $uploadResult = $this->fileUploadService->uploadTaskAttachment($fileData, $taskId);
                    
                    if ($uploadResult['success']) {
                        $uploadedFiles[] = $uploadResult['file_info'];
                        
                        // Log file upload
                        $this->auditService->logActivity('task_attachment_uploaded', [
                            'task_id' => $taskId,
                            'file_name' => $fileName,
                            'file_size' => $files['size'][$key],
                            'file_path' => $uploadResult['file_info']['path']
                        ]);
                    }
                }
            }
            
            // Update task with attachment information
            if (!empty($uploadedFiles)) {
                $this->taskService->updateTaskAttachments($taskId, $uploadedFiles);
            }
            
        } catch (\Exception $e) {
            error_log("Task attachment upload error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send enhanced notifications for task operations
     */
    private function sendEnhancedTaskNotifications(int $taskId, string $action, int $userId): void
    {
        $task = $this->taskService->getTaskById($taskId);
        $assignments = $this->taskService->getTaskAssignments($taskId);
        
        // Send notifications to all assigned users
        foreach ($assignments as $assignment) {
            $notificationData = [
                'type' => "task_{$action}",
                'task_id' => $taskId,
                'task_title' => $task['title'],
                'task_priority' => $task['priority'],
                'task_scope' => $task['scope'],
                'recipient_id' => $assignment['assigned_to'],
                'sender_id' => $userId,
                'message' => $this->generateEnhancedNotificationMessage($action, $task, $assignment),
                'url' => "/tasks/{$taskId}",
                'priority' => $this->getNotificationPriority($task['priority']),
                'channels' => $this->getNotificationChannels($assignment, $task),
                'metadata' => json_encode([
                    'task_scope' => $task['scope'],
                    'organization_level' => $assignment['organization_level'] ?? null,
                    'due_date' => $task['due_date'] ?? null,
                    'hierarchy_level' => $task['hierarchy_level'] ?? null
                ])
            ];
            
            $this->notificationService->sendEnhancedNotification($notificationData);
        }
        
        // Send notifications to supervisors if this is a Global task
        if ($task['scope'] === 'global') {
            $this->sendGlobalTaskSupervisorNotifications($task, $action, $userId);
        }
    }

    /**
     * Generate enhanced notification messages
     */
    private function generateEnhancedNotificationMessage(string $action, array $task, array $assignment): string
    {
        $baseKey = "tasks.notifications.{$action}";
        $context = [
            'title' => $task['title'],
            'priority' => __("tasks.priority.{$task['priority']}"),
            'scope' => __("tasks.scope.{$task['scope']}"),
            'due_date' => $task['due_date'] ? DateTimeHelper::formatDate($task['due_date']) : null,
            'role' => __("tasks.roles.{$assignment['role']}")
        ];
        
        return __($baseKey, $context);
    }

    /**
     * Get notification priority based on task priority
     */
    private function getNotificationPriority(string $taskPriority): string
    {
        $priorityMap = [
            'low' => 'low',
            'normal' => 'normal',
            'high' => 'high',
            'urgent' => 'urgent',
            'critical' => 'critical'
        ];
        
        return $priorityMap[$taskPriority] ?? 'normal';
    }

    /**
     * Get notification channels based on assignment and task context
     */
    private function getNotificationChannels(array $assignment, array $task): array
    {
        $channels = ['in_app'];
        
        // Add email for high priority or global tasks
        if (in_array($task['priority'], ['high', 'urgent', 'critical']) || $task['scope'] === 'global') {
            $channels[] = 'email';
        }
        
        // Add SMS for critical priority
        if ($task['priority'] === 'critical') {
            $channels[] = 'sms';
        }
        
        return $channels;
    }

    /**
     * Validate Global task assignment
     */
    private function validateGlobalAssignment(array $assignment): bool
    {
        // Check required fields
        $requiredFields = ['user_id', 'level', 'organization_id'];
        foreach ($requiredFields as $field) {
            if (!isset($assignment[$field]) || empty($assignment[$field])) {
                return false;
            }
        }
        
        // Validate organizational hierarchy
        return $this->userService->validateOrganizationalHierarchy(
            $assignment['user_id'],
            $assignment['level'],
            $assignment['organization_id']
        );
    }

    /**
     * Get task form data with validation and sanitization
     */
    private function getTaskFormData(): array
    {
        return [
            'title' => SecurityHelper::sanitizeInput($_POST['title'] ?? ''),
            'description' => SecurityHelper::sanitizeInput($_POST['description'] ?? ''),
            'scope' => $_POST['scope'] ?? 'gurmu',
            'category' => $_POST['category'] ?? 'administrative',
            'priority' => $_POST['priority'] ?? 'normal',
            'start_date' => $_POST['start_date'] ?? null,
            'due_date' => $_POST['due_date'] ?? null,
            'estimated_hours' => !empty($_POST['estimated_hours']) ? (int)$_POST['estimated_hours'] : null,
            'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [],
            'global_assignments' => $_POST['global_assignments'] ?? [],
            'subtasks' => $_POST['subtasks'] ?? [],
            'dependencies' => $_POST['dependencies'] ?? [],
            'template_id' => !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null,
            'parent_task_id' => !empty($_POST['parent_task_id']) ? (int)$_POST['parent_task_id'] : null,
            'organization_id' => $_POST['organization_id'] ?? null,
            'is_recurring' => isset($_POST['is_recurring']) ? (bool)$_POST['is_recurring'] : false,
            'recurrence_pattern' => $_POST['recurrence_pattern'] ?? null,
            'metadata' => $_POST['metadata'] ?? []
        ];
    }

    /**
     * Get user permissions for task operations
     */
    private function getUserPermissions(int $userId): array
    {
        return $this->userService->getUserPermissions($userId, 'tasks');
    }

    /**
     * Get organization hierarchy for Global event management
     */
    private function getOrganizationHierarchy(array $user): array
    {
        return $this->userService->getOrganizationHierarchy($user['id']);
    }

    /**
     * Get quick actions based on user permissions
     */
    private function getQuickActions(array $user, array $permissions): array
    {
        $actions = [];
        
        if (in_array('create_task', $permissions)) {
            $actions[] = ['key' => 'create_task', 'url' => '/tasks/create', 'icon' => 'plus'];
        }
        
        if (in_array('create_global_event', $permissions)) {
            $actions[] = ['key' => 'create_global_event', 'url' => '/tasks/create?scope=global', 'icon' => 'globe'];
        }
        
        if (in_array('manage_assignments', $permissions)) {
            $actions[] = ['key' => 'manage_assignments', 'url' => '/tasks/assignments', 'icon' => 'users'];
        }
        
        return $actions;
    }

    /**
     * Get assignment levels available to user
     */
    private function getAssignmentLevels(array $user): array
    {
        $userLevel = $user['organization_level'];
        $levels = [];
        
        switch ($userLevel) {
            case 'global':
                $levels = ['global', 'godina', 'gamta', 'gurmu'];
                break;
            case 'godina':
                $levels = ['godina', 'gamta', 'gurmu'];
                break;
            case 'gamta':
                $levels = ['gamta', 'gurmu'];
                break;
            case 'gurmu':
                $levels = ['gurmu'];
                break;
        }
        
        return $levels;
    }

    /**
     * Get users available for Global assignments
     */
    private function getGlobalAssignmentUsers(array $user): array
    {
        if ($user['organization_level'] === 'global') {
            return $this->userService->getAllActiveUsers();
        }
        
        return $this->userService->getUsersByHierarchy($user['organization_level'], $user['organization_id']);
    }

    /**
     * Get available scopes for task creation
     */
    private function getAvailableScopes(array $user): array
    {
        return $this->getAssignmentLevels($user);
    }

    /**
     * Send Global task supervisor notifications
     */
    private function sendGlobalTaskSupervisorNotifications(array $task, string $action, int $userId): void
    {
        $supervisors = $this->userService->getGlobalSupervisors();
        
        foreach ($supervisors as $supervisor) {
            $this->notificationService->sendSupervisorNotification([
                'type' => "global_task_{$action}",
                'task_id' => $task['id'],
                'recipient_id' => $supervisor['id'],
                'sender_id' => $userId,
                'message' => __("tasks.notifications.global_task_{$action}", ['title' => $task['title']]),
                'priority' => 'high'
            ]);
        }
    }
    
    // Helper Methods for Basic Functionality
    
    protected function getUserHierarchicalScope($userId)
    {
        $db = \App\Utils\Database::getInstance();
        
        $sql = "SELECT ua.*, p.name as position_name, p.hierarchy_type,
                       go.name as godina_name, ga.name as gamta_name, gu.name as gurmu_name
                FROM user_assignments ua
                LEFT JOIN positions p ON ua.position_id = p.id
                LEFT JOIN godinas go ON ua.godina_id = go.id
                LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
                LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
                WHERE ua.user_id = ? AND ua.status = 'active'
                LIMIT 1";
        
        return $db->fetch($sql, [$userId]) ?: [];
    }
    
    protected function getTasksForUserScope($userScope)
    {
        $db = \App\Utils\Database::getInstance();
        $hasScopeId = $db->columnExists('tasks', 'scope_id');
        $hasGodinaId = $db->columnExists('tasks', 'godina_id');
        $hasGamtaId = $db->columnExists('tasks', 'gamta_id');
        $hasGurmuId = $db->columnExists('tasks', 'gurmu_id');
        
        $sql = "SELECT t.*, u.first_name, u.last_name,
                       au.first_name as assigned_first_name, au.last_name as assigned_last_name
                FROM tasks t
                LEFT JOIN users u ON t.created_by = u.id
                LEFT JOIN users au ON t.assigned_to = au.id
                WHERE 1=1";
        
        $params = [];
        
        // Apply hierarchy filtering based on user scope
        if (!empty($userScope)) {
            $userId = $userScope['user_id'] ?? null;
            $scopeLevel = $userScope['level_scope'] ?? null;
            $scopeId = null;

            if ($scopeLevel === 'gurmu') {
                $scopeId = $userScope['gurmu_id'] ?? null;
            } elseif ($scopeLevel === 'gamta') {
                $scopeId = $userScope['gamta_id'] ?? null;
            } elseif ($scopeLevel === 'godina') {
                $scopeId = $userScope['godina_id'] ?? null;
            }

            if ($scopeLevel && $scopeId !== null && $userId && $hasScopeId) {
                $sql .= " AND ((t.level_scope = ? AND t.scope_id = ?) OR t.assigned_to = ?)";
                $params[] = $scopeLevel;
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel === 'gurmu' && $scopeId !== null && $userId && $hasGurmuId) {
                $sql .= " AND (t.gurmu_id = ? OR t.assigned_to = ?)";
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel === 'gamta' && $scopeId !== null && $userId && $hasGamtaId) {
                $sql .= " AND (t.gamta_id = ? OR t.assigned_to = ?)";
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel === 'godina' && $scopeId !== null && $userId && $hasGodinaId) {
                $sql .= " AND (t.godina_id = ? OR t.assigned_to = ?)";
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel && $userId) {
                $sql .= " AND (t.level_scope = ? OR t.assigned_to = ? OR t.created_by = ?)";
                $params[] = $scopeLevel;
                $params[] = $userId;
                $params[] = $userId;
            } elseif ($userId) {
                $sql .= " AND (t.assigned_to = ? OR t.created_by = ?)";
                $params[] = $userId;
                $params[] = $userId;
            }
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT 100";
        
        return $db->fetchAll($sql, $params);
    }
    
    protected function getTaskStatistics($userScope)
    {
        $db = \App\Utils\Database::getInstance();
        $hasScopeId = $db->columnExists('tasks', 'scope_id');
        $hasGodinaId = $db->columnExists('tasks', 'godina_id');
        $hasGamtaId = $db->columnExists('tasks', 'gamta_id');
        $hasGurmuId = $db->columnExists('tasks', 'gurmu_id');
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN due_date < NOW() AND status != 'completed' THEN 1 END) as overdue
                FROM tasks t
                WHERE 1=1";
        
        $params = [];
        
        // Apply hierarchy filtering
        if (!empty($userScope)) {
            $userId = $userScope['user_id'] ?? null;
            $scopeLevel = $userScope['level_scope'] ?? null;
            $scopeId = null;

            if ($scopeLevel === 'gurmu') {
                $scopeId = $userScope['gurmu_id'] ?? null;
            } elseif ($scopeLevel === 'gamta') {
                $scopeId = $userScope['gamta_id'] ?? null;
            } elseif ($scopeLevel === 'godina') {
                $scopeId = $userScope['godina_id'] ?? null;
            }

            if ($scopeLevel && $scopeId !== null && $userId && $hasScopeId) {
                $sql .= " AND ((t.level_scope = ? AND t.scope_id = ?) OR t.assigned_to = ?)";
                $params[] = $scopeLevel;
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel === 'gurmu' && $scopeId !== null && $userId && $hasGurmuId) {
                $sql .= " AND (t.gurmu_id = ? OR t.assigned_to = ?)";
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel === 'gamta' && $scopeId !== null && $userId && $hasGamtaId) {
                $sql .= " AND (t.gamta_id = ? OR t.assigned_to = ?)";
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel === 'godina' && $scopeId !== null && $userId && $hasGodinaId) {
                $sql .= " AND (t.godina_id = ? OR t.assigned_to = ?)";
                $params[] = $scopeId;
                $params[] = $userId;
            } elseif ($scopeLevel && $userId) {
                $sql .= " AND (t.level_scope = ? OR t.assigned_to = ? OR t.created_by = ?)";
                $params[] = $scopeLevel;
                $params[] = $userId;
                $params[] = $userId;
            } elseif ($userId) {
                $sql .= " AND (t.assigned_to = ? OR t.created_by = ?)";
                $params[] = $userId;
                $params[] = $userId;
            }
        }
        
        return $db->fetch($sql, $params) ?: [];
    }
}