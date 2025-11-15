<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\TaskService;
use App\Services\MeetingService;
use App\Services\DonationService;
use App\Services\UserService;
use App\Services\NotificationService;
use App\Services\AuditService;
use App\Utils\SecurityHelper;
use App\Utils\ValidationHelper;
use App\Utils\DateTimeHelper;

/**
 * API Controller
 * 
 * Handles comprehensive REST API endpoints for ABO-WBO Management System
 * with enterprise-grade features, authentication, rate limiting, and 
 * hierarchical data management.
 * 
 * @package App\Controllers
 * @version 1.0.0
 */
class APIController extends Controller
{
    protected TaskService $taskService;
    protected MeetingService $meetingService;
    protected DonationService $donationService;
    protected UserService $userService;
    protected NotificationService $notificationService;
    protected AuditService $auditService;
    
    protected array $rateLimits = [
        'default' => ['requests' => 100, 'window' => 3600], // 100 requests per hour
        'auth' => ['requests' => 10, 'window' => 900],      // 10 requests per 15 minutes
        'upload' => ['requests' => 20, 'window' => 3600],   // 20 uploads per hour
        'sensitive' => ['requests' => 50, 'window' => 3600]  // 50 sensitive operations per hour
    ];

    public function __construct()
    {
        parent::__construct();
        $this->taskService = new TaskService();
        $this->meetingService = new MeetingService();
        $this->donationService = new DonationService();
        $this->userService = new UserService();
        $this->notificationService = new NotificationService();
        $this->auditService = new AuditService();
        
        // Set JSON response headers
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
    }

    /**
     * API Authentication and Rate Limiting Middleware
     */
    private function authenticateAPI(): ?array
    {
        try {
            // Check rate limiting first
            if (!$this->checkRateLimit()) {
                $this->jsonResponse(['error' => 'Rate limit exceeded'], 429);
                return null;
            }
            
            // Get authentication token
            $token = $this->getAuthToken();
            if (!$token) {
                $this->jsonResponse(['error' => 'Authentication required'], 401);
                return null;
            }
            
            // Validate token
            $user = $this->validateApiToken($token);
            if (!$user) {
                $this->jsonResponse(['error' => 'Invalid or expired token'], 401);
                return null;
            }
            
            // Log API access
            $this->auditService->logActivity('api_access', [
                'user_id' => $user['id'],
                'endpoint' => $_SERVER['REQUEST_URI'],
                'method' => $_SERVER['REQUEST_METHOD'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return $user;
            
        } catch (\Exception $e) {
            error_log("API Authentication error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Authentication failed'], 500);
            return null;
        }
    }

    /**
     * GET /api/tasks - Get tasks with advanced filtering and pagination
     */
    public function getTasks(): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            // Parse query parameters
            $filters = [
                'status' => $_GET['status'] ?? null,
                'priority' => $_GET['priority'] ?? null,
                'scope' => $_GET['scope'] ?? null,
                'assigned_to' => $_GET['assigned_to'] ?? null,
                'created_by' => $_GET['created_by'] ?? null,
                'category' => $_GET['category'] ?? null,
                'due_date_from' => $_GET['due_date_from'] ?? null,
                'due_date_to' => $_GET['due_date_to'] ?? null,
                'search' => $_GET['search'] ?? null,
                'tags' => $_GET['tags'] ?? null,
                'organization_level' => $_GET['organization_level'] ?? null,
                'organization_id' => $_GET['organization_id'] ?? null,
                'parent_task_id' => $_GET['parent_task_id'] ?? null,
                'include_subtasks' => $_GET['include_subtasks'] ?? 'false'
            ];
            
            $pagination = [
                'page' => max(1, (int)($_GET['page'] ?? 1)),
                'limit' => min(100, max(1, (int)($_GET['limit'] ?? 20))),
                'sort_by' => $_GET['sort_by'] ?? 'created_at',
                'sort_order' => strtoupper($_GET['sort_order'] ?? 'DESC')
            ];
            
            // Validate sort parameters
            $allowedSortFields = ['id', 'title', 'created_at', 'updated_at', 'due_date', 'priority', 'status'];
            if (!in_array($pagination['sort_by'], $allowedSortFields)) {
                $pagination['sort_by'] = 'created_at';
            }
            
            if (!in_array($pagination['sort_order'], ['ASC', 'DESC'])) {
                $pagination['sort_order'] = 'DESC';
            }
            
            // Get tasks with user permissions
            $result = $this->taskService->getApiTasks($filters, $pagination, $user);
            
            $response = [
                'success' => true,
                'data' => $result['tasks'],
                'pagination' => [
                    'current_page' => $pagination['page'],
                    'per_page' => $pagination['limit'],
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / $pagination['limit']),
                    'has_next' => $pagination['page'] < ceil($result['total'] / $pagination['limit']),
                    'has_prev' => $pagination['page'] > 1
                ],
                'filters_applied' => array_filter($filters),
                'meta' => [
                    'user_permissions' => $this->userService->getUserPermissions($user['id'], 'tasks'),
                    'available_filters' => $this->getAvailableTaskFilters($user),
                    'timestamp' => date('c')
                ]
            ];
            
            $this->jsonResponse($response);
            
        } catch (\Exception $e) {
            error_log("API getTasks error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to retrieve tasks'], 500);
        }
    }

    /**
     * POST /api/tasks - Create new task with Global event support
     */
    public function createTask(): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            // Check permissions
            if (!$this->userService->hasPermission($user['id'], 'create_task')) {
                $this->jsonResponse(['error' => 'Insufficient permissions'], 403);
                return;
            }
            
            // Get and validate input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
                return;
            }
            
            // Validate required fields
            $requiredFields = ['title', 'description', 'scope'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    $this->jsonResponse(['error' => "Field '{$field}' is required"], 400);
                    return;
                }
            }
            
