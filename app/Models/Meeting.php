<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Meeting Model
 * Handles meeting management with Zoom integration support
 */
class Meeting extends Model
{
    protected $table = 'meetings';
    protected $primaryKey = 'id';
    
    // Meeting types
    const TYPE_REGULAR = 'regular';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_TRAINING = 'training';
    const TYPE_SOCIAL = 'social';
    const TYPE_PLANNING = 'planning';
    
    // Meeting statuses
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_POSTPONED = 'postponed';
    
    // Meeting platforms
    const PLATFORM_ZOOM = 'zoom';
    const PLATFORM_IN_PERSON = 'in_person';
    const PLATFORM_HYBRID = 'hybrid';
    
    // Level scopes
    const SCOPE_GLOBAL = 'global';
    const SCOPE_GODINA = 'godina';
    const SCOPE_GAMTA = 'gamta';
    const SCOPE_GURMU = 'gurmu';
    const SCOPE_CROSS_LEVEL = 'cross_level';

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'meeting_type',
        'level_scope',
        'scope_id',
        'start_datetime',
        'end_datetime',
        'timezone',
        'platform',
        'location',
        'zoom_meeting_id',
        'zoom_meeting_url',
        'zoom_password',
        'agenda',
        'recurring_pattern',
        'max_participants',
        'is_public',
        'requires_approval',
        'status',
        'meeting_minutes',
        'recording_url',
        'attachments',
        'created_by',
        'moderators',
        'tags'
    ];

    protected $casts = [
        'agenda' => 'json',
        'recurring_pattern' => 'json',
        'meeting_minutes' => 'json',
        'attachments' => 'json',
        'moderators' => 'json',
        'tags' => 'json'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new meeting
     */
    public function createMeeting(array $data): array
    {
        try {
            // Generate UUID
            $data['uuid'] = $this->generateUUID();
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Ensure JSON fields are properly encoded
            if (isset($data['agenda']) && is_array($data['agenda'])) {
                $data['agenda'] = json_encode($data['agenda']);
            }
            if (isset($data['recurring_pattern']) && is_array($data['recurring_pattern'])) {
                $data['recurring_pattern'] = json_encode($data['recurring_pattern']);
            }
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                $data['attachments'] = json_encode($data['attachments']);
            }
            if (isset($data['moderators']) && is_array($data['moderators'])) {
                $data['moderators'] = json_encode($data['moderators']);
            }
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = json_encode($data['tags']);
            }

            $meetingId = $this->create($data);
            
            if ($meetingId) {
                // Log meeting creation
                $this->logMeetingActivity($meetingId, 'created', 'Meeting created', $data['created_by']);
                
                return [
                    'success' => true,
                    'meeting_id' => $meetingId,
                    'message' => 'Meeting created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create meeting'];
            
        } catch (\Exception $e) {
            error_log("Meeting creation error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Meeting creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get meetings by hierarchical scope
     */
    public function getMeetingsByScope(string $scope, int $scopeId = null, array $filters = []): array
    {
        try {
            $query = "SELECT m.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name
                      FROM {$this->table} m
                      LEFT JOIN users u ON m.organized_by = u.id
                      WHERE m.level_scope = :scope";
            
            $params = ['scope' => $scope];
            
            if ($scopeId) {
                $query .= " AND m.scope_id = :scope_id";
                $params['scope_id'] = $scopeId;
            }
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND m.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['meeting_type'])) {
                $query .= " AND m.meeting_type = :meeting_type";
                $params['meeting_type'] = $filters['meeting_type'];
            }
            
            if (!empty($filters['platform'])) {
                $query .= " AND m.platform = :platform";
                $params['platform'] = $filters['platform'];
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(m.start_datetime) >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(m.start_datetime) <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }

            $query .= " ORDER BY m.start_datetime ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($meetings as &$meeting) {
                $meeting['agenda'] = json_decode($meeting['agenda'] ?? '[]', true);
                $meeting['recurring_pattern'] = json_decode($meeting['recurring_pattern'] ?? '{}', true);
                $meeting['meeting_minutes'] = json_decode($meeting['meeting_minutes'] ?? '{}', true);
                $meeting['attachments'] = json_decode($meeting['attachments'] ?? '[]', true);
                $meeting['moderators'] = json_decode($meeting['moderators'] ?? '[]', true);
                $meeting['tags'] = json_decode($meeting['tags'] ?? '[]', true);
            }
            
            return $meetings;
            
        } catch (\Exception $e) {
            error_log("Get meetings by scope error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming meetings for user
     */
    public function getUpcomingMeetingsForUser(int $userId, int $limit = 10): array
    {
        try {
            $query = "SELECT m.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name,
                             mp.status as participation_status
                      FROM {$this->table} m
                      LEFT JOIN users u ON m.organized_by = u.id
                      LEFT JOIN meeting_participants mp ON m.id = mp.meeting_id AND mp.user_id = :user_id
                      WHERE m.start_datetime >= NOW() 
                      AND m.status = 'scheduled'
                      AND (m.organized_by = :user_id 
                           OR mp.user_id = :user_id 
                           OR m.is_public = 1
                           OR JSON_CONTAINS(m.moderators, :user_id_json))
                      ORDER BY m.start_datetime ASC
                      LIMIT :limit";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id_json', json_encode([$userId]), PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($meetings as &$meeting) {
                $meeting['agenda'] = json_decode($meeting['agenda'] ?? '[]', true);
                $meeting['moderators'] = json_decode($meeting['moderators'] ?? '[]', true);
                $meeting['tags'] = json_decode($meeting['tags'] ?? '[]', true);
            }
            
            return $meetings;
            
        } catch (\Exception $e) {
            error_log("Get upcoming meetings for user error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update meeting status
     */
    public function updateMeetingStatus(int $meetingId, string $status, int $userId): array
    {
        try {
            $validStatuses = [
                self::STATUS_SCHEDULED,
                self::STATUS_IN_PROGRESS,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_POSTPONED
            ];
            
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->update($meetingId, $updateData);
            
            if ($result) {
                // Log status change
                $this->logMeetingActivity($meetingId, 'status_changed', "Status changed to: {$status}", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Meeting status updated successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to update meeting status'];
            
        } catch (\Exception $e) {
            error_log("Update meeting status error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Status update failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add participant to meeting
     */
    public function addParticipant(int $meetingId, int $userId, string $status = 'invited'): array
    {
        try {
            // Check if participant already exists
            $existingQuery = "SELECT id FROM meeting_participants 
                             WHERE meeting_id = :meeting_id AND user_id = :user_id";
            $stmt = $this->db->prepare($existingQuery);
            $stmt->execute(['meeting_id' => $meetingId, 'user_id' => $userId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'User is already a participant'];
            }
            
            // Add participant
            $insertQuery = "INSERT INTO meeting_participants 
                           (meeting_id, user_id, status, invited_at) 
                           VALUES (:meeting_id, :user_id, :status, :invited_at)";
            
            $stmt = $this->db->prepare($insertQuery);
            $result = $stmt->execute([
                'meeting_id' => $meetingId,
                'user_id' => $userId,
                'status' => $status,
                'invited_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Participant added successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to add participant'];
            
        } catch (\Exception $e) {
            error_log("Add participant error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to add participant: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update participant status
     */
    public function updateParticipantStatus(int $meetingId, int $userId, string $status): array
    {
        try {
            $validStatuses = ['invited', 'accepted', 'declined', 'attended', 'absent'];
            
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid participation status'];
            }
            
            $query = "UPDATE meeting_participants 
                     SET status = :status, updated_at = :updated_at
                     WHERE meeting_id = :meeting_id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'status' => $status,
                'meeting_id' => $meetingId,
                'user_id' => $userId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Participant status updated successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to update participant status'];
            
        } catch (\Exception $e) {
            error_log("Update participant status error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Status update failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get meeting participants
     */
    public function getMeetingParticipants(int $meetingId): array
    {
        try {
            $query = "SELECT mp.*, 
                             u.first_name, 
                             u.last_name, 
                             u.email,
                             u.profile_image
                      FROM meeting_participants mp
                      JOIN users u ON mp.user_id = u.id
                      WHERE mp.meeting_id = :meeting_id
                      ORDER BY mp.status ASC, u.first_name ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['meeting_id' => $meetingId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get meeting participants error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get one meeting record for detail views.
     */
    public function getMeetingById(int $meetingId): ?array
    {
        try {
            $ownerColumn = $this->db->columnExists($this->table, 'organized_by')
                ? 'organized_by'
                : ($this->db->columnExists($this->table, 'created_by') ? 'created_by' : null);

            $sql = 'SELECT m.*';
            if ($ownerColumn !== null) {
                $sql .= ", u.first_name as creator_first_name,
                            u.last_name as creator_last_name,
                            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as created_by_name";
            }

            $sql .= " FROM {$this->table} m";
            if ($ownerColumn !== null) {
                $sql .= " LEFT JOIN users u ON m.{$ownerColumn} = u.id";
            }

            $sql .= ' WHERE m.id = :meeting_id LIMIT 1';

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['meeting_id' => $meetingId]);
            $meeting = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if ($meeting === null) {
                return null;
            }

            foreach (['agenda', 'recurring_pattern', 'meeting_minutes', 'attachments', 'moderators', 'tags'] as $field) {
                $meeting[$field] = json_decode($meeting[$field] ?? '[]', true);
            }

            return $meeting;
        } catch (\Exception $e) {
            error_log('Get meeting by id error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get meeting activities for detail views.
     */
    public function getMeetingActivities(int $meetingId): array
    {
        return $this->getMeetingHistory($meetingId);
    }

    /**
     * Save meeting minutes
     */
    public function saveMeetingMinutes(int $meetingId, array $minutes, int $userId): array
    {
        try {
            $updateData = [
                'meeting_minutes' => json_encode($minutes),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->update($meetingId, $updateData);
            
            if ($result) {
                // Log minutes save
                $this->logMeetingActivity($meetingId, 'minutes_saved', 'Meeting minutes saved', $userId);
                
                return [
                    'success' => true,
                    'message' => 'Meeting minutes saved successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to save meeting minutes'];
            
        } catch (\Exception $e) {
            error_log("Save meeting minutes error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to save minutes: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get meeting statistics by scope
     */
    public function getMeetingStatistics(string $scope, int $scopeId = null): array
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total_meetings,
                        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_meetings,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_meetings,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_meetings,
                        SUM(CASE WHEN platform = 'zoom' THEN 1 ELSE 0 END) as virtual_meetings,
                        SUM(CASE WHEN platform = 'in_person' THEN 1 ELSE 0 END) as in_person_meetings,
                        SUM(CASE WHEN start_datetime >= CURDATE() THEN 1 ELSE 0 END) as upcoming_meetings
                      FROM {$this->table}
                      WHERE level_scope = :scope";
            
            $params = ['scope' => $scope];
            
            if ($scopeId) {
                $query .= " AND scope_id = :scope_id";
                $params['scope_id'] = $scopeId;
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate completion rate
            $stats['completion_rate'] = $stats['total_meetings'] > 0 
                ? round(($stats['completed_meetings'] / $stats['total_meetings']) * 100, 2) 
                : 0;
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Get meeting statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log meeting activity
     */
    public function logMeetingActivity(int $meetingId, $arg2, $arg3, $arg4 = null): void
    {
        try {
            if (is_int($arg2)) {
                $userId = $arg2;
                $action = (string) $arg3;
                $description = (string) ($arg4 ?? $arg3);
            } else {
                $action = (string) $arg2;
                $description = (string) $arg3;
                $userId = (int) $arg4;
            }

            if ($userId <= 0) {
                return;
            }

            $query = "INSERT INTO meeting_activities (meeting_id, user_id, action, description, created_at) 
                     VALUES (:meeting_id, :user_id, :action, :description, :created_at)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'meeting_id' => $meetingId,
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            error_log("Log meeting activity error: " . $e->getMessage());
        }
    }

    /**
     * Get meeting history
     */
    public function getMeetingHistory(int $meetingId): array
    {
        try {
            $query = "SELECT ma.*, 
                             u.first_name, 
                             u.last_name
                      FROM meeting_activities ma
                      LEFT JOIN users u ON ma.user_id = u.id
                      WHERE ma.meeting_id = :meeting_id
                      ORDER BY ma.created_at DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['meeting_id' => $meetingId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get meeting history error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search meetings
     */
    public function searchMeetings(array $criteria, int $userId): array
    {
        try {
            $query = "SELECT m.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name
                      FROM {$this->table} m
                      LEFT JOIN users u ON m.organized_by = u.id
                      LEFT JOIN meeting_participants mp ON m.id = mp.meeting_id
                      WHERE (m.organized_by = :user_id 
                             OR mp.user_id = :user_id 
                             OR m.is_public = 1
                             OR JSON_CONTAINS(m.moderators, :user_id_json))";
            
            $params = [
                'user_id' => $userId,
                'user_id_json' => json_encode([$userId])
            ];
            
            if (!empty($criteria['title'])) {
                $query .= " AND m.title LIKE :title";
                $params['title'] = '%' . $criteria['title'] . '%';
            }
            
            if (!empty($criteria['date_from'])) {
                $query .= " AND DATE(m.start_datetime) >= :date_from";
                $params['date_from'] = $criteria['date_from'];
            }
            
            if (!empty($criteria['date_to'])) {
                $query .= " AND DATE(m.start_datetime) <= :date_to";
                $params['date_to'] = $criteria['date_to'];
            }
            
            if (!empty($criteria['status'])) {
                $query .= " AND m.status = :status";
                $params['status'] = $criteria['status'];
            }
            
            if (!empty($criteria['platform'])) {
                $query .= " AND m.platform = :platform";
                $params['platform'] = $criteria['platform'];
            }
            
            $query .= " GROUP BY m.id ORDER BY m.start_datetime DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($meetings as &$meeting) {
                $meeting['agenda'] = json_decode($meeting['agenda'] ?? '[]', true);
                $meeting['moderators'] = json_decode($meeting['moderators'] ?? '[]', true);
                $meeting['tags'] = json_decode($meeting['tags'] ?? '[]', true);
            }
            
            return $meetings;
            
        } catch (\Exception $e) {
            error_log("Search meetings error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate UUID for meeting
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get meeting conflicts for user
     */
    public function getMeetingConflicts(int $userId, string $startDatetime, string $endDatetime, int $excludeMeetingId = null): array
    {
        try {
            $query = "SELECT m.*, mp.status as participation_status
                      FROM {$this->table} m
                      LEFT JOIN meeting_participants mp ON m.id = mp.meeting_id AND mp.user_id = :user_id
                      WHERE m.status = 'scheduled'
                      AND (m.organized_by = :user_id 
                           OR mp.user_id = :user_id 
                           OR JSON_CONTAINS(m.moderators, :user_id_json))
                      AND ((m.start_datetime <= :start_datetime AND m.end_datetime > :start_datetime)
                           OR (m.start_datetime < :end_datetime AND m.end_datetime >= :end_datetime)
                           OR (m.start_datetime >= :start_datetime AND m.end_datetime <= :end_datetime))";
            
            $params = [
                'user_id' => $userId,
                'user_id_json' => json_encode([$userId]),
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime
            ];
            
            if ($excludeMeetingId) {
                $query .= " AND m.id != :exclude_meeting_id";
                $params['exclude_meeting_id'] = $excludeMeetingId;
            }
            
            $query .= " ORDER BY m.start_datetime ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get meeting conflicts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recurring meeting instances
     */
    public function getRecurringInstances(int $meetingId, string $startDate, string $endDate): array
    {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE (id = :meeting_id OR parent_meeting_id = :meeting_id)
                     AND DATE(start_datetime) BETWEEN :start_date AND :end_date
                     ORDER BY start_datetime ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'meeting_id' => $meetingId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($meetings as &$meeting) {
                $meeting['agenda'] = json_decode($meeting['agenda'] ?? '[]', true);
                $meeting['recurring_pattern'] = json_decode($meeting['recurring_pattern'] ?? '{}', true);
                $meeting['moderators'] = json_decode($meeting['moderators'] ?? '[]', true);
            }
            
            return $meetings;
            
        } catch (\Exception $e) {
            error_log("Get recurring instances error: " . $e->getMessage());
            return [];
        }
    }
}