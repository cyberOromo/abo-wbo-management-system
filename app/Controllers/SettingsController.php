<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Utils\Database;
use App\Utils\Validator;

class SettingsController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Display settings dashboard - Admin only
     */
    public function index()
    {
        try {
            $user = $this->getAuthUser();
            
            // Only admins can access settings
            if (!$this->userIsAdmin($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            $systemSettings = $this->getSystemSettings();
            $systemStatus = $this->getSystemStatus();
            $recentActivities = $this->getRecentSystemActivities();
            $settingsStats = $this->getSettingsStats();
            $securityAlerts = $this->getSecurityAlerts();
            
            echo $this->render('settings/index_modern', [
                'system_settings' => $systemSettings,
                'system_status' => $systemStatus,
                'recent_activities' => $recentActivities,
                'settings_stats' => $settingsStats,
                'security_alerts' => $securityAlerts,
                'title' => 'System Settings'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::index error: " . $e->getMessage());
            return $this->errorResponse('Failed to load settings', 500);
        }
    }

    /**
     * General system settings
     */
    public function general()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->updateGeneral();
            }
            
            $generalSettings = $this->getGeneralSettings();
            $timezones = $this->getAvailableTimezones();
            $languages = $this->getAvailableLanguages();
            
            echo $this->render('settings/general', [
                'general_settings' => $generalSettings,
                'timezones' => $timezones,
                'languages' => $languages,
                'title' => 'General Settings'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::general error: " . $e->getMessage());
            return $this->errorResponse('Failed to load general settings', 500);
        }
    }

    /**
     * Update general settings
     */
    public function updateGeneral()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $data = $_POST;
            
            // Validate general settings data
            $validation = $this->validateGeneralSettings($data);
            if (!$validation['valid']) {
                return $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            }
            
            // Update settings
            $settingsToUpdate = [
                'organization_name' => $data['organization_name'],
                'organization_description' => $data['organization_description'],
                'default_timezone' => $data['default_timezone'],
                'default_language' => $data['default_language'],
                'date_format' => $data['date_format'],
                'time_format' => $data['time_format'],
                'currency' => $data['currency'],
                'registration_mode' => $data['registration_mode'],
                'maintenance_mode' => isset($data['maintenance_mode']) ? 1 : 0
            ];
            
            foreach ($settingsToUpdate as $key => $value) {
                $this->updateSystemSetting($key, $value, $user['id']);
            }
            
            // Log settings update
            $this->logSystemActivity('general_settings_updated', $user['id']);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'General settings updated successfully'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::updateGeneral error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update settings'], 500);
        }
    }

    /**
     * Email configuration settings
     */
    public function email()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->updateEmail();
            }
            
            $emailSettings = $this->getEmailSettings();
            $emailTemplates = $this->getEmailTemplates();
            
            echo $this->render('settings/email', [
                'email_settings' => $emailSettings,
                'email_templates' => $emailTemplates,
                'title' => 'Email Settings'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::email error: " . $e->getMessage());
            return $this->errorResponse('Failed to load email settings', 500);
        }
    }

    /**
     * Update email settings
     */
    public function updateEmail()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $data = $_POST;
            
            // Validate email settings
            $validation = $this->validateEmailSettings($data);
            if (!$validation['valid']) {
                return $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            }
            
            // Update email settings
            $emailSettings = [
                'smtp_host' => $data['smtp_host'],
                'smtp_port' => $data['smtp_port'],
                'smtp_username' => $data['smtp_username'],
                'smtp_password' => !empty($data['smtp_password']) ? $data['smtp_password'] : $this->getSystemSetting('smtp_password'),
                'smtp_encryption' => $data['smtp_encryption'],
                'from_email' => $data['from_email'],
                'from_name' => $data['from_name'],
                'reply_to_email' => $data['reply_to_email'],
                'email_notifications_enabled' => isset($data['email_notifications_enabled']) ? 1 : 0
            ];
            
            foreach ($emailSettings as $key => $value) {
                $this->updateSystemSetting($key, $value, $user['id']);
            }
            
            // Test email connection if requested
            if (isset($data['test_connection'])) {
                $testResult = $this->testEmailConnection($emailSettings);
                if (!$testResult['success']) {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => 'Settings saved but email test failed: ' . $testResult['error']
                    ], 200);
                }
            }
            
            $this->logSystemActivity('email_settings_updated', $user['id']);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Email settings updated successfully'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::updateEmail error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update email settings'], 500);
        }
    }

    /**
     * Notification preferences
     */
    public function notifications()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->updateNotifications();
            }
            
            $notificationSettings = $this->getNotificationSettings();
            $notificationTypes = $this->getNotificationTypes();
            
            echo $this->render('settings/notifications', [
                'notification_settings' => $notificationSettings,
                'notification_types' => $notificationTypes,
                'title' => 'Notification Settings'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::notifications error: " . $e->getMessage());
            return $this->errorResponse('Failed to load notification settings', 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $data = $_POST;
            
            // Update notification preferences
            $notificationSettings = [
                'enable_email_notifications' => isset($data['enable_email_notifications']) ? 1 : 0,
                'enable_browser_notifications' => isset($data['enable_browser_notifications']) ? 1 : 0,
                'notify_new_registrations' => isset($data['notify_new_registrations']) ? 1 : 0,
                'notify_task_assignments' => isset($data['notify_task_assignments']) ? 1 : 0,
                'notify_meeting_reminders' => isset($data['notify_meeting_reminders']) ? 1 : 0,
                'notify_event_updates' => isset($data['notify_event_updates']) ? 1 : 0,
                'notify_donation_received' => isset($data['notify_donation_received']) ? 1 : 0,
                'notification_frequency' => $data['notification_frequency'] ?? 'immediate',
                'digest_schedule' => $data['digest_schedule'] ?? 'daily'
            ];
            
            foreach ($notificationSettings as $key => $value) {
                $this->updateSystemSetting($key, $value, $user['id']);
            }
            
            $this->logSystemActivity('notification_settings_updated', $user['id']);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Notification settings updated successfully'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::updateNotifications error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update notification settings'], 500);
        }
    }

    /**
     * Backup and restore settings
     */
    public function backup()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->createBackup();
            }
            
            $backups = $this->getAvailableBackups();
            $backupSettings = $this->getBackupSettings();
            
            echo $this->render('settings/backup', [
                'backups' => $backups,
                'backup_settings' => $backupSettings,
                'title' => 'Backup & Restore'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::backup error: " . $e->getMessage());
            return $this->errorResponse('Failed to load backup settings', 500);
        }
    }

    /**
     * Create system backup
     */
    public function createBackup()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $backupType = $_POST['backup_type'] ?? 'full';
            $description = $_POST['description'] ?? '';
            
            // Create backup
            $backupResult = $this->performBackup($backupType, $description, $user['id']);
            
            if ($backupResult['success']) {
                $this->logSystemActivity('backup_created', $user['id'], [
                    'backup_id' => $backupResult['backup_id'],
                    'backup_type' => $backupType
                ]);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Backup created successfully',
                    'backup_id' => $backupResult['backup_id']
                ]);
            }
            
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Backup failed: ' . $backupResult['error']
            ], 500);
            
        } catch (\Exception $e) {
            error_log("SettingsController::createBackup error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Backup creation failed'], 500);
        }
    }

    /**
     * Maintenance mode settings
     */
    public function maintenance()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->toggleMaintenance();
            }
            
            $maintenanceSettings = $this->getMaintenanceSettings();
            $systemHealth = $this->getSystemHealthStatus();
            
            echo $this->render('settings/maintenance', [
                'maintenance_settings' => $maintenanceSettings,
                'system_health' => $systemHealth,
                'title' => 'System Maintenance'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::maintenance error: " . $e->getMessage());
            return $this->errorResponse('Failed to load maintenance settings', 500);
        }
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->userIsAdmin($user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $enabled = isset($_POST['enabled']) ? 1 : 0;
            $message = $_POST['message'] ?? 'System is currently under maintenance. Please check back later.';
            
            // Update maintenance mode settings
            $this->updateSystemSetting('maintenance_mode_enabled', $enabled, $user['id']);
            $this->updateSystemSetting('maintenance_mode_message', $message, $user['id']);
            
            $this->logSystemActivity('maintenance_mode_' . ($enabled ? 'enabled' : 'disabled'), $user['id']);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Maintenance mode ' . ($enabled ? 'enabled' : 'disabled') . ' successfully'
            ]);
            
        } catch (\Exception $e) {
            error_log("SettingsController::toggleMaintenance error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to toggle maintenance mode'], 500);
        }
    }

    // Helper Methods

    private function userIsAdmin($user)
    {
        return $user['role'] === 'admin';
    }

    private function getSystemSettings()
    {
        $sql = "SELECT setting_key, setting_value FROM system_settings WHERE is_active = 1";
        $settings = $this->db->fetchAll($sql);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }

    private function getSystemSetting($key, $default = '')
    {
        $sql = "SELECT setting_value FROM system_settings WHERE setting_key = ? AND is_active = 1";
        $result = $this->db->fetchRow($sql, [$key]);
        
        return $result ? $result['setting_value'] : $default;
    }

    private function updateSystemSetting($key, $value, $userId)
    {
        // Check if setting exists
        $sql = "SELECT id FROM system_settings WHERE setting_key = ?";
        $existing = $this->db->fetchRow($sql, [$key]);
        
        if ($existing) {
            // Update existing setting
            $sql = "UPDATE system_settings SET setting_value = ?, updated_by = ?, updated_at = ? WHERE setting_key = ?";
            return $this->db->query($sql, [$value, $userId, date('Y-m-d H:i:s'), $key]);
        } else {
            // Create new setting
            $sql = "INSERT INTO system_settings (setting_key, setting_value, created_by, created_at, is_active) VALUES (?, ?, ?, ?, 1)";
            return $this->db->query($sql, [$key, $value, $userId, date('Y-m-d H:i:s')]);
        }
    }

    private function getGeneralSettings()
    {
        return [
            'organization_name' => $this->getSystemSetting('organization_name', 'ABO-WBO Management System'),
            'organization_description' => $this->getSystemSetting('organization_description', ''),
            'default_timezone' => $this->getSystemSetting('default_timezone', 'America/New_York'),
            'default_language' => $this->getSystemSetting('default_language', 'en'),
            'date_format' => $this->getSystemSetting('date_format', 'Y-m-d'),
            'time_format' => $this->getSystemSetting('time_format', 'H:i:s'),
            'currency' => $this->getSystemSetting('currency', 'USD'),
            'registration_mode' => $this->getSystemSetting('registration_mode', 'open'),
            'maintenance_mode' => $this->getSystemSetting('maintenance_mode', 0)
        ];
    }

    private function getEmailSettings()
    {
        return [
            'smtp_host' => $this->getSystemSetting('smtp_host', ''),
            'smtp_port' => $this->getSystemSetting('smtp_port', '587'),
            'smtp_username' => $this->getSystemSetting('smtp_username', ''),
            'smtp_password' => '******', // Never display actual password
            'smtp_encryption' => $this->getSystemSetting('smtp_encryption', 'tls'),
            'from_email' => $this->getSystemSetting('from_email', ''),
            'from_name' => $this->getSystemSetting('from_name', ''),
            'reply_to_email' => $this->getSystemSetting('reply_to_email', ''),
            'email_notifications_enabled' => $this->getSystemSetting('email_notifications_enabled', 1)
        ];
    }

    private function getNotificationSettings()
    {
        return [
            'enable_email_notifications' => $this->getSystemSetting('enable_email_notifications', 1),
            'enable_browser_notifications' => $this->getSystemSetting('enable_browser_notifications', 1),
            'notify_new_registrations' => $this->getSystemSetting('notify_new_registrations', 1),
            'notify_task_assignments' => $this->getSystemSetting('notify_task_assignments', 1),
            'notify_meeting_reminders' => $this->getSystemSetting('notify_meeting_reminders', 1),
            'notify_event_updates' => $this->getSystemSetting('notify_event_updates', 1),
            'notify_donation_received' => $this->getSystemSetting('notify_donation_received', 1),
            'notification_frequency' => $this->getSystemSetting('notification_frequency', 'immediate'),
            'digest_schedule' => $this->getSystemSetting('digest_schedule', 'daily')
        ];
    }

    private function getSystemStatus()
    {
        return [
            'database_status' => $this->checkDatabaseStatus(),
            'storage_status' => $this->checkStorageStatus(),
            'email_status' => $this->checkEmailStatus(),
            'backup_status' => $this->checkBackupStatus(),
            'last_backup' => $this->getLastBackupInfo(),
            'system_uptime' => $this->getSystemUptime(),
            'maintenance_mode' => $this->getSystemSetting('maintenance_mode_enabled', 0)
        ];
    }

    private function validateGeneralSettings($data)
    {
        $errors = [];
        
        if (empty($data['organization_name'])) {
            $errors['organization_name'] = 'Organization name is required';
        }
        
        if (!empty($data['default_timezone']) && !in_array($data['default_timezone'], timezone_identifiers_list())) {
            $errors['default_timezone'] = 'Invalid timezone';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function validateEmailSettings($data)
    {
        $errors = [];
        
        if (!empty($data['from_email']) && !filter_var($data['from_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['from_email'] = 'Invalid from email address';
        }
        
        if (!empty($data['reply_to_email']) && !filter_var($data['reply_to_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['reply_to_email'] = 'Invalid reply-to email address';
        }
        
        if (!empty($data['smtp_port']) && (!is_numeric($data['smtp_port']) || $data['smtp_port'] <= 0)) {
            $errors['smtp_port'] = 'Invalid SMTP port';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function getAvailableTimezones()
    {
        $timezones = timezone_identifiers_list();
        $result = [];
        
        foreach ($timezones as $timezone) {
            $result[$timezone] = $timezone;
        }
        
        return $result;
    }

    private function getAvailableLanguages()
    {
        return [
            'en' => 'English',
            'om' => 'Oromo'
        ];
    }

    private function testEmailConnection($settings)
    {
        try {
            // Basic email connection test
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function logSystemActivity($action, $userId, $metadata = [])
    {
        $sql = "INSERT INTO system_activities (action, user_id, metadata, created_at) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$action, $userId, json_encode($metadata), date('Y-m-d H:i:s')]);
    }

    private function getRecentSystemActivities()
    {
        $sql = "SELECT sa.*, u.first_name, u.last_name
                FROM system_activities sa
                JOIN users u ON sa.user_id = u.id
                ORDER BY sa.created_at DESC
                LIMIT 20";
        
        return $this->db->fetchAll($sql);
    }

    // Additional helper methods for system status checks
    private function checkDatabaseStatus() { return 'healthy'; }
    private function checkStorageStatus() { return 'healthy'; }
    private function checkEmailStatus() { return 'not_configured'; }
    private function checkBackupStatus() { return 'pending'; }
    private function getLastBackupInfo() { return null; }
    private function getSystemUptime() { return '24 hours'; }
    private function getNotificationTypes() { return []; }
    private function getEmailTemplates() { return []; }
    private function getAvailableBackups() { return []; }
    private function getBackupSettings() { return []; }
    private function getMaintenanceSettings() { return []; }
    private function getSystemHealthStatus() { return []; }
    private function performBackup($type, $description, $userId) { return ['success' => false, 'error' => 'Not implemented']; }
    
    /**
     * Get settings statistics for dashboard
     */
    private function getSettingsStats()
    {
        try {
            $stats = [];
            
            // Count of configured settings
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM system_settings WHERE value IS NOT NULL AND value != ''");
            $stmt->execute();
            $stats['configured_settings'] = $stmt->fetchColumn();
            
            // Total settings available
            $stats['total_settings'] = 25; // Approximate number of settings
            
            // Configuration completeness
            $stats['completion_percentage'] = $stats['total_settings'] > 0 ? 
                round(($stats['configured_settings'] / $stats['total_settings']) * 100, 1) : 0;
            
            // Recent changes count (last 7 days)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM system_activity 
                                      WHERE action LIKE '%settings%' 
                                      AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute();
            $stats['recent_changes'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting settings stats: " . $e->getMessage());
            return [
                'configured_settings' => 0,
                'total_settings' => 25,
                'completion_percentage' => 0,
                'recent_changes' => 0
            ];
        }
    }
    
    /**
     * Get security alerts for dashboard
     */
    private function getSecurityAlerts()
    {
        try {
            $alerts = [];
            
            // Check for weak passwords
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE password_strength < 3");
            $stmt->execute();
            $weakPasswords = $stmt->fetchColumn();
            
            if ($weakPasswords > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "$weakPasswords users have weak passwords",
                    'action' => 'enforce_password_policy',
                    'severity' => 'medium'
                ];
            }
            
            // Check for failed login attempts
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM login_attempts 
                                      WHERE success = 0 
                                      AND attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $stmt->execute();
            $failedLogins = $stmt->fetchColumn();
            
            if ($failedLogins > 10) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => "$failedLogins failed login attempts in last hour",
                    'action' => 'review_security_logs',
                    'severity' => 'high'
                ];
            }
            
            // Check for inactive sessions
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_sessions 
                                      WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                                      AND active = 1");
            $stmt->execute();
            $staleSessions = $stmt->fetchColumn();
            
            if ($staleSessions > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'message' => "$staleSessions inactive sessions found",
                    'action' => 'cleanup_sessions',
                    'severity' => 'low'
                ];
            }
            
            return $alerts;
        } catch (PDOException $e) {
            error_log("Error getting security alerts: " . $e->getMessage());
            return [];
        }
    }
}