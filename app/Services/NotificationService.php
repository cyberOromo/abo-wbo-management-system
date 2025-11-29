<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

/**
 * Notification Service
 * Business logic for multi-channel notifications
 */
class NotificationService
{
    private Notification $notificationModel;
    private User $userModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->userModel = new User();
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
}