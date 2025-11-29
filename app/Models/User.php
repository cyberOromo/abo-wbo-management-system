<?php
namespace App\Models;

use App\Core\Model;

/**
 * User Model
 * ABO-WBO Management System
 */
class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'email', 'phone', 'password',
        'date_of_birth', 'gender', 'profile_photo', 'gurmu_id', 'user_type',
        'level_scope', 'language_preference', 'timezone', 'notification_preferences',
        'status', 'approval_status', 'approved_by', 'approved_at', 'rejection_reason',
        'verification_token', 'reset_token', 'reset_token_expires_at', 'remember_token',
        'two_factor_secret', 'two_factor_enabled', 'last_login_at', 'last_activity_at',
        'login_count', 'failed_login_attempts', 'locked_until', 'registration_ip',
        'metadata', 'notes', 'created_by', 'updated_by'
    ];
    
    protected $hidden = [
        'password', 'verification_token', 'reset_token', 'remember_token',
        'two_factor_secret', 'registration_ip'
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'reset_token_expires_at' => 'datetime',
        'locked_until' => 'datetime',
        'notification_preferences' => 'json',
        'metadata' => 'json',
        'two_factor_enabled' => 'boolean'
    ];
    
    /**
     * Get user with related data including hierarchy
     */
    public function findWithRelations($id)
    {
        $query = "
            SELECT u.*,
                   p.name_en as position_name_en,
                   p.name_om as position_name_om,
                   p.level_scope as position_level,
                   gur.name as gurmu_name,
                   gur.code as gurmu_code,
                   gam.name as gamta_name,
                   gam.code as gamta_code,
                   god.name as godina_name,
                   god.code as godina_code,
                   creator.first_name as created_by_name,
                   approver.first_name as approved_by_name
            FROM users u
            LEFT JOIN positions p ON u.position_id = p.id
            LEFT JOIN gurmus gur ON u.gurmu_id = gur.id
            LEFT JOIN gamtas gam ON gur.gamta_id = gam.id
            LEFT JOIN godinas god ON gam.godina_id = god.id
            LEFT JOIN users creator ON u.created_by = creator.id
            LEFT JOIN users approver ON u.approved_by = approver.id
            WHERE u.id = ?
        ";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Get users with pagination and filters
     */
    public function getWithFilters($filters = [], $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        $params = [];
        
        // Build WHERE conditions
        if (!empty($filters['search'])) {
            $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "u.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['role'])) {
            $conditions[] = "u.role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['gamta_id'])) {
            $conditions[] = "u.gamta_id = ?";
            $params[] = $filters['gamta_id'];
        }
        
        if (!empty($filters['position_id'])) {
            $conditions[] = "u.position_id = ?";
            $params[] = $filters['position_id'];
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM users u {$whereClause}";
        $total = $this->db->fetch($countQuery, $params)['total'];
        
        // Get users with relations
        $query = "
            SELECT u.*,
                   p.title as position_title,
                   g.name as gamta_name,
                   god.name as godina_name
            FROM users u
            LEFT JOIN positions p ON u.position_id = p.id
            LEFT JOIN gamtas g ON u.gamta_id = g.id
            LEFT JOIN godinas god ON g.godina_id = god.id
            {$whereClause}
            ORDER BY u.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";
        
        $users = $this->db->fetchAll($query, $params);
        
        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE email = ?", 
            [$email]
        );
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get user statistics
     */
    public function getStats()
    {
        $stats = [];
        
        // Total users
        $stats['total'] = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}")['count'];
        
        // Active users
        $stats['active'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'"
        )['count'];
        
        // Pending users
        $stats['pending'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'pending'"
        )['count'];
        
        // Users by role
        $roleStats = $this->db->fetchAll(
            "SELECT role, COUNT(*) as count FROM {$this->table} WHERE status != 'deleted' GROUP BY role"
        );
        
        foreach ($roleStats as $role) {
            $stats['roles'][$role['role']] = $role['count'];
        }
        
        // Recent registrations (last 30 days)
        $stats['recent'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )['count'];
        
        return $stats;
    }
    
    /**
     * Get user's positions
     */
    public function getPositions($userId)
    {
        return $this->db->fetchAll("
            SELECT p.*, up.assigned_at, up.status as assignment_status
            FROM positions p
            JOIN user_positions up ON p.id = up.position_id
            WHERE up.user_id = ?
            ORDER BY up.assigned_at DESC
        ", [$userId]);
    }
    
    /**
     * Assign position to user
     */
    public function assignPosition($userId, $positionId, $assignedBy = null)
    {
        // Check if already assigned
        $existing = $this->db->fetch(
            "SELECT * FROM user_positions WHERE user_id = ? AND position_id = ? AND status = 'active'",
            [$userId, $positionId]
        );
        
        if ($existing) {
            return false; // Already assigned
        }
        
        return $this->db->insert('user_positions', [
            'user_id' => $userId,
            'position_id' => $positionId,
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ]);
    }
    
    /**
     * Remove position from user
     */
    public function removePosition($userId, $positionId, $removedBy = null)
    {
        return $this->db->update('user_positions', [
            'status' => 'inactive',
            'removed_by' => $removedBy,
            'removed_at' => date('Y-m-d H:i:s')
        ], [
            'user_id' => $userId,
            'position_id' => $positionId,
            'status' => 'active'
        ]);
    }
    
    /**
     * Get user's activity log
     */
    public function getActivityLog($userId, $limit = 50)
    {
        return $this->db->fetchAll("
            SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$userId, $limit]);
    }
    
    /**
     * Update last login time
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Set email verification token
     */
    public function setEmailVerificationToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        
        $success = $this->update($userId, [
            'email_verification_token' => $token
        ]);
        
        return $success ? $token : false;
    }
    
    /**
     * Verify email with token
     */
    public function verifyEmail($token)
    {
        $user = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE email_verification_token = ?",
            [$token]
        );
        
        if (!$user) {
            return false;
        }
        
        return $this->update($user['id'], [
            'email_verified_at' => date('Y-m-d H:i:s'),
            'email_verification_token' => null,
            'status' => 'active'
        ]);
    }
    
    /**
     * Set password reset token
     */
    public function setPasswordResetToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $success = $this->update($userId, [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires
        ]);
        
        return $success ? $token : false;
    }
    
    /**
     * Find user by password reset token
     */
    public function findByPasswordResetToken($token)
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$token]
        );
    }
    
    /**
     * Clear password reset token
     */
    public function clearPasswordResetToken($userId)
    {
        return $this->update($userId, [
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
    }
    
    /**
     * Update user preferences
     */
    public function updatePreferences($userId, $preferences)
    {
        return $this->update($userId, [
            'preferences' => json_encode($preferences)
        ]);
    }
    
    /**
     * Get user preferences
     */
    public function getPreferences($userId)
    {
        $user = $this->find($userId);
        return $user ? json_decode($user['preferences'] ?? '{}', true) : [];
    }
    
    /**
     * Soft delete user
     */
    public function softDelete($id, $deletedBy = null)
    {
        return $this->update($id, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $deletedBy
        ]);
    }
    
    /**
     * Restore soft deleted user
     */
    public function restore($id)
    {
        return $this->update($id, [
            'status' => 'active',
            'deleted_at' => null,
            'deleted_by' => null
        ]);
    }
    
    /**
     * Get users for export
     */
    public function getForExport($format = 'csv')
    {
        $query = "
            SELECT u.first_name, u.last_name, u.email, u.phone, u.gender,
                   u.city, u.state, u.country, u.role, u.status,
                   p.title as position, g.name as gamta, u.created_at
            FROM users u
            LEFT JOIN positions p ON u.position_id = p.id
            LEFT JOIN gamtas g ON u.gamta_id = g.id
            WHERE u.status != 'deleted'
            ORDER BY u.created_at DESC
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Search users for autocomplete
     */
    public function search($term, $limit = 10)
    {
        return $this->db->fetchAll("
            SELECT id, first_name, last_name, email, user_type
            FROM {$this->table}
            WHERE status = 'active' 
            AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
            ORDER BY first_name, last_name
            LIMIT ?
        ", ["%{$term}%", "%{$term}%", "%{$term}%", $limit]);
    }
    
    /**
     * Check if user is a regular member
     */
    public function isMember($userId = null)
    {
        $userId = $userId ?? $this->id ?? null;
        if (!$userId) return false;
        
        $user = $this->find($userId);
        return $user && ($user['user_type'] ?? 'member') === 'member';
    }
    
    /**
     * Check if user is an executive
     */
    public function isExecutive($userId = null)
    {
        $userId = $userId ?? $this->id ?? null;
        if (!$userId) return false;
        
        $user = $this->find($userId);
        return $user && ($user['user_type'] ?? 'member') === 'executive';
    }
    
    /**
     * Check if user is system admin
     */
    public function isSystemAdmin($userId = null)
    {
        $userId = $userId ?? $this->id ?? null;
        if (!$userId) return false;
        
        $user = $this->find($userId);
        return $user && ($user['user_type'] ?? 'member') === 'system_admin';
    }
    
    /**
     * Get user's active position assignments
     */
    public function getActivePositions($userId)
    {
        return $this->db->fetchAll("
            SELECT ua.*, p.key_name, p.name_en, p.name_om, p.level_scope as position_level
            FROM user_assignments ua
            JOIN positions p ON ua.position_id = p.id
            WHERE ua.user_id = ? AND ua.status = 'active'
            ORDER BY FIELD(ua.level_scope, 'global', 'godina', 'gamta', 'gurmu')
        ", [$userId]);
    }
    
    /**
     * Promote member to executive (when assigned first position)
     */
    public function promoteToExecutive($userId)
    {
        return $this->db->update(
            $this->table,
            ['user_type' => 'executive'],
            ['id' => $userId]
        );
    }
    
    /**
     * Demote executive to member (when all positions removed)
     */
    public function demoteToMember($userId)
    {
        // Check if user has any active positions
        $hasPositions = $this->db->fetch(
            "SELECT COUNT(*) as count FROM user_assignments WHERE user_id = ? AND status = 'active'",
            [$userId]
        );
        
        if (($hasPositions['count'] ?? 0) == 0) {
            return $this->db->update(
                $this->table,
                ['user_type' => 'member'],
                ['id' => $userId]
            );
        }
        
        return false;
    }
}
