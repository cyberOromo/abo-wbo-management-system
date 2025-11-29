<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Meeting;
use App\Services\NotificationService;
use Exception;

class MeetingController extends BaseController
{
    private $meetingModel;
    private $notificationService;

    public function __construct()
    {
        parent::__construct();
        // Initialize services as needed
        // $this->meetingModel = new Meeting();
        // $this->notificationService = new NotificationService();
    }

    /**
     * Display meeting list
     */
    public function index()
    {
        try {
            $user = $this->getAuthUser();
            if (!$user) {
                return $this->redirect('/auth/login');
            }

            // Get meetings from database directly
            $db = \App\Utils\Database::getInstance();
            
            // Simple query for meetings with hierarchy filtering  
            $sql = "SELECT m.*, u.first_name, u.last_name,
                           CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                    FROM meetings m
                    LEFT JOIN users u ON m.created_by = u.id
                    WHERE 1=1";
            
            $params = [];
            
            // Apply hierarchy filtering based on user role and scope
            $scope = $this->getUserHierarchicalScope($user);
            
            if ($scope !== 'all') {
                if (isset($scope['godina']) && $scope['godina']) {
                    $sql .= " AND m.godina_id = ?";
                    $params[] = $scope['godina'];
                }
            }
            
            $sql .= " ORDER BY m.created_at DESC LIMIT 100";
            
            $meetings = $db->fetchAll($sql, $params);
            
            // Get meeting statistics
            $statsSql = "SELECT 
                            COUNT(*) as total,
                            COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled,
                            COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                            COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent
                        FROM meetings m
                        WHERE 1=1";
            
            if ($scope !== 'all' && isset($scope['godina']) && $scope['godina']) {
                $statsSql .= " AND m.godina_id = ?";
                $statsParams = [$scope['godina']];
            } else {
                $statsParams = [];
            }
            
            $stats = $db->fetch($statsSql, $statsParams) ?: [];
            
            $data = [
                'title' => 'Meetings Management',
                'meetings' => $meetings,
                'stats' => $stats,
                'user' => $user,
                'scope' => $scope
            ];
            
            return echo $this->render('meetings/index_modern', $data);
            
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to load meetings: ' . $e->getMessage());
        }
    }

    /**
     * Show create meeting form
     */
    public function create()
    {
        try {
            $this->requireAuth();
            
            // Check permissions
            if (!$this->canCreateMeeting()) {
                $this->setError('You do not have permission to create meetings');
                return $this->redirect('/meetings');
            }
            
            $data = [
                'meeting' => null,
                'isEdit' => false
            ];
            
            echo $this->render('meetings/create', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load create form: ' . $e->getMessage());
            $this->redirect('/meetings');
        }
    }

    /**
     * Store new meeting
     */
    public function store()
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            // Check permissions
            if (!$this->canCreateMeeting()) {
                throw new Exception('You do not have permission to create meetings');
            }
            
            $data = $this->validateMeetingData($_POST);
            $data['created_by'] = $this->user['id'];
            
            $meetingId = $this->meetingModel->createMeeting($data);
            
            if ($meetingId) {
                // Send notifications to participants
                if (!empty($data['participants'])) {
                    $this->notificationService->sendMeetingInvitationNotification($meetingId, $data['participants']);
                }
                
                $this->setSuccess('Meeting created successfully');
                $this->redirect('/meetings/' . $meetingId);
            } else {
                throw new Exception('Failed to create meeting');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to create meeting: ' . $e->getMessage());
            echo $this->render('meetings/create', ['meeting' => $_POST, 'isEdit' => false]);
        }
    }

