<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\User;
use Exception;

class MeetingService
{
    private $meetingModel;
    private $notificationService;

    public function __construct()
    {
        $this->meetingModel = new Meeting();
        $this->notificationService = new NotificationService();
    }

    /**
     * Create meeting with notifications and Zoom integration
     */
    public function createMeetingWithIntegration(array $data, int $createdBy): ?int
    {
        try {
            // Validate meeting data
            $this->validateMeetingData($data);
            
            // Set creator
            $data['created_by'] = $createdBy;
            
            // Generate UUID
            $data['uuid'] = $this->generateUuid('meeting_');
            
            // Handle Zoom integration if platform is zoom
            if ($data['platform'] === 'zoom') {
                $zoomData = $this->createZoomMeeting($data);
                $data = array_merge($data, $zoomData);
            }
            
            // Create meeting
            $meetingId = $this->meetingModel->createMeeting($data);
            
            if ($meetingId) {
                // Add initial participants if provided
                if (!empty($data['participants'])) {
                    $this->addParticipants($meetingId, $data['participants']);
                }
                
                // Send invitation notifications
                $this->sendMeetingInvitations($meetingId);
                
                // Log activity
                $this->logMeetingActivity($meetingId, $createdBy, 'created', 'Meeting created');
                
                return $meetingId;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("MeetingService::createMeetingWithIntegration failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update meeting with change notifications
     */
    public function updateMeetingWithNotifications(int $meetingId, array $data, int $updatedBy): bool
    {
        try {
            $meeting = $this->meetingModel->getMeetingById($meetingId);
            
            if (!$meeting) {
                throw new Exception('Meeting not found');
            }
            
            // Track changes for notifications
            $changes = $this->detectMeetingChanges($meeting, $data);
            
            // Update Zoom meeting if necessary
            if ($meeting['platform'] === 'zoom' && !empty($changes)) {
                $this->updateZoomMeeting($meeting['zoom_meeting_id'], $data);
            }
            
            // Update meeting
            $success = $this->meetingModel->updateMeeting($meetingId, $data);
            
            if ($success && !empty($changes)) {
                // Send update notifications to participants
                $this->sendMeetingUpdateNotifications($meetingId, $changes);
                
                // Log activity
                $this->logMeetingActivity($meetingId, $updatedBy, 'updated', 'Meeting updated: ' . implode(', ', array_keys($changes)));
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("MeetingService::updateMeetingWithNotifications failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel meeting with notifications
     */
    public function cancelMeeting(int $meetingId, int $cancelledBy, string $reason = ''): bool
    {
        try {
            $meeting = $this->meetingModel->getMeetingById($meetingId);
            
            if (!$meeting) {
                throw new Exception('Meeting not found');
            }
            
            // Update meeting status
            $success = $this->meetingModel->updateMeeting($meetingId, [
                'status' => 'cancelled'
            ]);
            
            if ($success) {
                // Cancel Zoom meeting if applicable
                if ($meeting['platform'] === 'zoom' && !empty($meeting['zoom_meeting_id'])) {
                    $this->cancelZoomMeeting($meeting['zoom_meeting_id']);
                }
                
                // Send cancellation notifications
                $this->sendMeetingCancellationNotifications($meetingId, $reason);
                
                // Log activity
                $this->logMeetingActivity($meetingId, $cancelledBy, 'cancelled', 'Meeting cancelled: ' . $reason);
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("MeetingService::cancelMeeting failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send meeting reminders
     */
    public function sendMeetingReminders(int $hoursBeforeStart = 24): int
    {
        try {
            $upcomingMeetings = $this->meetingModel->getUpcomingMeetings($hoursBeforeStart);
            $remindersSent = 0;
            
            foreach ($upcomingMeetings as $meeting) {
                $participants = $this->meetingModel->getMeetingParticipants($meeting['id']);
                
                foreach ($participants as $participant) {
                    if ($participant['status'] === 'accepted') {
                        $this->notificationService->sendMeetingReminderNotification(
                            $meeting['id'],
                            $participant['user_id'],
                            $hoursBeforeStart
                        );
                        $remindersSent++;
                    }
                }
            }
            
            return $remindersSent;
            
        } catch (Exception $e) {
            error_log("MeetingService::sendMeetingReminders failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate meeting analytics
     */
    public function generateMeetingAnalytics(string $scope, ?int $scopeId = null, string $period = '30days'): array
    {
        try {
            return [
                'total_meetings' => $this->meetingModel->getMeetingsCount($scope, $scopeId, 'all', 'all', '', $period),
                'completed_meetings' => $this->meetingModel->getMeetingsCount($scope, $scopeId, 'completed', 'all', '', $period),
                'cancelled_meetings' => $this->meetingModel->getMeetingsCount($scope, $scopeId, 'cancelled', 'all', '', $period),
                'average_participants' => $this->meetingModel->getAverageParticipants($scope, $scopeId, $period),
                'platform_breakdown' => $this->meetingModel->getPlatformBreakdown($scope, $scopeId, $period),
                'meeting_frequency' => $this->meetingModel->getMeetingFrequency($scope, $scopeId, $period),
                'participation_rate' => $this->meetingModel->getParticipationRate($scope, $scopeId, $period)
            ];
            
        } catch (Exception $e) {
            error_log("MeetingService::generateMeetingAnalytics failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create Zoom meeting via API
     */
    private function createZoomMeeting(array $meetingData): array
    {
        // This would integrate with Zoom SDK/API
        // For now, return placeholder data
        return [
            'zoom_meeting_id' => 'zoom_' . uniqid(),
            'zoom_meeting_url' => 'https://zoom.us/j/' . mt_rand(1000000000, 9999999999),
            'zoom_password' => substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8)
        ];
    }

    /**
     * Update Zoom meeting
     */
    private function updateZoomMeeting(string $zoomMeetingId, array $data): bool
    {
        // Zoom API integration would go here
        return true;
    }

    /**
     * Cancel Zoom meeting
     */
    private function cancelZoomMeeting(string $zoomMeetingId): bool
    {
        // Zoom API integration would go here
        return true;
    }

    /**
     * Add participants to meeting
     */
    private function addParticipants(int $meetingId, array $participantIds): void
    {
        foreach ($participantIds as $userId) {
            $this->meetingModel->addParticipant($meetingId, $userId, 'invited');
        }
    }

    /**
     * Send meeting invitations
     */
    private function sendMeetingInvitations(int $meetingId): void
    {
        $participants = $this->meetingModel->getMeetingParticipants($meetingId);
        
        foreach ($participants as $participant) {
            $this->notificationService->sendMeetingInvitationNotification($meetingId, $participant['user_id']);
        }
    }

    /**
     * Send meeting update notifications
     */
    private function sendMeetingUpdateNotifications(int $meetingId, array $changes): void
    {
        $participants = $this->meetingModel->getMeetingParticipants($meetingId);
        
        foreach ($participants as $participant) {
            $this->notificationService->sendMeetingUpdateNotification($meetingId, $participant['user_id'], $changes);
        }
    }

    /**
     * Send meeting cancellation notifications
     */
    private function sendMeetingCancellationNotifications(int $meetingId, string $reason): void
    {
        $participants = $this->meetingModel->getMeetingParticipants($meetingId);
        
        foreach ($participants as $participant) {
            $this->notificationService->sendMeetingCancellationNotification($meetingId, $participant['user_id'], $reason);
        }
    }

    /**
     * Detect changes between old and new meeting data
     */
    private function detectMeetingChanges(array $oldData, array $newData): array
    {
        $changes = [];
        $trackFields = ['title', 'start_datetime', 'end_datetime', 'location', 'platform'];
        
        foreach ($trackFields as $field) {
            if (isset($newData[$field]) && $oldData[$field] !== $newData[$field]) {
                $changes[$field] = [
                    'old' => $oldData[$field],
                    'new' => $newData[$field]
                ];
            }
        }
        
        return $changes;
    }

    /**
     * Validate meeting data
     */
    private function validateMeetingData(array $data): void
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Meeting title is required';
        }
        
        if (empty($data['start_datetime'])) {
            $errors[] = 'Start date and time is required';
        }
        
        if (empty($data['end_datetime'])) {
            $errors[] = 'End date and time is required';
        }
        
        if (!empty($data['start_datetime']) && !empty($data['end_datetime'])) {
            if (strtotime($data['end_datetime']) <= strtotime($data['start_datetime'])) {
                $errors[] = 'End time must be after start time';
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
    }

    /**
     * Log meeting activity
     */
    private function logMeetingActivity(int $meetingId, int $userId, string $action, string $description): void
    {
        $this->meetingModel->logMeetingActivity($meetingId, $userId, $action, $description);
    }

    /**
     * Generate UUID
     */
    private function generateUuid(string $prefix = ''): string
    {
        return $prefix . uniqid() . '_' . bin2hex(random_bytes(8));
    }
}