<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\User;
use App\Models\Gurmu;
use App\Models\Gamta;
use App\Models\UserAssignment;
use App\Utils\Database;
use Exception;

/**
 * Member Registration Controller
 * Handles member registration with hierarchy-based restrictions
 * - System Admin: Can register members for any Gurmu
 * - Gurmu Leaders: Can only register members for their own Gurmu
 */
class MemberRegistrationController extends BaseController
{
    protected $userModel;
    protected $gurmuModel;
    protected $gamtaModel;
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->gurmuModel = new Gurmu();
        $this->gamtaModel = new Gamta();
    }
    
    /**
     * Show member registration form
     */
    public function index()
    {
        $currentUser = auth_user();
        $allowedGurmus = $this->getAllowedGurmus($currentUser);
        $recentRegistrations = $this->getRecentRegistrations($currentUser);
        
        return $this->render('member-registration/index_modern', [
            'title' => 'Member Registration',
            'registrations' => $recentRegistrations,
            'registration_stats' => $this->getRegistrationStats($currentUser),
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
            
            // Create the new member
            $newMemberId = $this->createMember($data, $currentUser['id']);
            
            // Log the registration activity
            $this->logRegistrationActivity($currentUser['id'], $newMemberId, $data['gurmu_id']);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Member registered successfully',
                'member_id' => $newMemberId
            ]);
            
        } catch (Exception $e) {
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
    
    /**
     * HELPER METHODS
     */
    
    /**
     * Get Gurmus that the current user can register members for
     */
    private function getAllowedGurmus($currentUser)
    {
        // System Admin can access all Gurmus
        if (in_array($currentUser['role'], ['system_admin', 'super_admin'])) {
            return $this->gurmuModel->getActive();
        }
        
        // Check if user has a leadership position in any Gurmu
        $allowedGurmus = [];
        
        // Get user's active assignments
        $assignments = $this->db->fetchAll("
            SELECT ua.*, p.key_name as position_key, p.name as position_name,
                   ua.organizational_unit_id, ua.level_scope
            FROM user_assignments ua
            JOIN positions p ON ua.position_id = p.id
            WHERE ua.user_id = ? AND ua.status = 'active'
        ", [$currentUser['id']]);
        
        foreach ($assignments as $assignment) {
            // Leaders at Gurmu level can register members for their Gurmu
            if ($assignment['level_scope'] === 'gurmu') {
                $gurmu = $this->gurmuModel->find($assignment['organizational_unit_id']);
                if ($gurmu && $gurmu['status'] === 'active') {
                    $allowedGurmus[] = $gurmu;
                }
            }
            // Leaders at Gamta level can register members for all Gurmus in their Gamta
            else if ($assignment['level_scope'] === 'gamta') {
                $gurmus = $this->gurmuModel->where('gamta_id', $assignment['organizational_unit_id'])
                                          ->where('status', 'active')
                                          ->get();
                $allowedGurmus = array_merge($allowedGurmus, $gurmus);
            }
            // Leaders at Godina level can register members for all Gurmus in their Godina
            else if ($assignment['level_scope'] === 'godina') {
                $gurmus = $this->db->fetchAll("
                    SELECT g.* FROM gurmus g
                    JOIN gamtas gm ON g.gamta_id = gm.id
                    WHERE gm.godina_id = ? AND g.status = 'active'
                ", [$assignment['organizational_unit_id']]);
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
        // System Admin can register for any Gurmu
        if (in_array($currentUser['role'], ['system_admin', 'super_admin'])) {
            return true;
        }
        
        $allowedGurmus = $this->getAllowedGurmus($currentUser);
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
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'gurmu_id' => $data['gurmu_id'],
            'level_scope' => 'gurmu', // All members start at Gurmu level
            'language_preference' => $data['language_preference'] ?? 'en',
            'status' => 'active',
            'approval_status' => 'approved', // Auto-approved by authorized registrar
            'approved_by' => $createdBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'created_by' => $createdBy,
            'metadata' => json_encode([
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'registration_notes' => $data['notes'] ?? null,
                'temp_password' => $tempPassword, // Store for initial email
                'requires_password_change' => true
            ])
        ];
        
        $memberId = $this->userModel->create($memberData);
        
        // Send welcome email with temporary password
        $this->sendWelcomeEmail($data['email'], $data['first_name'], $tempPassword);
        
        return $memberId;
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
    private function sendWelcomeEmail($email, $firstName, $tempPassword)
    {
        // TODO: Implement email sending
        // For now, log the credentials
        error_log("Welcome email for {$email}: Temp password: {$tempPassword}");
    }
    
    /**
     * Log registration activity
     */
    private function logRegistrationActivity($registrarId, $newMemberId, $gurmuId)
    {
        $this->db->insert('activity_logs', [
            'user_id' => $registrarId,
            'action' => 'member_registration',
            'description' => "Registered new member (ID: {$newMemberId}) for Gurmu ID: {$gurmuId}",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get recent registrations for current user's scope
     */
    private function getRecentRegistrations($currentUser)
    {
        if (in_array($currentUser['role'], ['system_admin', 'super_admin'])) {
            // System admin sees all recent registrations
            $query = "
                SELECT u.*, g.name as gurmu_name, gm.name as gamta_name, god.name as godina_name,
                       creator.first_name as registered_by_name
                FROM users u
                LEFT JOIN gurmus g ON u.gurmu_id = g.id
                LEFT JOIN gamtas gm ON g.gamta_id = gm.id
                LEFT JOIN godinas god ON gm.godina_id = god.id
                LEFT JOIN users creator ON u.created_by = creator.id
                WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY u.created_at DESC
                LIMIT 20
            ";
            return $this->db->fetchAll($query);
        } else {
            // Leaders see only their registrations
            $query = "
                SELECT u.*, g.name as gurmu_name, gm.name as gamta_name, god.name as godina_name
                FROM users u
                LEFT JOIN gurmus g ON u.gurmu_id = g.id
                LEFT JOIN gamtas gm ON g.gamta_id = gm.id
                LEFT JOIN godinas god ON gm.godina_id = god.id
                WHERE u.created_by = ? AND u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY u.created_at DESC
                LIMIT 20
            ";
            return $this->db->fetchAll($query, [$currentUser['id']]);
        }
    }
    
    /**
     * Get member registration statistics
     */
    private function getMemberRegistrationStats($currentUser)
    {
        $allowedGurmus = $this->getAllowedGurmus($currentUser);
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
        if (!isset($_POST['_token']) || !hash_equals($_SESSION['_token'], $_POST['_token'])) {
            throw new Exception('Invalid CSRF token');
        }
    }
    
    /**
     * JSON response helper
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}