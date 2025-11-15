<?php
namespace App\Services;

use App\Utils\Database;
use Exception;

/**
 * Approval Workflow Service
 * 
 * Manages hierarchical approval workflows for registration requests
 * 
 * Features:
 * - Multi-step approval chains
 * - Position-based approval authority
 * - Automatic escalation
 * - Approval history tracking
 * - Bulk approval operations
 */
class ApprovalWorkflowService
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Start approval workflow for registration
     */
    public function startApprovalWorkflow(int $registrationId, array $approvalChain = null): int
    {
        try {
            // Get registration details
            $registration = $this->getRegistrationById($registrationId);
            if (!$registration) {
                throw new Exception("Registration not found");
            }
            
            // Generate approval chain if not provided
            if (!$approvalChain) {
                $approvalChain = $this->generateApprovalChain($registration);
            }
            
            // Create workflow record
            $workflowId = $this->createWorkflowRecord($registrationId, $approvalChain);
            
            // Create approval steps
            $this->createApprovalSteps($workflowId, $approvalChain);
            
            // Notify first approver
            $this->notifyNextApprover($workflowId);
            
            return $workflowId;
            
        } catch (Exception $e) {
            error_log("Failed to start approval workflow: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate approval chain based on hierarchy and position
     */
    protected function generateApprovalChain(array $registration): array
    {
        $approvalChain = [];
        $targetLevel = $registration['target_hierarchy_level'] ?? 'global';
        $targetId = $registration['target_hierarchy_id'] ?? null;
        
        switch ($targetLevel) {
            case 'gurmu':
                // Gurmu -> Gamta -> Godina -> Global
                $approvalChain = $this->buildGurmuApprovalChain($targetId);
                break;
                
            case 'gamta':
                // Gamta -> Godina -> Global
                $approvalChain = $this->buildGamtaApprovalChain($targetId);
                break;
                
            case 'godina':
                // Godina -> Global
                $approvalChain = $this->buildGodinaApprovalChain($targetId);
                break;
                
            case 'global':
            default:
                // Global only
                $approvalChain = $this->buildGlobalApprovalChain();
                break;
        }
        
        return $approvalChain;
    }
    
    /**
     * Build Gurmu approval chain
     */
    protected function buildGurmuApprovalChain(int $gurmuId): array
    {
        $chain = [];
        
        // Get Gurmu hierarchy
        $hierarchy = $this->getGurmuHierarchy($gurmuId);
        
        // 1. Gurmu level approvers
        $gurmuApprovers = $this->getApproversByHierarchy('gurmu', $gurmuId, ['gurmu_leader', 'gurmu_deputy']);
        foreach ($gurmuApprovers as $approver) {
            $chain[] = [
                'level' => 1,
                'approver_id' => $approver['user_id'],
                'approver_type' => 'gurmu_leader',
                'hierarchy_level' => 'gurmu',
                'hierarchy_id' => $gurmuId,
                'required' => true,
                'auto_approve_after_hours' => 72
            ];
        }
        
        // 2. Gamta level approvers
        if ($hierarchy['gamta_id']) {
            $gamtaApprovers = $this->getApproversByHierarchy('gamta', $hierarchy['gamta_id'], ['gamta_leader']);
            foreach ($gamtaApprovers as $approver) {
                $chain[] = [
                    'level' => 2,
                    'approver_id' => $approver['user_id'],
                    'approver_type' => 'gamta_leader',
                    'hierarchy_level' => 'gamta',
                    'hierarchy_id' => $hierarchy['gamta_id'],
                    'required' => true,
                    'auto_approve_after_hours' => 48
                ];
            }
        }
        
        // 3. Godina level approvers
        if ($hierarchy['godina_id']) {
            $godinaApprovers = $this->getApproversByHierarchy('godina', $hierarchy['godina_id'], ['godina_leader']);
            foreach ($godinaApprovers as $approver) {
                $chain[] = [
                    'level' => 3,
                    'approver_id' => $approver['user_id'],
                    'approver_type' => 'godina_leader',
                    'hierarchy_level' => 'godina',
                    'hierarchy_id' => $hierarchy['godina_id'],
                    'required' => false,
                    'auto_approve_after_hours' => 24
                ];
            }
        }
        
        return $chain;
    }
    
    /**
     * Build Gamta approval chain
     */
    protected function buildGamtaApprovalChain(int $gamtaId): array
    {
        $chain = [];
        
        // Get Gamta hierarchy
        $hierarchy = $this->getGamtaHierarchy($gamtaId);
        
        // 1. Gamta level approvers
        $gamtaApprovers = $this->getApproversByHierarchy('gamta', $gamtaId, ['gamta_leader']);
        foreach ($gamtaApprovers as $approver) {
            $chain[] = [
                'level' => 1,
                'approver_id' => $approver['user_id'],
                'approver_type' => 'gamta_leader',
                'hierarchy_level' => 'gamta',
                'hierarchy_id' => $gamtaId,
                'required' => true,
                'auto_approve_after_hours' => 48
            ];
        }
        
        // 2. Godina level approvers
        if ($hierarchy['godina_id']) {
            $godinaApprovers = $this->getApproversByHierarchy('godina', $hierarchy['godina_id'], ['godina_leader']);
            foreach ($godinaApprovers as $approver) {
                $chain[] = [
                    'level' => 2,
                    'approver_id' => $approver['user_id'],
                    'approver_type' => 'godina_leader',
                    'hierarchy_level' => 'godina',
                    'hierarchy_id' => $hierarchy['godina_id'],
                    'required' => false,
                    'auto_approve_after_hours' => 24
                ];
            }
        }
        
        return $chain;
    }
    
    /**
     * Build Godina approval chain
     */
    protected function buildGodinaApprovalChain(int $godinaId): array
    {
        $chain = [];
        
        // Godina level approvers
        $godinaApprovers = $this->getApproversByHierarchy('godina', $godinaId, ['godina_leader']);
        foreach ($godinaApprovers as $approver) {
            $chain[] = [
                'level' => 1,
                'approver_id' => $approver['user_id'],
                'approver_type' => 'godina_leader',
                'hierarchy_level' => 'godina',
                'hierarchy_id' => $godinaId,
                'required' => true,
                'auto_approve_after_hours' => 24
            ];
        }
        
        return $chain;
    }
    
    /**
     * Build Global approval chain
     */
    protected function buildGlobalApprovalChain(): array
    {
        $chain = [];
        
        // Global level approvers
        $globalApprovers = $this->getApproversByHierarchy('global', null, ['global_leader', 'system_admin']);
        foreach ($globalApprovers as $approver) {
            $chain[] = [
                'level' => 1,
                'approver_id' => $approver['user_id'],
                'approver_type' => $approver['position_key'],
                'hierarchy_level' => 'global',
                'hierarchy_id' => null,
                'required' => true,
                'auto_approve_after_hours' => 24
            ];
        }
        
        return $chain;
    }
    
    /**
     * Create workflow record
     */
    protected function createWorkflowRecord(int $registrationId, array $approvalChain): int
    {
        $data = [
            'registration_id' => $registrationId,
            'workflow_type' => 'registration_approval',
            'total_steps' => count($approvalChain),
            'current_step' => 1,
            'status' => 'pending',
            'approval_chain' => json_encode($approvalChain),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('approval_workflows', $data);
    }
    
    /**
     * Create approval steps
     */
    protected function createApprovalSteps(int $workflowId, array $approvalChain): void
    {
        foreach ($approvalChain as $step) {
            $stepData = [
                'workflow_id' => $workflowId,
                'step_number' => $step['level'],
                'approver_id' => $step['approver_id'],
                'approver_type' => $step['approver_type'],
                'hierarchy_level' => $step['hierarchy_level'],
                'hierarchy_id' => $step['hierarchy_id'],
                'is_required' => $step['required'] ? 1 : 0,
                'status' => $step['level'] == 1 ? 'pending' : 'waiting',
                'auto_approve_after' => $step['auto_approve_after_hours'] ? 
                    date('Y-m-d H:i:s', strtotime("+{$step['auto_approve_after_hours']} hours")) : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('approval_workflow_steps', $stepData);
        }
    }
    
    /**
     * Process approval decision
     */
    public function processApproval(int $workflowId, int $approverId, string $decision, string $comments = null): array
    {
        try {
            $this->db->beginTransaction();
            
            // Get current step
            $currentStep = $this->getCurrentApprovalStep($workflowId, $approverId);
            if (!$currentStep) {
                throw new Exception("No pending approval step found for this approver");
            }
            
            // Validate decision
            if (!in_array($decision, ['approved', 'rejected', 'needs_info'])) {
                throw new Exception("Invalid approval decision");
            }
            
            // Update step
            $this->updateApprovalStep($currentStep['id'], $decision, $comments, $approverId);
            
            // Get workflow
            $workflow = $this->getWorkflowById($workflowId);
            
            if ($decision === 'rejected') {
                // Reject entire workflow
                $this->rejectWorkflow($workflowId, $comments);
                $result = ['status' => 'rejected', 'final' => true];
                
            } elseif ($decision === 'needs_info') {
                // Request more information
                $this->requestMoreInfo($workflowId, $comments);
                $result = ['status' => 'needs_info', 'final' => false];
                
            } else {
                // Approved - check if workflow is complete
                $result = $this->checkWorkflowCompletion($workflowId);
            }
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Check if workflow is complete
     */
    protected function checkWorkflowCompletion(int $workflowId): array
    {
        // Get all required steps
        $requiredSteps = $this->db->fetchAll(
            "SELECT * FROM approval_workflow_steps 
             WHERE workflow_id = ? AND is_required = 1 
             ORDER BY step_number",
            [$workflowId]
        );
        
        $allApproved = true;
        $nextStep = null;
        
        foreach ($requiredSteps as $step) {
            if ($step['status'] !== 'approved') {
                if ($step['status'] === 'waiting') {
                    // Activate next step
                    $this->activateApprovalStep($step['id']);
                    $nextStep = $step;
                }
                $allApproved = false;
                break;
            }
        }
        
        if ($allApproved) {
            // Complete workflow
            $this->completeWorkflow($workflowId);
            return ['status' => 'completed', 'final' => true];
        } else {
            // Continue workflow
            if ($nextStep) {
                $this->notifyApprover($nextStep);
            }
            return ['status' => 'pending', 'final' => false, 'next_step' => $nextStep];
        }
    }
    
    /**
     * Complete workflow
     */
    protected function completeWorkflow(int $workflowId): void
    {
        // Update workflow status
        $this->db->update('approval_workflows', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ], ['id' => $workflowId]);
        
        // Get registration and approve it
        $workflow = $this->getWorkflowById($workflowId);
        $this->approveRegistration($workflow['registration_id']);
    }
    
    /**
     * Approve registration
     */
    protected function approveRegistration(int $registrationId): void
    {
        // Update registration status
        $this->db->update('pending_registrations', [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s')
        ], ['id' => $registrationId]);
        
        // Trigger user creation process
        $this->triggerUserCreation($registrationId);
    }
    
    /**
     * Reject workflow
     */
    protected function rejectWorkflow(int $workflowId, string $reason = null): void
    {
        // Update workflow
        $this->db->update('approval_workflows', [
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'completed_at' => date('Y-m-d H:i:s')
        ], ['id' => $workflowId]);
        
        // Update registration
        $workflow = $this->getWorkflowById($workflowId);
        $this->db->update('pending_registrations', [
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => date('Y-m-d H:i:s')
        ], ['id' => $workflow['registration_id']]);
        
        // Notify applicant
        $this->notifyApplicantRejection($workflow['registration_id'], $reason);
    }
    
    /**
     * Get approvers by hierarchy
     */
    protected function getApproversByHierarchy(string $level, int $hierarchyId = null, array $positions = []): array
    {
        $sql = "
            SELECT u.id as user_id, u.first_name, u.last_name, u.email, p.key_name as position_key
            FROM users u
            JOIN positions p ON u.position_id = p.id
            WHERE u.status = 'active'
        ";
        
        $params = [];
        
        if (!empty($positions)) {
            $placeholders = str_repeat('?,', count($positions) - 1) . '?';
            $sql .= " AND p.key_name IN ($placeholders)";
            $params = array_merge($params, $positions);
        }
        
        switch ($level) {
            case 'gurmu':
                $sql .= " AND u.gurmu_id = ?";
                $params[] = $hierarchyId;
                break;
                
            case 'gamta':
                $sql .= " AND u.gamta_id = ?";
                $params[] = $hierarchyId;
                break;
                
            case 'godina':
                $sql .= " AND u.godina_id = ?";
                $params[] = $hierarchyId;
                break;
                
            case 'global':
                $sql .= " AND u.global_id IS NOT NULL";
                break;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get current approval step for approver
     */
    protected function getCurrentApprovalStep(int $workflowId, int $approverId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM approval_workflow_steps 
             WHERE workflow_id = ? AND approver_id = ? AND status = 'pending'",
            [$workflowId, $approverId]
        );
    }
    
    /**
     * Update approval step
     */
    protected function updateApprovalStep(int $stepId, string $decision, string $comments = null, int $approverId = null): void
    {
        $data = [
            'status' => $decision,
            'decision' => $decision,
            'comments' => $comments,
            'decided_at' => date('Y-m-d H:i:s')
        ];
        
        if ($approverId) {
            $data['decided_by'] = $approverId;
        }
        
        $this->db->update('approval_workflow_steps', $data, ['id' => $stepId]);
    }
    
    /**
     * Activate approval step
     */
    protected function activateApprovalStep(int $stepId): void
    {
        $this->db->update('approval_workflow_steps', [
            'status' => 'pending',
            'activated_at' => date('Y-m-d H:i:s')
        ], ['id' => $stepId]);
    }
    
    /**
     * Notify approver
     */
    protected function notifyApprover(array $step): void
    {
        // Add to notification queue
        $this->db->insert('notification_queue', [
            'user_id' => $step['approver_id'],
            'notification_type' => 'approval_request',
            'title' => 'New Registration Approval Required',
            'message' => 'A new registration request requires your approval.',
            'data' => json_encode([
                'workflow_id' => $step['workflow_id'],
                'step_id' => $step['id'],
                'hierarchy_level' => $step['hierarchy_level']
            ]),
            'priority' => 'high',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Notify next approver
     */
    protected function notifyNextApprover(int $workflowId): void
    {
        $nextStep = $this->db->fetch(
            "SELECT * FROM approval_workflow_steps 
             WHERE workflow_id = ? AND status = 'pending' 
             ORDER BY step_number LIMIT 1",
            [$workflowId]
        );
        
        if ($nextStep) {
            $this->notifyApprover($nextStep);
        }
    }
    
    /**
     * Get workflow by ID
     */
    public function getWorkflowById(int $workflowId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM approval_workflows WHERE id = ?",
            [$workflowId]
        );
    }
    
    /**
     * Get registration by ID
     */
    protected function getRegistrationById(int $registrationId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM pending_registrations WHERE id = ?",
            [$registrationId]
        );
    }
    
    /**
     * Get hierarchy data
     */
    protected function getGurmuHierarchy(int $gurmuId): array
    {
        return $this->db->fetch(
            "SELECT g.*, ga.id as gamta_id, go.id as godina_id 
             FROM gurmus g
             LEFT JOIN gamtas ga ON g.gamta_id = ga.id
             LEFT JOIN godinas go ON ga.godina_id = go.id
             WHERE g.id = ?",
            [$gurmuId]
        ) ?: [];
    }
    
    protected function getGamtaHierarchy(int $gamtaId): array
    {
        return $this->db->fetch(
            "SELECT ga.*, go.id as godina_id 
             FROM gamtas ga
             LEFT JOIN godinas go ON ga.godina_id = go.id
             WHERE ga.id = ?",
            [$gamtaId]
        ) ?: [];
    }
    
    /**
     * Trigger user creation
     */
    protected function triggerUserCreation(int $registrationId): void
    {
        // This would be handled by the registration service
        // Add notification to process user creation
        $this->db->insert('notification_queue', [
            'user_id' => null, // System notification
            'notification_type' => 'system_task',
            'title' => 'Create User Account',
            'message' => 'Registration approved - create user account',
            'data' => json_encode([
                'registration_id' => $registrationId,
                'task' => 'create_user_account'
            ]),
            'priority' => 'high',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Notify applicant of rejection
     */
    protected function notifyApplicantRejection(int $registrationId, string $reason = null): void
    {
        $registration = $this->getRegistrationById($registrationId);
        if (!$registration) return;
        
        // Send email notification (would integrate with email service)
        error_log("Registration rejected for {$registration['personal_email']}: {$reason}");
    }
    
    /**
     * Get pending approvals for user
     */
    public function getPendingApprovalsForUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT aws.*, aw.registration_id, pr.first_name, pr.last_name, pr.personal_email
             FROM approval_workflow_steps aws
             JOIN approval_workflows aw ON aws.workflow_id = aw.id
             JOIN pending_registrations pr ON aw.registration_id = pr.id
             WHERE aws.approver_id = ? AND aws.status = 'pending'
             ORDER BY aws.created_at",
            [$userId]
        );
    }
    
    /**
     * Auto-approve expired steps
     */
    public function processAutoApprovals(): int
    {
        $expiredSteps = $this->db->fetchAll(
            "SELECT * FROM approval_workflow_steps 
             WHERE status = 'pending' 
             AND auto_approve_after IS NOT NULL 
             AND auto_approve_after <= NOW()"
        );
        
        $count = 0;
        foreach ($expiredSteps as $step) {
            try {
                $this->processApproval(
                    $step['workflow_id'],
                    $step['approver_id'],
                    'approved',
                    'Auto-approved due to timeout'
                );
                $count++;
            } catch (Exception $e) {
                error_log("Failed to auto-approve step {$step['id']}: " . $e->getMessage());
            }
        }
        
        return $count;
    }
}