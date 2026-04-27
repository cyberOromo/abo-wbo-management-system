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
use App\Models\Event;
use App\Models\Meeting;
use App\Models\Position;
use App\Models\Task;
use App\Models\UserAssignment;
use App\Models\Course;
use PDO;

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
     * Main dashboard router - redirects based on user_type and hierarchy
     */
    public function index()
    {
        $this->requireAuth();
        
        $user = auth_user();
        $userScope = $this->getUserHierarchicalScope($user);

        // Prefer the active assignment when it exists so promoted users land on the correct dashboard.
        $userType = $this->resolveDashboardUserType($user, $userScope);
        
        switch ($userType) {
            case 'admin':
            case 'system_admin':
                return $this->adminDashboard($userScope);
            case 'executive':
                return $this->executiveDashboard($userScope);
            case 'member':
            default:
                return $this->memberDashboard($userScope);
        }
    }

    private function resolveDashboardUserType($user, $userScope): string
    {
        $storedRole = (string) ($user['role'] ?? 'member');

        if (in_array($storedRole, ['admin', 'system_admin', 'super_admin'], true)) {
            return $storedRole === 'super_admin' ? 'system_admin' : $storedRole;
        }

        if (!empty($userScope['position_id']) && ($userScope['position_key'] ?? 'member') !== 'member') {
            return 'executive';
        }

        return 'member';
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
            'email_stats' => $this->getEmailStatistics()
        ];
        
        echo $this->render('dashboard/admin', $data);
    }
    
    /**
     * Executive Dashboard - Position and hierarchy specific
     */
    private function executiveDashboard($userScope)
    {
        $data = [
            'user' => auth_user(),
            'user_scope' => $userScope,
            'page_title' => $userScope['position_name'] . ' Dashboard - ' . $userScope['scope_name'],
            'my_tasks' => $this->getHierarchyTasks($userScope),
            'my_meetings' => $this->getHierarchyMeetings($userScope),
            'my_reports' => $this->getHierarchyReports($userScope),
            'hierarchy_members' => $this->getHierarchyMembers($userScope),
            'position_responsibilities' => $this->getPositionResponsibilities($userScope['position_id']),
            'recent_activities' => $this->getHierarchyActivities($userScope, 10),
            'hierarchy_stats' => $this->getHierarchyStatistics($userScope)
        ];
        
        // Position-specific data
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
        
        echo $this->render('dashboard/executive', $data);
    }
    
    /**
     * Member Dashboard - Limited to own gurmu
     */
    private function memberDashboard($userScope)
    {
        $memberTasks = $this->getMemberTasks($userScope);
        $upcomingMeetings = $this->getMemberMeetings($userScope);
        $communityEvents = $this->getCommunityEvents($userScope);
        $memberDonations = $this->getMemberDonations($userScope);
        $memberCourses = $this->getMemberCourses($userScope);
        $trainingSessions = $this->getTrainingSessions($upcomingMeetings);
        $gurmuInfo = $this->getMemberGurmuInfo($userScope);

        $data = [
            'user' => array_merge(auth_user() ?? [], [
                'gurmu_name' => $userScope['gurmu_name'] ?? null,
            ]),
            'user_scope' => $userScope,
            'page_title' => 'Member Dashboard - ' . ($userScope['gurmu_name'] ?? 'Community'),
            'stats' => [
                'upcoming_events' => count($communityEvents),
                'my_meetings' => count($upcomingMeetings),
                'my_tasks' => count($memberTasks),
                'my_donations' => count($memberDonations),
                'active_courses' => count($memberCourses),
                'training_sessions' => count($trainingSessions),
            ],
            'myTasks' => $memberTasks,
            'upcomingMeetings' => $upcomingMeetings,
            'recentEvents' => $communityEvents,
            'myDonations' => $memberDonations,
            'memberCourses' => $memberCourses,
            'trainingSessions' => $trainingSessions,
            'gurmuInfo' => $gurmuInfo,
            'recent_announcements' => $this->getCommunityAnnouncements($userScope, 5),
            'community_stats' => $this->getCommunityStatistics($userScope)
        ];
        
        echo $this->render('dashboard/member', $data);
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
    private function getUserHierarchicalScope($user)
    {
        if (!$user || !isset($user['id'])) {
            return null;
        }
        
        $db = Database::getInstance();
        $pdo = $db->getPdo();
        
        // Get user's hierarchy assignment with position details
        // Note: user_assignments uses separate godina_id, gamta_id, gurmu_id columns (not organizational_unit_id)
        $stmt = $pdo->prepare("
            SELECT 
                ua.user_id,
                ua.position_id,
                ua.global_id,
                ua.godina_id,
                ua.gamta_id,
                ua.gurmu_id,
                ua.level_scope,
                    p.name_en as position_name,
                p.key_name as position_key,
                p.hierarchy_type as hierarchy_type,
                p.responsibilities as responsibilities,
                CASE 
                    WHEN ua.level_scope = 'global' THEN 'Global Organization'
                    WHEN ua.level_scope = 'godina' THEN go.name 
                    WHEN ua.level_scope = 'gamta' THEN ga.name
                    WHEN ua.level_scope = 'gurmu' THEN gu.name
                    ELSE 'Unknown Scope'
                END as scope_name,
                go.name as godina_name,
                ga.name as gamta_name,
                gu.name as gurmu_name
            FROM user_assignments ua
            JOIN positions p ON ua.position_id = p.id
            LEFT JOIN godinas go ON ua.godina_id = go.id
            LEFT JOIN gamtas ga ON ua.gamta_id = ga.id  
            LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
            WHERE ua.user_id = ? AND ua.status = 'active'
            ORDER BY ua.created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([$user['id']]);
        $scope = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$scope) {
            // Default scope for users without assignments
            return [
                'user_id' => $user['id'],
                'position_id' => null,
                'position_name' => 'Member',
                'position_key' => 'member',
                'hierarchy_type' => 'member',
                'level_scope' => 'global',
                'organizational_unit_id' => null,
                'scope_name' => 'Global Organization',
                'godina_id' => null,
                'gamta_id' => null,
                'gurmu_id' => null,
                'godina_name' => null,
                'gamta_name' => null,
                'gurmu_name' => null
            ];
        }
        
        return $scope;
    }    /**
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
        
            return $roleMappings[$position['name_en']] ?? 'member';
    }
    
    /**
     * Get user's hierarchy scope
     */
    private function getUserHierarchyScope($user)
    {
        // Simplified version - return default global scope
        // Original query used non-existent global_id, godina_id, gamta_id, gurmu_id columns
        return [
            'level' => 'global',
            'id' => 1,
            'name' => 'Global Organization'
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
        $sql = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    private function getTotalMeetings()
    {
        $sql = "SELECT COUNT(*) as count FROM meetings";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    private function getTotalTasks()
    {
        $sql = "SELECT COUNT(*) as count FROM tasks";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    private function getTotalDonations()
    {
        $sql = "SELECT COUNT(*) as count FROM donations";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    private function getTotalGodinas()
    {
        $sql = "SELECT COUNT(*) as count FROM godinas WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    private function getTotalGamtas()
    {
        $sql = "SELECT COUNT(*) as count FROM gamtas WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    private function getTotalGurmus()
    {
        $sql = "SELECT COUNT(*) as count FROM gurmus WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    private function getTotalPositions()
    {
        $sql = "SELECT COUNT(*) as count FROM positions WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get email system statistics
     */
    private function getEmailStatistics()
    {
        $total = $this->db->fetch("SELECT COUNT(*) as count FROM internal_emails");
        $active = $this->db->fetch("SELECT COUNT(*) as count FROM internal_emails WHERE status = 'active'");
        $inactive = $this->db->fetch("SELECT COUNT(*) as count FROM internal_emails WHERE status = 'inactive'");
        
        return [
            'total_emails' => $total['count'] ?? 0,
            'active_emails' => $active['count'] ?? 0,
            'inactive_emails' => $inactive['count'] ?? 0
        ];
    }
    
    /**
     * Get hierarchy overview statistics
     * Returns total counts of active Godinas, Gamtas, Gurmus, and assignments
     */
    private function getHierarchyOverview()
    {
        return [
            'total_godinas' => $this->getTotalGodinas(),
            'total_gamtas' => $this->getTotalGamtas(),
            'total_gurmus' => $this->getTotalGurmus(),
            'total_assignments' => $this->getTotalAssignments()
        ];
    }
    
    /**
     * Get total active assignments
     */
    private function getTotalAssignments()
    {
        $sql = "SELECT COUNT(*) as count FROM user_assignments WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
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
     * SIMPLIFIED VERSION - returns empty arrays to allow dashboard to load
     * TODO: Implement proper filtering using level_scope + target_audience JSON
     */
    private function getHierarchyTasks($userScope)
    {
        // Return empty array for now - tasks table schema uses level_scope + target_audience JSON
        // not separate godina_id/gamta_id/gurmu_id columns
        return [];
    }
    
    private function getHierarchyMeetings($userScope)
    {
        // Return empty array for now - meetings table schema uses level_scope + target_audience JSON  
        // not separate godina_id/gamta_id/gurmu_id columns
        return [];
    }
    
    private function getHierarchyMembers($userScope)
    {
        // Return empty array for now - need to implement proper user_assignments filtering
        // using organizational_unit_id not separate hierarchy ID columns
        return [];
    }
    
    private function getPositionResponsibilities($positionId)
    {
        if (!$positionId) return [];
        
        $db = Database::getInstance();
        $pdo = $db->getPdo();
        
        // positions table has permissions column (JSON), not responsibilities
        $stmt = $pdo->prepare("SELECT permissions FROM positions WHERE id = ?");
        $stmt->execute([$positionId]);
        $position = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($position && $position['permissions']) {
            return json_decode($position['permissions'], true) ?: [];
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
        // Return basic stats - all zeros for now to allow dashboard to load
        return [
            'total_members' => 0,
            'active_tasks' => 0,
            'upcoming_meetings' => 0,
            'completed_events' => 0
        ];
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
    
    // Admin Dashboard Methods - Check if these already exist below
    private function getRecentSystemActivities($limit) { return []; }
    private function getSystemStatistics() { return []; }
    
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
    private function getUpcomingCommunityEvents($scope, $limit)
    {
        try {
            return (new Event())->getUpcomingEventsForUser((int) ($scope['user_id'] ?? 0), (int) $limit);
        } catch (\Throwable $e) {
            error_log('DashboardController::getUpcomingCommunityEvents error: ' . $e->getMessage());
            return [];
        }
    }
    private function getCommunityNews($scope, $limit) { return []; }
    private function getRecentMembers($scope, $limit) { return []; }
    private function getPersonalTasks($userId, $limit)
    {
        try {
            return array_slice((new Task())->getTasksAssignedToUser((int) $userId), 0, (int) $limit);
        } catch (\Throwable $e) {
            error_log('DashboardController::getPersonalTasks error: ' . $e->getMessage());
            return [];
        }
    }
    private function getPersonalMeetings($userId, $limit)
    {
        try {
            return (new Meeting())->getUpcomingMeetingsForUser((int) $userId, (int) $limit);
        } catch (\Throwable $e) {
            error_log('DashboardController::getPersonalMeetings error: ' . $e->getMessage());
            return [];
        }
    }
    private function getMemberEngagementScore($userId) { return 75; }
    private function getMemberAchievements($userId) { return []; }
    private function getMemberRecommendations($userId) { return []; }
    
    // Member Dashboard specific methods
    private function getMemberTasks($userScope) { return $this->getPersonalTasks($userScope['user_id'] ?? 0, 10); }
    private function getMemberMeetings($userScope) { return $this->getPersonalMeetings($userScope['user_id'] ?? 0, 5); }
    private function getCommunityEvents($userScope) { return $this->getUpcomingCommunityEvents($userScope, 5); }
    private function getMemberDonations($userScope) { 
        $userId = is_array($userScope) ? ($userScope['user_id'] ?? 0) : $userScope;
        return $this->getMemberDonationsInternal($userId, 10); 
    }
    private function getMemberDonationsInternal($userId, $limit) { return []; }
    private function getCommunityAnnouncements($userScope, $limit) { return $this->getCommunityNews($userScope, $limit); }
    private function getCommunityStatistics($userScope)
    {
        $gurmuInfo = $this->getMemberGurmuInfo($userScope);

        return [
            'member_count' => (int) ($gurmuInfo['member_count'] ?? 0),
            'meeting_count' => (int) ($gurmuInfo['meeting_count'] ?? 0),
            'course_count' => count($this->getMemberCourses($userScope)),
        ];
    }

    private function getTrainingSessions(array $meetings): array
    {
        return array_values(array_filter($meetings, static function ($meeting) {
            return ($meeting['meeting_type'] ?? '') === 'training';
        }));
    }

    private function getMemberCourses($userScope): array
    {
        $userId = (int) ($userScope['user_id'] ?? 0);
        if ($userId <= 0 || !$this->db->tableExists('courses') || !$this->db->tableExists('course_enrollments')) {
            return [];
        }

        try {
            return $this->db->fetchAll(
                "SELECT c.*, ce.status as enrollment_status, ce.enrolled_at,
                        CONCAT_WS(' ', instructor.first_name, instructor.last_name) as instructor_name
                 FROM course_enrollments ce
                 INNER JOIN courses c ON c.id = ce.course_id
                 LEFT JOIN users instructor ON c.instructor_id = instructor.id
                 WHERE ce.user_id = ?
                   AND ce.status IN ('pending', 'active', 'completed')
                 ORDER BY FIELD(ce.status, 'active', 'pending', 'completed'),
                          COALESCE(c.start_date, ce.enrolled_at) DESC
                 LIMIT 5",
                [$userId]
            );
        } catch (\Throwable $e) {
            error_log('DashboardController::getMemberCourses error: ' . $e->getMessage());
            return [];
        }
    }

    private function getMemberGurmuInfo($userScope): array
    {
        $gurmuId = (int) ($userScope['gurmu_id'] ?? 0);
        if ($gurmuId <= 0 || !$this->db->tableExists('gurmus')) {
            return [];
        }

        try {
            return $this->db->fetch(
                "SELECT g.*, 
                        (SELECT COUNT(*) FROM users u WHERE u.gurmu_id = g.id) as member_count,
                        (SELECT COUNT(*) FROM meetings m WHERE m.gurmu_id = g.id) as meeting_count
                 FROM gurmus g
                 WHERE g.id = ?
                 LIMIT 1",
                [$gurmuId]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardController::getMemberGurmuInfo error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getUserPosition($user) { return null; }
    private function getActiveSessions() { return 0; }
    private function getLastBackupTime() { return date('Y-m-d H:i:s'); }
    private function getDatabaseSize() { return '0 MB'; }
    private function getStorageUsed() { return '0 MB'; }
    
    /**
     * Legacy method for backward compatibility
     */
    public function getDashboardStats(): array
    {
        return $this->getGlobalSystemStats();
    }
}