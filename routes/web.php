<?php

/**
 * Web Routes
 * ABO-WBO Management System
 */

// $router is passed from Application::loadRoutes()
// No need to get Application instance here to avoid circular dependency

// Home routes
$router->get('/', 'DashboardController@welcome')->name('home');

// Legacy admin hierarchy route (for backward compatibility)
$router->get('/admin-hierarchy', 'HierarchyController@adminHierarchy')
    ->middleware('auth')
    ->name('admin.hierarchy.legacy');

$router->get('/dashboard', 'DashboardController@index')
    ->middleware('auth')
    ->name('dashboard');

// Authentication routes
$router->group(['prefix' => 'auth'], function() use ($router) {
    // Login routes
    $router->get('/login', 'AuthController@showLogin')->name('login');
    $router->post('/login', 'AuthController@login');
    
    // Registration routes
    $router->get('/register', 'AuthController@showRegister')->name('register');
    $router->post('/register', 'AuthController@register');
    
    // Logout
    $router->post('/logout', 'AuthController@logout')
        ->middleware('auth')
        ->name('logout');
    
    // Password reset routes
    $router->get('/forgot-password', 'AuthController@showForgotPassword')->name('password.request');
    $router->post('/forgot-password', 'AuthController@forgotPassword');
    $router->get('/reset-password/{token}', 'AuthController@showResetPassword')->name('password.reset');
    $router->post('/reset-password', 'AuthController@resetPassword');
});

// User management routes
$router->group(['prefix' => 'users', 'middleware' => ['auth', 'module_access']], function() use ($router) {
    $router->get('/', 'UserController@index')->name('users.index');
    $router->get('/create', 'UserController@create')->name('users.create');
    $router->post('/', 'UserController@store')->name('users.store');
    $router->get('/{id}', 'UserController@show')->name('users.show');
    $router->get('/{id}/edit', 'UserController@edit')->name('users.edit');
    $router->put('/{id}', 'UserController@update')->name('users.update');
    $router->delete('/{id}', 'UserController@destroy')->name('users.destroy');
    
    // AJAX endpoints for hierarchical dropdowns
    $router->get('/get-gamtas-by-godina', 'UserController@getGamtasByGodina');
    $router->get('/get-gurmus-by-gamta', 'UserController@getGurmusByGamta');
    
    // Profile routes
    $router->get('/profile/edit', 'UserController@editProfile')->name('profile.edit');
    $router->put('/profile', 'UserController@updateProfile')->name('profile.update');
    $router->put('/profile/password', 'UserController@updatePassword')->name('profile.password');
});

