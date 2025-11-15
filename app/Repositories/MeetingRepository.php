<?php

namespace App\Repositories;

use PDO;

/**
 * MeetingRepository - Meeting data access layer
 * 
 * Handles all database operations related to meetings including
 * scheduling, attendance, and meeting management across hierarchy levels.
 * 
 * @package App\Repositories
 * @version 1.0.0
 */
class MeetingRepository extends BaseRepository
{
    protected string $table = 'meetings';
    protected array $fillable = [
        'uuid', 'title', 'description', 'meeting_type', 'level_scope', 'scope_id',
        'start_datetime', 'end_datetime', 'timezone', 'location', 'is_virtual',
        'zoom_meeting_id', 'zoom_join_url', 'zoom_password', 'agenda', 'minutes',
        'recording_url', 'status', 'max_attendees', 'registration_required',
        'registration_deadline', 'created_by'
    ];
    protected array $casts = [
        'is_virtual' => 'boolean',
        'registration_required' => 'boolean',
        'max_attendees' => 'int',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'registration_deadline' => 'datetime'
    ];

    /**
     * Get meetings by hierarchy level
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
     * Get upcoming meetings
     */
    public function getUpcomingMeetings(string $level = 'global', ?int $scopeId = null, int $limit = 10): array
    {
        $this->resetQuery();
        
        $this->where('start_datetime', '>', date('Y-m-d H:i:s'))
             ->whereIn('status', ['scheduled', 'ongoing'])
             ->orderBy('start_datetime')
             ->limit($limit);
             
        if ($level !== 'global' && $scopeId) {
            $this->where('level_scope', $level)
                 ->where('scope_id', $scopeId);
        }
        
        return $this->get();
    }

    /**
     * Get meetings for user (organized or attending)
     */
    public function getUserMeetings(int $userId, array $filters = []): array
    {
        $sql = "
            SELECT DISTINCT m.*, 
                   CASE WHEN m.created_by = ? THEN 'organizer' ELSE 'attendee' END as user_role,
                   ma.status as attendance_status
            FROM {$this->table} m
            LEFT JOIN meeting_attendees ma ON m.id = ma.meeting_id AND ma.user_id = ?
            WHERE (m.created_by = ? OR ma.user_id = ?) AND m.deleted_at IS NULL
        ";
        
        $bindings = [$userId, $userId, $userId, $userId];
        
        // Apply date filters
        if (!empty($filters['date_from'])) {
            $sql .= " AND m.start_datetime >= ?";
            $bindings[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND m.start_datetime <= ?";
            $bindings[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Apply status filter
        if (!empty($filters['status'])) {
            $sql .= " AND m.status = ?";
            $bindings[] = $filters['status'];
        }
        
        $sql .= " ORDER BY m.start_datetime DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cast attributes for each meeting
        return array_map([$this, 'castAttributes'], $meetings);
    }

    /**
     * Get meeting with attendees
     */
    public function getMeetingWithAttendees(int $meetingId): ?array
    {
        $meeting = $this->find($meetingId);
        if (!$meeting) {
            return null;
        }
        
        // Get attendees
        $sql = "
            SELECT u.id, u.first_name, u.last_name, u.email, 
                   ma.status, ma.joined_at, ma.left_at, ma.role
            FROM meeting_attendees ma
            JOIN users u ON ma.user_id = u.id
            WHERE ma.meeting_id = ?
            ORDER BY ma.role, u.first_name, u.last_name
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$meetingId]);
        $meeting['attendees'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get organizer details
        $organizerSql = "SELECT id, first_name, last_name, email FROM users WHERE id = ?";
        $stmt = $this->db->prepare($organizerSql);
        $stmt->execute([$meeting['created_by']]);
        $meeting['organizer'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $meeting;
    }

    /**
     * Add attendee to meeting
     */
    public function addAttendee(int $meetingId, int $userId, string $role = 'attendee', string $status = 'invited'): bool
    {
        $sql = "
            INSERT INTO meeting_attendees (meeting_id, user_id, role, status, invited_at)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE role = VALUES(role), status = VALUES(status)
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $meetingId,
            $userId,
            $role,
            $status,
            date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update attendee status
     */
    public function updateAttendeeStatus(int $meetingId, int $userId, string $status): bool
    {
        $sql = "
            UPDATE meeting_attendees 
            SET status = ?, response_at = ?
            WHERE meeting_id = ? AND user_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $status,
            date('Y-m-d H:i:s'),
            $meetingId,
            $userId
        ]);
    }

    /**
     * Record meeting join/leave
     */
    public function recordAttendance(int $meetingId, int $userId, string $action): bool
    {
        $field = $action === 'join' ? 'joined_at' : 'left_at';
        
        $sql = "
            UPDATE meeting_attendees 
            SET {$field} = ?
            WHERE meeting_id = ? AND user_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            date('Y-m-d H:i:s'),
            $meetingId,
            $userId
        ]);
    }

