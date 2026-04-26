<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Responsibility;
use App\Models\ResponsibilityAssignment;
use App\Models\Position;
use App\Models\UserAssignment;
use App\Models\User;
use Exception;

/**
 * ResponsibilityController
 * Handles responsibility and assignment management
 * ABO-WBO Management System - Shared Responsibilities & Tasks Management
 */
class ResponsibilityController extends Controller
{
    protected $responsibilityModel;
    protected $assignmentModel;
    protected $positionModel;
    protected $userAssignmentModel;
    protected $userModel;
    
    public function __construct()
    {
        $this->responsibilityModel = new Responsibility();
        $this->assignmentModel = new ResponsibilityAssignment();
        $this->positionModel = new Position();
        $this->userAssignmentModel = new UserAssignment();
        $this->userModel = new User();
    }
    
    /**
     * Display responsibilities dashboard
     */
    public function index(): void
    {
        try {
            // Get responsibility statistics
            $stats = $this->responsibilityModel->getResponsibilityStats();
            
            // Get shared responsibilities (5 core areas)
            $sharedResponsibilities = $this->responsibilityModel->getSharedResponsibilities();
            
            // Get all positions with their specific responsibilities
            $positions = $this->positionModel->getExecutivePositions();
            $positionResponsibilities = [];
            
            foreach ($positions as $position) {
                $positionResponsibilities[$position['key_name']] = [
                    'position' => $position,
                    'individual' => $this->responsibilityModel->getPositionResponsibilities($position['key_name']),
                    'shared' => $sharedResponsibilities
                ];
            }
            
            // Get recent assignments
            $recentAssignments = $this->assignmentModel->getAssignmentsWithFilters([
                'status' => ['assigned', 'in_progress'],
                'limit' => 10
            ]);
            
            // Get overdue assignments
            $overdueAssignments = $this->assignmentModel->getOverdueAssignments(['limit' => 5]);
            
            echo $this->render('responsibilities/index', [
                'title' => 'Shared Responsibilities & Tasks Management',
                'section' => 'responsibilities',
                'stats' => $stats,
                'shared_responsibilities' => $sharedResponsibilities,
                'position_responsibilities' => $positionResponsibilities,
                'recent_assignments' => $recentAssignments,
                'overdue_assignments' => $overdueAssignments
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Failed to load responsibilities dashboard');
        }
    }
    
    /**
     * View specific responsibility details
     */
    public function view($responsibilityId = null): void
    {
        if (!$responsibilityId) {
            $this->redirectWithMessage('/responsibilities', 'Responsibility ID is required', 'error');
            return;
        }
        
        try {
            $responsibility = $this->responsibilityModel->find($responsibilityId);
            
            if (!$responsibility) {
                $this->redirectWithMessage('/responsibilities', 'Responsibility not found', 'error');
                return;
            }
            
            // Get assignments for this responsibility
            $assignments = $this->assignmentModel->getAssignmentsWithFilters([
                'responsibility_id' => $responsibilityId
            ]);
            
            // Get assignment statistics
            $assignmentStats = $this->assignmentModel->getAssignmentStats([
                'responsibility_id' => $responsibilityId
            ]);
            
            echo $this->render('responsibilities/view', [
                'title' => 'Responsibility Details',
                'section' => 'responsibilities',
                'responsibility' => $responsibility,
                'assignments' => $assignments,
                'assignment_stats' => $assignmentStats
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Failed to load responsibility details');
        }
    }
    
    /**
     * Assign responsibilities to users/positions
     */
    public function assign(): void
    {
        if ($this->isPost()) {
            $this->processAssignment();
            return;
        }
        
        try {
            // Get available positions
            $positions = $this->positionModel->getExecutivePositions();
            
            // Get all responsibilities
            $responsibilities = $this->responsibilityModel->getWithFilters();
            
            // Get organizational units for assignment context
            $organizationalUnits = $this->getOrganizationalUnits();
            
            echo $this->render('responsibilities/assign', [
                'title' => 'Assign Responsibilities',
                'section' => 'responsibilities',
                'positions' => $positions,
                'responsibilities' => $responsibilities,
                'organizational_units' => $organizationalUnits
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Failed to load assignment page');
        }
    }
    
    /**
     * Process responsibility assignment
     */
    private function processAssignment(): void
    {
        try {
            $data = $this->validateAssignmentData($_POST);
            
            if ($data['assignment_type'] === 'bulk_position') {
                // Bulk assign all responsibilities to position holders
                $results = $this->bulkAssignToPosition($data);
                $this->handleBulkAssignmentResults($results, $data);
            } else {
                // Individual assignment
                $assignmentId = $this->assignmentModel->assignResponsibility($data);
                $this->redirectWithMessage('/responsibilities/assignments/' . $assignmentId, 'Responsibility assigned successfully', 'success');
            }
            
        } catch (Exception $e) {
            $this->redirectWithMessage('/responsibilities/assign', $e->getMessage(), 'error');
        }
    }
    
    /**
     * Bulk assign responsibilities to position
     */
    private function bulkAssignToPosition(array $data): array
    {
        $responsibilityIds = [];
        
        if ($data['assign_shared']) {
            // Get shared responsibilities
            $sharedResponsibilities = $this->responsibilityModel->getSharedResponsibilities($data['level_scope']);
            $responsibilityIds = array_merge($responsibilityIds, array_column($sharedResponsibilities, 'id'));
        }
        
        if ($data['assign_individual']) {
            // Get position-specific responsibilities
            $position = $this->positionModel->find($data['position_id']);
            if ($position) {
                $individualResponsibilities = $this->responsibilityModel->getPositionResponsibilities(
                    $position['key_name'], 
                    $data['level_scope']
                );
                $responsibilityIds = array_merge($responsibilityIds, array_column($individualResponsibilities, 'id'));
            }
        }
        
        if (empty($responsibilityIds)) {
            throw new Exception('No responsibilities selected for assignment');
        }
        
        return $this->assignmentModel->bulkAssignToPositionHolders(
            $data['position_id'],
            $responsibilityIds,
            [
                'priority' => $data['priority'] ?? ResponsibilityAssignment::PRIORITY_MEDIUM,
                'due_date' => $data['due_date'] ?? null,
                'assigned_by' => $this->getCurrentUserId()
            ]
        );
    }
    
    /**
     * View assignments
     */
    public function assignments(): void
    {
        $assignmentId = $this->getParam('id');
        
        if ($assignmentId) {
            $this->viewAssignment($assignmentId);
            return;
        }
        
        try {
            // Get filter parameters
            $filters = $this->getAssignmentFilters();
            
            // Get assignments
            $assignments = $this->assignmentModel->getAssignmentsWithFilters($filters);
            
            // Get filter options
            $filterOptions = $this->getAssignmentFilterOptions();
            
            echo $this->render('responsibilities/assignments', [
                'title' => 'Responsibility Assignments',
                'section' => 'responsibilities',
                'assignments' => $assignments,
                'filters' => $filters,
                'filter_options' => $filterOptions
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Failed to load assignments');
        }
    }
    
    /**
     * View specific assignment
     */
    private function viewAssignment(int $assignmentId): void
    {
        try {
            $assignments = $this->assignmentModel->getAssignmentsWithFilters(['id' => $assignmentId]);
            
            if (empty($assignments)) {
                $this->redirectWithMessage('/responsibilities/assignments', 'Assignment not found', 'error');
                return;
            }
            
            $assignment = $assignments[0];
            
            // Get assignment history/activities
            $activities = $this->getAssignmentActivities($assignmentId);
            
            echo $this->render('responsibilities/assignment-view', [
                'title' => 'Assignment Details',
                'section' => 'responsibilities',
                'assignment' => $assignment,
                'activities' => $activities
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Failed to load assignment details');
        }
    }
    
    /**
     * Update assignment progress
     */
    public function updateProgress(): void
    {
        if (!$this->isPost()) {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }
        
        try {
            $assignmentId = $this->getParam('id');
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$assignmentId || !isset($data['completion_percentage'])) {
                $this->jsonResponse(['error' => 'Missing required parameters'], 400);
                return;
            }
            
            $updated = $this->assignmentModel->updateProgress(
                $assignmentId,
                $data['completion_percentage'],
                $data['notes'] ?? ''
            );
            
            if ($updated) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Progress updated successfully'
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to update progress'], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Complete assignment
     */
    public function complete(): void
    {
        if (!$this->isPost()) {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }
        
        try {
            $assignmentId = $this->getParam('id');
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$assignmentId) {
                $this->jsonResponse(['error' => 'Assignment ID is required'], 400);
                return;
            }
            
            $completed = $this->assignmentModel->completeAssignment(
                $assignmentId,
                $data['completion_notes'] ?? '',
                $this->getCurrentUserId()
            );
            
            if ($completed) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Assignment completed successfully'
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to complete assignment'], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Initialize default responsibilities
     */
    public function initialize(): void
    {
        try {
            $created = $this->responsibilityModel->initializeDefaultResponsibilities();
            
            $message = count($created) > 0 
                ? 'Successfully initialized ' . count($created) . ' responsibilities'
                : 'All responsibilities already exist';
            
            $this->redirectWithMessage('/responsibilities', $message, 'success');
            
        } catch (Exception $e) {
            $this->redirectWithMessage('/responsibilities', 'Failed to initialize responsibilities: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Get dashboard data for API
     */
    public function dashboard(): void
    {
        try {
            $data = [
                'stats' => $this->responsibilityModel->getResponsibilityStats(),
                'assignment_stats' => $this->assignmentModel->getAssignmentStats(),
                'overdue_count' => count($this->assignmentModel->getOverdueAssignments()),
                'recent_assignments' => $this->assignmentModel->getAssignmentsWithFilters([
                    'status' => ['assigned', 'in_progress'],
                    'limit' => 5
                ])
            ];
            
            $this->json($data);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Validate assignment data
     */
    private function validateAssignmentData(array $data): array
    {
        $required = ['assignment_type'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        if ($data['assignment_type'] === 'bulk_position') {
            if (empty($data['position_id']) || empty($data['level_scope'])) {
                throw new Exception('Position and level scope are required for bulk assignment');
            }
            
            if (empty($data['assign_shared']) && empty($data['assign_individual'])) {
                throw new Exception('At least one responsibility type must be selected');
            }
        } else {
            $individualRequired = ['user_id', 'responsibility_id', 'position_id', 'organizational_unit_id'];
            foreach ($individualRequired as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '{$field}' is required for individual assignment");
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Get assignment filters from request
     */
    private function getAssignmentFilters(): array
    {
        return [
            'user_id' => $this->getParam('user_id'),
            'position_id' => $this->getParam('position_id'),
            'status' => $this->getParam('status'),
            'priority' => $this->getParam('priority'),
            'responsibility_type' => $this->getParam('responsibility_type'),
            'overdue' => $this->getParam('overdue') === '1'
        ];
    }
    
    /**
     * Get filter options for assignments
     */
    private function getAssignmentFilterOptions(): array
    {
        return [
            'statuses' => [
                ResponsibilityAssignment::STATUS_PENDING => 'Pending',
                ResponsibilityAssignment::STATUS_ASSIGNED => 'Assigned',
                ResponsibilityAssignment::STATUS_IN_PROGRESS => 'In Progress',
                ResponsibilityAssignment::STATUS_COMPLETED => 'Completed',
                ResponsibilityAssignment::STATUS_OVERDUE => 'Overdue',
                ResponsibilityAssignment::STATUS_SUSPENDED => 'Suspended',
                ResponsibilityAssignment::STATUS_CANCELLED => 'Cancelled'
            ],
            'priorities' => [
                ResponsibilityAssignment::PRIORITY_LOW => 'Low',
                ResponsibilityAssignment::PRIORITY_MEDIUM => 'Medium',
                ResponsibilityAssignment::PRIORITY_HIGH => 'High',
                ResponsibilityAssignment::PRIORITY_CRITICAL => 'Critical'
            ],
            'responsibility_types' => [
                'shared' => 'Shared Responsibilities (5 Core Areas)',
                'individual' => 'Individual Position Responsibilities'
            ]
        ];
    }
    
    /**
     * Get organizational units for assignment context
     */
    private function getOrganizationalUnits(): array
    {
        // This would typically fetch from hierarchy models
        // For now, return structure based on current implementation
        return [
            'global' => ['name' => 'Global Level', 'type' => 'global'],
            'godina' => $this->getHierarchyUnits('godina'),
            'gamta' => $this->getHierarchyUnits('gamta'),
            'gurmu' => $this->getHierarchyUnits('gurmu')
        ];
    }
    
    /**
     * Get hierarchy units by type
     */
    private function getHierarchyUnits(string $type): array
    {
        try {
            $modelClass = "\\App\\Models\\" . ucfirst($type);
            if (class_exists($modelClass)) {
                $model = new $modelClass();
                return $model->getAll();
            }
        } catch (Exception $e) {
            error_log("Failed to get {$type} units: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Handle bulk assignment results
     */
    private function handleBulkAssignmentResults(array $results, array $data): void
    {
        $successful = array_filter($results, fn($r) => $r['success']);
        $failed = array_filter($results, fn($r) => !$r['success']);
        
        $message = sprintf(
            'Bulk assignment completed: %d successful, %d failed',
            count($successful),
            count($failed)
        );
        
        $type = count($failed) > 0 ? 'warning' : 'success';
        
        $this->redirectWithMessage('/responsibilities/assignments', $message, $type);
    }
    
    /**
     * Get assignment activities/history
     */
    private function getAssignmentActivities(int $assignmentId): array
    {
        // This would fetch from activity_logs table
        // For now, return empty array
        return [];
    }
    
    /**
     * Get current user ID
     */
    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }
    
    /**
     * Handle errors consistently
     */
    private function handleError(Exception $e, string $userMessage): void
    {
        error_log($userMessage . ': ' . $e->getMessage());
        $this->redirectWithMessage('/responsibilities', $userMessage, 'error');
    }
}