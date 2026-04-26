<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Event;
use App\Services\NotificationService;
use Exception;

class EventController extends BaseController
{
    private $eventModel;
    private $notificationService;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display event list
     */
    public function index()
    {
        try {
            $user = $this->getAuthUser();
            if (!$user) {
                return $this->redirect('/auth/login');
            }

            // Get events from database directly
            $db = \App\Utils\Database::getInstance();
            $hasCreatedBy = $db->columnExists('events', 'created_by');
            $hasGodinaId = $db->columnExists('events', 'godina_id');
            $hasScopeId = $db->columnExists('events', 'scope_id');
            $hasLevelScope = $db->columnExists('events', 'level_scope');
            
            // Simple query for events with hierarchy filtering
            $sql = "SELECT e.*";

            if ($hasCreatedBy) {
                $sql .= ", u.first_name, u.last_name,
                           CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as created_by_name
                        FROM events e
                        LEFT JOIN users u ON e.created_by = u.id";
            } else {
                $sql .= ", NULL as first_name, NULL as last_name, '' as created_by_name
                        FROM events e";
            }

            $sql .= "
                    WHERE 1=1";
            
            $params = [];
            
            // Apply hierarchy filtering based on user role and scope
            $scope = $this->getUserHierarchicalScope($user);
            
            if ($scope !== 'all') {
                if (isset($scope['godina']) && $scope['godina']) {
                    if ($hasGodinaId) {
                        $sql .= " AND e.godina_id = ?";
                        $params[] = $scope['godina'];
                    } elseif ($hasLevelScope && $hasScopeId) {
                        $sql .= " AND e.level_scope = 'godina' AND e.scope_id = ?";
                        $params[] = $scope['godina'];
                    }
                }
            }
            
            $sql .= " ORDER BY e.created_at DESC LIMIT 100";
            
            $events = $db->fetchAll($sql, $params);
            
            // Get event statistics
            $statsSql = "SELECT 
                            COUNT(*) as total,
                            COUNT(CASE WHEN status = 'upcoming' THEN 1 END) as upcoming,
                            COUNT(CASE WHEN status = 'ongoing' THEN 1 END) as ongoing,
                            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                            COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent
                        FROM events e
                        WHERE 1=1";
            
            if ($scope !== 'all' && isset($scope['godina']) && $scope['godina']) {
                if ($hasGodinaId) {
                    $statsSql .= " AND e.godina_id = ?";
                    $statsParams = [$scope['godina']];
                } elseif ($hasLevelScope && $hasScopeId) {
                    $statsSql .= " AND e.level_scope = 'godina' AND e.scope_id = ?";
                    $statsParams = [$scope['godina']];
                } else {
                    $statsParams = [];
                }
            } else {
                $statsParams = [];
            }
            
            $stats = $db->fetch($statsSql, $statsParams) ?: [];
            
            $data = [
                'title' => 'Events Management',
                'events' => $events,
                'stats' => $stats,
                'user' => $user,
                'scope' => $scope
            ];
            
            return $this->render('events/index_shell', $data);
            
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to load events: ' . $e->getMessage());
        }
    }

    /**
     * Show create event form
     */
    public function create()
    {
        try {
            $this->requireAuth();
            
            // Check permissions
            if (!$this->canCreateEvent()) {
                $this->setError('You do not have permission to create events');
                return $this->redirect('/events');
            }
            
            $data = [
                'event' => null,
                'isEdit' => false
            ];
            
            echo $this->render('events/create', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load create form: ' . $e->getMessage());
            $this->redirect('/events');
        }
    }

    /**
     * Store new event
     */
    public function store()
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            // Check permissions
            if (!$this->canCreateEvent()) {
                throw new Exception('You do not have permission to create events');
            }
            
            $data = $this->validateEventData($_POST);
            $data['created_by'] = $this->user['id'];
            
            $eventId = $this->eventModel->createEvent($data);
            
            if ($eventId) {
                // Send notification if event is published
                if ($data['status'] === 'open_registration') {
                    $this->notificationService->sendEventAnnouncementNotification($eventId);
                }
                
                $this->setSuccess('Event created successfully');
                $this->redirect('/events/' . $eventId);
            } else {
                throw new Exception('Failed to create event');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to create event: ' . $e->getMessage());
            echo $this->render('events/create', ['event' => $_POST, 'isEdit' => false]);
        }
    }

