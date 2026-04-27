<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Utils\Database;

/**
 * User Controller
 * ABO-WBO Management System
 */
class UserController extends Controller
{
    protected $userModel;
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userModel = new User();
    }
    
    /**
     * Display list of users
     */
    public function index()
    {
        $this->requireAuth();
        
        // Get pagination parameters
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Get search and filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $role = $_GET['role'] ?? '';
        
        // Build query conditions
        $conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.internal_email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if (!empty($status)) {
            $conditions[] = "u.status = ?";
            $params[] = $status;
        }
        
        if (!empty($role)) {
            $conditions[] = "u.user_type = ?";
            $params[] = $role;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $countQuery = "SELECT COUNT(DISTINCT u.id) as total FROM users u {$whereClause}";
        $totalUsers = (int) ($this->db->fetch($countQuery, $params)['total'] ?? 0);
        $totalPages = ceil($totalUsers / $limit);

        $positionNameExpression = $this->getPositionNameExpression('p');
        $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.internal_email, u.phone, u.user_type, u.status,
                         u.last_login_at, u.created_at, u.email_verified_at,
                         COUNT(DISTINCT ua.id) AS assignment_count,
                         GROUP_CONCAT(DISTINCT CONCAT(
                             {$positionNameExpression},
                             ' | ',
                             CASE
                                 WHEN ua.level_scope = 'global' THEN 'Global'
                                 WHEN ua.level_scope = 'godina' THEN COALESCE(god.name, 'Godina')
                                 WHEN ua.level_scope = 'gamta' THEN COALESCE(gam.name, 'Gamta')
                                 WHEN ua.level_scope = 'gurmu' THEN COALESCE(gur.name, 'Gurmu')
                                 ELSE COALESCE(ua.level_scope, 'Unscoped')
                             END
                         ) ORDER BY FIELD(ua.level_scope, 'global', 'godina', 'gamta', 'gurmu') SEPARATOR '||') AS positions
                  FROM users u
                  LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
                  LEFT JOIN positions p ON ua.position_id = p.id
                  LEFT JOIN godinas god ON ua.godina_id = god.id
                  LEFT JOIN gamtas gam ON ua.gamta_id = gam.id
                  LEFT JOIN gurmus gur ON ua.gurmu_id = gur.id
                  {$whereClause}
                  GROUP BY u.id, u.first_name, u.last_name, u.email, u.internal_email, u.phone, u.user_type, u.status,
                           u.last_login_at, u.created_at, u.email_verified_at
                  ORDER BY u.created_at DESC
                  LIMIT {$limit} OFFSET {$offset}";
        $users = $this->db->fetchAll($query, $params);

        foreach ($users as &$user) {
            $roleKey = $user['user_type'] ?? 'member';
            $user['role_key'] = $roleKey;
            $user['role_label'] = ucwords(str_replace('_', ' ', $roleKey));
            $positionSummary = array_values(array_filter(array_map('trim', explode('||', (string) ($user['positions'] ?? '')))));
            $user['position_summary'] = $positionSummary;
            $user['primary_position'] = $positionSummary[0] ?? null;
        }
        unset($user);
        
        echo $this->render('users.index', [
            'title' => 'User Management',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'search' => $search,
            'statusFilter' => $status,
            'roleFilter' => $role,
            'positions' => $this->getManagementPositions(),
            'godinas' => $this->getHierarchyRecords('godinas', 'name'),
            'gamtas' => $this->getHierarchyRecords('gamtas', 'name', ['godina_id']),
            'gurmus' => $this->getHierarchyRecords('gurmus', 'name', ['gamta_id'])
        ]);
    }
    
    /**
     * Show create user form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requirePermission('user.create');

        return $this->redirect('/admin/user-leader-registration');
    }
    
    /**
     * Store new user
     */
    public function store()
    {
        $this->requireAuth();
        $this->requirePermission('user.create');
        $this->requireCsrf();
        
        try {
            $data = $this->validate([
                'first_name' => 'required|min:2|max:100',
                'last_name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|min:10|max:20',
                'date_of_birth' => 'date',
                'address' => 'max:255',
                'role' => 'required|in:admin,executive,member,guest',
                'status' => 'required|in:active,inactive,pending,suspended',
                'password' => 'required|min:8|confirmed',
                'hierarchy_level' => 'required|in:global,godina,gamta,gurmu',
                'position_id' => 'required|exists:positions,id',
                'godina_id' => 'exists:godinas,id',
                'gamta_id' => 'exists:gamtas,id',
                'gurmu_id' => 'exists:gurmus,id'
            ]);
            
            // Validate hierarchical assignment requirements
            $hierarchyLevel = $data['hierarchy_level'];
            $errors = [];
            
            switch ($hierarchyLevel) {
                case 'godina':
                    if (empty($data['godina_id'])) {
                        $errors['godina_id'] = 'Godina is required for godina-level assignments';
                    }
                    break;
                case 'gamta':
                    if (empty($data['gamta_id'])) {
                        $errors['gamta_id'] = 'Gamta is required for gamta-level assignments';
                    }
                    break;
                case 'gurmu':
                    if (empty($data['gurmu_id'])) {
                        $errors['gurmu_id'] = 'Gurmu is required for gurmu-level assignments';
                    }
                    break;
            }
            
            if (!empty($errors)) {
                $this->redirectBack($errors);
            }
            
            // Hash password
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Prepare birth date
            if (!empty($data['date_of_birth'])) {
                $data['birth_date'] = $data['date_of_birth'];
            }
            unset($data['password'], $data['password_confirmation'], $data['date_of_birth']);
            
            // Remove hierarchy level and organizational IDs from user data
            $hierarchyData = [
                'hierarchy_level' => $data['hierarchy_level'],
                'position_id' => $data['position_id'],
                'godina_id' => $data['godina_id'] ?? null,
                'gamta_id' => $data['gamta_id'] ?? null,
                'gurmu_id' => $data['gurmu_id'] ?? null
            ];
            
            unset($data['hierarchy_level'], $data['position_id'], $data['godina_id'], 
                  $data['gamta_id'], $data['gurmu_id'], $data['gamta_godina_id'], 
                  $data['gurmu_godina_id'], $data['gurmu_gamta_id']);
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Create user using Database insert method
                $insertData = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password_hash' => $data['password_hash'],
                    'birth_date' => $data['birth_date'] ?? null,
                    'address' => $data['address'] ?? null,
                    'role' => $data['role'],
                    'status' => $data['status']
                ];
                
                // Use Database class insert method
                $userId = $this->db->insert('users', $insertData);
                
                if (!$userId) {
                    throw new \Exception('Failed to create user');
                }
                
                // Create hierarchical assignment using Database insert method
                $currentUser = auth_user();
                $assignmentData = [
                    'user_id' => $userId,
                    'position_id' => $hierarchyData['position_id'],
                    'level_scope' => $hierarchyData['hierarchy_level'],
                    'godina_id' => $hierarchyData['godina_id'],
                    'gamta_id' => $hierarchyData['gamta_id'],
                    'gurmu_id' => $hierarchyData['gurmu_id'],
                    'assigned_by' => $currentUser ? $currentUser['id'] : null,
                    'start_date' => date('Y-m-d'),
                    'status' => 'active',
                    'appointment_type' => 'appointed'
                ];
                
                $assignmentId = $this->db->insert('user_assignments', $assignmentData);
                
                $this->db->commit();
                
                log_activity('user.created', "Created user: {$data['email']} with {$hierarchyData['hierarchy_level']} level assignment", [
                    'user_id' => $userId,
                    'assignment_level' => $hierarchyData['hierarchy_level'],
                    'position_id' => $hierarchyData['position_id']
                ]);
                
                $this->redirectWithMessage('/users', 'User created successfully with organizational assignment!', 'success');
                
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            log_error('User creation error: ' . $e->getMessage());
            $this->redirectBack(['general' => 'An error occurred while creating the user.']);
        }
    }
    
    /**
     * Show user details
     */
    public function show($id)
    {
        $this->requireAuth();

        $payload = $this->getManagedUserPayload((int) $id);
        if (!$payload) {
            if ($this->wantsJsonResponse()) {
                $this->error('User not found.', null, 404);
            }

            $this->redirectWithMessage('/users', 'User not found.', 'error');
        }

        if ($this->wantsJsonResponse()) {
            $this->success($payload);
        }

        $this->redirect('/users?view=' . (int) $id);
    }
    
    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requirePermission('user.edit');

        $payload = $this->getManagedUserPayload((int) $id);
        if (!$payload) {
            if ($this->wantsJsonResponse()) {
                $this->error('User not found.', null, 404);
            }

            $this->redirectWithMessage('/users', 'User not found.', 'error');
        }

        if ($this->wantsJsonResponse()) {
            $payload['options'] = [
                'positions' => $this->getManagementPositions(),
                'godinas' => $this->getHierarchyRecords('godinas', 'name'),
                'gamtas' => $this->getHierarchyRecords('gamtas', 'name', ['godina_id']),
                'gurmus' => $this->getHierarchyRecords('gurmus', 'name', ['gamta_id']),
            ];
            $this->success($payload);
        }

        $this->redirect('/users?edit=' . (int) $id);
    }
    
    /**
     * Update user
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requirePermission('user.edit');
        $this->requireCsrf();
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->redirectWithMessage('/users', 'User not found.', 'error');
        }
        
        try {
            $data = $this->validate([
                'first_name' => 'required|min:2|max:100',
                'last_name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'required|min:10|max:20',
                'date_of_birth' => 'date',
                'gender' => 'required|in:male,female,other',
                'address' => 'max:255',
                'city' => 'max:100',
                'state' => 'max:100',
                'country' => 'required|max:100',
                'role' => 'required|in:admin,user,moderator',
                'status' => 'required|in:active,inactive,pending',
                'position_id' => 'exists:positions,id',
                'gamta_id' => 'exists:gamtas,id'
            ]);
            
            // Handle password update if provided
            if (!empty($_POST['password'])) {
                $passwordData = $this->validate([
                    'password' => 'required|min:8|confirmed'
                ]);
                $data['password_hash'] = password_hash($passwordData['password'], PASSWORD_DEFAULT);
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = auth_user()['id'];
            
            $success = $this->userModel->update($id, $data);
            
            if ($success) {
                log_activity('user.updated', "Updated user: {$data['email']}", ['user_id' => $id]);
                $this->redirectWithMessage('/users', 'User updated successfully!', 'success');
            } else {
                $this->redirectBack(['general' => 'Failed to update user. Please try again.']);
            }
            
        } catch (\Exception $e) {
            log_error('User update error: ' . $e->getMessage());
            $this->redirectBack(['general' => 'An error occurred while updating the user.']);
        }
    }
    
    /**
     * Delete user
     */
    public function destroy($id)
    {
        $this->requireAuth();
        $this->requirePermission('user.delete');
        $this->requireCsrf();
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->redirectWithMessage('/users', 'User not found.', 'error');
        }
        
        // Prevent deleting own account
        if ($id == auth_user()['id']) {
            $this->redirectWithMessage('/users', 'You cannot delete your own account.', 'error');
        }
        
        try {
            $success = $this->userModel->softDelete($id, auth_user()['id'] ?? null);
            
            if ($success) {
                log_activity('user.deleted', "Deleted user: {$user['email']}", ['user_id' => $id]);
                if ($this->wantsJsonResponse()) {
                    $this->success(null, 'User deleted successfully!');
                }
                $this->redirectWithMessage('/users', 'User deleted successfully!', 'success');
            } else {
                if ($this->wantsJsonResponse()) {
                    $this->error('Failed to delete user.', null, 400);
                }
                $this->redirectWithMessage('/users', 'Failed to delete user.', 'error');
            }
            
        } catch (\Exception $e) {
            log_error('User deletion error: ' . $e->getMessage());
            if ($this->wantsJsonResponse()) {
                $this->error('An error occurred while deleting the user.', null, 500);
            }
            $this->redirectWithMessage('/users', 'An error occurred while deleting the user.', 'error');
        }
    }

    private function wantsJsonResponse(): bool
    {
        return $this->isAjax() || (($_GET['format'] ?? '') === 'json') || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
    }

    private function getManagedUserPayload(int $userId): ?array
    {
        $userColumns = [
            'id',
            'first_name',
            'last_name',
            'email',
            'user_type',
            'status',
        ];

        foreach (['middle_name', 'internal_email', 'phone', 'last_login_at', 'created_at', 'email_verified_at', 'metadata'] as $optionalColumn) {
            if ($this->db->columnExists('users', $optionalColumn)) {
                $userColumns[] = $optionalColumn;
            }
        }

        $user = $this->db->fetch(
            "SELECT " . implode(', ', $userColumns) . "
             FROM users
             WHERE id = ?
             LIMIT 1",
            [$userId]
        );

        if (!$user) {
            return null;
        }

        $assignmentColumns = ['ua.id', 'ua.position_id'];
        foreach (['level_scope', 'organizational_unit_id', 'godina_id', 'gamta_id', 'gurmu_id', 'start_date', 'end_date', 'notes'] as $optionalColumn) {
            if ($this->db->columnExists('user_assignments', $optionalColumn)) {
                $assignmentColumns[] = 'ua.' . $optionalColumn;
            }
        }

        $positionKeyExpression = $this->db->columnExists('positions', 'key_name') ? 'p.key_name' : 'NULL';
        $levelScopeExpression = $this->db->columnExists('user_assignments', 'level_scope') ? 'ua.level_scope' : "'global'";
        $godinaExpression = $this->db->columnExists('user_assignments', 'godina_id') ? 'ua.godina_id' : 'NULL';
        $gamtaExpression = $this->db->columnExists('user_assignments', 'gamta_id') ? 'ua.gamta_id' : 'NULL';
        $gurmuExpression = $this->db->columnExists('user_assignments', 'gurmu_id') ? 'ua.gurmu_id' : 'NULL';
        $startDateExpression = $this->db->columnExists('user_assignments', 'start_date') ? 'ua.start_date' : 'ua.created_at';
        $assignmentStatusClause = $this->db->columnExists('user_assignments', 'status') ? " AND ua.status = 'active'" : '';

        $assignments = $this->db->fetchAll(
            "SELECT " . implode(', ', $assignmentColumns) . ",
                    " . $this->getPositionNameExpression('p') . " AS position_name,
                    {$positionKeyExpression} AS position_key,
                    CASE
                        WHEN {$levelScopeExpression} = 'global' THEN 'Global'
                        WHEN {$levelScopeExpression} = 'godina' THEN COALESCE(god.name, 'Godina')
                        WHEN {$levelScopeExpression} = 'gamta' THEN COALESCE(gam.name, 'Gamta')
                        WHEN {$levelScopeExpression} = 'gurmu' THEN COALESCE(gur.name, 'Gurmu')
                        ELSE COALESCE({$levelScopeExpression}, 'Unscoped')
                    END AS organizational_unit_name
             FROM user_assignments ua
             LEFT JOIN positions p ON ua.position_id = p.id
             LEFT JOIN godinas god ON {$godinaExpression} = god.id
             LEFT JOIN gamtas gam ON {$gamtaExpression} = gam.id
             LEFT JOIN gurmus gur ON {$gurmuExpression} = gur.id
             WHERE ua.user_id = ?{$assignmentStatusClause}
             ORDER BY FIELD({$levelScopeExpression}, 'global', 'godina', 'gamta', 'gurmu'), {$startDateExpression} DESC",
            [$userId]
        );

        $responsibilitySummary = ['shared' => 0, 'individual' => 0, 'total' => 0];
        if ($this->db->tableExists('responsibility_assignments') && $this->db->tableExists('responsibilities')) {
            $responsibilitySummary = $this->db->fetch(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN r.is_shared = 1 THEN 1 ELSE 0 END) AS shared,
                    SUM(CASE WHEN r.is_shared = 0 THEN 1 ELSE 0 END) AS individual
                 FROM responsibility_assignments ra
                 INNER JOIN responsibilities r ON r.id = ra.responsibility_id
                 WHERE ra.user_id = ? AND ra.status != 'cancelled'",
                [$userId]
            ) ?: $responsibilitySummary;
        }

        $user['role_key'] = $user['user_type'] ?? 'member';
        $user['role_label'] = ucwords(str_replace('_', ' ', (string) $user['role_key']));

        return [
            'user' => $user,
            'assignments' => $assignments,
            'responsibility_summary' => [
                'shared' => (int) ($responsibilitySummary['shared'] ?? 0),
                'individual' => (int) ($responsibilitySummary['individual'] ?? 0),
                'total' => (int) ($responsibilitySummary['total'] ?? 0),
            ],
        ];
    }

    private function getPositionNameExpression(string $alias = 'p'): string
    {
        $qualified = static fn (string $column) => $alias . '.' . $column;

        if ($this->db->columnExists('positions', 'name_en') && $this->db->columnExists('positions', 'name')) {
            return 'COALESCE(' . $qualified('name_en') . ', ' . $qualified('name') . ', ' . $qualified('key_name') . ')';
        }

        if ($this->db->columnExists('positions', 'name_en')) {
            return 'COALESCE(' . $qualified('name_en') . ', ' . $qualified('key_name') . ')';
        }

        if ($this->db->columnExists('positions', 'name')) {
            return 'COALESCE(' . $qualified('name') . ', ' . $qualified('key_name') . ')';
        }

        return $qualified('key_name');
    }

    private function getManagementPositions(): array
    {
        $nameExpression = $this->getPositionNameExpression('p');
        $whereClause = $this->db->columnExists('positions', 'status') ? "WHERE p.status = 'active'" : '';

        return $this->db->fetchAll(
            "SELECT p.id, p.key_name, {$nameExpression} AS name,
                    " . ($this->db->columnExists('positions', 'level_scope') ? 'p.level_scope' : "'all'") . " AS level_scope
             FROM positions p
             {$whereClause}
             ORDER BY p.id ASC"
        );
    }

    private function getHierarchyRecords(string $table, string $displayColumn, array $extraColumns = []): array
    {
        if (!$this->db->tableExists($table)) {
            return [];
        }

        $columns = array_merge(['id', $displayColumn], $extraColumns);
        $safeColumns = array_values(array_filter($columns, fn ($column) => $this->db->columnExists($table, $column)));
        if (empty($safeColumns)) {
            return [];
        }

        $orderColumn = in_array('name', $safeColumns, true) ? 'name' : $safeColumns[1];

        return $this->db->fetchAll(
            'SELECT ' . implode(', ', $safeColumns) . ' FROM ' . $table . ' ORDER BY ' . $orderColumn
        );
    }
    
    /**
     * Show user profile edit form
     */
    public function editProfile()
    {
        $this->requireAuth();
        
        $user = $this->userModel->find(auth_user()['id']);
        
        echo $this->render('users.profile', [
            'title' => 'Edit Profile',
            'user' => $user
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $this->requireAuth();
        $this->requireCsrf();
        
        $userId = auth_user()['id'];
        
        try {
            $data = $this->validate([
                'first_name' => 'required|min:2|max:100',
                'last_name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email,' . $userId,
                'phone' => 'required|min:10|max:20',
                'date_of_birth' => 'date',
                'gender' => 'required|in:male,female,other',
                'address' => 'max:255',
                'city' => 'max:100',
                'state' => 'max:100',
                'country' => 'required|max:100'
            ]);
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $success = $this->userModel->update($userId, $data);
            
            if ($success) {
                // Update session data
                $updatedUser = $this->userModel->find($userId);
                session_set('user', $updatedUser);
                
                log_activity('profile.updated', 'Updated profile');
                $this->redirectWithMessage('/profile/edit', 'Profile updated successfully!', 'success');
            } else {
                $this->redirectBack(['general' => 'Failed to update profile. Please try again.']);
            }
            
        } catch (\Exception $e) {
            log_error('Profile update error: ' . $e->getMessage());
            $this->redirectBack(['general' => 'An error occurred while updating your profile.']);
        }
    }
    
    /**
     * Update user password
     */
    public function updatePassword()
    {
        $this->requireAuth();
        $this->requireCsrf();
        
        $userId = auth_user()['id'];
        $user = $this->userModel->find($userId);
        
        try {
            $data = $this->validate([
                'current_password' => 'required',
                'password' => 'required|min:8|confirmed'
            ]);
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password_hash'])) {
                $this->redirectBack(['current_password' => 'Current password is incorrect.']);
            }
            
            // Update password
            $success = $this->userModel->update($userId, [
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                log_activity('password.updated', 'Updated password');
                $this->redirectWithMessage('/profile/edit', 'Password updated successfully!', 'success');
            } else {
                $this->redirectBack(['general' => 'Failed to update password. Please try again.']);
            }
            
        } catch (\Exception $e) {
            log_error('Password update error: ' . $e->getMessage());
            $this->redirectBack(['general' => 'An error occurred while updating your password.']);
        }
    }
    
    /**
     * Get available positions for dropdowns
     */
    private function getAvailablePositions(): array
    {
        return $this->db->fetchAll("SELECT id, name FROM positions WHERE status = 'active' ORDER BY level, name");
    }
    
    /**
     * Get available gamtas for dropdowns
     */
    private function getAvailableGamtas(): array
    {
        return $this->db->fetchAll("SELECT id, name FROM gamtas WHERE status = 'active' ORDER BY name");
    }
    
    /**
     * Get hierarchy data for dropdowns
     */
    private function getHierarchyData(): array
    {
        $global = $this->db->fetch("SELECT * FROM globals WHERE status = 'active' ORDER BY name LIMIT 1");
        
        $godinas = $this->db->fetchAll("
            SELECT g.id, g.name, g.code, 
                   COUNT(gam.id) as gamta_count,
                   g.description
            FROM godinas g 
            LEFT JOIN gamtas gam ON g.id = gam.godina_id 
            WHERE g.status = 'active' 
            GROUP BY g.id, g.name, g.code, g.description
            ORDER BY g.name, g.code
        ");
        
        $gamtas = $this->db->fetchAll("
            SELECT g.id, g.name, g.code, g.godina_id, god.name as godina_name 
            FROM gamtas g 
            JOIN godinas god ON g.godina_id = god.id 
            WHERE g.status = 'active' 
            ORDER BY god.name, g.name
        ");
        
        $gurmus = $this->db->fetchAll("
            SELECT gu.id, gu.name, gu.code, gu.gamta_id, gam.name as gamta_name, god.name as godina_name
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
     * Get Gamtas by Godina ID (AJAX endpoint)
     */
    public function getGamtasByGodina()
    {
        $this->requireAuth();
        
        $godinaId = $_GET['godina_id'] ?? null;
        if (!$godinaId) {
            $this->json(['success' => false, 'message' => 'Godina ID is required']);
        }
        
        try {
            $gamtas = $this->db->fetchAll("
                SELECT id, name, code, description 
                FROM gamtas 
                WHERE godina_id = ? AND status = 'active'
                ORDER BY name
            ", [$godinaId]);
            
            $this->json(['success' => true, 'gamtas' => $gamtas]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to load gamtas']);
        }
    }
    
    /**
     * Get Gurmus by Gamta ID (AJAX endpoint)
     */
    public function getGurmusByGamta()
    {
        $this->requireAuth();
        
        $gamtaId = $_GET['gamta_id'] ?? null;
        if (!$gamtaId) {
            $this->json(['success' => false, 'message' => 'Gamta ID is required']);
        }
        
        try {
            $gurmus = $this->db->fetchAll("
                SELECT id, name, code, description 
                FROM gurmus 
                WHERE gamta_id = ? AND status = 'active'
                ORDER BY name
            ", [$gamtaId]);
            
            $this->json(['success' => true, 'gurmus' => $gurmus]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to load gurmus']);
        }
    }
}