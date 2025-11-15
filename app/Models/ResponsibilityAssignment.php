<?php
namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * ResponsibilityAssignment Model
 * Manages assignment of responsibilities to users/positions
 * ABO-WBO Management System - Responsibility Assignment Management
 */
class ResponsibilityAssignment extends Model
{
    protected $table = 'responsibility_assignments';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id', 'responsibility_id', 'position_id', 'organizational_unit_id',
        'organizational_unit_type', 'level_scope', 'assignment_date', 'due_date',
        'priority', 'status', 'completion_percentage', 'notes', 'metadata',
        'assigned_by', 'approved_by', 'completed_at', 'approved_at'
    ];
    
    protected $casts = [
        'metadata' => 'json',
        'completion_percentage' => 'integer',
        'priority' => 'integer'
    ];
    
    // Assignment statuses
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    
    // Priority levels
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_CRITICAL = 4;
    
    /**
     * Assign responsibility to user
     */
    public function assignResponsibility(array $data): int
    {
        // Validate required fields
        $required = ['user_id', 'responsibility_id', 'position_id', 'organizational_unit_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        // Validate user exists and has position
        if (!$this->validateUserPosition($data['user_id'], $data['position_id'], $data['organizational_unit_id'])) {
            throw new Exception("User is not assigned to the specified position");
        }
        
        // Set defaults
        $data['assignment_date'] = $data['assignment_date'] ?? date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? self::STATUS_ASSIGNED;
        $data['priority'] = $data['priority'] ?? self::PRIORITY_MEDIUM;
        $data['completion_percentage'] = 0;
        
        // Calculate due date if not provided
        if (empty($data['due_date'])) {
            $responsibility = (new Responsibility())->find($data['responsibility_id']);
            if ($responsibility && $responsibility['frequency']) {
                $dueDate = new \DateTime($data['assignment_date']);
                $dueDate->add(new \DateInterval('P' . $responsibility['frequency'] . 'D'));
                $data['due_date'] = $dueDate->format('Y-m-d H:i:s');
            }
        }
        
        $assignmentId = $this->create($data);
        
        // Log activity
        $this->logActivity($assignmentId, 'responsibility_assigned', [
            'user_id' => $data['user_id'],
            'responsibility_id' => $data['responsibility_id'],
            'position_id' => $data['position_id'],
            'assigned_by' => $data['assigned_by'] ?? null
        ]);
        
        return $assignmentId;
    }
    
    /**
     * Get assignments with filters
     */
    public function getAssignmentsWithFilters(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        $joins = [];
        
        // Base query with joins
        $joins[] = "LEFT JOIN users u ON u.id = ra.user_id";
        $joins[] = "LEFT JOIN responsibilities r ON r.id = ra.responsibility_id";
        $joins[] = "LEFT JOIN positions p ON p.id = ra.position_id";
        
        // User filter
        if (!empty($filters['user_id'])) {
            $conditions[] = "ra.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        // Position filter
        if (!empty($filters['position_id'])) {
            $conditions[] = "ra.position_id = ?";
            $params[] = $filters['position_id'];
        }
        
        // Organizational unit filter
        if (!empty($filters['organizational_unit_id'])) {
            $conditions[] = "ra.organizational_unit_id = ?";
            $params[] = $filters['organizational_unit_id'];
        }
        
        // Level scope filter
        if (!empty($filters['level_scope'])) {
            $conditions[] = "ra.level_scope = ?";
            $params[] = $filters['level_scope'];
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $placeholders = str_repeat('?,', count($filters['status']) - 1) . '?';
                $conditions[] = "ra.status IN ({$placeholders})";
                $params = array_merge($params, $filters['status']);
            } else {
                $conditions[] = "ra.status = ?";
                $params[] = $filters['status'];
            }
        }
        
        // Priority filter
        if (!empty($filters['priority'])) {
            $conditions[] = "ra.priority = ?";
            $params[] = $filters['priority'];
        }
        
        // Due date filters
        if (!empty($filters['due_before'])) {
            $conditions[] = "ra.due_date <= ?";
            $params[] = $filters['due_before'];
        }
        
        if (!empty($filters['due_after'])) {
            $conditions[] = "ra.due_date >= ?";
            $params[] = $filters['due_after'];
        }
        
        // Overdue filter
        if (!empty($filters['overdue'])) {
            $conditions[] = "ra.due_date < NOW() AND ra.status NOT IN ('completed', 'cancelled')";
        }
        
        // Responsibility type filter
        if (!empty($filters['responsibility_type'])) {
            $conditions[] = "r.responsibility_type = ?";
            $params[] = $filters['responsibility_type'];
        }
        
        // Shared responsibility filter
        if (isset($filters['is_shared'])) {
            $conditions[] = "r.is_shared = ?";
            $params[] = $filters['is_shared'] ? 1 : 0;
        }
        
        $joinClause = implode(' ', $joins);
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $orderBy = "ORDER BY ra.priority DESC, ra.due_date ASC, ra.assignment_date DESC";
        
        $query = "
            SELECT 
                ra.*,
                u.full_name as user_name,
                u.email as user_email,
                r.name_en as responsibility_name_en,
                r.name_om as responsibility_name_om,
                r.responsibility_type,
                r.is_shared,
                p.name_en as position_name_en,
                p.name_om as position_name_om,
                p.key_name as position_key
            FROM {$this->table} ra
            {$joinClause}
            {$whereClause}
            {$orderBy}
        ";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get user's current assignments
     */
    public function getUserAssignments(int $userId, array $filters = []): array
    {
        $filters['user_id'] = $userId;
        return $this->getAssignmentsWithFilters($filters);
    }
    
    /**
     * Get position assignments
     */
    public function getPositionAssignments(int $positionId, array $filters = []): array
    {
        $filters['position_id'] = $positionId;
        return $this->getAssignmentsWithFilters($filters);
    }
    
    /**
     * Get organizational unit assignments
     */
    public function getOrganizationalUnitAssignments(int $organizationalUnitId, string $levelScope, array $filters = []): array
    {
        $filters['organizational_unit_id'] = $organizationalUnitId;
        $filters['level_scope'] = $levelScope;
        return $this->getAssignmentsWithFilters($filters);
    }
    
    /**
     * Update assignment progress
     */
    public function updateProgress(int $assignmentId, int $completionPercentage, string $notes = ''): bool
    {
        $data = [
            'completion_percentage' => max(0, min(100, $completionPercentage)),
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update status based on completion
        if ($completionPercentage >= 100) {
            $data['status'] = self::STATUS_COMPLETED;
            $data['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($completionPercentage > 0) {
            $data['status'] = self::STATUS_IN_PROGRESS;
        }
        
        $updated = $this->update($assignmentId, $data);
        
        if ($updated) {
            $this->logActivity($assignmentId, 'progress_updated', [
                'completion_percentage' => $completionPercentage,
                'status' => $data['status'],
                'notes' => $notes
            ]);
        }
        
        return $updated;
    }
    
    /**
     * Complete assignment
     */
    public function completeAssignment(int $assignmentId, string $completionNotes = '', ?int $approvedBy = null): bool
    {
        $data = [
            'status' => self::STATUS_COMPLETED,
            'completion_percentage' => 100,
            'completed_at' => date('Y-m-d H:i:s'),
            'notes' => $completionNotes,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($approvedBy) {
            $data['approved_by'] = $approvedBy;
            $data['approved_at'] = date('Y-m-d H:i:s');
        }
        
        $updated = $this->update($assignmentId, $data);
        
        if ($updated) {
            $this->logActivity($assignmentId, 'assignment_completed', [
                'completion_notes' => $completionNotes,
                'approved_by' => $approvedBy
            ]);
        }
        
        return $updated;
    }
    
    /**
     * Bulk assign responsibilities to position holders
     */
    public function bulkAssignToPositionHolders(int $positionId, array $responsibilityIds, array $assignmentData = []): array
    {
        $results = [];
        
        try {
            // Get all users assigned to this position
            $userAssignmentModel = new UserAssignment();
            $positionHolders = $userAssignmentModel->getUsersByPosition($positionId, ['status' => 'approved']);
            
            if (empty($positionHolders)) {
                throw new Exception("No approved users found for position ID: {$positionId}");
            }
            
            foreach ($positionHolders as $holder) {
                foreach ($responsibilityIds as $responsibilityId) {
                    $data = array_merge($assignmentData, [
                        'user_id' => $holder['user_id'],
                        'responsibility_id' => $responsibilityId,
                        'position_id' => $positionId,
                        'organizational_unit_id' => $holder['organizational_unit_id'],
                        'organizational_unit_type' => $holder['organizational_unit_type'],
                        'level_scope' => $holder['level_scope']
                    ]);
                    
                    try {
                        $assignmentId = $this->assignResponsibility($data);
                        $results[] = [
                            'success' => true,
                            'assignment_id' => $assignmentId,
                            'user_id' => $holder['user_id'],
                            'responsibility_id' => $responsibilityId
                        ];
                    } catch (Exception $e) {
                        $results[] = [
                            'success' => false,
                            'error' => $e->getMessage(),
                            'user_id' => $holder['user_id'],
                            'responsibility_id' => $responsibilityId
                        ];
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Bulk assignment error: " . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * Get overdue assignments
     */
    public function getOverdueAssignments(array $filters = []): array
    {
        $filters['overdue'] = true;
        $assignments = $this->getAssignmentsWithFilters($filters);
        
        // Update status to overdue if not already
        foreach ($assignments as $assignment) {
            if ($assignment['status'] !== self::STATUS_OVERDUE && 
                !in_array($assignment['status'], [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
                $this->update($assignment['id'], ['status' => self::STATUS_OVERDUE]);
            }
        }
        
        return $assignments;
    }
    
    /**
     * Get assignment statistics
     */
    public function getAssignmentStats(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        
        // Apply basic filters
        if (!empty($filters['user_id'])) {
            $conditions[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['position_id'])) {
            $conditions[] = "position_id = ?";
            $params[] = $filters['position_id'];
        }
        
        if (!empty($filters['organizational_unit_id'])) {
            $conditions[] = "organizational_unit_id = ?";
            $params[] = $filters['organizational_unit_id'];
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $query = "
            SELECT 
                status,
                COUNT(*) as total_assignments,
                AVG(completion_percentage) as avg_completion,
                SUM(CASE WHEN due_date < NOW() AND status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as overdue_count
            FROM {$this->table}
            {$whereClause}
            GROUP BY status
            ORDER BY status
        ";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Validate user has position assignment
     */
    private function validateUserPosition(int $userId, int $positionId, int $organizationalUnitId): bool
    {
        $userAssignmentModel = new UserAssignment();
        $assignment = $userAssignmentModel->getUserPositionAssignment($userId, $positionId, $organizationalUnitId);
        
        return $assignment && $assignment['status'] === 'approved';
    }
    
    /**
     * Log activity
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