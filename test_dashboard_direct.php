<?php
/**
 * Direct dashboard test - bypass authentication for debugging
 */

// Define application constants
define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

// Load Composer autoloader and helpers
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

// Bootstrap the application
try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Check if APP_LANGUAGE constant is defined
    echo "=== Language Check ===\n";
    if (defined('APP_LANGUAGE')) {
        echo "APP_LANGUAGE constant is defined: " . APP_LANGUAGE . "\n";
    } else {
        echo "APP_LANGUAGE constant is NOT defined\n";
    }
    
    // Create a simulated authenticated session for Dhangaa
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'member';  
    $_SESSION['user_name'] = 'Dhangaa Stream';
    $_SESSION['is_authenticated'] = true;
    
    // Create DashboardController instance
    $controller = new \App\Controllers\DashboardController();
    
    echo "\n=== Testing Dashboard Access ===\n";
    
    // Simulate GET /dashboard request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/dashboard';
    
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    echo "Dashboard loaded successfully!\n";
    echo "Output length: " . strlen($output) . " bytes\n";
    
    // Check for any errors in the output
    if (strpos($output, 'Fatal error') !== false) {
        echo "FATAL ERROR FOUND:\n";
        echo $output . "\n";
    } else if (strpos($output, 'Warning') !== false) {
        echo "WARNING FOUND:\n";
        echo $output . "\n";  
    } else if (strpos($output, 'Notice') !== false) {
        echo "NOTICE FOUND:\n";
        echo $output . "\n";
    } else {
        echo "No PHP errors detected in dashboard output.\n";
        echo "First 200 characters of output:\n";
        echo substr($output, 0, 200) . "...\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
?>