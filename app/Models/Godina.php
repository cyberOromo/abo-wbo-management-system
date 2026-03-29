<?php
namespace App\Models;

use App\Core\Model;

/**
 * Godina Model
 * ABO-WBO Management System - Global/Regional organizational units
 */
class Godina extends Model
{
    protected $table = 'godinas';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name', 'code', 'description', 'location', 'timezone',
        'contact_person', 'contact_email', 'contact_phone',
        'address', 'website', 'social_media', 'logo',
        'status', 'metadata', 'created_by', 'deleted_by'
    ];
    
    protected $casts = [
        'social_media' => 'json',
        'metadata' => 'json'
    ];
    
    /**
     * Get Godina with related data
     */
    public function findWithRelations($id)
    {
        $query = "
            SELECT g.*,
                   creator.first_name as created_by_name,
                   COUNT(DISTINCT ga.id) as gamta_count,
                   COUNT(DISTINCT gu.id) as gurmu_count
            FROM godinas g
            LEFT JOIN users creator ON g.created_by = creator.id
            LEFT JOIN gamtas ga ON g.id = ga.godina_id AND ga.status != 'deleted'
            LEFT JOIN gurmus gu ON ga.id = gu.gamta_id AND gu.status != 'deleted'
            WHERE g.id = ?
            GROUP BY g.id
        ";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Get all active Godinas
     */
    public function getActive()
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name"
        );
    }
    
    /**
     * Get Godinas with statistics
     */
    public function getWithStats($filters = [])
    {
        $conditions = [];
        $params = [];
        
        // Build WHERE conditions
        if (!empty($filters['search'])) {
            $conditions[] = "(g.name LIKE ? OR g.code LIKE ? OR g.location LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "g.status = ?";
            $params[] = $filters['status'];
        }
        
        // Exclude deleted by default
        $conditions[] = "g.status != 'deleted'";
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        
        $query = "
            SELECT g.*,
                   COUNT(DISTINCT ga.id) as gamta_count,
                   COUNT(DISTINCT u.id) as user_count,
                   creator.first_name as created_by_name
            FROM godinas g
            LEFT JOIN users creator ON g.created_by = creator.id
            LEFT JOIN gamtas ga ON g.id = ga.godina_id AND ga.status != 'deleted'
            LEFT JOIN users u ON ga.id = u.gamta_id AND u.status = 'active'
            {$whereClause}
            GROUP BY g.id
            ORDER BY g.name
        ";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get Godina statistics
     */
    public function getStats()
    {
        $stats = [];
        
        // Total Godinas
        $stats['total'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status != 'deleted'"
        )['count'];
        
        // Active Godinas
        $stats['active'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'"
        )['count'];
        
        // Godinas by status
        $statusStats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM {$this->table} 
             WHERE status != 'deleted' GROUP BY status"
        );
        
        foreach ($statusStats as $status) {
            $stats['by_status'][$status['status']] = $status['count'];
        }
        
        // Recent Godinas (last 30 days)
        $stats['recent'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )['count'];
        
        return $stats;
    }
    
    /**
     * Get Gamtas for this Godina
     */
    public function getGamtas($godinaId, $includeStats = false)
    {
        if ($includeStats) {
            return $this->db->fetchAll("
                SELECT ga.*,
                       COUNT(u.id) as user_count,
                       COUNT(CASE WHEN u.status = 'active' THEN u.id END) as active_users
                FROM gamtas ga
                LEFT JOIN users u ON ga.id = u.gamta_id
                WHERE ga.godina_id = ? AND ga.status != 'deleted'
                GROUP BY ga.id
                ORDER BY ga.name
            ", [$godinaId]);
        }
        
        return $this->db->fetchAll(
            "SELECT * FROM gamtas WHERE godina_id = ? AND status != 'deleted' ORDER BY name",
            [$godinaId]
        );
    }
    
    /**
     * Check if Godina code exists
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
     * Get hierarchy tree for this Godina
     */
    public function getHierarchyTree($godinaId)
    {
        $godina = $this->find($godinaId);
        if (!$godina) {
            return null;
        }
        
        $gamtas = $this->db->fetchAll("
            SELECT ga.*,
                   COUNT(u.id) as user_count
            FROM gamtas ga
            LEFT JOIN users u ON ga.id = u.gamta_id AND u.status = 'active'
            WHERE ga.godina_id = ? AND ga.status != 'deleted'
            GROUP BY ga.id
            ORDER BY ga.name
        ", [$godinaId]);
        
        return [
            'godina' => $godina,
            'gamtas' => $gamtas
        ];
    }
    
    /**
     * Activate/Deactivate Godina
     */
    public function toggleStatus($id, $status)
    {
        if (!in_array($status, ['active', 'inactive'])) {
            return false;
        }
        
        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Soft delete Godina
     */
    public function softDelete($id, $deletedBy = null)
    {
        // Check if Godina has Gamtas
        $gamtaCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gamtas WHERE godina_id = ? AND status != 'deleted'",
            [$id]
        )['count'];
        
        if ($gamtaCount > 0) {
            throw new \Exception('Cannot delete Godina that contains Gamtas. Please move or delete the Gamtas first.');
        }
        
        return $this->update($id, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $deletedBy
        ]);
    }
    
    /**
     * Restore soft deleted Godina
     */
    public function restore($id)
    {
        return $this->update($id, [
            'status' => 'active',
            'deleted_at' => null,
            'deleted_by' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Search Godinas
     */
    public function search($term, $limit = 10)
    {
        return $this->db->fetchAll("
            SELECT id, name, code, location, status
            FROM {$this->table}
            WHERE status != 'deleted' 
            AND (name LIKE ? OR code LIKE ? OR location LIKE ?)
            ORDER BY 
                CASE WHEN name LIKE ? THEN 1 ELSE 2 END,
                name
            LIMIT ?
        ", ["%{$term}%", "%{$term}%", "%{$term}%", "{$term}%", $limit]);
    }
    
    /**
     * Get Godinas for export
     */
    public function getForExport()
    {
        return $this->db->fetchAll("
            SELECT g.name, g.code, g.location, g.contact_person, 
                   g.contact_email, g.contact_phone, g.status,
                   COUNT(DISTINCT ga.id) as total_gamtas,
                   COUNT(DISTINCT u.id) as total_users,
                   g.created_at
            FROM godinas g
            LEFT JOIN gamtas ga ON g.id = ga.godina_id AND ga.status != 'deleted'
            LEFT JOIN users u ON ga.id = u.gamta_id AND u.status = 'active'
            WHERE g.status != 'deleted'
            GROUP BY g.id
            ORDER BY g.name
        ");
    }
    
    /**
     * Get activity log for Godina
     */
    public function getActivityLog($godinaId, $limit = 20)
    {
        return $this->db->fetchAll("
            SELECT al.*, u.first_name, u.last_name
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.action LIKE 'godina.%' 
            AND JSON_EXTRACT(al.metadata, '$.godina_id') = ?
            ORDER BY al.created_at DESC
            LIMIT ?
        ", [$godinaId, $limit]);
    }
    
    /**
     * Update social media links
     */
    public function updateSocialMedia($id, $socialMedia)
    {
        return $this->update($id, [
            'social_media' => json_encode($socialMedia)
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
     * Get dashboard stats for Godina
     */
    public function getDashboardStats($godinaId)
    {
        $stats = [];
        
        // Basic counts
        $basicStats = $this->db->fetch("
            SELECT 
                COUNT(DISTINCT ga.id) as gamta_count,
                COUNT(DISTINCT u.id) as user_count,
                COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as active_users,
                COUNT(DISTINCT CASE WHEN p.id IS NOT NULL THEN u.id END) as users_with_positions
            FROM gamtas ga
            LEFT JOIN users u ON ga.id = u.gamta_id
            LEFT JOIN positions p ON u.position_id = p.id
            WHERE ga.godina_id = ? AND ga.status != 'deleted'
        ", [$godinaId]);
        
        $stats = array_merge($stats, $basicStats);
        
        // Recent activity count
        $stats['recent_activity'] = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM activity_logs
            WHERE JSON_EXTRACT(metadata, '$.godina_id') = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ", [$godinaId])['count'];
        
        return $stats;
    }
}