            // Prepare task data
            $taskData = [
                'title' => SecurityHelper::sanitizeInput($input['title']),
                'description' => SecurityHelper::sanitizeInput($input['description']),
                'scope' => $input['scope'],
                'category' => $input['category'] ?? 'administrative',
                'priority' => $input['priority'] ?? 'normal',
                'start_date' => $input['start_date'] ?? null,
                'due_date' => $input['due_date'] ?? null,
                'estimated_hours' => isset($input['estimated_hours']) ? (int)$input['estimated_hours'] : null,
                'tags' => $input['tags'] ?? [],
                'parent_task_id' => isset($input['parent_task_id']) ? (int)$input['parent_task_id'] : null,
                'organization_id' => $input['organization_id'] ?? $user['organization_id'],
                'metadata' => $input['metadata'] ?? []
            ];
            
            // Validate task data
            $validation = $this->taskService->validateTaskData($taskData, $user);
            if (!$validation['valid']) {
                $this->jsonResponse(['error' => $validation['message']], 400);
                return;
            }
            
            // Create task
            $taskId = $this->taskService->createApiTask($taskData, $user['id']);
            
            // Handle Global assignments if provided
            if ($taskData['scope'] === 'global' && isset($input['assignments'])) {
                $this->taskService->handleGlobalAssignments($taskId, $input['assignments'], $user['id']);
            }
            
            // Handle sub-tasks if provided
            if (isset($input['subtasks']) && is_array($input['subtasks'])) {
                $this->taskService->createSubTasks($taskId, $input['subtasks'], $user['id']);
            }
            
