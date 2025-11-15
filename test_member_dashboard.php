<?php
/**
 * Test member dashboard directly
 */

// Define application constants
define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'member';  
    $_SESSION['user_name'] = 'Dhangaa Stream';
    $_SESSION['is_authenticated'] = true;
    
    $controller = new \App\Controllers\DashboardController();
    
    // Get user scope first
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getUserHierarchicalScope');
    $method->setAccessible(true);
    $scope = $method->invoke($controller, 1);
    
    echo "User scope retrieved\n";
    
    // Test member dashboard directly
    echo "=== Testing memberDashboard method ===\n";
    $memberMethod = $reflection->getMethod('memberDashboard');
    $memberMethod->setAccessible(true);
    
    ob_start();
    $result = $memberMethod->invoke($controller, $scope);
    $output = ob_get_clean();
    
    echo "Member dashboard completed!\n";
    echo "Return value: " . var_export($result, true) . "\n";
    echo "Output length: " . strlen($output) . " bytes\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>