<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Position;
use App\Models\UserAssignment;
use App\Models\User;
use App\Models\Godina;
use App\Models\Gamta;
use App\Models\Gurmu;
use Exception;

/**
 * Position Controller
 * Manages organizational positions and executive assignments
 * ABO-WBO Management System - Executive Management
 */
class PositionController extends Controller
{
    private Position $positionModel;
    private UserAssignment $assignmentModel;
    private User $userModel;
    private Godina $godinaModel;
    private Gamta $gamtaModel;
    private Gurmu $gurmuModel;
    
    public function __construct()
    {
        $this->positionModel = new Position();
        $this->assignmentModel = new UserAssignment();
        $this->userModel = new User();
        $this->godinaModel = new Godina();
        $this->gamtaModel = new Gamta();
        $this->gurmuModel = new Gurmu();
    }
    
    /**
     * Display positions dashboard
     */
    public function index(): void
    {
        try {
            // Get positions with statistics
            $positions = $this->positionModel->getExecutivePositions();
            $stats = $this->positionModel->getPositionStats();
            $assignmentStats = $this->assignmentModel->getAssignmentStats();
            
            // Get pending approvals
            $pendingApprovals = $this->assignmentModel->getPendingApprovals();
            
            // Get positions expiring soon
            $expiringSoon = $this->assignmentModel->getExpiringSoon(30);
            
            $data = [
                'title' => 'Position Management',
                'positions' => $positions,
                'stats' => $stats,
                'assignmentStats' => $assignmentStats,
                'pendingApprovals' => $pendingApprovals,
                'expiringSoon' => $expiringSoon
            ];
            
            $this->render('positions/index', $data);
            
        } catch (Exception $e) {
            error_log("Error in PositionController::index: " . $e->getMessage());
            $this->redirectWithError('/dashboard', 'Error loading positions dashboard');
        }
    }
    
    /**
     * Show position details
     */
    public function show(): void
    {
        $id = (int)$this->getRouteParam('id');
        
        try {
            $position = $this->positionModel->findWithPermissions($id);
            if (!$position) {
                $this->redirectWithError('/positions', 'Position not found');
                return;
            }
            
            // Get current assignments for this position
            $assignments = $this->assignmentModel->getWithDetails(['position_id' => $id]);
            
            // Get assignment history
            $assignmentHistory = $this->assignmentModel->getWithDetails([
                'position_id' => $id
            ]);
            
            $data = [
                'title' => $position['name_en'] . ' Position',
                'position' => $position,
                'assignments' => $assignments,
                'assignmentHistory' => $assignmentHistory
            ];
            
            $this->render('positions/show', $data);
            
        } catch (Exception $e) {
            error_log("Error in PositionController::show: " . $e->getMessage());
            $this->redirectWithError('/positions', 'Error loading position details');
        }
    }
    
    /**
     * Show position creation form
     */
    public function create(): void
    {
        $data = [
            'title' => 'Create New Position',
            'levels' => ['global', 'godina', 'gamta', 'gurmu', 'all'],
            'electionCycles' => ['elected', 'appointed', 'volunteer']
        ];
        
        $this->render('positions/create', $data);
    }
    
    /**
     * Store new position
     */
    public function store(): void
    {
        try {
            $data = [
                'key_name' => trim($_POST['key_name'] ?? ''),
                'name_en' => trim($_POST['name_en'] ?? ''),
                'name_om' => trim($_POST['name_om'] ?? ''),
                'description_en' => trim($_POST['description_en'] ?? ''),
                'description_om' => trim($_POST['description_om'] ?? ''),
                'level_scope' => $_POST['level_scope'] ?? '',
                'permissions' => $_POST['permissions'] ?? [],
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'term_length' => (int)($_POST['term_length'] ?? 24),
                'election_cycle' => $_POST['election_cycle'] ?? 'elected'
            ];
            
            // Validation
            if (empty($data['key_name']) || empty($data['name_en']) || empty($data['level_scope'])) {
                throw new Exception('Required fields are missing');
            }
            
            $positionId = $this->positionModel->createPosition($data);
            
            // Log activity
            $this->positionModel->logActivity($positionId, 'position_created', $data, $this->getCurrentUserId());
            
            $this->redirectWithSuccess('/positions/' . $positionId, 'Position created successfully');
            
        } catch (Exception $e) {
            error_log("Error creating position: " . $e->getMessage());
            $this->redirectWithError('/positions/create', $e->getMessage());
        }
    }
    
    /**
     * Show position edit form
     */
    public function edit(): void
    {
        $id = (int)$this->getRouteParam('id');
        
        try {
            $position = $this->positionModel->find($id);
            if (!$position) {
                $this->redirectWithError('/positions', 'Position not found');
                return;
            }
            
            $data = [
                'title' => 'Edit Position',
                'position' => $position,
                'levels' => ['global', 'godina', 'gamta', 'gurmu', 'all'],
                'electionCycles' => ['elected', 'appointed', 'volunteer']
            ];
            
            $this->render('positions/edit', $data);
            
        } catch (Exception $e) {
            error_log("Error in PositionController::edit: " . $e->getMessage());
            $this->redirectWithError('/positions', 'Error loading position for editing');
        }
    }
    
