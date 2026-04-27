<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Godina;
use App\Models\Gamta;
use App\Models\Gurmu;
use App\Models\Position;
use App\Models\UserAssignment;
use App\Services\InternalEmailGenerator;
use App\Utils\Database;
use App\Utils\EmailSender;
use App\Utils\Logger;
use Exception;

/**
 * System Admin User/Leader Registration Controller
 * 
 * CRITICAL: This module is ONLY accessible by System Administrators
 * Handles registration of users with leadership positions and role assignments
 */
class UserLeaderRegistrationController extends Controller
{
    private Database $db;
    private User $userModel;
    private Position $positionModel;
    private UserAssignment $assignmentModel;
    private InternalEmailGenerator $emailGenerator;
    private EmailSender $emailService;
    private Logger $logger;

    public function __construct()
    {
        // Initialize dependencies
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->positionModel = new Position();
        $this->assignmentModel = new UserAssignment();
        $this->emailGenerator = new InternalEmailGenerator();
        $this->emailService = new EmailSender();
        $this->logger = new Logger();
        
        // CRITICAL: Verify System Admin access
        $this->enforceSystemAdminAccess();
    }

    /**
     * Display user/leader registration page
     */
    public function index()
    {
        try {
            // Get organizational data for dropdowns
            $godinas = $this->getGodinas();
            $gamtas = $this->getGamtas();
            $gurmus = $this->getGurmus();
            $positions = $this->getPositions();
            $recent_registrations = $this->getRecentRegistrations();
            $statistics = $this->getRegistrationStatistics();
            
            return $this->view('admin/user_leader_registration', [
                'title' => 'User & Leader Registration',
                'godinas' => $godinas,
                'gamtas' => $gamtas,
                'gurmus' => $gurmus,
                'positions' => $positions,
                'recent_registrations' => $recent_registrations,
                'statistics' => $statistics,
                'current_user' => $_SESSION['user'] ?? []
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Error loading user/leader registration page', [
                'error' => $e->getMessage(),
                'user_id' => $_SESSION['user']['id'] ?? null
            ]);
            
            return $this->redirect('/admin')->with('error', 'Unable to load registration page');
        }
    }

