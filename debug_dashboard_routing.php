<?php
/**
 * Debug dashboard type detection for Dhangaa
 */

define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Simulate Dhangaa's session
    $_SESSION['user'] = [
        'id' => 33,
        'first_name' => 'Dhangaa', 
        'last_name' => 'Stream',
        'email' => 'dhangaatorbanii@gmail.com',
        'role' => 'executive'
    ];
    $_SESSION['user_id'] = 33;
    $_SESSION['is_authenticated'] = true;
    
    echo "=== Dashboard Routing Debug ===\n";
    $user = auth_user();
    echo "User role from auth_user(): " . ($user['role'] ?? 'UNKNOWN') . "\n";
    
    // Test the dashboard controller directly
    $controller = new \App\Controllers\DashboardController();
    
    echo "\n=== Testing Dashboard Method Calls ===\n";
    
    // Test the getUserHierarchicalScope
    $reflection = new ReflectionClass($controller);
    $scopeMethod = $reflection->getMethod('getUserHierarchicalScope');
    $scopeMethod->setAccessible(true);
    $userScope = $scopeMethod->invoke($controller, 33);
    echo "User scope: " . print_r($userScope, true) . "\n";
    
    // Test which dashboard method should be called
    echo "Should call: ";
    switch ($user['role']) {
        case 'admin':
            echo "adminDashboard()\n";
            break;
        case 'executive':
            echo "executiveDashboard()\n";
            break;
        case 'member':
            echo "memberDashboard()\n";
            break;
        default:
            echo "memberDashboard() (default)\n";
    }
    
    // Test the executive dashboard method directly
    echo "\n=== Testing ExecutiveDashboard Method ===\n";
    $execMethod = $reflection->getMethod('executiveDashboard');
    $execMethod->setAccessible(true);
    
    ob_start();
    $result = $execMethod->invoke($controller, $userScope);
    $output = ob_get_clean();
    
    echo "Executive dashboard output length: " . strlen($output) . " bytes\n";
    echo "Contains 'Executive': " . (strpos($output, 'Executive') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains 'Media & Public Relations': " . (strpos($output, 'Media & Public Relations') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains 'Minneapolis': " . (strpos($output, 'Minneapolis') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains 'position_name': " . (strpos($output, 'position_name') !== false ? 'YES' : 'NO') . "\n";
    
    // Show first 500 characters
    echo "\nFirst 500 characters of executive dashboard:\n";
    echo substr($output, 0, 500) . "...\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>