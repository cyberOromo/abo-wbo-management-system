<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\InternalEmailGenerator;
use App\Utils\Database;
use Exception;

/**
 * User Email Controller
 * 
 * Manages internal email accounts for users
 * Administrative interface for email CRUD operations
 * 
 * Features:
 * - View all internal emails
 * - Create new email accounts
 * - Update email settings
 * - Reset passwords
 * - Manage quotas and forwarding
 * - Bulk operations
 * - Usage statistics
 */
class UserEmailController extends Controller
{
    protected $db;
    protected $emailGenerator;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->emailGenerator = new InternalEmailGenerator();
    }
    
    /**
     * List all internal email accounts
     */
    public function index()
    {
        $this->requirePermission('manage_internal_emails');
        
        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? null,
            'email_type' => $_GET['email_type'] ?? null,
            'search' => $_GET['search'] ?? null,
            'page' => $_GET['page'] ?? 1,
            'limit' => $_GET['limit'] ?? 50
        ];
        
        $offset = ($filters['page'] - 1) * $filters['limit'];
        
        // Build query
        $sql = "SELECT ie.*, 
                       u.first_name, u.last_name, u.email as personal_email, u.role,
                       GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as position_name
                FROM internal_emails ie
                LEFT JOIN users u ON ie.user_id = u.id
                LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
                LEFT JOIN positions p ON ua.position_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if ($filters['status']) {
            $sql .= " AND ie.status = ?";
            $params[] = $filters['status'];
        }
        
        if ($filters['email_type']) {
            $sql .= " AND ie.email_type = ?";
            $params[] = $filters['email_type'];
        }
        
        if ($filters['search']) {
            $sql .= " AND (ie.internal_email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY ie.created_at DESC LIMIT ? OFFSET ?";
        // Add GROUP BY before ORDER BY
        $sql = str_replace(" ORDER BY", " GROUP BY ie.id ORDER BY", $sql);
        $params[] = (int) $filters['limit'];
        $params[] = $offset;
        
        $emails = $this->db->fetchAll($sql, $params);
        
        // Get total count for pagination
        $countSql = str_replace("SELECT ie.*, u.first_name, u.last_name, u.email as personal_email, u.role, p.name as position_name", "SELECT COUNT(*) as total", $sql);
        $countSql = preg_replace('/ORDER BY .* LIMIT .* OFFSET .*/', '', $countSql);
        $countParams = array_slice($params, 0, -2); // Remove limit and offset
        $totalCount = $this->db->fetch($countSql, $countParams);
        
        // Get statistics
        $stats = $this->getEmailStatistics();
        
        echo $this->render('admin/user_email_management', [
            'title' => 'Internal Email Management',
            'emails' => $emails,
            'filters' => $filters,
            'stats' => $stats,
            'total_count' => $totalCount['total'] ?? 0,
            'total_pages' => ceil(($totalCount['total'] ?? 0) / $filters['limit']),
            'current_page' => $filters['page']
        ]);
    }
    
    /**
     * View email account details
     */
    public function view($id)
    {
        $this->requirePermission('manage_internal_emails');
        
        $email = $this->db->fetch(
            "SELECT ie.*, 
                    u.first_name, u.last_name, u.email as personal_email, u.role, u.phone,
                    p.name as position_name,
                    creator.first_name as created_by_name, creator.last_name as created_by_lastname
             FROM internal_emails ie
             LEFT JOIN users u ON ie.user_id = u.id
             LEFT JOIN positions p ON u.position_id = p.id
             LEFT JOIN users creator ON CAST(JSON_EXTRACT(ie.creation_metadata, '$.created_by') AS UNSIGNED) = creator.id
             WHERE ie.id = ?",
            [$id]
        );
        
        if (!$email) {
            $this->redirect('/admin/emails?error=not_found');
            return;
        }
        
        // Get usage statistics (in production, this would query cPanel API)
        $usageStats = $this->getEmailUsageStats($email['internal_email']);
        
        // Get activity log
        $activityLog = $this->getEmailActivityLog($id);
        
        echo $this->render('admin/view-email-account', [
            'title' => 'Email Account Details - ' . $email['internal_email'],
            'email' => $email,
            'usage_stats' => $usageStats,
            'activity_log' => $activityLog
        ]);
    }
    
    /**
     * Create new email account form
     */
    public function create()
    {
        $this->requirePermission('manage_internal_emails');
        
        // Get all users without internal emails
        $users = $this->db->fetchAll(
            "SELECT u.* FROM users u
             LEFT JOIN internal_emails ie ON u.id = ie.user_id AND ie.email_type = 'primary'
             WHERE ie.id IS NULL AND u.status = 'active'
             ORDER BY u.first_name, u.last_name"
        );
        
        echo $this->render('admin/create-email-account', [
            'title' => 'Create Internal Email Account',
            'users' => $users
        ]);
    }
    
    /**
     * Store new email account
     */
    public function store()
    {
        try {
            $this->requirePermission('manage_internal_emails');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['user_id']);
            
            $userId = (int) $_POST['user_id'];
            
            // Get user data
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                return $this->jsonResponse(['success' => false, 'message' => 'User not found']);
            }
            
            // Get position and hierarchy data
            $position = $this->db->fetch("SELECT * FROM positions WHERE id = ?", [$user['position_id']]);
            $hierarchyData = $this->getUserHierarchyData($user);
            
            // Generate internal email
            $internalEmail = $this->emailGenerator->generateInternalEmail($user, $position, $hierarchyData);
            
            // Check if email already exists
            if (!$this->emailGenerator->isEmailUnique($internalEmail)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Generated email already exists. Please try again.'
                ]);
            }
            
            // Generate password
            $password = $this->emailGenerator->generateEmailPassword();
            
            // Create email record
            $emailId = $this->emailGenerator->createInternalEmailRecord($userId, $internalEmail, [
                'email_type' => $_POST['email_type'] ?? 'primary',
                'quota_mb' => $_POST['quota_mb'] ?? 1024,
                'forward_to' => $_POST['forward_to'] ?? null,
                'created_by' => $this->getCurrentUserId(),
                'creation_method' => 'admin_manual'
            ]);
            
            if ($emailId) {
                // Create cPanel account
                $cpanelResult = $this->emailGenerator->createCPanelEmailAccount(
                    $internalEmail,
                    $password,
                    $_POST['quota_mb'] ?? 1024
                );
                
                // Update user record
                $this->db->update('users', [
                    'internal_email' => $internalEmail
                ], ['id' => $userId]);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Email account created successfully',
                    'email_id' => $emailId,
                    'internal_email' => $internalEmail,
                    'temporary_password' => $password,
                    'cpanel_result' => $cpanelResult
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create email account'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Email creation error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error creating email account: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reset email password
     */
    public function resetPassword()
    {
        try {
            $this->requirePermission('manage_internal_emails');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['email_id']);
            
            $emailId = (int) $_POST['email_id'];
            
            $email = $this->db->fetch("SELECT * FROM internal_emails WHERE id = ?", [$emailId]);
            if (!$email) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email not found']);
            }
            
            // Generate new password
            $newPassword = $this->emailGenerator->generateEmailPassword();
            
            // Update database
            $updated = $this->db->update('internal_emails', [
                'email_password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'password_changed_at' => date('Y-m-d H:i:s'),
                'password_changed_by' => $this->getCurrentUserId()
            ], ['id' => $emailId]);
            
            if ($updated) {
                // Update cPanel password (placeholder)
                // In production: $this->updateCPanelPassword($email['internal_email'], $newPassword);
                
                // Log activity
                $this->logEmailActivity($emailId, 'password_reset', 'Password reset by admin');
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Password reset successfully',
                    'new_password' => $newPassword
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to reset password'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error resetting password'
            ]);
        }
    }
    
    /**
     * Update email quota
     */
    public function updateQuota()
    {
        try {
            $this->requirePermission('manage_internal_emails');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['email_id', 'quota_mb']);
            
            $emailId = (int) $_POST['email_id'];
            $quotaMB = (int) $_POST['quota_mb'];
            
            if ($quotaMB < 100 || $quotaMB > 10240) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Quota must be between 100MB and 10GB'
                ]);
            }
            
            $email = $this->db->fetch("SELECT * FROM internal_emails WHERE id = ?", [$emailId]);
            if (!$email) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email not found']);
            }
            
            // Update database
            $updated = $this->db->update('internal_emails', [
                'email_quota_mb' => $quotaMB,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $emailId]);
            
            if ($updated) {
                // Update cPanel quota (placeholder)
                
                // Log activity
                $this->logEmailActivity($emailId, 'quota_update', "Quota updated to {$quotaMB}MB");
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Quota updated successfully',
                    'quota_mb' => $quotaMB
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update quota'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Quota update error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error updating quota'
            ]);
        }
    }
    
    /**
     * Setup email forwarding
     */
    public function setupForwarding()
    {
        try {
            $this->requirePermission('manage_internal_emails');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['email_id', 'forward_to']);
            
            $emailId = (int) $_POST['email_id'];
            $forwardTo = filter_var($_POST['forward_to'], FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($forwardTo, FILTER_VALIDATE_EMAIL)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid forwarding email address'
                ]);
            }
            
            $email = $this->db->fetch("SELECT * FROM internal_emails WHERE id = ?", [$emailId]);
            if (!$email) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email not found']);
            }
            
            // Setup forwarding
            $result = $this->emailGenerator->setupEmailForwarding($email['internal_email'], $forwardTo);
            
            if ($result) {
                // Log activity
                $this->logEmailActivity($emailId, 'forwarding_setup', "Forwarding setup to {$forwardTo}");
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Forwarding setup successfully',
                    'forward_to' => $forwardTo
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to setup forwarding'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Forwarding setup error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error setting up forwarding'
            ]);
        }
    }
    
    /**
     * Remove email forwarding
     */
    public function removeForwarding()
    {
        try {
            $this->requirePermission('manage_internal_emails');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['email_id']);
            
            $emailId = (int) $_POST['email_id'];
            
            $email = $this->db->fetch("SELECT * FROM internal_emails WHERE id = ?", [$emailId]);
            if (!$email) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email not found']);
            }
            
            // Remove forwarding
            $updated = $this->db->update('internal_emails', [
                'auto_forward_to' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $emailId]);
            
            if ($updated) {
                // Log activity
                $this->logEmailActivity($emailId, 'forwarding_removed', 'Email forwarding removed');
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Forwarding removed successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to remove forwarding'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Remove forwarding error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error removing forwarding'
            ]);
        }
    }
    
    /**
     * Deactivate email account
     */
    public function deactivate()
    {
        try {
            $this->requirePermission('manage_internal_emails');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['email_id']);
            
            $emailId = (int) $_POST['email_id'];
            $reason = $this->sanitizeInput($_POST['reason'] ?? '');
            
            $email = $this->db->fetch("SELECT * FROM internal_emails WHERE id = ?", [$emailId]);
            if (!$email) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email not found']);
            }
            
            // Deactivate
            $updated = $this->db->update('internal_emails', [
                'status' => 'inactive',
                'deactivated_at' => date('Y-m-d H:i:s'),
                'deactivated_by' => $this->getCurrentUserId(),
                'deactivation_reason' => $reason
            ], ['id' => $emailId]);
            
            if ($updated) {
                // Log activity
                $this->logEmailActivity($emailId, 'deactivated', "Deactivated: {$reason}");
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Email account deactivated successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to deactivate email account'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Deactivation error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error deactivating email'
            ]);
        }
    }
    
    /**
     * Reactivate email account
     */
    public function reactivate()
    {
        try {
            $this->requirePermission('manage_internal_emails');
            $this->validateCsrfToken();
            $this->validateRequiredFields(['email_id']);
            
            $emailId = (int) $_POST['email_id'];
            
            $email = $this->db->fetch("SELECT * FROM internal_emails WHERE id = ?", [$emailId]);
            if (!$email) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email not found']);
            }
            
            // Reactivate
            $updated = $this->db->update('internal_emails', [
                'status' => 'active',
                'reactivated_at' => date('Y-m-d H:i:s'),
                'reactivated_by' => $this->getCurrentUserId()
            ], ['id' => $emailId]);
            
            if ($updated) {
                // Log activity
                $this->logEmailActivity($emailId, 'reactivated', 'Email account reactivated');
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Email account reactivated successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to reactivate email account'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Reactivation error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error reactivating email'
            ]);
        }
    }
    
    /**
     * Get email statistics
     */
    protected function getEmailStatistics(): array
    {
        $stats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_emails,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_emails,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_emails,
                SUM(CASE WHEN status = 'pending_creation' THEN 1 ELSE 0 END) as pending_emails,
                SUM(CASE WHEN email_type = 'primary' THEN 1 ELSE 0 END) as primary_emails,
                SUM(CASE WHEN email_type = 'alias' THEN 1 ELSE 0 END) as alias_emails,
                SUM(email_quota_mb) as total_quota_mb,
                AVG(email_quota_mb) as avg_quota_mb
            FROM internal_emails
        ");
        
        return $stats ?: [];
    }
    
    /**
     * Get email usage stats (placeholder)
     */
    protected function getEmailUsageStats(string $email): array
    {
        // In production, query cPanel API
        return [
            'disk_used_mb' => rand(50, 500),
            'disk_quota_mb' => 1024,
            'disk_percent_used' => rand(5, 50),
            'emails_sent_today' => rand(10, 100),
            'emails_received_today' => rand(20, 200),
            'last_login' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
        ];
    }
    
    /**
     * Get email activity log
     */
    protected function getEmailActivityLog(int $emailId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM email_activity_log 
             WHERE email_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$emailId, $limit]
        ) ?: [];
    }
    
    /**
     * Log email activity
     */
    protected function logEmailActivity(int $emailId, string $action, string $description): void
    {
        $this->db->insert('email_activity_log', [
            'email_id' => $emailId,
            'action' => $action,
            'description' => $description,
            'performed_by' => $this->getCurrentUserId(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get user hierarchy data
     */
    protected function getUserHierarchyData(array $user): array
    {
        // Determine hierarchy level
        if ($user['gurmu_id']) {
            $gurmu = $this->db->fetch("SELECT * FROM gurmus WHERE id = ?", [$user['gurmu_id']]);
            return array_merge($gurmu ?: [], ['level' => 'gurmu']);
        } elseif ($user['gamta_id']) {
            $gamta = $this->db->fetch("SELECT * FROM gamtas WHERE id = ?", [$user['gamta_id']]);
            return array_merge($gamta ?: [], ['level' => 'gamta']);
        } elseif ($user['godina_id']) {
            $godina = $this->db->fetch("SELECT * FROM godinas WHERE id = ?", [$user['godina_id']]);
            return array_merge($godina ?: [], ['level' => 'godina']);
        } else {
            return ['level' => 'global'];
        }
    }
    
    /**
     * Helper methods
     */
    protected function validateRequiredFields(array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                throw new Exception("Missing required field: {$field}");
            }
        }
    }
    
    protected function sanitizeInput(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    protected function getCurrentUserId(): int
    {
        $user = auth_user();
        return $user['id'] ?? 0;
    }
    
    protected function validateCsrfToken(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
    }
}
