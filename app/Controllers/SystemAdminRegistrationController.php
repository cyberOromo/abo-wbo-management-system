<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Position;
use App\Models\UserAssignment;
use App\Utils\Database;
use Exception;

/**
 * System Admin Hierarchical User Registration Controller
 * 
 * This controller handles the comprehensive registration of users across
 * the organizational hierarchy with proper position assignments.
 * 
 * Features:
 * - Create users at any hierarchy level (Global, Godina, Gamta, Gurmu)
 * - Assign organizational positions to users
 * - Set up module permissions based on positions
 * - Handle executive and member role assignments
 * - Generate secure temporary passwords
 * - Send welcome emails with login credentials
 */
class SystemAdminRegistrationController extends Controller
{
    protected $userModel;
    protected $positionModel;
    protected $assignmentModel;
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole(['admin']); // Only system admins can access
        
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->positionModel = new Position();
        $this->assignmentModel = new UserAssignment();
    }
    
    /**
     * Show hierarchical user registration dashboard
     */
    public function index()
    {
        // Get hierarchy data for the form
        $hierarchyData = $this->getHierarchyData();
        $positions = $this->getAvailablePositions();
        $recentRegistrations = $this->getRecentRegistrations();
        $registrationStats = $this->getRegistrationStats();
        
        return $this->render('admin.hierarchical-registration', [
            'title' => 'Hierarchical User Registration',
            'hierarchy_data' => $hierarchyData,
            'positions' => $positions,
            'recent_registrations' => $recentRegistrations,
            'stats' => $registrationStats,
            'current_user' => auth_user()
        ]);
    }
    
    /**
     * Handle hierarchical user registration
     */
    public function register()
    {
        try {
            $this->validateCSRF();
            
            // Validate input data
            $data = $this->validateRegistrationData();
            
            // Check if email already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                throw new Exception('A user with this email already exists.');
            }
            
            // Start database transaction
            $this->db->beginTransaction();
            
            try {
                // Create the new user
                $userId = $this->createHierarchicalUser($data);
                
                // Create position assignment if specified
                if (!empty($data['position_id'])) {
                    $this->createUserAssignment($userId, $data);
                }
                
                // Set up default module permissions
                $this->setupUserModulePermissions($userId, $data);
                
                // Send welcome email
                $this->sendWelcomeEmail($data);
                
                // Log the registration
                $this->logRegistrationActivity($userId, $data);
                
                $this->db->commit();
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User registered successfully with hierarchical assignment',
                    'user_id' => $userId,
                    'temp_password' => $data['temp_password']
                ]);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Get hierarchy data for form dropdowns
     */
    public function getHierarchyData()
    {
        // Get Global organization
        $global = $this->db->fetch("SELECT * FROM globals WHERE status = 'active' ORDER BY name LIMIT 1");
        
        // Get all Godinas
        $godinas = $this->db->fetchAll("SELECT * FROM godinas WHERE status = 'active' ORDER BY name");
        
        // Get all Gamtas grouped by Godina
        $gamtas = $this->db->fetchAll("
            SELECT g.*, god.name as godina_name 
            FROM gamtas g 
            JOIN godinas god ON g.godina_id = god.id 
            WHERE g.status = 'active' 
            ORDER BY god.name, g.name
        ");
        
        // Get all Gurmus grouped by Gamta
        $gurmus = $this->db->fetchAll("
            SELECT gu.*, gam.name as gamta_name, god.name as godina_name
            FROM gurmus gu
            JOIN gamtas gam ON gu.gamta_id = gam.id
            JOIN godinas god ON gam.godina_id = god.id
            WHERE gu.status = 'active'
            ORDER BY god.name, gam.name, gu.name
        ");
        
        return [
            'global' => $global,
            'godinas' => $godinas,
            'gamtas' => $gamtas,
            'gurmus' => $gurmus
        ];
    }
    
    /**
     * Get available positions for assignment
     */
    public function getAvailablePositions()
    {
        return $this->db->fetchAll("
            SELECT id, key_name, name, code, description, hierarchy_type, is_executive 
            FROM positions 
            WHERE status = 'active' 
            ORDER BY hierarchy_type, name
        ");
    }
    
    /**
     * API endpoint to get Gamtas by Godina
     */
    public function getGamtasByGodina($godinaId)
    {
        $gamtas = $this->db->fetchAll("
            SELECT id, name, code, description 
            FROM gamtas 
            WHERE godina_id = ? AND status = 'active' 
            ORDER BY name
        ", [$godinaId]);
        
        return $this->jsonResponse([
            'success' => true,
            'data' => $gamtas
        ]);
    }
    
    /**
     * API endpoint to get Gurmus by Gamta
     */
    public function getGurmusByGamta($gamtaId)
    {
        $gurmus = $this->db->fetchAll("
            SELECT id, name, code, description 
            FROM gurmus 
            WHERE gamta_id = ? AND status = 'active' 
            ORDER BY name
        ", [$gamtaId]);
        
        return $this->jsonResponse([
            'success' => true,
            'data' => $gurmus
        ]);
    }
    
    /**
     * HELPER METHODS
     */
    
    /**
     * Validate registration data
     */
    private function validateRegistrationData()
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:executive,member',
            'level_scope' => 'required|in:global,godina,gamta,gurmu',
            'global_id' => 'nullable|integer|exists:globals,id',
            'godina_id' => 'nullable|integer|exists:godinas,id',
            'gamta_id' => 'nullable|integer|exists:gamtas,id',
            'gurmu_id' => 'nullable|integer|exists:gurmus,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'appointment_type' => 'nullable|in:elected,appointed,volunteer,permanent',
            'start_date' => 'nullable|date',
            'language_preference' => 'nullable|in:en,om',
            'send_welcome_email' => 'nullable|boolean'
        ];
        
        $data = $this->validate($rules);
        
        // Generate secure temporary password
        $data['temp_password'] = $this->generateSecurePassword();
        
        // Validate hierarchy consistency
        $this->validateHierarchyConsistency($data);
        
        return $data;
    }
    
    /**
     * Validate hierarchy consistency
     */
    private function validateHierarchyConsistency($data)
    {
        switch ($data['level_scope']) {
            case 'global':
                if (empty($data['global_id'])) {
                    throw new Exception('Global ID is required for global level scope');
                }
                break;
                
            case 'godina':
                if (empty($data['global_id']) || empty($data['godina_id'])) {
                    throw new Exception('Global ID and Godina ID are required for godina level scope');
                }
                break;
                
            case 'gamta':
                if (empty($data['global_id']) || empty($data['godina_id']) || empty($data['gamta_id'])) {
                    throw new Exception('Global ID, Godina ID, and Gamta ID are required for gamta level scope');
                }
                break;
                
            case 'gurmu':
                if (empty($data['global_id']) || empty($data['godina_id']) || empty($data['gamta_id']) || empty($data['gurmu_id'])) {
                    throw new Exception('All hierarchy IDs are required for gurmu level scope');
                }
                break;
        }
        
        // Validate that the hierarchy chain is correct
        if ($data['level_scope'] !== 'global') {
            $this->validateHierarchyChain($data);
        }
    }
    
    /**
     * Validate hierarchy chain integrity
     */
    private function validateHierarchyChain($data)
    {
        if (!empty($data['gamta_id'])) {
            $gamta = $this->db->fetch("SELECT godina_id FROM gamtas WHERE id = ?", [$data['gamta_id']]);
            if (!$gamta || $gamta['godina_id'] != $data['godina_id']) {
                throw new Exception('Selected Gamta does not belong to the selected Godina');
            }
        }
        
        if (!empty($data['gurmu_id'])) {
            $gurmu = $this->db->fetch("SELECT gamta_id FROM gurmus WHERE id = ?", [$data['gurmu_id']]);
            if (!$gurmu || $gurmu['gamta_id'] != $data['gamta_id']) {
                throw new Exception('Selected Gurmu does not belong to the selected Gamta');
            }
        }
    }
    
    /**
     * Create hierarchical user
     */
    private function createHierarchicalUser($data)
    {
        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => password_hash($data['temp_password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'), // Auto-verify admin-created accounts
            'language' => $data['language_preference'] ?? 'en',
            'metadata' => json_encode([
                'created_by_admin' => true,
                'temp_password_sent' => !empty($data['send_welcome_email']),
                'hierarchy_level' => $data['level_scope'],
                'requires_password_change' => true
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('users', $userData);
    }
    
    /**
     * Create user assignment
     */
    private function createUserAssignment($userId, $data)
    {
        $assignmentData = [
            'user_id' => $userId,
            'position_id' => $data['position_id'],
            'level_scope' => $data['level_scope'],
            'global_id' => $data['global_id'] ?? null,
            'godina_id' => $data['godina_id'] ?? null,
            'gamta_id' => $data['gamta_id'] ?? null,
            'gurmu_id' => $data['gurmu_id'] ?? null,
            'status' => 'active',
            'start_date' => $data['start_date'] ?? date('Y-m-d'),
            'appointment_type' => $data['appointment_type'] ?? 'appointed',
            'assigned_by' => auth_user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('user_assignments', $assignmentData);
    }
    
    /**
     * Setup user module permissions based on position
     */
    private function setupUserModulePermissions($userId, $data)
    {
        if (empty($data['position_id'])) return;
        
        // Get position details
        $position = $this->db->fetch("SELECT * FROM positions WHERE id = ?", [$data['position_id']]);
        if (!$position) return;
        
        // Get default modules for this position
        $defaultModules = $this->db->fetchAll("
            SELECT module_name, access_level 
            FROM position_modules 
            WHERE position_name = ? AND is_default = 1
        ", [$position['name']]);
        
        // Create user module overrides based on position defaults
        foreach ($defaultModules as $module) {
            $this->db->insert('user_module_overrides', [
                'user_id' => $userId,
                'module_name' => $module['module_name'],
                'access_level' => $module['access_level'],
                'enabled_by' => auth_user()['id'],
                'reason' => 'Auto-assigned based on position: ' . $position['name']
            ]);
        }
    }
    
    /**
     * Generate secure password
     */
    private function generateSecurePassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }
    
    /**
     * Send welcome email
     */
    private function sendWelcomeEmail($data)
    {
        if (empty($data['send_welcome_email'])) return;
        
        // TODO: Implement comprehensive email system
        // For now, log the credentials for admin reference
        error_log("=== HIERARCHICAL USER REGISTRATION ===");
        error_log("Name: {$data['first_name']} {$data['last_name']}");
        error_log("Email: {$data['email']}");
        error_log("Temporary Password: {$data['temp_password']}");
        error_log("Role: {$data['role']}");
        error_log("Level: {$data['level_scope']}");
        error_log("======================================");
    }
    
    /**
     * Log registration activity
     */
    private function logRegistrationActivity($userId, $data)
    {
        $description = sprintf(
            "Created hierarchical user: %s %s (%s) at %s level with role: %s",
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['level_scope'],
            $data['role']
        );
        
        // TODO: Use proper activity logging system
        error_log("ADMIN ACTIVITY: " . $description);
    }
    
    /**
     * Get recent registrations
     */
    private function getRecentRegistrations()
    {
        return $this->db->fetchAll("
            SELECT 
                u.id, u.first_name, u.last_name, u.email, u.role, u.created_at,
                ua.level_scope,
                p.name as position_name,
                CASE 
                    WHEN ua.level_scope = 'global' THEN 'Global Organization'
                    WHEN ua.level_scope = 'godina' THEN CONCAT('Godina: ', god.name)
                    WHEN ua.level_scope = 'gamta' THEN CONCAT('Gamta: ', gam.name)
                    WHEN ua.level_scope = 'gurmu' THEN CONCAT('Gurmu: ', gur.name)
                    ELSE 'Unassigned'
                END as hierarchy_assignment
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            LEFT JOIN godinas god ON ua.godina_id = god.id
            LEFT JOIN gamtas gam ON ua.gamta_id = gam.id
            LEFT JOIN gurmus gur ON ua.gurmu_id = gur.id
            WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND u.email LIKE '%@abo-wbo.org'
            ORDER BY u.created_at DESC
            LIMIT 20
        ");
    }
    
    /**
     * Get registration statistics
     */
    private function getRegistrationStats()
    {
        $stats = [];
        
        // Total users by role
        $roleStats = $this->db->fetchAll("
            SELECT role, COUNT(*) as count 
            FROM users 
            WHERE status = 'active' 
            GROUP BY role
        ");
        
        $stats['by_role'] = [];
        foreach ($roleStats as $stat) {
            $stats['by_role'][$stat['role']] = $stat['count'];
        }
        
        // Total users by hierarchy level
        $levelStats = $this->db->fetchAll("
            SELECT ua.level_scope, COUNT(DISTINCT ua.user_id) as count
            FROM user_assignments ua
            WHERE ua.status = 'active'
            GROUP BY ua.level_scope
        ");
        
        $stats['by_level'] = [];
        foreach ($levelStats as $stat) {
            $stats['by_level'][$stat['level_scope']] = $stat['count'];
        }
        
        // Recent registrations count
        $stats['recent'] = [
            'today' => $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'],
            'this_week' => $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)")['count'],
            'this_month' => $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")['count']
        ];
        
        return $stats;
    }
    
    /**
     * Validate request data
     */
    private function validate(array $rules)
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
    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}