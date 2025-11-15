<?php
/**
 * Test specific dashboard method to isolate hanging issue
 */

// Define application constants
define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoloader and helpers
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Simulate authenticated session for Dhangaa
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'member';  
    $_SESSION['user_name'] = 'Dhangaa Stream';
    $_SESSION['is_authenticated'] = true;
    
    $controller = new \App\Controllers\DashboardController();
    
    // Test just the getUserHierarchicalScope method first
    echo "=== Testing getUserHierarchicalScope ===\n";
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getUserHierarchicalScope');
    $method->setAccessible(true);
    $scope = $method->invoke($controller, 1);
    echo "User scope: " . print_r($scope, true) . "\n";
    
    // Test getting member tasks
    echo "=== Testing getMemberTasks ===\n";
    $tasksMethod = $reflection->getMethod('getMemberTasks');
    $tasksMethod->setAccessible(true);
    $tasks = $tasksMethod->invoke($controller, $scope);
    echo "Member tasks count: " . (is_array($tasks) ? count($tasks) : 'null') . "\n";
    
    // Test getting member meetings
    echo "=== Testing getMemberMeetings ===\n"; 
    $meetingsMethod = $reflection->getMethod('getMemberMeetings');
    $meetingsMethod->setAccessible(true);
    $meetings = $meetingsMethod->invoke($controller, $scope);
    echo "Member meetings count: " . (is_array($meetings) ? count($meetings) : 'null') . "\n";
    
    echo "All tests completed successfully!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>