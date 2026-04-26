<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Utils\Database;

class ReportController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display reports dashboard with hierarchy-based access
     */
    public function index()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            // Get report categories available to user
            $availableReports = $this->getAvailableReports($user);
            $quickStats = $this->getQuickStatistics($userScope);
            $recentReports = $this->getRecentReports($user['id']);
            
            return $this->render('reports/index_modern', [
                'available_reports' => $availableReports,
                'quick_stats' => $quickStats,
                'recent_reports' => $recentReports,
                'user_scope' => $userScope,
                'user_role' => $user['role'],
                'title' => 'Reports & Analytics'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::index error: " . $e->getMessage());
            return $this->errorResponse('Failed to load reports dashboard', 500);
        }
    }

    /**
     * User reports with hierarchy filtering
     */
    public function users()
    {
        try {
            $user = $this->getAuthUser();
            
            // Check permissions
            if (!$this->canViewUserReports($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $filters = [
                'role' => $_GET['role'] ?? 'all',
                'status' => $_GET['status'] ?? 'all',
                'registration_period' => $_GET['registration_period'] ?? '30_days'
            ];
            
            $userReport = $this->generateUserReport($userScope, $filters);
            
            return $this->render('reports/detail', [
                'report_title' => 'User Reports',
                'report_data' => $userReport,
                'filters' => $filters,
                'user_scope' => $userScope,
                'title' => 'User Reports'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::users error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate user report', 500);
        }
    }

    /**
     * Hierarchy reports
     */
    public function hierarchy()
    {
        try {
            $user = $this->getAuthUser();
            
            if (!$this->canViewHierarchyReports($user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $hierarchyData = $this->generateHierarchyReport($userScope);
            $positionDistribution = $this->getPositionDistribution($userScope);
            $hierarchyHealth = $this->getHierarchyHealthMetrics($userScope);
            
            return $this->render('reports/detail', [
                'report_title' => 'Organizational Hierarchy Reports',
                'hierarchy_data' => $hierarchyData,
                'position_distribution' => $positionDistribution,
                'hierarchy_health' => $hierarchyHealth,
                'user_scope' => $userScope,
                'title' => 'Organizational Hierarchy Reports'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::hierarchy error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate hierarchy report', 500);
        }
    }

    /**
     * Task reports
     */
    public function tasks()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $filters = [
                'status' => $_GET['status'] ?? 'all',
                'priority' => $_GET['priority'] ?? 'all',
                'date_range' => $_GET['date_range'] ?? '30_days',
                'assigned_to' => $_GET['assigned_to'] ?? 'all'
            ];
            
            $taskReport = $this->generateTaskReport($userScope, $filters);
            $taskMetrics = $this->getTaskMetrics($userScope, $filters);
            $productivityData = $this->getProductivityData($userScope, $filters);
            
            return $this->render('reports/detail', [
                'report_title' => 'Task Reports & Analytics',
                'task_report' => $taskReport,
                'task_metrics' => $taskMetrics,
                'productivity_data' => $productivityData,
                'filters' => $filters,
                'user_scope' => $userScope,
                'title' => 'Task Reports & Analytics'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::tasks error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate task report', 500);
        }
    }

    /**
     * Meeting reports
     */
    public function meetings()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $filters = [
                'date_range' => $_GET['date_range'] ?? '30_days',
                'meeting_type' => $_GET['meeting_type'] ?? 'all',
                'status' => $_GET['status'] ?? 'all'
            ];
            
            $meetingReport = $this->generateMeetingReport($userScope, $filters);
            $attendanceData = $this->getMeetingAttendanceData($userScope, $filters);
            $meetingEffectiveness = $this->getMeetingEffectivenessMetrics($userScope, $filters);
            
            return $this->render('reports/detail', [
                'report_title' => 'Meeting Reports & Analytics',
                'meeting_report' => $meetingReport,
                'attendance_data' => $attendanceData,
                'effectiveness_metrics' => $meetingEffectiveness,
                'filters' => $filters,
                'user_scope' => $userScope,
                'title' => 'Meeting Reports & Analytics'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::meetings error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate meeting report', 500);
        }
    }

    /**
     * Event reports
     */
    public function events()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $filters = [
                'date_range' => $_GET['date_range'] ?? '90_days',
                'event_type' => $_GET['event_type'] ?? 'all',
                'status' => $_GET['status'] ?? 'all'
            ];
            
            $eventReport = $this->generateEventReport($userScope, $filters);
            $participationData = $this->getEventParticipationData($userScope, $filters);
            $eventImpact = $this->getEventImpactMetrics($userScope, $filters);
            
            return $this->render('reports/detail', [
                'report_title' => 'Community Events Reports',
                'event_report' => $eventReport,
                'participation_data' => $participationData,
                'event_impact' => $eventImpact,
                'filters' => $filters,
                'user_scope' => $userScope,
                'title' => 'Community Events Reports'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::events error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate event report', 500);
        }
    }

    /**
     * Donation reports
     */
    public function donations()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $filters = [
                'date_range' => $_GET['date_range'] ?? '30_days',
                'donation_type' => $_GET['donation_type'] ?? 'all',
                'category' => $_GET['category'] ?? 'all',
                'amount_range' => $_GET['amount_range'] ?? 'all'
            ];
            
            $donationReport = $this->generateDonationReport($userScope, $filters);
            $donationTrends = $this->getDonationTrends($userScope, $filters);
            $donorAnalysis = $this->getDonorAnalysis($userScope, $filters);
            
            return $this->render('reports/detail', [
                'report_title' => 'Donation Reports & Analytics',
                'donation_report' => $donationReport,
                'donation_trends' => $donationTrends,
                'donor_analysis' => $donorAnalysis,
                'filters' => $filters,
                'user_scope' => $userScope,
                'title' => 'Donation Reports & Analytics'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::donations error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate donation report', 500);
        }
    }

    /**
     * Course reports
     */
    public function courses()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $filters = [
                'date_range' => $_GET['date_range'] ?? '90_days',
                'course_type' => $_GET['course_type'] ?? 'all',
                'status' => $_GET['status'] ?? 'all'
            ];
            
            $courseReport = $this->generateCourseReport($userScope, $filters);
            $enrollmentData = $this->getCourseEnrollmentData($userScope, $filters);
            $completionRates = $this->getCourseCompletionRates($userScope, $filters);
            
            return $this->render('reports/detail', [
                'report_title' => 'Education & Training Reports',
                'course_report' => $courseReport,
                'enrollment_data' => $enrollmentData,
                'completion_rates' => $completionRates,
                'filters' => $filters,
                'user_scope' => $userScope,
                'title' => 'Education & Training Reports'
            ]);
            
        } catch (\Exception $e) {
            error_log("ReportController::courses error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate course report', 500);
        }
    }

    /**
     * Export reports in various formats
     */
    public function export($type)
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $format = $_GET['format'] ?? 'pdf';
            $filters = $_GET;
            $normalizedType = $this->normalizeExportType($type);
            
            // Validate report type and user permissions
            if (!$this->canExportReport($normalizedType, $user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $reportData = $this->generateExportData($normalizedType, $userScope, $filters);
            
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($reportData, $normalizedType);
                case 'excel':
                    return $this->exportToExcel($reportData, $normalizedType);
                case 'pdf':
                    return $this->exportToPdf($reportData, $normalizedType);
                default:
                    return $this->jsonResponse(['success' => false, 'message' => 'Invalid export format'], 400);
            }
            
        } catch (\Exception $e) {
            error_log("ReportController::export error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Export failed'], 500);
        }
    }

    // Helper Methods

    protected function getUserHierarchicalScope($userId)
    {
        $db = Database::getInstance();
        
        $sql = "SELECT ua.*, p.name as position_name, p.hierarchy_type,
                       go.name as godina_name, ga.name as gamta_name, gu.name as gurmu_name
                FROM user_assignments ua
                LEFT JOIN positions p ON ua.position_id = p.id
                LEFT JOIN godinas go ON ua.godina_id = go.id
                LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
                LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
                WHERE ua.user_id = ? AND ua.status = 'active'
                LIMIT 1";
        
        return $db->fetch($sql, [$userId]) ?: [];
    }

    protected function getAvailableReports($user)
    {
        $reports = [
            'tasks' => [
                'title' => 'Task Reports',
                'description' => 'Task completion, productivity and assignment reports',
                'icon' => 'fas fa-tasks',
                'available' => true
            ],
            'meetings' => [
                'title' => 'Meeting Reports',
                'description' => 'Meeting attendance, effectiveness and scheduling reports',
                'icon' => 'fas fa-users',
                'available' => true
            ],
            'events' => [
                'title' => 'Event Reports',
                'description' => 'Community events participation and impact reports',
                'icon' => 'fas fa-calendar-alt',
                'available' => true
            ],
            'donations' => [
                'title' => 'Donation Reports',
                'description' => 'Financial contributions and donor analysis reports',
                'icon' => 'fas fa-hand-holding-usd',
                'available' => true
            ]
        ];

        // Admin and executive specific reports
        if (in_array($user['role'], ['admin', 'executive'])) {
            $reports['users'] = [
                'title' => 'User Reports',
                'description' => 'Member registration, activity and engagement reports',
                'icon' => 'fas fa-users-cog',
                'available' => true
            ];
            
            $reports['hierarchy'] = [
                'title' => 'Hierarchy Reports',
                'description' => 'Organizational structure and position distribution reports',
                'icon' => 'fas fa-sitemap',
                'available' => true
            ];
            
            $reports['courses'] = [
                'title' => 'Education Reports',
                'description' => 'Training programs and educational activity reports',
                'icon' => 'fas fa-graduation-cap',
                'available' => true
            ];
        }

        return $reports;
    }

    protected function getQuickStatistics($userScope)
    {
        return [
            'total_members' => $this->getTotalMembersInScope($userScope),
            'active_tasks' => $this->getActiveTasksInScope($userScope),
            'upcoming_meetings' => $this->getUpcomingMeetingsInScope($userScope),
            'monthly_donations' => $this->getMonthlyDonationsInScope($userScope),
            'recent_events' => $this->getRecentEventsInScope($userScope)
        ];
    }

    private function canViewUserReports($user)
    {
        return in_array($user['role'], ['admin', 'executive']);
    }

    private function canViewHierarchyReports($user)
    {
        return in_array($user['role'], ['admin', 'executive']);
    }

    private function canExportReport($type, $user)
    {
        $allowedReports = ['tasks', 'meetings', 'events', 'donations'];
        
        if (in_array($user['role'], ['admin', 'executive'])) {
            $allowedReports = array_merge($allowedReports, ['users', 'hierarchy', 'courses']);
        }
        
        return in_array($type, $allowedReports);
    }

    private function generateUserReport($userScope, $filters)
    {
        $sql = "SELECT u.*, ua.level_scope, ua.created_at as assignment_date,
                       p.name as position_name,
                       go.name as godina_name, ga.name as gamta_name, gu.name as gurmu_name
                FROM users u
                LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
                LEFT JOIN positions p ON ua.position_id = p.id
                LEFT JOIN godinas go ON ua.godina_id = go.id
                LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
                LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
                WHERE u.status = 'active'";
        
        $params = [];
        
        // Apply hierarchy filtering
        if (!empty($userScope)) {
            if ($userScope['level_scope'] === 'gurmu') {
                $sql .= " AND ua.gurmu_id = ?";
                $params[] = $userScope['gurmu_id'];
            } elseif ($userScope['level_scope'] === 'gamta') {
                $sql .= " AND ua.gamta_id = ?";
                $params[] = $userScope['gamta_id'];
            } elseif ($userScope['level_scope'] === 'godina') {
                $sql .= " AND ua.godina_id = ?";
                $params[] = $userScope['godina_id'];
            }
        }
        
        // Apply role filter
        if ($filters['role'] !== 'all') {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function generateHierarchyReport($userScope)
    {
        // Generate comprehensive hierarchy statistics
        return [
            'godinas' => $this->getGodinaStatistics($userScope),
            'gamtas' => $this->getGamtaStatistics($userScope),
            'gurmus' => $this->getGurmuStatistics($userScope),
            'positions' => $this->getPositionStatistics($userScope)
        ];
    }

    private function generateTaskReport($userScope, $filters)
    {
        $sql = "SELECT t.*, u.first_name, u.last_name,
                       au.first_name as assigned_first_name, au.last_name as assigned_last_name
                FROM tasks t
                LEFT JOIN users u ON t.created_by = u.id
                LEFT JOIN users au ON t.assigned_to = au.id
                WHERE 1=1";
        
        $params = [];
        
        // Apply hierarchy filtering based on user scope
        if (!empty($userScope)) {
            // Add hierarchy-based task filtering logic here
        }
        
        // Apply status filter
        if ($filters['status'] !== 'all') {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        // Apply priority filter
        if ($filters['priority'] !== 'all') {
            $sql .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }
        
        // Apply date range filter
        if ($filters['date_range'] !== 'all') {
            $dateCondition = $this->getDateRangeCondition($filters['date_range']);
            $sql .= " AND " . $dateCondition;
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT 1000";
        
        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getDateRangeCondition($range, $column = 't.created_at')
    {
        switch ($range) {
            case '7_days':
                return "{$column} >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30_days':
                return "{$column} >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90_days':
                return "{$column} >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'this_year':
                return "YEAR({$column}) = YEAR(NOW())";
            default:
                return "1=1";
        }
    }

    private function getPositionDistribution($userScope)
    {
        $sql = "SELECT p.name as label, COUNT(ua.id) as value
                FROM positions p
                LEFT JOIN user_assignments ua ON p.id = ua.position_id AND ua.status = 'active'
                GROUP BY p.id, p.name
                ORDER BY value DESC, p.name ASC
                LIMIT 20";

        return Database::getInstance()->fetchAll($sql);
    }

    private function getHierarchyHealthMetrics($userScope)
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM godinas) as total_godinas,
                    (SELECT COUNT(*) FROM gamtas) as total_gamtas,
                    (SELECT COUNT(*) FROM gurmus) as total_gurmus,
                    (SELECT COUNT(*) FROM users WHERE status = 'active') as active_users,
                    (SELECT COUNT(*) FROM user_assignments WHERE status = 'active') as active_assignments";

        return Database::getInstance()->fetch($sql) ?: [];
    }

    private function getTaskMetrics($userScope, $filters)
    {
        $sql = "SELECT
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN t.status IN ('pending', 'in_progress', 'under_review') THEN 1 ELSE 0 END) as active_tasks
                FROM tasks t
                WHERE 1=1";

        $params = [];

        if ($filters['status'] !== 'all') {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['priority'] !== 'all') {
            $sql .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 't.created_at');
        }

        $metrics = Database::getInstance()->fetch($sql, $params) ?: [];

        $totalTasks = (int) ($metrics['total_tasks'] ?? 0);
        $completedTasks = (int) ($metrics['completed_tasks'] ?? 0);
        $metrics['average_progress'] = $totalTasks > 0
            ? round(($completedTasks / $totalTasks) * 100, 2)
            : 0;

        return $metrics;
    }

    private function getProductivityData($userScope, $filters)
    {
        $sql = "SELECT DATE(t.created_at) as label, COUNT(*) as value
                FROM tasks t
                WHERE 1=1";

        $params = [];

        if ($filters['status'] !== 'all') {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['priority'] !== 'all') {
            $sql .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 't.created_at');
        }

        $sql .= " GROUP BY DATE(t.created_at) ORDER BY DATE(t.created_at) DESC LIMIT 30";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function generateMeetingReport($userScope, $filters)
    {
        $hasMeetingAttendees = $this->reportTableExists('meeting_attendees');
        $hasCreatedBy = Database::getInstance()->columnExists('meetings', 'created_by');

        $sql = "SELECT m.*";

        if ($hasCreatedBy) {
            $sql .= ", u.first_name, u.last_name";
        } else {
            $sql .= ", NULL as first_name, NULL as last_name";
        }

        if ($hasMeetingAttendees) {
            $sql .= ", COUNT(ma.id) as attendee_count,
                       SUM(CASE WHEN ma.attendance_status = 'present' THEN 1 ELSE 0 END) as present_count";
        } else {
            $sql .= ", 0 as attendee_count, 0 as present_count";
        }

        $sql .= "
        FROM meetings m";

    if ($hasCreatedBy) {
        $sql .= "
        LEFT JOIN users u ON m.created_by = u.id";
    }

        if ($hasMeetingAttendees) {
            $sql .= "
                LEFT JOIN meeting_attendees ma ON m.id = ma.meeting_id";
        }

        $sql .= "
                WHERE 1=1";

        $params = [];

        if ($filters['meeting_type'] !== 'all') {
            $sql .= " AND m.meeting_type = ?";
            $params[] = $filters['meeting_type'];
        }

        if ($filters['status'] !== 'all') {
            $sql .= " AND m.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'm.start_datetime');
        }

        $sql .= " GROUP BY m.id ORDER BY m.start_datetime DESC LIMIT 500";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getMeetingAttendanceData($userScope, $filters)
    {
        if (!$this->reportTableExists('meeting_attendees')) {
            return [];
        }

        $sql = "SELECT ma.attendance_status as label, COUNT(*) as value
                FROM meeting_attendees ma
                INNER JOIN meetings m ON ma.meeting_id = m.id
                WHERE 1=1";

        $params = [];

        if ($filters['meeting_type'] !== 'all') {
            $sql .= " AND m.meeting_type = ?";
            $params[] = $filters['meeting_type'];
        }

        if ($filters['status'] !== 'all') {
            $sql .= " AND m.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'm.start_datetime');
        }

        $sql .= " GROUP BY ma.attendance_status ORDER BY value DESC";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getMeetingEffectivenessMetrics($userScope, $filters)
    {
        $hasMeetingAttendees = $this->reportTableExists('meeting_attendees');
        $hasPlatform = Database::getInstance()->columnExists('meetings', 'platform');

        $sql = "SELECT
                    COUNT(*) as total_meetings,
                    SUM(CASE WHEN m.status = 'completed' THEN 1 ELSE 0 END) as completed_meetings,
                    ";

        if ($hasPlatform) {
            $sql .= "SUM(CASE WHEN m.platform IN ('zoom', 'hybrid') THEN 1 ELSE 0 END) as virtual_meetings";
        } else {
            $sql .= "0 as virtual_meetings";
        }

        if ($hasMeetingAttendees) {
            $sql .= ", COALESCE(AVG(COALESCE(attendance_summary.present_count, 0)), 0) as average_present_attendees";
        } else {
            $sql .= ", 0 as average_present_attendees";
        }

        $sql .= "
                FROM meetings m";

        if ($hasMeetingAttendees) {
            $sql .= "
                LEFT JOIN (
                    SELECT meeting_id,
                           SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present_count
                    FROM meeting_attendees
                    GROUP BY meeting_id
                ) attendance_summary ON attendance_summary.meeting_id = m.id";
        }

        $sql .= "
                WHERE 1=1";

        $params = [];

        if ($filters['meeting_type'] !== 'all') {
            $sql .= " AND m.meeting_type = ?";
            $params[] = $filters['meeting_type'];
        }

        if ($filters['status'] !== 'all') {
            $sql .= " AND m.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'm.start_datetime');
        }

        return Database::getInstance()->fetch($sql, $params) ?: [];
    }

    private function generateEventReport($userScope, $filters)
    {
        $hasEventRegistrations = $this->reportTableExists('event_registrations');

        $sql = "SELECT e.*, u.first_name, u.last_name";

        if ($hasEventRegistrations) {
            $sql .= ", COUNT(er.id) as registration_count";
        } else {
            $sql .= ", 0 as registration_count";
        }

        $sql .= "
                FROM events e
                LEFT JOIN users u ON e.created_by = u.id";

        if ($hasEventRegistrations) {
            $sql .= "
                LEFT JOIN event_registrations er ON e.id = er.event_id";
        }

        $sql .= "
                WHERE 1=1";

        $params = [];

        if ($filters['event_type'] !== 'all') {
            $sql .= " AND e.event_type = ?";
            $params[] = $filters['event_type'];
        }

        if ($filters['status'] !== 'all') {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'e.start_datetime');
        }

        $sql .= " GROUP BY e.id ORDER BY e.start_datetime DESC LIMIT 500";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getEventParticipationData($userScope, $filters)
    {
        if (!$this->reportTableExists('event_registrations')) {
            return [];
        }

        $sql = "SELECT er.status as label, COUNT(*) as value
                FROM event_registrations er
                INNER JOIN events e ON er.event_id = e.id
                WHERE 1=1";

        $params = [];

        if ($filters['event_type'] !== 'all') {
            $sql .= " AND e.event_type = ?";
            $params[] = $filters['event_type'];
        }

        if ($filters['status'] !== 'all') {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'e.start_datetime');
        }

        $sql .= " GROUP BY er.status ORDER BY value DESC";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getEventImpactMetrics($userScope, $filters)
    {
        $hasEventRegistrations = $this->reportTableExists('event_registrations');

        $sql = "SELECT
                    COUNT(*) as total_events,
                    SUM(CASE WHEN e.status IN ('open_registration', 'in_progress', 'completed') THEN 1 ELSE 0 END) as published_events,
                    SUM(CASE WHEN e.requires_payment = 1 THEN 1 ELSE 0 END) as paid_events";

        if ($hasEventRegistrations) {
            $sql .= ", COALESCE(AVG(COALESCE(registration_summary.registration_count, 0)), 0) as average_registrations";
        } else {
            $sql .= ", 0 as average_registrations";
        }

        $sql .= "
                FROM events e";

        if ($hasEventRegistrations) {
            $sql .= "
                LEFT JOIN (
                    SELECT event_id, COUNT(*) as registration_count
                    FROM event_registrations
                    GROUP BY event_id
                ) registration_summary ON registration_summary.event_id = e.id";
        }

        $sql .= "
                WHERE 1=1";

        $params = [];

        if ($filters['event_type'] !== 'all') {
            $sql .= " AND e.event_type = ?";
            $params[] = $filters['event_type'];
        }

        if ($filters['status'] !== 'all') {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'e.start_datetime');
        }

        return Database::getInstance()->fetch($sql, $params) ?: [];
    }

    private function generateDonationReport($userScope, $filters)
    {
        $sql = "SELECT d.*
                FROM donations d
                WHERE 1=1";

        $params = [];

        if ($filters['donation_type'] !== 'all') {
            $sql .= " AND d.donation_type = ?";
            $params[] = $filters['donation_type'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'd.created_at');
        }

        if ($filters['amount_range'] !== 'all') {
            switch ($filters['amount_range']) {
                case 'under_100':
                    $sql .= " AND d.amount < 100";
                    break;
                case '100_500':
                    $sql .= " AND d.amount BETWEEN 100 AND 500";
                    break;
                case '500_1000':
                    $sql .= " AND d.amount BETWEEN 500 AND 1000";
                    break;
                case 'over_1000':
                    $sql .= " AND d.amount > 1000";
                    break;
            }
        }

        $sql .= " ORDER BY d.created_at DESC LIMIT 500";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getDonationTrends($userScope, $filters)
    {
        $sql = "SELECT DATE_FORMAT(d.created_at, '%Y-%m') as label,
                       COUNT(*) as donation_count,
                       COALESCE(SUM(d.amount), 0) as total_amount
                FROM donations d
                WHERE 1=1";

        $params = [];

        if ($filters['donation_type'] !== 'all') {
            $sql .= " AND d.donation_type = ?";
            $params[] = $filters['donation_type'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'd.created_at');
        }

        $sql .= " GROUP BY DATE_FORMAT(d.created_at, '%Y-%m') ORDER BY label DESC LIMIT 12";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getDonorAnalysis($userScope, $filters)
    {
        $sql = "SELECT CONCAT(d.donor_type, ' / ', d.payment_status) as label, COUNT(*) as value
                FROM donations d
                WHERE 1=1";

        $params = [];

        if ($filters['donation_type'] !== 'all') {
            $sql .= " AND d.donation_type = ?";
            $params[] = $filters['donation_type'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'd.created_at');
        }

        $sql .= " GROUP BY d.donor_type, d.payment_status ORDER BY value DESC";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function generateCourseReport($userScope, $filters)
    {
        $hasCourseEnrollments = $this->reportTableExists('course_enrollments');

        $sql = "SELECT c.*";

        if ($hasCourseEnrollments) {
            $sql .= ", COUNT(ce.id) as enrollment_count";
        } else {
            $sql .= ", 0 as enrollment_count";
        }

        $sql .= "
                FROM courses c";

        if ($hasCourseEnrollments) {
            $sql .= "
                LEFT JOIN course_enrollments ce ON c.id = ce.course_id";
        }

        $sql .= "
                WHERE 1=1";

        $params = [];

        if ($filters['status'] !== 'all') {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'c.created_at');
        }

        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT 500";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getCourseEnrollmentData($userScope, $filters)
    {
        if (!$this->reportTableExists('course_enrollments')) {
            return [];
        }

        $sql = "SELECT ce.status as label, COUNT(*) as value
                FROM course_enrollments ce
                INNER JOIN courses c ON ce.course_id = c.id
                WHERE 1=1";

        $params = [];

        if ($filters['status'] !== 'all') {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'c.created_at');
        }

        $sql .= " GROUP BY ce.status ORDER BY value DESC";

        return Database::getInstance()->fetchAll($sql, $params);
    }

    private function getCourseCompletionRates($userScope, $filters)
    {
        if (!$this->reportTableExists('course_enrollments')) {
            return [
                'total_enrollments' => 0,
                'completed_enrollments' => 0,
                'average_progress' => 0,
            ];
        }

        $sql = "SELECT
                    COUNT(*) as total_enrollments,
                    SUM(CASE WHEN ce.status = 'completed' THEN 1 ELSE 0 END) as completed_enrollments
                FROM course_enrollments ce
                INNER JOIN courses c ON ce.course_id = c.id
                WHERE 1=1";

        $params = [];

        if ($filters['status'] !== 'all') {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['date_range'] !== 'all') {
            $sql .= " AND " . $this->getDateRangeCondition($filters['date_range'], 'c.created_at');
        }

        $metrics = Database::getInstance()->fetch($sql, $params) ?: [];

        $totalEnrollments = (int) ($metrics['total_enrollments'] ?? 0);
        $completedEnrollments = (int) ($metrics['completed_enrollments'] ?? 0);
        $metrics['average_progress'] = $totalEnrollments > 0
            ? round(($completedEnrollments / $totalEnrollments) * 100, 2)
            : 0;

        return $metrics;
    }

    private function getGodinaStatistics($userScope)
    {
        return Database::getInstance()->fetchAll(
            "SELECT g.name as label, COUNT(ga.id) as value
             FROM godinas g
             LEFT JOIN gamtas ga ON ga.godina_id = g.id
             GROUP BY g.id, g.name
             ORDER BY value DESC, g.name ASC"
        );
    }

    private function getGamtaStatistics($userScope)
    {
        return Database::getInstance()->fetchAll(
            "SELECT ga.name as label, COUNT(gu.id) as value
             FROM gamtas ga
             LEFT JOIN gurmus gu ON gu.gamta_id = ga.id
             GROUP BY ga.id, ga.name
             ORDER BY value DESC, ga.name ASC"
        );
    }

    private function getGurmuStatistics($userScope)
    {
        return Database::getInstance()->fetchAll(
            "SELECT gu.name as label, COUNT(u.id) as value
             FROM gurmus gu
             LEFT JOIN users u ON u.gurmu_id = gu.id AND u.status = 'active'
             GROUP BY gu.id, gu.name
             ORDER BY value DESC, gu.name ASC"
        );
    }

    private function getPositionStatistics($userScope)
    {
        return Database::getInstance()->fetchAll(
            "SELECT p.name as label, COUNT(ua.id) as value
             FROM positions p
             LEFT JOIN user_assignments ua ON ua.position_id = p.id AND ua.status = 'active'
             GROUP BY p.id, p.name
             ORDER BY value DESC, p.name ASC"
        );
    }

    private function reportTableExists($tableName)
    {
        $count = Database::getInstance()->fetchColumn(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?",
            [$tableName]
        );

        return (int) $count > 0;
    }

    // Additional helper methods for other report types...
    
    private function getTotalMembersInScope($userScope)
    {
        $sql = "SELECT COUNT(*) as count FROM users u 
                JOIN user_assignments ua ON u.id = ua.user_id 
                WHERE u.status = 'active' AND ua.status = 'active'";
        
        // Add hierarchy filtering
        if (!empty($userScope)) {
            if ($userScope['level_scope'] === 'gurmu') {
                $sql .= " AND ua.gurmu_id = " . intval($userScope['gurmu_id']);
            } elseif ($userScope['level_scope'] === 'gamta') {
                $sql .= " AND ua.gamta_id = " . intval($userScope['gamta_id']);
            } elseif ($userScope['level_scope'] === 'godina') {
                $sql .= " AND ua.godina_id = " . intval($userScope['godina_id']);
            }
        }
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['count'] : 0;
    }

    private function getActiveTasksInScope($userScope)
    {
        $sql = "SELECT COUNT(*) as count FROM tasks t WHERE t.status IN ('pending', 'in_progress')";
        
        // Add hierarchy filtering for tasks based on user scope
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['count'] : 0;
    }

    private function getUpcomingMeetingsInScope($userScope)
    {
        $sql = "SELECT COUNT(*) as count FROM meetings m 
                WHERE m.start_datetime >= NOW() AND m.status = 'scheduled'";
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['count'] : 0;
    }

    private function getMonthlyDonationsInScope($userScope)
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM donations d 
                WHERE YEAR(d.created_at) = YEAR(NOW()) 
                AND MONTH(d.created_at) = MONTH(NOW())";

        if (Database::getInstance()->columnExists('donations', 'status')) {
            $sql .= " AND d.status != 'deleted'";
        }
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['total'] : 0;
    }

    private function getRecentEventsInScope($userScope)
    {
        $sql = "SELECT COUNT(*) as count FROM events e 
                WHERE e.start_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['count'] : 0;
    }

    private function getRecentReports($userId)
    {
        // Get user's recent report access history
        return [];
    }

    private function exportToCsv($data, $type)
    {
        $rows = $this->flattenExportData($data);
        $handle = fopen('php://temp', 'r+');

        if (!empty($rows)) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
        } else {
            fputcsv($handle, ['message']);
            fputcsv($handle, ['No report rows available']);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $this->sendExportResponse($content, $this->buildExportFilename($type, 'csv'), 'text/csv; charset=UTF-8');
    }

    private function exportToExcel($data, $type)
    {
        $rows = $this->flattenExportData($data);
        $headers = !empty($rows) ? array_keys($rows[0]) : ['message'];

        $html = '<table border="1"><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $html .= '<td>' . htmlspecialchars((string) ($row[$header] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td>No report rows available</td></tr>';
        }

        $html .= '</tbody></table>';

        return $this->sendExportResponse(
            $html,
            $this->buildExportFilename($type, 'xls'),
            'application/vnd.ms-excel; charset=UTF-8'
        );
    }

    private function exportToPdf($data, $type)
    {
        $lines = [
            strtoupper(str_replace('_', ' ', $type)) . ' REPORT',
            'Generated: ' . date('Y-m-d H:i:s'),
            '',
        ];

        foreach ($this->flattenExportData($data) as $row) {
            foreach ($row as $key => $value) {
                $lines[] = $key . ': ' . $value;
            }
            $lines[] = '';
        }

        if (count($lines) === 3) {
            $lines[] = 'No report rows available';
        }

        $content = $this->buildSimplePdf($lines);

        return $this->sendExportResponse($content, $this->buildExportFilename($type, 'pdf'), 'application/pdf');
    }

    private function generateExportData($type, $userScope, $filters)
    {
        $defaultTaskFilters = [
            'status' => $filters['status'] ?? 'all',
            'priority' => $filters['priority'] ?? 'all',
            'date_range' => $filters['date_range'] ?? '30_days',
            'assigned_to' => $filters['assigned_to'] ?? 'all',
        ];

        $defaultMeetingFilters = [
            'date_range' => $filters['date_range'] ?? '30_days',
            'meeting_type' => $filters['meeting_type'] ?? 'all',
            'status' => $filters['status'] ?? 'all',
        ];

        $defaultEventFilters = [
            'date_range' => $filters['date_range'] ?? '90_days',
            'event_type' => $filters['event_type'] ?? 'all',
            'status' => $filters['status'] ?? 'all',
        ];

        $defaultDonationFilters = [
            'date_range' => $filters['date_range'] ?? '30_days',
            'donation_type' => $filters['donation_type'] ?? 'all',
            'category' => $filters['category'] ?? 'all',
            'amount_range' => $filters['amount_range'] ?? 'all',
        ];

        $defaultCourseFilters = [
            'date_range' => $filters['date_range'] ?? '90_days',
            'course_type' => $filters['course_type'] ?? 'all',
            'status' => $filters['status'] ?? 'all',
        ];

        switch ($type) {
            case 'summary':
                return [[
                    'total_members' => $this->getTotalMembersInScope($userScope),
                    'active_tasks' => $this->getActiveTasksInScope($userScope),
                    'upcoming_meetings' => $this->getUpcomingMeetingsInScope($userScope),
                    'monthly_donations' => $this->getMonthlyDonationsInScope($userScope),
                    'recent_events' => $this->getRecentEventsInScope($userScope),
                ]];
            case 'detailed':
                return array_merge(
                    $this->flattenExportData($this->generateTaskReport($userScope, $defaultTaskFilters), 'tasks'),
                    $this->flattenExportData($this->generateMeetingReport($userScope, $defaultMeetingFilters), 'meetings'),
                    $this->flattenExportData($this->generateEventReport($userScope, $defaultEventFilters), 'events'),
                    $this->flattenExportData($this->generateDonationReport($userScope, $defaultDonationFilters), 'donations')
                );
            case 'users':
                return $this->generateUserReport($userScope, [
                    'role' => $filters['role'] ?? 'all',
                    'status' => $filters['status'] ?? 'all',
                    'registration_period' => $filters['registration_period'] ?? '30_days',
                ]);
            case 'hierarchy':
                return $this->generateHierarchyReport($userScope);
            case 'tasks':
                return $this->generateTaskReport($userScope, $defaultTaskFilters);
            case 'meetings':
                return $this->generateMeetingReport($userScope, $defaultMeetingFilters);
            case 'events':
                return $this->generateEventReport($userScope, $defaultEventFilters);
            case 'donations':
                return $this->generateDonationReport($userScope, $defaultDonationFilters);
            case 'courses':
                return $this->generateCourseReport($userScope, $defaultCourseFilters);
            default:
                return [];
        }
    }

    private function normalizeExportType($type)
    {
        $aliases = [
            'financial' => 'donations',
            'membership' => 'users',
            'activities' => 'tasks',
        ];

        return $aliases[$type] ?? $type;
    }

    private function flattenExportData($data, $section = null)
    {
        if ($data === null) {
            return [];
        }

        if (is_array($data) && $this->isAssocArray($data)) {
            $allScalar = true;
            foreach ($data as $value) {
                if (is_array($value) || is_object($value)) {
                    $allScalar = false;
                    break;
                }
            }

            if ($allScalar) {
                return [[
                    'section' => $section ?? 'report',
                ] + array_map([$this, 'stringifyExportValue'], $data)];
            }

            $rows = [];
            foreach ($data as $key => $value) {
                $rows = array_merge($rows, $this->flattenExportData($value, (string) $key));
            }
            return $rows;
        }

        if (is_array($data)) {
            $rows = [];
            foreach ($data as $item) {
                if (is_array($item) && $this->isAssocArray($item)) {
                    $rows[] = [
                        'section' => $section ?? 'report',
                    ] + array_map([$this, 'stringifyExportValue'], $item);
                } else {
                    $rows[] = [
                        'section' => $section ?? 'report',
                        'value' => $this->stringifyExportValue($item),
                    ];
                }
            }
            return $rows;
        }

        return [[
            'section' => $section ?? 'report',
            'value' => $this->stringifyExportValue($data),
        ]];
    }

    private function stringifyExportValue($value)
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function isAssocArray(array $data)
    {
        return array_keys($data) !== range(0, count($data) - 1);
    }

    private function buildExportFilename($type, $extension)
    {
        return sprintf('%s_report_%s.%s', $type, date('Ymd_His'), $extension);
    }

    private function sendExportResponse($content, $filename, $contentType)
    {
        if (PHP_SAPI !== 'cli') {
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
        }

        echo $content;
        return $content;
    }

    private function buildSimplePdf(array $lines)
    {
        $sanitizedLines = [];
        foreach ($lines as $line) {
            $sanitizedLines[] = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string) $line);
        }

        $text = "BT\n/F1 10 Tf\n50 780 Td\n14 TL\n";
        foreach ($sanitizedLines as $index => $line) {
            if ($index === 0) {
                $text .= '(' . $line . ") Tj\n";
            } else {
                $text .= 'T* (' . $line . ") Tj\n";
            }
        }
        $text .= "ET";

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
        $objects[] = "5 0 obj << /Length " . strlen($text) . " >> stream\n" . $text . "\nendstream endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }
        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }
}