    /**
     * Save meeting minutes
     */
    public function saveMinutes(int $meetingId, string $minutes, array $actionItems = []): bool
    {
        $this->beginTransaction();
        
        try {
            // Update meeting with minutes
            $this->update($meetingId, ['minutes' => $minutes]);
            
            // Save action items
            if (!empty($actionItems)) {
                $this->saveActionItems($meetingId, $actionItems);
            }
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Save meeting action items
     */
    private function saveActionItems(int $meetingId, array $actionItems): void
    {
        // Delete existing action items
        $deleteSql = "DELETE FROM meeting_action_items WHERE meeting_id = ?";
        $stmt = $this->db->prepare($deleteSql);
        $stmt->execute([$meetingId]);
        
        // Insert new action items
        $insertSql = "
            INSERT INTO meeting_action_items (meeting_id, description, assigned_to, due_date, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', ?)
        ";
        
        $stmt = $this->db->prepare($insertSql);
        
        foreach ($actionItems as $item) {
            $stmt->execute([
                $meetingId,
                $item['description'],
                $item['assigned_to'] ?? null,
                $item['due_date'] ?? null,
                date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get meeting statistics
     */
    public function getStatistics(string $level = 'global', ?int $scopeId = null): array
    {
        $baseWhere = "WHERE deleted_at IS NULL";
        if ($level !== 'global' && $scopeId) {
            $baseWhere .= " AND level_scope = '{$level}' AND scope_id = {$scopeId}";
        }
        
        // Total meetings
        $totalSql = "SELECT COUNT(*) FROM {$this->table} {$baseWhere}";
        $stmt = $this->db->prepare($totalSql);
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Meetings by status
        $statusSql = "SELECT status, COUNT(*) as count FROM {$this->table} {$baseWhere} GROUP BY status";
        $stmt = $this->db->prepare($statusSql);
        $stmt->execute();
        $statusStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Meetings by type
        $typeSql = "SELECT meeting_type, COUNT(*) as count FROM {$this->table} {$baseWhere} GROUP BY meeting_type";
        $stmt = $this->db->prepare($typeSql);
        $stmt->execute();
        $typeStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Average attendance
        $attendanceSql = "
            SELECT AVG(attendee_count) as avg_attendance
            FROM (
                SELECT m.id, COUNT(ma.user_id) as attendee_count
                FROM {$this->table} m
                LEFT JOIN meeting_attendees ma ON m.id = ma.meeting_id AND ma.status = 'attended'
                {$baseWhere}
                GROUP BY m.id
            ) attendance_stats
        ";
        $stmt = $this->db->prepare($attendanceSql);
        $stmt->execute();
        $avgAttendance = $stmt->fetchColumn();
        
        return [
            'total' => $total,
            'by_status' => $statusStats,
            'by_type' => $typeStats,
            'average_attendance' => round($avgAttendance, 2)
        ];
    }

    /**
     * Apply common filters
     */
    private function applyFilters(array $filters): self
    {
        if (!empty($filters['status'])) {
            $this->where('status', $filters['status']);
        }
        
        if (!empty($filters['meeting_type'])) {
            $this->where('meeting_type', $filters['meeting_type']);
        }
        
        if (!empty($filters['is_virtual'])) {
            $this->where('is_virtual', $filters['is_virtual'] ? 1 : 0);
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
     * Get calendar events for a date range
     */
    public function getCalendarEvents(string $startDate, string $endDate, string $level = 'global', ?int $scopeId = null): array
    {
        $this->resetQuery();
        
        $this->whereBetween('start_datetime', $startDate, $endDate)
             ->whereIn('status', ['scheduled', 'ongoing', 'completed']);
             
        if ($level !== 'global' && $scopeId) {
            $this->where('level_scope', $level)
                 ->where('scope_id', $scopeId);
        }
        
        $meetings = $this->get(['id', 'title', 'start_datetime', 'end_datetime', 'is_virtual', 'status']);
        
        // Format for calendar
        return array_map(function($meeting) {
            return [
                'id' => $meeting['id'],
                'title' => $meeting['title'],
                'start' => $meeting['start_datetime'],
                'end' => $meeting['end_datetime'],
                'backgroundColor' => $this->getStatusColor($meeting['status']),
                'textColor' => '#fff',
                'url' => "/meetings/{$meeting['id']}"
            ];
        }, $meetings);
    }

    /**
     * Get status color for calendar display
     */
    private function getStatusColor(string $status): string
    {
        $colors = [
            'scheduled' => '#007bff',
            'ongoing' => '#28a745',
            'completed' => '#6c757d',
            'cancelled' => '#dc3545',
            'postponed' => '#ffc107'
        ];
        
        return $colors[$status] ?? '#6c757d';
    }
}