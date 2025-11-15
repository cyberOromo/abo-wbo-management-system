<?php
/**
 * Test role-based navigation for Dhangaa
 */

// Define application constants
define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

// Load Composer autoloader and helpers
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Simulate Dhangaa's authenticated session correctly
    $_SESSION['user_id'] = 33;
    $_SESSION['is_authenticated'] = true;
    
    // Set user data in the format expected by auth_user()
    $_SESSION['user'] = [
        'id' => 33,
        'first_name' => 'Dhangaa',
        'last_name' => 'Stream', 
        'email' => 'dhangaatorbanii@gmail.com',
        'role' => 'executive'
    ];
    
    // Test helper functions
    echo "=== Testing Role Helper Functions ===\n";
    echo "User is admin: " . (user_is_admin() ? 'true' : 'false') . "\n";
    echo "User is executive: " . (user_is_executive() ? 'true' : 'false') . "\n";
    echo "User is member: " . (user_is_member() ? 'true' : 'false') . "\n";
    echo "User can access admin: " . (user_can_access_admin() ? 'true' : 'false') . "\n";
    echo "User can manage users: " . (user_can_manage_users() ? 'true' : 'false') . "\n";
    echo "User can view reports: " . (user_can_view_reports() ? 'true' : 'false') . "\n";
    echo "User can register members: " . (user_can_register_members() ? 'true' : 'false') . "\n";
    echo "Role display: " . get_user_role_display() . "\n";
    
    echo "\n=== User Capabilities ===\n";
    $capabilities = get_user_capabilities();
    foreach ($capabilities as $capability) {
        echo "- " . $capability . "\n";
    }
    
    echo "\n=== Testing Dashboard Access ===\n";
    $controller = new \App\Controllers\DashboardController();
    
    ob_start();
    $result = $controller->index();
    $output = ob_get_clean();
    
    echo "Dashboard loaded successfully!\n";
    echo "Output contains 'Executive' role content: " . (strpos($output, 'Executive') !== false ? 'true' : 'false') . "\n";
    echo "Output contains admin-only content: " . (strpos($output, 'User Management') !== false ? 'true' : 'false') . "\n";
    echo "Output contains leadership content: " . (strpos($output, 'Leadership') !== false ? 'true' : 'false') . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>