<?php

namespace App\Services;

use App\Models\Event;
use Exception;

class EventService
{
    private $eventModel;
    private $notificationService;

    public function __construct()
    {
        $this->eventModel = new Event();
        $this->notificationService = new NotificationService();
    }

    /**
     * Create event with registration management
     */
    public function createEventWithRegistration(array $data, int $createdBy): ?int
    {
        try {
            // Validate event data
            $this->validateEventData($data);
            
            // Set creator
            $data['created_by'] = $createdBy;
            
            // Generate UUID
            $data['uuid'] = $this->generateUuid('event_');
            
            // Create event
            $eventId = $this->eventModel->createEvent($data);
            
            if ($eventId) {
                // Send announcement if event is open for registration
                if ($data['status'] === 'open_registration') {
                    $this->sendEventAnnouncement($eventId);
                }
                
                // Log activity
                $this->logEventActivity($eventId, $createdBy, 'created', 'Event created');
                
                return $eventId;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("EventService::createEventWithRegistration failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register user for event with validation
     */
    public function registerUserForEvent(int $eventId, int $userId, array $registrationData = []): bool
    {
        try {
            $event = $this->eventModel->getEventById($eventId);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            // Check if event is open for registration
            if ($event['status'] !== 'open_registration') {
                throw new Exception('Event registration is not currently open');
            }
            
            // Check if user is already registered
            $existingRegistration = $this->eventModel->getUserRegistration($eventId, $userId);
            if ($existingRegistration) {
                throw new Exception('User is already registered for this event');
            }
            
            // Check capacity
            if (!empty($event['max_participants'])) {
                $currentCount = $this->eventModel->getRegistrationCount($eventId);
                if ($currentCount >= $event['max_participants']) {
                    // Add to waitlist if available
                    return $this->addToWaitlist($eventId, $userId, $registrationData);
                }
            }
            
            // Check registration dates
            $now = time();
            if (!empty($event['registration_start']) && strtotime($event['registration_start']) > $now) {
                throw new Exception('Registration has not started yet');
            }
            if (!empty($event['registration_end']) && strtotime($event['registration_end']) < $now) {
                throw new Exception('Registration period has ended');
            }
            
            // Process payment if required
            if ($event['requires_payment'] && $event['registration_fee'] > 0) {
                $paymentResult = $this->processEventPayment($eventId, $userId, $event['registration_fee']);
                if (!$paymentResult['success']) {
                    throw new Exception('Payment processing failed: ' . $paymentResult['error']);
                }
                $registrationData['payment_id'] = $paymentResult['payment_id'];
            }
            
            // Register user
            $success = $this->eventModel->registerUser($eventId, $userId, 'registered', $registrationData);
            
            if ($success) {
                // Send confirmation notification
                $this->notificationService->sendEventRegistrationConfirmationNotification($eventId, $userId);
                
                // Log activity
                $this->logEventActivity($eventId, $userId, 'registered', 'User registered for event');
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("EventService::registerUserForEvent failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel event registration
     */
    public function cancelRegistration(int $eventId, int $userId, string $reason = ''): bool
    {
        try {
            $registration = $this->eventModel->getUserRegistration($eventId, $userId);
            
            if (!$registration) {
                throw new Exception('User is not registered for this event');
            }
            
            // Update registration status
            $success = $this->eventModel->updateParticipantStatus($eventId, $userId, 'cancelled');
            
            if ($success) {
                // Process refund if applicable
                if (!empty($registration['payment_id'])) {
                    $this->processEventRefund($registration['payment_id']);
                }
                
                // Promote waitlisted user if space available
                $this->promoteFromWaitlist($eventId);
                
                // Send cancellation confirmation
                $this->notificationService->sendEventCancellationConfirmationNotification($eventId, $userId);
                
                // Log activity
                $this->logEventActivity($eventId, $userId, 'cancelled', 'Registration cancelled: ' . $reason);
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("EventService::cancelRegistration failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check in participant for event
     */
    public function checkInParticipant(int $eventId, int $userId, int $checkedInBy): bool
    {
        try {
            $registration = $this->eventModel->getUserRegistration($eventId, $userId);
            
            if (!$registration) {
                throw new Exception('User is not registered for this event');
            }
            
            if ($registration['status'] !== 'confirmed' && $registration['status'] !== 'registered') {
                throw new Exception('User registration is not valid for check-in');
            }
            
            // Update status to attended
            $success = $this->eventModel->updateParticipantStatus($eventId, $userId, 'attended');
            
            if ($success) {
                // Log activity
                $this->logEventActivity($eventId, $checkedInBy, 'checked_in', "Checked in participant: User ID {$userId}");
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("EventService::checkInParticipant failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate event analytics and reports
     */
    public function generateEventAnalytics(int $eventId): array
    {
        try {
            $event = $this->eventModel->getEventById($eventId);
            $statistics = $this->eventModel->getEventStatistics($eventId);
            
            return [
                'event' => $event,
                'total_registered' => $statistics['total_registered'],
                'total_attended' => $statistics['total_attended'],
                'total_cancelled' => $statistics['total_cancelled'],
                'total_waitlisted' => $statistics['total_waitlisted'],
                'attendance_rate' => $statistics['total_registered'] > 0 ? 
                    round(($statistics['total_attended'] / $statistics['total_registered']) * 100, 2) : 0,
                'cancellation_rate' => $statistics['total_registered'] > 0 ? 
                    round(($statistics['total_cancelled'] / $statistics['total_registered']) * 100, 2) : 0,
                'revenue' => $this->calculateEventRevenue($eventId),
                'capacity_utilization' => !empty($event['max_participants']) ? 
                    round(($statistics['total_attended'] / $event['max_participants']) * 100, 2) : null,
                'registration_timeline' => $this->getRegistrationTimeline($eventId),
                'demographic_breakdown' => $this->getDemographicBreakdown($eventId)
            ];
            
        } catch (Exception $e) {
            error_log("EventService::generateEventAnalytics failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Send event reminders
     */
    public function sendEventReminders(int $eventId, int $hoursBeforeEvent = 24): int
    {
        try {
            $participants = $this->eventModel->getEventParticipants($eventId);
            $remindersSent = 0;
            
            foreach ($participants as $participant) {
                if (in_array($participant['status'], ['registered', 'confirmed'])) {
                    $this->notificationService->sendEventReminderNotification(
                        $eventId,
                        $participant['user_id'],
                        $hoursBeforeEvent
                    );
                    $remindersSent++;
                }
            }
            
            return $remindersSent;
            
        } catch (Exception $e) {
            error_log("EventService::sendEventReminders failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Export event data
     */
    public function exportEventData(int $eventId, string $format = 'csv'): array
    {
        try {
            $event = $this->eventModel->getEventById($eventId);
            $participants = $this->eventModel->getEventParticipants($eventId);
            
            $exportData = [];
            foreach ($participants as $participant) {
                $exportData[] = [
                    'Event Title' => $event['title'],
                    'Participant Name' => $participant['first_name'] . ' ' . $participant['last_name'],
                    'Email' => $participant['email'],
                    'Phone' => $participant['phone'],
                    'Registration Status' => $participant['status'],
                    'Registration Date' => $participant['registered_at'],
                    'Payment Status' => $participant['payment_status'] ?? 'N/A'
                ];
            }
            
            return [
                'data' => $exportData,
                'filename' => 'event_' . $eventId . '_participants_' . date('Y-m-d') . '.' . $format,
                'format' => $format
            ];
            
        } catch (Exception $e) {
            error_log("EventService::exportEventData failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add user to waitlist
     */
    private function addToWaitlist(int $eventId, int $userId, array $registrationData): bool
    {
        $success = $this->eventModel->registerUser($eventId, $userId, 'waitlisted', $registrationData);
        
        if ($success) {
            $this->notificationService->sendEventWaitlistNotification($eventId, $userId);
            $this->logEventActivity($eventId, $userId, 'waitlisted', 'User added to waitlist');
        }
        
        return $success;
    }

    /**
     * Promote user from waitlist
     */
    private function promoteFromWaitlist(int $eventId): void
    {
        $waitlistedUsers = $this->eventModel->getWaitlistedUsers($eventId, 1);
        
        if (!empty($waitlistedUsers)) {
            $userId = $waitlistedUsers[0]['user_id'];
            $this->eventModel->updateParticipantStatus($eventId, $userId, 'registered');
            
            $this->notificationService->sendEventWaitlistPromotionNotification($eventId, $userId);
            $this->logEventActivity($eventId, $userId, 'promoted', 'User promoted from waitlist');
        }
    }

    /**
     * Send event announcement
     */
    private function sendEventAnnouncement(int $eventId): void
    {
        $this->notificationService->sendEventAnnouncementNotification($eventId);
    }

    /**
     * Process event payment
     */
    private function processEventPayment(int $eventId, int $userId, float $amount): array
    {
        // This would integrate with payment gateways (PayPal, Stripe, etc.)
        // For now, return success placeholder
        return [
            'success' => true,
            'payment_id' => 'pay_' . uniqid(),
            'amount' => $amount,
            'currency' => 'USD'
        ];
    }

    /**
     * Process event refund
     */
    private function processEventRefund(string $paymentId): array
    {
        // This would integrate with payment gateways for refund processing
        return [
            'success' => true,
            'refund_id' => 'ref_' . uniqid(),
            'payment_id' => $paymentId
        ];
    }

    /**
     * Calculate event revenue
     */
    private function calculateEventRevenue(int $eventId): float
    {
        $participants = $this->eventModel->getEventParticipants($eventId);
        $revenue = 0;
        
        foreach ($participants as $participant) {
            if ($participant['status'] === 'attended' && !empty($participant['payment_amount'])) {
                $revenue += $participant['payment_amount'];
            }
        }
        
        return $revenue;
    }

    /**
     * Get registration timeline
     */
    private function getRegistrationTimeline(int $eventId): array
    {
        return $this->eventModel->getRegistrationTimeline($eventId);
    }

    /**
     * Get demographic breakdown
     */
    private function getDemographicBreakdown(int $eventId): array
    {
        return $this->eventModel->getDemographicBreakdown($eventId);
    }

    /**
     * Validate event data
     */
    private function validateEventData(array $data): void
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Event title is required';
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
        
        if (!empty($data['max_participants']) && (int)$data['max_participants'] < 1) {
            $errors[] = 'Maximum participants must be at least 1';
        }
        
        if (!empty($data['registration_fee']) && (float)$data['registration_fee'] < 0) {
            $errors[] = 'Registration fee cannot be negative';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
    }

    /**
     * Log event activity
     */
    private function logEventActivity(int $eventId, int $userId, string $action, string $description): void
    {
        $this->eventModel->logEventActivity($eventId, $userId, $action, $description);
    }

    /**
     * Generate UUID
     */
    private function generateUuid(string $prefix = ''): string
    {
        return $prefix . uniqid() . '_' . bin2hex(random_bytes(8));
    }
}