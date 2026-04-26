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
        
        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM users u {$whereClause}";
        $totalUsers = $this->db->fetch($countQuery, $params)['total'];
        $totalPages = ceil($totalUsers / $limit);
        
        // Get users with pagination
        $query = "SELECT u.*
                  FROM users u
                  {$whereClause}
                  ORDER BY u.created_at DESC
                  LIMIT {$limit} OFFSET {$offset}";
        $users = $this->db->fetchAll($query, $params);

        foreach ($users as &$user) {
            $roleKey = $user['user_type'] ?? 'member';
            $user['role_key'] = $roleKey;
            $user['role_label'] = ucwords(str_replace('_', ' ', $roleKey));
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
            'roleFilter' => $role
        ]);
    }
    
    /**
     * Show create user form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requirePermission('user.create');
        
        echo $this->render('users.create', [
            'title' => 'Create User',
            'positions' => $this->getAvailablePositions(),
            'hierarchy_data' => $this->getHierarchyData(),
            'gamtas' => $this->getAvailableGamtas()
        ]);
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
        
        $user = $this->userModel->findWithRelations($id);
        
        if (!$user) {
            $this->redirectWithMessage('/users', 'User not found.', 'error');
        }
        
        // Get user's activity log
        $activities = $this->db->fetchAll(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
            [$id]
        );
        
        // Get user's positions
        $positions = $this->db->fetchAll(
            "SELECT p.*, up.assigned_at FROM positions p 
             JOIN user_positions up ON p.id = up.position_id 
             WHERE up.user_id = ? AND up.status = 'active'",
            [$id]
        );
        
        echo $this->render('users.show', [
            'title' => 'User Details',
            'user' => $user,
            'activities' => $activities,
            'positions' => $positions
        ]);
    }
    
    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requirePermission('user.edit');
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->redirectWithMessage('/users', 'User not found.', 'error');
        }
        
        echo $this->render('users.edit', [
            'title' => 'Edit User',
            'user' => $user,
            'positions' => $this->getAvailablePositions(),
            'gamtas' => $this->getAvailableGamtas()
        ]);
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
            // Soft delete - update status instead of actually deleting
            $success = $this->userModel->update($id, [
                'status' => 'deleted',
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth_user()['id']
            ]);
            
            if ($success) {
                log_activity('user.deleted', "Deleted user: {$user['email']}", ['user_id' => $id]);
                $this->redirectWithMessage('/users', 'User deleted successfully!', 'success');
            } else {
                $this->redirectWithMessage('/users', 'Failed to delete user.', 'error');
            }
            
        } catch (\Exception $e) {
            log_error('User deletion error: ' . $e->getMessage());
            $this->redirectWithMessage('/users', 'An error occurred while deleting the user.', 'error');
        }
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