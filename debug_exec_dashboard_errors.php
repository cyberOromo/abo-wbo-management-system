<?php
/**
 * Debug executive dashboard with error catching
 */

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
    
    echo "=== Testing Executive Dashboard with Error Handling ===\n";
    
    $controller = new \App\Controllers\DashboardController();
    
    // Get user scope
    $reflection = new ReflectionClass($controller);
    $scopeMethod = $reflection->getMethod('getUserHierarchicalScope');
    $scopeMethod->setAccessible(true);
    $userScope = $scopeMethod->invoke($controller, 33);
    
    echo "User scope found: " . (!empty($userScope) ? 'YES' : 'NO') . "\n";
    
    // Test executive dashboard method with full error catching
    $execMethod = $reflection->getMethod('executiveDashboard');
    $execMethod->setAccessible(true);
    
    echo "Calling executiveDashboard()...\n";
    
    try {
        ob_start();
        $result = $execMethod->invoke($controller, $userScope);
        $output = ob_get_clean();
        
        echo "Method executed successfully!\n";
        echo "Return value: " . var_export($result, true) . "\n";
        echo "Output length: " . strlen($output) . " bytes\n";
        
        if (strlen($output) > 0) {
            echo "Output starts with: " . substr($output, 0, 200) . "...\n";
        } else {
            echo "No output captured!\n";
        }
        
    } catch (\Exception $e) {
        ob_end_clean();
        echo "EXCEPTION in executiveDashboard(): " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
    
    // Test the render method directly
    echo "\n=== Testing render() method directly ===\n";
    try {
        ob_start();
        $directResult = $controller->render('dashboard/executive', [
            'user' => auth_user(),
            'user_scope' => $userScope,
            'page_title' => 'Test Executive Dashboard'
        ]);
        $directOutput = ob_get_clean();
        
        echo "Direct render result: " . (is_string($directResult) ? 'STRING' : gettype($directResult)) . "\n";
        echo "Direct output length: " . strlen($directOutput) . " bytes\n";
        
        if (strlen($directOutput) > 0) {
            echo "Direct output starts with: " . substr($directOutput, 0, 200) . "...\n";
        }
        
    } catch (\Exception $e) {
        ob_end_clean();
        echo "EXCEPTION in render(): " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "OUTER ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n"; 
    echo "Line: " . $e->getLine() . "\n";
}
?>