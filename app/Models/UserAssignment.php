<?php
namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * User Assignment Model
 * Manages user position assignments with approval workflows
 * ABO-WBO Management System - Executive Assignment Management
 */
class UserAssignment extends Model
{
    protected $table = 'user_assignments';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id', 'position_id', 'organizational_unit_id', 'level_scope',
        'assigned_by', 'approved_by', 'status', 'approval_status',
        'term_start', 'term_end', 'election_date', 'appointment_type',
        'approval_notes', 'rejection_reason', 'metadata', 'notes'
    ];
    
    protected $casts = [
        'term_start' => 'date',
        'term_end' => 'date',
        'election_date' => 'date',
        'metadata' => 'json'
    ];
    
    /**
     * Get assignments with full details
     */
    public function getWithDetails(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        
        // User filter
        if (!empty($filters['user_id'])) {
            $conditions[] = "ua.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        // Position filter
        if (!empty($filters['position_id'])) {
            $conditions[] = "ua.position_id = ?";
            $params[] = $filters['position_id'];
        }
        
        // Level scope filter
        if (!empty($filters['level_scope'])) {
            $conditions[] = "ua.level_scope = ?";
            $params[] = $filters['level_scope'];
        }
        
        // Organizational unit filter
        if (!empty($filters['organizational_unit_id'])) {
            $conditions[] = "ua.organizational_unit_id = ?";
            $params[] = $filters['organizational_unit_id'];
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $conditions[] = "ua.status = ?";
            $params[] = $filters['status'];
        }
        
        // Approval status filter
        if (!empty($filters['approval_status'])) {
            $conditions[] = "ua.approval_status = ?";
            $params[] = $filters['approval_status'];
        }
        
        // Expiring soon filter
        if (!empty($filters['expiring_days'])) {
            $conditions[] = "ua.term_end IS NOT NULL AND ua.term_end <= DATE_ADD(NOW(), INTERVAL ? DAY)";
            $params[] = $filters['expiring_days'];
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $query = "
            SELECT ua.*,
                   u.first_name, u.last_name, u.email, u.phone,
                   p.name_en as position_name_en, p.name_om as position_name_om,
                   p.key_name as position_key, p.term_length, p.election_cycle,
                   assignedBy.first_name as assigned_by_name,
                   approvedBy.first_name as approved_by_name,
                   CASE ua.level_scope
                       WHEN 'global' THEN 'Global Organization'
                       WHEN 'godina' THEN god.name
                       WHEN 'gamta' THEN gam.name
                       WHEN 'gurmu' THEN gur.name
                   END as unit_name,
                   DATEDIFF(ua.term_end, NOW()) as days_until_expiry
            FROM {$this->table} ua
            JOIN users u ON ua.user_id = u.id
            JOIN positions p ON ua.position_id = p.id
            LEFT JOIN users assignedBy ON ua.assigned_by = assignedBy.id
            LEFT JOIN users approvedBy ON ua.approved_by = approvedBy.id
            LEFT JOIN godinas god ON ua.level_scope = 'godina' AND ua.organizational_unit_id = god.id
            LEFT JOIN gamtas gam ON ua.level_scope = 'gamta' AND ua.organizational_unit_id = gam.id
            LEFT JOIN gurmus gur ON ua.level_scope = 'gurmu' AND ua.organizational_unit_id = gur.id
            {$whereClause}
            ORDER BY ua.created_at DESC
        ";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get user's current active assignments
     */
    public function getUserActiveAssignments(int $userId): array
    {
        return $this->getWithDetails([
            'user_id' => $userId,
            'status' => 'active'
        ]);
    }
    
    /**
     * Get assignments for organizational unit
     */
    public function getUnitAssignments(string $levelScope, int $unitId, ?string $status = 'active'): array
    {
        $filters = [
            'level_scope' => $levelScope,
            'organizational_unit_id' => $unitId
        ];
        
        if ($status) {
            $filters['status'] = $status;
        }
        
        return $this->getWithDetails($filters);
    }
    
    /**
     * Assign user to position with approval workflow
     */
    public function assignUserToPosition(array $data): int
    {
        // Validate required fields
        $required = ['user_id', 'position_id', 'organizational_unit_id', 'level_scope', 'assigned_by'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        // Check if user already has an active assignment
        $currentAssignments = $this->getUserActiveAssignments($data['user_id']);
        if (!empty($currentAssignments)) {
            throw new Exception("User already has an active position assignment");
        }
        
        // Check if position is already filled
        $existingAssignment = $this->getPositionCurrentAssignment($data['position_id'], $data['organizational_unit_id']);
        if ($existingAssignment) {
            throw new Exception("Position is already filled");
        }
        
        // Set defaults
        $data['status'] = 'pending_approval';
        $data['approval_status'] = 'pending';
        $data['appointment_type'] = $data['appointment_type'] ?? 'appointment';
        
        // Calculate term dates if not provided
        if (empty($data['term_start'])) {
            $data['term_start'] = date('Y-m-d');
        }
        
        if (empty($data['term_end']) && !empty($data['term_length'])) {
            $data['term_end'] = date('Y-m-d', strtotime($data['term_start'] . ' + ' . $data['term_length'] . ' months'));
        }
        
        $assignmentId = $this->create($data);
        
        // Log activity
        $this->logActivity($assignmentId, 'assignment_created', [
            'user_id' => $data['user_id'],
            'position_id' => $data['position_id'],
            'assigned_by' => $data['assigned_by']
        ]);
        
        return $assignmentId;
    }
    
    /**
     * Approve position assignment
     */
    public function approveAssignment(int $assignmentId, int $approvedBy, ?string $notes = null): bool
    {
        $assignment = $this->find($assignmentId);
        if (!$assignment) {
            throw new Exception("Assignment not found");
        }
        
        if ($assignment['approval_status'] !== 'pending') {
            throw new Exception("Assignment is not pending approval");
        }
        
        $updateData = [
            'approval_status' => 'approved',
            'status' => 'active',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        if ($notes) {
            $updateData['approval_notes'] = $notes;
        }
        
        $success = $this->update($assignmentId, $updateData);
        
        if ($success) {
            $this->logActivity($assignmentId, 'assignment_approved', [
                'approved_by' => $approvedBy,
                'notes' => $notes
            ]);
        }
        
        return $success;
    }
    
    /**
     * Reject position assignment
     */
    public function rejectAssignment(int $assignmentId, int $rejectedBy, string $reason): bool
    {
        $assignment = $this->find($assignmentId);
        if (!$assignment) {
            throw new Exception("Assignment not found");
        }
        
        if ($assignment['approval_status'] !== 'pending') {
            throw new Exception("Assignment is not pending approval");
        }
        
        $updateData = [
            'approval_status' => 'rejected',
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ];
        
        $success = $this->update($assignmentId, $updateData);
        
        if ($success) {
            $this->logActivity($assignmentId, 'assignment_rejected', [
                'rejected_by' => $rejectedBy,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }
    
    /**
     * End assignment (resignation, term completion, removal)
     */
    public function endAssignment(int $assignmentId, string $endReason, int $endedBy, ?string $notes = null): bool
    {
        $assignment = $this->find($assignmentId);
        if (!$assignment) {
            throw new Exception("Assignment not found");
        }
        
        if ($assignment['status'] !== 'active') {
            throw new Exception("Assignment is not active");
        }
        
        $updateData = [
            'status' => 'ended',
            'ended_at' => date('Y-m-d H:i:s'),
            'end_reason' => $endReason,
            'ended_by' => $endedBy
        ];
        
        if ($notes) {
            $updateData['end_notes'] = $notes;
        }
        
        $success = $this->update($assignmentId, $updateData);
        
        if ($success) {
            $this->logActivity($assignmentId, 'assignment_ended', [
                'reason' => $endReason,
                'ended_by' => $endedBy,
                'notes' => $notes
            ]);
        }
        
        return $success;
    }
    
    /**
     * Get current assignment for position
     */
    public function getPositionCurrentAssignment(int $positionId, int $organizationalUnitId): ?array
    {
        $query = "
            SELECT ua.*, u.first_name, u.last_name, u.email
            FROM {$this->table} ua
            JOIN users u ON ua.user_id = u.id
            WHERE ua.position_id = ? 
            AND ua.organizational_unit_id = ?
            AND ua.status = 'active'
            LIMIT 1
        ";
        
        return $this->db->fetch($query, [$positionId, $organizationalUnitId]);
    }
    
    /**
     * Get pending approvals for user/level
     */
    public function getPendingApprovals(array $filters = []): array
    {
        $filters['approval_status'] = 'pending';
        return $this->getWithDetails($filters);
    }
    
    /**
     * Get assignments expiring soon
     */
    public function getExpiringSoon(int $days = 30, ?string $levelScope = null): array
    {
        $filters = [
            'expiring_days' => $days,
            'status' => 'active'
        ];
        
        if ($levelScope) {
            $filters['level_scope'] = $levelScope;
        }
        
        return $this->getWithDetails($filters);
    }
    
    /**
     * Get assignment statistics
     */
    public function getAssignmentStats(): array
    {
        $query = "
            SELECT 
                level_scope,
                COUNT(*) as total_assignments,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_assignments,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending_approval,
                SUM(CASE WHEN status = 'active' AND term_end <= DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon
            FROM {$this->table}
            GROUP BY level_scope
            ORDER BY FIELD(level_scope, 'global', 'godina', 'gamta', 'gurmu')
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Check if user can approve assignment based on hierarchy
     */
    public function canUserApprove(int $userId, int $assignmentId): bool
    {
        $assignment = $this->find($assignmentId);
        if (!$assignment) {
            return false;
        }
        
        // Get user's current position and level
        $userAssignments = $this->getUserActiveAssignments($userId);
        if (empty($userAssignments)) {
            return false;
        }
        
        $userAssignment = $userAssignments[0];
        
        // Approval hierarchy rules:
        // Global executives can approve Godina assignments
        // Godina executives can approve Gamta assignments within their Godina
        // Gamta executives can approve Gurmu assignments within their Gamta
        
        switch ($assignment['level_scope']) {
            case 'godina':
                return $userAssignment['level_scope'] === 'global';
                
            case 'gamta':
                if ($userAssignment['level_scope'] === 'global') {
                    return true;
                }
                if ($userAssignment['level_scope'] === 'godina') {
                    // Check if the Gamta belongs to user's Godina
                    return $this->isGamtaInGodina($assignment['organizational_unit_id'], $userAssignment['organizational_unit_id']);
                }
                return false;
                
            case 'gurmu':
                if ($userAssignment['level_scope'] === 'global') {
                    return true;
                }
                if ($userAssignment['level_scope'] === 'gamta') {
                    // Check if the Gurmu belongs to user's Gamta
                    return $this->isGurmuInGamta($assignment['organizational_unit_id'], $userAssignment['organizational_unit_id']);
                }
                return false;
                
            default:
                return false;
        }
    }
    
    /**
     * Check if Gamta belongs to Godina
     */
    private function isGamtaInGodina(int $gamtaId, int $godinaId): bool
    {
        $query = "SELECT COUNT(*) as count FROM gamtas WHERE id = ? AND godina_id = ?";
        $result = $this->db->fetch($query, [$gamtaId, $godinaId]);
        return $result['count'] > 0;
    }
    
    /**
     * Check if Gurmu belongs to Gamta
     */
    private function isGurmuInGamta(int $gurmuId, int $gamtaId): bool
    {
        $query = "SELECT COUNT(*) as count FROM gurmus WHERE id = ? AND gamta_id = ?";
        $result = $this->db->fetch($query, [$gurmuId, $gamtaId]);
        return $result['count'] > 0;
    }
    
    /**
     * Get assignment history for user
     */
    public function getUserAssignmentHistory(int $userId): array
    {
        return $this->getWithDetails(['user_id' => $userId]);
    }
    
    /**
     * Log assignment activity
     */
    public function logActivity(int $assignmentId, string $action, array $details = [], ?int $userId = null): void
    {
        $logData = [
            'table_name' => $this->table,
            'record_id' => $assignmentId,
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