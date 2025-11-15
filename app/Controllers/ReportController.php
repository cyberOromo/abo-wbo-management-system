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
            
            return $this->render('reports/users', [
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
            
            return $this->render('reports/hierarchy', [
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
            
            return $this->render('reports/tasks', [
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
            
            return $this->render('reports/meetings', [
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
            
            return $this->render('reports/events', [
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
            
            return $this->render('reports/donations', [
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
            
            return $this->render('reports/courses', [
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
            
            // Validate report type and user permissions
            if (!$this->canExportReport($type, $user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $reportData = $this->generateExportData($type, $userScope, $filters);
            
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($reportData, $type);
                case 'excel':
                    return $this->exportToExcel($reportData, $type);
                case 'pdf':
                    return $this->exportToPdf($reportData, $type);
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

    private function getDateRangeCondition($range)
    {
        switch ($range) {
            case '7_days':
                return "t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30_days':
                return "t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90_days':
                return "t.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'this_year':
                return "YEAR(t.created_at) = YEAR(NOW())";
            default:
                return "1=1";
        }
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
                WHERE m.start_date >= NOW() AND m.status = 'scheduled'";
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['count'] : 0;
    }

    private function getMonthlyDonationsInScope($userScope)
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM donations d 
                WHERE YEAR(d.created_at) = YEAR(NOW()) 
                AND MONTH(d.created_at) = MONTH(NOW()) 
                AND d.status != 'deleted'";
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['total'] : 0;
    }

    private function getRecentEventsInScope($userScope)
    {
        $sql = "SELECT COUNT(*) as count FROM events e 
                WHERE e.start_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $result = Database::getInstance()->fetch($sql);
        return $result ? $result['count'] : 0;
    }

    private function getRecentReports($userId)
    {
        // Get user's recent report access history
        return [];
    }

    // Export methods would be implemented here
    private function exportToCsv($data, $type) {}
    private function exportToExcel($data, $type) {}
    private function exportToPdf($data, $type) {}
    private function generateExportData($type, $userScope, $filters) { return []; }
}