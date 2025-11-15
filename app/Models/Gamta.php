<?php
namespace App\Models;

use App\Core\Model;

/**
 * Gamta Model
 * ABO-WBO Management System - Local/Community organizational units
 */
class Gamta extends Model
{
    protected $table = 'gamtas';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'godina_id', 'name', 'code', 'description', 'location', 'timezone',
        'contact_person', 'contact_email', 'contact_phone',
        'address', 'meeting_day', 'meeting_time', 'meeting_location',
        'status', 'metadata', 'created_by', 'updated_by', 'deleted_by'
    ];
    
    protected $casts = [
        'metadata' => 'json'
    ];
    
    /**
     * Get Gamta with related data
     */
    public function findWithRelations($id)
    {
        $query = "
            SELECT ga.*,
                   god.name as godina_name,
                   god.code as godina_code,
                   creator.first_name as created_by_name,
                   updater.first_name as updated_by_name,
                   COUNT(u.id) as user_count,
                   COUNT(CASE WHEN u.status = 'active' THEN u.id END) as active_users
            FROM gamtas ga
            LEFT JOIN godinas god ON ga.godina_id = god.id
            LEFT JOIN users creator ON ga.created_by = creator.id
            LEFT JOIN users updater ON ga.updated_by = updater.id
            LEFT JOIN users u ON ga.id = u.gamta_id
            WHERE ga.id = ?
            GROUP BY ga.id
        ";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Get all active Gamtas
     */
    public function getActive()
    {
        return $this->db->fetchAll(
            "SELECT ga.*, god.name as godina_name 
             FROM {$this->table} ga 
             LEFT JOIN godinas god ON ga.godina_id = god.id 
             WHERE ga.status = 'active' 
             ORDER BY god.name, ga.name"
        );
    }
    
    /**
     * Get Gamtas with statistics
     */
    public function getWithStats($filters = [])
    {
        $conditions = [];
        $params = [];
        
        // Build WHERE conditions
        if (!empty($filters['search'])) {
            $conditions[] = "(ga.name LIKE ? OR ga.code LIKE ? OR ga.location LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "ga.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['godina_id'])) {
            $conditions[] = "ga.godina_id = ?";
            $params[] = $filters['godina_id'];
        }
        
        // Exclude deleted by default
        $conditions[] = "ga.status != 'deleted'";
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        
        $query = "
            SELECT ga.*,
                   god.name as godina_name,
                   god.code as godina_code,
                   COUNT(u.id) as user_count,
                   COUNT(CASE WHEN u.status = 'active' THEN u.id END) as active_users,
                   creator.first_name as created_by_name
            FROM gamtas ga
            LEFT JOIN godinas god ON ga.godina_id = god.id
            LEFT JOIN users creator ON ga.created_by = creator.id
            LEFT JOIN users u ON ga.id = u.gamta_id
            {$whereClause}
            GROUP BY ga.id
            ORDER BY god.name, ga.name
        ";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get Gamtas by Godina
     */
    public function getByGodina($godinaId, $includeStats = false)
    {
        if ($includeStats) {
            return $this->db->fetchAll("
                SELECT ga.*,
                       COUNT(u.id) as user_count,
                       COUNT(CASE WHEN u.status = 'active' THEN u.id END) as active_users
                FROM {$this->table} ga
                LEFT JOIN users u ON ga.id = u.gamta_id
                WHERE ga.godina_id = ? AND ga.status != 'deleted'
                GROUP BY ga.id
                ORDER BY ga.name
            ", [$godinaId]);
        }
        
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE godina_id = ? AND status != 'deleted' ORDER BY name",
            [$godinaId]
        );
    }
    
    /**
     * Get Gamta statistics
     */
    public function getStats()
    {
        $stats = [];
        
        // Total Gamtas
        $stats['total'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status != 'deleted'"
        )['count'];
        
        // Active Gamtas
        $stats['active'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'"
        )['count'];
        
        // Gamtas by status
        $statusStats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM {$this->table} 
             WHERE status != 'deleted' GROUP BY status"
        );
        
        foreach ($statusStats as $status) {
            $stats['by_status'][$status['status']] = $status['count'];
        }
        
        // Gamtas by Godina
        $godinaStats = $this->db->fetchAll(
            "SELECT god.name, COUNT(ga.id) as count
             FROM {$this->table} ga
             LEFT JOIN godinas god ON ga.godina_id = god.id
             WHERE ga.status != 'deleted'
             GROUP BY ga.godina_id
             ORDER BY count DESC
             LIMIT 10"
        );
        
        $stats['by_godina'] = $godinaStats;
        
        // Recent Gamtas (last 30 days)
        $stats['recent'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )['count'];
        
        return $stats;
    }
    
    /**
     * Check if Gamta code exists
     */
    public function codeExists($code, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE code = ? AND status != 'deleted'";
        $params = [$code];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get users for this Gamta
     */
    public function getUsers($gamtaId, $includeInactive = false)
    {
        $statusCondition = $includeInactive ? "" : "AND u.status = 'active'";
        
        return $this->db->fetchAll("
            SELECT u.*,
                   p.title as position_title,
                   p.level as position_level
            FROM users u
            LEFT JOIN positions p ON u.position_id = p.id
            WHERE u.gamta_id = ? {$statusCondition}
            ORDER BY p.level DESC, u.first_name, u.last_name
        ", [$gamtaId]);
    }
    
    /**
     * Get user count by role for this Gamta
     */
    public function getUserCountByRole($gamtaId)
    {
        return $this->db->fetchAll(
            "SELECT role, COUNT(*) as count 
             FROM users 
             WHERE gamta_id = ? AND status = 'active' 
             GROUP BY role",
            [$gamtaId]
        );
    }
    
    /**
     * Activate/Deactivate Gamta
     */
    public function toggleStatus($id, $status)
    {
        if (!in_array($status, ['active', 'inactive'])) {
            return false;
        }
        
        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => auth_user()['id'] ?? null
        ]);
    }
    
    /**
     * Soft delete Gamta
     */
    public function softDelete($id, $deletedBy = null)
    {
        // Check if Gamta has users
        $userCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE gamta_id = ? AND status != 'deleted'",
            [$id]
        )['count'];
        
        if ($userCount > 0) {
            throw new \Exception('Cannot delete Gamta that has assigned users. Please reassign the users first.');
        }
        
        return $this->update($id, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $deletedBy
        ]);
    }
    
    /**
     * Restore soft deleted Gamta
     */
    public function restore($id)
    {
        return $this->update($id, [
            'status' => 'active',
            'deleted_at' => null,
            'deleted_by' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => auth_user()['id'] ?? null
        ]);
    }
    
    /**
     * Move Gamta to different Godina
     */
    public function moveToGodina($gamtaId, $newGodinaId)
    {
        // Verify the new Godina exists and is active
        $godina = $this->db->fetch(
            "SELECT id FROM godinas WHERE id = ? AND status = 'active'",
            [$newGodinaId]
        );
        
        if (!$godina) {
            throw new \Exception('Target Godina not found or is not active.');
        }
        
        return $this->update($gamtaId, [
            'godina_id' => $newGodinaId,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => auth_user()['id'] ?? null
        ]);
    }
    
    /**
     * Search Gamtas
     */
    public function search($term, $limit = 10)
    {
        return $this->db->fetchAll("
            SELECT ga.id, ga.name, ga.code, ga.location, ga.status,
                   god.name as godina_name
            FROM {$this->table} ga
            LEFT JOIN godinas god ON ga.godina_id = god.id
            WHERE ga.status != 'deleted' 
            AND (ga.name LIKE ? OR ga.code LIKE ? OR ga.location LIKE ?)
            ORDER BY 
                CASE WHEN ga.name LIKE ? THEN 1 ELSE 2 END,
                god.name, ga.name
            LIMIT ?
        ", ["%{$term}%", "%{$term}%", "%{$term}%", "{$term}%", $limit]);
    }
    
    /**
     * Get Gamtas for export
     */
    public function getForExport()
    {
        return $this->db->fetchAll("
            SELECT ga.name, ga.code, ga.location, ga.contact_person, 
                   ga.contact_email, ga.contact_phone, ga.status,
                   god.name as godina_name,
                   COUNT(u.id) as total_users,
                   ga.created_at
            FROM {$this->table} ga
            LEFT JOIN godinas god ON ga.godina_id = god.id
            LEFT JOIN users u ON ga.id = u.gamta_id AND u.status = 'active'
            WHERE ga.status != 'deleted'
            GROUP BY ga.id
            ORDER BY god.name, ga.name
        ");
    }
    
    /**
     * Get activity log for Gamta
     */
    public function getActivityLog($gamtaId, $limit = 20)
    {
        return $this->db->fetchAll("
            SELECT al.*, u.first_name, u.last_name
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.action LIKE 'gamta.%' 
            AND JSON_EXTRACT(al.metadata, '$.gamta_id') = ?
            ORDER BY al.created_at DESC
            LIMIT ?
        ", [$gamtaId, $limit]);
    }
    
    /**
     * Update meeting schedule
     */
    public function updateMeetingSchedule($id, $meetingDay, $meetingTime, $meetingLocation)
    {
        return $this->update($id, [
            'meeting_day' => $meetingDay,
            'meeting_time' => $meetingTime,
            'meeting_location' => $meetingLocation,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => auth_user()['id'] ?? null
        ]);
    }
    
    /**
     * Update metadata
     */
    public function updateMetadata($id, $metadata)
    {
        return $this->update($id, [
            'metadata' => json_encode($metadata)
        ]);
    }
    
    /**
     * Get dashboard stats for Gamta
     */
    public function getDashboardStats($gamtaId)
    {
        $stats = [];
        
        // Basic user stats
        $userStats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                COUNT(CASE WHEN position_id IS NOT NULL THEN 1 END) as users_with_positions
            FROM users
            WHERE gamta_id = ?
        ", [$gamtaId]);
        
        $stats = array_merge($stats, $userStats);
        
        // User role distribution
        $roleStats = $this->db->fetchAll(
            "SELECT role, COUNT(*) as count 
             FROM users 
             WHERE gamta_id = ? AND status = 'active' 
             GROUP BY role",
            [$gamtaId]
        );
        
        $stats['roles'] = [];
        foreach ($roleStats as $role) {
            $stats['roles'][$role['role']] = $role['count'];
        }
        
        // Recent activity count
        $stats['recent_activity'] = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM activity_logs
            WHERE JSON_EXTRACT(metadata, '$.gamta_id') = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ", [$gamtaId])['count'];
        
        return $stats;
    }
    
    /**
     * Get upcoming events for Gamta
     */
    public function getUpcomingEvents($gamtaId, $limit = 5)
    {
        return $this->db->fetchAll("
            SELECT e.*, et.name as event_type_name
            FROM events e
            LEFT JOIN event_types et ON e.event_type_id = et.id
            WHERE e.gamta_id = ? 
            AND e.start_date >= CURDATE()
            AND e.status = 'active'
            ORDER BY e.start_date ASC
            LIMIT ?
        ", [$gamtaId, $limit]);
    }
    
    /**
     * Get recent meetings for Gamta
     */
    public function getRecentMeetings($gamtaId, $limit = 5)
    {
        return $this->db->fetchAll("
            SELECT m.*
            FROM meetings m
            WHERE m.gamta_id = ? 
            AND m.status != 'cancelled'
            ORDER BY m.meeting_date DESC
            LIMIT ?
        ", [$gamtaId, $limit]);
    }
    
    /**
     * Validate Gamta data
     */
    public function validateGamtaData($data, $excludeId = null)
    {
        $errors = [];
        
        // Check if code is unique
        if (!empty($data['code']) && $this->codeExists($data['code'], $excludeId)) {
            $errors['code'] = 'Gamta code already exists.';
        }
        
        // Validate Godina exists
        if (!empty($data['godina_id'])) {
            $godina = $this->db->fetch(
                "SELECT id FROM godinas WHERE id = ? AND status != 'deleted'",
                [$data['godina_id']]
            );
            
            if (!$godina) {
                $errors['godina_id'] = 'Selected Godina does not exist or is inactive.';
            }
        }
        
        return $errors;
    }
}