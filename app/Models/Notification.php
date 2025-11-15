<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Notification Model
 * Handles multi-channel notifications (Email, SMS, In-app)
 */
class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    
    // Notification types
    const TYPE_SYSTEM = 'system';
    const TYPE_TASK = 'task';
    const TYPE_MEETING = 'meeting';
    const TYPE_EVENT = 'event';
    const TYPE_COURSE = 'course';
    const TYPE_DONATION = 'donation';
    const TYPE_USER = 'user';
    const TYPE_ANNOUNCEMENT = 'announcement';
    
    // Notification channels
    const CHANNEL_IN_APP = 'in_app';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_PUSH = 'push';
    
    // Notification priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    // Notification statuses
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';
    
    // Level scopes
    const SCOPE_GLOBAL = 'global';
    const SCOPE_GODINA = 'godina';
    const SCOPE_GAMTA = 'gamta';
    const SCOPE_GURMU = 'gurmu';
    const SCOPE_USER = 'user';

    protected $fillable = [
        'uuid',
        'type',
        'title',
        'message',
        'level_scope',
        'scope_id',
        'recipient_id',
        'sender_id',
        'channels',
        'priority',
        'scheduled_at',
        'sent_at',
        'read_at',
        'status',
        'metadata',
        'action_url',
        'action_text',
        'template_id',
        'template_data',
        'delivery_attempts',
        'last_attempt_at',
        'error_message',
        'expires_at'
    ];

    protected $casts = [
        'channels' => 'json',
        'metadata' => 'json',
        'template_data' => 'json'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new notification
     */
    public function createNotification(array $data): array
    {
        try {
            // Generate UUID
            $data['uuid'] = $this->generateUUID();
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Set default status
            if (!isset($data['status'])) {
                $data['status'] = self::STATUS_PENDING;
            }
            
            // Ensure JSON fields are properly encoded
            if (isset($data['channels']) && is_array($data['channels'])) {
                $data['channels'] = json_encode($data['channels']);
            }
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $data['metadata'] = json_encode($data['metadata']);
            }
            if (isset($data['template_data']) && is_array($data['template_data'])) {
                $data['template_data'] = json_encode($data['template_data']);
            }

            $notificationId = $this->create($data);
            
            if ($notificationId) {
                return [
                    'success' => true,
                    'notification_id' => $notificationId,
                    'message' => 'Notification created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create notification'];
            
        } catch (\Exception $e) {
            error_log("Notification creation error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Notification creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to single user
     */
    public function sendToUser(int $recipientId, array $notificationData): array
    {
        try {
            $data = array_merge($notificationData, [
                'level_scope' => self::SCOPE_USER,
                'recipient_id' => $recipientId
            ]);
            
            $result = $this->createNotification($data);
            
            if ($result['success']) {
                // Queue for immediate sending if no scheduled time
                if (empty($data['scheduled_at'])) {
                    $this->queueForSending($result['notification_id']);
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Send notification to user error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to scope (broadcast)
     */
    public function sendToScope(string $scope, int $scopeId, array $notificationData): array
    {
        try {
            $data = array_merge($notificationData, [
                'level_scope' => $scope,
                'scope_id' => $scopeId
            ]);
            
            $result = $this->createNotification($data);
            
            if ($result['success']) {
                // Get users in scope and create individual notifications
                $users = $this->getUsersInScope($scope, $scopeId);
                $individualNotifications = [];
                
                foreach ($users as $user) {
                    $individualData = array_merge($notificationData, [
                        'level_scope' => self::SCOPE_USER,
                        'recipient_id' => $user['id'],
                        'metadata' => array_merge(
                            json_decode($notificationData['metadata'] ?? '{}', true),
                            ['broadcast_id' => $result['notification_id']]
                        )
                    ]);
                    
                    $individualResult = $this->createNotification($individualData);
                    if ($individualResult['success']) {
                        $individualNotifications[] = $individualResult['notification_id'];
                        
                        // Queue for immediate sending if no scheduled time
                        if (empty($data['scheduled_at'])) {
                            $this->queueForSending($individualResult['notification_id']);
                        }
                    }
                }
                
                return [
                    'success' => true,
                    'broadcast_id' => $result['notification_id'],
                    'individual_notifications' => count($individualNotifications),
                    'message' => 'Broadcast notification created successfully'
                ];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Send notification to scope error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to send broadcast notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get notifications for user
     */
    public function getNotificationsForUser(int $userId, array $filters = []): array
    {
        try {
            $query = "SELECT n.*, 
                             sender.first_name as sender_first_name, 
                             sender.last_name as sender_last_name
                      FROM {$this->table} n
                      LEFT JOIN users sender ON n.sender_id = sender.id
                      WHERE n.recipient_id = :user_id";
            
            $params = ['user_id' => $userId];
            
            // Apply filters
            if (!empty($filters['type'])) {
                $query .= " AND n.type = :type";
                $params['type'] = $filters['type'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND n.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['priority'])) {
                $query .= " AND n.priority = :priority";
                $params['priority'] = $filters['priority'];
            }
            
            if (!empty($filters['unread_only'])) {
                $query .= " AND n.read_at IS NULL";
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(n.created_at) >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(n.created_at) <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }

            $query .= " ORDER BY 
                       CASE n.priority 
                           WHEN 'urgent' THEN 1 
                           WHEN 'high' THEN 2 
                           WHEN 'normal' THEN 3 
                           WHEN 'low' THEN 4 
                       END,
                       n.created_at DESC";

            // Add limit if specified
            if (!empty($filters['limit'])) {
                $query .= " LIMIT " . (int) $filters['limit'];
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($notifications as &$notification) {
                $notification['channels'] = json_decode($notification['channels'] ?? '[]', true);
                $notification['metadata'] = json_decode($notification['metadata'] ?? '{}', true);
                $notification['template_data'] = json_decode($notification['template_data'] ?? '{}', true);
            }
            
            return $notifications;
            
        } catch (\Exception $e) {
            error_log("Get notifications for user error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): array
    {
        try {
            $query = "UPDATE {$this->table} 
                     SET status = :status, read_at = :read_at, updated_at = :updated_at
                     WHERE id = :id AND recipient_id = :user_id AND read_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'status' => self::STATUS_READ,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $notificationId,
                'user_id' => $userId
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Notification marked as read'
                ];
            }
            
            return ['success' => false, 'message' => 'Notification not found or already read'];
            
        } catch (\Exception $e) {
            error_log("Mark notification as read error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to mark notification as read: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): array
    {
        try {
            $query = "UPDATE {$this->table} 
                     SET status = :status, read_at = :read_at, updated_at = :updated_at
                     WHERE recipient_id = :user_id AND read_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'status' => self::STATUS_READ,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'user_id' => $userId
            ]);
            
            if ($result) {
                $count = $stmt->rowCount();
                return [
                    'success' => true,
                    'marked_count' => $count,
                    'message' => "Marked {$count} notifications as read"
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to mark notifications as read'];
            
        } catch (\Exception $e) {
            error_log("Mark all notifications as read error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to mark notifications as read: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table} 
                     WHERE recipient_id = :user_id AND read_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            
            return (int) $stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Get unread count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get pending notifications for sending
     */
    public function getPendingNotifications(int $limit = 100): array
    {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE status = :status 
                     AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                     AND (expires_at IS NULL OR expires_at > NOW())
                     ORDER BY 
                         CASE priority 
                             WHEN 'urgent' THEN 1 
                             WHEN 'high' THEN 2 
                             WHEN 'normal' THEN 3 
                             WHEN 'low' THEN 4 
                         END,
                         created_at ASC
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':status', self::STATUS_PENDING, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($notifications as &$notification) {
                $notification['channels'] = json_decode($notification['channels'] ?? '[]', true);
                $notification['metadata'] = json_decode($notification['metadata'] ?? '{}', true);
                $notification['template_data'] = json_decode($notification['template_data'] ?? '{}', true);
            }
            
            return $notifications;
            
        } catch (\Exception $e) {
            error_log("Get pending notifications error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update notification status after delivery attempt
     */
    public function updateDeliveryStatus(int $notificationId, string $status, string $errorMessage = null): array
    {
        try {
            $updateData = [
                'status' => $status,
                'delivery_attempts' => 'delivery_attempts + 1',
                'last_attempt_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($status === self::STATUS_SENT) {
                $updateData['sent_at'] = date('Y-m-d H:i:s');
            }
            
            if ($errorMessage) {
                $updateData['error_message'] = $errorMessage;
            }
            
            $query = "UPDATE {$this->table} SET 
                     status = :status,
                     delivery_attempts = delivery_attempts + 1,
                     last_attempt_at = :last_attempt_at,
                     updated_at = :updated_at";
            
            if ($status === self::STATUS_SENT) {
                $query .= ", sent_at = :sent_at";
            }
            
            if ($errorMessage) {
                $query .= ", error_message = :error_message";
            }
            
            $query .= " WHERE id = :id";
            
            $params = [
                'status' => $status,
                'last_attempt_at' => $updateData['last_attempt_at'],
                'updated_at' => $updateData['updated_at'],
                'id' => $notificationId
            ];
            
            if ($status === self::STATUS_SENT) {
                $params['sent_at'] = $updateData['sent_at'];
            }
            
            if ($errorMessage) {
                $params['error_message'] = $errorMessage;
            }
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Delivery status updated'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to update delivery status'];
            
        } catch (\Exception $e) {
            error_log("Update delivery status error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to update delivery status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get notification statistics by scope
     */
    public function getNotificationStatistics(string $scope, int $scopeId = null): array
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total_notifications,
                        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_notifications,
                        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_notifications,
                        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_notifications,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_notifications,
                        SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_notifications,
                        AVG(CASE WHEN read_at IS NOT NULL AND sent_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(MINUTE, sent_at, read_at) 
                            ELSE NULL END) as avg_read_time_minutes
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
            
            // Calculate delivery and read rates
            $stats['delivery_rate'] = $stats['total_notifications'] > 0 
                ? round(($stats['delivered_notifications'] / $stats['total_notifications']) * 100, 2) 
                : 0;
            
            $stats['read_rate'] = $stats['delivered_notifications'] > 0 
                ? round(($stats['read_notifications'] / $stats['delivered_notifications']) * 100, 2) 
                : 0;
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Get notification statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean up old notifications
     */
    public function cleanupOldNotifications(int $daysOld = 90): array
    {
        try {
            $query = "DELETE FROM {$this->table} 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                     AND status IN ('read', 'failed')";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['days' => $daysOld]);
            
            $deletedCount = $stmt->rowCount();
            
            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => "Cleaned up {$deletedCount} old notifications"
            ];
            
        } catch (\Exception $e) {
            error_log("Cleanup old notifications error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to cleanup notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get users in scope for broadcasting
     */
    private function getUsersInScope(string $scope, int $scopeId): array
    {
        try {
            $query = "SELECT id, email, phone, first_name, last_name FROM users WHERE status = 'active'";
            
            switch ($scope) {
                case self::SCOPE_GLOBAL:
                    // All active users
                    break;
                    
                case self::SCOPE_GODINA:
                    $query .= " AND godina_id = :scope_id";
                    break;
                    
                case self::SCOPE_GAMTA:
                    $query .= " AND gamta_id = :scope_id";
                    break;
                    
                case self::SCOPE_GURMU:
                    $query .= " AND gurmu_id = :scope_id";
                    break;
                    
                default:
                    return [];
            }
            
            $stmt = $this->db->prepare($query);
            if ($scope !== self::SCOPE_GLOBAL) {
                $stmt->execute(['scope_id' => $scopeId]);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get users in scope error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Queue notification for sending
     */
    private function queueForSending(int $notificationId): void
    {
        // This would typically add to a job queue
        // For now, we'll just log it
        error_log("Notification {$notificationId} queued for sending");
    }

    /**
     * Generate UUID for notification
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
     * Create system notification
     */
    public function createSystemNotification(string $title, string $message, array $options = []): array
    {
        $data = array_merge([
            'type' => self::TYPE_SYSTEM,
            'title' => $title,
            'message' => $message,
            'channels' => [self::CHANNEL_IN_APP],
            'priority' => self::PRIORITY_NORMAL
        ], $options);
        
        return $this->createNotification($data);
    }

    /**
     * Create task notification
     */
    public function createTaskNotification(int $recipientId, string $action, array $taskData): array
    {
        $data = [
            'type' => self::TYPE_TASK,
            'title' => "Task {$action}",
            'message' => "Task '{$taskData['title']}' has been {$action}",
            'recipient_id' => $recipientId,
            'channels' => [self::CHANNEL_IN_APP, self::CHANNEL_EMAIL],
            'priority' => $taskData['priority'] === 'urgent' ? self::PRIORITY_URGENT : self::PRIORITY_NORMAL,
            'metadata' => [
                'task_id' => $taskData['id'],
                'task_title' => $taskData['title'],
                'action' => $action
            ],
            'action_url' => "/tasks/{$taskData['id']}",
            'action_text' => 'View Task'
        ];
        
        return $this->createNotification($data);
    }

    /**
     * Create meeting notification
     */
    public function createMeetingNotification(int $recipientId, string $action, array $meetingData): array
    {
        $data = [
            'type' => self::TYPE_MEETING,
            'title' => "Meeting {$action}",
            'message' => "Meeting '{$meetingData['title']}' has been {$action}",
            'recipient_id' => $recipientId,
            'channels' => [self::CHANNEL_IN_APP, self::CHANNEL_EMAIL],
            'priority' => self::PRIORITY_NORMAL,
            'metadata' => [
                'meeting_id' => $meetingData['id'],
                'meeting_title' => $meetingData['title'],
                'action' => $action,
                'start_datetime' => $meetingData['start_datetime']
            ],
            'action_url' => "/meetings/{$meetingData['id']}",
            'action_text' => 'View Meeting'
        ];
        
        return $this->createNotification($data);
    }
}