    /**
     * Show specific meeting
     */
    public function show($id)
    {
        try {
            $this->requireAuth();
            
            $meeting = $this->meetingModel->getMeetingById($id);
            
            if (!$meeting) {
                $this->setError('Meeting not found');
                return $this->redirect('/meetings');
            }
            
            // Check access permissions
            if (!$this->canViewMeeting($meeting)) {
                $this->setError('You do not have permission to view this meeting');
                return $this->redirect('/meetings');
            }
            
            $participants = $this->meetingModel->getMeetingParticipants($id);
            $activities = $this->meetingModel->getMeetingActivities($id);
            
            $data = [
                'meeting' => $meeting,
                'participants' => $participants,
                'activities' => $activities,
                'canEdit' => $this->canEditMeeting($meeting),
                'canDelete' => $this->canDeleteMeeting($meeting)
            ];
            
            echo $this->render('meetings/show', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load meeting: ' . $e->getMessage());
            $this->redirect('/meetings');
        }
    }

    /**
     * Show edit meeting form
     */
    public function edit($id)
    {
        try {
            $this->requireAuth();
            
            $meeting = $this->meetingModel->getMeetingById($id);
            
            if (!$meeting) {
                $this->setError('Meeting not found');
                return $this->redirect('/meetings');
            }
            
            if (!$this->canEditMeeting($meeting)) {
                $this->setError('You do not have permission to edit this meeting');
                return $this->redirect('/meetings/' . $id);
            }
            
            $data = [
                'meeting' => $meeting,
                'isEdit' => true
            ];
            
            echo $this->render('meetings/edit', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load edit form: ' . $e->getMessage());
            $this->redirect('/meetings');
        }
    }

    /**
     * Update meeting
     */
    public function update($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $meeting = $this->meetingModel->getMeetingById($id);
            
            if (!$meeting) {
                throw new Exception('Meeting not found');
            }
            
            if (!$this->canEditMeeting($meeting)) {
                throw new Exception('You do not have permission to edit this meeting');
            }
            
            $data = $this->validateMeetingData($_POST);
            
            $success = $this->meetingModel->updateMeeting($id, $data);
            
            if ($success) {
                // Log activity
                $this->meetingModel->logMeetingActivity($id, $this->user['id'], 'updated', 'Meeting details updated');
                
                $this->setSuccess('Meeting updated successfully');
                $this->redirect('/meetings/' . $id);
            } else {
                throw new Exception('Failed to update meeting');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to update meeting: ' . $e->getMessage());
            echo $this->render('meetings/edit', ['meeting' => array_merge($meeting, $_POST), 'isEdit' => true]);
        }
    }

    /**
     * Delete meeting
     */
    public function delete($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $meeting = $this->meetingModel->getMeetingById($id);
            
            if (!$meeting) {
                throw new Exception('Meeting not found');
            }
            
            if (!$this->canDeleteMeeting($meeting)) {
                throw new Exception('You do not have permission to delete this meeting');
            }
            
            $success = $this->meetingModel->deleteMeeting($id);
            
            if ($success) {
                $this->setSuccess('Meeting deleted successfully');
                $this->redirect('/meetings');
            } else {
                throw new Exception('Failed to delete meeting');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to delete meeting: ' . $e->getMessage());
            $this->redirect('/meetings/' . $id);
        }
    }

    /**
     * Join meeting
     */
    public function join($id)
    {
        try {
            $this->requireAuth();
            
            $meeting = $this->meetingModel->getMeetingById($id);
            
            if (!$meeting) {
                $this->setError('Meeting not found');
                return $this->redirect('/meetings');
            }
            
            // Add user as participant if not already added
            $this->meetingModel->addParticipant($id, $this->user['id'], 'accepted');
            
            // Log activity
            $this->meetingModel->logMeetingActivity($id, $this->user['id'], 'joined', 'Joined the meeting');
            
            // Redirect to meeting platform or show join information
            if ($meeting['platform'] === 'zoom' && !empty($meeting['zoom_meeting_url'])) {
                header('Location: ' . $meeting['zoom_meeting_url']);
                exit;
            } else {
                $this->setSuccess('You have joined the meeting');
                $this->redirect('/meetings/' . $id);
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to join meeting: ' . $e->getMessage());
            $this->redirect('/meetings/' . $id);
        }
    }

    /**
     * Update participant status
     */
    public function updateParticipantStatus($meetingId, $userId)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $status = $_POST['status'] ?? 'invited';
            
            $meeting = $this->meetingModel->getMeetingById($meetingId);
            
            if (!$meeting) {
                throw new Exception('Meeting not found');
            }
            
            // Check permissions
            if (!$this->canManageMeetingParticipants($meeting) && $userId != $this->user['id']) {
                throw new Exception('You do not have permission to update participant status');
            }
            
            $success = $this->meetingModel->updateParticipantStatus($meetingId, $userId, $status);
            
            if ($success) {
                $this->setSuccess('Participant status updated successfully');
            } else {
                throw new Exception('Failed to update participant status');
            }
            
            $this->redirect('/meetings/' . $meetingId);
            
        } catch (Exception $e) {
            $this->setError('Failed to update participant status: ' . $e->getMessage());
            $this->redirect('/meetings/' . $meetingId);
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
                case 'conflicts':
                    return $this->apiCheckConflicts();
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
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        
        $meetings = $this->meetingModel->getMeetingsByScope($scope, $scopeId, $status, 'all', $page, $limit);
        $total = $this->meetingModel->getMeetingsCount($scope, $scopeId, $status);
        
        $this->jsonResponse([
            'meetings' => $meetings,
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
            throw new Exception('Meeting ID is required');
        }
        
        $meeting = $this->meetingModel->getMeetingById($id);
        
        if (!$meeting) {
            throw new Exception('Meeting not found');
        }
        
        if (!$this->canViewMeeting($meeting)) {
            throw new Exception('You do not have permission to view this meeting');
        }
        
        $participants = $this->meetingModel->getMeetingParticipants($id);
        
        $this->jsonResponse([
            'meeting' => $meeting,
            'participants' => $participants
        ]);
    }

    private function apiCheckConflicts()
    {
        $startDatetime = $_GET['start_datetime'] ?? null;
        $endDatetime = $_GET['end_datetime'] ?? null;
        $excludeId = $_GET['exclude_id'] ?? null;
        
        if (!$startDatetime || !$endDatetime) {
            throw new Exception('Start and end datetime are required');
        }
        
        $conflicts = $this->meetingModel->getMeetingConflicts($startDatetime, $endDatetime, $excludeId);
        
        $this->jsonResponse(['conflicts' => $conflicts]);
    }

    /**
     * Validate meeting data
     */
    private function validateMeetingData($data)
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
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
        
        // Clean and structure data
        $cleanData = [
            'title' => trim($data['title']),
            'description' => trim($data['description'] ?? ''),
            'meeting_type' => $data['meeting_type'] ?? 'regular',
            'level_scope' => $data['level_scope'] ?? $this->user['level_scope'],
            'scope_id' => $data['scope_id'] ?? $this->user['scope_id'],
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'timezone' => $data['timezone'] ?? 'UTC',
            'platform' => $data['platform'] ?? 'in_person',
            'location' => trim($data['location'] ?? ''),
            'max_participants' => !empty($data['max_participants']) ? (int)$data['max_participants'] : null,
            'is_public' => !empty($data['is_public']) ? 1 : 0,
            'requires_approval' => !empty($data['requires_approval']) ? 1 : 0,
            'agenda' => !empty($data['agenda']) ? json_encode($data['agenda']) : null,
            'tags' => !empty($data['tags']) ? json_encode(explode(',', $data['tags'])) : null
        ];
        
        // Handle Zoom integration
        if ($cleanData['platform'] === 'zoom') {
            $cleanData['zoom_meeting_id'] = $data['zoom_meeting_id'] ?? null;
            $cleanData['zoom_meeting_url'] = $data['zoom_meeting_url'] ?? null;
            $cleanData['zoom_password'] = $data['zoom_password'] ?? null;
        }
        
        return $cleanData;
    }

    /**
     * Permission checks
     */
    private function canCreateMeeting()
    {
        return in_array($this->user['role'], ['admin', 'leader', 'secretary']);
    }
    
    private function canViewMeeting($meeting)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($meeting['created_by'] == $this->user['id']) return true;
        if ($meeting['is_public']) return true;
        
        // Check if user is in the same scope or parent scope
        return $this->isInScopeHierarchy($meeting['level_scope'], $meeting['scope_id']);
    }
    
    private function canEditMeeting($meeting)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($meeting['created_by'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canDeleteMeeting($meeting)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($meeting['created_by'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canManageMeetingParticipants($meeting)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($meeting['created_by'] == $this->user['id']) return true;
        
        // Check if user is moderator
        $moderators = json_decode($meeting['moderators'] ?? '[]', true);
        return in_array($this->user['id'], $moderators);
    }
}