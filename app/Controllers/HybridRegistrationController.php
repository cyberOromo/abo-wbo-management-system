<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\HybridRegistrationService;
use App\Services\InternalEmailGenerator;
use App\Services\ApprovalWorkflowService;
use App\Services\NotificationService;
use App\Utils\Validator;
use Exception;

/**
 * Hybrid Registration Controller
 * 
 * Handles two-step registration process:
 * 1. Personal email verification
 * 2. Internal email generation and approval workflow
 * 
 * Features:
 * - Email verification with codes
 * - Hierarchical approval workflows
 * - Internal email generation
 * - Progress tracking
 * - Admin management
 */
class HybridRegistrationController extends Controller
{
    protected $hybridService;
    protected $emailGenerator;
    protected $approvalService;
    protected $notificationService;
    protected $validator;
    
    public function __construct()
    {
        parent::__construct();
        $this->hybridService = new HybridRegistrationService();
        $this->emailGenerator = new InternalEmailGenerator();
        $this->approvalService = new ApprovalWorkflowService();
        $this->notificationService = new NotificationService();
        $this->validator = new Validator();
    }
    
    /**
     * Show registration form
     */
    public function index()
    {
        $this->render('hybrid-registration/index', [
            'title' => 'ABO-WBO Registration',
            'hierarchies' => $this->getHierarchyOptions(),
            'positions' => $this->getPositionOptions()
        ]);
    }
    