    /**
     * Update position
     */
    public function update(): void
    {
        $id = (int)$this->getRouteParam('id');
        
        try {
            $data = [
                'key_name' => trim($_POST['key_name'] ?? ''),
                'name_en' => trim($_POST['name_en'] ?? ''),
                'name_om' => trim($_POST['name_om'] ?? ''),
                'description_en' => trim($_POST['description_en'] ?? ''),
                'description_om' => trim($_POST['description_om'] ?? ''),
                'level_scope' => $_POST['level_scope'] ?? '',
                'permissions' => $_POST['permissions'] ?? [],
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'term_length' => (int)($_POST['term_length'] ?? 24),
                'election_cycle' => $_POST['election_cycle'] ?? 'elected',
                'status' => $_POST['status'] ?? 'active'
            ];
            
            $success = $this->positionModel->updatePosition($id, $data);
            
            if ($success) {
                $this->positionModel->logActivity($id, 'position_updated', $data, $this->getCurrentUserId());
                $this->redirectWithSuccess('/positions/' . $id, 'Position updated successfully');
            } else {
                throw new Exception('Failed to update position');
            }
            
        } catch (Exception $e) {
            error_log("Error updating position: " . $e->getMessage());
            $this->redirectWithError('/positions/' . $id . '/edit', $e->getMessage());
        }
    }
    