            // Get created task with full details
            $createdTask = $this->taskService->getTaskById($taskId);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $createdTask,
                'task_id' => $taskId
            ], 201);
            
        } catch (\Exception $e) {
            error_log("API createTask error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to create task'], 500);
        }
    }

    /**
     * GET /api/tasks/{id} - Get specific task with hierarchical details
     */
    public function getTask(int $taskId): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            $task = $this->taskService->getApiTaskById($taskId, $user);
            
            if (!$task) {
                $this->jsonResponse(['error' => 'Task not found'], 404);
                return;
            }
            
            // Check access permissions
            if (!$this->taskService->canUserAccessTask($taskId, $user['id'])) {
                $this->jsonResponse(['error' => 'Access denied'], 403);
                return;
            }
            
            // Get additional task details
            $taskDetails = [
                'task' => $task,
                'assignments' => $this->taskService->getTaskAssignments($taskId),
                'subtasks' => $this->taskService->getSubTasksHierarchy($taskId),
                'comments' => $this->taskService->getTaskComments($taskId),
                'attachments' => $this->taskService->getTaskAttachments($taskId),
                'timeline' => $this->taskService->getTaskTimeline($taskId),
                'dependencies' => $this->taskService->getTaskDependencies($taskId),
                'progress' => $this->taskService->calculateTaskProgress($taskId),
                'time_tracking' => $this->taskService->getTimeTrackingData($taskId)
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $taskDetails,
                'meta' => [
                    'can_edit' => $this->taskService->canUserEditTask($taskId, $user['id']),
                    'can_assign' => $this->taskService->canUserAssignTask($taskId, $user['id']),
                    'can_complete' => $this->taskService->canUserCompleteTask($taskId, $user['id'])
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("API getTask error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to retrieve task'], 500);
        }
    }

    /**
     * PUT /api/tasks/{id} - Update task with comprehensive validation
     */
    public function updateTask(int $taskId): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            // Check if task exists and user can edit
            if (!$this->taskService->canUserEditTask($taskId, $user['id'])) {
                $this->jsonResponse(['error' => 'Task not found or access denied'], 404);
                return;
            }
            
            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
                return;
            }
            
            // Get current task for comparison
            $currentTask = $this->taskService->getTaskById($taskId);
            
            // Prepare update data
            $updateData = array_intersect_key($input, array_flip([
                'title', 'description', 'priority', 'status', 'due_date', 
                'estimated_hours', 'actual_hours', 'completion_percentage',
                'tags', 'metadata'
            ]));
            
            // Sanitize input
            if (isset($updateData['title'])) {
                $updateData['title'] = SecurityHelper::sanitizeInput($updateData['title']);
            }
            if (isset($updateData['description'])) {
                $updateData['description'] = SecurityHelper::sanitizeInput($updateData['description']);
            }
            
            // Validate update data
            $validation = $this->taskService->validateTaskUpdate($updateData, $currentTask, $user);
            if (!$validation['valid']) {
                $this->jsonResponse(['error' => $validation['message']], 400);
                return;
            }
            
            // Update task
            $result = $this->taskService->updateApiTask($taskId, $updateData, $user['id']);
            
            if ($result['success']) {
                // Get updated task
                $updatedTask = $this->taskService->getTaskById($taskId);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Task updated successfully',
                    'data' => $updatedTask,
                    'changes' => $result['changes']
                ]);
            } else {
                $this->jsonResponse(['error' => $result['message']], 400);
            }
            
        } catch (\Exception $e) {
            error_log("API updateTask error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to update task'], 500);
        }
    }

    /**
     * POST /api/tasks/{id}/assign - Assign task to multiple users
     */
    public function assignTask(int $taskId): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            // Check permissions
            if (!$this->taskService->canUserAssignTask($taskId, $user['id'])) {
                $this->jsonResponse(['error' => 'Task not found or insufficient permissions'], 403);
                return;
            }
            
            // Get assignment data
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['assignments'])) {
                $this->jsonResponse(['error' => 'Assignment data required'], 400);
                return;
            }
            
            $assignments = $input['assignments'];
            if (!is_array($assignments) || empty($assignments)) {
                $this->jsonResponse(['error' => 'At least one assignment required'], 400);
                return;
            }
            
            // Validate assignments
            foreach ($assignments as $assignment) {
                if (empty($assignment['user_id'])) {
                    $this->jsonResponse(['error' => 'User ID required for each assignment'], 400);
                    return;
                }
                
                // Check if user exists and can be assigned
                if (!$this->userService->canAssignToUser($assignment['user_id'], $user)) {
                    $this->jsonResponse(['error' => 'Invalid user for assignment'], 400);
                    return;
                }
            }
            
            // Process assignments
            $result = $this->taskService->assignApiTask($taskId, $assignments, $user['id']);
            
            if ($result['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Task assigned successfully',
                    'assignments' => $result['assignments']
                ]);
            } else {
                $this->jsonResponse(['error' => $result['message']], 400);
            }
            
        } catch (\Exception $e) {
            error_log("API assignTask error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to assign task'], 500);
        }
    }

    /**
     * POST /api/tasks/{id}/complete - Mark task as complete
     */
    public function completeTask(int $taskId): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            // Check permissions
            if (!$this->taskService->canUserCompleteTask($taskId, $user['id'])) {
                $this->jsonResponse(['error' => 'Task not found or insufficient permissions'], 403);
                return;
            }
            
            // Get completion data
            $input = json_decode(file_get_contents('php://input'), true);
            $completionData = [
                'completion_notes' => $input['completion_notes'] ?? null,
                'actual_hours' => isset($input['actual_hours']) ? (int)$input['actual_hours'] : null,
                'completion_percentage' => 100,
                'completed_by' => $user['id'],
                'completed_at' => date('Y-m-d H:i:s')
            ];
            
            // Complete task
            $result = $this->taskService->completeApiTask($taskId, $completionData, $user['id']);
            
            if ($result['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Task completed successfully',
                    'data' => $result['task']
                ]);
            } else {
                $this->jsonResponse(['error' => $result['message']], 400);
            }
            
        } catch (\Exception $e) {
            error_log("API completeTask error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to complete task'], 500);
        }
    }

    /**
     * GET /api/meetings - Get meetings with filtering
     */
    public function getMeetings(): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            $filters = [
                'status' => $_GET['status'] ?? null,
                'type' => $_GET['type'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'organizer_id' => $_GET['organizer_id'] ?? null,
                'search' => $_GET['search'] ?? null
            ];
            
            $pagination = [
                'page' => max(1, (int)($_GET['page'] ?? 1)),
                'limit' => min(50, max(1, (int)($_GET['limit'] ?? 20)))
            ];
            
            $result = $this->meetingService->getApiMeetings($filters, $pagination, $user);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $result['meetings'],
                'pagination' => $result['pagination']
            ]);
            
        } catch (\Exception $e) {
            error_log("API getMeetings error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to retrieve meetings'], 500);
        }
    }

    /**
     * GET /api/donations - Get donations with filtering
     */
    public function getDonations(): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            // Check permissions
            if (!$this->userService->hasPermission($user['id'], 'view_donations')) {
                $this->jsonResponse(['error' => 'Insufficient permissions'], 403);
                return;
            }
            
            $filters = [
                'status' => $_GET['status'] ?? null,
                'payment_method' => $_GET['payment_method'] ?? null,
                'amount_from' => $_GET['amount_from'] ?? null,
                'amount_to' => $_GET['amount_to'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'campaign_id' => $_GET['campaign_id'] ?? null
            ];
            
            $pagination = [
                'page' => max(1, (int)($_GET['page'] ?? 1)),
                'limit' => min(100, max(1, (int)($_GET['limit'] ?? 20)))
            ];
            
            $result = $this->donationService->getApiDonations($filters, $pagination, $user);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $result['donations'],
                'pagination' => $result['pagination'],
                'summary' => $result['summary']
            ]);
            
        } catch (\Exception $e) {
            error_log("API getDonations error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to retrieve donations'], 500);
        }
    }

    /**
     * GET /api/dashboard/stats - Get dashboard statistics
     */
    public function getDashboardStats(): void
    {
        $user = $this->authenticateAPI();
        if (!$user) return;
        
        try {
            $userLevel = $user['organization_level'];
            $organizationId = $user['organization_id'];
            
            $stats = [
                'tasks' => $this->taskService->getUserTaskStats($user['id'], $userLevel, $organizationId),
                'meetings' => $this->meetingService->getUserMeetingStats($user['id'], $userLevel, $organizationId),
                'notifications' => $this->notificationService->getUserNotificationStats($user['id']),
                'recent_activities' => $this->auditService->getRecentActivities($user['id'], 10)
            ];
            
            // Add donation stats if user has permission
            if ($this->userService->hasPermission($user['id'], 'view_donations')) {
                $stats['donations'] = $this->donationService->getUserDonationStats($user['id'], $userLevel, $organizationId);
            }
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats,
                'meta' => [
                    'user_level' => $userLevel,
                    'organization_id' => $organizationId,
                    'timestamp' => date('c')
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("API getDashboardStats error: " . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to retrieve dashboard statistics'], 500);
        }
    }

    /**
     * Check API rate limits
     */
    private function checkRateLimit(string $type = 'default'): bool
    {
        try {
            $clientId = $this->getClientIdentifier();
            $limit = $this->rateLimits[$type] ?? $this->rateLimits['default'];
            
            return $this->auditService->checkRateLimit($clientId, $type, $limit['requests'], $limit['window']);
            
        } catch (\Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return true; // Allow on error to prevent blocking
        }
    }

    /**
     * Get client identifier for rate limiting
     */
    private function getClientIdentifier(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        return hash('sha256', $ip . '|' . $userAgent);
    }

    /**
     * Get authentication token from request
     */
    private function getAuthToken(): ?string
    {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        // Check query parameter
        return $_GET['token'] ?? null;
    }

    /**
     * Validate API token
     */
    private function validateApiToken(string $token): ?array
    {
        try {
            return $this->userService->validateApiToken($token);
        } catch (\Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get available task filters for user
     */
    private function getAvailableTaskFilters(array $user): array
    {
        return [
            'status' => ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'],
            'priority' => ['low', 'normal', 'high', 'urgent', 'critical'],
            'scope' => $this->taskService->getAvailableScopes($user),
            'category' => $this->taskService->getTaskCategories()
        ];
    }

    /**
     * Enhanced JSON response with security headers
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        
        // Add API response headers
        header('X-API-Version: 1.0');
        header('X-Rate-Limit-Remaining: ' . $this->getRemainingRateLimit());
        
        // Add timestamp to response
        if ($statusCode >= 200 && $statusCode < 300) {
            $data['timestamp'] = date('c');
        }
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Get remaining rate limit for current client
     */
    private function getRemainingRateLimit(): int
    {
        try {
            $clientId = $this->getClientIdentifier();
            return $this->auditService->getRemainingRateLimit($clientId, 'default');
        } catch (\Exception $e) {
            return 0;
        }
    }
}