    /**
     * Show specific event
     */
    public function show($id)
    {
        try {
            $this->requireAuth();
            
            $event = $this->eventModel->getEventById($id);
            
            if (!$event) {
                $this->setError('Event not found');
                return $this->redirect('/events');
            }
            
            // Check access permissions
            if (!$this->canViewEvent($event)) {
                $this->setError('You do not have permission to view this event');
                return $this->redirect('/events');
            }
            
            $participants = $this->eventModel->getEventParticipants($id);
            $statistics = $this->eventModel->getEventStatistics($id);
            $activities = $this->eventModel->getEventActivities($id);
            $userRegistration = $this->eventModel->getUserRegistration($id, $this->user['id']);
            
            $data = [
                'event' => $event,
                'participants' => $participants,
                'statistics' => $statistics,
                'activities' => $activities,
                'userRegistration' => $userRegistration,
                'canEdit' => $this->canEditEvent($event),
                'canDelete' => $this->canDeleteEvent($event),
                'canRegister' => $this->canRegisterForEvent($event, $userRegistration)
            ];
            
            echo $this->render('events/show', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load event: ' . $e->getMessage());
            $this->redirect('/events');
        }
    }

    /**
     * Show edit event form
     */
    public function edit($id)
    {
        try {
            $this->requireAuth();
            
            $event = $this->eventModel->getEventById($id);
            
            if (!$event) {
                $this->setError('Event not found');
                return $this->redirect('/events');
            }
            
            if (!$this->canEditEvent($event)) {
                $this->setError('You do not have permission to edit this event');
                return $this->redirect('/events/' . $id);
            }
            
            $data = [
                'event' => $event,
                'isEdit' => true
            ];
            
            echo $this->render('events/edit', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load edit form: ' . $e->getMessage());
            $this->redirect('/events');
        }
    }

    /**
     * Update event
     */
    public function update($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $event = $this->eventModel->getEventById($id);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            if (!$this->canEditEvent($event)) {
                throw new Exception('You do not have permission to edit this event');
            }
            
            $data = $this->validateEventData($_POST);
            
            $success = $this->eventModel->updateEvent($id, $data);
            
            if ($success) {
                // Log activity
                $this->eventModel->logEventActivity($id, $this->user['id'], 'updated', 'Event details updated');
                
                $this->setSuccess('Event updated successfully');
                $this->redirect('/events/' . $id);
            } else {
                throw new Exception('Failed to update event');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to update event: ' . $e->getMessage());
            echo $this->render('events/edit', ['event' => array_merge($event, $_POST), 'isEdit' => true]);
        }
    }

    /**
     * Delete event
     */
    public function delete($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $event = $this->eventModel->getEventById($id);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            if (!$this->canDeleteEvent($event)) {
                throw new Exception('You do not have permission to delete this event');
            }
            
            $success = $this->eventModel->deleteEvent($id);
            
            if ($success) {
                $this->setSuccess('Event deleted successfully');
                $this->redirect('/events');
            } else {
                throw new Exception('Failed to delete event');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to delete event: ' . $e->getMessage());
            $this->redirect('/events/' . $id);
        }
    }

    /**
     * Register for event
     */
    public function register($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $event = $this->eventModel->getEventById($id);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            $existingRegistration = $this->eventModel->getUserRegistration($id, $this->user['id']);
            
            if ($existingRegistration) {
                throw new Exception('You are already registered for this event');
            }
            
            if (!$this->canRegisterForEvent($event, null)) {
                throw new Exception('Registration is not available for this event');
            }
            
            $registrationData = $_POST['registration_data'] ?? [];
            
            $success = $this->eventModel->registerUser($id, $this->user['id'], 'registered', $registrationData);
            
            if ($success) {
                // Log activity
                $this->eventModel->logEventActivity($id, $this->user['id'], 'registered', 'User registered for event');
                
                // Send confirmation notification
                $this->notificationService->sendEventRegistrationConfirmationNotification($id, $this->user['id']);
                
                $this->setSuccess('Successfully registered for the event');
                $this->redirect('/events/' . $id);
            } else {
                throw new Exception('Failed to register for event');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to register: ' . $e->getMessage());
            $this->redirect('/events/' . $id);
        }
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $event = $this->eventModel->getEventById($id);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            $registration = $this->eventModel->getUserRegistration($id, $this->user['id']);
            
            if (!$registration) {
                throw new Exception('You are not registered for this event');
            }
            
            $success = $this->eventModel->updateParticipantStatus($id, $this->user['id'], 'cancelled');
            
            if ($success) {
                // Log activity
                $this->eventModel->logEventActivity($id, $this->user['id'], 'cancelled', 'User cancelled registration');
                
                $this->setSuccess('Registration cancelled successfully');
                $this->redirect('/events/' . $id);
            } else {
                throw new Exception('Failed to cancel registration');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to cancel registration: ' . $e->getMessage());
            $this->redirect('/events/' . $id);
        }
    }

    /**
     * Update participant status (admin only)
     */
    public function updateParticipantStatus($eventId, $userId)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $status = $_POST['status'] ?? 'registered';
            
            $event = $this->eventModel->getEventById($eventId);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            // Check permissions
            if (!$this->canManageEventParticipants($event)) {
                throw new Exception('You do not have permission to manage participants');
            }
            
            $success = $this->eventModel->updateParticipantStatus($eventId, $userId, $status);
            
            if ($success) {
                // Log activity
                $this->eventModel->logEventActivity($eventId, $this->user['id'], 'participant_status_updated', "Participant status changed to: {$status}");
                
                $this->setSuccess('Participant status updated successfully');
            } else {
                throw new Exception('Failed to update participant status');
            }
            
            $this->redirect('/events/' . $eventId);
            
        } catch (Exception $e) {
            $this->setError('Failed to update participant status: ' . $e->getMessage());
            $this->redirect('/events/' . $eventId);
        }
    }

