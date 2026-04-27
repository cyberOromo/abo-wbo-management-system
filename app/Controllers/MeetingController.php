<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Meeting;
use App\Services\AttachmentUploadService;
use App\Services\NotificationService;
use Exception;

class MeetingController extends BaseController
{
    private $meetingModel;
    private $notificationService;
    private AttachmentUploadService $attachmentUploadService;
    private array $user = [];

    public function __construct()
    {
        parent::__construct();
        $this->meetingModel = new Meeting();
        $this->notificationService = new NotificationService();
        $this->attachmentUploadService = new AttachmentUploadService();
        $this->user = $this->getAuthUser() ?? [];
    }
    
    private function syncCurrentUser(): void
    {
        $this->user = $this->getAuthUser() ?? [];
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
            $hasCreatedBy = $db->columnExists('meetings', 'created_by');
            $hasOrganizedBy = $db->columnExists('meetings', 'organized_by');
            $ownerColumn = $hasOrganizedBy ? 'organized_by' : ($hasCreatedBy ? 'created_by' : null);
            
            // Simple query for meetings with hierarchy filtering  
            $sql = "SELECT m.*";

            if ($ownerColumn !== null) {
                $sql .= ", u.first_name, u.last_name,
                           CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as created_by_name
                        FROM meetings m
                        LEFT JOIN users u ON m.{$ownerColumn} = u.id
                        LEFT JOIN user_assignments meeting_scope_ua ON meeting_scope_ua.user_id = m.{$ownerColumn} AND meeting_scope_ua.status = 'active'";
            } else {
                $sql .= ", NULL as first_name, NULL as last_name, '' as created_by_name
                        FROM meetings m";
            }

            $sql .= "
                    WHERE 1=1";
            
            $params = [];
            
            // Apply hierarchy filtering based on user role and scope
            $scope = $this->getResolvedMeetingUserScope((int) $user['id']);
            
            if (($user['role'] ?? null) !== 'admin') {
                $sql = $this->applyMeetingScopeFilter($sql, $params, $scope);
            }
            
            $sql .= " ORDER BY m.created_at DESC LIMIT 100";
            
            $meetings = $db->fetchAll($sql, $params);
            
            // Get meeting statistics
            $statsSql = "SELECT 
                            COUNT(*) as total,
                            COUNT(CASE WHEN m.status = 'scheduled' THEN 1 END) as scheduled,
                            COUNT(CASE WHEN m.status = 'in_progress' THEN 1 END) as in_progress,
                            COUNT(CASE WHEN m.status = 'completed' THEN 1 END) as completed,
                            COUNT(CASE WHEN m.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent
                        FROM meetings m";

            if ($ownerColumn !== null) {
                $statsSql .= "
                        LEFT JOIN user_assignments meeting_scope_ua ON meeting_scope_ua.user_id = m.{$ownerColumn} AND meeting_scope_ua.status = 'active'";
            }

            $statsSql .= "
                        WHERE 1=1";

            $statsParams = [];

            if (($user['role'] ?? null) !== 'admin') {
                $statsSql = $this->applyMeetingScopeFilter($statsSql, $statsParams, $scope);
            }
            
            $stats = $db->fetch($statsSql, $statsParams) ?: [];
            
            $displayStats = [
                'total' => (int) ($stats['total'] ?? count($meetings)),
                'scheduled' => (int) ($stats['scheduled'] ?? 0),
                'in_progress' => (int) ($stats['in_progress'] ?? 0),
                'completed' => (int) ($stats['completed'] ?? 0),
                'recent' => (int) ($stats['recent'] ?? 0)
            ];

            $data = [
                'title' => 'Meetings Management',
                'meetings' => $meetings,
                'stats' => $displayStats,
                'user' => $user,
                'scope' => $scope,
                'can_create_meeting' => false
            ];
            
            return $this->render('meetings/index_shell', $data);
            
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to load meetings: ' . $e->getMessage());
        }
    }

    private function getResolvedMeetingUserScope(int $userId): array
    {
        $db = \App\Utils\Database::getInstance();
        $scope = $db->fetch(
            "SELECT ua.*, go.name as global_name, gd.name as godina_name, ga.name as gamta_name, gu.name as gurmu_name
             FROM user_assignments ua
             LEFT JOIN globals go ON ua.global_id = go.id
             LEFT JOIN godinas gd ON ua.godina_id = gd.id
             LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
             LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
             WHERE ua.user_id = ? AND ua.status = 'active'
             LIMIT 1",
            [$userId]
        ) ?: [];

        if (!empty($scope)) {
            $scope['scope_name'] = $scope['gurmu_name']
                ?? $scope['gamta_name']
                ?? $scope['godina_name']
                ?? $scope['global_name']
                ?? 'Current executive scope';
        }

        return $scope;
    }

