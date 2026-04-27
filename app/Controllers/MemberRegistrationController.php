<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Gurmu;
use App\Models\Gamta;
use App\Models\UserAssignment;
use App\Services\InternalEmailGenerator;
use App\Utils\Database;
use Exception;

/**
 * Member Registration Controller
 * Handles member registration with hierarchy-based restrictions
 * - System Admin: Can register members for any Gurmu
 * - Gurmu Leaders: Can only register members for their own Gurmu
 */
class MemberRegistrationController extends Controller
{
    protected $userModel;
    protected $gurmuModel;
    protected $gamtaModel;
    protected $db;
    protected $emailGenerator;
    
    public function __construct()
    {
        $this->requireAuth();
        
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->gurmuModel = new Gurmu();
        $this->gamtaModel = new Gamta();
        $this->emailGenerator = new InternalEmailGenerator();
    }
    
    /**
     * Show member registration form
     */
    public function index()
    {
        $currentUser = auth_user();
        $allowedGurmus = $this->resolveAllowedGurmus($currentUser);
        $recentRegistrations = $this->getRecentRegistrations($currentUser);
        
        return $this->render('members.registration', [
            'title' => 'Member Registration',
            'allowed_gurmus' => $allowedGurmus,
            'recent_registrations' => $recentRegistrations,
            'current_user' => $currentUser
        ]);
    }
    
