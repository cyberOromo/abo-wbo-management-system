<?php
/**
 * Email Management Methods
 * 
 * Standalone script for managing internal email accounts
 * Can be run from CLI or included in other scripts
 * 
 * Usage:
 * - php email-management-methods.php create user@abo-wbo.org password123
 * - php email-management-methods.php list
 * - php email-management-methods.php delete user@abo-wbo.org
 * - php email-management-methods.php reset-password user@abo-wbo.org newpassword123
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Utils\Database;

class EmailManagementMethods
{
    protected $db;
    protected $domain = 'j-abo-wbo.org';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create internal email account
     */
    public function createEmail(string $email, string $password, array $options = []): array
    {
        try {
            // Validate email format
            if (!$this->validateEmailFormat($email)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if email already exists
            if ($this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Extract local part and validate
            list($localPart, $domain) = explode('@', $email);
            
            // Get user ID if email is linked to a user
            $userId = $options['user_id'] ?? $this->getUserIdByEmail($email);
            
            // Prepare email data
            $emailData = [
                'user_id' => $userId,
                'internal_email' => $email,
                'email_type' => $options['email_type'] ?? 'primary',
                'email_quota_mb' => $options['quota_mb'] ?? 1024,
                'email_password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'auto_forward_to' => $options['forward_to'] ?? null,
                'status' => 'pending_creation',
                'cpanel_account_created' => false,
                'creation_metadata' => json_encode([
                    'created_method' => 'manual',
                    'created_by' => $options['created_by'] ?? 'system',
                    'created_at' => date('Y-m-d H:i:s')
                ]),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Insert into database
            $emailId = $this->db->insert('internal_emails', $emailData);
            
            if ($emailId) {
                // Simulate cPanel account creation (in production, this would call cPanel API)
                $cpanelResult = $this->createCPanelAccount($email, $password, $emailData['email_quota_mb']);
                
                if ($cpanelResult['success']) {
                    // Update status
                    $this->db->update('internal_emails', [
                        'status' => 'active',
                        'cpanel_account_created' => true,
                        'activated_at' => date('Y-m-d H:i:s')
                    ], ['id' => $emailId]);
                    
                    return [
                        'success' => true,
                        'message' => 'Email account created successfully',
                        'email_id' => $emailId,
                        'email' => $email
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Database record created but cPanel account creation failed',
                        'error' => $cpanelResult['error']
                    ];
                }
            } else {
                return ['success' => false, 'message' => 'Failed to create email record'];
            }
            
        } catch (Exception $e) {
            error_log("Email creation error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * List all internal email accounts
     */
    public function listEmails(array $filters = []): array
    {
        try {
            $sql = "SELECT ie.*, u.first_name, u.last_name, u.email as personal_email, u.role
                    FROM internal_emails ie
                    LEFT JOIN users u ON ie.user_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $sql .= " AND ie.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND ie.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['email_type'])) {
                $sql .= " AND ie.email_type = ?";
                $params[] = $filters['email_type'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (ie.internal_email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY ie.created_at DESC";
            
            // Add pagination
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int) $filters['limit'];
                
                if (!empty($filters['offset'])) {
                    $sql .= " OFFSET ?";
                    $params[] = (int) $filters['offset'];
                }
            }
            
            $emails = $this->db->fetchAll($sql, $params);
            
            return [
                'success' => true,
                'emails' => $emails,
                'count' => count($emails)
            ];
            
        } catch (Exception $e) {
            error_log("List emails error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get email account details
     */
    public function getEmailDetails(string $email): array
    {
        try {
            $emailData = $this->db->fetch(
                "SELECT ie.*, u.first_name, u.last_name, u.email as personal_email, u.role, u.phone
                 FROM internal_emails ie
                 LEFT JOIN users u ON ie.user_id = u.id
                 WHERE ie.internal_email = ?",
                [$email]
            );
            
            if ($emailData) {
                // Get usage statistics
                $emailData['usage_stats'] = $this->getEmailUsageStats($email);
                
                return [
                    'success' => true,
                    'email' => $emailData
                ];
            } else {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
        } catch (Exception $e) {
            error_log("Get email details error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete internal email account
     */
    public function deleteEmail(string $email, bool $permanent = false): array
    {
        try {
            $emailData = $this->db->fetch(
                "SELECT * FROM internal_emails WHERE internal_email = ?",
                [$email]
            );
            
            if (!$emailData) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            if ($permanent) {
                // Permanently delete from database
                $deleted = $this->db->delete('internal_emails', ['internal_email' => $email]);
                
                // Delete cPanel account
                $cpanelResult = $this->deleteCPanelAccount($email);
                
                if ($deleted) {
                    return [
                        'success' => true,
                        'message' => 'Email account permanently deleted',
                        'cpanel_deleted' => $cpanelResult['success']
                    ];
                } else {
                    return ['success' => false, 'message' => 'Failed to delete email'];
                }
            } else {
                // Soft delete - deactivate
                $updated = $this->db->update('internal_emails', [
                    'status' => 'inactive',
                    'deactivated_at' => date('Y-m-d H:i:s')
                ], ['internal_email' => $email]);
                
                if ($updated) {
                    // Suspend cPanel account
                    $cpanelResult = $this->suspendCPanelAccount($email);
                    
                    return [
                        'success' => true,
                        'message' => 'Email account deactivated',
                        'cpanel_suspended' => $cpanelResult['success']
                    ];
                } else {
                    return ['success' => false, 'message' => 'Failed to deactivate email'];
                }
            }
            
        } catch (Exception $e) {
            error_log("Delete email error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Reset email password
     */
    public function resetPassword(string $email, string $newPassword): array
    {
        try {
            if (!$this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            // Update database
            $updated = $this->db->update('internal_emails', [
                'email_password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'password_changed_at' => date('Y-m-d H:i:s')
            ], ['internal_email' => $email]);
            
            if ($updated) {
                // Update cPanel password
                $cpanelResult = $this->updateCPanelPassword($email, $newPassword);
                
                return [
                    'success' => true,
                    'message' => 'Password reset successfully',
                    'cpanel_updated' => $cpanelResult['success']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to reset password'];
            }
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Update email quota
     */
    public function updateQuota(string $email, int $quotaMB): array
    {
        try {
            if (!$this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            // Update database
            $updated = $this->db->update('internal_emails', [
                'email_quota_mb' => $quotaMB,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['internal_email' => $email]);
            
            if ($updated) {
                // Update cPanel quota
                $cpanelResult = $this->updateCPanelQuota($email, $quotaMB);
                
                return [
                    'success' => true,
                    'message' => 'Quota updated successfully',
                    'quota_mb' => $quotaMB,
                    'cpanel_updated' => $cpanelResult['success']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to update quota'];
            }
            
        } catch (Exception $e) {
            error_log("Update quota error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Setup email forwarding
     */
    public function setupForwarding(string $email, string $forwardTo): array
    {
        try {
            if (!$this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            if (!filter_var($forwardTo, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid forwarding email address'];
            }
            
            // Update database
            $updated = $this->db->update('internal_emails', [
                'auto_forward_to' => $forwardTo,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['internal_email' => $email]);
            
            if ($updated) {
                // Setup cPanel forwarding
                $cpanelResult = $this->setupCPanelForwarding($email, $forwardTo);
                
                return [
                    'success' => true,
                    'message' => 'Forwarding setup successfully',
                    'forward_to' => $forwardTo,
                    'cpanel_configured' => $cpanelResult['success']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to setup forwarding'];
            }
            
        } catch (Exception $e) {
            error_log("Setup forwarding error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Remove email forwarding
     */
    public function removeForwarding(string $email): array
    {
        try {
            if (!$this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            // Update database
            $updated = $this->db->update('internal_emails', [
                'auto_forward_to' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['internal_email' => $email]);
            
            if ($updated) {
                // Remove cPanel forwarding
                $cpanelResult = $this->removeCPanelForwarding($email);
                
                return [
                    'success' => true,
                    'message' => 'Forwarding removed successfully',
                    'cpanel_updated' => $cpanelResult['success']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to remove forwarding'];
            }
            
        } catch (Exception $e) {
            error_log("Remove forwarding error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get email usage statistics
     */
    public function getEmailUsageStats(string $email): array
    {
        // In production, this would query cPanel API for actual usage
        // For now, return simulated data
        return [
            'disk_used_mb' => rand(50, 500),
            'disk_quota_mb' => 1024,
            'disk_percent_used' => rand(5, 50),
            'total_emails_sent' => rand(100, 1000),
            'total_emails_received' => rand(200, 2000),
            'last_login' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
        ];
    }
    
    /**
     * Bulk operations
     */
    public function bulkCreateEmails(array $emailsData): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($emailsData)
        ];
        
        foreach ($emailsData as $emailData) {
            $result = $this->createEmail(
                $emailData['email'],
                $emailData['password'],
                $emailData['options'] ?? []
            );
            
            if ($result['success']) {
                $results['success'][] = $emailData['email'];
            } else {
                $results['failed'][] = [
                    'email' => $emailData['email'],
                    'error' => $result['message']
                ];
            }
        }
        
        return $results;
    }
    
    public function bulkUpdateQuota(array $emails, int $quotaMB): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($emails)
        ];
        
        foreach ($emails as $email) {
            $result = $this->updateQuota($email, $quotaMB);
            
            if ($result['success']) {
                $results['success'][] = $email;
            } else {
                $results['failed'][] = [
                    'email' => $email,
                    'error' => $result['message']
                ];
            }
        }
        
        return $results;
    }
    
    // ========================================
    // CPANEL API METHODS (Placeholder implementations)
    // ========================================
    
    protected function createCPanelAccount(string $email, string $password, int $quotaMB): array
    {
        // In production, this would call cPanel API
        // For now, simulate success
        error_log("Creating cPanel account for: $email");
        return ['success' => true, 'message' => 'Account created (simulated)'];
    }
    
    protected function deleteCPanelAccount(string $email): array
    {
        error_log("Deleting cPanel account for: $email");
        return ['success' => true, 'message' => 'Account deleted (simulated)'];
    }
    
    protected function suspendCPanelAccount(string $email): array
    {
        error_log("Suspending cPanel account for: $email");
        return ['success' => true, 'message' => 'Account suspended (simulated)'];
    }
    
    protected function updateCPanelPassword(string $email, string $newPassword): array
    {
        error_log("Updating cPanel password for: $email");
        return ['success' => true, 'message' => 'Password updated (simulated)'];
    }
    
    protected function updateCPanelQuota(string $email, int $quotaMB): array
    {
        error_log("Updating cPanel quota for: $email to {$quotaMB}MB");
        return ['success' => true, 'message' => 'Quota updated (simulated)'];
    }
    
    protected function setupCPanelForwarding(string $email, string $forwardTo): array
    {
        error_log("Setting up cPanel forwarding for: $email to $forwardTo");
        return ['success' => true, 'message' => 'Forwarding configured (simulated)'];
    }
    
    protected function removeCPanelForwarding(string $email): array
    {
        error_log("Removing cPanel forwarding for: $email");
        return ['success' => true, 'message' => 'Forwarding removed (simulated)'];
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    protected function emailExists(string $email): bool
    {
        $result = $this->db->fetch(
            "SELECT id FROM internal_emails WHERE internal_email = ?",
            [$email]
        );
        return $result !== null;
    }
    
    protected function validateEmailFormat(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        list($localPart, $domain) = explode('@', $email);
        return $domain === $this->domain;
    }
    
    protected function getUserIdByEmail(string $email): ?int
    {
        $user = $this->db->fetch(
            "SELECT id FROM users WHERE email = ? OR internal_email = ?",
            [$email, $email]
        );
        return $user ? $user['id'] : null;
    }
}

// ========================================
// CLI EXECUTION
// ========================================

if (php_sapi_name() === 'cli') {
    $manager = new EmailManagementMethods();
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'create':
            if (!isset($argv[2], $argv[3])) {
                echo "Usage: php email-management-methods.php create email@abo-wbo.org password\n";
                exit(1);
            }
            $result = $manager->createEmail($argv[2], $argv[3]);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'list':
            $filters = [];
            if (isset($argv[2])) {
                $filters['search'] = $argv[2];
            }
            $result = $manager->listEmails($filters);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'details':
            if (!isset($argv[2])) {
                echo "Usage: php email-management-methods.php details email@abo-wbo.org\n";
                exit(1);
            }
            $result = $manager->getEmailDetails($argv[2]);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'delete':
            if (!isset($argv[2])) {
                echo "Usage: php email-management-methods.php delete email@abo-wbo.org [permanent]\n";
                exit(1);
            }
            $permanent = isset($argv[3]) && $argv[3] === 'permanent';
            $result = $manager->deleteEmail($argv[2], $permanent);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'reset-password':
            if (!isset($argv[2], $argv[3])) {
                echo "Usage: php email-management-methods.php reset-password email@abo-wbo.org newpassword\n";
                exit(1);
            }
            $result = $manager->resetPassword($argv[2], $argv[3]);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'update-quota':
            if (!isset($argv[2], $argv[3])) {
                echo "Usage: php email-management-methods.php update-quota email@abo-wbo.org quotaMB\n";
                exit(1);
            }
            $result = $manager->updateQuota($argv[2], (int)$argv[3]);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'setup-forwarding':
            if (!isset($argv[2], $argv[3])) {
                echo "Usage: php email-management-methods.php setup-forwarding email@abo-wbo.org forward-to@example.com\n";
                exit(1);
            }
            $result = $manager->setupForwarding($argv[2], $argv[3]);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'help':
        default:
            echo "Email Management Methods - ABO-WBO System\n\n";
            echo "Available commands:\n";
            echo "  create email@abo-wbo.org password    - Create new email account\n";
            echo "  list [search]                         - List all email accounts\n";
            echo "  details email@abo-wbo.org            - Get email account details\n";
            echo "  delete email@abo-wbo.org [permanent] - Delete/deactivate email account\n";
            echo "  reset-password email password        - Reset email password\n";
            echo "  update-quota email quotaMB           - Update email quota\n";
            echo "  setup-forwarding email forward-to    - Setup email forwarding\n";
            echo "  help                                 - Show this help message\n";
            break;
    }
}
