<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Utils\Database;

/**
 * User Email Management Controller
 * Handles internal email generation and management for system administrators
 */
class UserEmailController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireSystemAdmin();
    }
    
    /**
     * User Email Management Dashboard
     */
    public function index()
    {
        $db = Database::getInstance();
        
        // Get users with their email status
        $users = $db->fetchAll("
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email as personal_email,
                u.internal_email,
                u.status,
                u.account_type,
                ua.level_scope,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions,
                ie.internal_email as table_internal_email,
                ie.status as internal_email_status,
                ie.cpanel_account_created,
                ie.created_at as email_created_at
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            LEFT JOIN internal_emails ie ON u.id = ie.user_id
            WHERE u.status IN ('active', 'pending')
            GROUP BY u.id
            ORDER BY u.id
        ");
        
        // Generate statistics
        $stats = [
            'total_users' => count($users),
            'has_internal_email' => count(array_filter($users, fn($u) => !empty($u['internal_email']))),
            'missing_internal_email' => count(array_filter($users, fn($u) => empty($u['internal_email']))),
            'has_assignments' => count(array_filter($users, fn($u) => !empty($u['positions']))),
            'cpanel_accounts' => count(array_filter($users, fn($u) => $u['cpanel_account_created'] == 1))
        ];
        
        return $this->render('admin.user_email_management', [
            'title' => 'User Email Management',
            'users' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * Generate missing internal emails for users
     */
    public function generateMissing()
    {
        try {
            require_once __DIR__ . '/../../scripts/analyze-user-emails.php';
            
            $analyzer = new \App\Scripts\UserEmailAnalyzer();
            
            // Get users missing emails
            $missingEmails = $this->getUsersMissingEmails();
            
            if (empty($missingEmails)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'All users already have internal emails',
                    'generated' => 0
                ]);
            }
            
            $generated = 0;
            $errors = [];
            
            foreach ($missingEmails as $issue) {
                $result = $this->generateEmailForUser($issue['user_id']);
                
                if ($result['success']) {
                    $generated++;
                } else {
                    $errors[] = "User {$issue['user_id']}: {$result['message']}";
                }
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => "Successfully generated {$generated} internal emails",
                'generated' => $generated,
                'errors' => $errors
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error generating emails: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Regenerate internal email for specific user
     */
    public function regenerate($userId)
    {
        try {
            $db = Database::getInstance();
            
            // Verify user exists
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }
            
            // Delete existing internal email
            $db->query("DELETE FROM internal_emails WHERE user_id = ?", [$userId]);
            $db->query("UPDATE users SET internal_email = NULL WHERE id = ?", [$userId]);
            
            // Generate new email
            $result = $this->generateEmailForUser($userId);
            
            return $this->jsonResponse($result);
            
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error regenerating email: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get users missing internal emails
     */
    private function getUsersMissingEmails()
    {
        $db = Database::getInstance();
        
        return $db->fetchAll("
            SELECT 
                u.id as user_id,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                u.email,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions,
                ua.level_scope
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            WHERE u.status IN ('active', 'pending')
            AND (u.internal_email IS NULL OR u.internal_email = '')
            AND ua.user_id IS NOT NULL
            GROUP BY u.id
            ORDER BY u.id
        ");
    }
    
    /**
     * Generate email for a specific user
     */
    private function generateEmailForUser($userId)
    {
        try {
            $db = Database::getInstance();
            
            // Get user assignment details
            $user = $db->fetchAll("
                SELECT u.*, ua.* FROM users u 
                LEFT JOIN user_assignments ua ON u.id = ua.user_id 
                WHERE u.id = ? AND ua.status = 'active' 
                LIMIT 1",
                [$userId]
            );
            
            if (empty($user)) {
                return ['success' => false, 'message' => 'User not found or no active assignments'];
            }
            
            $userData = $user[0];
            
            // Use the InternalEmailGenerator service
            require_once __DIR__ . '/../Services/InternalEmailGenerator.php';
            $emailGenerator = new \App\Services\InternalEmailGenerator();
            
            $emailData = [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'level_scope' => $userData['level_scope'],
                'global_id' => $userData['global_id'],
                'godina_id' => $userData['godina_id'],
                'gamta_id' => $userData['gamta_id'],
                'gurmu_id' => $userData['gurmu_id'],
                'position_id' => $userData['position_id']
            ];
            
            $result = $emailGenerator->generateInternalEmail($emailData);
            
            if ($result['success']) {
                // Insert into internal_emails table
                $db->insert('internal_emails', [
                    'user_id' => $userId,
                    'internal_email' => $result['email'],
                    'email_type' => 'primary',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'activated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Update users table
                $db->query("UPDATE users SET internal_email = ? WHERE id = ?", [$result['email'], $userId]);
                
                return [
                    'success' => true,
                    'message' => 'Internal email generated successfully',
                    'email' => $result['email']
                ];
            } else {
                return $result;
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if user is system admin
     */
    private function requireSystemAdmin()
    {
        $user = auth_user();
        if (!$user || !in_array($user['role'], ['system_admin', 'super_admin'])) {
            $this->redirect('/dashboard');
        }
    }
}