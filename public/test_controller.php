<?php
// Direct controller test - bypass routing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Direct Controller Test</h2>";

try {
    // Set up the environment
    require_once '../vendor/autoload.php';
    require_once '../app/helpers.php';
    
    // Start session for auth testing
    session_start();
    
    // Test if we can instantiate controllers directly
    echo "<p>Testing HierarchyController directly...</p>";
    
    $controller = new \App\Controllers\HierarchyController();
    echo "<p>✅ HierarchyController instantiated successfully</p>";
    
    // Test if we can call the index method
    echo "<p>Testing index method...</p>";
    
    // Mock a simple user session for testing
    $_SESSION['user'] = [
        'id' => 1,
        'email' => 'test@test.com',
        'role' => 'admin',
        'permissions' => ['admin']
    ];
    
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    if (strlen($output) > 100) {
        echo "<p>✅ index() method executed successfully, produced " . strlen($output) . " bytes of output</p>";
        echo "<details><summary>Output Preview (first 500 chars)</summary><pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre></details>";
    } else {
        echo "<p>⚠️ index() method executed but produced limited output: " . strlen($output) . " bytes</p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
}

echo "<hr>";
echo "<p><a href='/hierarchy'>Test /hierarchy route</a></p>";
echo "<p><a href='/test_routing.php'>Back to main test</a></p>";
?>