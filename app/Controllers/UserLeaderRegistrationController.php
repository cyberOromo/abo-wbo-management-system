<?php

namespace App\Controllers;

use App\Core\BaseController;
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
class UserLeaderRegistrationController extends BaseController
{
    private Database $db;
    private User $userModel;
    private Position $positionModel;
    private UserAssignment $assignmentModel;
    private InternalEmailGenerator $emailGenerator;
    private ?EmailSender $emailService = null;
    private ?Logger $logger = null;
    private ?array $positionColumns = null;

    public function __construct()
    {
        // Initialize dependencies
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->positionModel = new Position();
        $this->assignmentModel = new UserAssignment();
        $this->emailGenerator = new InternalEmailGenerator();
        
        // CRITICAL: Verify System Admin access
        $this->enforceSystemAdminAccess();
    }

    private function getLogger(): Logger
    {
        if ($this->logger === null) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    private function getEmailService(): EmailSender
    {
        if ($this->emailService === null) {
            $this->emailService = new EmailSender();
        }

        return $this->emailService;
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
            
            return $this->render('admin/user_leader_registration', [
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
            $this->getLogger()->error('Error loading user/leader registration page', [
                'error' => $e->getMessage(),
                'user_id' => $_SESSION['user']['id'] ?? null
            ]);
            
            $this->setError('Unable to load registration page');
            return $this->redirect('/admin');
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
            $this->validateCsrfToken();

            // Extract and validate input data
            $userData = $this->extractUserData();
            $positionData = $this->extractPositionData();
            $primaryPlacement = $this->resolvePrimaryPlacement($userData, $positionData);
            
            // Validate required data
            $validation = $this->validateRegistrationData($userData, $positionData);
            if (!$validation['valid']) {
                return $this->jsonResponse(['success' => false, 'message' => $validation['message']]);
            }

            // Start database transaction
            $this->db->beginTransaction();

            try {
                // Create user account
                $userId = $this->createUserAccount($userData, $primaryPlacement);
                
                // Assign positions only for roles that can hold leadership offices.
                $assignmentResults = $this->roleRequiresLeadershipAssignments($userData['role'])
                    ? $this->assignPositionsToUser($userId, $positionData)
                    : [];

                // Provision immutable primary email plus office aliases.
                $emailProvisioning = $this->provisionUserInternalEmails($userId, $userData, $assignmentResults === [] ? [] : $positionData);
                
                // Send welcome email with credentials
                $this->sendWelcomeEmail($userId, $userData, $assignmentResults, $emailProvisioning);
                
                // Log the registration
                $this->logUserRegistration($userId, $assignmentResults);
                
                // Commit transaction
                $this->db->commit();
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => $assignmentResults === []
                        ? 'User registered successfully'
                        : 'User registered successfully with leadership positions assigned',
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
            $this->getLogger()->error('Error registering user/leader', [
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

            $positions = $this->getAssignmentPositionsForLevel((string) $level);

            // Check which positions are already occupied for this hierarchy level
            if ($hierarchyId) {
                $occupiedPositions = $this->getOccupiedPositions($level, $hierarchyId);
                
                // Mark occupied positions
                foreach ($positions as &$position) {
                    $position['is_occupied'] = in_array((int) $position['id'], array_map('intval', $occupiedPositions), true);
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
                $conditions[] = "u.user_type = ?";
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
                    u.user_type AS role,
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
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.internal_email, u.user_type, u.status, u.created_at
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
            $userId = (int) ($_POST['user_id'] ?? 0);
            $assignments = $_POST['assignments'] ?? [];

            if ($userId <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'User ID required']);
            }

            // Validate user exists
            $user = User::find($userId);
            if (!$user) {
                return $this->jsonResponse(['success' => false, 'message' => 'User not found']);
            }

            $validAssignments = array_values(array_filter(
                is_array($assignments) ? $assignments : [],
                fn (array $assignment): bool => $this->validateAssignmentData($assignment)
            ));

            if (empty($validAssignments)) {
                return $this->jsonResponse(['success' => false, 'message' => 'At least one valid assignment is required']);
            }

            $this->db->beginTransaction();

            try {
                $activeAssignments = $this->getActiveUserAssignments($userId);
                $hasSingleActiveConstraint = $this->hasSingleActiveAssignmentConstraint();

                if ($hasSingleActiveConstraint && count($validAssignments) > 1) {
                    throw new Exception('Current assignment schema supports only one active assignment per user.');
                }

                $this->deactivateResponsibilityAssignments($userId);

                if ($hasSingleActiveConstraint && count($validAssignments) === 1 && count($activeAssignments) === 1) {
                    $assignmentId = $this->updateActiveUserAssignmentInPlace(
                        (int) $activeAssignments[0]['id'],
                        $userId,
                        $validAssignments[0]
                    );

                    $this->db->commit();

                    return $this->jsonResponse([
                        'success' => true,
                        'message' => 'User assignments updated successfully',
                        'data' => ['assignment_ids' => [$assignmentId]]
                    ]);
                }

                $this->deactivateUserAssignments($userId);

                // Create new assignments
                $newAssignments = [];
                foreach ($validAssignments as $assignment) {
                    $assignmentId = $this->createUserAssignment($userId, $assignment);
                    $newAssignments[] = $assignmentId;
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
            $this->getLogger()->error('Error updating user assignments', [
                'error' => $e->getMessage(),
                'user_id' => $userId ?? null,
                'admin_user_id' => $_SESSION['user']['id'] ?? null,
            ]);

            return $this->jsonResponse(['success' => false, 'message' => 'Error updating assignments: ' . $e->getMessage()]);
        }
    }

    public function backfillResponsibilities()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
        }

        try {
            $this->validateCsrfToken();

            if (!$this->db->tableExists('user_assignments')) {
                return $this->jsonResponse(['success' => false, 'message' => 'User assignments table is unavailable']);
            }

            $assignmentColumns = [
                'id',
                'user_id',
                'position_id',
                'level_scope',
                'organizational_unit_id',
                'global_id',
                'godina_id',
                'gamta_id',
                'gurmu_id',
            ];

            foreach (['start_date', 'term_start', 'end_date', 'term_end', 'notes'] as $optionalColumn) {
                if ($this->db->columnExists('user_assignments', $optionalColumn)) {
                    $assignmentColumns[] = $optionalColumn;
                }
            }

            $assignments = $this->db->fetchAll(
                "SELECT " . implode(', ', $assignmentColumns) . "
                 FROM user_assignments
                 WHERE status = 'active'
                 ORDER BY user_id ASC, id ASC"
            );

            $processed = 0;
            $skipped = 0;

            foreach ($assignments as $assignment) {
                $payload = [
                    'position_id' => (int) ($assignment['position_id'] ?? 0),
                    'hierarchy_level' => (string) ($assignment['level_scope'] ?? 'global'),
                    'hierarchy_id' => $this->resolveAssignmentHierarchyId($assignment),
                    'start_date' => $assignment['start_date'] ?? $assignment['term_start'] ?? date('Y-m-d'),
                    'end_date' => $assignment['end_date'] ?? $assignment['term_end'] ?? null,
                    'notes' => $assignment['notes'] ?? 'Legacy assignment responsibility backfill',
                ];

                if (!$this->validateAssignmentData($payload) || (int) ($assignment['user_id'] ?? 0) <= 0) {
                    $skipped++;
                    continue;
                }

                $this->syncResponsibilitiesForAssignment((int) $assignment['user_id'], $payload);
                $processed++;
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Responsibility backfill completed successfully',
                'data' => [
                    'processed_assignments' => $processed,
                    'skipped_assignments' => $skipped,
                ]
            ]);
        } catch (Exception $e) {
            $this->getLogger()->error('Error backfilling legacy responsibilities', [
                'error' => $e->getMessage(),
                'admin_user_id' => $_SESSION['user']['id'] ?? null,
            ]);

            return $this->jsonResponse(['success' => false, 'message' => 'Responsibility backfill failed: ' . $e->getMessage()]);
        }
    }

    // Private helper methods

    /**
     * Enforce System Admin access
     */
    private function enforceSystemAdminAccess()
    {
        $currentUser = auth_user();
        $normalizedRole = function_exists('normalized_user_role') ? normalized_user_role($currentUser) : ($currentUser['role'] ?? null);

        if (!$currentUser || !in_array($normalizedRole, ['admin', 'system_admin', 'super_admin'], true)) {
            if (($currentUser['role'] ?? null) === 'admin') {
                return;
            }

            if ($this->isAjaxRequest()) {
                echo json_encode(['success' => false, 'message' => 'Access denied. System Admin required.']);
                exit;
            }
            
            $this->setError('Access denied. System Administrator privileges required.');
            $this->redirect('/dashboard');
            exit;
        }
    }

    /**
     * Extract user data from POST request
     */
    private function extractUserData(): array
    {
        $role = $this->normalizeRegistrationRole((string) ($_POST['role'] ?? 'member'));

        return [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $role,
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
                $hasInput = !empty($assignment['position_id'])
                    || !empty($assignment['hierarchy_level'])
                    || !empty($assignment['hierarchy_id'])
                    || !empty($assignment['notes'])
                    || !empty($assignment['start_date'])
                    || !empty($assignment['end_date']);

                if (!$hasInput) {
                    continue;
                }

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
        $role = $userData['role'] ?? 'member';
        $hasLeadershipAssignments = $this->hasLeadershipAssignments($positionData);
        $hasPlacement = $this->hasPlacementSelection($positionData);

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
        $allowedRoles = ['member', 'executive', 'admin', 'system_admin'];
        if (!in_array($userData['role'], $allowedRoles)) {
            return ['valid' => false, 'message' => 'Invalid user role'];
        }

        if (!$hasPlacement) {
            return ['valid' => false, 'message' => 'At least one organizational placement is required'];
        }

        foreach ($positionData as $assignment) {
            if (empty($assignment['hierarchy_level'])) {
                return ['valid' => false, 'message' => 'Hierarchy level is required for all placements'];
            }

            if (($assignment['hierarchy_level'] ?? '') !== 'global' && empty($assignment['hierarchy_id'])) {
                return ['valid' => false, 'message' => 'Organizational unit is required for non-global placements'];
            }
        }

        if ($this->roleRequiresLeadershipAssignments($role) && !$hasLeadershipAssignments) {
            return ['valid' => false, 'message' => 'At least one leadership position assignment is required for this role'];
        }

        if (!$this->roleRequiresLeadershipAssignments($role) && $hasLeadershipAssignments) {
            return ['valid' => false, 'message' => 'Members cannot be assigned leadership positions or responsibilities. Change the role to Executive to add assignments.'];
        }

        return ['valid' => true, 'message' => 'Validation passed'];
    }

    /**
     * Create user account
     */
    private function createUserAccount(array $userData, array $primaryAssignment): int
    {
        // Generate temporary password
        $temporaryPassword = $this->generateTemporaryPassword();
        $passwordHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);
        $resolvedScope = $this->resolveUserScopeData($primaryAssignment);

        $storageRole = match ($userData['role']) {
            'system_admin' => 'system_admin',
            'admin' => 'admin',
            'executive' => 'executive',
            default => 'member',
        };
        
        $userRecord = $this->filterTableData('users', [
            'first_name' => $userData['first_name'],
            'middle_name' => $userData['middle_name'] ?: null,
            'last_name' => $userData['last_name'],
            'email' => $userData['email'],
            'personal_email' => $userData['email'],
            'phone' => $userData['phone'],
            'personal_phone' => $userData['phone'] ?: null,
            'password_hash' => $passwordHash,
            'password' => $passwordHash,
            'user_type' => $storageRole,
            'position_id' => $primaryAssignment['position_id'] ?? null,
            'level_scope' => $resolvedScope['level_scope'],
            'gurmu_id' => $resolvedScope['gurmu_id'],
            'status' => 'active',
            'date_of_birth' => $userData['date_of_birth'] ?: null,
            'birth_date' => $userData['date_of_birth'] ?: null,
            'gender' => $userData['gender'] ?: null,
            'address' => $userData['address'],
            'emergency_contact' => !empty($userData['emergency_contact'])
                ? json_encode(['details' => $userData['emergency_contact']])
                : null,
            'language_preference' => $userData['language_preference'] ?: 'en',
            'language' => $userData['language_preference'] ?: 'en',
            'registration_source' => 'admin_created',
            'account_type' => 'internal_only',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'personal_email_verified' => 1,
            'onboarding_completed' => 0,
            'created_by' => $_SESSION['user']['id'],
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
        ]);

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
        $assignmentData = $this->buildUserAssignmentPayload($userId, $assignment, true);

        $assignmentId = $this->db->insert('user_assignments', $assignmentData);
        $this->syncResponsibilitiesForAssignment($userId, $assignment);

        return $assignmentId;
    }

    private function updateActiveUserAssignmentInPlace(int $assignmentId, int $userId, array $assignment): int
    {
        $assignmentData = $this->buildUserAssignmentPayload($userId, $assignment, false);

        $this->db->update('user_assignments', $assignmentData, ['id' => $assignmentId]);
        $this->syncResponsibilitiesForAssignment($userId, $assignment);

        return $assignmentId;
    }

    private function buildUserAssignmentPayload(int $userId, array $assignment, bool $includeCreatedAt): array
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

        $payload = [
            'user_id' => $userId,
            'position_id' => $assignment['position_id'],
            'organizational_unit_id' => $assignment['hierarchy_id'] ?: null,
            'level_scope' => $assignment['hierarchy_level'],
            'start_date' => $assignment['start_date'],
            'term_start' => $assignment['start_date'],
            'end_date' => $assignment['end_date'],
            'term_end' => $assignment['end_date'],
            'status' => 'active',
            'appointment_type' => 'appointed',
            'assigned_by' => $_SESSION['user']['id'],
            'assignment_reason' => $assignment['notes'] ?: 'Assigned during admin registration',
            'notes' => $assignment['notes'] ?: null,
            'metadata' => json_encode(['notes' => $assignment['notes'] ?: null]),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($includeCreatedAt) {
            $payload['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->filterTableData('user_assignments', $payload)
            + $this->filterTableData('user_assignments', $scopeColumns);
    }

    private function getActiveUserAssignments(int $userId): array
    {
        if (!$this->db->tableExists('user_assignments')) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT id
             FROM user_assignments
             WHERE user_id = ? AND status = 'active'
             ORDER BY id ASC",
            [$userId]
        );
    }

    private function hasSingleActiveAssignmentConstraint(): bool
    {
        if (!$this->db->tableExists('user_assignments')) {
            return false;
        }

        try {
            $indexes = $this->db->fetchAll("SHOW INDEX FROM user_assignments WHERE Key_name = 'unique_active_user_assignment'");
            return !empty($indexes);
        } catch (Exception $e) {
            return false;
        }
    }

    private function deactivateUserAssignments(int $userId): void
    {
        $payload = $this->filterTableData('user_assignments', [
            'status' => 'inactive',
            'updated_at' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d'),
            'term_end' => date('Y-m-d'),
        ]);

        if (empty($payload)) {
            return;
        }

        $this->db->update('user_assignments', $payload, ['user_id' => $userId, 'status' => 'active']);
    }

    private function deactivateResponsibilityAssignments(int $userId): void
    {
        if (!$this->db->tableExists('responsibility_assignments')) {
            return;
        }

        $payload = $this->filterTableData('responsibility_assignments', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
            'notes' => 'Superseded by updated user leadership assignments.',
        ]);

        if (empty($payload)) {
            return;
        }

        $this->db->update('responsibility_assignments', $payload, ['user_id' => $userId]);
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

        $userUpdate = $this->filterTableData('users', [
            'internal_email' => $primaryEmail,
            'internal_account_created_at' => date('Y-m-d H:i:s'),
            'internal_credentials_sent_at' => date('Y-m-d H:i:s')
        ]);

        if (!empty($userUpdate)) {
            $this->db->update('users', $userUpdate, ['id' => $userId]);
        }

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

            $this->getEmailService()->sendWelcomeEmail(
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
        return $this->getAssignmentPositionsForLevel('global');
    }

    private function normalizeRegistrationRole(string $role): string
    {
        return match (trim(strtolower($role))) {
            'user', 'member' => 'member',
            'leader', 'executive' => 'executive',
            'admin', 'administrator' => 'admin',
            'system_admin', 'system administrator', 'super_admin' => 'system_admin',
            default => trim(strtolower($role)),
        };
    }

    private function roleRequiresLeadershipAssignments(string $role): bool
    {
        return in_array($role, ['executive', 'admin', 'system_admin'], true);
    }

    private function hasLeadershipAssignments(array $assignments): bool
    {
        foreach ($assignments as $assignment) {
            if (!empty($assignment['position_id']) && !empty($assignment['hierarchy_level'])) {
                return true;
            }
        }

        return false;
    }

    private function hasPlacementSelection(array $assignments): bool
    {
        foreach ($assignments as $assignment) {
            if (empty($assignment['hierarchy_level'])) {
                continue;
            }

            if (($assignment['hierarchy_level'] ?? '') === 'global' || !empty($assignment['hierarchy_id'])) {
                return true;
            }
        }

        return false;
    }

    private function resolvePrimaryPlacement(array $userData, array $assignments): array
    {
        foreach ($assignments as $assignment) {
            if (empty($assignment['hierarchy_level'])) {
                continue;
            }

            if (($assignment['hierarchy_level'] ?? '') !== 'global' && empty($assignment['hierarchy_id'])) {
                continue;
            }

            if (!$this->roleRequiresLeadershipAssignments($userData['role'] ?? 'member')) {
                return $assignment;
            }

            if (!empty($assignment['position_id'])) {
                return $assignment;
            }
        }

        return [];
    }

    private function getAssignmentPositionsForLevel(string $level): array
    {
        $nameColumn = $this->getPositionNameColumn();
        $nameOmColumn = $this->hasPositionColumn('name_om') ? 'name_om' : $nameColumn;
        $scopeColumn = $this->getPositionHierarchyColumn();
        $executiveExpression = $this->hasPositionColumn('is_executive') ? 'is_executive' : '1';
        $whereClauses = [];

        if ($this->hasPositionColumn('status')) {
            $whereClauses[] = "status = 'active'";
        }

        $rows = $this->db->fetchAll(
            "SELECT id, key_name, {$nameColumn} AS name, {$nameOmColumn} AS name_om, {$scopeColumn} AS scope_value, {$executiveExpression} AS is_executive
             FROM positions"
             . (!empty($whereClauses) ? ' WHERE ' . implode(' AND ', $whereClauses) : '')
             . ' ORDER BY id ASC'
        );

        $canonicalKeys = $this->getCanonicalExecutivePositionKeys();
        $positions = [];

        foreach ($rows as $row) {
            $positionKey = (string) ($row['key_name'] ?? '');
            if ($positionKey !== '' && !in_array($positionKey, $canonicalKeys, true)) {
                continue;
            }

            $scopeValue = (string) ($row['scope_value'] ?? 'all');
            $appliesToAll = $scopeValue === '' || $scopeValue === 'all';
            if (!$appliesToAll && $scopeValue !== $level) {
                continue;
            }

            $displayName = (string) ($row['name'] ?? $positionKey ?: 'Position');
            $positions[] = [
                'id' => (int) ($row['id'] ?? 0),
                'key_name' => $positionKey,
                'name' => $displayName,
                'name_om' => (string) ($row['name_om'] ?? $displayName),
                'hierarchy_type' => $appliesToAll ? 'all' : $scopeValue,
                'is_executive' => (bool) ((int) ($row['is_executive'] ?? 1)),
                'responsibility_preview' => $this->buildPositionResponsibilityPreview($positionKey, $displayName, $level),
            ];
        }

        usort($positions, function (array $left, array $right) use ($canonicalKeys): int {
            $leftIndex = array_search($left['key_name'] ?? '', $canonicalKeys, true);
            $rightIndex = array_search($right['key_name'] ?? '', $canonicalKeys, true);

            return ((int) $leftIndex) <=> ((int) $rightIndex);
        });

        return $positions;
    }

    private function buildPositionResponsibilityPreview(string $positionKey, string $positionName, string $levelScope): array
    {
        $categories = $this->getCanonicalResponsibilityCategories();
        $shared = [];
        $individual = [];

        foreach ($categories as $categoryKey => $category) {
            $shared[] = [
                'key_name' => $categoryKey,
                'category' => $categoryKey,
                'name_en' => $category['shared_name_en'],
                'name_om' => $category['shared_name_om'],
                'description_en' => $category['shared_description_en'],
                'description_om' => $category['shared_description_om'],
                'responsibility_type' => 'shared',
            ];

            $individual[] = [
                'key_name' => $positionKey . '_' . $categoryKey,
                'category' => $categoryKey,
                'name_en' => $positionName . ': ' . $category['individual_name_en'],
                'name_om' => $positionName . ': ' . $category['individual_name_om'],
                'description_en' => sprintf('%s responsibilities for %s at %s scope.', $category['individual_name_en'], $positionName, ucfirst($levelScope)),
                'description_om' => sprintf('%s itti gaafatamummaa %s sadarkaa %s keessatti.', $category['individual_name_om'], $positionName, ucfirst($levelScope)),
                'responsibility_type' => 'individual',
                'position_scope' => $positionKey,
            ];
        }

        return [
            'shared' => $shared,
            'individual' => $individual,
        ];
    }

    private function syncResponsibilitiesForAssignment(int $userId, array $assignment): void
    {
        if (!$this->db->tableExists('responsibilities') || !$this->db->tableExists('responsibility_assignments')) {
            return;
        }

        $position = $this->db->fetch('SELECT * FROM positions WHERE id = ?', [(int) ($assignment['position_id'] ?? 0)]);
        if (!$position) {
            return;
        }

        $positionKey = (string) ($position['key_name'] ?? '');
        if ($positionKey === '') {
            return;
        }

        $positionNameColumn = $this->getPositionNameColumn();
        $positionName = (string) ($position[$positionNameColumn] ?? $positionKey);
        $levelScope = (string) ($assignment['hierarchy_level'] ?? 'global');
        $organizationalUnitId = (int) (($assignment['hierarchy_id'] ?? null) ?: ($levelScope === 'global' ? 1 : 0));
        if ($organizationalUnitId <= 0) {
            return;
        }

        $preview = $this->buildPositionResponsibilityPreview($positionKey, $positionName, $levelScope);
        $definitions = array_merge($preview['shared'], $preview['individual']);

        foreach ($definitions as $definition) {
            $responsibilityId = $this->ensureResponsibilityRecord($definition, $levelScope, $positionKey);
            if ($responsibilityId <= 0) {
                continue;
            }

            $existingAssignment = $this->db->fetch(
                'SELECT id, status FROM responsibility_assignments WHERE user_id = ? AND responsibility_id = ? AND position_id = ? AND organizational_unit_id = ? LIMIT 1',
                [$userId, $responsibilityId, (int) $assignment['position_id'], $organizationalUnitId]
            );

            if ($existingAssignment && !in_array((string) ($existingAssignment['status'] ?? ''), ['cancelled', 'inactive'], true)) {
                continue;
            }

            $assignmentPayload = $this->filterTableData('responsibility_assignments', [
                'user_id' => $userId,
                'responsibility_id' => $responsibilityId,
                'position_id' => (int) $assignment['position_id'],
                'organizational_unit_id' => $organizationalUnitId,
                'organizational_unit_type' => $levelScope,
                'level_scope' => $levelScope,
                'assignment_date' => date('Y-m-d H:i:s'),
                'priority' => '2',
                'status' => 'assigned',
                'completion_percentage' => 0,
                'notes' => 'Assigned automatically during user leadership registration.',
                'metadata' => json_encode([
                    'source' => 'user_leader_registration',
                    'position_key' => $positionKey,
                    'responsibility_type' => $definition['responsibility_type'] ?? 'individual',
                    'category' => $definition['category'] ?? null,
                ]),
                'assigned_by' => $_SESSION['user']['id'] ?? null,
            ]);

            if ($existingAssignment && !empty($assignmentPayload)) {
                $this->db->update('responsibility_assignments', $assignmentPayload, ['id' => (int) $existingAssignment['id']]);
                continue;
            }

            if (!empty($assignmentPayload)) {
                $this->db->insert('responsibility_assignments', $assignmentPayload);
            }
        }
    }

    private function resolveAssignmentHierarchyId(array $assignment): ?int
    {
        $levelScope = (string) ($assignment['level_scope'] ?? 'global');

        return match ($levelScope) {
            'global' => 1,
            'godina' => !empty($assignment['godina_id']) ? (int) $assignment['godina_id'] : null,
            'gamta' => !empty($assignment['gamta_id']) ? (int) $assignment['gamta_id'] : null,
            'gurmu' => !empty($assignment['gurmu_id']) ? (int) $assignment['gurmu_id'] : null,
            default => !empty($assignment['organizational_unit_id']) ? (int) $assignment['organizational_unit_id'] : null,
        };
    }

    private function ensureResponsibilityRecord(array $definition, string $levelScope, string $positionKey): int
    {
        $keyName = (string) ($definition['key_name'] ?? '');
        if ($keyName === '') {
            return 0;
        }

        $existingId = (int) ($this->db->fetchColumn('SELECT id FROM responsibilities WHERE key_name = ? LIMIT 1', [$keyName]) ?? 0);
        if ($existingId > 0) {
            return $existingId;
        }

        $payload = $this->filterTableData('responsibilities', [
            'key_name' => $keyName,
            'name_en' => (string) ($definition['name_en'] ?? $keyName),
            'name_om' => (string) ($definition['name_om'] ?? $keyName),
            'description_en' => (string) ($definition['description_en'] ?? ''),
            'description_om' => (string) ($definition['description_om'] ?? ''),
            'responsibility_type' => ($definition['responsibility_type'] ?? 'individual') === 'shared' ? 'shared' : 'individual',
            'category' => ($definition['responsibility_type'] ?? 'individual') === 'shared' ? 'core' : 'position_specific',
            'level_scope' => ($definition['responsibility_type'] ?? 'individual') === 'shared' ? $levelScope : 'all',
            'position_scope' => ($definition['responsibility_type'] ?? 'individual') === 'shared' ? 'all' : $positionKey,
            'is_shared' => ($definition['responsibility_type'] ?? 'individual') === 'shared' ? 1 : 0,
            'priority' => ($definition['responsibility_type'] ?? 'individual') === 'shared' ? 1 : 2,
            'frequency' => 30,
            'status' => 'active',
            'metadata' => json_encode([
                'source' => 'COMPREHENSIVE_POSITIONS_RESPONSIBILITIES_DOCUMENTATION',
                'category' => $definition['category'] ?? null,
            ]),
        ]);

        return !empty($payload) ? (int) $this->db->insert('responsibilities', $payload) : 0;
    }

    private function getCanonicalExecutivePositionKeys(): array
    {
        return [
            'barreessaa',
            'dinagdee',
            'diplomaasii_hawaasummaa',
            'dura_taa',
            'ijaarsaa_siyaasa',
            'mediyaa_sab_quunnamtii',
            'tohannoo_keessaa',
        ];
    }

    private function getCanonicalResponsibilityCategories(): array
    {
        return [
            'gabaasa' => [
                'shared_name_en' => 'Collective Reporting & Documentation',
                'shared_name_om' => 'Gabaasa fi Galmee Waliigalaa',
                'shared_description_en' => 'Joint preparation of comprehensive organizational reports and documentation.',
                'shared_description_om' => 'Qopheessuu waliigalaa gabaasa fi galmee bal\'aa dhaabbataa.',
                'individual_name_en' => 'Reporting & Documentation',
                'individual_name_om' => 'Gabaasa',
            ],
            'gamaaggama' => [
                'shared_name_en' => 'Team Evaluation & Assessment',
                'shared_name_om' => 'Gamaaggama fi Madaallii Garee',
                'shared_description_en' => 'Collaborative evaluation and assessment of organizational performance.',
                'shared_description_om' => 'Gamaaggama fi madaallii tumsaa raawwii dhaabbataa.',
                'individual_name_en' => 'Evaluation & Assessment',
                'individual_name_om' => 'Gamaaggama',
            ],
            'karoora' => [
                'shared_name_en' => 'Collaborative Planning & Strategic Development',
                'shared_name_om' => 'Karoora Tumsaa fi Misooma Tarsiimoo',
                'shared_description_en' => 'Joint strategic planning development and coordinated decision making.',
                'shared_description_om' => 'Misooma karoora tarsiimoo waliigalaa fi murtii qindoomina.',
                'individual_name_en' => 'Planning & Strategic Development',
                'individual_name_om' => 'Karoora',
            ],
            'projektoota' => [
                'shared_name_en' => 'Joint Projects & Initiatives',
                'shared_name_om' => 'Pirojektii fi Jalqabni Waliigalaa',
                'shared_description_en' => 'Cross-functional project and initiative management and implementation.',
                'shared_description_om' => 'Bulchiinsa pirojektii fi jalqabni hojii-garagar-qaban fi hojiirra oolmaa.',
                'individual_name_en' => 'Projects & Initiatives',
                'individual_name_om' => 'Projektoota',
            ],
            'qaboo_yaii' => [
                'shared_name_en' => 'Shared Meetings Management',
                'shared_name_om' => 'Bulchiinsa Qaboo Ya\'ii Qoodamaa',
                'shared_description_en' => 'Collective meeting management and coordination.',
                'shared_description_om' => 'Bulchiinsa fi qindoomina qaboo ya\'ii waliigalaa.',
                'individual_name_en' => 'Meetings Management',
                'individual_name_om' => 'Qaboo Ya\'ii',
            ],
        ];
    }

    private function getPositionColumns(): array
    {
        if ($this->positionColumns !== null) {
            return $this->positionColumns;
        }

        $columns = $this->db->fetchAll('SHOW COLUMNS FROM positions');
        $this->positionColumns = array_column($columns, 'Field');

        return $this->positionColumns;
    }

    private function hasPositionColumn(string $column): bool
    {
        return in_array($column, $this->getPositionColumns(), true);
    }

    private function getPositionNameColumn(): string
    {
        if ($this->hasPositionColumn('name')) {
            return 'name';
        }

        if ($this->hasPositionColumn('name_en')) {
            return 'name_en';
        }

        return 'key_name';
    }

    private function getPositionHierarchyColumn(): string
    {
        if ($this->hasPositionColumn('hierarchy_type')) {
            return 'hierarchy_type';
        }

        if ($this->hasPositionColumn('level_scope')) {
            return 'level_scope';
        }

        return 'level';
    }

    private function filterTableData(string $table, array $data): array
    {
        $filtered = [];

        foreach ($data as $column => $value) {
            if ($this->db->columnExists($table, $column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function resolveUserScopeData(array $assignment): array
    {
        $levelScope = $assignment['hierarchy_level'] ?? 'global';
        $hierarchyId = isset($assignment['hierarchy_id']) ? (int) $assignment['hierarchy_id'] : 0;

        $gurmuId = match ($levelScope) {
            'gurmu' => $hierarchyId,
            'gamta' => (int) ($this->db->fetchColumn(
                'SELECT id FROM gurmus WHERE gamta_id = ? ORDER BY id LIMIT 1',
                [$hierarchyId]
            ) ?? 0),
            'godina' => (int) ($this->db->fetchColumn(
                'SELECT gu.id FROM gurmus gu JOIN gamtas ga ON gu.gamta_id = ga.id WHERE ga.godina_id = ? ORDER BY gu.id LIMIT 1',
                [$hierarchyId]
            ) ?? 0),
            'global' => (int) ($this->db->fetchColumn(
                'SELECT id FROM gurmus ORDER BY id LIMIT 1'
            ) ?? 0),
            default => 0,
        };

        if ($gurmuId <= 0) {
            throw new Exception('Unable to resolve a Gurmu scope for the selected assignment.');
        }

        return [
            'level_scope' => $levelScope,
            'gurmu_id' => $gurmuId,
        ];
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
                u.user_type AS role,
                u.status,
                u.created_at,
                COUNT(ua.id) as position_count,
                GROUP_CONCAT(p.name SEPARATOR ', ') as positions
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
                        WHERE JSON_VALID(u.metadata)
                            AND CAST(JSON_UNQUOTE(JSON_EXTRACT(u.metadata, '$.registered_by')) AS UNSIGNED) = ?
            GROUP BY u.id, u.first_name, u.last_name, u.email, u.internal_email, u.user_type, u.status, u.created_at
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
        $metadataScope = "JSON_VALID(metadata) AND CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.registered_by')) AS UNSIGNED) = ?";
        $joinedMetadataScope = "JSON_VALID(u.metadata) AND CAST(JSON_UNQUOTE(JSON_EXTRACT(u.metadata, '$.registered_by')) AS UNSIGNED) = ?";
        
        return [
            'total_users' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE {$metadataScope}", [$adminId]),
            'this_month' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE {$metadataScope} AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())", [$adminId]),
            'this_week' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE {$metadataScope} AND WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())", [$adminId]),
            'today' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE {$metadataScope} AND DATE(created_at) = DATE(NOW())", [$adminId]),
            'active_assignments' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM user_assignments ua JOIN users u ON ua.user_id = u.id WHERE {$joinedMetadataScope} AND ua.status = 'active'", [$adminId])
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
        $this->getLogger()->info('User/Leader registered by System Admin', [
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