    /**
     * Delete position (soft delete)
     */
    public function destroy(): void
    {
        $id = (int)$this->getRouteParam('id');
        
        try {
            $success = $this->positionModel->softDelete($id);
            
            if ($success) {
                $this->positionModel->logActivity($id, 'position_deleted', [], $this->getCurrentUserId());
                $this->jsonResponse(['success' => true, 'message' => 'Position deleted successfully']);
            } else {
                throw new Exception('Failed to delete position');
            }
            
        } catch (Exception $e) {
            error_log("Error deleting position: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Show assignment form
     */
    public function assign(): void
    {
        $positionId = (int)$this->getRouteParam('id');
        $levelScope = $_GET['level_scope'] ?? '';
        $unitId = (int)($_GET['unit_id'] ?? 0);
        
        try {
            $position = $this->positionModel->find($positionId);
            if (!$position) {
                $this->redirectWithError('/positions', 'Position not found');
                return;
            }
            
            // Get organizational unit details
            $unit = $this->getOrganizationalUnit($levelScope, $unitId);
            
            // Get available users (members without current assignments)
            $availableUsers = $this->getAvailableUsers($levelScope, $unitId);
            
            $data = [
                'title' => 'Assign Position',
                'position' => $position,
                'unit' => $unit,
                'levelScope' => $levelScope,
                'unitId' => $unitId,
                'availableUsers' => $availableUsers
            ];
            
            $this->render('positions/assign', $data);
            
        } catch (Exception $e) {
            error_log("Error in PositionController::assign: " . $e->getMessage());
            $this->redirectWithError('/positions', 'Error loading assignment form');
        }
    }
    
    /**
     * Process position assignment
     */
    public function processAssignment(): void
    {
        try {
            $data = [
                'user_id' => (int)($_POST['user_id'] ?? 0),
                'position_id' => (int)($_POST['position_id'] ?? 0),
                'organizational_unit_id' => (int)($_POST['organizational_unit_id'] ?? 0),
                'level_scope' => $_POST['level_scope'] ?? '',
                'assigned_by' => $this->getCurrentUserId(),
                'appointment_type' => $_POST['appointment_type'] ?? 'appointment',
                'term_start' => $_POST['term_start'] ?? date('Y-m-d'),
                'term_end' => $_POST['term_end'] ?? null,
                'notes' => trim($_POST['notes'] ?? '')
            ];
            
            // Validation
            if (!$data['user_id'] || !$data['position_id'] || !$data['organizational_unit_id'] || !$data['level_scope']) {
                throw new Exception('Required fields are missing');
            }
            
            $assignmentId = $this->assignmentModel->assignUserToPosition($data);
            
            $this->redirectWithSuccess('/positions/assignments/' . $assignmentId, 'Position assignment created and pending approval');
            
        } catch (Exception $e) {
            error_log("Error processing assignment: " . $e->getMessage());
            $this->redirectWithError('/positions', $e->getMessage());
        }
    }
    
    /**
     * Show assignments management
     */
    public function assignments(): void
    {
        $filters = [
            'level_scope' => $_GET['level_scope'] ?? '',
            'status' => $_GET['status'] ?? '',
            'approval_status' => $_GET['approval_status'] ?? ''
        ];
        
        try {
            $assignments = $this->assignmentModel->getWithDetails($filters);
            $pendingApprovals = $this->assignmentModel->getPendingApprovals();
            $expiringSoon = $this->assignmentModel->getExpiringSoon(30);
            
            $data = [
                'title' => 'Position Assignments',
                'assignments' => $assignments,
                'pendingApprovals' => $pendingApprovals,
                'expiringSoon' => $expiringSoon,
                'filters' => $filters
            ];
            
            $this->render('positions/assignments', $data);
            
        } catch (Exception $e) {
            error_log("Error in PositionController::assignments: " . $e->getMessage());
            $this->redirectWithError('/positions', 'Error loading assignments');
        }
    }
    
    /**
     * Approve assignment
     */
    public function approveAssignment(): void
    {
        $assignmentId = (int)$this->getRouteParam('id');
        
        try {
            // Check if current user can approve this assignment
            if (!$this->assignmentModel->canUserApprove($this->getCurrentUserId(), $assignmentId)) {
                $this->jsonResponse(['success' => false, 'message' => 'Insufficient permissions to approve this assignment'], 403);
                return;
            }
            
            $notes = trim($_POST['approval_notes'] ?? '');
            $success = $this->assignmentModel->approveAssignment($assignmentId, $this->getCurrentUserId(), $notes);
            
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Assignment approved successfully']);
            } else {
                throw new Exception('Failed to approve assignment');
            }
            
        } catch (Exception $e) {
            error_log("Error approving assignment: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Reject assignment
     */
    public function rejectAssignment(): void
    {
        $assignmentId = (int)$this->getRouteParam('id');
        
        try {
            // Check if current user can approve/reject this assignment
            if (!$this->assignmentModel->canUserApprove($this->getCurrentUserId(), $assignmentId)) {
                $this->jsonResponse(['success' => false, 'message' => 'Insufficient permissions to reject this assignment'], 403);
                return;
            }
            
            $reason = trim($_POST['rejection_reason'] ?? '');
            if (empty($reason)) {
                throw new Exception('Rejection reason is required');
            }
            
            $success = $this->assignmentModel->rejectAssignment($assignmentId, $this->getCurrentUserId(), $reason);
            
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Assignment rejected']);
            } else {
                throw new Exception('Failed to reject assignment');
            }
            
        } catch (Exception $e) {
            error_log("Error rejecting assignment: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * End assignment
     */
    public function endAssignment(): void
    {
        $assignmentId = (int)$this->getRouteParam('id');
        
        try {
            $endReason = $_POST['end_reason'] ?? '';
            $notes = trim($_POST['end_notes'] ?? '');
            
            if (empty($endReason)) {
                throw new Exception('End reason is required');
            }
            
            $success = $this->assignmentModel->endAssignment($assignmentId, $endReason, $this->getCurrentUserId(), $notes);
            
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Assignment ended successfully']);
            } else {
                throw new Exception('Failed to end assignment');
            }
            
        } catch (Exception $e) {
            error_log("Error ending assignment: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Get organizational unit details
     */
    private function getOrganizationalUnit(string $levelScope, int $unitId): ?array
    {
        switch ($levelScope) {
            case 'global':
                return ['id' => 0, 'name' => 'Global Organization', 'type' => 'global'];
            case 'godina':
                return $this->godinaModel->find($unitId);
            case 'gamta':
                return $this->gamtaModel->find($unitId);
            case 'gurmu':
                return $this->gurmuModel->find($unitId);
            default:
                return null;
        }
    }
    
    /**
     * Get available users for assignment
     */
    private function getAvailableUsers(string $levelScope, int $unitId): array
    {
        // Get users without current active assignments
        $query = "
            SELECT u.id, u.first_name, u.last_name, u.email
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            WHERE ua.id IS NULL
            AND u.status = 'active'
        ";
        
        // Add level-specific filters
        switch ($levelScope) {
            case 'gurmu':
                $query .= " AND u.gurmu_id = ?";
                return $this->db->fetchAll($query, [$unitId]);
                
            case 'gamta':
                $query .= " AND u.gurmu_id IN (SELECT id FROM gurmus WHERE gamta_id = ?)";
                return $this->db->fetchAll($query, [$unitId]);
                
            case 'godina':
                $query .= " AND u.gurmu_id IN (
                    SELECT gur.id FROM gurmus gur
                    JOIN gamtas gam ON gur.gamta_id = gam.id
                    WHERE gam.godina_id = ?
                )";
                return $this->db->fetchAll($query, [$unitId]);
                
            case 'global':
                return $this->db->fetchAll($query);
                
            default:
                return [];
        }
    }
    
    /**
     * Get positions by level API endpoint
     */
    public function getPositionsByLevel(): void
    {
        $levelScope = $_GET['level_scope'] ?? '';
        
        try {
            $positions = $this->positionModel->getByLevelScope($levelScope);
            $this->jsonResponse(['success' => true, 'data' => $positions]);
            
        } catch (Exception $e) {
            error_log("Error getting positions by level: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to load positions'], 500);
        }
    }
}