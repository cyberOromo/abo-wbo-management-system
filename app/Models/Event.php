<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Event Model
 * Handles organizational event management with participation tracking
 */
class Event extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    
    // Event types
    const TYPE_CULTURAL = 'cultural';
    const TYPE_EDUCATIONAL = 'educational';
    const TYPE_FUNDRAISING = 'fundraising';
    const TYPE_SOCIAL = 'social';
    const TYPE_POLITICAL = 'political';
    const TYPE_MEMORIAL = 'memorial';
    const TYPE_CELEBRATION = 'celebration';
    const TYPE_CONFERENCE = 'conference';
    
    // Event statuses
    const STATUS_PLANNING = 'planning';
    const STATUS_OPEN_REGISTRATION = 'open_registration';
    const STATUS_REGISTRATION_CLOSED = 'registration_closed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_POSTPONED = 'postponed';
    
    // Registration types
    const REGISTRATION_OPEN = 'open';
    const REGISTRATION_APPROVAL_REQUIRED = 'approval_required';
    const REGISTRATION_INVITATION_ONLY = 'invitation_only';
    const REGISTRATION_CLOSED = 'closed';
    
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
        'event_type',
        'level_scope',
        'scope_id',
        'start_datetime',
        'end_datetime',
        'timezone',
        'venue_name',
        'venue_address',
        'venue_city',
        'venue_country',
        'is_virtual',
        'virtual_link',
        'registration_type',
        'registration_start',
        'registration_end',
        'max_participants',
        'min_participants',
        'registration_fee',
        'currency',
        'requires_payment',
        'agenda',
        'speakers',
        'sponsors',
        'social_media_links',
        'banner_image',
        'gallery_images',
        'requirements',
        'what_to_bring',
        'contact_email',
        'contact_phone',
        'status',
        'tags',
        'custom_fields',
        'created_by',
        'organizers'
    ];

    protected $casts = [
        'agenda' => 'json',
        'speakers' => 'json',
        'sponsors' => 'json',
        'social_media_links' => 'json',
        'gallery_images' => 'json',
        'requirements' => 'json',
        'what_to_bring' => 'json',
        'tags' => 'json',
        'custom_fields' => 'json',
        'organizers' => 'json'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new event
     */
    public function createEvent(array $data): array
    {
        try {
            // Generate UUID
            $data['uuid'] = $this->generateUUID();
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Ensure JSON fields are properly encoded
            $jsonFields = ['agenda', 'speakers', 'sponsors', 'social_media_links', 
                          'gallery_images', 'requirements', 'what_to_bring', 
                          'tags', 'custom_fields', 'organizers'];
            
            foreach ($jsonFields as $field) {
                if (isset($data[$field]) && is_array($data[$field])) {
                    $data[$field] = json_encode($data[$field]);
                }
            }

            $eventId = $this->create($data);
            
            if ($eventId) {
                // Log event creation
                $this->logEventActivity($eventId, 'created', 'Event created', $data['created_by']);
                
                return [
                    'success' => true,
                    'event_id' => $eventId,
                    'message' => 'Event created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create event'];
            
        } catch (\Exception $e) {
            error_log("Event creation error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Event creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get events by hierarchical scope
     */
    public function getEventsByScope(string $scope, int $scopeId = null, array $filters = []): array
    {
        try {
            $query = "SELECT e.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name,
                             COUNT(ep.id) as total_registrations,
                             SUM(CASE WHEN ep.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_registrations
                      FROM {$this->table} e
                      LEFT JOIN users u ON e.organized_by = u.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.level_scope = :scope";
            
            $params = ['scope' => $scope];
            
            if ($scopeId) {
                $query .= " AND e.scope_id = :scope_id";
                $params['scope_id'] = $scopeId;
            }
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND e.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['event_type'])) {
                $query .= " AND e.event_type = :event_type";
                $params['event_type'] = $filters['event_type'];
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(e.start_datetime) >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(e.start_datetime) <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            if (!empty($filters['is_virtual'])) {
                $query .= " AND e.is_virtual = :is_virtual";
                $params['is_virtual'] = $filters['is_virtual'];
            }

            $query .= " GROUP BY e.id ORDER BY e.start_datetime ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($events as &$event) {
                $this->decodeEventJsonFields($event);
            }
            
            return $events;
            
        } catch (\Exception $e) {
            error_log("Get events by scope error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming events for user
     */
    public function getUpcomingEventsForUser(int $userId, int $limit = 10): array
    {
        try {
            $query = "SELECT e.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name,
                             ep.status as participation_status,
                             ep.registered_at
                      FROM {$this->table} e
                      LEFT JOIN users u ON e.organized_by = u.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id AND ep.user_id = :user_id
                      WHERE e.start_datetime >= NOW() 
                      AND e.status IN ('open_registration', 'registration_closed', 'in_progress')
                      ORDER BY e.start_datetime ASC
                      LIMIT :limit";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($events as &$event) {
                $this->decodeEventJsonFields($event);
            }
            
            return $events;
            
        } catch (\Exception $e) {
            error_log("Get upcoming events for user error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Register user for event
     */
    public function registerUser(int $eventId, int $userId, array $registrationData = []): array
    {
        try {
            // Check if event exists and registration is open
            $event = $this->find($eventId);
            if (!$event) {
                return ['success' => false, 'message' => 'Event not found'];
            }
            
            if (!$this->canRegisterForEvent($event)) {
                return ['success' => false, 'message' => 'Registration is not available for this event'];
            }
            
            // Check if user is already registered
            $existingQuery = "SELECT id FROM event_participants 
                             WHERE event_id = :event_id AND user_id = :user_id";
            $stmt = $this->db->prepare($existingQuery);
            $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'User is already registered for this event'];
            }
            
            // Check capacity
            if ($event['max_participants'] > 0) {
                $currentCount = $this->getConfirmedParticipantsCount($eventId);
                if ($currentCount >= $event['max_participants']) {
                    return ['success' => false, 'message' => 'Event is full'];
                }
            }
            
            // Determine initial status based on registration type
            $status = $this->getInitialRegistrationStatus($event);
            
            // Register user
            $insertQuery = "INSERT INTO event_participants 
                           (event_id, user_id, status, registration_data, registered_at) 
                           VALUES (:event_id, :user_id, :status, :registration_data, :registered_at)";
            
            $stmt = $this->db->prepare($insertQuery);
            $result = $stmt->execute([
                'event_id' => $eventId,
                'user_id' => $userId,
                'status' => $status,
                'registration_data' => json_encode($registrationData),
                'registered_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                // Log registration
                $this->logEventActivity($eventId, 'user_registered', 'User registered for event', $userId);
                
                return [
                    'success' => true,
                    'status' => $status,
                    'message' => 'Registration successful'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to register for event'];
            
        } catch (\Exception $e) {
            error_log("Register user for event error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update participant status
     */
    public function updateParticipantStatus(int $eventId, int $userId, string $status): array
    {
        try {
            $validStatuses = ['registered', 'confirmed', 'cancelled', 'attended', 'no_show', 'waitlisted'];
            
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid participation status'];
            }
            
            $query = "UPDATE event_participants 
                     SET status = :status, updated_at = :updated_at
                     WHERE event_id = :event_id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'status' => $status,
                'event_id' => $eventId,
                'user_id' => $userId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                // Log status change
                $this->logEventActivity($eventId, 'status_changed', "Participant status changed to: {$status}", $userId);
                
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
     * Get event participants
     */
    public function getEventParticipants(int $eventId, string $status = null): array
    {
        try {
            $query = "SELECT ep.*, 
                             u.first_name, 
                             u.last_name, 
                             u.email,
                             u.phone,
                             u.profile_image
                      FROM event_participants ep
                      JOIN users u ON ep.user_id = u.id
                      WHERE ep.event_id = :event_id";
            
            $params = ['event_id' => $eventId];
            
            if ($status) {
                $query .= " AND ep.status = :status";
                $params['status'] = $status;
            }
            
            $query .= " ORDER BY ep.registered_at ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode registration data
            foreach ($participants as &$participant) {
                $participant['registration_data'] = json_decode($participant['registration_data'] ?? '{}', true);
            }
            
            return $participants;
            
        } catch (\Exception $e) {
            error_log("Get event participants error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get event statistics by scope
     */
    public function getEventStatistics(string $scope, int $scopeId = null): array
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total_events,
                        SUM(CASE WHEN status = 'planning' THEN 1 ELSE 0 END) as planning_events,
                        SUM(CASE WHEN status = 'open_registration' THEN 1 ELSE 0 END) as open_registration_events,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_events,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_events,
                        SUM(CASE WHEN is_virtual = 1 THEN 1 ELSE 0 END) as virtual_events,
                        SUM(CASE WHEN start_datetime >= CURDATE() THEN 1 ELSE 0 END) as upcoming_events,
                        AVG(CASE WHEN max_participants > 0 THEN max_participants ELSE NULL END) as avg_capacity
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
            
            // Get participation statistics
            $participationQuery = "SELECT 
                                     COUNT(DISTINCT ep.user_id) as unique_participants,
                                     COUNT(ep.id) as total_registrations,
                                     SUM(CASE WHEN ep.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_registrations,
                                     SUM(CASE WHEN ep.status = 'attended' THEN 1 ELSE 0 END) as attended_count
                                   FROM event_participants ep
                                   JOIN events e ON ep.event_id = e.id
                                   WHERE e.level_scope = :scope";
            
            if ($scopeId) {
                $participationQuery .= " AND e.scope_id = :scope_id";
            }
            
            $stmt = $this->db->prepare($participationQuery);
            $stmt->execute($params);
            
            $participationStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Merge statistics
            $stats = array_merge($stats, $participationStats);
            
            // Calculate attendance rate
            $stats['attendance_rate'] = $stats['confirmed_registrations'] > 0 
                ? round(($stats['attended_count'] / $stats['confirmed_registrations']) * 100, 2) 
                : 0;
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Get event statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search events
     */
    public function searchEvents(array $criteria): array
    {
        try {
            $query = "SELECT e.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name,
                             COUNT(ep.id) as total_registrations
                      FROM {$this->table} e
                      LEFT JOIN users u ON e.organized_by = u.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE 1=1";
            
            $params = [];
            
            if (!empty($criteria['title'])) {
                $query .= " AND e.title LIKE :title";
                $params['title'] = '%' . $criteria['title'] . '%';
            }
            
            if (!empty($criteria['event_type'])) {
                $query .= " AND e.event_type = :event_type";
                $params['event_type'] = $criteria['event_type'];
            }
            
            if (!empty($criteria['location'])) {
                $query .= " AND (e.venue_city LIKE :location OR e.venue_country LIKE :location)";
                $params['location'] = '%' . $criteria['location'] . '%';
            }
            
            if (!empty($criteria['date_from'])) {
                $query .= " AND DATE(e.start_datetime) >= :date_from";
                $params['date_from'] = $criteria['date_from'];
            }
            
            if (!empty($criteria['date_to'])) {
                $query .= " AND DATE(e.start_datetime) <= :date_to";
                $params['date_to'] = $criteria['date_to'];
            }
            
            if (!empty($criteria['status'])) {
                $query .= " AND e.status = :status";
                $params['status'] = $criteria['status'];
            }
            
            if (!empty($criteria['is_virtual'])) {
                $query .= " AND e.is_virtual = :is_virtual";
                $params['is_virtual'] = $criteria['is_virtual'];
            }
            
            if (!empty($criteria['tags'])) {
                $query .= " AND JSON_CONTAINS(e.tags, :tags)";
                $params['tags'] = json_encode($criteria['tags']);
            }
            
            $query .= " GROUP BY e.id ORDER BY e.start_datetime ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($events as &$event) {
                $this->decodeEventJsonFields($event);
            }
            
            return $events;
            
        } catch (\Exception $e) {
            error_log("Search events error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user can register for event
     */
    private function canRegisterForEvent(array $event): bool
    {
        $now = date('Y-m-d H:i:s');
        
        // Check if registration is open
        if ($event['registration_type'] === self::REGISTRATION_CLOSED) {
            return false;
        }
        
        // Check registration period
        if ($event['registration_start'] && $now < $event['registration_start']) {
            return false;
        }
        
        if ($event['registration_end'] && $now > $event['registration_end']) {
            return false;
        }
        
        // Check event status
        $allowedStatuses = [self::STATUS_OPEN_REGISTRATION, self::STATUS_PLANNING];
        if (!in_array($event['status'], $allowedStatuses)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get initial registration status based on event settings
     */
    private function getInitialRegistrationStatus(array $event): string
    {
        switch ($event['registration_type']) {
            case self::REGISTRATION_APPROVAL_REQUIRED:
                return 'registered'; // Requires approval
                
            case self::REGISTRATION_INVITATION_ONLY:
                return 'registered'; // Requires invitation
                
            case self::REGISTRATION_OPEN:
            default:
                return 'confirmed'; // Auto-confirmed
        }
    }

    /**
     * Get confirmed participants count
     */
    private function getConfirmedParticipantsCount(int $eventId): int
    {
        try {
            $query = "SELECT COUNT(*) FROM event_participants 
                     WHERE event_id = :event_id AND status = 'confirmed'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['event_id' => $eventId]);
            
            return (int) $stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Get confirmed participants count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Decode JSON fields in event array
     */
    private function decodeEventJsonFields(array &$event): void
    {
        $jsonFields = ['agenda', 'speakers', 'sponsors', 'social_media_links', 
                      'gallery_images', 'requirements', 'what_to_bring', 
                      'tags', 'custom_fields', 'organizers'];
        
        foreach ($jsonFields as $field) {
            $event[$field] = json_decode($event[$field] ?? '[]', true);
        }
    }

    /**
     * Log event activity
     */
    private function logEventActivity(int $eventId, string $action, string $description, int $userId): void
    {
        try {
            $query = "INSERT INTO event_activities (event_id, user_id, action, description, created_at) 
                     VALUES (:event_id, :user_id, :action, :description, :created_at)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'event_id' => $eventId,
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            error_log("Log event activity error: " . $e->getMessage());
        }
    }

    /**
     * Generate UUID for event
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
     * Get popular events
     */
    public function getPopularEvents(int $limit = 10): array
    {
        try {
            $query = "SELECT e.*, 
                             u.first_name as creator_first_name, 
                             u.last_name as creator_last_name,
                             COUNT(ep.id) as registration_count
                      FROM {$this->table} e
                      LEFT JOIN users u ON e.organized_by = u.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.status IN ('open_registration', 'registration_closed', 'in_progress')
                      AND e.start_datetime >= NOW()
                      GROUP BY e.id
                      ORDER BY registration_count DESC, e.start_datetime ASC
                      LIMIT :limit";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($events as &$event) {
                $this->decodeEventJsonFields($event);
            }
            
            return $events;
            
        } catch (\Exception $e) {
            error_log("Get popular events error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get event attendance summary
     */
    public function getEventAttendanceSummary(int $eventId): array
    {
        try {
            $query = "SELECT 
                        status,
                        COUNT(*) as count
                      FROM event_participants 
                      WHERE event_id = :event_id
                      GROUP BY status
                      ORDER BY 
                        CASE status 
                            WHEN 'confirmed' THEN 1
                            WHEN 'registered' THEN 2
                            WHEN 'attended' THEN 3
                            WHEN 'waitlisted' THEN 4
                            WHEN 'cancelled' THEN 5
                            WHEN 'no_show' THEN 6
                            ELSE 7
                        END";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['event_id' => $eventId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get event attendance summary error: " . $e->getMessage());
            return [];
        }
    }
}