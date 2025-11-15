<?php

namespace App\Repositories;

use PDO;

/**
 * UserRepository - User data access layer
 * 
 * Handles all database operations related to users including
 * authentication, hierarchy management, and user relationships.
 * 
 * @package App\Repositories
 * @version 1.0.0
 */
class UserRepository extends BaseRepository
{
    protected string $table = 'users';
    protected array $fillable = [
        'uuid', 'email', 'password_hash', 'first_name', 'last_name', 'middle_name',
        'phone', 'date_of_birth', 'gender', 'profile_image', 'address', 'city', 'country',
        'gurmu_id', 'position_id', 'level_scope', 'language_preference', 'timezone',
        'status', 'email_verified', 'email_verified_at', 'phone_verified', 'phone_verified_at',
        'two_factor_enabled', 'two_factor_secret', 'last_login', 'last_activity',
        'created_by', 'approved_by', 'approved_at'
    ];
    protected array $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'last_activity' => 'datetime',
        'approved_at' => 'datetime'
    ];
    protected bool $cacheEnabled = true;
    protected int $cacheLifetime = 1800; // 30 minutes

    /**
     * Find user by email
     * 
     * @param string $email Email address
     * @return array|null User record or null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findWhere(['email' => $email]);
    }

    /**
     * Find user by UUID
     * 
     * @param string $uuid User UUID
     * @return array|null User record or null
     */
    public function findByUuid(string $uuid): ?array
    {
        return $this->findWhere(['uuid' => $uuid]);
    }

    /**
     * Get user with hierarchy information
     * 
     * @param int $userId User ID
     * @return array|null User with hierarchy data
     */
    public function findWithHierarchy(int $userId): ?array
    {
        $sql = "
            SELECT u.*, 
                   gu.name as gurmu_name, gu.code as gurmu_code,
                   ga.name as gamta_name, ga.code as gamta_code,
                   go.name as godina_name, go.code as godina_code,
                   p.name_en as position_name_en, p.name_om as position_name_om,
                   p.key_name as position_key, p.level_scope as position_level_scope
            FROM {$this->table} u
            LEFT JOIN gurmus gu ON u.gurmu_id = gu.id
            LEFT JOIN gamtas ga ON gu.gamta_id = ga.id
            LEFT JOIN godinas go ON ga.godina_id = go.id
            LEFT JOIN positions p ON u.position_id = p.id
            WHERE u.id = ? AND u.deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user = $this->castAttributes($user);
            
            // Structure hierarchy data
            $user['hierarchy'] = [
                'gurmu' => [
                    'id' => $user['gurmu_id'],
                    'name' => $user['gurmu_name'],
                    'code' => $user['gurmu_code']
                ],
                'gamta' => [
                    'name' => $user['gamta_name'],
                    'code' => $user['gamta_code']
                ],
                'godina' => [
                    'name' => $user['godina_name'],
                    'code' => $user['godina_code']
                ]
            ];
            
            $user['position'] = [
                'name_en' => $user['position_name_en'],
                'name_om' => $user['position_name_om'],
                'key' => $user['position_key'],
                'level_scope' => $user['position_level_scope']
            ];
            
            // Remove redundant fields
            unset($user['gurmu_name'], $user['gurmu_code'], $user['gamta_name'], 
                  $user['gamta_code'], $user['godina_name'], $user['godina_code'],
                  $user['position_name_en'], $user['position_name_om'], 
                  $user['position_key'], $user['position_level_scope']);
        }
        
        return $user ?: null;
    }

    /**
     * Get users by hierarchy level
     * 
     * @param string $level Hierarchy level (gurmu, gamta, godina, global)
     * @param int $scopeId Scope ID
     * @param array $filters Additional filters
     * @return array Users list
     */
    public function getByHierarchyLevel(string $level, int $scopeId, array $filters = []): array
    {
        $this->resetQuery();
        
        switch ($level) {
            case 'gurmu':
                $this->where('gurmu_id', $scopeId);
                break;
            case 'gamta':
                $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                     ->where('gu.gamta_id', $scopeId);
                break;
            case 'godina':
                $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                     ->join('gamtas ga', 'gu.gamta_id', '=', 'ga.id')
                     ->where('ga.godina_id', $scopeId);
                break;
            case 'global':
                // No additional filtering for global level
                break;
        }
        
        // Apply additional filters
        if (isset($filters['status'])) {
            $this->where('status', $filters['status']);
        }
        
        if (isset($filters['position_id'])) {
            $this->where('position_id', $filters['position_id']);
        }
        
        if (isset($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $this->where(function($query) use ($search) {
                $query->whereLike('first_name', $search)
                      ->orWhereLike('last_name', $search)
                      ->orWhereLike('email', $search);
            });
        }
        
        return $this->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get();
    }

    /**
     * Get pending user approvals by hierarchy level
     * 
     * @param string $level Hierarchy level
     * @param int $scopeId Scope ID
     * @return array Pending users
     */
    public function getPendingApprovals(string $level, int $scopeId): array
    {
        return $this->getByHierarchyLevel($level, $scopeId, ['status' => 'pending']);
    }

    /**
     * Update user last login
     * 
     * @param int $userId User ID
     * @param string|null $ipAddress IP address
     * @return bool Success status
     */
    public function updateLastLogin(int $userId, ?string $ipAddress = null): bool
    {
        $data = [
            'last_login' => date('Y-m-d H:i:s'),
            'last_activity' => date('Y-m-d H:i:s')
        ];
        
        if ($ipAddress) {
            $data['last_login_ip'] = $ipAddress;
        }
        
        return $this->update($userId, $data);
    }

    /**
     * Update user activity timestamp
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function updateActivity(int $userId): bool
    {
        return $this->update($userId, ['last_activity' => date('Y-m-d H:i:s')]);
    }

    /**
     * Verify user email
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function verifyEmail(int $userId): bool
    {
        return $this->update($userId, [
            'email_verified' => true,
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verify user phone
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function verifyPhone(int $userId): bool
    {
        return $this->update($userId, [
            'phone_verified' => true,
            'phone_verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Approve user registration
     * 
     * @param int $userId User ID
     * @param int $approvedBy Approver user ID
     * @param int|null $positionId Position ID to assign
     * @return bool Success status
     */
    public function approveUser(int $userId, int $approvedBy, ?int $positionId = null): bool
    {
        $data = [
            'status' => 'active',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        if ($positionId) {
            $data['position_id'] = $positionId;
        }
        
        return $this->update($userId, $data);
    }

    /**
     * Suspend user
     * 
     * @param int $userId User ID
     * @param string $reason Suspension reason
     * @param int $suspendedBy User who suspended
     * @return bool Success status
     */
    public function suspendUser(int $userId, string $reason, int $suspendedBy): bool
    {
        $data = [
            'status' => 'suspended',
            'suspension_reason' => $reason,
            'suspended_by' => $suspendedBy,
            'suspended_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($userId, $data);
    }

    /**
     * Reactivate suspended user
     * 
     * @param int $userId User ID
     * @param int $reactivatedBy User who reactivated
     * @return bool Success status
     */
    public function reactivateUser(int $userId, int $reactivatedBy): bool
    {
        $data = [
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_by' => null,
            'suspended_at' => null,
            'reactivated_by' => $reactivatedBy,
            'reactivated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($userId, $data);
    }

    /**
     * Assign position to user
     * 
     * @param int $userId User ID
     * @param int $positionId Position ID
     * @param string $levelScope Level scope
     * @param int $assignedBy User who assigned position
     * @return bool Success status
     */
    public function assignPosition(int $userId, int $positionId, string $levelScope, int $assignedBy): bool
    {
        return $this->update($userId, [
            'position_id' => $positionId,
            'level_scope' => $levelScope,
            'position_assigned_by' => $assignedBy,
            'position_assigned_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove position from user
     * 
     * @param int $userId User ID
     * @param int $removedBy User who removed position
     * @return bool Success status
     */
    public function removePosition(int $userId, int $removedBy): bool
    {
        return $this->update($userId, [
            'position_id' => null,
            'level_scope' => 'gurmu', // Default to gurmu level
            'position_removed_by' => $removedBy,
            'position_removed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get user statistics
     * 
     * @param string $level Hierarchy level
     * @param int $scopeId Scope ID
     * @return array User statistics
     */
    public function getStatistics(string $level = 'global', ?int $scopeId = null): array
    {
        $this->resetQuery();
        
        // Apply hierarchy filter
        if ($level !== 'global' && $scopeId) {
            switch ($level) {
                case 'gurmu':
                    $this->where('gurmu_id', $scopeId);
                    break;
                case 'gamta':
                    $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                         ->where('gu.gamta_id', $scopeId);
                    break;
                case 'godina':
                    $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                         ->join('gamtas ga', 'gu.gamta_id', '=', 'ga.id')
                         ->where('ga.godina_id', $scopeId);
                    break;
            }
        }
        
        // Total users
        $total = $this->count();
        
        // Users by status
        $statusStats = [];
        foreach (['pending', 'active', 'suspended', 'inactive'] as $status) {
            $this->resetQuery();
            if ($level !== 'global' && $scopeId) {
                $this->applyHierarchyFilter($level, $scopeId);
            }
            $statusStats[$status] = $this->where('status', $status)->count();
        }
        
        // Recent registrations (last 30 days)
        $this->resetQuery();
        if ($level !== 'global' && $scopeId) {
            $this->applyHierarchyFilter($level, $scopeId);
        }
        $recentRegistrations = $this->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))->count();
        
        // Users by position
        $sql = "
            SELECT p.name_en, COUNT(u.id) as count
            FROM positions p
            LEFT JOIN users u ON p.id = u.position_id
            WHERE u.deleted_at IS NULL
        ";
        
        if ($level !== 'global' && $scopeId) {
            switch ($level) {
                case 'gurmu':
                    $sql .= " AND u.gurmu_id = {$scopeId}";
                    break;
                case 'gamta':
                    $sql .= " AND u.gurmu_id IN (SELECT id FROM gurmus WHERE gamta_id = {$scopeId})";
                    break;
                case 'godina':
                    $sql .= " AND u.gurmu_id IN (SELECT gu.id FROM gurmus gu JOIN gamtas ga ON gu.gamta_id = ga.id WHERE ga.godina_id = {$scopeId})";
                    break;
            }
        }
        
        $sql .= " GROUP BY p.id, p.name_en";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $positionStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'total' => $total,
            'by_status' => $statusStats,
            'recent_registrations' => $recentRegistrations,
            'by_position' => $positionStats,
            'active_percentage' => $total > 0 ? round(($statusStats['active'] / $total) * 100, 2) : 0
        ];
    }

    /**
     * Apply hierarchy filter to current query
     * 
     * @param string $level Hierarchy level
     * @param int $scopeId Scope ID
     * @return void
     */
    private function applyHierarchyFilter(string $level, int $scopeId): void
    {
        switch ($level) {
            case 'gurmu':
                $this->where('gurmu_id', $scopeId);
                break;
            case 'gamta':
                $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                     ->where('gu.gamta_id', $scopeId);
                break;
            case 'godina':
                $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                     ->join('gamtas ga', 'gu.gamta_id', '=', 'ga.id')
                     ->where('ga.godina_id', $scopeId);
                break;
        }
    }

    /**
     * Search users with advanced filters
     * 
     * @param array $filters Search filters
     * @return array Search results
     */
    public function search(array $filters): array
    {
        $this->resetQuery();
        
        // Text search
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $this->where(function($query) use ($search) {
                $query->whereLike('first_name', $search)
                      ->orWhereLike('last_name', $search)
                      ->orWhereLike('email', $search)
                      ->orWhereLike('phone', $search);
            });
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $this->whereIn('status', $filters['status']);
            } else {
                $this->where('status', $filters['status']);
            }
        }
        
        // Position filter
        if (!empty($filters['position_id'])) {
            $this->where('position_id', $filters['position_id']);
        }
        
        // Level scope filter
        if (!empty($filters['level_scope'])) {
            $this->where('level_scope', $filters['level_scope']);
        }
        
        // Hierarchy filters
        if (!empty($filters['gurmu_id'])) {
            $this->where('gurmu_id', $filters['gurmu_id']);
        }
        
        if (!empty($filters['gamta_id'])) {
            $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                 ->where('gu.gamta_id', $filters['gamta_id']);
        }
        
        if (!empty($filters['godina_id'])) {
            $this->join('gurmus gu', 'users.gurmu_id', '=', 'gu.id')
                 ->join('gamtas ga', 'gu.gamta_id', '=', 'ga.id')
                 ->where('ga.godina_id', $filters['godina_id']);
        }
        
        // Date filters
        if (!empty($filters['created_from'])) {
            $this->where('created_at', '>=', $filters['created_from']);
        }
        
        if (!empty($filters['created_to'])) {
            $this->where('created_at', '<=', $filters['created_to'] . ' 23:59:59');
        }
        
        // Age filter
        if (!empty($filters['age_min']) || !empty($filters['age_max'])) {
            if (!empty($filters['age_min'])) {
                $maxBirthDate = date('Y-m-d', strtotime("-{$filters['age_min']} years"));
                $this->where('date_of_birth', '<=', $maxBirthDate);
            }
            
            if (!empty($filters['age_max'])) {
                $minBirthDate = date('Y-m-d', strtotime("-{$filters['age_max']} years"));
                $this->where('date_of_birth', '>=', $minBirthDate);
            }
        }
        
        // Email verification filter
        if (isset($filters['email_verified'])) {
            $this->where('email_verified', $filters['email_verified'] ? 1 : 0);
        }
        
        // Two-factor authentication filter
        if (isset($filters['two_factor_enabled'])) {
            $this->where('two_factor_enabled', $filters['two_factor_enabled'] ? 1 : 0);
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $this->orderBy($sortBy, $sortOrder);
        
        // Pagination
        if (isset($filters['page']) && isset($filters['per_page'])) {
            return $this->paginate($filters['per_page'], $filters['page']);
        }
        
        return $this->get();
    }

    /**
     * Get users who need to renew their information
     * 
     * @param int $daysThreshold Days threshold for stale information
     * @return array Users with stale information
     */
    public function getUsersNeedingRenewal(int $daysThreshold = 365): array
    {
        $thresholdDate = date('Y-m-d', strtotime("-{$daysThreshold} days"));
        
        return $this->where('updated_at', '<', $thresholdDate)
                    ->where('status', 'active')
                    ->orderBy('updated_at')
                    ->get();
    }

    /**
     * Get user login history
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to retrieve
     * @return array Login history
     */
    public function getLoginHistory(int $userId, int $limit = 10): array
    {
        $sql = "
            SELECT login_time, ip_address, user_agent, success
            FROM user_login_history 
            WHERE user_id = ? 
            ORDER BY login_time DESC 
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record login attempt
     * 
     * @param int $userId User ID
     * @param string $ipAddress IP address
     * @param string $userAgent User agent
     * @param bool $success Success status
     * @return bool Success status
     */
    public function recordLoginAttempt(int $userId, string $ipAddress, string $userAgent, bool $success): bool
    {
        $sql = "
            INSERT INTO user_login_history (user_id, login_time, ip_address, user_agent, success)
            VALUES (?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            date('Y-m-d H:i:s'),
            $ipAddress,
            $userAgent,
            $success ? 1 : 0
        ]);
    }
}