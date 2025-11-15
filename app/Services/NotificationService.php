<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Utils\Database;
use Exception;

/**
 * Enhanced Notification Service
 * 
 * Manages all system notifications including email, SMS, and in-app notifications
 * Supports hybrid registration system and approval workflows
 * 
 * Features:
 * - Multi-channel notifications (email, SMS, in-app)
 * - Priority-based delivery
 * - Template-based messaging
 * - Delivery tracking
 * - Bulk operations
 * - Queue processing
 */
class NotificationService
{
    private Notification $notificationModel;
    private User $userModel;
    protected $db;
    protected $emailService;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->userModel = new User();
        $this->db = Database::getInstance();
        // Email service would be injected in production
    }

    /**
     * Send task assignment notification
     */
    public function sendTaskAssignmentNotification(int $userId, int $taskId, string $taskTitle = null): array
    {
        try {
            return $this->notificationModel->createTaskNotification($userId, 'assigned', [
                'id' => $taskId,
                'title' => $taskTitle ?? 'New Task',
                'priority' => 'normal'
            ]);
            
        } catch (\Exception $e) {
            error_log("Send task assignment notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send task status change notification
     */
    public function sendTaskStatusChangeNotification(int $userId, int $taskId, string $taskTitle, string $newStatus): array
    {
        try {
            return $this->notificationModel->createTaskNotification($userId, "status changed to {$newStatus}", [
                'id' => $taskId,
                'title' => $taskTitle,
                'priority' => 'normal'
            ]);
            
        } catch (\Exception $e) {
            error_log("Send task status change notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send meeting invitation notification
     */
    public function sendMeetingInvitationNotification(int $userId, array $meetingData): array
    {
        try {
            return $this->notificationModel->createMeetingNotification($userId, 'invitation sent', $meetingData);
            
        } catch (\Exception $e) {
            error_log("Send meeting invitation notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send meeting reminder notification
     */
    public function sendMeetingReminderNotification(int $userId, array $meetingData): array
    {
        try {
            $data = [
                'type' => Notification::TYPE_MEETING,
                'title' => 'Meeting Reminder',
                'message' => "Reminder: Meeting '{$meetingData['title']}' starts in 30 minutes",
                'recipient_id' => $userId,
                'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL, Notification::CHANNEL_SMS],
                'priority' => Notification::PRIORITY_HIGH,
                'metadata' => [
                    'meeting_id' => $meetingData['id'],
                    'meeting_title' => $meetingData['title'],
                    'start_datetime' => $meetingData['start_datetime'],
                    'reminder_type' => '30_minutes'
                ],
                'action_url' => "/meetings/{$meetingData['id']}",
                'action_text' => 'Join Meeting'
            ];
            
            return $this->notificationModel->createNotification($data);
            
        } catch (\Exception $e) {
            error_log("Send meeting reminder notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send event registration confirmation
     */
    public function sendEventRegistrationConfirmation(int $userId, array $eventData): array
    {
        try {
            $data = [
                'type' => Notification::TYPE_EVENT,
                'title' => 'Event Registration Confirmed',
                'message' => "Your registration for '{$eventData['title']}' has been confirmed",
                'recipient_id' => $userId,
                'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL],
                'priority' => Notification::PRIORITY_NORMAL,
                'metadata' => [
                    'event_id' => $eventData['id'],
                    'event_title' => $eventData['title'],
                    'start_datetime' => $eventData['start_datetime']
                ],
                'action_url' => "/events/{$eventData['id']}",
                'action_text' => 'View Event'
            ];
            
            return $this->notificationModel->createNotification($data);
            
        } catch (\Exception $e) {
            error_log("Send event registration confirmation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send course enrollment confirmation
     */
    public function sendCourseEnrollmentConfirmation(int $userId, array $courseData): array
    {
        try {
            $data = [
                'type' => Notification::TYPE_COURSE,
                'title' => 'Course Enrollment Confirmed',
                'message' => "You have been enrolled in '{$courseData['title']}'",
                'recipient_id' => $userId,
                'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL],
                'priority' => Notification::PRIORITY_NORMAL,
                'metadata' => [
                    'course_id' => $courseData['id'],
                    'course_title' => $courseData['title'],
                    'instructor' => $courseData['instructor_name'] ?? 'TBD'
                ],
                'action_url' => "/courses/{$courseData['id']}",
                'action_text' => 'Start Learning'
            ];
            
            return $this->notificationModel->createNotification($data);
            
        } catch (\Exception $e) {
            error_log("Send course enrollment confirmation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send donation receipt notification
     */
    public function sendDonationReceiptNotification(int $userId, array $donationData): array
    {
        try {
            $data = [
                'type' => Notification::TYPE_DONATION,
                'title' => 'Donation Receipt',
                'message' => "Thank you for your donation of {$donationData['currency']} {$donationData['amount']}",
                'recipient_id' => $userId,
                'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL],
                'priority' => Notification::PRIORITY_NORMAL,
                'metadata' => [
                    'donation_id' => $donationData['id'],
                    'amount' => $donationData['amount'],
                    'currency' => $donationData['currency'],
                    'transaction_reference' => $donationData['transaction_reference'] ?? null
                ],
                'action_url' => "/donations/{$donationData['id']}/receipt",
                'action_text' => 'Download Receipt'
            ];
            
            return $this->notificationModel->createNotification($data);
            
        } catch (\Exception $e) {
            error_log("Send donation receipt notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send system announcement
     */
    public function sendSystemAnnouncement(string $title, string $message, string $scope, int $scopeId = null, array $options = []): array
    {
        try {
            $data = array_merge([
                'type' => Notification::TYPE_ANNOUNCEMENT,
                'title' => $title,
                'message' => $message,
                'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL],
                'priority' => Notification::PRIORITY_NORMAL
            ], $options);
            
            return $this->notificationModel->sendToScope($scope, $scopeId, $data);
            
        } catch (\Exception $e) {
            error_log("Send system announcement error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send announcement'];
        }
    }

    /**
     * Send welcome notification to new user
     */
    public function sendWelcomeNotification(int $userId, array $userData): array
    {
        try {
            $data = [
                'type' => Notification::TYPE_USER,
                'title' => 'Welcome to ABO-WBO Management System',
                'message' => "Welcome {$userData['first_name']}! Your account has been created successfully.",
                'recipient_id' => $userId,
                'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL],
                'priority' => Notification::PRIORITY_NORMAL,
                'metadata' => [
                    'welcome_message' => true,
                    'user_level' => $userData['level_scope'] ?? 'gurmu'
                ],
                'action_url' => '/dashboard',
                'action_text' => 'Get Started'
            ];
            
            return $this->notificationModel->createNotification($data);
            
        } catch (\Exception $e) {
            error_log("Send welcome notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Send account approval notification
     */
    public function sendAccountApprovalNotification(int $userId, string $status): array
    {
        try {
            $title = $status === 'approved' ? 'Account Approved' : 'Account Status Update';
            $message = $status === 'approved' 
                ? 'Your account has been approved. You can now access all features.'
                : "Your account status has been updated to: {$status}";
            
            $data = [
                'type' => Notification::TYPE_USER,
                'title' => $title,
                'message' => $message,
                'recipient_id' => $userId,
                'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL],
                'priority' => Notification::PRIORITY_HIGH,
                'metadata' => [
                    'account_status' => $status,
                    'approval_notification' => true
                ],
                'action_url' => '/dashboard',
                'action_text' => 'Access Dashboard'
            ];
            
            return $this->notificationModel->createNotification($data);
            
        } catch (\Exception $e) {
            error_log("Send account approval notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send notification'];
        }
    }

    /**
     * Process pending notifications (for cron job or queue worker)
     */
    public function processPendingNotifications(int $batchSize = 50): array
    {
        try {
            $pendingNotifications = $this->notificationModel->getPendingNotifications($batchSize);
            $processedCount = 0;
            $failedCount = 0;
            
            foreach ($pendingNotifications as $notification) {
                $result = $this->deliverNotification($notification);
                
                if ($result['success']) {
                    $processedCount++;
                } else {
                    $failedCount++;
                }
            }
            
            return [
                'success' => true,
                'processed' => $processedCount,
                'failed' => $failedCount,
                'message' => "Processed {$processedCount} notifications, {$failedCount} failed"
            ];
            
        } catch (\Exception $e) {
            error_log("Process pending notifications error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Deliver single notification through specified channels
     */
    private function deliverNotification(array $notification): array
    {
        try {
            $deliveryResults = [];
            $channels = $notification['channels'];
            $overallSuccess = true;
            
            foreach ($channels as $channel) {
                switch ($channel) {
                    case Notification::CHANNEL_IN_APP:
                        // In-app notifications are already stored, just mark as delivered
                        $deliveryResults[$channel] = ['success' => true];
                        break;
                        
                    case Notification::CHANNEL_EMAIL:
                        $deliveryResults[$channel] = $this->sendEmailNotification($notification);
                        break;
                        
                    case Notification::CHANNEL_SMS:
                        $deliveryResults[$channel] = $this->sendSMSNotification($notification);
                        break;
                        
                    case Notification::CHANNEL_PUSH:
                        $deliveryResults[$channel] = $this->sendPushNotification($notification);
                        break;
                        
                    default:
                        $deliveryResults[$channel] = ['success' => false, 'message' => 'Unknown channel'];
                        break;
                }
                
                if (!$deliveryResults[$channel]['success']) {
                    $overallSuccess = false;
                }
            }
            
            // Update notification status
            $status = $overallSuccess ? Notification::STATUS_SENT : Notification::STATUS_FAILED;
            $errorMessage = $overallSuccess ? null : json_encode($deliveryResults);
            
            $this->notificationModel->updateDeliveryStatus(
                $notification['id'],
                $status,
                $errorMessage
            );
            
            return [
                'success' => $overallSuccess,
                'delivery_results' => $deliveryResults
            ];
            
        } catch (\Exception $e) {
            error_log("Deliver notification error: " . $e->getMessage());
            
            // Mark as failed
            $this->notificationModel->updateDeliveryStatus(
                $notification['id'],
                Notification::STATUS_FAILED,
                $e->getMessage()
            );
            
            return [
                'success' => false,
                'message' => 'Delivery failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(array $notification): array
    {
        try {
            // Get recipient email
            if (!$notification['recipient_id']) {
                return ['success' => false, 'message' => 'No recipient specified'];
            }
            
            $user = $this->userModel->find($notification['recipient_id']);
            if (!$user || !$user['email']) {
                return ['success' => false, 'message' => 'Recipient email not found'];
            }
            
            // Prepare email content
            $subject = $notification['title'];
            $message = $this->formatEmailMessage($notification);
            
            // Send email (implementation would use your email service)
            $emailSent = $this->sendEmail($user['email'], $subject, $message);
            
            return [
                'success' => $emailSent,
                'message' => $emailSent ? 'Email sent successfully' : 'Failed to send email'
            ];
            
        } catch (\Exception $e) {
            error_log("Send email notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Email send failed: ' . $e->getMessage()];
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSMSNotification(array $notification): array
    {
        try {
            // Get recipient phone
            if (!$notification['recipient_id']) {
                return ['success' => false, 'message' => 'No recipient specified'];
            }
            
            $user = $this->userModel->find($notification['recipient_id']);
            if (!$user || !$user['phone']) {
                return ['success' => false, 'message' => 'Recipient phone not found'];
            }
            
            // Prepare SMS content (keep it short)
            $message = $this->formatSMSMessage($notification);
            
            // Send SMS (implementation would use your SMS service)
            $smsSent = $this->sendSMS($user['phone'], $message);
            
            return [
                'success' => $smsSent,
                'message' => $smsSent ? 'SMS sent successfully' : 'Failed to send SMS'
            ];
            
        } catch (\Exception $e) {
            error_log("Send SMS notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'SMS send failed: ' . $e->getMessage()];
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification(array $notification): array
    {
        try {
            // Implementation would use Firebase Cloud Messaging or similar
            // For now, we'll just simulate success
            return ['success' => true, 'message' => 'Push notification sent'];
            
        } catch (\Exception $e) {
            error_log("Send push notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Push send failed: ' . $e->getMessage()];
        }
    }

    /**
     * Format email message with HTML template
     */
    private function formatEmailMessage(array $notification): string
    {
        $html = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c5aa0;'>{$notification['title']}</h2>
                <p>{$notification['message']}</p>";
                
        if ($notification['action_url'] && $notification['action_text']) {
            $html .= "
                <p style='margin-top: 30px;'>
                    <a href='{$notification['action_url']}' 
                       style='background-color: #2c5aa0; color: white; padding: 12px 24px; 
                              text-decoration: none; border-radius: 4px; display: inline-block;'>
                        {$notification['action_text']}
                    </a>
                </p>";
        }
        
        $html .= "
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666; text-align: center;'>
                    This is an automated message from ABO-WBO Management System.<br>
                    Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Format SMS message (keep it short)
     */
    private function formatSMSMessage(array $notification): string
    {
        $message = "{$notification['title']}: {$notification['message']}";
        
        // Truncate if too long for SMS
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }
        
        return $message;
    }

    /**
     * Send email using configured email service
     */
    private function sendEmail(string $to, string $subject, string $message): bool
    {
        try {
            // This would use your configured email service (PHPMailer, Swiftmailer, etc.)
            // For now, we'll use PHP's mail function as a placeholder
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ABO-WBO System <noreply@abo-wbo.org>',
                'Reply-To: noreply@abo-wbo.org',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            return mail($to, $subject, $message, implode("\r\n", $headers));
            
        } catch (\Exception $e) {
            error_log("Send email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS using configured SMS service
     */
    private function sendSMS(string $to, string $message): bool
    {
        try {
            // This would use your configured SMS service (Twilio, Nexmo, etc.)
            // For now, we'll just log and return true as placeholder
            error_log("SMS to {$to}: {$message}");
            return true;
            
        } catch (\Exception $e) {
            error_log("Send SMS error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send scheduled reminders
     */
    public function sendScheduledReminders(): array
    {
        try {
            $processed = 0;
            
            // Meeting reminders (30 minutes before)
            $upcomingMeetings = $this->getUpcomingMeetings();
            foreach ($upcomingMeetings as $meeting) {
                $participants = $this->getMeetingParticipants($meeting['id']);
                foreach ($participants as $participant) {
                    $this->sendMeetingReminderNotification($participant['user_id'], $meeting);
                    $processed++;
                }
            }
            
            // Event reminders (24 hours before)
            $upcomingEvents = $this->getUpcomingEvents();
            foreach ($upcomingEvents as $event) {
                $participants = $this->getEventParticipants($event['id']);
                foreach ($participants as $participant) {
                    $this->sendEventReminderNotification($participant['user_id'], $event);
                    $processed++;
                }
            }
            
            return [
                'success' => true,
                'processed' => $processed,
                'message' => "Sent {$processed} scheduled reminders"
            ];
            
        } catch (\Exception $e) {
            error_log("Send scheduled reminders error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send reminders: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get upcoming meetings for reminders
     */
    private function getUpcomingMeetings(): array
    {
        // Implementation would query meetings starting in 30 minutes
        return [];
    }

    /**
     * Get upcoming events for reminders  
     */
    private function getUpcomingEvents(): array
    {
        // Implementation would query events starting in 24 hours
        return [];
    }

    /**
     * Get meeting participants
     */
    private function getMeetingParticipants(int $meetingId): array
    {
        // Implementation would get meeting participants
        return [];
    }

    /**
     * Get event participants
     */
    private function getEventParticipants(int $eventId): array
    {
        // Implementation would get event participants
        return [];
    }

    /**
     * Send event reminder notification
     */
    private function sendEventReminderNotification(int $userId, array $eventData): array
    {
        $data = [
            'type' => Notification::TYPE_EVENT,
            'title' => 'Event Reminder',
            'message' => "Reminder: Event '{$eventData['title']}' starts tomorrow",
            'recipient_id' => $userId,
            'channels' => [Notification::CHANNEL_IN_APP, Notification::CHANNEL_EMAIL],
            'priority' => Notification::PRIORITY_NORMAL,
            'metadata' => [
                'event_id' => $eventData['id'],
                'event_title' => $eventData['title'],
                'start_datetime' => $eventData['start_datetime'],
                'reminder_type' => '24_hours'
            ],
            'action_url' => "/events/{$eventData['id']}",
            'action_text' => 'View Event'
        ];
        
        return $this->notificationModel->createNotification($data);
    }

    // ========================================
    // HYBRID REGISTRATION SYSTEM METHODS
    // ========================================

    /**
     * Send enhanced notification with queue support
     */
    public function sendNotification(array $notification): int
    {
        // Validate notification data
        $this->validateNotificationData($notification);
        
        // Create notification record
        $notificationId = $this->createNotificationRecord($notification);
        
        // Process immediate delivery for high priority notifications
        if ($notification['priority'] === 'high' || $notification['send_immediately'] ?? false) {
            $this->processNotification($notificationId);
        }
        
        return $notificationId;
    }

    /**
     * Create notification record in queue
     */
    protected function createNotificationRecord(array $notification): int
    {
        $data = [
            'user_id' => $notification['user_id'] ?? null,
            'notification_type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => isset($notification['data']) ? json_encode($notification['data']) : null,
            'channels' => json_encode($notification['channels'] ?? ['in_app']),
            'priority' => $notification['priority'] ?? 'normal',
            'status' => 'pending',
            'template_id' => $notification['template_id'] ?? null,
            'template_data' => isset($notification['template_data']) ? json_encode($notification['template_data']) : null,
            'send_at' => $notification['send_at'] ?? date('Y-m-d H:i:s'),
            'expires_at' => $notification['expires_at'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('notification_queue', $data);
    }

    /**
     * Process notification from queue
     */
    public function processNotification(int $notificationId): bool
    {
        try {
            $notification = $this->getNotificationById($notificationId);
            if (!$notification || $notification['status'] !== 'pending') {
                return false;
            }
            
            // Check if notification has expired
            if ($notification['expires_at'] && strtotime($notification['expires_at']) < time()) {
                $this->markNotificationExpired($notificationId);
                return false;
            }
            
            // Mark as processing
            $this->updateNotificationStatus($notificationId, 'processing');
            
            $channels = json_decode($notification['channels'], true) ?: ['in_app'];
            $results = [];
            
            // Process each channel
            foreach ($channels as $channel) {
                $result = $this->deliverToChannel($notification, $channel);
                $results[$channel] = $result;
            }
            
            // Update final status
            $allSuccessful = array_reduce($results, function($carry, $result) {
                return $carry && $result['success'];
            }, true);
            
            $status = $allSuccessful ? 'delivered' : 'failed';
            $this->updateNotificationStatus($notificationId, $status, [
                'delivery_results' => json_encode($results),
                'delivered_at' => date('Y-m-d H:i:s')
            ]);
            
            return $allSuccessful;
            
        } catch (Exception $e) {
            $this->updateNotificationStatus($notificationId, 'failed', [
                'error' => $e->getMessage()
            ]);
            error_log("Failed to process notification {$notificationId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deliver notification to specific channel
     */
    protected function deliverToChannel(array $notification, string $channel): array
    {
        switch ($channel) {
            case 'email':
                return $this->deliverEmail($notification);
                
            case 'sms':
                return $this->deliverSMS($notification);
                
            case 'in_app':
                return $this->deliverInApp($notification);
                
            case 'push':
                return $this->deliverPush($notification);
                
            default:
                return ['success' => false, 'error' => "Unknown channel: {$channel}"];
        }
    }

    /**
     * Deliver email notification
     */
    protected function deliverEmail(array $notification): array
    {
        try {
            // Get user email
            $user = $this->getUserById($notification['user_id']);
            if (!$user || empty($user['email'])) {
                return ['success' => false, 'error' => 'No email address found'];
            }
            
            // Prepare email content
            $content = $this->prepareEmailContent($notification);
            
            // Send email (placeholder - would integrate with actual email service)
            $emailSent = $this->sendEmailEnhanced([
                'to' => $user['email'],
                'subject' => $content['subject'],
                'html_body' => $content['html_body'],
                'text_body' => $content['text_body']
            ]);
            
            return [
                'success' => $emailSent,
                'recipient' => $user['email'],
                'sent_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Deliver in-app notification
     */
    protected function deliverInApp(array $notification): array
    {
        try {
            // Create in-app notification record
            $inAppId = $this->db->insert('user_notifications', [
                'user_id' => $notification['user_id'],
                'notification_id' => $notification['id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'data' => $notification['data'],
                'type' => $notification['notification_type'],
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => $inAppId > 0,
                'in_app_id' => $inAppId,
                'delivered_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Prepare email content
     */
    protected function prepareEmailContent(array $notification): array
    {
        $subject = $notification['title'];
        $message = $notification['message'];
        
        // Apply template if specified
        if ($notification['template_id']) {
            $template = $this->getNotificationTemplate($notification['template_id']);
            if ($template) {
                $templateData = json_decode($notification['template_data'], true) ?: [];
                $subject = $this->applyTemplate($template['subject_template'], $templateData);
                $message = $this->applyTemplate($template['body_template'], $templateData);
            }
        }
        
        return [
            'subject' => $subject,
            'html_body' => $this->convertToHTML($message),
            'text_body' => strip_tags($message)
        ];
    }

    /**
     * Helper methods for hybrid registration notifications
     */
    public function sendHybridRegistrationNotification(string $email, string $verificationCode): array
    {
        $data = [
            'type' => 'registration_verification',
            'title' => 'ABO-WBO Registration Verification',
            'message' => "Your verification code is: {$verificationCode}. This code expires in 24 hours.",
            'channels' => ['email'],
            'priority' => 'high',
            'template_data' => [
                'verification_code' => $verificationCode,
                'organization' => 'ABO-WBO',
                'expires_hours' => 24
            ]
        ];
        
        // Send directly via email since user doesn't exist yet
        return $this->sendEmailDirectly($email, $data);
    }

    public function sendApprovalRequestNotification(int $approverId, array $registrationData): int
    {
        return $this->sendNotification([
            'user_id' => $approverId,
            'type' => 'approval_request',
            'title' => 'New Registration Approval Required',
            'message' => "A new registration from {$registrationData['first_name']} {$registrationData['last_name']} requires your approval.",
            'channels' => ['in_app', 'email'],
            'priority' => 'high',
            'data' => [
                'registration_id' => $registrationData['id'],
                'applicant_name' => $registrationData['first_name'] . ' ' . $registrationData['last_name'],
                'target_hierarchy' => $registrationData['target_hierarchy_level']
            ]
        ]);
    }

    public function sendInternalEmailCreatedNotification(int $userId, string $internalEmail, string $tempPassword): int
    {
        return $this->sendNotification([
            'user_id' => $userId,
            'type' => 'internal_email_created',
            'title' => 'Your Internal Email Account is Ready',
            'message' => "Your internal email {$internalEmail} has been created. Your temporary password is: {$tempPassword}",
            'channels' => ['in_app', 'email'],
            'priority' => 'high',
            'data' => [
                'internal_email' => $internalEmail,
                'temp_password' => $tempPassword,
                'login_url' => 'https://mail.abo-wbo.org'
            ]
        ]);
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    protected function deliverSMS(array $notification): array
    {
        // SMS delivery placeholder
        return ['success' => true, 'message' => 'SMS delivery not implemented'];
    }

    protected function deliverPush(array $notification): array
    {
        // Push notification placeholder
        return ['success' => true, 'message' => 'Push delivery not implemented'];
    }

    protected function sendEmailEnhanced(array $emailData): bool
    {
        // Enhanced email sending placeholder
        error_log("Email sent to {$emailData['to']}: {$emailData['subject']}");
        return true;
    }

    protected function sendEmailDirectly(string $email, array $data): array
    {
        // Direct email sending for registration verification
        error_log("Direct email sent to {$email}: {$data['title']}");
        return ['success' => true, 'recipient' => $email];
    }

    protected function getUserById(int $userId): ?array
    {
        if (!$userId) return null;
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    }

    protected function getNotificationById(int $notificationId): ?array
    {
        return $this->db->fetch("SELECT * FROM notification_queue WHERE id = ?", [$notificationId]);
    }

    protected function getNotificationTemplate(int $templateId): ?array
    {
        return $this->db->fetch("SELECT * FROM notification_templates WHERE id = ?", [$templateId]);
    }

    protected function updateNotificationStatus(int $notificationId, string $status, array $additionalData = []): void
    {
        $data = array_merge(['status' => $status], $additionalData);
        $this->db->update('notification_queue', $data, ['id' => $notificationId]);
    }

    protected function markNotificationExpired(int $notificationId): void
    {
        $this->updateNotificationStatus($notificationId, 'expired', ['expired_at' => date('Y-m-d H:i:s')]);
    }

    protected function validateNotificationData(array $notification): void
    {
        $required = ['type', 'title', 'message'];
        foreach ($required as $field) {
            if (empty($notification[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
    }

    protected function applyTemplate(string $template, array $data): string
    {
        $result = $template;
        foreach ($data as $key => $value) {
            $result = str_replace("{{$key}}", $value, $result);
        }
        return $result;
    }

    protected function convertToHTML(string $text): string
    {
        return nl2br(htmlspecialchars($text));
    }

    /**
     * Process notification queue
     */
    public function processQueue(int $limit = 50): int
    {
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notification_queue 
             WHERE status = 'pending' 
             AND (send_at IS NULL OR send_at <= NOW())
             ORDER BY priority DESC, created_at ASC 
             LIMIT ?",
            [$limit]
        );
        
        $processed = 0;
        foreach ($notifications as $notification) {
            if ($this->processNotification($notification['id'])) {
                $processed++;
            }
        }
        
        return $processed;
    }
}