<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Utils\Database;
use App\Models\Global;
use App\Models\Godina;
use App\Models\Gamta;
use App\Models\Gurmu;
use App\Models\User;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Position;
use App\Models\UserAssignment;

/**
 * Dashboard Controller - Enhanced with Role-Based Dashboards
 * ABO-WBO Management System
 */
class DashboardController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Welcome page (public access)
     */
    public function welcome()
    {
        // If user is logged in, redirect to dashboard
        if (auth_check()) {
            header('Location: /dashboard');
            exit;
        }
        
        // Simple welcome page without complex rendering
        $this->showWelcomePage();
    }
    
    /**
     * Show simple welcome page
     */
    private function showWelcomePage()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome - ABO-WBO Management System</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                .hero-section {
                    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                    color: white;
                    padding: 100px 0;
                }
                .feature-icon {
                    font-size: 3rem;
                    margin-bottom: 1rem;
                    color: #28a745;
                }
            </style>
        </head>
        <body>
            <!-- Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container">
                    <a class="navbar-brand fw-bold" href="/">
                        <i class="fas fa-users text-success me-2"></i>
                        ABO-WBO Management System
                    </a>
                    <div class="navbar-nav ms-auto">
                        <a class="nav-link" href="/auth/login">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <section class="hero-section">
                <div class="container text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        Welcome to ABO-WBO Management System
                    </h1>
                    <p class="lead mb-5">
                        A comprehensive digital platform for managing global Oromo organizational operations.
                    </p>
                    <div class="d-grid gap-2 d-md-block">
                        <a href="/auth/login" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login to System
                        </a>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-5">
                <div class="container">
                    <div class="row text-center mb-5">
                        <div class="col-12">
                            <h2 class="display-5 fw-bold">System Features</h2>
                            <p class="lead text-muted">Comprehensive tools for organizational management</p>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="feature-icon">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <h4>Hierarchy Management</h4>
                            <p class="text-muted">
                                Manage 4-tier organizational structure: Global → Godina → Gamta → Gurmu levels
                            </p>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div class="feature-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <h4>Position Management</h4>
                            <p class="text-muted">
                                7 executive positions with 5 shared responsibilities across all levels
                            </p>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div class="feature-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h4>Task Management</h4>
                            <p class="text-muted">
                                Cross-functional task assignment and progress tracking
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Login Section -->
            <section class="py-5 bg-light">
                <div class="container text-center">
                    <h2 class="display-5 fw-bold mb-4">Ready to Get Started?</h2>
                    <p class="lead mb-4">Access the system with your credentials</p>
                    <a href="/auth/login" class="btn btn-success btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login Now
                    </a>
                </div>
            </section>

            <!-- Footer -->
            <footer class="bg-dark text-light py-4">
                <div class="container text-center">
                    <p class="mb-0">
                        &copy; <?= date('Y') ?> ABO-WBO Management System. 
                        <span class="text-success">Version 1.0.0</span>
                    </p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit;
    }
    
    /**
     * Main dashboard router - redirects based on role and hierarchy
     */
    public function index()
    {
        $this->requireAuth();
        
        $user = auth_user();
        if (!$user) {
            return redirect('/auth/login');
        }
        
        $userScope = $this->getUserHierarchicalScope($user['id']);
        
        // Route to appropriate dashboard based on role and hierarchy
        switch ($user['role']) {
            case 'admin':
                return $this->adminDashboard($userScope);
            case 'executive':
                return $this->executiveDashboard($userScope);
            case 'member':
                return $this->memberDashboard($userScope);
            default:
                return $this->memberDashboard($userScope);
        }
    }
    
    /**
     * Admin Dashboard - Full system access
     */
    private function adminDashboard($userScope)
    {
        $data = [
            'user' => auth_user(),
            'user_scope' => $userScope,
            'page_title' => 'System Administration Dashboard',
            'total_users' => $this->getTotalUsers(),
            'total_meetings' => $this->getTotalMeetings(),
            'total_tasks' => $this->getTotalTasks(),
            'total_donations' => $this->getTotalDonations(),
            'recent_activities' => $this->getRecentSystemActivities(10),
            'system_stats' => $this->getSystemStatistics(),
            'hierarchy_overview' => $this->getHierarchyOverview(),
            'dashboard_type' => 'admin'
        ];
        
        return $this->render('dashboard.admin', $data);
    }
    
    /**
     * Executive Dashboard - Position and hierarchy specific
     */
    private function executiveDashboard($userScope)
    {
        if (!$userScope) {
            // Handle case where user scope cannot be determined
            return $this->render('dashboard/error', [
                'error_message' => 'Unable to determine your organizational scope. Please contact your administrator.',
                'user' => auth_user()
            ]);
        }

        $data = [
            'user' => auth_user(),
            'user_scope' => $userScope,
            'page_title' => ($userScope['position_name'] ?? 'Executive') . ' Dashboard - ' . ($userScope['scope_name'] ?? 'Organization'),
            'my_tasks' => $this->getHierarchyTasks($userScope),
            'my_meetings' => $this->getHierarchyMeetings($userScope),
            'my_reports' => $this->getHierarchyReports($userScope),
            'hierarchy_members' => $this->getHierarchyMembers($userScope),
            'position_responsibilities' => $this->getPositionResponsibilities($userScope['position_id'] ?? null),
            'recent_activities' => $this->getHierarchyActivities($userScope, 10),
            'hierarchy_stats' => $this->getHierarchyStatistics($userScope)
        ];
        
        // Position-specific data
        if (isset($userScope['position_key'])) {
            switch ($userScope['position_key']) {
                case 'dinagdee':
                    $data['financial_data'] = $this->getFinancialData($userScope);
                    break;
                case 'mediyaa_sab_quunnamtii':
                    $data['media_data'] = $this->getMediaData($userScope);
                    break;
                case 'dura_taa':
                    $data['leadership_data'] = $this->getLeadershipData($userScope);
                    break;
            }
        }
        
        return $this->render('dashboard/executive', $data);
    }
    
    /**
     * Member Dashboard - Limited to own gurmu
     */
    private function memberDashboard($userScope)
    {
        $data = [
            'user' => auth_user(),
            'user_scope' => $userScope,
            'page_title' => 'Member Dashboard - ' . ($userScope['gurmu_name'] ?? 'Community'),
            'my_tasks' => $this->getMemberTasks($userScope),
            'upcoming_meetings' => $this->getMemberMeetings($userScope),
            'community_events' => $this->getCommunityEvents($userScope),
            'my_donations' => $this->getMemberDonations(auth_user()['id'], 5),
            'recent_announcements' => $this->getCommunityAnnouncements($userScope, 5),
            'community_stats' => $this->getCommunityStatistics($userScope)
        ];
        
        return $this->render('dashboard/member', $data);
    }
    
    /**
     * System Administrator Dashboard
     */
    public function systemAdminDashboard()
    {
        $globalStats = $this->getGlobalSystemStats();
        $recentActivity = $this->getRecentSystemActivity();
        $systemHealth = $this->getSystemHealthMetrics();
        $securityAlerts = $this->getSecurityAlerts();
        $performanceMetrics = $this->getPerformanceMetrics();
        
        return $this->render('dashboard.system_admin', [
            'title' => 'System Administration Dashboard',
            'user' => auth_user(),
            'global_stats' => $globalStats,
            'recent_activity' => $recentActivity,
            'system_health' => $systemHealth,
            'security_alerts' => $securityAlerts,
            'performance_metrics' => $performanceMetrics,
            'dashboard_type' => 'system_admin'
        ]);
    }
    
    /**
     * Finance Dashboard (for Finance Managers and Treasurers)
     */
    public function financeDashboard()
    {
        $user = auth_user();
        $hierarchyScope = $this->getUserHierarchyScope($user);
        
        // Financial statistics
        $donationStats = $this->getDonationStatistics($hierarchyScope);
        $budgetStats = $this->getBudgetStatistics($hierarchyScope);
        $campaignStats = $this->getCampaignStatistics($hierarchyScope);
        $expenseStats = $this->getExpenseStatistics($hierarchyScope);
        
        // Recent financial activity
        $recentDonations = $this->getRecentDonations($hierarchyScope, 10);
        $pendingApprovals = $this->getPendingFinancialApprovals($hierarchyScope);
        $activeCampaigns = $this->getActiveCampaigns($hierarchyScope);
        $topDonors = $this->getTopDonors($hierarchyScope, 10);
        
        // Financial trends
        $monthlyTrends = $this->getMonthlyFinancialTrends($hierarchyScope);
        $quarterlyComparison = $this->getQuarterlyComparison($hierarchyScope);
        
        return $this->render('dashboard.finance', [
            'title' => 'Finance Dashboard',
            'user' => $user,
            'hierarchy_scope' => $hierarchyScope,
            'donation_stats' => $donationStats,
            'budget_stats' => $budgetStats,
            'campaign_stats' => $campaignStats,
            'expense_stats' => $expenseStats,
            'recent_donations' => $recentDonations,
            'pending_approvals' => $pendingApprovals,
            'active_campaigns' => $activeCampaigns,
            'top_donors' => $topDonors,
            'monthly_trends' => $monthlyTrends,
            'quarterly_comparison' => $quarterlyComparison,
            'dashboard_type' => 'finance'
        ]);
    }
    
    /**
     * HELPER METHODS FOR DASHBOARD DATA
     */
    
    /**
     * Get user's hierarchical scope for data filtering
     */
    public function getUserHierarchicalScope($userId)
    {
        if (!$userId) {
            return null;
        }
        
        // Get user's hierarchy assignment with position details and calculate scope_id
        $sql = "
            SELECT 
                ua.user_id,
                ua.position_id,
                ua.godina_id,
                ua.gamta_id,
                ua.gurmu_id,
                ua.level_scope,
                p.name as position_name,
                p.key_name as position_key,
                p.hierarchy_type,
                p.responsibilities,
                CASE 
                    WHEN ua.level_scope = 'gurmu' THEN ua.gurmu_id
                    WHEN ua.level_scope = 'gamta' THEN ua.gamta_id
                    WHEN ua.level_scope = 'godina' THEN ua.godina_id
                    ELSE NULL
                END as scope_id,
                CASE 
                    WHEN ua.level_scope = 'global' THEN 'Global Organization'
                    WHEN ua.level_scope = 'godina' THEN go.name
                    WHEN ua.level_scope = 'gamta' THEN ga.name
                    WHEN ua.level_scope = 'gurmu' THEN gu.name
                    ELSE ua.level_scope
                END as scope_name
            FROM user_assignments ua
            LEFT JOIN positions p ON ua.position_id = p.id
            LEFT JOIN godinas go ON ua.godina_id = go.id
            LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
            LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
            WHERE ua.user_id = ? AND ua.status = 'active'
            LIMIT 1
        ";
        
        return $this->db->fetch($sql, [$userId]);
    }
    
    private function getTotalMembers($userScope)
    {
        // Count members based on user's hierarchical scope
        $conditions = [];
        $params = [];
        
        switch ($userScope['level_scope']) {
            case 'gurmu':
                $conditions[] = "ua.gurmu_id = ?";
                $params[] = $userScope['gurmu_id'];
                break;
            case 'gamta':
                $conditions[] = "ua.gamta_id = ?";
                $params[] = $userScope['gamta_id'];
                break;
            case 'godina':
                $conditions[] = "ua.godina_id = ?";
                $params[] = $userScope['godina_id'];
                break;
            case 'global':
                $conditions[] = "1=1"; // Count all members
                break;
        }
        
        $whereClause = implode(" AND ", $conditions);
        
        $sql = "SELECT COUNT(DISTINCT ua.user_id) as total 
                FROM user_assignments ua 
                WHERE ua.status = 'active' AND $whereClause";
        
        $result = $this->db->fetch($sql, $params);
        return $result ? $result['total'] : 0;
    }

    /**
     * Get user's primary role
     */
    private function getUserRole($user)
    {
        // Return default role if user is null or doesn't have id
        if (!$user || !isset($user['id'])) {
            return 'guest';
        }
        
        // Check user assignments to determine primary role
        $sql = "SELECT p.name, p.hierarchy_type, p.responsibilities 
                FROM user_assignments ua
                INNER JOIN positions p ON ua.position_id = p.id
                WHERE ua.user_id = ? AND ua.status = 'active'
                ORDER BY p.level DESC
                LIMIT 1";
        
        $assignment = $this->db->fetchAll($sql, [$user['id']]);
        
        if (empty($assignment)) {
            return 'member'; // Default role
        }
        
        $position = $assignment[0];
        
        // Map position names to dashboard roles
        $roleMappings = [
            'System Administrator' => 'system_admin',
            'Super Admin' => 'super_admin',
            'Finance Manager' => 'finance_manager',
            'Treasurer' => 'treasurer',
            'Chairman' => 'chairman',
            'Vice Chairman' => 'vice_chairman',
            'Secretary' => 'secretary',
            'Committee Member' => 'committee_member'
        ];
        
        return $roleMappings[$position['name']] ?? 'member';
    }
    
    /**
     * Get user's hierarchy scope
     */
    private function getUserHierarchyScope($user)
    {
        $sql = "SELECT ua.level_scope, ua.global_id, ua.godina_id, ua.gamta_id, ua.gurmu_id,
                       COALESCE(g.name, gd.name, ga.name, gu.name, 'Global') as scope_name
                FROM user_assignments ua
                LEFT JOIN globals g ON ua.global_id = g.id
                LEFT JOIN godinas gd ON ua.godina_id = gd.id
                LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
                LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
                WHERE ua.user_id = ? AND ua.status = 'active'
                ORDER BY 
                    CASE ua.level_scope 
                        WHEN 'gurmu' THEN 4
                        WHEN 'gamta' THEN 3
                        WHEN 'godina' THEN 2
                        WHEN 'global' THEN 1
                        ELSE 0
                    END DESC
                LIMIT 1";
        
        $result = $this->db->fetchAll($sql, [$user['id']]);
        
        if (empty($result)) {
            return [
                'level' => 'global',
                'id' => 1,
                'name' => 'Global Organization'
            ];
        }
        
        $scope = $result[0];
        return [
            'level' => $scope['level_scope'],
            'id' => $scope[$scope['level_scope'] . '_id'],
            'name' => $scope['scope_name']
        ];
    }
    
    /**
     * Get global system statistics
     */
    private function getGlobalSystemStats()
    {
        // $globalModel = new Global();
        // $global = $globalModel->getDefault();
        
        return [
            'total_users' => $this->getTotalUsers(),
            'total_godinas' => $this->getTotalGodinas(),
            'total_gamtas' => $this->getTotalGamtas(),
            'total_gurmus' => $this->getTotalGurmus(),
            'total_positions' => $this->getTotalPositions(),
            'active_sessions' => $this->getActiveSessions(),
            'system_version' => '1.0.0',
            'last_backup' => $this->getLastBackupTime(),
            'database_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsed()
        ];
    }
    
    /**
     * Get donation statistics for dashboard
     */
    private function getDonationStatistics($hierarchyScope)
    {
        if (!class_exists('App\Models\Donation')) {
            return $this->getPlaceholderDonationStats();
        }
        
        return Donation::getDashboardStats($hierarchyScope['level'], $hierarchyScope['id']);
    }
    
    /**
     * Get placeholder donation stats when Donation model isn't available
     */
    private function getPlaceholderDonationStats()
    {
        return [
            'total_donations' => 0,
            'approved_donations' => 0,
            'pending_donations' => 0,
            'total_amount' => 0,
            'monthly_amount' => 0,
            'unique_donors' => 0
        ];
    }
    
    /**
     * Get recent donations
     */
    private function getRecentDonations($hierarchyScope, $limit = 10)
    {
        if (!class_exists('App\Models\Donation')) {
            return [];
        }
        
        return Donation::getByHierarchyScope($hierarchyScope['level'], $hierarchyScope['id'], ['limit' => $limit]);
    }
    
    /**
     * Helper methods that return placeholder data for now
     * These would be implemented with actual database queries
     */
    
    private function getTotalUsers()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getTotalGodinas()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM godinas";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getTotalGamtas()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM gamtas";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getTotalGurmus()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM gurmus";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getTotalPositions()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM positions WHERE status = 'active'";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getTotalMeetings()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM meetings";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getTotalTasks()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM tasks";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getTotalDonations()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM donations";
        $result = $db->fetchAll($sql);
        return $result[0]['count'] ?? 0;
    }
    
    private function getRecentSystemActivities($limit = 10)
    {
        // For now return empty array - can be implemented later
        return [];
    }
    
    private function getSystemStatistics()
    {
        return [
            'database_size' => '0 MB',
            'storage_used' => '0 MB',
            'active_sessions' => 0,
            'last_backup' => 'Never'
        ];
    }
    
    private function getHierarchyOverview()
    {
        return [
            'total_godinas' => $this->db->fetchColumn("SELECT COUNT(*) FROM godinas"),
            'total_gamtas' => $this->db->fetchColumn("SELECT COUNT(*) FROM gamtas"),
            'total_gurmus' => $this->db->fetchColumn("SELECT COUNT(*) FROM gurmus"),
            'total_assignments' => $this->db->fetchColumn("SELECT COUNT(*) FROM user_assignments WHERE status = 'active'")
        ];
    }
    
    // Additional placeholder methods - would be implemented with real functionality
    private function getRecentSystemActivity() { return []; }
    private function getSystemHealthMetrics() { return ['status' => 'healthy']; }
    private function getSecurityAlerts() { return []; }
    private function getPerformanceMetrics() { return ['cpu' => 45, 'memory' => 67, 'disk' => 23]; }
    private function getBudgetStatistics($scope) { return ['total' => 0, 'allocated' => 0, 'spent' => 0]; }
    private function getCampaignStatistics($scope) { return ['total' => 0, 'active' => 0, 'raised' => 0]; }
    private function getExpenseStatistics($scope) { return ['total' => 0, 'pending' => 0, 'approved' => 0]; }
    private function getPendingFinancialApprovals($scope) { return []; }
    private function getActiveCampaigns($scope) { return []; }
    private function getTopDonors($scope, $limit) { return []; }
    private function getMonthlyFinancialTrends($scope) { return []; }
    private function getQuarterlyComparison($scope) { return []; }
    private function getMembershipStatistics($scope) { return ['total' => 0, 'new' => 0, 'active' => 0]; }
    private function getActivityStatistics($scope) { return ['tasks' => 0, 'meetings' => 0, 'events' => 0]; }
    private function getFinancialOverview($scope) { return ['income' => 0, 'expenses' => 0]; }
    private function getRecentActivities($scope, $limit) { return []; }
    private function getUpcomingMeetings($scope, $limit) { return []; }
    private function getPendingTasks($userId, $limit) { return []; }
    private function getApprovalQueue($scope) { return []; }
    private function getMemberEngagementMetrics($scope) { return ['score' => 75]; }
    private function getGoalProgress($scope) { return []; }
    private function getKPIMetrics($scope) { return []; }
    private function getUserCommittees($userId) { return []; }
    private function getCommitteeStatistics($committees) { return []; }
    private function getProjectStatistics($committees) { return []; }
    private function getCommitteeMemberStats($committees) { return []; }
    
    /**
     * Hierarchy-specific data retrieval methods
     */
    private function getHierarchyTasks($userScope)
    {
        // Now we can filter by hierarchy using the new columns!
        $level = $userScope['level_scope'];
        $scopeId = $userScope['scope_id'];
        
        // Build query based on hierarchy level
        $conditions = ["(t.created_by = ? OR t.assigned_to = ?)"];
        $params = [$userScope['user_id'], $userScope['user_id']];
        
        // Add hierarchy filtering based on user's level
        if ($level === 'gurmu' && $userScope['gurmu_id']) {
            $conditions[] = "(t.level_scope = 'gurmu' AND t.gurmu_id = ?) OR (t.level_scope = 'personal' AND (t.created_by = ? OR t.assigned_to = ?))";
            $params[] = $userScope['gurmu_id'];
            $params[] = $userScope['user_id'];
            $params[] = $userScope['user_id'];
        } elseif ($level === 'gamta' && $userScope['gamta_id']) {
            $conditions[] = "(t.level_scope IN ('gamta', 'gurmu') AND t.gamta_id = ?) OR (t.level_scope = 'personal' AND (t.created_by = ? OR t.assigned_to = ?))";
            $params[] = $userScope['gamta_id'];
            $params[] = $userScope['user_id'];
            $params[] = $userScope['user_id'];
        } elseif ($level === 'godina' && $userScope['godina_id']) {
            $conditions[] = "(t.level_scope IN ('godina', 'gamta', 'gurmu') AND t.godina_id = ?) OR (t.level_scope = 'personal' AND (t.created_by = ? OR t.assigned_to = ?))";
            $params[] = $userScope['godina_id'];
            $params[] = $userScope['user_id'];
            $params[] = $userScope['user_id'];
        } elseif ($level === 'global') {
            // Global users can see all tasks
            $conditions[] = "1=1";
        }
        
        $whereClause = "(" . implode(") AND (", $conditions) . ")";
        
        $sql = "SELECT t.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                       CASE 
                           WHEN t.level_scope = 'personal' THEN 'Personal'
                           WHEN t.level_scope = 'gurmu' THEN CONCAT('Gurmu: ', gu.name)
                           WHEN t.level_scope = 'gamta' THEN CONCAT('Gamta: ', ga.name)
                           WHEN t.level_scope = 'godina' THEN CONCAT('Godina: ', go.name)
                           WHEN t.level_scope = 'global' THEN 'Global'
                           ELSE t.level_scope
                       END as scope_display
                FROM tasks t 
                LEFT JOIN users u ON t.assigned_to = u.id 
                LEFT JOIN gurmus gu ON t.gurmu_id = gu.id
                LEFT JOIN gamtas ga ON t.gamta_id = ga.id
                LEFT JOIN godinas go ON t.godina_id = go.id
                WHERE $whereClause
                ORDER BY t.priority DESC, t.due_date ASC, t.created_at DESC 
                LIMIT 15";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function getHierarchyMeetings($userScope)
    {
        // Use the meetings table's level_scope and scope_id columns
        $sql = "
            SELECT * FROM meetings 
            WHERE level_scope = ? AND scope_id = ?
            ORDER BY start_datetime DESC 
            LIMIT 5
        ";
        
        $scopeId = null;
        switch ($userScope['level_scope']) {
            case 'gurmu':
                $scopeId = $userScope['gurmu_id'];
                break;
            case 'gamta':
                $scopeId = $userScope['gamta_id'];
                break;
            case 'godina':
                $scopeId = $userScope['godina_id'];
                break;
            default:
                $scopeId = 0; // Global scope
        }
        
        return $this->db->fetchAll($sql, [$userScope['level_scope'], $scopeId]);
    }
    
    private function getHierarchyEvents($userScope)
    {
        // Use the enhanced events table with hierarchy columns
        $level = $userScope['level_scope'];
        
        $conditions = [];
        $params = [];
        
        // Build hierarchy-based filtering for events
        if ($level === 'gurmu' && $userScope['gurmu_id']) {
            $conditions[] = "(e.level_scope = 'gurmu' AND e.gurmu_id = ?) OR e.level_scope = 'global'";
            $params[] = $userScope['gurmu_id'];
        } elseif ($level === 'gamta' && $userScope['gamta_id']) {
            $conditions[] = "(e.level_scope IN ('gamta', 'gurmu') AND e.gamta_id = ?) OR e.level_scope = 'global'";
            $params[] = $userScope['gamta_id'];
        } elseif ($level === 'godina' && $userScope['godina_id']) {
            $conditions[] = "(e.level_scope IN ('godina', 'gamta', 'gurmu') AND e.godina_id = ?) OR e.level_scope = 'global'";
            $params[] = $userScope['godina_id'];
        } elseif ($level === 'global') {
            $conditions[] = "1=1"; // Global users see all events
        }
        
        $whereClause = implode(" OR ", $conditions);
        
        $sql = "SELECT e.*, 
                       CASE 
                           WHEN e.level_scope = 'gurmu' THEN CONCAT('Gurmu: ', gu.name)
                           WHEN e.level_scope = 'gamta' THEN CONCAT('Gamta: ', ga.name)
                           WHEN e.level_scope = 'godina' THEN CONCAT('Godina: ', go.name)
                           WHEN e.level_scope = 'global' THEN 'Global'
                           ELSE e.level_scope
                       END as scope_display
                FROM events e 
                LEFT JOIN gurmus gu ON e.gurmu_id = gu.id
                LEFT JOIN gamtas ga ON e.gamta_id = ga.id
                LEFT JOIN godinas go ON e.godina_id = go.id
                WHERE $whereClause
                ORDER BY e.event_date ASC 
                LIMIT 10";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function getHierarchyMembers($userScope)
    {
        $conditions = [];
        $params = [];
        
        // Filter members based on user's hierarchical scope
        if ($userScope['level_scope'] === 'gurmu' && $userScope['gurmu_id']) {
            $conditions[] = "ua.gurmu_id = ?";
            $params[] = $userScope['gurmu_id'];
        } elseif ($userScope['level_scope'] === 'gamta' && $userScope['gamta_id']) {
            $conditions[] = "ua.gamta_id = ?";
            $params[] = $userScope['gamta_id'];
        } elseif ($userScope['level_scope'] === 'godina' && $userScope['godina_id']) {
            $conditions[] = "ua.godina_id = ?";
            $params[] = $userScope['godina_id'];
        }
        
        $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : 'WHERE 1=1';
        
        $sql = "
            SELECT u.id, u.first_name, u.last_name, u.email, p.name as position_name
            FROM users u
            JOIN user_assignments ua ON u.id = ua.user_id
            JOIN positions p ON ua.position_id = p.id
            {$whereClause}
            AND ua.status = 'active'
            ORDER BY u.first_name, u.last_name
            LIMIT 20
        ";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function getPositionResponsibilities($positionId)
    {
        if (!$positionId) return [];
        
        $position = $this->db->fetch("SELECT responsibilities FROM positions WHERE id = ?", [$positionId]);
        
        if ($position && $position['responsibilities']) {
            return json_decode($position['responsibilities'], true) ?: [];
        }
        
        return [];
    }
    
    private function getHierarchyActivities($userScope, $limit = 10)
    {
        // For now, return empty array - can be implemented later
        return [];
    }
    
    private function getHierarchyReports($userScope)
    {
        // For now, return empty array - can be implemented later  
        return [];
    }
    
    private function getHierarchyStatistics($userScope)
    {
        $stats = [
            'total_members' => 0,
            'active_tasks' => 0,
            'upcoming_meetings' => 0,
            'completed_events' => 0
        ];
        
        $conditions = [];
        $params = [];
        
        // Build hierarchy filter conditions
        if ($userScope['level_scope'] === 'gurmu' && $userScope['gurmu_id']) {
            $conditions[] = "gurmu_id = ?";
            $params[] = $userScope['gurmu_id'];
        } elseif ($userScope['level_scope'] === 'gamta' && $userScope['gamta_id']) {
            $conditions[] = "gamta_id = ?";
            $params[] = $userScope['gamta_id'];
        } elseif ($userScope['level_scope'] === 'godina' && $userScope['godina_id']) {
            $conditions[] = "godina_id = ?";  
            $params[] = $userScope['godina_id'];
        }
        
        $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : 'WHERE 1=1';
        
        // Count members in scope
        $sql = "SELECT COUNT(DISTINCT ua.user_id) as count FROM user_assignments ua {$whereClause} AND ua.status = 'active'";
        $result = $this->db->fetchAll($sql, $params);
        $stats['total_members'] = $result[0]['count'] ?? 0;
        
        return $stats;
    }
    
    // Position-specific data methods
    private function getFinancialData($userScope)
    {
        // Financial data for Dinagdee (Treasurer) position
        return [
            'budget_overview' => 'Available for implementation',
            'recent_transactions' => [],
            'financial_reports' => []
        ];
    }
    
    private function getMediaData($userScope)
    {
        // Media data for Media & Public Relations position
        return [
            'recent_publications' => [],
            'social_media_stats' => ['followers' => 0, 'engagement' => 0],
            'press_releases' => []
        ];
    }
    
    private function getLeadershipData($userScope)
    {
        // Leadership data for Dura-taa (Chairman) position
        return [
            'organization_health' => 'Good',
            'strategic_initiatives' => [],
            'leadership_metrics' => []
        ];
    }
    private function getUpcomingCommitteeMeetings($committees, $limit) { return []; }
    private function getAssignedTasks($userId, $limit) { return []; }
    private function getActiveProjects($committees) { return []; }
    private function getCommitteeProgress($committees) { return []; }
    private function getTaskCompletionStats($userId) { return ['completed' => 0, 'pending' => 0]; }
    private function getMemberProfile($userId) { return []; }
    private function getMemberStatistics($userId) { return []; }
    private function getMemberActivities($userId, $limit) { return []; }
    private function getUpcomingCommunityEvents($scope, $limit) { return []; }
    private function getCommunityNews($scope, $limit) { return []; }
    private function getRecentMembers($scope, $limit) { return []; }
    private function getPersonalTasks($userId, $limit) { return []; }
    private function getPersonalMeetings($userId, $limit) { return []; }
    private function getMemberDonations($userId, $limit) { return []; }
    private function getMemberEngagementScore($userId) { return 75; }
    private function getMemberAchievements($userId) { return []; }
    private function getMemberRecommendations($userId) { return []; }
    private function getUserPosition($user) { return null; }
    private function getActiveSessions() { return 0; }
    private function getLastBackupTime() { return date('Y-m-d H:i:s'); }
    private function getDatabaseSize() { return '0 MB'; }
    private function getStorageUsed() { return '0 MB'; }
    
    /**
     * Member Dashboard Methods - Scope-based data retrieval
     */
    private function getMemberTasks($userScope)
    {
        if (!$userScope || !isset($userScope['scope_id'])) {
            return [];
        }
        
        $user = auth_user();
        
        try {
            // Get tasks assigned to this member or related to their scope
            $tasks = $this->db->fetchAll("
                SELECT 
                    t.id,
                    t.title,
                    t.description,
                    t.status,
                    t.priority,
                    t.due_date,
                    t.assigned_to,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE (t.assigned_to = ? OR t.scope_id = ?)
                AND t.status != 'completed'
                ORDER BY t.priority DESC, t.due_date ASC
                LIMIT 10
            ", [$user['id'], $userScope['scope_id']]);
            
            return $tasks ?: [];
        } catch (\Exception $e) {
            log_error("Error fetching member tasks: " . $e->getMessage());
            return [];
        }
    }
    
    private function getMemberMeetings($userScope)
    {
        if (!$userScope) {
            return [];
        }
        
        try {
            // Get upcoming meetings for this member's scope
            $meetings = $this->db->fetchAll("
                SELECT 
                    m.id,
                    m.title,
                    m.description,
                    m.meeting_date,
                    m.meeting_time,
                    m.location,
                    m.meeting_type,
                    m.status
                FROM meetings m
                WHERE m.scope_id = ? 
                AND m.meeting_date >= CURDATE()
                AND m.status = 'scheduled'
                ORDER BY m.meeting_date ASC, m.meeting_time ASC
                LIMIT 5
            ", [$userScope['scope_id'] ?? 0]);
            
            return $meetings ?: [];
        } catch (\Exception $e) {
            log_error("Error fetching member meetings: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCommunityEvents($userScope)
    {
        if (!$userScope) {
            return [];
        }
        
        try {
            // Get upcoming community events
            $events = $this->db->fetchAll("
                SELECT 
                    e.id,
                    e.title,
                    e.description,
                    e.event_date,
                    e.event_time,
                    e.location,
                    e.event_type,
                    e.status
                FROM events e
                WHERE e.scope_id = ? 
                AND e.event_date >= CURDATE()
                AND e.status = 'active'
                ORDER BY e.event_date ASC
                LIMIT 5
            ", [$userScope['scope_id'] ?? 0]);
            
            return $events ?: [];
        } catch (\Exception $e) {
            log_error("Error fetching community events: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCommunityAnnouncements($userScope, $limit = 5)
    {
        if (!$userScope) {
            return [];
        }
        
        try {
            // Get recent announcements for this member's scope
            $announcements = $this->db->fetchAll("
                SELECT 
                    a.id,
                    a.title,
                    a.content,
                    a.announcement_type,
                    a.created_at,
                    CONCAT(u.first_name, ' ', u.last_name) as author_name
                FROM announcements a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE a.scope_id = ? 
                AND a.status = 'active'
                ORDER BY a.created_at DESC
                LIMIT ?
            ", [$userScope['scope_id'] ?? 0, $limit]);
            
            return $announcements ?: [];
        } catch (\Exception $e) {
            log_error("Error fetching community announcements: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCommunityStatistics($userScope)
    {
        if (!$userScope) {
            return [
                'total_members' => 0,
                'active_tasks' => 0,
                'upcoming_meetings' => 0,
                'recent_donations' => 0
            ];
        }
        
        try {
            // Get basic community statistics
            $stats = [
                'total_members' => $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM user_assignments WHERE scope_id = ? AND status = 'active'",
                    [$userScope['scope_id'] ?? 0]
                ) ?: 0,
                'active_tasks' => $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM tasks WHERE scope_id = ? AND status != 'completed'",
                    [$userScope['scope_id'] ?? 0]
                ) ?: 0,
                'upcoming_meetings' => $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM meetings WHERE scope_id = ? AND meeting_date >= CURDATE()",
                    [$userScope['scope_id'] ?? 0]
                ) ?: 0,
                'recent_donations' => $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM donations WHERE scope_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                    [$userScope['scope_id'] ?? 0]
                ) ?: 0
            ];
            
            return $stats;
        } catch (\Exception $e) {
            log_error("Error fetching community statistics: " . $e->getMessage());
            return [
                'total_members' => 0,
                'active_tasks' => 0,
                'upcoming_meetings' => 0,
                'recent_donations' => 0
            ];
        }
    }
    
    /**
     * Legacy method for backward compatibility
     */
    public function getDashboardStats(): array
    {
        return $this->getGlobalSystemStats();
    }
}