    /**
     * Step 1: Submit personal email for verification
     */
    public function submitPersonalEmail()
    {
        try {
            $this->validateRequest(['personal_email']);
            
            $email = $this->sanitizeInput($_POST['personal_email']);
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid email format']);
            }
            
            // Check if email already in use
            if ($this->hybridService->isEmailInUse($email)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email already registered']);
            }
            
            // Generate and send verification code
            $verificationCode = $this->hybridService->generateVerificationCode();
            $registrationId = $this->hybridService->createPendingRegistration([
                'personal_email' => $email,
                'verification_code' => $verificationCode,
                'status' => 'email_verification_pending'
            ]);
            
            // Send verification email
            $emailSent = $this->notificationService->sendHybridRegistrationNotification($email, $verificationCode);
            
            if ($emailSent['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Verification code sent to your email',
                    'registration_id' => $registrationId
                ]);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to send verification email']);
            }
            
        } catch (Exception $e) {
            error_log("Personal email submission error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.']);
        }
    }
    
    /**
     * Step 2: Verify email code and show full registration form
     */
    public function verifyEmail()
    {
        try {
            $this->validateRequest(['registration_id', 'verification_code']);
            
            $registrationId = (int) $_POST['registration_id'];
            $code = $this->sanitizeInput($_POST['verification_code']);
            
            // Verify code
            $verification = $this->hybridService->verifyEmailCode($registrationId, $code);
            
            if ($verification['success']) {
                // Get registration data
                $registration = $this->hybridService->getRegistrationById($registrationId);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Email verified successfully',
                    'redirect' => '/hybrid-registration/complete/' . $registrationId,
                    'registration' => [
                        'id' => $registration['id'],
                        'personal_email' => $registration['personal_email']
                    ]
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => $verification['message']
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Verification failed. Please try again.']);
        }
    }
    
    /**
     * Step 3: Show complete registration form
     */
    public function complete($registrationId)
    {
        try {
            $registration = $this->hybridService->getRegistrationById($registrationId);
            
            if (!$registration || $registration['status'] !== 'email_verified') {
                $this->redirect('/hybrid-registration?error=invalid_registration');
                return;
            }
            
            $this->render('hybrid-registration/complete', [
                'title' => 'Complete Registration',
                'registration' => $registration,
                'hierarchies' => $this->getHierarchyOptions(),
                'positions' => $this->getPositionOptions()
            ]);
            
        } catch (Exception $e) {
            error_log("Complete registration view error: " . $e->getMessage());
            $this->redirect('/hybrid-registration?error=system_error');
        }
    }
    
    /**
     * Step 4: Submit complete registration data
     */
    public function submitComplete()
    {
        try {
            $requiredFields = [
                'registration_id', 'first_name', 'last_name', 'phone',
                'date_of_birth', 'gender', 'target_hierarchy_level'
            ];
            
            $this->validateRequest($requiredFields);
            
            $registrationId = (int) $_POST['registration_id'];
            
            // Get existing registration
            $registration = $this->hybridService->getRegistrationById($registrationId);
            if (!$registration || $registration['status'] !== 'email_verified') {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid registration state']);
            }
            
            // Prepare registration data
            $registrationData = [
                'first_name' => $this->sanitizeInput($_POST['first_name']),
                'last_name' => $this->sanitizeInput($_POST['last_name']),
                'phone' => $this->sanitizeInput($_POST['phone']),
                'date_of_birth' => $_POST['date_of_birth'],
                'gender' => $_POST['gender'],
                'target_hierarchy_level' => $_POST['target_hierarchy_level'],
                'target_hierarchy_id' => $_POST['target_hierarchy_id'] ?? null,
                'target_position_id' => $_POST['target_position_id'] ?? null,
                'additional_info' => $this->sanitizeInput($_POST['additional_info'] ?? ''),
                'status' => 'approval_pending'
            ];
            
            // Validate data
            $validation = $this->validateRegistrationData($registrationData);
            if (!$validation['valid']) {
                return $this->jsonResponse(['success' => false, 'message' => $validation['message']]);
            }
            
            // Update registration
            $updated = $this->hybridService->updateRegistration($registrationId, $registrationData);
            
            if ($updated) {
                // Generate internal email preview
                $emailPreview = $this->generateInternalEmailPreview($registrationData);
                
                // Start approval workflow
                $workflowId = $this->approvalService->startApprovalWorkflow($registrationId);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Registration submitted successfully',
                    'data' => [
                        'registration_id' => $registrationId,
                        'workflow_id' => $workflowId,
                        'internal_email_preview' => $emailPreview,
                        'status' => 'Pending Approval'
                    ]
                ]);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to update registration']);
            }
            
        } catch (Exception $e) {
            error_log("Complete registration submission error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.']);
        }
    }
    
    /**
     * Check registration status
     */
    public function checkStatus()
    {
        try {
            $this->validateRequest(['registration_id']);
            
            $registrationId = (int) $_POST['registration_id'];
            $status = $this->hybridService->getRegistrationStatus($registrationId);
            
            return $this->jsonResponse(['success' => true, 'status' => $status]);
            
        } catch (Exception $e) {
            error_log("Status check error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to check status']);
        }
    }
    
    /**
     * Resend verification code
     */
    public function resendVerification()
    {
        try {
            $this->validateRequest(['registration_id']);
            
            $registrationId = (int) $_POST['registration_id'];
            $registration = $this->hybridService->getRegistrationById($registrationId);
            
            if (!$registration) {
                return $this->jsonResponse(['success' => false, 'message' => 'Registration not found']);
            }
            
            if ($registration['status'] !== 'email_verification_pending') {
                return $this->jsonResponse(['success' => false, 'message' => 'Email already verified']);
            }
            
            // Generate new code
            $verificationCode = $this->hybridService->generateVerificationCode();
            $this->hybridService->updateVerificationCode($registrationId, $verificationCode);
            
            // Send email
            $emailSent = $this->notificationService->sendHybridRegistrationNotification(
                $registration['personal_email'], 
                $verificationCode
            );
            
            if ($emailSent['success']) {
                return $this->jsonResponse(['success' => true, 'message' => 'Verification code resent']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to resend code']);
            }
            
        } catch (Exception $e) {
            error_log("Resend verification error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to resend verification']);
        }
    }
    
    // ========================================
    // ADMIN METHODS
    // ========================================
    
    /**
     * Admin dashboard for registration management
     */
    public function adminDashboard()
    {
        $this->requirePermission('manage_registrations');
        
        $stats = $this->hybridService->getRegistrationStats();
        $pendingApprovals = $this->approvalService->getPendingApprovalsForUser($this->getCurrentUserId());
        
        $this->render('hybrid-registration/admin/dashboard', [
            'title' => 'Registration Management',
            'stats' => $stats,
            'pending_approvals' => $pendingApprovals
        ]);
    }
    
    /**
     * Process approval decision
     */
    public function processApproval()
    {
        try {
            $this->requirePermission('approve_registrations');
            $this->validateRequest(['workflow_id', 'decision']);
            
            $workflowId = (int) $_POST['workflow_id'];
            $decision = $_POST['decision'];
            $comments = $this->sanitizeInput($_POST['comments'] ?? '');
            $approverId = $this->getCurrentUserId();
            
            $result = $this->approvalService->processApproval($workflowId, $approverId, $decision, $comments);
            
            // If approved and final, create user account
            if ($result['status'] === 'completed' && $result['final']) {
                $this->processApprovedRegistration($workflowId);
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => ucfirst($decision) . ' processed successfully',
                'result' => $result
            ]);
            
        } catch (Exception $e) {
            error_log("Approval processing error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to process approval']);
        }
    }
    
    /**
     * Get pending registrations
     */
    public function getPendingRegistrations()
    {
        $this->requirePermission('view_registrations');
        
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $status = $_GET['status'] ?? null;
            
            $registrations = $this->hybridService->getPendingRegistrations($page, $limit, $status);
            
            return $this->jsonResponse(['success' => true, 'data' => $registrations]);
            
        } catch (Exception $e) {
            error_log("Get pending registrations error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to fetch registrations']);
        }
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Validate registration data
     */
    protected function validateRegistrationData(array $data): array
    {
        // Name validation
        if (strlen($data['first_name']) < 2 || strlen($data['last_name']) < 2) {
            return ['valid' => false, 'message' => 'Names must be at least 2 characters'];
        }
        
        // Phone validation
        if (!preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $data['phone'])) {
            return ['valid' => false, 'message' => 'Invalid phone number format'];
        }
        
        // Date of birth validation
        $dob = strtotime($data['date_of_birth']);
        if (!$dob || $dob > strtotime('-18 years')) {
            return ['valid' => false, 'message' => 'Must be at least 18 years old'];
        }
        
        // Gender validation
        if (!in_array($data['gender'], ['male', 'female', 'other'])) {
            return ['valid' => false, 'message' => 'Invalid gender selection'];
        }
        
        // Hierarchy validation
        if (!in_array($data['target_hierarchy_level'], ['global', 'godina', 'gamta', 'gurmu'])) {
            return ['valid' => false, 'message' => 'Invalid hierarchy level'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generate internal email preview
     */
    protected function generateInternalEmailPreview(array $registrationData): array
    {
        try {
            // Get hierarchy and position data
            $hierarchyData = $this->getHierarchyData($registrationData['target_hierarchy_level'], $registrationData['target_hierarchy_id']);
            $positionData = $this->getPositionData($registrationData['target_position_id']);
            
            return $this->emailGenerator->previewEmailGeneration($registrationData, $positionData, $hierarchyData);
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Process approved registration
     */
    protected function processApprovedRegistration(int $workflowId): void
    {
        try {
            $workflow = $this->approvalService->getWorkflowById($workflowId);
            $registration = $this->hybridService->getRegistrationById($workflow['registration_id']);
            
            // Create user account
            $userId = $this->hybridService->createUserAccount($registration);
            
            // Generate internal email
            $hierarchyData = $this->getHierarchyData($registration['target_hierarchy_level'], $registration['target_hierarchy_id']);
            $positionData = $this->getPositionData($registration['target_position_id']);
            
            $internalEmail = $this->emailGenerator->generateInternalEmail($registration, $positionData, $hierarchyData);
            $emailId = $this->emailGenerator->createInternalEmailRecord($userId, $internalEmail);

            if ($positionData && !empty($positionData['key_name']) && $positionData['key_name'] !== 'member') {
                $this->emailGenerator->provisionRoleAlias($userId, $registration, $positionData, $hierarchyData, [
                    'forward_to' => $internalEmail,
                    'creation_method' => 'hybrid_registration_alias'
                ]);
            }
            
            // Generate temporary password
            $tempPassword = $this->emailGenerator->generateEmailPassword();
            
            // Create cPanel email account (placeholder)
            $this->emailGenerator->createCPanelEmailAccount($internalEmail, $tempPassword);
            
            // Notify user
            $this->notificationService->sendInternalEmailCreatedNotification($userId, $internalEmail, $tempPassword);
            
            // Update registration as completed
            $this->hybridService->updateRegistration($workflow['registration_id'], [
                'status' => 'completed',
                'user_id' => $userId,
                'internal_email' => $internalEmail,
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Process approved registration error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get hierarchy options for dropdowns
     */
    protected function getHierarchyOptions(): array
    {
        return [
            'globals' => $this->db->fetchAll("SELECT * FROM globals ORDER BY name"),
            'godinas' => $this->db->fetchAll("SELECT * FROM godinas ORDER BY name"),
            'gamtas' => $this->db->fetchAll("SELECT * FROM gamtas ORDER BY name"),
            'gurmus' => $this->db->fetchAll("SELECT * FROM gurmus ORDER BY name")
        ];
    }
    
    /**
     * Get position options
     */
    protected function getPositionOptions(): array
    {
        return $this->db->fetchAll("SELECT * FROM positions ORDER BY level_order, name");
    }
    
    /**
     * Get hierarchy data
     */
    protected function getHierarchyData(string $level, int $id = null): array
    {
        if (!$id) {
            return ['level' => $level];
        }
        
        switch ($level) {
            case 'godina':
                return $this->db->fetch("SELECT *, 'godina' as level FROM godinas WHERE id = ?", [$id]) ?: [];
            case 'gamta':
                return $this->db->fetch("SELECT *, 'gamta' as level FROM gamtas WHERE id = ?", [$id]) ?: [];
            case 'gurmu':
                return $this->db->fetch("SELECT *, 'gurmu' as level FROM gurmus WHERE id = ?", [$id]) ?: [];
            default:
                return ['level' => 'global'];
        }
    }
    
    /**
     * Get position data
     */
    protected function getPositionData(int $positionId = null): ?array
    {
        if (!$positionId) return null;
        return $this->db->fetch("SELECT * FROM positions WHERE id = ?", [$positionId]);
    }
    
    /**
     * Validate required request fields
     */
    protected function validateRequest(array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
    }
    
    /**
     * Sanitize input
     */
    protected function sanitizeInput(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Get current user ID
     */
    protected function getCurrentUserId(): int
    {
        return $_SESSION['user_id'] ?? 0;
    }
    
    /**
     * Check if user has permission
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
            exit;
        }
    }
    
    /**
     * Check permission (placeholder)
     */
    protected function hasPermission(string $permission): bool
    {
        // Would integrate with RBAC system
        return true;
    }
}