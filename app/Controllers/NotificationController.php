<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Exception;

class NotificationController extends Controller
{
    private $notificationModel;
    private $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new Notification();
        $this->notificationService = new NotificationService();
    }

    /**
     * Display notification list
     */
    public function index()
    {
        try {
            $this->requireAuth();
            
            $status = $_GET['status'] ?? 'all';
            $type = $_GET['type'] ?? 'all';
            $priority = $_GET['priority'] ?? 'all';
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            
            // Get user's notifications
            $notifications = $this->notificationModel->getUserNotifications($this->user['id'], $status, $type, $priority, $page, $limit);
            $totalNotifications = $this->notificationModel->getUserNotificationsCount($this->user['id'], $status, $type, $priority);
            $unreadCount = $this->notificationModel->getUnreadCount($this->user['id']);
            
            $data = [
                'notifications' => $notifications,
                'total' => $totalNotifications,
                'unreadCount' => $unreadCount,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($totalNotifications / $limit),
                'filters' => [
                    'status' => $status,
                    'type' => $type,
                    'priority' => $priority
                ]
            ];
            
            $this->render('notifications/index', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load notifications: ' . $e->getMessage());
            $this->render('notifications/index', ['notifications' => [], 'total' => 0]);
        }
    }

    /**
     * Show create notification form (admin only)
     */
    public function create()
    {
        try {
            $this->requireAuth();
            
            // Check permissions
            if (!$this->canCreateNotification()) {
                $this->setError('You do not have permission to create notifications');
                return $this->redirect('/notifications');
            }
            
            $data = [
                'notification' => null,
                'isEdit' => false
            ];
            
            $this->render('notifications/create', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load create form: ' . $e->getMessage());
            $this->redirect('/notifications');
        }
    }

    /**
     * Store new notification
     */
    public function store()
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            // Check permissions
            if (!$this->canCreateNotification()) {
                throw new Exception('You do not have permission to create notifications');
            }
            
            $data = $this->validateNotificationData($_POST);
            $data['sender_id'] = $this->user['id'];
            
            $notificationId = $this->notificationModel->createNotification($data);
            
            if ($notificationId) {
                // Send notification immediately if scheduled for now
                if (empty($data['scheduled_at']) || strtotime($data['scheduled_at']) <= time()) {
                    $this->notificationService->processPendingNotifications();
                }
                
                $this->setSuccess('Notification created successfully');
                $this->redirect('/notifications/admin');
            } else {
                throw new Exception('Failed to create notification');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to create notification: ' . $e->getMessage());
            $this->render('notifications/create', ['notification' => $_POST, 'isEdit' => false]);
        }
    }

    /**
     * Show specific notification
     */
    public function show($id)
    {
        try {
            $this->requireAuth();
            
            $notification = $this->notificationModel->getNotificationById($id);
            
            if (!$notification) {
                $this->setError('Notification not found');
                return $this->redirect('/notifications');
            }
            
            // Check access permissions
            if (!$this->canViewNotification($notification)) {
                $this->setError('You do not have permission to view this notification');
                return $this->redirect('/notifications');
            }
            
            // Mark as read if it's for the current user
            if ($notification['recipient_id'] == $this->user['id'] && $notification['status'] === 'delivered') {
                $this->notificationModel->markAsRead($id);
            }
            
            $data = [
                'notification' => $notification,
                'canEdit' => $this->canEditNotification($notification),
                'canDelete' => $this->canDeleteNotification($notification)
            ];
            
            $this->render('notifications/show', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load notification: ' . $e->getMessage());
            $this->redirect('/notifications');
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $notification = $this->notificationModel->getNotificationById($id);
            
            if (!$notification) {
                throw new Exception('Notification not found');
            }
            
            if ($notification['recipient_id'] != $this->user['id']) {
                throw new Exception('You can only mark your own notifications as read');
            }
            
            $success = $this->notificationModel->markAsRead($id);
            
            if ($success) {
                $this->setSuccess('Notification marked as read');
            } else {
                throw new Exception('Failed to mark notification as read');
            }
            
            // Return JSON for AJAX requests
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/notifications');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->setError('Failed to mark notification as read: ' . $e->getMessage());
                $this->redirect('/notifications');
            }
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $success = $this->notificationModel->markAllAsRead($this->user['id']);
            
            if ($success) {
                $this->setSuccess('All notifications marked as read');
            } else {
                throw new Exception('Failed to mark all notifications as read');
            }
            
            // Return JSON for AJAX requests
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->redirect('/notifications');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->setError('Failed to mark all notifications as read: ' . $e->getMessage());
                $this->redirect('/notifications');
            }
        }
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $notification = $this->notificationModel->getNotificationById($id);
            
            if (!$notification) {
                throw new Exception('Notification not found');
            }
            
            if (!$this->canDeleteNotification($notification)) {
                throw new Exception('You do not have permission to delete this notification');
            }
            
            $success = $this->notificationModel->deleteNotification($id);
            
            if ($success) {
                $this->setSuccess('Notification deleted successfully');
                $this->redirect('/notifications');
            } else {
                throw new Exception('Failed to delete notification');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to delete notification: ' . $e->getMessage());
            $this->redirect('/notifications/' . $id);
        }
    }

    /**
     * Admin notification management
     */
    public function admin()
    {
        try {
            $this->requireAuth();
            
            // Check permissions
            if (!$this->canManageNotifications()) {
                $this->setError('You do not have permission to manage notifications');
                return $this->redirect('/notifications');
            }
            
            $status = $_GET['status'] ?? 'all';
            $type = $_GET['type'] ?? 'all';
            $scope = $_GET['scope'] ?? 'all';
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            
            // Get all notifications for admin view
            $notifications = $this->notificationModel->getAllNotifications($status, $type, $scope, $page, $limit);
            $totalNotifications = $this->notificationModel->getAllNotificationsCount($status, $type, $scope);
            $statistics = $this->notificationModel->getNotificationStatistics();
            
            $data = [
                'notifications' => $notifications,
                'total' => $totalNotifications,
                'statistics' => $statistics,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($totalNotifications / $limit),
                'filters' => [
                    'status' => $status,
                    'type' => $type,
                    'scope' => $scope
                ]
            ];
            
            $this->render('notifications/admin', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load admin notifications: ' . $e->getMessage());
            $this->render('notifications/admin', ['notifications' => [], 'total' => 0]);
        }
    }

    /**
     * Resend failed notification
     */
    public function resend($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            // Check permissions
            if (!$this->canManageNotifications()) {
                throw new Exception('You do not have permission to resend notifications');
            }
            
            $notification = $this->notificationModel->getNotificationById($id);
            
            if (!$notification) {
                throw new Exception('Notification not found');
            }
            
            if ($notification['status'] !== 'failed') {
                throw new Exception('Only failed notifications can be resent');
            }
            
            // Reset notification status to pending
            $success = $this->notificationModel->updateNotificationStatus($id, 'pending');
            
            if ($success) {
                // Process the notification
                $this->notificationService->processPendingNotifications();
                
                $this->setSuccess('Notification queued for resending');
            } else {
                throw new Exception('Failed to queue notification for resending');
            }
            
            $this->redirect('/notifications/admin');
            
        } catch (Exception $e) {
            $this->setError('Failed to resend notification: ' . $e->getMessage());
            $this->redirect('/notifications/admin');
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
                case 'unread':
                    return $this->apiUnread();
                case 'mark_read':
                    return $this->apiMarkAsRead();
                case 'mark_all_read':
                    return $this->apiMarkAllAsRead();
                case 'delete':
                    return $this->apiDelete();
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
        $status = $_GET['status'] ?? 'all';
        $type = $_GET['type'] ?? 'all';
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        
        $notifications = $this->notificationModel->getUserNotifications($this->user['id'], $status, $type, 'all', $page, $limit);
        $total = $this->notificationModel->getUserNotificationsCount($this->user['id'], $status, $type);
        
        $this->jsonResponse([
            'notifications' => $notifications,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }

    private function apiUnread()
    {
        $limit = min((int)($_GET['limit'] ?? 10), 50);
        
        $notifications = $this->notificationModel->getUnreadNotifications($this->user['id'], $limit);
        $unreadCount = $this->notificationModel->getUnreadCount($this->user['id']);
        
        $this->jsonResponse([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    private function apiMarkAsRead()
    {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Notification ID is required');
        }
        
        $notification = $this->notificationModel->getNotificationById($id);
        
        if (!$notification) {
            throw new Exception('Notification not found');
        }
        
        if ($notification['recipient_id'] != $this->user['id']) {
            throw new Exception('You can only mark your own notifications as read');
        }
        
        $success = $this->notificationModel->markAsRead($id);
        
        if ($success) {
            $unreadCount = $this->notificationModel->getUnreadCount($this->user['id']);
            $this->jsonResponse(['success' => true, 'unreadCount' => $unreadCount]);
        } else {
            throw new Exception('Failed to mark notification as read');
        }
    }

    private function apiMarkAllAsRead()
    {
        $success = $this->notificationModel->markAllAsRead($this->user['id']);
        
        if ($success) {
            $this->jsonResponse(['success' => true, 'unreadCount' => 0]);
        } else {
            throw new Exception('Failed to mark all notifications as read');
        }
    }

    private function apiStatistics()
    {
        if (!$this->canManageNotifications()) {
            throw new Exception('You do not have permission to view notification statistics');
        }
        
        $statistics = $this->notificationModel->getNotificationStatistics();
        
        $this->jsonResponse(['statistics' => $statistics]);
    }

    /**
     * Validate notification data
     */
    private function validateNotificationData($data)
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['message'])) {
            $errors[] = 'Message is required';
        }
        
        if (empty($data['type'])) {
            $errors[] = 'Type is required';
        }
        
        if (empty($data['level_scope'])) {
            $errors[] = 'Level scope is required';
        }
        
        if (empty($data['channels']) || !is_array($data['channels'])) {
            $errors[] = 'At least one notification channel is required';
        }
        
        if (!empty($data['scheduled_at']) && strtotime($data['scheduled_at']) === false) {
            $errors[] = 'Invalid scheduled date/time';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
        
        // Clean and structure data
        $cleanData = [
            'type' => $data['type'],
            'title' => trim($data['title']),
            'message' => trim($data['message']),
            'level_scope' => $data['level_scope'],
            'scope_id' => !empty($data['scope_id']) ? (int)$data['scope_id'] : null,
            'recipient_id' => !empty($data['recipient_id']) ? (int)$data['recipient_id'] : null,
            'channels' => json_encode($data['channels']),
            'priority' => $data['priority'] ?? 'normal',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'action_url' => trim($data['action_url'] ?? ''),
            'action_text' => trim($data['action_text'] ?? ''),
            'template_id' => $data['template_id'] ?? null,
            'expires_at' => $data['expires_at'] ?? null
        ];
        
        // Handle metadata
        if (!empty($data['metadata'])) {
            $cleanData['metadata'] = json_encode($data['metadata']);
        }
        
        // Handle template data
        if (!empty($data['template_data'])) {
            $cleanData['template_data'] = json_encode($data['template_data']);
        }
        
        return $cleanData;
    }

    /**
     * Permission checks
     */
    private function canCreateNotification()
    {
        return in_array($this->user['role'], ['admin', 'leader']);
    }
    
    private function canViewNotification($notification)
    {
        // Users can view their own notifications
        if ($notification['recipient_id'] == $this->user['id']) return true;
        
        // Admins can view all notifications
        if ($this->user['role'] === 'admin') return true;
        
        // Senders can view notifications they sent
        if ($notification['sender_id'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canEditNotification($notification)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($notification['sender_id'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canDeleteNotification($notification)
    {
        // Users can delete their own received notifications
        if ($notification['recipient_id'] == $this->user['id']) return true;
        
        // Admins can delete any notification
        if ($this->user['role'] === 'admin') return true;
        
        // Senders can delete their own sent notifications (if not yet delivered)
        if ($notification['sender_id'] == $this->user['id'] && $notification['status'] === 'pending') {
            return true;
        }
        
        return false;
    }
    
    private function canManageNotifications()
    {
        return in_array($this->user['role'], ['admin', 'leader']);
    }
    
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}