    /**
     * Register new user with leadership position
     */
    public function registerUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
        }

        try {
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid security token']);
            }

            // Extract and validate input data
            $userData = $this->extractUserData();
            $positionData = $this->extractPositionData();
            
            // Validate required data
            $validation = $this->validateRegistrationData($userData, $positionData);
            if (!$validation['valid']) {
                return $this->jsonResponse(['success' => false, 'message' => $validation['message']]);
            }

            // Start database transaction
            $this->db->beginTransaction();

            try {
                // Create user account
                $userId = $this->createUserAccount($userData);
                
                // Assign positions to user
                $assignmentResults = $this->assignPositionsToUser($userId, $positionData);

                // Provision immutable primary email plus office aliases.
                $emailProvisioning = $this->provisionUserInternalEmails($userId, $userData, $positionData);
                
                // Send welcome email with credentials
                $this->sendWelcomeEmail($userId, $userData, $assignmentResults, $emailProvisioning);
                
                // Log the registration
                $this->logUserRegistration($userId, $assignmentResults);
                
                // Commit transaction
                $this->db->commit();
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User registered successfully with leadership positions assigned',
                    'data' => [
                        'user_id' => $userId,
                        'assignments' => $assignmentResults,
                        'internal_email' => $emailProvisioning['primary_email'] ?? null,
                        'role_aliases' => $emailProvisioning['aliases'] ?? []
                    ]
                ]);
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $this->db->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            $this->logger->error('Error registering user/leader', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_user_id' => $_SESSION['user']['id'] ?? null,
                'input_data' => $_POST ?? []
            ]);

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available Gamtas for selected Godina
     */
    public function getGamtasForGodina()
    {
        try {
            $godinaId = $_GET['godina_id'] ?? null;
            
            if (!$godinaId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Godina ID required']);
            }

            $gamtas = $this->db->fetchAll(
                "SELECT id, name, description FROM gamtas WHERE godina_id = ? ORDER BY name",
                [$godinaId]
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $gamtas
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error loading Gamtas']);
        }
    }

    /**
     * Get available Gurmus for selected Gamta
     */
    public function getGurmusForGamta()
    {
        try {
            $gamtaId = $_GET['gamta_id'] ?? null;
            
            if (!$gamtaId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Gamta ID required']);
            }

            $gurmus = $this->db->fetchAll(
                "SELECT id, name, description FROM gurmus WHERE gamta_id = ? ORDER BY name",
                [$gamtaId]
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $gurmus
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error loading Gurmus']);
        }
    }

    /**
     * Get positions available for specific hierarchy level
     */
    public function getPositionsForLevel()
    {
        try {
            $level = $_GET['level'] ?? null;
            $hierarchyId = $_GET['hierarchy_id'] ?? null;
            
            if (!$level) {
                return $this->jsonResponse(['success' => false, 'message' => 'Level required']);
            }

            // Get positions for the specified level
            $positions = $this->db->fetchAll(
                "SELECT id, name, description, is_executive, hierarchy_type 
                 FROM positions 
                 WHERE hierarchy_type = ? 
                 ORDER BY is_executive DESC, name",
                [$level]
            );

            // Check which positions are already occupied for this hierarchy level
            if ($hierarchyId) {
                $occupiedPositions = $this->getOccupiedPositions($level, $hierarchyId);
                
                // Mark occupied positions
                foreach ($positions as &$position) {
                    $position['is_occupied'] = in_array($position['id'], $occupiedPositions);
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $positions
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error loading positions']);
        }
    }

    /**
     * Get registration statistics
     */
    public function getStats()
    {
        try {
            $stats = $this->getRegistrationStatistics();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error loading statistics']);
        }
    }

    /**
     * Get list of users with their assignments
     */
    public function listUsers()
    {
        try {
            $page = max(1, $_GET['page'] ?? 1);
            $limit = min(100, max(10, $_GET['limit'] ?? 20));
            $offset = ($page - 1) * $limit;
            
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';

            // Build query conditions
            $conditions = [];
            $params = [];
            
            if ($search) {
                $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($role) {
                $conditions[] = "u.role = ?";
                $params[] = $role;
            }
            
            if ($status) {
                $conditions[] = "u.status = ?";
                $params[] = $status;
            }

            $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

            // Get users with their assignments
            $users = $this->db->fetchAll("
                SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.internal_email,
                    u.role,
                    u.status,
                    u.created_at,
                    COUNT(ua.id) as assignment_count,
                    GROUP_CONCAT(
                        CONCAT(p.name, ' (', 
                            CASE 
                                WHEN ua.level_scope = 'global' THEN 'Global'
                                WHEN ua.level_scope = 'godina' THEN god.name
                                WHEN ua.level_scope = 'gamta' THEN gam.name
                                WHEN ua.level_scope = 'gurmu' THEN gur.name
                            END,
                        ')')
                        SEPARATOR ', '
                    ) as positions
                FROM users u
                LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
                LEFT JOIN positions p ON ua.position_id = p.id
                LEFT JOIN godinas god ON ua.godina_id = god.id AND ua.level_scope = 'godina'
                LEFT JOIN gamtas gam ON ua.gamta_id = gam.id AND ua.level_scope = 'gamta'
                LEFT JOIN gurmus gur ON ua.gurmu_id = gur.id AND ua.level_scope = 'gurmu'
                $whereClause
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.internal_email, u.role, u.status, u.created_at
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$limit, $offset]));

            // Get total count
            $totalCount = (int) $this->db->fetchColumn("
                SELECT COUNT(DISTINCT u.id)
                FROM users u
                LEFT JOIN user_assignments ua ON u.id = ua.user_id
                $whereClause
            ", $params);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $totalCount,
                        'total_pages' => ceil($totalCount / $limit)
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error loading users']);
        }
    }

    /**
     * Update user assignments
     */
    public function updateUserAssignments()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
        }

        try {
            $userId = $_POST['user_id'] ?? null;
            $assignments = $_POST['assignments'] ?? [];

            if (!$userId) {
                return $this->jsonResponse(['success' => false, 'message' => 'User ID required']);
            }

            // Validate user exists
            $user = $this->userModel->findById($userId);
            if (!$user) {
                return $this->jsonResponse(['success' => false, 'message' => 'User not found']);
            }

            $this->db->beginTransaction();

            try {
                // Deactivate current assignments
                $this->db->query(
                    "UPDATE user_assignments SET status = 'inactive', updated_at = NOW() WHERE user_id = ?",
                    [$userId]
                );

                // Create new assignments
                $newAssignments = [];
                foreach ($assignments as $assignment) {
                    if ($this->validateAssignmentData($assignment)) {
                        $assignmentId = $this->createUserAssignment($userId, $assignment);
                        $newAssignments[] = $assignmentId;
                    }
                }

                $this->db->commit();

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User assignments updated successfully',
                    'data' => ['assignment_ids' => $newAssignments]
                ]);

            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error updating assignments']);
        }
    }

    // Private helper methods

    /**
     * Enforce System Admin access
     */
    private function enforceSystemAdminAccess()
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['system_admin', 'super_admin'])) {
            if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
                return;
            }

            if ($this->isJsonRequest()) {
                echo json_encode(['success' => false, 'message' => 'Access denied. System Admin required.']);
                exit;
            }
            
            $this->redirect('/dashboard')->with('error', 'Access denied. System Administrator privileges required.');
            exit;
        }
    }

    /**
     * Extract user data from POST request
     */
    private function extractUserData(): array
    {
        return [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => trim($_POST['role'] ?? 'user'),
            'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
            'gender' => trim($_POST['gender'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
            'language_preference' => trim($_POST['language_preference'] ?? 'en'),
            'notes' => trim($_POST['notes'] ?? '')
        ];
    }

    /**
     * Extract position assignment data from POST request
     */
    private function extractPositionData(): array
    {
        $assignments = [];
        
        // Handle multiple position assignments
        if (isset($_POST['assignments']) && is_array($_POST['assignments'])) {
            foreach ($_POST['assignments'] as $assignment) {
                $assignments[] = [
                    'position_id' => $assignment['position_id'] ?? null,
                    'hierarchy_level' => $assignment['hierarchy_level'] ?? null,
                    'hierarchy_id' => $assignment['hierarchy_id'] ?? null,
                    'start_date' => $assignment['start_date'] ?? date('Y-m-d'),
                    'end_date' => $assignment['end_date'] ?? null,
                    'notes' => $assignment['notes'] ?? ''
                ];
            }
        }
        
        return $assignments;
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData(array $userData, array $positionData): array
    {
        // Validate required user fields
        if (empty($userData['first_name'])) {
            return ['valid' => false, 'message' => 'First name is required'];
        }
        
        if (empty($userData['last_name'])) {
            return ['valid' => false, 'message' => 'Last name is required'];
        }
        
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Valid email address is required'];
        }

        // Check if email already exists
        $existingUser = $this->userModel->findByEmail($userData['email']);
        if ($existingUser) {
            return ['valid' => false, 'message' => 'Email address already exists'];
        }

        // Validate role
        $allowedRoles = ['user', 'leader', 'admin', 'system_admin'];
        if (!in_array($userData['role'], $allowedRoles)) {
            return ['valid' => false, 'message' => 'Invalid user role'];
        }

        // Validate position assignments
        if (empty($positionData)) {
            return ['valid' => false, 'message' => 'At least one position assignment is required'];
        }

        foreach ($positionData as $assignment) {
            if (empty($assignment['position_id']) || empty($assignment['hierarchy_level'])) {
                return ['valid' => false, 'message' => 'Position and hierarchy level are required for all assignments'];
            }
        }

        return ['valid' => true, 'message' => 'Validation passed'];
    }

    /**
     * Create user account
     */
    private function createUserAccount(array $userData): int
    {
        // Generate temporary password
        $temporaryPassword = $this->generateTemporaryPassword();

        $storageRole = match ($userData['role']) {
            'admin', 'system_admin' => 'admin',
            'leader' => 'executive',
            default => 'member',
        };
        
        $userRecord = [
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'email' => $userData['email'],
            'personal_email' => $userData['email'],
            'phone' => $userData['phone'],
            'personal_phone' => $userData['phone'] ?: null,
            'password_hash' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
            'role' => $storageRole,
            'status' => 'active',
            'birth_date' => $userData['date_of_birth'] ?: null,
            'address' => $userData['address'],
            'emergency_contact' => !empty($userData['emergency_contact'])
                ? json_encode(['details' => $userData['emergency_contact']])
                : null,
            'language' => $userData['language_preference'] ?: 'en',
            'registration_source' => 'admin_created',
            'account_type' => 'internal_only',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'personal_email_verified' => 1,
            'onboarding_completed' => 0,
            'metadata' => json_encode([
                'middle_name' => $userData['middle_name'] ?: null,
                'gender' => $userData['gender'] ?: null,
                'language_preference' => $userData['language_preference'] ?: 'en',
                'notes' => $userData['notes'] ?: null,
                'registered_by' => $_SESSION['user']['id'],
                'requested_role' => $userData['role'],
                'temporary_password' => $temporaryPassword,
                'requires_password_change' => true
            ])
        ];

        $userId = $this->db->insert('users', $userRecord);
        
        // Store temporary password for email
        $userRecord['temporary_password'] = $temporaryPassword;
        $this->tempUserData = $userRecord;
        
        return $userId;
    }

    /**
     * Assign positions to user
     */
    private function assignPositionsToUser(int $userId, array $positionData): array
    {
        $results = [];
        
        foreach ($positionData as $assignment) {
            $assignmentId = $this->createUserAssignment($userId, $assignment);
            $results[] = [
                'assignment_id' => $assignmentId,
                'position_id' => $assignment['position_id'],
                'hierarchy_level' => $assignment['hierarchy_level'],
                'hierarchy_id' => $assignment['hierarchy_id']
            ];
        }
        
        return $results;
    }

    /**
     * Create user assignment record
     */
    private function createUserAssignment(int $userId, array $assignment): int
    {
        $scopeColumns = [
            'global_id' => null,
            'godina_id' => null,
            'gamta_id' => null,
            'gurmu_id' => null,
        ];

        if (($assignment['hierarchy_level'] ?? null) !== 'global') {
            $scopeColumn = $assignment['hierarchy_level'] . '_id';
            if (array_key_exists($scopeColumn, $scopeColumns)) {
                $scopeColumns[$scopeColumn] = $assignment['hierarchy_id'] ?: null;
            }
        }

        $assignmentData = [
            'user_id' => $userId,
            'position_id' => $assignment['position_id'],
            'level_scope' => $assignment['hierarchy_level'],
            'start_date' => $assignment['start_date'],
            'end_date' => $assignment['end_date'],
            'status' => 'active',
            'appointment_type' => 'appointed',
            'assigned_by' => $_SESSION['user']['id'],
            'assignment_reason' => $assignment['notes'] ?: 'Assigned during admin registration',
            'metadata' => json_encode(['notes' => $assignment['notes'] ?: null]),
            'created_at' => date('Y-m-d H:i:s')
        ] + $scopeColumns;

        return $this->db->insert('user_assignments', $assignmentData);
    }

    private function provisionUserInternalEmails(int $userId, array $userData, array $positionData): array
    {
        $primaryEmail = $this->emailGenerator->generateInternalEmail($userData);
        $temporaryPassword = $this->tempUserData['temporary_password'] ?? $this->generateTemporaryPassword();

        $this->emailGenerator->createInternalEmailRecord($userId, $primaryEmail, [
            'email_type' => 'primary',
            'quota_mb' => 2048,
            'created_by' => $_SESSION['user']['id'],
            'creation_method' => 'user_leader_registration_primary'
        ]);
        $this->emailGenerator->createCPanelEmailAccount($primaryEmail, $temporaryPassword, 2048);

        $aliases = [];
        $seenAliases = [];

        foreach ($positionData as $assignment) {
            $position = $this->db->fetch("SELECT * FROM positions WHERE id = ?", [$assignment['position_id']]);
            $hierarchyData = $this->resolveHierarchyData($assignment['hierarchy_level'], $assignment['hierarchy_id']);

            if (!$position || !$hierarchyData) {
                continue;
            }

            $aliasEmail = $this->emailGenerator->provisionRoleAlias($userId, $userData, $position, $hierarchyData, [
                'forward_to' => $primaryEmail,
                'created_by' => $_SESSION['user']['id'],
                'creation_method' => 'user_leader_registration_alias'
            ]);

            if ($aliasEmail && !isset($seenAliases[$aliasEmail])) {
                $aliases[] = $aliasEmail;
                $seenAliases[$aliasEmail] = true;
            }
        }

        $this->db->update('users', [
            'internal_email' => $primaryEmail,
            'internal_account_created_at' => date('Y-m-d H:i:s'),
            'internal_credentials_sent_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);

        $this->tempUserData['internal_email'] = $primaryEmail;
        $this->tempUserData['role_aliases'] = $aliases;

        return [
            'primary_email' => $primaryEmail,
            'aliases' => $aliases,
        ];
    }

    private function resolveHierarchyData(string $level, $hierarchyId): array
    {
        if ($level === 'global') {
            return ['level' => 'global', 'code' => 'global'];
        }

        if (empty($hierarchyId)) {
            return [];
        }

        return match ($level) {
            'godina' => $this->db->fetch("SELECT id, code, name, 'godina' as level FROM godinas WHERE id = ?", [$hierarchyId]) ?: [],
            'gamta' => $this->db->fetch("SELECT id, code, name, 'gamta' as level FROM gamtas WHERE id = ?", [$hierarchyId]) ?: [],
            'gurmu' => $this->db->fetch("SELECT id, code, name, 'gurmu' as level FROM gurmus WHERE id = ?", [$hierarchyId]) ?: [],
            default => [],
        };
    }

    /**
     * Send welcome email with credentials
     */
    private function sendWelcomeEmail(int $userId, array $userData, array $assignments, array $emailProvisioning = [])
    {
        if (isset($this->tempUserData['temporary_password'])) {
            $emailData = [
                'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'email' => $userData['email'],
                'temporary_password' => $this->tempUserData['temporary_password'],
                'internal_email' => $emailProvisioning['primary_email'] ?? ($this->tempUserData['internal_email'] ?? null),
                'role_aliases' => $emailProvisioning['aliases'] ?? ($this->tempUserData['role_aliases'] ?? []),
                'assignments' => $assignments,
                'login_url' => $_SERVER['HTTP_HOST'] . '/auth/login'
            ];

            $this->emailService->sendWelcomeEmail(
                $emailData['email'],
                $emailData['name'],
                [
                    'username' => $emailData['internal_email'] ?? $emailData['email'],
                    'password' => $emailData['temporary_password'] ?? '',
                ]
            );
        }
    }

    /**
     * Generate temporary password
     */
    private function generateTemporaryPassword(): string
    {
        return 'ABO' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get Godinas for dropdown
     */
    private function getGodinas(): array
    {
        return $this->db->fetchAll("SELECT id, name, description FROM godinas ORDER BY name");
    }

    private function getGamtas(): array
    {
        return $this->db->fetchAll(
            "SELECT ga.id, ga.name, ga.description, ga.godina_id, god.name AS godina_name
             FROM gamtas ga
             LEFT JOIN godinas god ON ga.godina_id = god.id
             ORDER BY god.name, ga.name"
        );
    }

    private function getGurmus(): array
    {
        return $this->db->fetchAll(
            "SELECT gu.id, gu.name, gu.description, gu.gamta_id, ga.name AS gamta_name,
                    ga.godina_id, god.name AS godina_name
             FROM gurmus gu
             LEFT JOIN gamtas ga ON gu.gamta_id = ga.id
             LEFT JOIN godinas god ON ga.godina_id = god.id
             ORDER BY god.name, ga.name, gu.name"
        );
    }

    /**
     * Get Positions for assignment
     */
    private function getPositions(): array
    {
        return $this->db->fetchAll("
            SELECT id, name, description, hierarchy_type, is_executive 
            FROM positions 
            ORDER BY hierarchy_type, is_executive DESC, name
        ");
    }

    /**
     * Get recent registrations
     */
    private function getRecentRegistrations(): array
    {
        return $this->db->fetchAll("
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.internal_email,
                u.role,
                u.status,
                u.created_at,
                COUNT(ua.id) as position_count,
                GROUP_CONCAT(p.name SEPARATOR ', ') as positions
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            WHERE CAST(JSON_UNQUOTE(JSON_EXTRACT(u.metadata, '$.registered_by')) AS UNSIGNED) = ?
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT 10
        ", [$_SESSION['user']['id']]);
    }

    /**
     * Get registration statistics
     */
    private function getRegistrationStatistics(): array
    {
        $adminId = $_SESSION['user']['id'];
        
        return [
            'total_users' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.registered_by')) AS UNSIGNED) = ?", [$adminId]),
            'this_month' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.registered_by')) AS UNSIGNED) = ? AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())", [$adminId]),
            'this_week' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.registered_by')) AS UNSIGNED) = ? AND WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())", [$adminId]),
            'today' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.registered_by')) AS UNSIGNED) = ? AND DATE(created_at) = DATE(NOW())", [$adminId]),
            'active_assignments' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM user_assignments ua JOIN users u ON ua.user_id = u.id WHERE CAST(JSON_UNQUOTE(JSON_EXTRACT(u.metadata, '$.registered_by')) AS UNSIGNED) = ? AND ua.status = 'active'", [$adminId])
        ];
    }

    /**
     * Get occupied positions for hierarchy level
     */
    private function getOccupiedPositions(string $level, int $hierarchyId): array
    {
        if ($level === 'global') {
            $positions = $this->db->fetchAll(
                "SELECT DISTINCT position_id FROM user_assignments WHERE level_scope = 'global' AND status = 'active'"
            );
            return array_column($positions, 'position_id');
        }

        $scopeColumn = $level . '_id';
        $positions = $this->db->fetchAll(
            "SELECT DISTINCT position_id FROM user_assignments WHERE level_scope = ? AND {$scopeColumn} = ? AND status = 'active'",
            [$level, $hierarchyId]
        );

        return array_column($positions, 'position_id');
    }

    /**
     * Log user registration activity
     */
    private function logUserRegistration(int $userId, array $assignments)
    {
        $this->logger->info('User/Leader registered by System Admin', [
            'registered_user_id' => $userId,
            'admin_user_id' => $_SESSION['user']['id'],
            'assignments_count' => count($assignments),
            'assignments' => $assignments
        ]);
    }

    /**
     * Validate assignment data
     */
    private function validateAssignmentData(array $assignment): bool
    {
        return !empty($assignment['position_id']) && 
               !empty($assignment['hierarchy_level']) && 
               ($assignment['hierarchy_level'] === 'global' || !empty($assignment['hierarchy_id']));
    }
}