<?php
namespace App\Services;

use App\Models\Godina;
use App\Models\Gamta;
use App\Models\Gurmu;
use App\Models\Position;
use App\Models\IndividualResponsibility;
use App\Models\SharedResponsibility;
use App\Models\User;
use App\Models\UserAssignment;
use App\Utils\Database;
use Exception;

/**
 * HierarchyService
 * Comprehensive service for managing the ABO-WBO organizational hierarchy
 * Implements the 4-tier structure: Global -> Godina -> Gamta -> Gurmu
 * Enhanced with advanced hierarchy operations for tasks, events, and more
 */
class HierarchyService
{
    private $godina;
    private $gamta;
    private $gurmu;
    private $position;
    private $individualResponsibility;
    private $sharedResponsibility;
    private $user;
    private $userAssignment;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->godina = new Godina();
        $this->gamta = new Gamta();
        $this->gurmu = new Gurmu();
        $this->position = new Position();
        $this->individualResponsibility = new IndividualResponsibility();
        $this->sharedResponsibility = new SharedResponsibility();
        $this->user = new User();
        $this->userAssignment = new UserAssignment();
    }

    // ===== NEW ENHANCED HIERARCHY METHODS =====

    /**
     * Get all entities (tasks, events, meetings) for a user's hierarchy scope
     */
    public function getHierarchyScopedData($userScope, $entityType, $filters = [])
    {
        $level = $userScope['level_scope'];
        
        $conditions = [];
        $params = [];
        
        // Build hierarchy filtering
        switch ($level) {
            case 'gurmu':
                if ($userScope['gurmu_id']) {
                    $conditions[] = "(level_scope = 'gurmu' AND gurmu_id = ?) OR level_scope = 'personal'";
                    $params[] = $userScope['gurmu_id'];
                }
                break;
            case 'gamta':
                if ($userScope['gamta_id']) {
                    $conditions[] = "(level_scope IN ('gamta', 'gurmu') AND gamta_id = ?) OR level_scope = 'personal'";
                    $params[] = $userScope['gamta_id'];
                }
                break;
            case 'godina':
                if ($userScope['godina_id']) {
                    $conditions[] = "(level_scope IN ('godina', 'gamta', 'gurmu') AND godina_id = ?) OR level_scope = 'personal'";
                    $params[] = $userScope['godina_id'];
                }
                break;
            case 'global':
                $conditions[] = "1=1"; // Global users see all
                break;
        }
        
        // Apply additional filters
        foreach ($filters as $field => $value) {
            $conditions[] = "$field = ?";
            $params[] = $value;
        }
        
        $whereClause = implode(" AND ", $conditions);
        
        $sql = "SELECT * FROM $entityType WHERE $whereClause ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Create a task with proper hierarchy assignment
     */
    public function createHierarchyTask($taskData, $creatorScope)
    {
        // Auto-assign hierarchy data based on creator's scope
        $taskData['created_by'] = $creatorScope['user_id'];
        $taskData['level_scope'] = $taskData['level_scope'] ?? $creatorScope['level_scope'];
        
        // Set hierarchy IDs based on level scope
        switch ($taskData['level_scope']) {
            case 'gurmu':
                $taskData['gurmu_id'] = $creatorScope['gurmu_id'];
                $taskData['gamta_id'] = $creatorScope['gamta_id'];
                $taskData['godina_id'] = $creatorScope['godina_id'];
                $taskData['scope_id'] = $creatorScope['gurmu_id'];
                break;
            case 'gamta':
                $taskData['gurmu_id'] = null;
                $taskData['gamta_id'] = $creatorScope['gamta_id'];
                $taskData['godina_id'] = $creatorScope['godina_id'];
                $taskData['scope_id'] = $creatorScope['gamta_id'];
                break;
            case 'godina':
                $taskData['gurmu_id'] = null;
                $taskData['gamta_id'] = null;
                $taskData['godina_id'] = $creatorScope['godina_id'];
                $taskData['scope_id'] = $creatorScope['godina_id'];
                break;
            case 'global':
                $taskData['gurmu_id'] = null;
                $taskData['gamta_id'] = null;
                $taskData['godina_id'] = null;
                $taskData['scope_id'] = null;
                break;
            case 'personal':
                $taskData['gurmu_id'] = $creatorScope['gurmu_id'];
                $taskData['gamta_id'] = $creatorScope['gamta_id'];
                $taskData['godina_id'] = $creatorScope['godina_id'];
                $taskData['scope_id'] = null;
                break;
        }
        
        $fields = implode(', ', array_keys($taskData));
        $placeholders = implode(', ', array_fill(0, count($taskData), '?'));
        
        $sql = "INSERT INTO tasks ($fields) VALUES ($placeholders)";
        
        return $this->db->execute($sql, array_values($taskData));
    }

    /**
     * Get hierarchy metrics for reporting
     */
    public function getHierarchyMetrics($scope, $metricType = null, $period = 'monthly')
    {
        $conditions = [
            "level_scope = ?",
            "scope_id = ?",
            "measurement_period = ?"
        ];
        $params = [$scope['level_scope'], $scope['scope_id'], $period];
        
        if ($metricType) {
            $conditions[] = "metric_type = ?";
            $params[] = $metricType;
        }
        
        $sql = "SELECT * FROM hierarchy_metrics 
                WHERE " . implode(" AND ", $conditions) . "
                ORDER BY measurement_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get hierarchy overview with statistics
     */
    public function getHierarchyOverview($scope)
    {
        $sql = "SELECT * FROM vw_hierarchy_overview 
                WHERE level_scope = ? AND scope_id = ?";
        
        return $this->db->fetch($sql, [$scope['level_scope'], $scope['scope_id']]);
    }

    // ===== EXISTING HIERARCHY METHODS =====

    /**
     * Get complete hierarchy structure
     */
    public function getCompleteHierarchy(): array
    {
        // Get all hierarchy levels
        $godinas = $this->godina->getActive();
        $gamtas = $this->gamta->getActive();
        $gurmus = $this->gurmu->getActive();

        // Build nested structure
        $hierarchy = [
            'global' => [
                'id' => 1,
                'name' => 'Global Organization',
                'type' => 'global',
                'children' => []
            ]
        ];

        // Build Godina level
        foreach ($godinas as $godina) {
            $godinaNode = [
                'id' => $godina['id'],
                'name' => $godina['name'],
                'code' => $godina['code'],
                'type' => 'godina',
                'children' => []
            ];

            // Add Gamtas under this Godina
            foreach ($gamtas as $gamta) {
                if ($gamta['godina_id'] == $godina['id']) {
                    $gamtaNode = [
                        'id' => $gamta['id'],
                        'name' => $gamta['name'],
                        'code' => $gamta['code'],
                        'type' => 'gamta',
                        'parent_id' => $godina['id'],
                        'children' => []
                    ];

                    // Add Gurmus under this Gamta
                    foreach ($gurmus as $gurmu) {
                        if ($gurmu['gamta_id'] == $gamta['id']) {
                            $gurmuNode = [
                                'id' => $gurmu['id'],
                                'name' => $gurmu['name'],
                                'code' => $gurmu['code'],
                                'type' => 'gurmu',
                                'parent_id' => $gamta['id'],
                                'membership_fee' => $gurmu['membership_fee'],
                                'currency' => $gurmu['currency'],
                                'children' => []
                            ];
                            $gamtaNode['children'][] = $gurmuNode;
                        }
                    }
                    $godinaNode['children'][] = $gamtaNode;
                }
            }
            $hierarchy['global']['children'][] = $godinaNode;
        }

        return $hierarchy;
    }

    /**
     * Get hierarchy statistics
     */
    public function getHierarchyStatistics(): array
    {
        $stats = [
            'global' => ['count' => 1, 'active' => 1],
            'godina' => $this->getEntityStatistics('godinas'),
            'gamta' => $this->getEntityStatistics('gamtas'),
            'gurmu' => $this->getEntityStatistics('gurmus'),
            'users' => $this->getEntityStatistics('users'),
            'positions' => $this->getEntityStatistics('positions'),
            'user_assignments' => $this->getEntityStatistics('user_assignments')
        ];

        // Calculate totals
        $stats['total_units'] = $stats['godina']['active'] + $stats['gamta']['active'] + $stats['gurmu']['active'] + 1;
        $stats['total_positions'] = $stats['total_units'] * 7; // 7 positions per unit
        $stats['assignment_coverage'] = $stats['positions']['active'] > 0 ? 
            ($stats['user_assignments']['active'] / $stats['total_positions']) * 100 : 0;

        return $stats;
    }

    /**
     * Get entity statistics
     */
    private function getEntityStatistics(string $table): array
    {
        $db = app()->resolve('database');
        
        $total = $db->fetchColumn("SELECT COUNT(*) FROM {$table}");
        $active = $db->fetchColumn("SELECT COUNT(*) FROM {$table} WHERE status = 'active'");
        
        return [
            'count' => (int)$total,
            'active' => (int)$active,
            'inactive' => (int)($total - $active)
        ];
    }

    /**
     * Get organizational unit path (from unit to global)
     */
    public function getOrganizationalPath(string $unitType, int $unitId): array
    {
        $path = [];
        
        switch ($unitType) {
            case 'gurmu':
                $gurmu = $this->gurmu->find($unitId);
                if ($gurmu) {
                    $path[] = [
                        'type' => 'gurmu',
                        'id' => $gurmu['id'],
                        'name' => $gurmu['name'],
                        'code' => $gurmu['code']
                    ];
                    
                    $gamta = $this->gamta->find($gurmu['gamta_id']);
                    if ($gamta) {
                        $path[] = [
                            'type' => 'gamta',
                            'id' => $gamta['id'],
                            'name' => $gamta['name'],
                            'code' => $gamta['code']
                        ];
                        
                        $godina = $this->godina->find($gamta['godina_id']);
                        if ($godina) {
                            $path[] = [
                                'type' => 'godina',
                                'id' => $godina['id'],
                                'name' => $godina['name'],
                                'code' => $godina['code']
                            ];
                        }
                    }
                }
                break;
                
            case 'gamta':
                $gamta = $this->gamta->find($unitId);
                if ($gamta) {
                    $path[] = [
                        'type' => 'gamta',
                        'id' => $gamta['id'],
                        'name' => $gamta['name'],
                        'code' => $gamta['code']
                    ];
                    
                    $godina = $this->godina->find($gamta['godina_id']);
                    if ($godina) {
                        $path[] = [
                            'type' => 'godina',
                            'id' => $godina['id'],
                            'name' => $godina['name'],
                            'code' => $godina['code']
                        ];
                    }
                }
                break;
                
            case 'godina':
                $godina = $this->godina->find($unitId);
                if ($godina) {
                    $path[] = [
                        'type' => 'godina',
                        'id' => $godina['id'],
                        'name' => $godina['name'],
                        'code' => $godina['code']
                    ];
                }
                break;
        }
        
        // Add global level
        $path[] = [
            'type' => 'global',
            'id' => 1,
            'name' => 'Global Organization',
            'code' => 'GLOBAL'
        ];
        
        return array_reverse($path); // Global -> Godina -> Gamta -> Gurmu
    }

    /**
     * Get user access scope based on their position assignment
     */
    public function getUserAccessScope(int $userId): array
    {
        $user = $this->user->find($userId);
        if (!$user) {
            throw new Exception("User not found");
        }

        // Get active user assignment
        $assignment = $this->userAssignment->getActiveAssignment($userId);
        if (!$assignment) {
            // Default scope is their Gurmu
            if ($user['gurmu_id']) {
                return $this->getOrganizationalPath('gurmu', $user['gurmu_id']);
            }
            return [];
        }

        // Get access scope based on assignment level
        $accessibleUnits = [];
        
        switch ($assignment['level_scope']) {
            case 'global':
                $accessibleUnits = $this->getCompleteHierarchy();
                break;
                
            case 'godina':
                $accessibleUnits = $this->getGodinaScope($assignment['organizational_unit_id']);
                break;
                
            case 'gamta':
                $accessibleUnits = $this->getGamtaScope($assignment['organizational_unit_id']);
                break;
                
            case 'gurmu':
                $accessibleUnits = $this->getGurmuScope($assignment['organizational_unit_id']);
                break;
        }

        return [
            'user' => $user,
            'assignment' => $assignment,
            'access_level' => $assignment['level_scope'],
            'accessible_units' => $accessibleUnits,
            'permissions' => $this->getUserPermissions($assignment)
        ];
    }

    /**
     * Get Godina scope (all Gamtas and Gurmus under this Godina)
     */
    private function getGodinaScope(int $godinaId): array
    {
        $godina = $this->godina->find($godinaId);
        if (!$godina) return [];

        $gamtas = $this->gamta->getWithFilters(['godina_id' => $godinaId]);
        $scope = ['godina' => $godina, 'gamtas' => []];

        foreach ($gamtas as $gamta) {
            $gurmus = $this->gurmu->getWithFilters(['gamta_id' => $gamta['id']]);
            $scope['gamtas'][] = [
                'gamta' => $gamta,
                'gurmus' => $gurmus
            ];
        }

        return $scope;
    }

    /**
     * Get Gamta scope (all Gurmus under this Gamta)
     */
    private function getGamtaScope(int $gamtaId): array
    {
        $gamta = $this->gamta->find($gamtaId);
        if (!$gamta) return [];

        $gurmus = $this->gurmu->getWithFilters(['gamta_id' => $gamtaId]);
        $godina = $this->godina->find($gamta['godina_id']);

        return [
            'godina' => $godina,
            'gamta' => $gamta,
            'gurmus' => $gurmus
        ];
    }

    /**
     * Get Gurmu scope (only this Gurmu)
     */
    private function getGurmuScope(int $gurmuId): array
    {
        $gurmu = $this->gurmu->find($gurmuId);
        if (!$gurmu) return [];

        $gamta = $this->gamta->find($gurmu['gamta_id']);
        $godina = $gamta ? $this->godina->find($gamta['godina_id']) : null;

        return [
            'godina' => $godina,
            'gamta' => $gamta,
            'gurmu' => $gurmu
        ];
    }

    /**
     * Get user permissions based on position assignment
     */
    private function getUserPermissions(array $assignment): array
    {
        $position = $this->position->find($assignment['position_id']);
        if (!$position) return [];

        // Get individual responsibilities for this position
        $individualResponsibilities = $this->individualResponsibility->getByPosition($position['key_name']);
        
        // Get shared responsibilities for this level
        $sharedResponsibilities = $this->sharedResponsibility->getByLevel($assignment['level_scope']);

        return [
            'position' => $position,
            'level' => $assignment['level_scope'],
            'individual_responsibilities' => $individualResponsibilities,
            'shared_responsibilities' => $sharedResponsibilities,
            'can_manage_users' => in_array($position['key_name'], ['dura_taa', 'tohannoo_keessaa']),
            'can_manage_finances' => $position['key_name'] === 'dinagdee',
            'can_manage_communications' => $position['key_name'] === 'mediyaa_sab_quunnamtii',
            'can_access_audit' => $position['key_name'] === 'tohannoo_keessaa'
        ];
    }

    /**
     * Assign user to position
     */
    public function assignUserToPosition(int $userId, int $positionId, int $organizationalUnitId, string $levelScope, int $assignedBy): array
    {
        // Validate inputs
        $user = $this->user->find($userId);
        $position = $this->position->find($positionId);
        
        if (!$user || !$position) {
            throw new Exception("Invalid user or position");
        }

        // Check if position is already assigned at this unit
        $existingAssignment = $this->userAssignment->getActivePositionAssignment($positionId, $organizationalUnitId, $levelScope);
        if ($existingAssignment) {
            throw new Exception("Position already assigned at this organizational unit");
        }

        // Create assignment
        $assignmentData = [
            'user_id' => $userId,
            'position_id' => $positionId,
            'organizational_unit_id' => $organizationalUnitId,
            'level_scope' => $levelScope,
            'assigned_by' => $assignedBy,
            'status' => 'pending_approval',
            'appointment_type' => 'appointment',
            'term_start' => date('Y-m-d'),
            'term_end' => date('Y-m-d', strtotime('+' . $position['term_length'] . ' months'))
        ];

        $assignmentId = $this->userAssignment->create($assignmentData);

        // Auto-assign responsibilities
        $this->assignResponsibilitiesToUser($userId, $position['key_name'], $levelScope, $organizationalUnitId);

        return [
            'assignment_id' => $assignmentId,
            'message' => 'User assigned to position successfully',
            'requires_approval' => true
        ];
    }

    /**
     * Auto-assign responsibilities to user
     */
    private function assignResponsibilitiesToUser(int $userId, string $positionKey, string $levelScope, int $organizationalUnitId): void
    {
        // Assign individual responsibilities
        $individualResponsibilities = $this->individualResponsibility->getByPosition($positionKey);
        foreach ($individualResponsibilities as $responsibility) {
            $this->createResponsibilityAssignment($userId, 'individual', $responsibility['id'], $levelScope, $organizationalUnitId);
        }

        // Assign shared responsibilities
        $sharedResponsibilities = $this->sharedResponsibility->getByLevel($levelScope);
        foreach ($sharedResponsibilities as $responsibility) {
            $this->createResponsibilityAssignment($userId, 'shared', $responsibility['id'], $levelScope, $organizationalUnitId);
        }
    }

    /**
     * Create responsibility assignment
     */
    private function createResponsibilityAssignment(int $userId, string $type, int $responsibilityId, string $levelScope, int $organizationalUnitId): void
    {
        $db = app()->resolve('database');
        
        $assignmentData = [
            'user_id' => $userId,
            'responsibility_type' => $type,
            'responsibility_id' => $responsibilityId,
            'assignment_level' => $levelScope,
            'scope_id' => $organizationalUnitId,
            'assigned_by' => 1, // System assignment
            'status' => 'assigned',
            'priority' => 'medium'
        ];

        $db->insert('user_responsibility_assignments', $assignmentData);
    }

    /**
     * Validate hierarchy integrity
     */
    public function validateHierarchyIntegrity(): array
    {
        $issues = [];

        // Check for orphaned entities
        $orphanedGamtas = $this->gamta->getOrphaned();
        if (!empty($orphanedGamtas)) {
            $issues[] = "Found " . count($orphanedGamtas) . " orphaned Gamtas (no parent Godina)";
        }

        $orphanedGurmus = $this->gurmu->getOrphaned();
        if (!empty($orphanedGurmus)) {
            $issues[] = "Found " . count($orphanedGurmus) . " orphaned Gurmus (no parent Gamta)";
        }

        // Check position responsibilities
        $positions = $this->position->getActive();
        foreach ($positions as $position) {
            $validation = $this->individualResponsibility->validatePositionResponsibilities($position['key_name']);
            if (!$validation['valid']) {
                $issues[] = "Position {$position['name_en']} has incomplete responsibilities (found {$validation['count']}, expected 5)";
            }
        }

        // Check shared responsibilities
        $levels = ['global', 'godina', 'gamta', 'gurmu'];
        foreach ($levels as $level) {
            $validation = $this->sharedResponsibility->validateLevelResponsibilities($level);
            if (!$validation['valid']) {
                $issues[] = "Level {$level} has incomplete shared responsibilities (found {$validation['count']}, expected 5)";
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get comprehensive system status
     */
    public function getSystemStatus(): array
    {
        return [
            'hierarchy' => $this->getHierarchyStatistics(),
            'integrity' => $this->validateHierarchyIntegrity(),
            'positions' => [
                'total' => count($this->position->getActive()),
                'individual_responsibilities' => $this->individualResponsibility->getStatistics(),
                'shared_responsibilities' => $this->sharedResponsibility->getStatistics()
            ],
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
}