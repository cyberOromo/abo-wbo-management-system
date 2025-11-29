<?php

namespace App\Models;

use App\Core\Model;
use App\Utils\Database;
use PDO;
use Exception;

class Gurmu extends Model
{
    protected $table = 'gurmus';
    protected $fillable = [
        'gamta_id', 'name', 'code', 'description', 'contact_email', 'contact_phone',
        'address', 'website', 'meeting_schedule', 'membership_fee', 'currency',
        'status', 'metadata', 'created_by'
    ];

    /**
     * Get all active Gurmus
     */
    public function getActive()
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name"
        );
    }

    /**
     * Get all gurmus with optional filtering and pagination
     */
    public static function getWithFilters($filters = [], $page = 1, $limit = 20)
    {
        $db = Database::getInstance();
        $conditions = ['1=1'];
        $params = [];

        // Build WHERE conditions
        if (!empty($filters['search'])) {
            $conditions[] = "(g.name LIKE :search OR g.code LIKE :search OR g.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $conditions[] = "g.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['gamta_id'])) {
            $conditions[] = "g.gamta_id = :gamta_id";
            $params[':gamta_id'] = $filters['gamta_id'];
        }

        if (!empty($filters['godina_id'])) {
            $conditions[] = "gm.godina_id = :godina_id";
            $params[':godina_id'] = $filters['godina_id'];
        }

        if (!empty($filters['membership_fee_min'])) {
            $conditions[] = "g.membership_fee >= :fee_min";
            $params[':fee_min'] = $filters['membership_fee_min'];
        }

        if (!empty($filters['membership_fee_max'])) {
            $conditions[] = "g.membership_fee <= :fee_max";
            $params[':fee_max'] = $filters['membership_fee_max'];
        }

        if (!empty($filters['currency'])) {
            $conditions[] = "g.currency = :currency";
            $params[':currency'] = $filters['currency'];
        }

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Main query with joins
        $sql = "
            SELECT 
                g.*,
                gm.name as gamta_name,
                gm.code as gamta_code,
                god.name as godina_name,
                god.code as godina_code,
                creator.first_name as created_by_first_name,
                creator.last_name as created_by_last_name,
                COUNT(DISTINCT u.id) as user_count,
                COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as active_users,
                COUNT(DISTINCT t.id) as task_count,
                COUNT(DISTINCT m.id) as meeting_count,
                COUNT(DISTINCT e.id) as event_count
            FROM gurmus g
            LEFT JOIN gamtas gm ON g.gamta_id = gm.id
            LEFT JOIN godinas god ON gm.godina_id = god.id
            LEFT JOIN users creator ON g.created_by = creator.id
            LEFT JOIN users u ON g.id = u.gurmu_id
            LEFT JOIN tasks t ON JSON_CONTAINS(t.target_audience, JSON_QUOTE(CONCAT('gurmu_', g.id)))
            LEFT JOIN meetings m ON JSON_CONTAINS(m.target_audience, JSON_QUOTE(CONCAT('gurmu_', g.id)))
            LEFT JOIN events evt ON JSON_CONTAINS(evt.target_audience, JSON_QUOTE(CONCAT('gurmu_', g.id)))
            WHERE " . implode(' AND ', $conditions) . "
            GROUP BY g.id
            ORDER BY g.name ASC
            LIMIT :limit OFFSET :offset
        ";

        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $db->query($sql);
        $stmt->execute($params);
        $gurmus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "
            SELECT COUNT(DISTINCT g.id) as total
            FROM gurmus g
            LEFT JOIN gamtas gm ON g.gamta_id = gm.id
            LEFT JOIN godinas god ON gm.godina_id = god.id
            WHERE " . implode(' AND ', $conditions);

        unset($params[':limit'], $params[':offset']);
        $countStmt = $db->query($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'data' => $gurmus,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Find gurmu with detailed relationships
     */
    public static function findWithRelations($id)
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                g.*,
                gm.id as gamta_id,
                gm.name as gamta_name,
                gm.code as gamta_code,
                gm.status as gamta_status,
                god.id as godina_id,
                god.name as godina_name,
                god.code as godina_code,
                god.status as godina_status,
                creator.first_name as created_by_first_name,
                creator.last_name as created_by_last_name,
                creator.email as created_by_email,
                COUNT(DISTINCT u.id) as user_count,
                COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as active_users,
                COUNT(DISTINCT CASE WHEN u.status = 'pending_approval' THEN u.id END) as pending_users,
                SUM(DISTINCT d.amount) as total_donations,
                COUNT(DISTINCT d.id) as donation_count,
                MAX(u.last_activity_at) as last_user_activity
            FROM gurmus g
            LEFT JOIN gamtas gm ON g.gamta_id = gm.id
            LEFT JOIN godinas god ON gm.godina_id = god.id
            LEFT JOIN users creator ON g.created_by = creator.id
            LEFT JOIN users u ON g.id = u.gurmu_id
            LEFT JOIN donations d ON d.allocated_to_level = 'gurmu' AND d.allocated_to_id = g.id AND d.payment_status = 'completed'
            WHERE g.id = :id
            GROUP BY g.id
        ";

        $stmt = $db->query($sql);
        $stmt->execute([':id' => $id]);
        $gurmu = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$gurmu) {
            return null;
        }

        // Get users assigned to this gurmu
        $gurmu['users'] = self::getUsers($id);

        // Get recent activities
        $gurmu['recent_activities'] = self::getRecentActivities($id);

        // Get statistics
        $gurmu['statistics'] = self::getStatistics($id);

        return $gurmu;
    }

    /**
     * Get all gurmus for a specific gamta
     */
    public static function getByGamta($gamtaId)
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                g.*,
                COUNT(DISTINCT u.id) as user_count,
                COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as active_users
            FROM gurmus g
            LEFT JOIN users u ON g.id = u.gurmu_id
            WHERE g.gamta_id = :gamta_id AND g.status != 'deleted'
            GROUP BY g.id
            ORDER BY g.name ASC
        ";

        $stmt = $db->query($sql);
        $stmt->execute([':gamta_id' => $gamtaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get users assigned to this gurmu
     */
    public static function getUsers($gurmuId, $status = null)
    {
        $db = Database::getInstance();
        $conditions = ['u.gurmu_id = :gurmu_id'];
        $params = [':gurmu_id' => $gurmuId];

        if ($status) {
            $conditions[] = 'u.status = :status';
            $params[':status'] = $status;
        }

        $sql = "
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.status,
                u.last_login_at,
                u.created_at,
                p.name_en as position_name,
                p.name_om as position_name_om
            FROM users u
            LEFT JOIN positions p ON u.position_id = p.id
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY u.first_name ASC, u.last_name ASC
        ";

        $stmt = $db->query($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user count by role/position for this gurmu
     */
    public static function getUserRoleDistribution($gurmuId)
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                COALESCE(p.name_en, 'Member') as role_name,
                COUNT(u.id) as user_count
            FROM users u
            LEFT JOIN positions p ON u.position_id = p.id
            WHERE u.gurmu_id = :gurmu_id AND u.status = 'active'
            GROUP BY u.position_id, p.name_en
            ORDER BY user_count DESC
        ";

        $stmt = $db->query($sql);
        $stmt->execute([':gurmu_id' => $gurmuId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get membership fee statistics
     */
    public static function getMembershipFeeStats($gurmuId)
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                g.membership_fee,
                g.currency,
                COUNT(u.id) as total_members,
                COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_members,
                (g.membership_fee * COUNT(CASE WHEN u.status = 'active' THEN 1 END)) as expected_monthly_revenue
            FROM gurmus g
            LEFT JOIN users u ON g.id = u.gurmu_id
            WHERE g.id = :gurmu_id
            GROUP BY g.id
        ";

        $stmt = $db->query($sql);
        $stmt->execute([':gurmu_id' => $gurmuId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get meeting schedule and attendance statistics
     */
    public static function getMeetingStats($gurmuId)
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                COUNT(m.id) as total_meetings,
                COUNT(CASE WHEN m.status = 'completed' THEN 1 END) as completed_meetings,
                COUNT(CASE WHEN m.status = 'scheduled' THEN 1 END) as upcoming_meetings,
                AVG(
                    (SELECT COUNT(*) FROM meeting_attendees ma WHERE ma.meeting_id = m.id AND ma.attendance_status = 'present')
                ) as avg_attendance
            FROM meetings m
            WHERE JSON_CONTAINS(m.target_audience, JSON_QUOTE(CONCAT('gurmu_', :gurmu_id)))
            AND m.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ";

        $stmt = $db->query($sql);
        $stmt->execute([':gurmu_id' => $gurmuId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new gurmu with validation
     */
    public static function createGurmu($data)
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            // Validate required fields
            $required = ['gamta_id', 'name', 'code'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }

            // Check if code is unique
            if (self::isCodeExists($data['code'])) {
                throw new Exception("Gurmu code '{$data['code']}' already exists");
            }

            // Validate gamta exists
            $gamtaData = $db->fetch("SELECT id, status FROM gamtas WHERE id = ?", [$data['gamta_id']]);
            
            if (!$gamtaData) {
                throw new Exception("Parent Gamta not found");
            }
            
            if ($gamtaData['status'] !== 'active') {
                throw new Exception("Parent Gamta is not active");
            }

            // Set defaults
            $data['status'] = $data['status'] ?? 'active';
            $data['membership_fee'] = $data['membership_fee'] ?? 0.00;
            $data['currency'] = $data['currency'] ?? 'USD';
            $data['created_by'] = $data['created_by'] ?? $_SESSION['user_id'] ?? null;

            // Prepare metadata
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $data['metadata'] = json_encode($data['metadata']);
            }

            // Insert gurmu using Database insert() method
            $insertData = [
                'gamta_id' => $data['gamta_id'],
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'description' => $data['description'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'address' => $data['address'] ?? null,
                'website' => $data['website'] ?? null,
                'meeting_schedule' => $data['meeting_schedule'] ?? null,
                'membership_fee' => $data['membership_fee'],
                'currency' => $data['currency'],
                'status' => $data['status'],
                'metadata' => $data['metadata'] ?? null,
                'created_by' => $data['created_by']
            ];

            $gurmuId = $db->insert('gurmus', $insertData);

            if (!$gurmuId) {
                throw new Exception("Failed to create gurmu");
            }

            // Log activity
            self::logActivity($gurmuId, 'created', null, $data, $data['created_by']);

            $db->commit();

            return $gurmuId;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Update gurmu with validation
     */
    public static function updateGurmu($id, $data)
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            // Get current data for comparison
            $current = self::find($id);
            if (!$current) {
                throw new Exception("Gurmu not found");
            }

            // Check if code is unique (excluding current record)
            if (isset($data['code']) && $data['code'] !== $current['code']) {
                if (self::isCodeExists($data['code'], $id)) {
                    throw new Exception("Gurmu code '{$data['code']}' already exists");
                }
            }

            // Validate gamta exists if changing
            if (isset($data['gamta_id']) && $data['gamta_id'] !== $current['gamta_id']) {
                $gamta = $db->query("SELECT id, status FROM gamtas WHERE id = :id");
                $gamta->execute([':id' => $data['gamta_id']]);
                $gamtaData = $gamta->fetch(PDO::FETCH_ASSOC);
                
                if (!$gamtaData) {
                    throw new Exception("Parent Gamta not found");
                }
                
                if ($gamtaData['status'] !== 'active') {
                    throw new Exception("Parent Gamta is not active");
                }
            }

            // Prepare metadata
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $data['metadata'] = json_encode($data['metadata']);
            }

            // Build update query
            $updateFields = [];
            $params = [':id' => $id];

            foreach ($data as $field => $value) {
                if (in_array($field, self::$fillable)) {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $value;
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No valid fields to update");
            }

            $sql = "UPDATE gurmus SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $db->query($sql);
            $result = $stmt->execute($params);

            if (!$result) {
                throw new Exception("Failed to update gurmu");
            }

            // Log activity
            self::logActivity($id, 'updated', $current, $data, $_SESSION['user_id'] ?? null);

            $db->commit();

            return true;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete gurmu
     */
    public static function softDelete($id, $userId = null)
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            $current = self::find($id);
            if (!$current) {
                throw new Exception("Gurmu not found");
            }

            // Check if gurmu has active users
            $userCount = $db->query("SELECT COUNT(*) FROM users WHERE gurmu_id = :id AND status = 'active'");
            $userCount->execute([':id' => $id]);
            $activeUsers = $userCount->fetchColumn();

            if ($activeUsers > 0) {
                throw new Exception("Cannot delete gurmu with active users. Please reassign users first.");
            }

            // Soft delete by setting status to inactive
            $stmt = $db->query("UPDATE gurmus SET status = 'inactive', updated_at = NOW() WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if (!$result) {
                throw new Exception("Failed to delete gurmu");
            }

            // Log activity
            self::logActivity($id, 'deleted', $current, ['status' => 'inactive'], $userId);

            $db->commit();

            return true;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Check if code exists
     */
    public static function isCodeExists($code, $excludeId = null)
    {
        $db = Database::getInstance();
        $conditions = ['code = :code'];
        $params = [':code' => strtoupper($code)];

        if ($excludeId) {
            $conditions[] = 'id != ?';
            $params[] = $excludeId;
        }

        $sql = "SELECT COUNT(*) FROM gurmus WHERE " . implode(' AND ', $conditions);
        $count = $db->fetchColumn($sql, $params);
        return $count > 0;
    }

    /**
     * Get recent activities for this gurmu
     */
    public static function getRecentActivities($gurmuId, $limit = 10)
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                'user_joined' as type,
                CONCAT(u.first_name, ' ', u.last_name, ' joined the gurmu') as description,
                u.created_at as created_at
            FROM users u
            WHERE u.gurmu_id = :gurmu_id AND u.status = 'active'
            
            UNION ALL
            
            SELECT 
                'task_created' as type,
                CONCAT('New task: ', t.title) as description,
                t.created_at
            FROM tasks t
            WHERE JSON_CONTAINS(t.target_audience, JSON_QUOTE(CONCAT('gurmu_', :gurmu_id)))
            
            UNION ALL
            
            SELECT 
                'meeting_scheduled' as type,
                CONCAT('Meeting scheduled: ', m.title) as description,
                m.created_at
            FROM meetings m
            WHERE JSON_CONTAINS(m.target_audience, JSON_QUOTE(CONCAT('gurmu_', :gurmu_id)))
            
            ORDER BY created_at DESC
            LIMIT :limit
        ";

        $stmt = $db->query($sql);
        $stmt->execute([
            ':gurmu_id' => $gurmuId,
            ':limit' => $limit
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get comprehensive statistics for gurmu
     */
    public static function getStatistics($gurmuId)
    {
        $db = Database::getInstance();
        
        // Basic user statistics
        $userStats = $db->query("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                COUNT(CASE WHEN status = 'pending_approval' THEN 1 END) as pending_users,
                COUNT(CASE WHEN date_of_birth IS NOT NULL AND TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 35 THEN 1 END) as youth_count,
                COUNT(CASE WHEN gender = 'female' THEN 1 END) as female_count,
                COUNT(CASE WHEN last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_last_30_days
            FROM users 
            WHERE gurmu_id = :gurmu_id
        ");
        $userStats->execute([':gurmu_id' => $gurmuId]);
        $userStats = $userStats->fetch(PDO::FETCH_ASSOC);

        // Activity statistics
        $activityStats = $db->query("
            SELECT 
                COUNT(DISTINCT t.id) as total_tasks,
                COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_tasks,
                COUNT(DISTINCT m.id) as total_meetings,
                COUNT(DISTINCT CASE WHEN m.status = 'completed' THEN m.id END) as completed_meetings,
                COUNT(DISTINCT e.id) as total_events,
                COALESCE(SUM(d.amount), 0) as total_donations
            FROM gurmus g
            LEFT JOIN tasks t ON JSON_CONTAINS(t.target_audience, JSON_QUOTE(CONCAT('gurmu_', g.id)))
            LEFT JOIN meetings m ON JSON_CONTAINS(m.target_audience, JSON_QUOTE(CONCAT('gurmu_', g.id)))
            LEFT JOIN events e ON JSON_CONTAINS(e.target_audience, JSON_QUOTE(CONCAT('gurmu_', g.id)))
            LEFT JOIN donations d ON d.allocated_to_level = 'gurmu' AND d.allocated_to_id = g.id AND d.payment_status = 'completed'
            WHERE g.id = :gurmu_id
            GROUP BY g.id
        ");
        $activityStats->execute([':gurmu_id' => $gurmuId]);
        $activityStats = $activityStats->fetch(PDO::FETCH_ASSOC) ?: [];

        return array_merge($userStats, $activityStats);
    }

    /**
     * Log gurmu activity
     */
    private static function logActivity($gurmuId, $type, $oldData, $newData, $userId)
    {
        $db = Database::getInstance();
        
        try {
            $sql = "
                INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
                VALUES (:user_id, :action, 'gurmus', :record_id, :old_values, :new_values, :ip_address, :user_agent)
            ";

            $stmt = $db->query($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => "gurmu_{$type}",
                ':record_id' => $gurmuId,
                ':old_values' => $oldData ? json_encode($oldData) : null,
                ':new_values' => json_encode($newData),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Log errors but don't fail the main operation
            error_log("Failed to log gurmu activity: " . $e->getMessage());
        }
    }

    /**
     * Get gurmus list for dropdown/select options
     */
    public static function getSelectOptions($gamtaId = null, $status = 'active')
    {
        $db = Database::getInstance();
        $conditions = ['g.status = :status'];
        $params = [':status' => $status];

        if ($gamtaId) {
            $conditions[] = 'g.gamta_id = :gamta_id';
            $params[':gamta_id'] = $gamtaId;
        }

        $sql = "
            SELECT 
                g.id,
                g.name,
                g.code,
                gm.name as gamta_name,
                CONCAT(g.name, ' (', g.code, ')') as display_name
            FROM gurmus g
            LEFT JOIN gamtas gm ON g.gamta_id = gm.id
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY g.name ASC
        ";

        $stmt = $db->query($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Move users from one gurmu to another
     */
    public static function moveUsers($fromGurmuId, $toGurmuId, $userIds = [])
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            // Validate both gurmus exist and are active
            $gurmus = $db->query("SELECT id, status FROM gurmus WHERE id IN (:from_id, :to_id)");
            $gurmus->execute([':from_id' => $fromGurmuId, ':to_id' => $toGurmuId]);
            $gurmuData = $gurmus->fetchAll(PDO::FETCH_ASSOC);

            if (count($gurmuData) !== 2) {
                throw new Exception("One or both gurmus not found");
            }

            foreach ($gurmuData as $gurmu) {
                if ($gurmu['status'] !== 'active') {
                    throw new Exception("Both gurmus must be active");
                }
            }

            // Build user condition
            $userCondition = '';
            $params = [':from_gurmu_id' => $fromGurmuId, ':to_gurmu_id' => $toGurmuId];

            if (!empty($userIds)) {
                $placeholders = [];
                foreach ($userIds as $index => $userId) {
                    $placeholder = ":user_id_{$index}";
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $userId;
                }
                $userCondition = ' AND id IN (' . implode(',', $placeholders) . ')';
            }

            // Move users
            $sql = "UPDATE users SET gurmu_id = :to_gurmu_id WHERE gurmu_id = :from_gurmu_id" . $userCondition;
            $stmt = $db->query($sql);
            $result = $stmt->execute($params);

            if (!$result) {
                throw new Exception("Failed to move users");
            }

            $movedCount = $stmt->rowCount();

            // Log activity
            $logData = [
                'moved_from_gurmu_id' => $fromGurmuId,
                'moved_to_gurmu_id' => $toGurmuId,
                'user_count' => $movedCount,
                'specific_users' => $userIds
            ];

            self::logActivity($toGurmuId, 'users_moved_in', null, $logData, $_SESSION['user_id'] ?? null);
            self::logActivity($fromGurmuId, 'users_moved_out', null, $logData, $_SESSION['user_id'] ?? null);

            $db->commit();

            return $movedCount;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Get hierarchy path for gurmu
     */
    public static function getHierarchyPath($gurmuId)
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                god.id as godina_id,
                god.name as godina_name,
                god.code as godina_code,
                gm.id as gamta_id,
                gm.name as gamta_name,
                gm.code as gamta_code,
                g.id as gurmu_id,
                g.name as gurmu_name,
                g.code as gurmu_code
            FROM gurmus g
            JOIN gamtas gm ON g.gamta_id = gm.id
            JOIN godinas god ON gm.godina_id = god.id
            WHERE g.id = :gurmu_id
        ";

        $stmt = $db->query($sql);
        $stmt->execute([':gurmu_id' => $gurmuId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}