<?php
/**
 * Dashboard controller test with error handling
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

// Bootstrap the application
try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Simulate authenticated session for Dhangaa
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'member';  
    $_SESSION['user_name'] = 'Dhangaa Stream';
    $_SESSION['is_authenticated'] = true;
    
    echo "=== Creating Dashboard Controller ===\n";
    $controller = new \App\Controllers\DashboardController();
    echo "Controller created successfully\n";
    
    echo "=== Testing getUserHierarchicalScope ===\n";
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getUserHierarchicalScope');
    $method->setAccessible(true);
    $scope = $method->invoke($controller, 1);
    echo "User scope retrieved: " . print_r($scope, true) . "\n";
    
    echo "=== Testing view rendering ===\n";
    // Test just the view function
    $testData = ['title' => 'Test Dashboard', 'test' => 'data'];
    
    ob_start();
    view('dashboard.index', $testData);
    $viewOutput = ob_get_clean();
    echo "View rendered successfully!\n";
    echo "View output length: " . strlen($viewOutput) . " bytes\n";
    echo "First 200 chars: " . substr($viewOutput, 0, 200) . "...\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (\Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
?>