// Comprehensive Hierarchy management routes
$router->group(['prefix' => 'hierarchy', 'middleware' => ['auth', 'module_access']], function() use ($router) {
    // Dashboard and overview
    $router->get('/', 'HierarchyController@index')->name('hierarchy.index');
    $router->get('/dashboard', 'HierarchyController@dashboard')->name('hierarchy.dashboard');
    $router->get('/statistics', 'HierarchyController@getStatistics')->name('hierarchy.statistics');
    $router->get('/system-status', 'HierarchyController@getSystemStatus')->name('hierarchy.system_status');
    
    // Complete hierarchy management
    $router->get('/godinas', 'HierarchyController@godinas')->name('hierarchy.godinas');
    $router->get('/gamtas', 'HierarchyController@gamtas')->name('hierarchy.gamtas');
    $router->get('/gurmus', 'HierarchyController@gurmus')->name('hierarchy.gurmus');
    $router->get('/positions', 'HierarchyController@positions')->name('hierarchy.positions');
    $router->get('/assignments', 'HierarchyController@assignments')->name('hierarchy.assignments');
    
    // Create forms for hierarchy units
    $router->get('/create/godina', 'HierarchyController@createGodina')->name('hierarchy.create.godina');
    $router->post('/create/godina', 'HierarchyController@storeGodina')->name('hierarchy.store.godina');
    $router->get('/create/gamta', 'HierarchyController@createGamta')->name('hierarchy.create.gamta');
    $router->post('/create/gamta', 'HierarchyController@storeGamta')->name('hierarchy.store.gamta');
    $router->get('/create/gurmu', 'HierarchyController@createGurmu')->name('hierarchy.create.gurmu');
    $router->post('/create/gurmu', 'HierarchyController@storeGurmu')->name('hierarchy.store.gurmu');
    
    // Tree view
    $router->get('/tree', 'HierarchyController@tree')->name('hierarchy.tree');
    $router->get('/api/tree-data', 'HierarchyController@getTreeData')->name('hierarchy.api.tree_data');
    
    // API endpoints for hierarchy data
    $router->get('/api/hierarchy', 'HierarchyController@getHierarchy')->name('hierarchy.api.hierarchy');
    $router->get('/api/organizational-path', 'HierarchyController@getOrganizationalPath')->name('hierarchy.api.path');
    $router->get('/api/user-access-scope', 'HierarchyController@getUserAccessScope')->name('hierarchy.api.access_scope');
    $router->get('/api/validate-integrity', 'HierarchyController@validateIntegrity')->name('hierarchy.api.validate');
    
    // Responsibility management API
    $router->get('/api/individual-responsibilities', 'HierarchyController@getIndividualResponsibilities')->name('hierarchy.api.individual_responsibilities');
    $router->get('/api/shared-responsibilities', 'HierarchyController@getSharedResponsibilities')->name('hierarchy.api.shared_responsibilities');
    $router->get('/api/responsibility-matrix', 'HierarchyController@getResponsibilityMatrix')->name('hierarchy.api.responsibility_matrix');
    $router->post('/api/create-missing-responsibilities', 'HierarchyController@createMissingResponsibilities')->name('hierarchy.api.create_missing');
    
    // User assignment API
    $router->post('/api/assign-user-position', 'HierarchyController@assignUserToPosition')->name('hierarchy.api.assign_user');
    
    // Export functionality
    $router->get('/export', 'HierarchyController@export')->name('hierarchy.export');
    
    // Hierarchy tree view - MUST come before /{id} route to avoid conflicts
    $router->get('/tree/view', 'HierarchyController@treeView')->name('hierarchy.tree.view');
    $router->get('/tree/data', 'HierarchyController@treeData')->name('hierarchy.tree.data');
    
    // List endpoints for dropdowns - MUST come before /{id} route
    $router->get('/godinas/list', 'HierarchyController@listGodinas')->name('hierarchy.godinas.list');
    $router->get('/gamtas/list', 'HierarchyController@listGamtas')->name('hierarchy.gamtas.list');
    
    // Legacy routes for backward compatibility - /{id} MUST be last
    $router->get('/create', 'HierarchyController@create')->name('hierarchy.create');
    $router->post('/', 'HierarchyController@store')->name('hierarchy.store');
    $router->get('/{id}/edit', 'HierarchyController@edit')->name('hierarchy.edit');
    $router->put('/{id}', 'HierarchyController@update')->name('hierarchy.update');
    $router->delete('/{id}', 'HierarchyController@destroy')->name('hierarchy.destroy');
    
    // Show route - MUST be LAST because it's a catch-all
    $router->get('/{id}', 'HierarchyController@show')->name('hierarchy.show');
});

// Position management routes
$router->group(['prefix' => 'positions', 'middleware' => ['auth', 'module_access']], function() use ($router) {
    $router->get('/', 'PositionController@index')->name('positions.index');
    $router->get('/create', 'PositionController@create')->name('positions.create');
    $router->post('/', 'PositionController@store')->name('positions.store');
    $router->get('/{id}', 'PositionController@show')->name('positions.show');
    $router->get('/{id}/edit', 'PositionController@edit')->name('positions.edit');
    $router->put('/{id}', 'PositionController@update')->name('positions.update');
    $router->delete('/{id}', 'PositionController@destroy')->name('positions.destroy');
    
    // Position assignments
    $router->get('/{id}/assign', 'PositionController@assign')->name('positions.assign');
    $router->post('/assign', 'PositionController@processAssignment')->name('positions.process_assignment');
    $router->get('/assignments', 'PositionController@assignments')->name('positions.assignments');
    $router->post('/assignments/{id}/approve', 'PositionController@approveAssignment')->name('positions.approve');
    $router->post('/assignments/{id}/reject', 'PositionController@rejectAssignment')->name('positions.reject');
    $router->post('/assignments/{id}/end', 'PositionController@endAssignment')->name('positions.end');
    
    // API endpoints
    $router->get('/api/by-level', 'PositionController@getPositionsByLevel')->name('positions.api.by_level');
});

