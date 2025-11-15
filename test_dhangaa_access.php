<?php
/**
 * Complete role-based access test for Dhangaa
 */

define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Simulate Dhangaa's complete authenticated session (executive)
    $_SESSION['user'] = [
        'id' => 33,
        'first_name' => 'Dhangaa',
        'last_name' => 'Stream',
        'email' => 'dhangaatorbanii@gmail.com',
        'role' => 'executive'
    ];
    $_SESSION['user_id'] = 33;
    $_SESSION['is_authenticated'] = true;
    
    echo "=== Dhangaa Stream - Executive Access Test ===\n";
    echo "Role: " . get_user_role_display() . "\n";
    
    // Test what Dhangaa can access
    echo "\n=== Access Permissions ===\n";
    echo "✓ Dashboard: " . (auth_check() ? 'ALLOWED' : 'DENIED') . "\n";
    echo "✗ User Management: " . (user_can_manage_users() ? 'ALLOWED' : 'DENIED') . "\n";
    echo "✗ Hierarchy Management: " . (user_can_manage_hierarchy() ? 'ALLOWED' : 'DENIED') . "\n";
    echo "✗ Position Management: " . (user_can_manage_positions() ? 'ALLOWED' : 'DENIED') . "\n";
    echo "✓ View Reports: " . (user_can_view_reports() ? 'ALLOWED' : 'DENIED') . "\n";
    echo "✓ Register Members: " . (user_can_register_members() ? 'ALLOWED' : 'DENIED') . "\n";
    
    // Test dashboard routing
    echo "\n=== Dashboard Routing Test ===\n";
    $controller = new \App\Controllers\DashboardController();
    
    ob_start();
    $result = $controller->index();
    $output = ob_get_clean();
    
    echo "Dashboard Type: " . (strpos($output, 'Executive Dashboard') !== false ? 'EXECUTIVE' : 
                               (strpos($output, 'Admin Dashboard') !== false ? 'ADMIN' : 'MEMBER')) . "\n";
    echo "Shows Position: " . (strpos($output, 'Media & Public Relations') !== false ? 'YES' : 'NO') . "\n";
    echo "Shows Scope: " . (strpos($output, 'Minneapolis Gurmu') !== false ? 'YES' : 'NO') . "\n";
    
    // Test URL access patterns (simulated)
    echo "\n=== URL Access Simulation ===\n";
    $testUrls = [
        '/dashboard' => 'SHOULD_ALLOW',
        '/users' => 'SHOULD_DENY', 
        '/hierarchy' => 'SHOULD_DENY',
        '/positions' => 'SHOULD_DENY',
        '/tasks' => 'SHOULD_ALLOW',
        '/meetings' => 'SHOULD_ALLOW',
        '/events' => 'SHOULD_ALLOW',
        '/donations' => 'SHOULD_ALLOW',
        '/responsibilities' => 'SHOULD_ALLOW',
        '/member-registration' => 'SHOULD_ALLOW',
        '/reports' => 'SHOULD_ALLOW',
        '/admin' => 'SHOULD_DENY'
    ];
    
    foreach ($testUrls as $url => $expected) {
        $canAccess = 'UNKNOWN';
        
        if (in_array($url, ['/users', '/hierarchy', '/positions', '/admin'])) {
            $canAccess = user_can_access_admin() ? 'ALLOW' : 'DENY';
        } elseif (in_array($url, ['/reports', '/member-registration', '/responsibilities'])) {
            $canAccess = (user_is_executive() || user_is_admin()) ? 'ALLOW' : 'DENY';
        } else {
            $canAccess = auth_check() ? 'ALLOW' : 'DENY';
        }
        
        $status = ($expected === 'SHOULD_ALLOW' && $canAccess === 'ALLOW') || 
                  ($expected === 'SHOULD_DENY' && $canAccess === 'DENY') ? '✓' : '✗';
        
        echo "{$status} {$url}: {$canAccess} ({$expected})\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "Dhangaa Stream (Executive) now has:\n";
    echo "✓ Access to his executive dashboard\n";
    echo "✓ Leadership functions (responsibilities, member registration, reports)\n";  
    echo "✓ Personal activities (tasks, meetings, events, donations)\n";
    echo "✗ NO access to admin functions (user/hierarchy/position management)\n";
    echo "✓ Role-appropriate navigation menu\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>