    /**
     * Handle member registration
     */
    public function register()
    {
        try {
            $this->validateCSRF();
            $currentUser = auth_user();
            
            // Validate input data
            $data = $this->validateRegistrationData();
            
            // Check if current user can register members for the selected Gurmu
            $this->validateGurmuPermission($currentUser, $data['gurmu_id']);
            
            // Check if email already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                throw new Exception('A user with this email already exists.');
            }

            $this->db->beginTransaction();
            
            // Create the new member
            $newMemberId = $this->createMember($data, $currentUser['id']);
            
            // Log the registration activity
            $this->logRegistrationActivity($currentUser['id'], $newMemberId, $data['gurmu_id']);

            $this->db->commit();
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Member registered successfully',
                'member_id' => $newMemberId
            ]);
            
        } catch (Exception $e) {
            if ($this->db->getPdo()->inTransaction()) {
                $this->db->rollback();
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Get member registration statistics
     */
    public function stats()
    {
        $this->requireAuth();
        $currentUser = auth_user();
        
        $stats = $this->getMemberRegistrationStats($currentUser);
        
        return $this->jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function getStats()
    {
        return $this->stats();
    }

    public function getAllowedGurmus($currentUser = null)
    {
        if (is_array($currentUser)) {
            return $this->resolveAllowedGurmus($currentUser);
        }

        $this->requireAuth();

        return $this->jsonResponse([
            'success' => true,
            'data' => $this->resolveAllowedGurmus(auth_user())
        ]);
    }
    
    /**
     * HELPER METHODS
     */
    
    /**
     * Get Gurmus that the current user can register members for
     */
    private function resolveAllowedGurmus($currentUser)
    {
        // Admin-level users can access all Gurmus.
        if ($this->isRegistrationAdmin($currentUser)) {
            return $this->db->fetchAll(
                "SELECT g.*, gm.name as gamta_name, god.name as godina_name
                 FROM gurmus g
                 LEFT JOIN gamtas gm ON g.gamta_id = gm.id
                 LEFT JOIN godinas god ON gm.godina_id = god.id
                 WHERE g.status = 'active'
                 ORDER BY gm.name, g.name"
            );
        }
        
        // Check if user has a leadership position in any Gurmu
        $allowedGurmus = [];
        
        // Get user's active assignments
        $assignments = $this->db->fetchAll("
            SELECT ua.*, p.key_name as position_key, p.name as position_name
            FROM user_assignments ua
            JOIN positions p ON ua.position_id = p.id
            WHERE ua.user_id = ? AND ua.status = 'active'
        ", [$currentUser['id']]);
        
        foreach ($assignments as $assignment) {
            // Leaders at Gurmu level can register members for their Gurmu
            if ($assignment['level_scope'] === 'gurmu' && !empty($assignment['gurmu_id'])) {
                $gurmu = $this->db->fetch(
                    "SELECT g.*, gm.name as gamta_name, god.name as godina_name
                     FROM gurmus g
                     LEFT JOIN gamtas gm ON g.gamta_id = gm.id
                     LEFT JOIN godinas god ON gm.godina_id = god.id
                     WHERE g.id = ? AND g.status = 'active'",
                    [$assignment['gurmu_id']]
                );
                if ($gurmu) {
                    $allowedGurmus[] = $gurmu;
                }
            }
            // Leaders at Gamta level can register members for all Gurmus in their Gamta
            else if ($assignment['level_scope'] === 'gamta' && !empty($assignment['gamta_id'])) {
                $gurmus = $this->db->fetchAll(
                    "SELECT g.*, gm.name as gamta_name, god.name as godina_name
                     FROM gurmus g
                     LEFT JOIN gamtas gm ON g.gamta_id = gm.id
                     LEFT JOIN godinas god ON gm.godina_id = god.id
                     WHERE g.gamta_id = ? AND g.status = 'active'
                     ORDER BY g.name",
                    [$assignment['gamta_id']]
                );
                $allowedGurmus = array_merge($allowedGurmus, $gurmus);
            }
            // Leaders at Godina level can register members for all Gurmus in their Godina
            else if ($assignment['level_scope'] === 'godina' && !empty($assignment['godina_id'])) {
                $gurmus = $this->db->fetchAll("
                    SELECT g.*, gm.name as gamta_name, god.name as godina_name
                    FROM gurmus g
                    JOIN gamtas gm ON g.gamta_id = gm.id
                    JOIN godinas god ON gm.godina_id = god.id
                    WHERE gm.godina_id = ? AND g.status = 'active'
                    ORDER BY gm.name, g.name
                ", [$assignment['godina_id']]);
                $allowedGurmus = array_merge($allowedGurmus, $gurmus);
            }
        }
        
        // Remove duplicates
        $uniqueGurmus = [];
        $seen = [];
        foreach ($allowedGurmus as $gurmu) {
            if (!isset($seen[$gurmu['id']])) {
                $uniqueGurmus[] = $gurmu;
                $seen[$gurmu['id']] = true;
            }
        }
        
        return $uniqueGurmus;
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistrationData()
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'gurmu_id' => 'required|integer|exists:gurmus,id',
            'language_preference' => 'nullable|in:en,om',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'notes' => 'nullable|string'
        ];
        
        return $this->validate($rules);
    }
    
    /**
     * Validate if current user can register members for the selected Gurmu
     */
    private function validateGurmuPermission($currentUser, $gurmuId)
    {
        // Admin-level users can register for any Gurmu.
        if ($this->isRegistrationAdmin($currentUser)) {
            return true;
        }
        
        $allowedGurmus = $this->resolveAllowedGurmus($currentUser);
        $allowed = false;
        
        foreach ($allowedGurmus as $gurmu) {
            if ($gurmu['id'] == $gurmuId) {
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            throw new Exception('You do not have permission to register members for this Gurmu.');
        }
        
        return true;
    }
    
    /**
     * Create new member
     */
    private function createMember($data, $createdBy)
    {
        // Generate temporary password
        $tempPassword = $this->generateTempPassword();
        
        $memberData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password_hash' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'birth_date' => $data['date_of_birth'] ?? null,
            'gurmu_id' => $data['gurmu_id'],
            'role' => 'member',
            'language' => $data['language_preference'] ?? 'en',
            'status' => 'active',
            'address' => $data['address'] ?? null,
            'emergency_contact' => !empty($data['emergency_contact'])
                ? json_encode(['details' => $data['emergency_contact']])
                : null,
            'registration_source' => 'admin_created',
            'account_type' => 'internal_only',
            'onboarding_completed' => 0,
            'metadata' => json_encode([
                'middle_name' => $data['middle_name'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'registration_notes' => $data['notes'] ?? null,
                'registered_by' => $createdBy,
                'temp_password' => $tempPassword, // Store for initial email
                'requires_password_change' => true
            ])
        ];

        $memberId = $this->db->insert('users', $memberData);
        $internalEmail = $this->provisionMemberInternalEmail($memberId, $data, $createdBy, $tempPassword);
        
        // Send welcome email with temporary password
        $this->sendWelcomeEmail($data['email'], $data['first_name'], $tempPassword, $internalEmail);
        
        return $memberId;
    }

    private function provisionMemberInternalEmail($memberId, $data, $createdBy, $tempPassword)
    {
        $hierarchyData = $this->db->fetch(
            "SELECT g.id, g.code, g.name, 'gurmu' as level, gm.id as gamta_id, gm.name as gamta_name,
                    god.id as godina_id, god.name as godina_name
             FROM gurmus g
             LEFT JOIN gamtas gm ON g.gamta_id = gm.id
             LEFT JOIN godinas god ON gm.godina_id = god.id
             WHERE g.id = ?",
            [$data['gurmu_id']]
        );

        if (!$hierarchyData) {
            throw new Exception('Unable to resolve Gurmu hierarchy for internal email generation.');
        }

        $internalEmail = $this->emailGenerator->generateInternalEmail([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name']
        ], null, $hierarchyData);

        $this->emailGenerator->createInternalEmailRecord($memberId, $internalEmail, [
            'email_type' => 'primary',
            'quota_mb' => 1024,
            'created_by' => $createdBy,
            'creation_method' => 'member_registration',
            'hierarchy_data' => $hierarchyData,
            'position_data' => ['key_name' => 'member']
        ]);

        $this->emailGenerator->createCPanelEmailAccount($internalEmail, $tempPassword, 1024);

        $this->db->update('users', [
            'internal_email' => $internalEmail,
            'internal_account_created_at' => date('Y-m-d H:i:s'),
            'internal_credentials_sent_at' => date('Y-m-d H:i:s')
        ], ['id' => $memberId]);

        return $internalEmail;
    }
    
    /**
     * Generate temporary password
     */
    private function generateTempPassword()
    {
        $length = 10;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Send welcome email to new member
     */
    private function sendWelcomeEmail($email, $firstName, $tempPassword, $internalEmail = null)
    {
        // TODO: Implement email sending
        // For now, log the credentials
        error_log("Welcome email for {$email}: Internal email {$internalEmail}; Temp password: {$tempPassword}");
    }
    
    /**
     * Log registration activity
     */
    private function logRegistrationActivity($registrarId, $newMemberId, $gurmuId)
    {
        if ($this->memberTableExists('activity_logs')) {
            $this->db->insert('activity_logs', [
                'user_id' => $registrarId,
                'action' => 'member_registration',
                'description' => "Registered new member (ID: {$newMemberId}) for Gurmu ID: {$gurmuId}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            return;
        }

        if ($this->memberTableExists('audit_logs')) {
            $this->db->insert('audit_logs', [
                'user_id' => $registrarId,
                'action' => 'member_registration',
                'table_name' => 'users',
                'record_id' => $newMemberId,
                'new_values' => json_encode(['gurmu_id' => $gurmuId]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Get recent registrations for current user's scope
     */
    private function getRecentRegistrations($currentUser)
    {
        if ($this->isRegistrationAdmin($currentUser)) {
            // Admin sees all recent registrations in the last 30 days.
            $query = "
                SELECT u.*, g.name as gurmu_name, gm.name as gamta_name, god.name as godina_name,
                       'System' as registered_by_name
                FROM users u
                LEFT JOIN gurmus g ON u.gurmu_id = g.id
                LEFT JOIN gamtas gm ON g.gamta_id = gm.id
                LEFT JOIN godinas god ON gm.godina_id = god.id
                WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY u.created_at DESC
                LIMIT 20
            ";
            return $this->db->fetchAll($query);
        } else {
            // Leaders see recent registrations in Gurmus they are allowed to manage.
            $allowedGurmus = $this->resolveAllowedGurmus($currentUser);
            $gurmuIds = array_column($allowedGurmus, 'id');

            if (empty($gurmuIds)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($gurmuIds), '?'));
            $query = "
                SELECT u.*, g.name as gurmu_name, gm.name as gamta_name, god.name as godina_name
                FROM users u
                LEFT JOIN gurmus g ON u.gurmu_id = g.id
                LEFT JOIN gamtas gm ON g.gamta_id = gm.id
                LEFT JOIN godinas god ON gm.godina_id = god.id
                WHERE u.gurmu_id IN ({$placeholders})
                  AND u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY u.created_at DESC
                LIMIT 20
            ";
            return $this->db->fetchAll($query, $gurmuIds);
        }
    }
    
    /**
     * Get member registration statistics
     */
    private function getMemberRegistrationStats($currentUser)
    {
        $allowedGurmus = $this->resolveAllowedGurmus($currentUser);
        $gurmuIds = array_column($allowedGurmus, 'id');
        
        if (empty($gurmuIds)) {
            return [
                'total_members' => 0,
                'this_month' => 0,
                'this_week' => 0,
                'today' => 0
            ];
        }
        
        $placeholders = implode(',', array_fill(0, count($gurmuIds), '?'));
        
        // Total members in allowed Gurmus
        $totalQuery = "SELECT COUNT(*) as count FROM users WHERE gurmu_id IN ({$placeholders}) AND status = 'active'";
        $total = $this->db->fetch($totalQuery, $gurmuIds)['count'];
        
        // This month
        $monthQuery = "SELECT COUNT(*) as count FROM users WHERE gurmu_id IN ({$placeholders}) AND status = 'active' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $thisMonth = $this->db->fetch($monthQuery, $gurmuIds)['count'];
        
        // This week
        $weekQuery = "SELECT COUNT(*) as count FROM users WHERE gurmu_id IN ({$placeholders}) AND status = 'active' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        $thisWeek = $this->db->fetch($weekQuery, $gurmuIds)['count'];
        
        // Today
        $todayQuery = "SELECT COUNT(*) as count FROM users WHERE gurmu_id IN ({$placeholders}) AND status = 'active' AND DATE(created_at) = CURDATE()";
        $today = $this->db->fetch($todayQuery, $gurmuIds)['count'];
        
        return [
            'total_members' => $total,
            'this_month' => $thisMonth,
            'this_week' => $thisWeek,
            'today' => $today
        ];
    }

    private function isRegistrationAdmin($currentUser)
    {
        return in_array($currentUser['role'] ?? '', ['admin', 'system_admin', 'super_admin']);
    }
    
    /**
     * Validate request data
     */
    protected function validate(array $rules, array $messages = []): array
    {
        $data = $_POST;
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rulesList = explode('|', $rule);
            $value = $data[$field] ?? null;
            
            foreach ($rulesList as $singleRule) {
                if ($singleRule === 'required' && empty($value)) {
                    $errors[$field] = ucfirst($field) . ' is required';
                    break;
                }
                
                if (strpos($singleRule, 'max:') === 0 && !empty($value)) {
                    $max = intval(substr($singleRule, 4));
                    if (strlen($value) > $max) {
                        $errors[$field] = ucfirst($field) . " cannot exceed {$max} characters";
                    }
                }
                
                if ($singleRule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email address';
                }
                
                if ($singleRule === 'date' && !empty($value) && !strtotime($value)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid date';
                }
                
                if (strpos($singleRule, 'in:') === 0 && !empty($value)) {
                    $allowedValues = explode(',', substr($singleRule, 3));
                    if (!in_array($value, $allowedValues)) {
                        $errors[$field] = ucfirst($field) . ' must be one of: ' . implode(', ', $allowedValues);
                    }
                }
                
                if (strpos($singleRule, 'exists:') === 0 && !empty($value)) {
                    $parts = explode(',', substr($singleRule, 7));
                    $table = $parts[0];
                    $column = $parts[1] ?? 'id';
                    
                    $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
                    $result = $this->db->fetch($query, [$value]);
                    if ($result['count'] === 0) {
                        $errors[$field] = ucfirst($field) . ' does not exist';
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        return array_intersect_key($data, $rules);
    }
    
    /**
     * Validate CSRF token
     */
    private function validateCSRF()
    {
        if (function_exists('csrf_verify')) {
            if (!csrf_verify($_POST['_token'] ?? '')) {
                throw new Exception('Invalid CSRF token');
            }

            return;
        }

        if (!isset($_POST['_token'], $_SESSION['_token']) || !hash_equals($_SESSION['_token'], $_POST['_token'])) {
            throw new Exception('Invalid CSRF token');
        }
    }

    private function memberTableExists($tableName)
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?",
            [$tableName]
        );

        return (int) $count > 0;
    }
    
    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}