// Responsibility management routes - Shared Responsibilities & Tasks (5 Core Areas)
$router->group(['prefix' => 'responsibilities', 'middleware' => ['auth', 'module_access']], function() use ($router) {
    // Main responsibility management
    $router->get('/', 'ResponsibilityController@index')->name('responsibilities.index');
    
    // Assignment management
    $router->get('/assign', 'ResponsibilityController@assign')->name('responsibilities.assign');
    $router->post('/assign', 'ResponsibilityController@assign')->name('responsibilities.process_assign');
    
    // Assignment tracking
    $router->get('/assignments', 'ResponsibilityController@assignments')->name('responsibilities.assignments');
    $router->get('/assignments/{id}', 'ResponsibilityController@assignments')->name('responsibilities.assignment.view');
    
    // Progress updates
    $router->put('/assignments/{id}/progress', 'ResponsibilityController@updateProgress')->name('responsibilities.progress');
    $router->put('/assignments/{id}/complete', 'ResponsibilityController@complete')->name('responsibilities.complete');
    
    // System initialization
    $router->get('/initialize', 'ResponsibilityController@initialize')->name('responsibilities.initialize');
    
    // Dashboard API
    $router->get('/dashboard', 'ResponsibilityController@dashboard')->name('responsibilities.dashboard');
    
    // Reports
    $router->get('/report', 'ResponsibilityController@report')->name('responsibilities.report');

    // Detail view
    $router->get('/{id}', 'ResponsibilityController@view')->name('responsibilities.view');
});

// Task management routes
$router->group(['prefix' => 'tasks', 'middleware' => 'auth'], function() use ($router) {
    $router->get('/', 'TaskController@index')->name('tasks.index');
    $router->get('/create', 'TaskController@create')->name('tasks.create');
    $router->post('/', 'TaskController@store')->name('tasks.store');
    $router->get('/{id}', 'TaskController@show')->name('tasks.show');
    $router->get('/{id}/edit', 'TaskController@edit')->name('tasks.edit');
    $router->put('/{id}', 'TaskController@update')->name('tasks.update');
    $router->delete('/{id}', 'TaskController@destroy')->name('tasks.destroy');
    
    // Task status updates
    $router->put('/{id}/status', 'TaskController@updateStatus')->name('tasks.status');
    $router->post('/{id}/assign', 'TaskController@assign')->name('tasks.assign');
    $router->post('/{id}/comments', 'TaskController@addComment')->name('tasks.comments');
});

// Meeting management routes
$router->group(['prefix' => 'meetings', 'middleware' => 'auth'], function() use ($router) {
    $router->get('/', 'MeetingController@index')->name('meetings.index');
    $router->get('/create', 'MeetingController@create')->name('meetings.create');
    $router->post('/', 'MeetingController@store')->name('meetings.store');
    $router->get('/{id}', 'MeetingController@show')->name('meetings.show');
    $router->get('/{id}/edit', 'MeetingController@edit')->name('meetings.edit');
    $router->put('/{id}', 'MeetingController@update')->name('meetings.update');
    $router->delete('/{id}', 'MeetingController@destroy')->name('meetings.destroy');
    
    // Meeting participants
    $router->post('/{id}/participants', 'MeetingController@addParticipant')->name('meetings.participants.add');
    $router->delete('/{id}/participants/{userId}', 'MeetingController@removeParticipant')->name('meetings.participants.remove');
    
    // Meeting minutes
    $router->get('/{id}/minutes', 'MeetingController@minutes')->name('meetings.minutes');
    $router->put('/{id}/minutes', 'MeetingController@updateMinutes')->name('meetings.minutes.update');
});

