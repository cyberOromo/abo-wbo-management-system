<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\InternalEmailGenerator;
use App\Services\ApprovalWorkflowService;
use App\Services\NotificationService;
use App\Utils\Database;
use App\Utils\Validator;
use Exception;

/**
 * System Admin Registration Controller
 * 
 * Handles registration and management of system administrators
 * Includes approval workflow and internal email generation
 * 
 * Features:
 * - Multi-step registration process
 * - Email verification
 * - Approval workflow by existing admins
 * - Internal email generation
 * - Security validations
 * - Admin user management
 */
class SystemAdminRegistrationController extends Controller
{
    protected $db;
    protected $emailGenerator;
    protected $approvalService;
    protected $notificationService;
    protected $validator;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->emailGenerator = new InternalEmailGenerator();
        $this->approvalService = new ApprovalWorkflowService();
        $this->notificationService = new NotificationService();
        $this->validator = new Validator();
    }
    
    /**
     * Display system admin registration form
     */
    public function index()
    {
        // Check if current user has permission to initiate system admin registration
        $this->requirePermission('register_system_admin');
        
        echo $this->render('admin/system-admin-registration', [
            'title' => 'Register New System Administrator',
            'page_title' => 'System Admin Registration'
        ]);
    }
    
    /**
     * Submit system admin registration request
     */
    public function submit()
    {
        try {
            $this->requirePermission('register_system_admin');
            $this->validateCsrfToken();
            
            // Validate required fields
            $requiredFields = [
                'first_name', 'last_name', 'personal_email', 
                'phone', 'date_of_birth', 'gender'
            ];
            
            $this->validateRequiredFields($requiredFields);
            
            // Sanitize and prepare data
            $data = [
                'first_name' => $this->sanitizeInput($_POST['first_name']),
                'last_name' => $this->sanitizeInput($_POST['last_name']),
                'personal_email' => filter_var($_POST['personal_email'], FILTER_SANITIZE_EMAIL),
                'phone' => $this->sanitizeInput($_POST['phone']),
                'date_of_birth' => $_POST['date_of_birth'],
                'gender' => $_POST['gender'],
                'address' => $this->sanitizeInput($_POST['address'] ?? ''),
                'city' => $this->sanitizeInput($_POST['city'] ?? ''),
                'country' => $this->sanitizeInput($_POST['country'] ?? ''),
                'emergency_contact_name' => $this->sanitizeInput($_POST['emergency_contact_name'] ?? ''),
                'emergency_contact_phone' => $this->sanitizeInput($_POST['emergency_contact_phone'] ?? ''),
                'qualifications' => $this->sanitizeInput($_POST['qualifications'] ?? ''),
                'experience' => $this->sanitizeInput($_POST['experience'] ?? ''),
                'reason_for_application' => $this->sanitizeInput($_POST['reason_for_application'] ?? ''),
                'requested_by' => $this->getCurrentUserId(),
                'registration_type' => 'system_admin',
                'target_hierarchy_level' => 'global',
                'target_position_id' => $this->getSystemAdminPositionId(),
                'status' => 'email_verification_pending',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate data
            $validation = $this->validateRegistrationData($data);
            if (!$validation['valid']) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $validation['message']
                ]);
            }
            
            // Check if email already exists
            if ($this->isEmailInUse($data['personal_email'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email address is already registered'
                ]);
            }
            
            // Generate verification code
            $verificationCode = $this->generateVerificationCode();
            $data['verification_code'] = $verificationCode;
            $data['verification_code_expires_at'] = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Create registration record
            $registrationId = $this->db->insert('pending_registrations', $data);
            
            if ($registrationId) {
                // Send verification email
                $emailSent = $this->notificationService->sendEmailVerificationCode(
                    $data['personal_email'],
                    $data['first_name'],
                    $verificationCode
                );
                
                if ($emailSent['success']) {
                    return $this->jsonResponse([
                        'success' => true,
                        'message' => 'Registration submitted. Please check email for verification code.',
                        'registration_id' => $registrationId
                    ]);
                } else {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => 'Registration created but failed to send verification email'
                    ]);
                }
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create registration record'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("System Admin Registration Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ]);
        }
    }
    
    /**
     * Verify email with code
     */
    public function verifyEmail()
    {
        try {
            $this->validateRequiredFields(['registration_id', 'verification_code']);
            
            $registrationId = (int) $_POST['registration_id'];
            $code = $this->sanitizeInput($_POST['verification_code']);
            
            // Get registration
            $registration = $this->db->fetch(
                "SELECT * FROM pending_registrations WHERE id = ?",
                [$registrationId]
            );
            
            if (!$registration) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Registration not found'
                ]);
            }
            
            // Check if already verified
            if ($registration['status'] === 'email_verified' || $registration['status'] === 'approval_pending') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email already verified'
                ]);
            }
            
            // Check if code matches
            if ($registration['verification_code'] !== $code) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid verification code'
                ]);
            }
            
            // Check if code expired
            if (strtotime($registration['verification_code_expires_at']) < time()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Verification code has expired. Please request a new one.'
                ]);
            }
            
            // Update registration status
            $updated = $this->db->update('pending_registrations', [
                'status' => 'email_verified',
                'email_verified_at' => date('Y-m-d H:i:s')
            ], ['id' => $registrationId]);
            
            if ($updated) {
                // Start approval workflow
                $workflowId = $this->approvalService->startApprovalWorkflow($registrationId);
                
                // Update status to approval pending
                $this->db->update('pending_registrations', [
                    'status' => 'approval_pending',
                    'approval_workflow_id' => $workflowId
                ], ['id' => $registrationId]);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Email verified successfully. Registration is now pending approval.',
                    'workflow_id' => $workflowId
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to verify email'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Email Verification Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ]);
        }
    }
    
    /**
     * Resend verification code
     */
    public function resendVerificationCode()
    {
        try {
            $this->validateRequiredFields(['registration_id']);
            
            $registrationId = (int) $_POST['registration_id'];
            
            $registration = $this->db->fetch(
                "SELECT * FROM pending_registrations WHERE id = ?",
                [$registrationId]
            );
            
            if (!$registration) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Registration not found'
                ]);
            }
            
            if ($registration['status'] !== 'email_verification_pending') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email already verified or registration completed'
                ]);
            }
            
            // Generate new verification code
            $newCode = $this->generateVerificationCode();
            
            $updated = $this->db->update('pending_registrations', [
                'verification_code' => $newCode,
                'verification_code_expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ], ['id' => $registrationId]);
            
            if ($updated) {
                // Send new code
                $emailSent = $this->notificationService->sendEmailVerificationCode(
                    $registration['personal_email'],
                    $registration['first_name'],
                    $newCode
                );
                
                if ($emailSent['success']) {
                    return $this->jsonResponse([
                        'success' => true,
                        'message' => 'Verification code resent successfully'
                    ]);
                } else {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => 'Failed to send verification email'
                    ]);
                }
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to generate new code'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Resend Verification Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to resend code'
            ]);
        }
    }
    
    /**
     * View pending system admin registrations
     */
    public function pending()
    {
        $this->requirePermission('approve_system_admin');
        
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get pending registrations
        $registrations = $this->db->fetchAll(
            "SELECT pr.*, u.first_name as requested_by_name, u.last_name as requested_by_lastname
             FROM pending_registrations pr
             LEFT JOIN users u ON pr.requested_by = u.id
             WHERE pr.registration_type = 'system_admin' 
             AND pr.status IN ('approval_pending', 'email_verified')
             ORDER BY pr.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
        
        // Get total count
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM pending_registrations 
             WHERE registration_type = 'system_admin' 
             AND status IN ('approval_pending', 'email_verified')"
        );
        
        echo $this->render('admin/pending-system-admin-registrations', [
            'title' => 'Pending System Admin Registrations',
            'registrations' => $registrations,
            'total_count' => $totalCount['count'],
            'current_page' => $page,
            'total_pages' => ceil($totalCount['count'] / $limit)
        ]);
    }
    
    /**
     * View registration details
     */
    public function view($id)
    {
        $this->requirePermission('approve_system_admin');
        
        $registration = $this->db->fetch(
            "SELECT pr.*, u.first_name as requested_by_name, u.last_name as requested_by_lastname, u.email as requested_by_email
             FROM pending_registrations pr
             LEFT JOIN users u ON pr.requested_by = u.id
             WHERE pr.id = ? AND pr.registration_type = 'system_admin'",
            [$id]
        );
        
        if (!$registration) {
            $this->redirect('/admin/system-admin-registration/pending?error=not_found');
            return;
        }
        
        // Get approval workflow if exists
        $workflow = null;
        $approvalSteps = [];
        
        if ($registration['approval_workflow_id']) {
            $workflow = $this->db->fetch(
                "SELECT * FROM approval_workflows WHERE id = ?",
                [$registration['approval_workflow_id']]
            );
            
            $approvalSteps = $this->db->fetchAll(
                "SELECT aws.*, u.first_name, u.last_name, u.email, p.name as position_name
                 FROM approval_workflow_steps aws
                 LEFT JOIN users u ON aws.approver_id = u.id
                 LEFT JOIN positions p ON u.position_id = p.id
                 WHERE aws.workflow_id = ?
                 ORDER BY aws.step_number",
                [$registration['approval_workflow_id']]
            );
        }
        
        echo $this->render('admin/view-system-admin-registration', [
            'title' => 'System Admin Registration Details',
            'registration' => $registration,
            'workflow' => $workflow,
            'approval_steps' => $approvalSteps
        ]);
    }
    
    /**
     * Approve system admin registration
     */
    public function approve()
    {
        try {
            $this->requirePermission('approve_system_admin');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['registration_id']);
            
            $registrationId = (int) $_POST['registration_id'];
            $comments = $this->sanitizeInput($_POST['comments'] ?? '');
            
            $registration = $this->db->fetch(
                "SELECT * FROM pending_registrations WHERE id = ? AND registration_type = 'system_admin'",
                [$registrationId]
            );
            
            if (!$registration) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Registration not found'
                ]);
            }
            
            $this->db->beginTransaction();
            
            try {
                // Create user account
                $userId = $this->createSystemAdminUser($registration);
                
                // Generate internal email
                $internalEmail = $this->emailGenerator->generateInternalEmail(
                    $registration,
                    ['key_name' => 'system_admin'],
                    ['level' => 'global']
                );
                
                // Create internal email record
                $emailId = $this->emailGenerator->createInternalEmailRecord($userId, $internalEmail, [
                    'email_type' => 'primary',
                    'quota_mb' => 5120, // 5GB for system admins
                    'created_by' => $this->getCurrentUserId(),
                    'creation_method' => 'system_admin_registration'
                ]);
                
                // Generate temporary password
                $tempPassword = $this->generateTemporaryPassword();
                
                // Update user with internal email
                $this->db->update('users', [
                    'internal_email' => $internalEmail,
                    'temp_password' => password_hash($tempPassword, PASSWORD_DEFAULT),
                    'password_reset_required' => 1
                ], ['id' => $userId]);
                
                // Update registration status
                $this->db->update('pending_registrations', [
                    'status' => 'approved',
                    'approved_by' => $this->getCurrentUserId(),
                    'approved_at' => date('Y-m-d H:i:s'),
                    'approval_comments' => $comments,
                    'user_id' => $userId,
                    'internal_email' => $internalEmail
                ], ['id' => $registrationId]);
                
                // Send welcome email with credentials
                $this->notificationService->sendSystemAdminWelcomeEmail(
                    $registration['personal_email'],
                    $registration['first_name'],
                    $internalEmail,
                    $tempPassword
                );
                
                $this->db->commit();
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'System admin registration approved successfully',
                    'user_id' => $userId,
                    'internal_email' => $internalEmail
                ]);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Approval Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to approve registration: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reject system admin registration
     */
    public function reject()
    {
        try {
            $this->requirePermission('approve_system_admin');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['registration_id', 'rejection_reason']);
            
            $registrationId = (int) $_POST['registration_id'];
            $reason = $this->sanitizeInput($_POST['rejection_reason']);
            
            $registration = $this->db->fetch(
                "SELECT * FROM pending_registrations WHERE id = ? AND registration_type = 'system_admin'",
                [$registrationId]
            );
            
            if (!$registration) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Registration not found'
                ]);
            }
            
            // Update registration status
            $updated = $this->db->update('pending_registrations', [
                'status' => 'rejected',
                'rejected_by' => $this->getCurrentUserId(),
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason
            ], ['id' => $registrationId]);
            
            if ($updated) {
                // Send rejection notification
                $this->notificationService->sendRegistrationRejectionEmail(
                    $registration['personal_email'],
                    $registration['first_name'],
                    $reason
                );
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Registration rejected successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to reject registration'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Rejection Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to reject registration'
            ]);
        }
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Create system admin user account
     */
    protected function createSystemAdminUser(array $registration): int
    {
        $userData = [
            'first_name' => $registration['first_name'],
            'last_name' => $registration['last_name'],
            'email' => $registration['personal_email'],
            'phone' => $registration['phone'],
            'date_of_birth' => $registration['date_of_birth'],
            'gender' => $registration['gender'],
            'role' => 'system_admin',
            'user_type' => 'system_admin',
            'position_id' => $this->getSystemAdminPositionId(),
            'status' => 'active',
            'address' => $registration['address'],
            'city' => $registration['city'],
            'country' => $registration['country'],
            'emergency_contact_name' => $registration['emergency_contact_name'],
            'emergency_contact_phone' => $registration['emergency_contact_phone'],
            'created_by' => $this->getCurrentUserId(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('users', $userData);
    }
    
    /**
     * Get system admin position ID
     */
    protected function getSystemAdminPositionId(): int
    {
        $position = $this->db->fetch(
            "SELECT id FROM positions WHERE key_name = 'system_admin' LIMIT 1"
        );
        
        return $position ? $position['id'] : 1; // Default to ID 1 if not found
    }
    
    /**
     * Validate registration data
     */
    protected function validateRegistrationData(array $data): array
    {
        // Email validation
        if (!filter_var($data['personal_email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
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
        
        return ['valid' => true];
    }
    
    /**
     * Check if email is already in use
     */
    protected function isEmailInUse(string $email): bool
    {
        // Check in users table
        $user = $this->db->fetch(
            "SELECT id FROM users WHERE email = ? OR internal_email = ?",
            [$email, $email]
        );
        
        if ($user) {
            return true;
        }
        
        // Check in pending registrations
        $pending = $this->db->fetch(
            "SELECT id FROM pending_registrations 
             WHERE personal_email = ? 
             AND status NOT IN ('rejected', 'expired')",
            [$email]
        );
        
        return $pending !== null;
    }
    
    /**
     * Generate 6-digit verification code
     */
    protected function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate temporary password
     */
    protected function generateTemporaryPassword(): string
    {
        return $this->emailGenerator->generateEmailPassword(12);
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequiredFields(array $fields): void
    {
        foreach ($fields as $field) {
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
        $user = auth_user();
        return $user['id'] ?? 0;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
    }
}
