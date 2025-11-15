<?php
/**
 * Complete dashboard request simulation
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
    
    echo "=== APPLICATION BOOTSTRAPPED ===\n";
    echo "APP_LANGUAGE: " . (defined('APP_LANGUAGE') ? APP_LANGUAGE : 'NOT DEFINED') . "\n";
    
    // Simulate authenticated session for Dhangaa
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'member';  
    $_SESSION['user_name'] = 'Dhangaa Stream';
    $_SESSION['is_authenticated'] = true;
    
    // Set up request environment
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/dashboard';
    $_SERVER['HTTP_HOST'] = 'abo-wbo.local';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    
    echo "=== CREATING DASHBOARD CONTROLLER ===\n";
    $controller = new \App\Controllers\DashboardController();
    echo "Controller created successfully\n";
    
    echo "=== TESTING DASHBOARD INDEX METHOD ===\n";
    
    // Capture output
    ob_start();
    $result = $controller->index();
    $output = ob_get_clean();
    
    if ($result === null) {
        echo "Dashboard method executed successfully!\n";
        echo "Output length: " . strlen($output) . " bytes\n";
        
        if (strpos($output, 'Fatal error') !== false) {
            echo "FATAL ERROR DETECTED:\n";
            echo $output . "\n";
        } elseif (strpos($output, 'Warning') !== false) {
            echo "WARNING DETECTED:\n";
            echo $output . "\n";
        } else {
            echo "Dashboard rendered successfully!\n";
            echo "First 500 characters:\n";
            echo substr($output, 0, 500) . "...\n";
        }
    } else {
        echo "Dashboard returned result: " . var_export($result, true) . "\n";
    }
    
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