// Event management routes
$router->group(['prefix' => 'events', 'middleware' => 'auth'], function() use ($router) {
    $router->get('/', 'EventController@index')->name('events.index');
    $router->get('/create', 'EventController@create')->name('events.create');
    $router->post('/', 'EventController@store')->name('events.store');
    $router->get('/{id}', 'EventController@show')->name('events.show');
    $router->get('/{id}/edit', 'EventController@edit')->name('events.edit');
    $router->put('/{id}', 'EventController@update')->name('events.update');
    $router->delete('/{id}', 'EventController@destroy')->name('events.destroy');
    
    // Event registration
    $router->post('/{id}/register', 'EventController@register')->name('events.register');
    $router->delete('/{id}/unregister', 'EventController@unregister')->name('events.unregister');
    
    // Event calendar
    $router->get('/calendar/view', 'EventController@calendar')->name('events.calendar');
    $router->get('/calendar/data', 'EventController@calendarData')->name('events.calendar.data');
});

// Internal Email Management routes
$router->group(['prefix' => 'user-emails', 'middleware' => ['auth', 'module_access']], function() use ($router) {
    // Main email management
    $router->get('/', 'UserEmailController@index')->name('user_emails.index');
    $router->get('/create', 'UserEmailController@create')->name('user_emails.create');
    $router->post('/', 'UserEmailController@store')->name('user_emails.store');
    $router->get('/{id}', 'UserEmailController@view')->name('user_emails.view');
    $router->get('/{id}/edit', 'UserEmailController@edit')->name('user_emails.edit');
    $router->put('/{id}', 'UserEmailController@update')->name('user_emails.update');
    $router->delete('/{id}', 'UserEmailController@destroy')->name('user_emails.destroy');
    
    // Email operations
    $router->post('/{id}/reset-password', 'UserEmailController@resetPassword')->name('user_emails.reset_password');
    $router->post('/{id}/update-quota', 'UserEmailController@updateQuota')->name('user_emails.update_quota');
    $router->post('/{id}/setup-forwarding', 'UserEmailController@setupForwarding')->name('user_emails.setup_forwarding');
    $router->delete('/{id}/remove-forwarding', 'UserEmailController@removeForwarding')->name('user_emails.remove_forwarding');
    $router->post('/{id}/deactivate', 'UserEmailController@deactivate')->name('user_emails.deactivate');
    $router->post('/{id}/reactivate', 'UserEmailController@reactivate')->name('user_emails.reactivate');
    
    // Bulk operations
    $router->post('/bulk/activate', 'UserEmailController@bulkActivate')->name('user_emails.bulk_activate');
    $router->post('/bulk/deactivate', 'UserEmailController@bulkDeactivate')->name('user_emails.bulk_deactivate');
    $router->post('/bulk/delete', 'UserEmailController@bulkDelete')->name('user_emails.bulk_delete');
    
    // Statistics and reports
    $router->get('/statistics', 'UserEmailController@statistics')->name('user_emails.statistics');
    $router->get('/export', 'UserEmailController@export')->name('user_emails.export');
});

// Donation management routes
$router->group(['prefix' => 'donations', 'middleware' => 'auth'], function() use ($router) {
    $router->get('/', 'DonationController@index')->name('donations.index');
    $router->get('/create', 'DonationController@create')->name('donations.create');
    $router->post('/', 'DonationController@store')->name('donations.store');
    $router->get('/{id}', 'DonationController@show')->name('donations.show');
    $router->get('/{id}/edit', 'DonationController@edit')->name('donations.edit');
    $router->put('/{id}', 'DonationController@update')->name('donations.update');
    $router->delete('/{id}', 'DonationController@destroy')->name('donations.destroy');
    
    // Donation reports
    $router->get('/reports/summary', 'DonationController@reportSummary')->name('donations.reports.summary');
    $router->get('/reports/detailed', 'DonationController@reportDetailed')->name('donations.reports.detailed');
    $router->get('/reports/export', 'DonationController@exportReport')->name('donations.reports.export');
});

// Course management routes
$router->group(['prefix' => 'courses', 'middleware' => 'auth'], function() use ($router) {
    $router->get('/', 'CourseController@index')->name('courses.index');
    $router->get('/create', 'CourseController@create')->name('courses.create');
    $router->post('/', 'CourseController@store')->name('courses.store');
    $router->get('/{id}', 'CourseController@show')->name('courses.show');
    $router->get('/{id}/edit', 'CourseController@edit')->name('courses.edit');
    $router->put('/{id}', 'CourseController@update')->name('courses.update');
    $router->delete('/{id}', 'CourseController@destroy')->name('courses.destroy');
    
    // Course enrollment
    $router->post('/{id}/enroll', 'CourseController@enroll')->name('courses.enroll');
    $router->delete('/{id}/unenroll', 'CourseController@unenroll')->name('courses.unenroll');
    
    // Course progress
    $router->get('/{id}/progress', 'CourseController@progress')->name('courses.progress');
    $router->put('/{id}/complete', 'CourseController@markComplete')->name('courses.complete');
});

