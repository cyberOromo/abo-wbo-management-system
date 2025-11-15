<?php
namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * Position Model
 * Manages organizational positions and executive roles
 * ABO-WBO Management System - 4-tier hierarchy management
 */
class Position extends Model
{
    protected $table = 'positions';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'key_name', 'name_en', 'name_om', 'description_en', 'description_om',
        'level_scope', 'permissions', 'sort_order', 'term_length',
        'election_cycle', 'status'
    ];
    
    protected $casts = [
        'permissions' => 'json',
        'sort_order' => 'integer',
        'term_length' => 'integer'
    ];
    
    /**
     * Get all active positions
     */
    public function getActive(): array
    {
        return $this->getWithFilters(['status' => 'active']);
    }

    /**
     * Get all positions with optional filters
     */
    public function getWithFilters(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        
        // Level scope filter
        if (!empty($filters['level_scope'])) {
            $conditions[] = "level_scope = ?";
            $params[] = $filters['level_scope'];
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $conditions[] = "status = ?";
            $params[] = $filters['status'];
        } else {
            $conditions[] = "status = 'active'";
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $conditions[] = "(name LIKE ? OR code LIKE ? OR key_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $orderBy = "ORDER BY level ASC, name ASC";
        
        $query = "SELECT * FROM {$this->table} {$whereClause} {$orderBy}";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get positions by level scope
     */
    public function getByLevelScope(string $levelScope): array
    {
        return $this->getWithFilters(['level_scope' => $levelScope]);
    }
    
    /**
     * Get executive positions (excluding general member positions)
     */
    public function getExecutivePositions(): array
    {
        $query = "
            SELECT * FROM {$this->table} 
            WHERE status = 'active' 
            AND key_name NOT IN ('member', 'committee_member', 'volunteer_coordinator')
            ORDER BY level_scope, sort_order ASC
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get core executive positions for a level (7 main positions)
     */
    public function getCoreExecutivePositions(string $levelScope): array
    {
        $corePositions = [
            '_chairperson', '_secretary', '_finance_head', '_diplomacy_head',
            '_organization_head', '_media_head', '_internal_audit'
        ];
        
        $placeholders = str_repeat('?,', count($corePositions) - 1) . '?';
        $likeConditions = array_map(function($pos) use ($levelScope) {
            return "key_name LIKE ?";
        }, $corePositions);
        
        $params = array_map(function($pos) use ($levelScope) {
            return $levelScope . $pos;
        }, $corePositions);
        
        $query = "
            SELECT * FROM {$this->table} 
            WHERE (" . implode(' OR ', $likeConditions) . ")
            AND status = 'active'
            ORDER BY sort_order ASC
        ";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get position with permissions details
     */
    public function findWithPermissions(int $id): ?array
    {
        $query = "
            SELECT p.*,
                   COUNT(ua.user_id) as assigned_users_count
            FROM {$this->table} p
            LEFT JOIN user_assignments ua ON p.id = ua.position_id AND ua.status = 'active'
            WHERE p.id = ?
            GROUP BY p.id
        ";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Create new position
     */
    public function createPosition(array $data): int
    {
        // Validate required fields
        $required = ['key_name', 'name_en', 'name_om', 'level_scope'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        // Check for duplicate key_name
        if ($this->keyNameExists($data['key_name'])) {
            throw new Exception("Position key '{$data['key_name']}' already exists");
        }
        
        // Set defaults
        $data['permissions'] = $data['permissions'] ?? [];
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['term_length'] = $data['term_length'] ?? 24;
        $data['election_cycle'] = $data['election_cycle'] ?? 'elected';
        $data['status'] = $data['status'] ?? 'active';
        
        return $this->create($data);
    }
    
    /**
     * Update position
     */
    public function updatePosition(int $id, array $data): bool
    {
        // Check if key_name is being changed and ensure uniqueness
        if (isset($data['key_name'])) {
            $existing = $this->find($id);
            if ($existing && $existing['key_name'] !== $data['key_name']) {
                if ($this->keyNameExists($data['key_name'])) {
                    throw new Exception("Position key '{$data['key_name']}' already exists");
                }
            }
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Soft delete position (deactivate)
     */
    public function softDelete(int $id): bool
    {
        // Check if position has active assignments
        $assignments = $this->getActiveAssignments($id);
        if (!empty($assignments)) {
            throw new Exception("Cannot delete position with active assignments. Please reassign users first.");
        }
        
        return $this->update($id, ['status' => 'inactive']);
    }
    
    /**
     * Check if key_name exists
     */
    public function keyNameExists(string $keyName, ?int $excludeId = null): bool
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE key_name = ?";
        $params = [$keyName];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get active assignments for position
     */
    public function getActiveAssignments(int $positionId): array
    {
        $query = "
            SELECT ua.*, u.first_name, u.last_name, u.email
            FROM user_assignments ua
            JOIN users u ON ua.user_id = u.id
            WHERE ua.position_id = ? AND ua.status = 'active'
        ";
        
        return $this->db->fetchAll($query, [$positionId]);
    }
    
    /**
     * Get position statistics
     */
    public function getPositionStats(): array
    {
        $query = "
            SELECT 
                level_scope,
                COUNT(*) as total_positions,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_positions,
                SUM(CASE WHEN election_cycle = 'elected' THEN 1 ELSE 0 END) as elected_positions,
                SUM(CASE WHEN election_cycle = 'appointed' THEN 1 ELSE 0 END) as appointed_positions
            FROM {$this->table}
            GROUP BY level_scope
            ORDER BY FIELD(level_scope, 'global', 'godina', 'gamta', 'gurmu', 'all')
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get positions hierarchy tree
     */
    public function getPositionsHierarchy(): array
    {
        $positions = $this->getWithFilters(['status' => 'active']);
        
        $hierarchy = [
            'global' => [],
            'godina' => [],
            'gamta' => [],
            'gurmu' => [],
            'all' => []
        ];
        
        foreach ($positions as $position) {
            $hierarchy[$position['level_scope']][] = $position;
        }
        
        return $hierarchy;
    }
    
    /**
     * Check if user can be assigned to position based on hierarchy rules
     */
    public function canAssignUserToPosition(int $userId, int $positionId, int $organizationalUnitId): bool
    {
        $position = $this->find($positionId);
        if (!$position) {
            return false;
        }
        
        // Get user's current assignments
        $query = "
            SELECT ua.*, p.level_scope, p.key_name
            FROM user_assignments ua
            JOIN positions p ON ua.position_id = p.id
            WHERE ua.user_id = ? AND ua.status = 'active'
        ";
        
        $currentAssignments = $this->db->fetchAll($query, [$userId]);
        
        // Rule: Users can only hold one position (no multi-level positions)
        if (!empty($currentAssignments)) {
            return false;
        }
        
        // Additional validation based on organizational unit and position level
        // This would need to be expanded based on specific business rules
        
        return true;
    }
    
    /**
     * Get positions with current assignments for organizational unit
     */
    public function getPositionsForUnit(string $levelScope, int $unitId): array
    {
        $query = "
            SELECT p.*,
                   ua.id as assignment_id,
                   ua.user_id,
                   ua.assigned_at,
                   ua.term_start,
                   ua.term_end,
                   ua.status as assignment_status,
                   u.first_name,
                   u.last_name,
                   u.email
            FROM {$this->table} p
            LEFT JOIN user_assignments ua ON p.id = ua.position_id 
                AND ua.organizational_unit_id = ? 
                AND ua.status = 'active'
            LEFT JOIN users u ON ua.user_id = u.id
            WHERE p.level_scope = ? AND p.status = 'active'
            ORDER BY p.sort_order ASC
        ";
        
        return $this->db->fetchAll($query, [$unitId, $levelScope]);
    }
    
    /**
     * Get positions requiring elections soon
     */
    public function getPositionsRequiringElections(int $monthsAhead = 3): array
    {
        $query = "
            SELECT p.*, ua.*, u.first_name, u.last_name,
                   DATEDIFF(ua.term_end, NOW()) as days_remaining
            FROM {$this->table} p
            JOIN user_assignments ua ON p.id = ua.position_id
            JOIN users u ON ua.user_id = u.id
            WHERE ua.status = 'active'
            AND ua.term_end IS NOT NULL
            AND ua.term_end <= DATE_ADD(NOW(), INTERVAL ? MONTH)
            AND p.election_cycle = 'elected'
            ORDER BY ua.term_end ASC
        ";
        
        return $this->db->fetchAll($query, [$monthsAhead]);
    }
    
    /**
     * Get position responsibilities (shared + individual)
     */
    public function getPositionResponsibilities(int $positionId, string $levelScope = 'all'): array
    {
        $position = $this->find($positionId);
        if (!$position) {
            return ['individual' => [], 'shared' => [], 'total_count' => 0];
        }
        
        // Get responsibilities using the Responsibility model
        $responsibilityModel = new \App\Models\Responsibility();
        return $responsibilityModel->getAllPositionResponsibilities($position['key_name'], $levelScope);
    }
    
    /**
     * Get positions with their responsibility counts
     */
    public function getPositionsWithResponsibilityCounts(): array
    {
        $query = "
            SELECT 
                p.*,
                COALESCE(shared_count.count, 0) as shared_responsibilities_count,
                COALESCE(individual_count.count, 0) as individual_responsibilities_count,
                (COALESCE(shared_count.count, 0) + COALESCE(individual_count.count, 0)) as total_responsibilities_count
            FROM {$this->table} p
            LEFT JOIN (
                SELECT 
                    'all' as position_scope,
                    COUNT(*) as count
                FROM responsibilities 
                WHERE is_shared = 1 AND status = 'active'
            ) shared_count ON 1=1
            LEFT JOIN (
                SELECT 
                    position_scope,
                    COUNT(*) as count
                FROM responsibilities 
                WHERE is_shared = 0 AND status = 'active'
                GROUP BY position_scope
            ) individual_count ON individual_count.position_scope = p.key_name
            WHERE p.status = 'active'
            ORDER BY p.sort_order, p.name_en
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Check if position has specific responsibility
     */
    public function hasResponsibility(int $positionId, string $responsibilityKey): bool
    {
        $position = $this->find($positionId);
        if (!$position) {
            return false;
        }
        
        $query = "
            SELECT COUNT(*) as count
            FROM responsibilities r
            WHERE r.key_name = ? 
            AND r.status = 'active'
            AND (
                (r.is_shared = 1) OR 
                (r.is_shared = 0 AND r.position_scope = ?)
            )
        ";
        
        $result = $this->db->fetch($query, [$responsibilityKey, $position['key_name']]);
        return $result['count'] > 0;
    }
    
    /**
     * Get position assignment statistics with responsibilities
     */
    public function getPositionStatsWithResponsibilities(): array
    {
        $query = "
            SELECT 
                p.id,
                p.key_name,
                p.name_en,
                p.name_om,
                p.level_scope,
                COUNT(DISTINCT ua.id) as total_assignments,
                COUNT(DISTINCT CASE WHEN ua.status = 'approved' THEN ua.id END) as active_assignments,
                COUNT(DISTINCT ra.id) as responsibility_assignments,
                COUNT(DISTINCT CASE WHEN ra.status = 'completed' THEN ra.id END) as completed_responsibilities,
                COALESCE(resp_counts.total_responsibilities, 0) as available_responsibilities
            FROM {$this->table} p
            LEFT JOIN user_assignments ua ON p.id = ua.position_id
            LEFT JOIN responsibility_assignments ra ON p.id = ra.position_id
            LEFT JOIN (
                SELECT 
                    p2.id as position_id,
                    (
                        (SELECT COUNT(*) FROM responsibilities WHERE is_shared = 1 AND status = 'active') +
                        (SELECT COUNT(*) FROM responsibilities WHERE is_shared = 0 AND position_scope = p2.key_name AND status = 'active')
                    ) as total_responsibilities
                FROM positions p2
            ) resp_counts ON resp_counts.position_id = p.id
            WHERE p.status = 'active'
            GROUP BY p.id, p.key_name, p.name_en, p.name_om, p.level_scope, resp_counts.total_responsibilities
            ORDER BY p.sort_order, p.name_en
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Log position activity
     */
    public function logActivity(int $positionId, string $action, array $details = [], ?int $userId = null): void
    {
        $logData = [
            'table_name' => $this->table,
            'record_id' => $positionId,
            'action' => $action,
            'details' => json_encode($details),
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->query(
            "INSERT INTO activity_logs (table_name, record_id, action, details, user_id, ip_address, user_agent, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($logData)
        );
    }
}