    /**
     * API endpoints
     */
    public function api($action = 'list')
    {
        try {
            $this->requireAuth();
            $this->setJsonResponse();
            
            switch ($action) {
                case 'list':
                    return $this->apiList();
                case 'show':
                    return $this->apiShow();
                case 'create':
                    return $this->apiCreate();
                case 'update':
                    return $this->apiUpdate();
                case 'delete':
                    return $this->apiDelete();
                case 'register':
                    return $this->apiRegister();
                case 'statistics':
                    return $this->apiStatistics();
                default:
                    throw new Exception('Invalid API action');
            }
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    private function apiList()
    {
        $scope = $_GET['scope'] ?? $this->user['level_scope'];
        $scopeId = $_GET['scope_id'] ?? $this->user['scope_id'];
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        
        $events = $this->eventModel->searchEvents($search, $scope, $scopeId, $status, 'all', $page, $limit);
        $total = $this->eventModel->getEventsCount($scope, $scopeId, $status, 'all', $search);
        
        $this->jsonResponse([
            'events' => $events,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }

    private function apiShow()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Event ID is required');
        }
        
        $event = $this->eventModel->getEventById($id);
        
        if (!$event) {
            throw new Exception('Event not found');
        }
        
        if (!$this->canViewEvent($event)) {
            throw new Exception('You do not have permission to view this event');
        }
        
        $statistics = $this->eventModel->getEventStatistics($id);
        $userRegistration = $this->eventModel->getUserRegistration($id, $this->user['id']);
        
        $this->jsonResponse([
            'event' => $event,
            'statistics' => $statistics,
            'userRegistration' => $userRegistration,
            'canRegister' => $this->canRegisterForEvent($event, $userRegistration)
        ]);
    }

    private function apiStatistics()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Event ID is required');
        }
        
        $event = $this->eventModel->getEventById($id);
        
        if (!$event) {
            throw new Exception('Event not found');
        }
        
        if (!$this->canViewEvent($event)) {
            throw new Exception('You do not have permission to view this event');
        }
        
        $statistics = $this->eventModel->getEventStatistics($id);
        
        $this->jsonResponse(['statistics' => $statistics]);
    }