// Notification routes
$router->group(['prefix' => 'notifications', 'middleware' => ['auth', 'module_access']], function() use ($router) {
    $router->get('/', 'NotificationController@index')->name('notifications.index');
    $router->get('/unread', 'NotificationController@unread')->name('notifications.unread');
    $router->put('/{id}/read', 'NotificationController@markRead')->name('notifications.read');
    $router->put('/mark-all-read', 'NotificationController@markAllRead')->name('notifications.read.all');
    $router->delete('/{id}', 'NotificationController@destroy')->name('notifications.destroy');
});

// Report routes
$router->group(['prefix' => 'reports', 'middleware' => ['auth', 'module_access']], function() use ($router) {
    $router->get('/', 'ReportController@index')->name('reports.index');
    $router->get('/users', 'ReportController@users')->name('reports.users');
    $router->get('/hierarchy', 'ReportController@hierarchy')->name('reports.hierarchy');
    $router->get('/tasks', 'ReportController@tasks')->name('reports.tasks');
    $router->get('/meetings', 'ReportController@meetings')->name('reports.meetings');
    $router->get('/events', 'ReportController@events')->name('reports.events');
    $router->get('/donations', 'ReportController@donations')->name('reports.donations');
    $router->get('/courses', 'ReportController@courses')->name('reports.courses');
    
    // Export routes
    $router->get('/export/{type}', 'ReportController@export')->name('reports.export');
});

// Member Registration routes (Hierarchy-based access control)
$router->group(['prefix' => 'member-registration', 'middleware' => 'auth'], function() use ($router) {
    // Main member registration page
    $router->get('/', 'MemberRegistrationController@index')->name('member.registration.index');
    
    // Register new member
    $router->post('/register', 'MemberRegistrationController@register')->name('member.registration.register');
    
    // Get statistics for dashboard
    $router->get('/stats', 'MemberRegistrationController@getStats')->name('member.registration.stats');
    
    // Get allowed Gurmus for current user
    $router->get('/allowed-gurmus', 'MemberRegistrationController@getAllowedGurmus')->name('member.registration.allowed_gurmus');
    
    // Member management
    $router->get('/members', 'MemberRegistrationController@listMembers')->name('member.registration.list');
    $router->get('/members/{id}', 'MemberRegistrationController@viewMember')->name('member.registration.view');
    $router->put('/members/{id}', 'MemberRegistrationController@updateMember')->name('member.registration.update');
    $router->delete('/members/{id}', 'MemberRegistrationController@deleteMember')->name('member.registration.delete');
    
    // Bulk actions
    $router->post('/bulk-import', 'MemberRegistrationController@bulkImport')->name('member.registration.bulk_import');
    $router->get('/export', 'MemberRegistrationController@exportMembers')->name('member.registration.export');
});

// User/Leader Registration routes (System Admin Only)
$router->group(['prefix' => 'admin/user-leader-registration', 'middleware' => ['auth', 'system_admin']], function() use ($router) {
    // Main user/leader registration page
    $router->get('/', 'UserLeaderRegistrationController@index')->name('admin.user_leader.registration.index');
    
    // Register new user/leader
    $router->post('/register', 'UserLeaderRegistrationController@registerUser')->name('admin.user_leader.registration.register');
    
    // Get statistics
    $router->get('/stats', 'UserLeaderRegistrationController@getStats')->name('admin.user_leader.registration.stats');
    
    // Get organizational units based on selection
    $router->get('/gamtas-for-godina', 'UserLeaderRegistrationController@getGamtasForGodina')->name('admin.user_leader.registration.gamtas');
    $router->get('/gurmus-for-gamta', 'UserLeaderRegistrationController@getGurmusForGamta')->name('admin.user_leader.registration.gurmus');
    $router->get('/positions-for-level', 'UserLeaderRegistrationController@getPositionsForLevel')->name('admin.user_leader.registration.positions');
    
    // User management
    $router->get('/users', 'UserLeaderRegistrationController@listUsers')->name('admin.user_leader.registration.users');
    $router->post('/update-assignments', 'UserLeaderRegistrationController@updateUserAssignments')->name('admin.user_leader.registration.update_assignments');
});

