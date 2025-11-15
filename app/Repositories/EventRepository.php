<?php

namespace App\Repositories;

use PDO;

/**
 * EventRepository - Event data access layer
 * 
 * Handles all database operations related to events including
 * registration, participation tracking, and event management.
 * 
 * @package App\Repositories
 * @version 1.0.0
 */
class EventRepository extends BaseRepository
{
    protected string $table = 'events';
    protected array $fillable = [
        'uuid', 'title', 'description', 'event_type', 'level_scope', 'scope_id',
        'start_datetime', 'end_datetime', 'timezone', 'venue_name', 'venue_address',
        'is_virtual', 'virtual_platform', 'virtual_link', 'is_paid_event', 'ticket_price',
        'currency', 'max_attendees', 'registration_required', 'registration_deadline',
        'agenda', 'requirements', 'featured_image', 'status', 'created_by'
    ];
    protected array $casts = [
        'is_virtual' => 'boolean',
        'is_paid_event' => 'boolean',
        'registration_required' => 'boolean',
        'ticket_price' => 'float',
        'max_attendees' => 'int',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'registration_deadline' => 'datetime',
        'agenda' => 'json'
    ];

    /**
     * Get events by hierarchy level
     */
    public function getByHierarchyLevel(string $level, int $scopeId, array $filters = []): array
    {
        $this->resetQuery();
        
        if ($level !== 'global') {
            $this->where('level_scope', $level)
                 ->where('scope_id', $scopeId);
        }
        
        return $this->applyFilters($filters)->get();
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents(string $level = 'global', ?int $scopeId = null, int $limit = 10): array
    {
        $this->resetQuery();
        
        $this->where('start_datetime', '>', date('Y-m-d H:i:s'))
             ->where('status', 'published')
             ->orderBy('start_datetime')
             ->limit($limit);
             
        if ($level !== 'global' && $scopeId) {
            $this->where('level_scope', $level)
                 ->where('scope_id', $scopeId);
        }
        
        return $this->get();
    }

    /**
     * Get events for user (registered or created)
     */
    public function getUserEvents(int $userId, array $filters = []): array
    {
        $sql = "
            SELECT DISTINCT e.*, 
                   CASE WHEN e.created_by = ? THEN 'organizer' ELSE 'participant' END as user_role,
                   er.status as registration_status,
                   er.registered_at
            FROM {$this->table} e
            LEFT JOIN event_registrations er ON e.id = er.event_id AND er.user_id = ?
            WHERE (e.created_by = ? OR er.user_id = ?) AND e.deleted_at IS NULL
        ";
        
        $bindings = [$userId, $userId, $userId, $userId];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $bindings[] = $filters['status'];
        }
        
        if (!empty($filters['event_type'])) {
            $sql .= " AND e.event_type = ?";
            $bindings[] = $filters['event_type'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND e.start_datetime >= ?";
            $bindings[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND e.start_datetime <= ?";
            $bindings[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY e.start_datetime DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cast attributes for each event
        return array_map([$this, 'castAttributes'], $events);
    }

    /**
     * Get event with registration details
     */
    public function getEventWithRegistrations(int $eventId): ?array
    {
        $event = $this->find($eventId);
        if (!$event) {
            return null;
        }
        
        // Get registration statistics
        $statsSql = "
            SELECT 
                COUNT(*) as total_registrations,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_registrations,
                SUM(CASE WHEN status = 'attended' THEN 1 ELSE 0 END) as attended_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
            FROM event_registrations 
            WHERE event_id = ?
        ";
        
        $stmt = $this->db->prepare($statsSql);
        $stmt->execute([$eventId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $event['registration_stats'] = $stats;
        
        // Get organizer details
        $organizerSql = "SELECT id, first_name, last_name, email FROM users WHERE id = ?";
        $stmt = $this->db->prepare($organizerSql);
        $stmt->execute([$event['created_by']]);
        $event['organizer'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $event;
    }

    /**
     * Register user for event
     */
    public function registerUser(int $eventId, int $userId, array $registrationData = []): bool
    {
        $sql = "
            INSERT INTO event_registrations (
                event_id, user_id, registration_type, status, special_requirements,
                dietary_preferences, emergency_contact, registered_at
            ) VALUES (?, ?, ?, 'confirmed', ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                status = 'confirmed',
                registration_type = VALUES(registration_type),
                special_requirements = VALUES(special_requirements),
                dietary_preferences = VALUES(dietary_preferences),
                emergency_contact = VALUES(emergency_contact)
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $eventId,
            $userId,
            $registrationData['registration_type'] ?? 'participant',
            $registrationData['special_requirements'] ?? null,
            !empty($registrationData['dietary_preferences']) ? json_encode($registrationData['dietary_preferences']) : null,
            !empty($registrationData['emergency_contact']) ? json_encode($registrationData['emergency_contact']) : null,
            date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update registration status
     */
    public function updateRegistrationStatus(int $eventId, int $userId, string $status): bool
    {
        $sql = "
            UPDATE event_registrations 
            SET status = ?, updated_at = ?
            WHERE event_id = ? AND user_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $status,
            date('Y-m-d H:i:s'),
            $eventId,
            $userId
        ]);
    }

    /**
     * Cancel user registration
     */
    public function cancelRegistration(int $eventId, int $userId, string $reason = null): bool
    {
        $sql = "
            UPDATE event_registrations 
            SET status = 'cancelled', cancellation_reason = ?, cancelled_at = ?
            WHERE event_id = ? AND user_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $reason,
            date('Y-m-d H:i:s'),
            $eventId,
            $userId
        ]);
    }

    /**
     * Mark attendance
     */
    public function markAttendance(int $eventId, int $userId, bool $attended = true): bool
    {
        $status = $attended ? 'attended' : 'no_show';
        
        $sql = "
            UPDATE event_registrations 
            SET status = ?, attendance_marked_at = ?
            WHERE event_id = ? AND user_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $status,
            date('Y-m-d H:i:s'),
            $eventId,
            $userId
        ]);
    }

    /**
     * Get event registrations
     */
    public function getEventRegistrations(int $eventId, array $filters = []): array
    {
        $sql = "
            SELECT er.*, u.first_name, u.last_name, u.email, u.phone
            FROM event_registrations er
            JOIN users u ON er.user_id = u.id
            WHERE er.event_id = ?
        ";
        
        $bindings = [$eventId];
        
        // Apply status filter
        if (!empty($filters['status'])) {
            $sql .= " AND er.status = ?";
            $bindings[] = $filters['status'];
        }
        
        // Apply registration type filter
        if (!empty($filters['registration_type'])) {
            $sql .= " AND er.registration_type = ?";
            $bindings[] = $filters['registration_type'];
        }
        
        $sql .= " ORDER BY er.registered_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get event statistics
     */
    public function getStatistics(string $level = 'global', ?int $scopeId = null): array
    {
        $baseWhere = "WHERE deleted_at IS NULL";
        if ($level !== 'global' && $scopeId) {
            $baseWhere .= " AND level_scope = '{$level}' AND scope_id = {$scopeId}";
        }
        
        // Total events
        $totalSql = "SELECT COUNT(*) FROM {$this->table} {$baseWhere}";
        $stmt = $this->db->prepare($totalSql);
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Events by status
        $statusSql = "SELECT status, COUNT(*) as count FROM {$this->table} {$baseWhere} GROUP BY status";
        $stmt = $this->db->prepare($statusSql);
        $stmt->execute();
        $statusStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Events by type
        $typeSql = "SELECT event_type, COUNT(*) as count FROM {$this->table} {$baseWhere} GROUP BY event_type";
        $stmt = $this->db->prepare($typeSql);
        $stmt->execute();
        $typeStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Average attendance rate
        $attendanceSql = "
            SELECT AVG(attendance_rate) as avg_attendance_rate
            FROM (
                SELECT e.id,
                       COUNT(er.user_id) as total_registrations,
                       SUM(CASE WHEN er.status = 'attended' THEN 1 ELSE 0 END) as attended_count,
                       CASE 
                           WHEN COUNT(er.user_id) > 0 
                           THEN (SUM(CASE WHEN er.status = 'attended' THEN 1 ELSE 0 END) / COUNT(er.user_id)) * 100
                           ELSE 0
                       END as attendance_rate
                FROM {$this->table} e
                LEFT JOIN event_registrations er ON e.id = er.event_id
                {$baseWhere}
                AND e.status = 'completed'
                GROUP BY e.id
            ) attendance_stats
        ";
        $stmt = $this->db->prepare($attendanceSql);
        $stmt->execute();
        $avgAttendanceRate = $stmt->fetchColumn();
        
        return [
            'total' => $total,
            'by_status' => $statusStats,
            'by_type' => $typeStats,
            'average_attendance_rate' => round($avgAttendanceRate, 2)
        ];
    }

    /**
     * Get calendar events for a date range
     */
    public function getCalendarEvents(string $startDate, string $endDate, string $level = 'global', ?int $scopeId = null): array
    {
        $this->resetQuery();
        
        $this->whereBetween('start_datetime', $startDate, $endDate)
             ->where('status', 'published');
             
        if ($level !== 'global' && $scopeId) {
            $this->where('level_scope', $level)
                 ->where('scope_id', $scopeId);
        }
        
        $events = $this->get(['id', 'title', 'start_datetime', 'end_datetime', 'event_type', 'is_virtual']);
        
        // Format for calendar
        return array_map(function($event) {
            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_datetime'],
                'end' => $event['end_datetime'],
                'backgroundColor' => $this->getTypeColor($event['event_type']),
                'textColor' => '#fff',
                'url' => "/events/{$event['id']}"
            ];
        }, $events);
    }

    /**
     * Get type color for calendar display
     */
    private function getTypeColor(string $eventType): string
    {
        $colors = [
            'cultural' => '#e74c3c',
            'educational' => '#3498db',
            'fundraising' => '#f39c12',
            'social' => '#2ecc71',
            'political' => '#9b59b6',
            'religious' => '#34495e',
            'sports' => '#e67e22',
            'conference' => '#1abc9c'
        ];
        
        return $colors[$eventType] ?? '#6c757d';
    }

    /**
     * Apply common filters
     */
    private function applyFilters(array $filters): self
    {
        if (!empty($filters['status'])) {
            $this->where('status', $filters['status']);
        }
        
        if (!empty($filters['event_type'])) {
            $this->where('event_type', $filters['event_type']);
        }
        
        if (!empty($filters['is_virtual'])) {
            $this->where('is_virtual', $filters['is_virtual'] ? 1 : 0);
        }
        
        if (!empty($filters['is_paid_event'])) {
            $this->where('is_paid_event', $filters['is_paid_event'] ? 1 : 0);
        }
        
        if (!empty($filters['date_from'])) {
            $this->where('start_datetime', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $this->where('start_datetime', '<=', $filters['date_to'] . ' 23:59:59');
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $this->whereLike('title', $search)
                 ->orWhereLike('description', $search);
        }
        
        return $this->orderBy('start_datetime', 'desc');
    }

    /**
     * Get popular events (by registration count)
     */
    public function getPopularEvents(int $limit = 10, string $level = 'global', ?int $scopeId = null): array
    {
        $whereClause = "WHERE e.status = 'published' AND e.deleted_at IS NULL";
        
        if ($level !== 'global' && $scopeId) {
            $whereClause .= " AND e.level_scope = '{$level}' AND e.scope_id = {$scopeId}";
        }
        
        $sql = "
            SELECT e.*, COUNT(er.user_id) as registration_count
            FROM {$this->table} e
            LEFT JOIN event_registrations er ON e.id = er.event_id AND er.status IN ('confirmed', 'attended')
            {$whereClause}
            GROUP BY e.id
            ORDER BY registration_count DESC
            LIMIT {$limit}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cast attributes for each event
        return array_map([$this, 'castAttributes'], $events);
    }

    /**
     * Check if user is registered for event
     */
    public function isUserRegistered(int $eventId, int $userId): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM event_registrations 
            WHERE event_id = ? AND user_id = ? AND status IN ('confirmed', 'attended')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$eventId, $userId]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get user's registration status for event
     */
    public function getUserRegistrationStatus(int $eventId, int $userId): ?string
    {
        $sql = "
            SELECT status 
            FROM event_registrations 
            WHERE event_id = ? AND user_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$eventId, $userId]);
        
        return $stmt->fetchColumn() ?: null;
    }
}