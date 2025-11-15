<?php
/**
 * Quick Dashboard Fix Test
 */

require_once 'vendor/autoload.php';
require_once 'app/helpers.php';

use App\Controllers\DashboardController;
use App\Utils\Database;

echo "=== TESTING DASHBOARD FIX ===\n\n";

// Setup session like a logged-in user
session_start();
$_SESSION['user_id'] = 33;
$_SESSION['user'] = [
    'id' => 33,
    'first_name' => 'Dhangaa',
    'last_name' => 'Stream',
    'email' => 'dhangaatorbanii@gmail.com',
    'role' => 'executive'
];
$_SESSION['logged_in'] = true;

try {
    echo "1. Testing DashboardController instantiation...\n";
    $dashboard = new DashboardController();
    echo "✅ DashboardController created\n";
    
    echo "2. Testing getUserHierarchicalScope...\n";
    $userScope = $dashboard->getUserHierarchicalScope(33);
    echo "✅ User scope retrieved: " . json_encode($userScope, JSON_PRETTY_PRINT) . "\n";
    
    echo "3. Testing critical methods that might cause 500 errors...\n";
    
    // Test getPositionResponsibilities
    if (isset($userScope['position_id'])) {
        try {
            $reflection = new ReflectionClass($dashboard);
            $method = $reflection->getMethod('getPositionResponsibilities');
            $method->setAccessible(true);
            $responsibilities = $method->invoke($dashboard, $userScope['position_id']);
            echo "✅ getPositionResponsibilities works\n";
        } catch (Exception $e) {
            echo "❌ getPositionResponsibilities error: " . $e->getMessage() . "\n";
        }
    }
    
    // Test executiveDashboard method  
    try {
        $reflection = new ReflectionClass($dashboard);
        $method = $reflection->getMethod('executiveDashboard');
        $method->setAccessible(true);
        
        ob_start();
        $method->invoke($dashboard, $userScope);
        $output = ob_get_clean();
        
        if (strlen($output) > 0) {
            echo "✅ executiveDashboard renders successfully (" . strlen($output) . " chars)\n";
            
            // Check for errors in output
            if (strpos($output, 'Fatal error') !== false || strpos($output, 'Warning:') !== false) {
                echo "❌ Output contains PHP errors\n";
                echo "First 500 characters:\n" . substr($output, 0, 500) . "...\n";
            } else {
                echo "✅ No obvious PHP errors in output\n";
            }
        } else {
            echo "❌ executiveDashboard produced no output\n";
        }
        
    } catch (Exception $e) {
        echo "❌ executiveDashboard error: " . $e->getMessage() . "\n";
    }
    
    echo "\n4. Testing if dashboard/executive.php view exists...\n";
    $viewPath = 'resources/views/dashboard/executive.php';
    if (file_exists($viewPath)) {
        echo "✅ Executive dashboard view exists\n";
    } else {
        echo "❌ Executive dashboard view missing: {$viewPath}\n";
        echo "This could be the 500 error cause!\n";
    }
    
    echo "\n=== DASHBOARD FIX TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ Critical error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>