// System Admin routes (Only for System Admins)
$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'system_admin']], function() use ($router) {
    // Main admin dashboard
    $router->get('/', 'SystemAdminController@index')->name('admin.dashboard');
    
    // Global Organization Settings
    $router->get('/global-settings', 'SystemAdminController@globalSettings')->name('admin.global.settings');
    $router->post('/global-settings', 'SystemAdminController@globalSettings');
    
    // Comprehensive Hierarchy Management
    $router->get('/hierarchy', 'SystemAdminController@hierarchyManagement')->name('admin.hierarchy');
    
    // Godina Management
    $router->get('/godina-management', 'SystemAdminController@godinaManagement')->name('admin.godina');
    $router->post('/godina-management', 'SystemAdminController@godinaManagement');
    
    // Gamta Management  
    $router->get('/gamta-management', 'SystemAdminController@gamtaManagement')->name('admin.gamta');
    $router->post('/gamta-management', 'SystemAdminController@gamtaManagement');
    
    // Gurmu Management
    $router->get('/gurmu-management', 'SystemAdminController@gurmuManagement')->name('admin.gurmu');
    $router->post('/gurmu-management', 'SystemAdminController@gurmuManagement');
    
    // User & Leader Registration (System Admin Only)
    $router->get('/user-registration', 'SystemAdminController@userRegistration')->name('admin.user.registration');
    $router->post('/user-registration', 'SystemAdminController@userRegistration');
    
    // Position Assignment (System Admin Only)
    $router->get('/position-assignments', 'SystemAdminController@positionAssignments')->name('admin.positions');
    $router->post('/position-assignments', 'SystemAdminController@positionAssignments');
    
    // Security Settings
    $router->get('/security', 'SystemAdminController@securitySettings')->name('admin.security');
    $router->post('/security', 'SystemAdminController@securitySettings');
    
    // System Maintenance
    $router->get('/maintenance', 'SystemAdminController@maintenance')->name('admin.maintenance');
    $router->post('/maintenance', 'SystemAdminController@maintenance');
});

// Settings routes
$router->group(['prefix' => 'settings', 'middleware' => ['auth', 'admin']], function() use ($router) {
    $router->get('/', 'SettingsController@index')->name('settings.index');
    $router->get('/general', 'SettingsController@general')->name('settings.general');
    $router->put('/general', 'SettingsController@updateGeneral')->name('settings.general.update');
    $router->get('/email', 'SettingsController@email')->name('settings.email');
    $router->put('/email', 'SettingsController@updateEmail')->name('settings.email.update');
    $router->get('/notifications', 'SettingsController@notifications')->name('settings.notifications');
    $router->put('/notifications', 'SettingsController@updateNotifications')->name('settings.notifications.update');
    $router->get('/backup', 'SettingsController@backup')->name('settings.backup');
    $router->post('/backup', 'SettingsController@createBackup')->name('settings.backup.create');
    $router->get('/maintenance', 'SettingsController@maintenance')->name('settings.maintenance');
    $router->put('/maintenance', 'SettingsController@toggleMaintenance')->name('settings.maintenance.toggle');
});

// API status endpoint
$router->get('/status', function() {
    return json_encode([
        'status' => 'OK',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ]);
});

// Health check endpoint
$router->get('/health', function() {
    // Basic health checks
    $checks = [
        'database' => false,
        'storage' => false,
        'cache' => true // Assuming no cache system yet
    ];
    
    try {
        $db = \App\Utils\Database::getInstance();
        $db->query('SELECT 1');
        $checks['database'] = true;
    } catch (Exception $e) {
        // Database check failed
    }
    
    $checks['storage'] = is_writable(STORAGE_PATH);
    
    $status = array_reduce($checks, function($carry, $check) {
        return $carry && $check;
    }, true) ? 'healthy' : 'unhealthy';
    
    http_response_code($status === 'healthy' ? 200 : 503);
    
    return json_encode([
        'status' => $status,
        'checks' => $checks,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});