    private function applyMeetingScopeFilter(string $sql, array &$params, array $scope): string
    {
        $scopeColumn = $this->getMeetingScopeColumn((string) ($scope['level_scope'] ?? ''));
        $scopeValue = $scopeColumn !== null ? ($scope[$scopeColumn] ?? null) : null;

        if ($scopeColumn !== null && $scopeValue !== null) {
            $sql .= " AND meeting_scope_ua.{$scopeColumn} = ?";
            $params[] = (int) $scopeValue;
        }

        return $sql;
    }

    private function getMeetingScopeColumn(string $levelScope): ?string
    {
        return match ($levelScope) {
            'global' => 'global_id',
            'godina' => 'godina_id',
            'gamta' => 'gamta_id',
            'gurmu' => 'gurmu_id',
            default => null,
        };
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
            
            $result = $this->meetingModel->createMeeting($data);

            if (($result['success'] ?? false) && !empty($result['meeting_id'])) {
                $meetingId = (int) $result['meeting_id'];
                // Send notifications to participants
                if (!empty($data['participants'])) {
                    $this->notificationService->sendMeetingInvitationNotification($meetingId, $data['participants']);
                }
                
                $this->setSuccess('Meeting created successfully');
                $this->redirect('/meetings/' . $meetingId);
            } else {
                throw new Exception((string) ($result['message'] ?? 'Failed to create meeting'));
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
            $this->syncCurrentUser();
            
            $meeting = $this->meetingModel->getMeetingById($id);
            
            if (!$meeting) {
                $this->notFoundResponse('Meeting not found.');
                return;
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
            
        } catch (\Throwable $e) {
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
            $this->syncCurrentUser();
            
            $meeting = $this->meetingModel->getMeetingById($id);
            
            if (!$meeting) {
                $this->notFoundResponse('Meeting not found.');
                return;
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
            
        } catch (\Throwable $e) {
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
            $this->syncCurrentUser();
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

            $existingAttachments = is_array($meeting['attachments'] ?? null) ? $meeting['attachments'] : [];
            $newAttachments = $this->attachmentUploadService->uploadMany($_FILES['attachments'] ?? [], 'meeting-attachments');
            if ($existingAttachments !== [] || $newAttachments !== []) {
                $data['attachments'] = array_values(array_merge($existingAttachments, $newAttachments));
            }
            
            $success = $this->meetingModel->updateMeeting($id, $data);
            
            if ($success) {
                // Log activity
                $this->meetingModel->logMeetingActivity($id, $this->user['id'], 'updated', 'Meeting details updated');
                
                $this->setSuccess('Meeting updated successfully');
                $this->redirect('/meetings/' . $id);
            } else {
                throw new Exception('Failed to update meeting');
            }
            
        } catch (\Throwable $e) {
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
        if ($this->getMeetingOwnerUserId($meeting) === (int) $this->user['id']) return true;
        if (!empty($meeting['is_public'])) return true;
        
        return $this->meetingMatchesScope($meeting, $this->getResolvedMeetingUserScope((int) $this->user['id']));
    }
    
    private function canEditMeeting($meeting)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($this->getMeetingOwnerUserId($meeting) === (int) $this->user['id']) return true;
        
        return false;
    }
    
    private function canDeleteMeeting($meeting)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($this->getMeetingOwnerUserId($meeting) === (int) $this->user['id']) return true;
        
        return false;
    }
    
    private function canManageMeetingParticipants($meeting)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($this->getMeetingOwnerUserId($meeting) === (int) $this->user['id']) return true;
        
        // Check if user is moderator
        $moderators = json_decode($meeting['moderators'] ?? '[]', true);
        return in_array($this->user['id'], $moderators);
    }

    private function getMeetingOwnerUserId(array $meeting): int
    {
        return (int) ($meeting['organized_by'] ?? $meeting['created_by'] ?? 0);
    }

    private function meetingMatchesScope(array $meeting, array $scope): bool
    {
        $ownerUserId = $this->getMeetingOwnerUserId($meeting);
        if ($ownerUserId <= 0) {
            return false;
        }

        $scopeColumn = $this->getMeetingScopeColumn((string) ($scope['level_scope'] ?? ''));
        $scopeValue = $scopeColumn !== null ? ($scope[$scopeColumn] ?? null) : null;

        if ($scopeColumn === null || $scopeValue === null) {
            return false;
        }

        $assignment = \App\Utils\Database::getInstance()->fetch(
            "SELECT global_id, godina_id, gamta_id, gurmu_id FROM user_assignments WHERE user_id = ? AND status = 'active' LIMIT 1",
            [$ownerUserId]
        );

        return !empty($assignment) && (int) ($assignment[$scopeColumn] ?? 0) === (int) $scopeValue;
    }
}