    /**
     * Validate event data
     */
    private function validateEventData($data)
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
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
        
        // Clean and structure data
        $cleanData = [
            'title' => trim($data['title']),
            'description' => trim($data['description'] ?? ''),
            'event_type' => $data['event_type'] ?? 'social',
            'level_scope' => $data['level_scope'] ?? $this->user['level_scope'],
            'scope_id' => $data['scope_id'] ?? $this->user['scope_id'],
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'timezone' => $data['timezone'] ?? 'UTC',
            'venue_name' => trim($data['venue_name'] ?? ''),
            'venue_address' => trim($data['venue_address'] ?? ''),
            'venue_city' => trim($data['venue_city'] ?? ''),
            'venue_country' => trim($data['venue_country'] ?? ''),
            'is_virtual' => !empty($data['is_virtual']) ? 1 : 0,
            'virtual_link' => trim($data['virtual_link'] ?? ''),
            'registration_type' => $data['registration_type'] ?? 'open',
            'registration_start' => $data['registration_start'] ?? null,
            'registration_end' => $data['registration_end'] ?? null,
            'max_participants' => !empty($data['max_participants']) ? (int)$data['max_participants'] : null,
            'min_participants' => !empty($data['min_participants']) ? (int)$data['min_participants'] : 0,
            'registration_fee' => !empty($data['registration_fee']) ? (float)$data['registration_fee'] : 0.00,
            'currency' => $data['currency'] ?? 'USD',
            'requires_payment' => !empty($data['requires_payment']) ? 1 : 0,
            'contact_email' => trim($data['contact_email'] ?? ''),
            'contact_phone' => trim($data['contact_phone'] ?? ''),
            'status' => $data['status'] ?? 'planning',
            'tags' => !empty($data['tags']) ? json_encode(explode(',', $data['tags'])) : null
        ];
        
        // Handle JSON fields
        if (!empty($data['agenda'])) {
            $cleanData['agenda'] = json_encode($data['agenda']);
        }
        
        if (!empty($data['speakers'])) {
            $cleanData['speakers'] = json_encode($data['speakers']);
        }
        
        if (!empty($data['sponsors'])) {
            $cleanData['sponsors'] = json_encode($data['sponsors']);
        }
        
        if (!empty($data['requirements'])) {
            $cleanData['requirements'] = json_encode($data['requirements']);
        }
        
        if (!empty($data['organizers'])) {
            $cleanData['organizers'] = json_encode($data['organizers']);
        }
        
        return $cleanData;
    }

    /**
     * Permission checks
     */
    private function canCreateEvent()
    {
        return in_array($this->user['role'], ['admin', 'leader', 'secretary']);
    }
    
    private function canViewEvent($event)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($event['created_by'] == $this->user['id']) return true;
        
        // Check if user is in the same scope or parent scope
        return $this->isInScopeHierarchy($event['level_scope'], $event['scope_id']);
    }
    
    private function canEditEvent($event)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($event['created_by'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canDeleteEvent($event)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($event['created_by'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canRegisterForEvent($event, $existingRegistration)
    {
        if ($existingRegistration) return false;
        if ($event['status'] !== 'open_registration') return false;
        if ($event['registration_type'] === 'closed') return false;
        
        // Check registration dates
        $now = time();
        if (!empty($event['registration_start']) && strtotime($event['registration_start']) > $now) {
            return false;
        }
        if (!empty($event['registration_end']) && strtotime($event['registration_end']) < $now) {
            return false;
        }
        
        // Check capacity
        if (!empty($event['max_participants'])) {
            $statistics = $this->eventModel->getEventStatistics($event['id']);
            if ($statistics['total_registered'] >= $event['max_participants']) {
                return false;
            }
        }
        
        return true;
    }
    
    private function canManageEventParticipants($event)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($event['created_by'] == $this->user['id']) return true;
        
        // Check if user is organizer
        $organizers = json_decode($event['organizers'] ?? '[]', true);
        return in_array($this->user['id'], $organizers);
    }
}