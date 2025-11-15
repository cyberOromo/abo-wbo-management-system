<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Utils\Database;
use App\Models\Global as GlobalModel;
use App\Models\Godina;
use App\Models\Gamta;
use App\Models\Gurmu;
use App\Models\User;
use App\Models\Position;
use App\Models\UserAssignment;
use App\Models\Responsibility;

/**
 * System Admin Controller
 * Comprehensive system configuration and management
 * Only accessible by System Administrators and Super Admins
 */
class SystemAdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireSystemAdmin();
    }
    
    /**
     * System Admin Dashboard
     */
    public function index()
    {
        $systemOverview = $this->getSystemOverview();
        $recentActivity = $this->getRecentSystemActivity();
        $systemHealth = $this->getSystemHealthMetrics();
        $securityStatus = $this->getSecurityStatus();
        $maintenanceTasks = $this->getMaintenanceTasks();
        
        return $this->render('admin.dashboard', [
            'title' => 'System Administration',
            'system_overview' => $systemOverview,
            'recent_activity' => $recentActivity,
            'system_health' => $systemHealth,
            'security_status' => $securityStatus,
            'maintenance_tasks' => $maintenanceTasks
        ]);
    }
    
    /**
     * GLOBAL ORGANIZATION MANAGEMENT
     */
    
    /**
     * Manage Global Organization Settings
     */
    public function globalSettings()
    {
        $globalModel = new GlobalModel();
        $global = $globalModel->getDefault();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateGlobalSettings();
        }
        
        return $this->render('admin.global_settings', [
            'title' => 'Global Organization Settings',
            'global' => $global
        ]);
    }
    
    /**
     * Update Global Organization Settings
     */
    private function updateGlobalSettings()
    {
        try {
            $this->validateCSRF();
            
            $data = $_POST;
            $globalModel = new GlobalModel();
            $global = $globalModel->getDefault();
            
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'headquarters_address' => $data['headquarters_address'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'website' => $data['website'],
                'mission_statement' => $data['mission_statement'],
                'vision_statement' => $data['vision_statement'],
                'fiscal_year_start' => $data['fiscal_year_start'],
                'fiscal_year_end' => $data['fiscal_year_end']
            ];
            
            $globalModel->update($global['id'], $updateData);
            
            return $this->redirectWithMessage('/admin/global-settings', 'Global settings updated successfully', 'success');
            
        } catch (\Exception $e) {
            return $this->redirectWithMessage('/admin/global-settings', 'Error updating settings: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * HIERARCHY MANAGEMENT
     */
    
    /**
     * Comprehensive Hierarchy Management
     */
    public function hierarchyManagement()
    {
        $hierarchyData = $this->getCompleteHierarchy();
        $hierarchyStats = $this->getHierarchyStatistics();
        
        return $this->render('admin.hierarchy_management', [
            'title' => 'Hierarchy Management',
            'hierarchy_data' => $hierarchyData,
            'hierarchy_stats' => $hierarchyStats
        ]);
    }
    
    /**
     * Godina Management
     */
    public function godinaManagement()
    {
        $godinaModel = new Godina();
        $godinas = $godinaModel->getActive();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleGodinaOperation();
        }
        
        return $this->render('admin.godina_management', [
            'title' => 'Godina Management',
            'godinas' => $godinas
        ]);
    }
    
    /**
     * Handle Godina CRUD Operations
     */
    private function handleGodinaOperation()
    {
        try {
            $this->validateCSRF();
            $operation = $_POST['operation'] ?? '';
            $godinaModel = new Godina();
            
            switch ($operation) {
                case 'create':
                    return $this->createGodina();
                case 'update':
                    return $this->updateGodina();
                case 'delete':
                    return $this->deleteGodina();
                default:
                    throw new \Exception('Invalid operation');
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Create new Godina
     */
    private function createGodina()
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:godinas,code',
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url'
        ]);
        
        $godinaModel = new Godina();
        $data['global_id'] = 1; // Always belongs to the main global organization
        $data['status'] = 'active';
        
        $godinaId = $godinaModel->create($data);
        
        return $this->jsonResponse([
            'success' => true, 
            'message' => 'Godina created successfully',
            'data' => ['id' => $godinaId]
        ]);
    }
    
    /**
     * Update existing Godina
     */
    private function updateGodina()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new \Exception('Godina ID is required');
        }
        
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:godinas,code,' . $id,
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'status' => 'required|in:active,inactive,suspended'
        ]);
        
        $godinaModel = new Godina();
        $godinaModel->update($id, $data);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Godina updated successfully'
        ]);
    }
    
    /**
     * Delete Godina (with cascade check)
     */
    private function deleteGodina()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new \Exception('Godina ID is required');
        }
        
        $godinaModel = new Godina();
        $gamtaModel = new Gamta();
        
        // Check for dependent Gamtas
        $dependentGamtas = $gamtaModel->where('godina_id', $id)->count();
        if ($dependentGamtas > 0) {
            throw new \Exception("Cannot delete Godina with {$dependentGamtas} dependent Gamtas. Please reassign or delete them first.");
        }
        
        $godinaModel->delete($id);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Godina deleted successfully'
        ]);
    }
    
    /**
     * Gamta Management
     */
    public function gamtaManagement()
    {
        $gamtaModel = new Gamta();
        $gamtas = $gamtaModel->getActive();
        
        $godinaModel = new Godina();
        $godinas = $godinaModel->getActive();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleGamtaOperation();
        }
        
        return $this->render('admin.gamta_management', [
            'title' => 'Gamta Management',
            'gamtas' => $gamtas,
            'godinas' => $godinas
        ]);
    }
    
    /**
     * Handle Gamta CRUD Operations
     */
    private function handleGamtaOperation()
    {
        try {
            $this->validateCSRF();
            $operation = $_POST['operation'] ?? '';
            
            switch ($operation) {
                case 'create':
                    return $this->createGamta();
                case 'update':
                    return $this->updateGamta();
                case 'delete':
                    return $this->deleteGamta();
                default:
                    throw new \Exception('Invalid operation');
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Create new Gamta
     */
    private function createGamta()
    {
        $data = $this->validate([
            'godina_id' => 'required|integer|exists:godinas,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:gamtas,code',
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'timezone' => 'nullable|string|max:50'
        ]);
        
        $gamtaModel = new Gamta();
        $data['status'] = 'active';
        
        $gamtaId = $gamtaModel->create($data);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Gamta created successfully',
            'data' => ['id' => $gamtaId]
        ]);
    }
    
    /**
     * Update existing Gamta
     */
    private function updateGamta()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new \Exception('Gamta ID is required');
        }
        
        $data = $this->validate([
            'godina_id' => 'required|integer|exists:godinas,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:gamtas,code,' . $id,
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'timezone' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,suspended'
        ]);
        
        $gamtaModel = new Gamta();
        $gamtaModel->update($id, $data);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Gamta updated successfully'
        ]);
    }
    
    /**
     * Delete Gamta (with cascade check)
     */
    private function deleteGamta()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new \Exception('Gamta ID is required');
        }
        
        $gamtaModel = new Gamta();
        $gurmuModel = new Gurmu();
        
        // Check for dependent Gurmus
        $dependentGurmus = $gurmuModel->where('gamta_id', $id)->count();
        if ($dependentGurmus > 0) {
            throw new \Exception("Cannot delete Gamta with {$dependentGurmus} dependent Gurmus. Please reassign or delete them first.");
        }
        
        $gamtaModel->delete($id);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Gamta deleted successfully'
        ]);
    }
    
    /**
     * Gurmu Management
     */
    public function gurmuManagement()
    {
        $gurmuModel = new Gurmu();
        $gurmus = $gurmuModel->getActive();
        
        $gamtaModel = new Gamta();
        $gamtas = $gamtaModel->getActive();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleGurmuOperation();
        }
        
        return $this->render('admin.gurmu_management', [
            'title' => 'Gurmu Management',
            'gurmus' => $gurmus,
            'gamtas' => $gamtas
        ]);
    }
    
    /**
     * Handle Gurmu CRUD Operations
     */
    private function handleGurmuOperation()
    {
        try {
            $this->validateCSRF();
            $operation = $_POST['operation'] ?? '';
            
            switch ($operation) {
                case 'create':
                    return $this->createGurmu();
                case 'update':
                    return $this->updateGurmu();
                case 'delete':
                    return $this->deleteGurmu();
                default:
                    throw new \Exception('Invalid operation');
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Create new Gurmu
     */
    private function createGurmu()
    {
        $data = $this->validate([
            'gamta_id' => 'required|integer|exists:gamtas,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:gurmus,code',
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'meeting_schedule' => 'nullable|string',
            'membership_fee' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3'
        ]);
        
        $gurmuModel = new Gurmu();
        $data['status'] = 'active';
        $data['currency'] = $data['currency'] ?? 'USD';
        
        $gurmuId = $gurmuModel->create($data);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Gurmu created successfully',
            'data' => ['id' => $gurmuId]
        ]);
    }
    
    /**
     * Update existing Gurmu
     */
    private function updateGurmu()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new \Exception('Gurmu ID is required');
        }
        
        $data = $this->validate([
            'gamta_id' => 'required|integer|exists:gamtas,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:gurmus,code,' . $id,
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'meeting_schedule' => 'nullable|string',
            'membership_fee' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'status' => 'required|in:active,inactive,suspended'
        ]);
        
        $gurmuModel = new Gurmu();
        $gurmuModel->update($id, $data);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Gurmu updated successfully'
        ]);
    }
    
    /**
     * Delete Gurmu (with cascade check)
     */
    private function deleteGurmu()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new \Exception('Gurmu ID is required');
        }
        
        $gurmuModel = new Gurmu();
        $userModel = new User();
        
        // Check for dependent Users
        $dependentUsers = $userModel->where('gurmu_id', $id)->count();
        if ($dependentUsers > 0) {
            throw new \Exception("Cannot delete Gurmu with {$dependentUsers} dependent members. Please reassign or remove them first.");
        }
        
        $gurmuModel->delete($id);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Gurmu deleted successfully'
        ]);
    }
    
    /**
     * USER MANAGEMENT
     */
    
    /**
     * Comprehensive User Management
     */
    public function userManagement()
    {
        $userModel = new User();
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'role' => $_GET['role'] ?? '',
            'hierarchy' => $_GET['hierarchy'] ?? '',
            'search' => $_GET['search'] ?? '',
            'limit' => $_GET['limit'] ?? 50
        ];
        
        $users = $this->getUsersWithFilters($filters);
        $userStats = $this->getUserStatistics();
        $positions = $this->getAvailablePositions();
        
        return $this->render('admin.user_management', [
            'title' => 'User Management',
            'users' => $users,
            'user_stats' => $userStats,
            'positions' => $positions,
            'filters' => $filters
        ]);
    }
    
    /**
     * User Profile Management
     */
    public function userProfile($id)
    {
        $userModel = new User();
        $user = $userModel->find($id);
        
        if (!$user) {
            return $this->notFound('User not found');
        }
        
        $userAssignments = $this->getUserAssignments($id);
        $userResponsibilities = $this->getUserResponsibilities($id);
        $userActivity = $this->getUserActivity($id);
        $userPermissions = $this->getUserPermissions($id);
        
        return $this->render('admin.user_profile', [
            'title' => 'User Profile - ' . $user['first_name'] . ' ' . $user['last_name'],
            'user' => $user,
            'assignments' => $userAssignments,
            'responsibilities' => $userResponsibilities,
            'activity' => $userActivity,
            'permissions' => $userPermissions
        ]);
    }
    
    /**
     * POSITION & RESPONSIBILITY MANAGEMENT
     */
    
    /**
     * Position Management
     */
    public function positionManagement()
    {
        $positionModel = new Position();
        $positions = $positionModel->with('responsibilities')->all();
        
        $responsibilityModel = new Responsibility();
        $responsibilities = $responsibilityModel->where('status', 'active')->all();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handlePositionOperation();
        }
        
        return $this->render('admin.position_management', [
            'title' => 'Position Management',
            'positions' => $positions,
            'responsibilities' => $responsibilities
        ]);
    }
    
    /**
     * Responsibility Management
     */
    public function responsibilityManagement()
    {
        $responsibilityModel = new Responsibility();
        $responsibilities = $responsibilityModel->all();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleResponsibilityOperation();
        }
        
        return $this->render('admin.responsibility_management', [
            'title' => 'Responsibility Management',
            'responsibilities' => $responsibilities
        ]);
    }
    
    /**
     * SYSTEM CONFIGURATION
     */
    
    /**
     * System Configuration
     */
    public function systemConfiguration()
    {
        $configurations = $this->getSystemConfigurations();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateSystemConfiguration();
        }
        
        return $this->render('admin.system_configuration', [
            'title' => 'System Configuration',
            'configurations' => $configurations
        ]);
    }
    
    /**
     * Security Settings
     */
    public function securitySettings()
    {
        $securityConfig = $this->getSecurityConfiguration();
        $securityLogs = $this->getRecentSecurityLogs();
        $activeSessions = $this->getActiveSessions();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateSecuritySettings();
        }
        
        return $this->render('admin.security_settings', [
            'title' => 'Security Settings',
            'security_config' => $securityConfig,
            'security_logs' => $securityLogs,
            'active_sessions' => $activeSessions
        ]);
    }
    
    /**
     * EMAIL & NOTIFICATION SETTINGS
     */
    
    /**
     * Email Configuration
     */
    public function emailSettings()
    {
        $emailConfig = $this->getEmailConfiguration();
        $emailTemplates = $this->getEmailTemplates();
        $emailLogs = $this->getRecentEmailLogs();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateEmailSettings();
        }
        
        return $this->render('admin.email_settings', [
            'title' => 'Email Configuration',
            'email_config' => $emailConfig,
            'email_templates' => $emailTemplates,
            'email_logs' => $emailLogs
        ]);
    }
    
    /**
     * Notification Settings
     */
    public function notificationSettings()
    {
        $notificationConfig = $this->getNotificationConfiguration();
        $notificationTypes = $this->getNotificationTypes();
        $recentNotifications = $this->getRecentNotifications();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateNotificationSettings();
        }
        
        return $this->render('admin.notification_settings', [
            'title' => 'Notification Settings',
            'notification_config' => $notificationConfig,
            'notification_types' => $notificationTypes,
            'recent_notifications' => $recentNotifications
        ]);
    }
    
    /**
     * BACKUP & MAINTENANCE
     */
    
    /**
     * Backup Management
     */
    public function backupManagement()
    {
        $backups = $this->getAvailableBackups();
        $backupConfig = $this->getBackupConfiguration();
        $backupSchedule = $this->getBackupSchedule();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleBackupOperation();
        }
        
        return $this->render('admin.backup_management', [
            'title' => 'Backup Management',
            'backups' => $backups,
            'backup_config' => $backupConfig,
            'backup_schedule' => $backupSchedule
        ]);
    }
    
    /**
     * System Maintenance
     */
    public function systemMaintenance()
    {
        $maintenanceStatus = $this->getMaintenanceStatus();
        $scheduledTasks = $this->getScheduledMaintenanceTasks();
        $systemCleanup = $this->getSystemCleanupOptions();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleMaintenanceOperation();
        }
        
        return $this->render('admin.system_maintenance', [
            'title' => 'System Maintenance',
            'maintenance_status' => $maintenanceStatus,
            'scheduled_tasks' => $scheduledTasks,
            'system_cleanup' => $systemCleanup
        ]);
    }
    
    /**
     * REPORTING & ANALYTICS
     */
    
    /**
     * System Reports
     */
    public function systemReports()
    {
        $reportTypes = $this->getAvailableReportTypes();
        $recentReports = $this->getRecentReports();
        $reportSchedules = $this->getReportSchedules();
        
        return $this->render('admin.system_reports', [
            'title' => 'System Reports',
            'report_types' => $reportTypes,
            'recent_reports' => $recentReports,
            'report_schedules' => $reportSchedules
        ]);
    }
    
    /**
     * Analytics Dashboard
     */
    public function analytics()
    {
        $userAnalytics = $this->getUserAnalytics();
        $systemAnalytics = $this->getSystemAnalytics();
        $performanceMetrics = $this->getPerformanceAnalytics();
        $usageStatistics = $this->getUsageStatistics();
        
        return $this->render('admin.analytics', [
            'title' => 'System Analytics',
            'user_analytics' => $userAnalytics,
            'system_analytics' => $systemAnalytics,
            'performance_metrics' => $performanceMetrics,
            'usage_statistics' => $usageStatistics
        ]);
    }
    
    /**
     * HELPER METHODS
     */
    
    /**
     * Database instance
     */
    private $db;
    
    /**
     * Initialize database connection
     */
    private function initDatabase()
    {
        if (!$this->db) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }
    
    /**
     * Execute database query
     */
    private function query($sql, $params = [])
    {
        $this->initDatabase();
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get system overview statistics
     */
    private function getSystemOverview()
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'active_users' => $this->getActiveUsers(),
            'total_godinas' => $this->getTotalGodinas(),
            'total_gamtas' => $this->getTotalGamtas(),
            'total_gurmus' => $this->getTotalGurmus(),
            'total_positions' => $this->getTotalPositions(),
            'total_responsibilities' => $this->getTotalResponsibilities(),
            'active_sessions' => $this->getActiveSessionCount(),
            'database_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsed(),
            'system_uptime' => $this->getSystemUptime(),
            'last_backup' => $this->getLastBackupTime()
        ];
    }
    
    /**
     * Get complete hierarchy structure
     */
    private function getCompleteHierarchy()
    {
        $globalModel = new GlobalModel();
        $global = $globalModel->getDefault();
        
        if ($global) {
            $globalObject = new GlobalModel();
            $globalObject->fill($global);
            return $globalObject->getHierarchicalStructure();
        }
        
        return [];
    }
    
    /**
     * Get hierarchy statistics
     */
    private function getHierarchyStatistics()
    {
        return [
            'godinas' => [
                'total' => $this->getTotalGodinas(),
                'active' => $this->getActiveGodinas(),
                'with_members' => $this->getGodinasWithMembers()
            ],
            'gamtas' => [
                'total' => $this->getTotalGamtas(),
                'active' => $this->getActiveGamtas(),
                'with_members' => $this->getGamtasWithMembers()
            ],
            'gurmus' => [
                'total' => $this->getTotalGurmus(),
                'active' => $this->getActiveGurmus(),
                'with_members' => $this->getGurmusWithMembers()
            ]
        ];
    }
    

    
    /**
     * Placeholder methods for actual implementations
     * These would contain the real database operations and business logic
     */
    
    // System Statistics
    private function getTotalUsers() { return $this->getCount('users'); }
    private function getActiveUsers() { return $this->getCount('users', ['status' => 'active']); }
    private function getTotalGodinas() { return $this->getCount('godinas'); }
    private function getActiveGodinas() { return $this->getCount('godinas', ['status' => 'active']); }
    private function getTotalGamtas() { return $this->getCount('gamtas'); }
    private function getActiveGamtas() { return $this->getCount('gamtas', ['status' => 'active']); }
    private function getTotalGurmus() { return $this->getCount('gurmus'); }
    private function getActiveGurmus() { return $this->getCount('gurmus', ['status' => 'active']); }
    private function getTotalPositions() { return $this->getCount('positions'); }
    private function getTotalResponsibilities() { return $this->getCount('responsibilities'); }
    
    // Helper for counting records
    private function getCount($table, $conditions = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->query($sql, $params);
        return $result[0]['count'] ?? 0;
    }
    
    // Placeholder methods for additional functionality
    private function getRecentSystemActivity() { return []; }
    private function getSystemHealthMetrics() { return ['status' => 'healthy']; }
    private function getSecurityStatus() { return ['alerts' => 0, 'threats' => 0]; }
    private function getMaintenanceTasks() { return []; }
    private function getUsersWithFilters($filters) { return []; }
    private function getUserStatistics() { return []; }
    private function getAvailablePositions() { return []; }
    private function getUserAssignments($userId) { return []; }
    private function getUserResponsibilities($userId) { return []; }
    private function getUserActivity($userId) { return []; }
    private function getUserPermissions($userId) { return []; }
    private function handlePositionOperation() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function handleResponsibilityOperation() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function getSystemConfigurations() { return []; }
    private function updateSystemConfiguration() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function getSecurityConfiguration() { return []; }
    private function getRecentSecurityLogs() { return []; }
    private function getActiveSessions() { return []; }
    private function updateSecuritySettings() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function getEmailConfiguration() { return []; }
    private function getEmailTemplates() { return []; }
    private function getRecentEmailLogs() { return []; }
    private function updateEmailSettings() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function getNotificationConfiguration() { return []; }
    private function getNotificationTypes() { return []; }
    private function getRecentNotifications() { return []; }
    private function updateNotificationSettings() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function getAvailableBackups() { return []; }
    private function getBackupConfiguration() { return []; }
    private function getBackupSchedule() { return []; }
    private function handleBackupOperation() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function getMaintenanceStatus() { return ['status' => 'normal']; }
    private function getScheduledMaintenanceTasks() { return []; }
    private function getSystemCleanupOptions() { return []; }
    private function handleMaintenanceOperation() { return $this->jsonResponse(['message' => 'Not implemented']); }
    private function getAvailableReportTypes() { return []; }
    private function getRecentReports() { return []; }
    private function getReportSchedules() { return []; }
    private function getUserAnalytics() { return []; }
    private function getSystemAnalytics() { return []; }
    private function getPerformanceAnalytics() { return []; }
    private function getUsageStatistics() { return []; }
    private function getGodinasWithMembers() { return 0; }
    private function getGamtasWithMembers() { return 0; }
    private function getGurmusWithMembers() { return 0; }
    private function getActiveSessionCount() { return 0; }
    private function getDatabaseSize() { return '0 MB'; }
    private function getStorageUsed() { return '0 MB'; }
    private function getSystemUptime() { return '0 days'; }
    private function getLastBackupTime() { return 'Never'; }
    /**
     * VALIDATION AND HELPER METHODS
     */
    
    /**
     * Validate request data
     */
    private function validate(array $rules)
    {
        $data = $_POST;
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rulesList = explode('|', $rule);
            $value = $data[$field] ?? null;
            
            foreach ($rulesList as $singleRule) {
                if ($singleRule === 'required' && empty($value)) {
                    $errors[$field] = ucfirst($field) . ' is required';
                    break;
                }
                
                if (strpos($singleRule, 'max:') === 0 && !empty($value)) {
                    $max = intval(substr($singleRule, 4));
                    if (strlen($value) > $max) {
                        $errors[$field] = ucfirst($field) . " cannot exceed {$max} characters";
                    }
                }
                
                if ($singleRule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email address';
                }
                
                if ($singleRule === 'url' && !empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid URL';
                }
                
                if ($singleRule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field] = ucfirst($field) . ' must be a number';
                }
                
                if (strpos($singleRule, 'unique:') === 0 && !empty($value)) {
                    $parts = explode(',', substr($singleRule, 7));
                    $table = $parts[0];
                    $column = $parts[1];
                    $excludeId = $parts[2] ?? null;
                    
                    $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
                    $params = [$value];
                    
                    if ($excludeId) {
                        $query .= " AND id != ?";
                        $params[] = $excludeId;
                    }
                    
                    $result = $this->db->fetch($query, $params);
                    if ($result['count'] > 0) {
                        $errors[$field] = ucfirst($field) . ' already exists';
                    }
                }
                
                if (strpos($singleRule, 'exists:') === 0 && !empty($value)) {
                    $parts = explode(',', substr($singleRule, 7));
                    $table = $parts[0];
                    $column = $parts[1] ?? 'id';
                    
                    $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
                    $result = $this->db->fetch($query, [$value]);
                    if ($result['count'] === 0) {
                        $errors[$field] = ucfirst($field) . ' does not exist';
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }
        
        return array_intersect_key($data, $rules);
    }
    
    /**
     * Validate CSRF token
     */
    private function validateCSRF()
    {
        if (!isset($_POST['_token']) || !hash_equals($_SESSION['_token'], $_POST['_token'])) {
            throw new \Exception('Invalid CSRF token');
        }
    }
    
    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    

    
    /**
     * INTERNAL EMAIL MANAGEMENT
     */
    
    /**
     * User Email Management Dashboard
     */
    public function userEmailManagement()
    {
        $db = Database::getInstance();
        
        // Get users with their email status
        $users = $db->fetchAll("
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email as personal_email,
                u.internal_email,
                u.status,
                u.account_type,
                ua.level_scope,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions,
                ie.internal_email as table_internal_email,
                ie.status as internal_email_status,
                ie.cpanel_account_created,
                ie.created_at as email_created_at
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            LEFT JOIN internal_emails ie ON u.id = ie.user_id
            WHERE u.status IN ('active', 'pending')
            GROUP BY u.id
            ORDER BY u.id
        ");
        
        // Generate statistics
        $stats = [
            'total_users' => count($users),
            'has_internal_email' => count(array_filter($users, fn($u) => !empty($u['internal_email']))),
            'missing_internal_email' => count(array_filter($users, fn($u) => empty($u['internal_email']))),
            'has_assignments' => count(array_filter($users, fn($u) => !empty($u['positions']))),
            'cpanel_accounts' => count(array_filter($users, fn($u) => $u['cpanel_account_created'] == 1))
        ];
        
        return $this->render('admin.user_email_management', [
            'title' => 'User Email Management',
            'users' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * Generate missing internal emails for users
     */
    public function generateInternalEmails()
    {
        try {
            require_once __DIR__ . '/../../scripts/analyze-user-emails.php';
            
            $analyzer = new \App\Scripts\UserEmailAnalyzer();
            
            // Get users missing emails
            $missingEmails = $this->getUsersMissingEmails();
            
            if (empty($missingEmails)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'All users already have internal emails',
                    'generated' => 0
                ]);
            }
            
            $generated = 0;
            $errors = [];
            
            foreach ($missingEmails as $issue) {
                $result = $analyzer->generateEmailForSpecificUser($issue['user_id']);
                
                if ($result['success']) {
                    $generated++;
                } else {
                    $errors[] = "User {$issue['user_id']}: {$result['message']}";
                }
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => "Successfully generated {$generated} internal emails",
                'generated' => $generated,
                'errors' => $errors
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error generating emails: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Regenerate internal email for specific user
     */
    public function regenerateUserEmail($userId)
    {
        try {
            $db = Database::getInstance();
            
            // Verify user exists
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }
            
            require_once __DIR__ . '/../../scripts/analyze-user-emails.php';
            $analyzer = new \App\Scripts\UserEmailAnalyzer();
            
            // Delete existing internal email
            $db->query("DELETE FROM internal_emails WHERE user_id = ?", [$userId]);
            $db->query("UPDATE users SET internal_email = NULL WHERE id = ?", [$userId]);
            
            // Generate new email
            $result = $analyzer->generateEmailForSpecificUser($userId);
            
            return $this->jsonResponse($result);
            
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error regenerating email: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Hybrid Registration Management Dashboard
     */
    public function hybridRegistrationManagement()
    {
        $db = Database::getInstance();
        
        // Get hybrid registration statistics
        $stats = [
            'total_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations"),
            'pending_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations WHERE status = 'pending'"),
            'approved_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations WHERE status = 'approved'"),
            'rejected_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations WHERE status = 'rejected'")
        ];
        
        // Get recent registrations
        $recentRegistrations = $db->fetchAll("
            SELECT hr.*, u.first_name, u.last_name, u.email
            FROM hybrid_registrations hr
            LEFT JOIN users u ON hr.user_id = u.id
            ORDER BY hr.created_at DESC
            LIMIT 20
        ");
        
        return $this->render('admin.hybrid_registration_management', [
            'title' => 'Hybrid Registration Management',
            'stats' => $stats,
            'recent_registrations' => $recentRegistrations
        ]);
    }
    
    /**
     * Get users missing internal emails
     */
    private function getUsersMissingEmails()
    {
        $db = Database::getInstance();
        
        return $db->fetchAll("
            SELECT 
                u.id as user_id,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                u.email,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions,
                ua.level_scope
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            WHERE u.status IN ('active', 'pending')
            AND (u.internal_email IS NULL OR u.internal_email = '')
            AND ua.user_id IS NOT NULL
            GROUP BY u.id
            ORDER BY u.id
        ");
    }
    
    /**
     * Check if user is system admin
     */
    private function requireSystemAdmin()
    {
        $user = auth_user();
        if (!$user || !in_array($user['role'], ['system_admin', 'super_admin'])) {
            $this->redirect('/dashboard');
        }
    }
}