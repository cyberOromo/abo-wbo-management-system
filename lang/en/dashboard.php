<?php

/**
 * English Dashboard Translations
 * 
 * Dashboard interface, widgets, statistics, and navigation
 * translations for all organizational levels.
 * 
 * @package Lang\EN
 * @version 1.0.0
 */

return [
    // Dashboard Titles
    'title' => 'Dashboard',
    'welcome' => 'Welcome back, :name!',
    'overview' => 'Overview',
    'statistics' => 'Statistics',
    'recent_activity' => 'Recent Activity',
    'quick_actions' => 'Quick Actions',
    'notifications' => 'Notifications',
    'calendar' => 'Calendar',
    'weather' => 'Weather',

    // Level-Specific Dashboards
    'levels' => [
        'global' => [
            'title' => 'Global Dashboard',
            'subtitle' => 'ABO-WBO Global Organization Overview',
            'total_godinas' => 'Total Godinas',
            'total_gamtas' => 'Total Gamtas',
            'total_gurmus' => 'Total Gurmus',
            'total_members' => 'Total Members',
            'active_regions' => 'Active Regions',
            'global_events' => 'Global Events',
            'global_projects' => 'Global Projects',
            'financial_overview' => 'Financial Overview'
        ],
        'godina' => [
            'title' => 'Godina Dashboard',
            'subtitle' => ':name Godina Overview',
            'total_gamtas' => 'Total Gamtas',
            'total_gurmus' => 'Total Gurmus',
            'total_members' => 'Total Members',
            'active_projects' => 'Active Projects',
            'upcoming_meetings' => 'Upcoming Meetings',
            'financial_status' => 'Financial Status',
            'recent_activities' => 'Recent Activities'
        ],
        'gamta' => [
            'title' => 'Gamta Dashboard',
            'subtitle' => ':name Gamta Overview',
            'total_gurmus' => 'Total Gurmus',
            'total_members' => 'Total Members',
            'active_tasks' => 'Active Tasks',
            'completed_tasks' => 'Completed Tasks',
            'upcoming_events' => 'Upcoming Events',
            'member_engagement' => 'Member Engagement',
            'resource_allocation' => 'Resource Allocation'
        ],
        'gurmu' => [
            'title' => 'Gurmu Dashboard',
            'subtitle' => ':name Gurmu Overview',
            'total_members' => 'Total Members',
            'active_members' => 'Active Members',
            'pending_approvals' => 'Pending Approvals',
            'monthly_contributions' => 'Monthly Contributions',
            'meeting_attendance' => 'Meeting Attendance',
            'community_projects' => 'Community Projects',
            'local_events' => 'Local Events'
        ]
    ],

    // Statistics Cards
    'stats' => [
        'total' => 'Total',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
        'completed' => 'Completed',
        'in_progress' => 'In Progress',
        'overdue' => 'Overdue',
        'upcoming' => 'Upcoming',
        'this_month' => 'This Month',
        'this_week' => 'This Week',
        'today' => 'Today',
        'growth' => 'Growth',
        'decline' => 'Decline',
        'no_change' => 'No Change',
        'percentage_change' => ':percent% :direction from last month',
        'comparison' => [
            'vs_last_month' => 'vs last month',
            'vs_last_week' => 'vs last week',
            'vs_last_year' => 'vs last year'
        ]
    ],

    // Widgets
    'widgets' => [
        'user_statistics' => 'User Statistics',
        'task_progress' => 'Task Progress',
        'meeting_schedule' => 'Meeting Schedule',
        'financial_summary' => 'Financial Summary',
        'recent_donations' => 'Recent Donations',
        'event_calendar' => 'Event Calendar',
        'member_growth' => 'Member Growth',
        'activity_feed' => 'Activity Feed',
        'quick_stats' => 'Quick Statistics',
        'performance_metrics' => 'Performance Metrics',
        'system_health' => 'System Health',
        'upcoming_deadlines' => 'Upcoming Deadlines',
        'popular_content' => 'Popular Content',
        'user_engagement' => 'User Engagement',
        'geographic_distribution' => 'Geographic Distribution'
    ],

    // Quick Actions
    'quick_actions' => [
        'create_task' => 'Create Task',
        'schedule_meeting' => 'Schedule Meeting',
        'add_member' => 'Add Member',
        'create_event' => 'Create Event',
        'record_donation' => 'Record Donation',
        'send_notification' => 'Send Notification',
        'generate_report' => 'Generate Report',
        'backup_data' => 'Backup Data',
        'export_data' => 'Export Data',
        'import_data' => 'Import Data',
        'manage_permissions' => 'Manage Permissions',
        'system_settings' => 'System Settings'
    ],

    // Recent Activity
    'activity' => [
        'no_activity' => 'No recent activity',
        'view_all' => 'View All Activity',
        'activity_types' => [
            'user_registered' => ':user registered to the system',
            'user_approved' => ':user was approved by :approver',
            'task_created' => 'Task ":task" was created by :user',
            'task_completed' => 'Task ":task" was completed by :user',
            'meeting_scheduled' => 'Meeting ":meeting" was scheduled by :user',
            'donation_received' => 'Donation of :amount received from :donor',
            'event_created' => 'Event ":event" was created by :user',
            'member_joined' => ':member joined :gurmu',
            'document_uploaded' => 'Document ":document" was uploaded by :user',
            'report_generated' => 'Report ":report" was generated by :user'
        ],
        'time_ago' => ':time ago',
        'refresh_activity' => 'Refresh Activity'
    ],

    // Charts and Analytics
    'charts' => [
        'member_growth_chart' => 'Member Growth Chart',
        'task_completion_chart' => 'Task Completion Chart',
        'donation_trends' => 'Donation Trends',
        'meeting_attendance' => 'Meeting Attendance',
        'event_participation' => 'Event Participation',
        'financial_overview' => 'Financial Overview',
        'user_engagement' => 'User Engagement',
        'geographic_distribution' => 'Geographic Distribution',
        'performance_metrics' => 'Performance Metrics',
        'system_usage' => 'System Usage',
        'no_data' => 'No data available for this period',
        'loading_chart' => 'Loading chart data...',
        'chart_error' => 'Error loading chart data'
    ],

    // Navigation Menu
    'menu' => [
        'dashboard' => 'Dashboard',
        'members' => 'Members',
        'hierarchy' => 'Hierarchy Management',
        'tasks' => 'Task Management',
        'meetings' => 'Meetings',
        'events' => 'Events',
        'donations' => 'Donations',
        'reports' => 'Reports',
        'communications' => 'Communications',
        'settings' => 'Settings',
        'help' => 'Help & Support',
        'profile' => 'My Profile',
        'logout' => 'Logout',
        'administration' => 'Administration',
        'system_management' => 'System Management',
        'user_management' => 'User Management',
        'content_management' => 'Content Management',
        'financial_management' => 'Financial Management'
    ],

    // Status Indicators
    'status' => [
        'online' => 'Online',
        'offline' => 'Offline',
        'away' => 'Away',
        'busy' => 'Busy',
        'available' => 'Available',
        'in_meeting' => 'In Meeting',
        'do_not_disturb' => 'Do Not Disturb',
        'system_status' => 'System Status',
        'all_systems_operational' => 'All systems operational',
        'maintenance_mode' => 'Maintenance mode active',
        'partial_outage' => 'Partial system outage',
        'major_outage' => 'Major system outage'
    ],

    // Filters and Sorting
    'filters' => [
        'filter_by' => 'Filter by',
        'all' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
        'in_progress' => 'In Progress',
        'overdue' => 'Overdue',
        'date_range' => 'Date Range',
        'custom_range' => 'Custom Range',
        'last_7_days' => 'Last 7 days',
        'last_30_days' => 'Last 30 days',
        'last_90_days' => 'Last 90 days',
        'this_year' => 'This year',
        'clear_filters' => 'Clear Filters',
        'apply_filters' => 'Apply Filters'
    ],

    // Time Periods
    'periods' => [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'this_week' => 'This Week',
        'last_week' => 'Last Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_quarter' => 'This Quarter',
        'last_quarter' => 'Last Quarter',
        'this_year' => 'This Year',
        'last_year' => 'Last Year',
        'custom' => 'Custom Period'
    ],

    // System Information
    'system' => [
        'system_info' => 'System Information',
        'version' => 'Version',
        'last_update' => 'Last Update',
        'uptime' => 'System Uptime',
        'database_status' => 'Database Status',
        'storage_usage' => 'Storage Usage',
        'memory_usage' => 'Memory Usage',
        'active_users' => 'Active Users',
        'total_sessions' => 'Total Sessions',
        'server_load' => 'Server Load',
        'backup_status' => 'Backup Status',
        'security_status' => 'Security Status'
    ],

    // Notifications Panel
    'notifications_panel' => [
        'title' => 'Notifications',
        'no_notifications' => 'No new notifications',
        'mark_all_read' => 'Mark all as read',
        'view_all' => 'View all notifications',
        'notification_types' => [
            'info' => 'Information',
            'success' => 'Success',
            'warning' => 'Warning',
            'error' => 'Error',
            'task' => 'Task',
            'meeting' => 'Meeting',
            'event' => 'Event',
            'system' => 'System',
            'security' => 'Security'
        ],
        'actions' => [
            'mark_read' => 'Mark as read',
            'mark_unread' => 'Mark as unread',
            'delete' => 'Delete notification',
            'view_details' => 'View details'
        ]
    ],

    // Calendar Widget
    'calendar_widget' => [
        'title' => 'Calendar',
        'today' => 'Today',
        'no_events' => 'No events scheduled',
        'view_calendar' => 'View Full Calendar',
        'upcoming_events' => 'Upcoming Events',
        'event_types' => [
            'meeting' => 'Meeting',
            'event' => 'Event',
            'deadline' => 'Deadline',
            'reminder' => 'Reminder',
            'holiday' => 'Holiday'
        ]
    ],

    // Performance Metrics
    'performance' => [
        'title' => 'Performance Metrics',
        'response_time' => 'Average Response Time',
        'page_load_time' => 'Page Load Time',
        'database_queries' => 'Database Queries',
        'cache_hit_rate' => 'Cache Hit Rate',
        'error_rate' => 'Error Rate',
        'user_satisfaction' => 'User Satisfaction',
        'system_efficiency' => 'System Efficiency',
        'resource_utilization' => 'Resource Utilization'
    ],

    // Help and Tips
    'help' => [
        'welcome_tour' => 'Take a welcome tour',
        'help_center' => 'Help Center',
        'keyboard_shortcuts' => 'Keyboard Shortcuts',
        'tips_and_tricks' => 'Tips & Tricks',
        'contact_support' => 'Contact Support',
        'feature_requests' => 'Feature Requests',
        'report_bug' => 'Report a Bug',
        'documentation' => 'Documentation',
        'video_tutorials' => 'Video Tutorials',
        'community_forum' => 'Community Forum'
    ]
];