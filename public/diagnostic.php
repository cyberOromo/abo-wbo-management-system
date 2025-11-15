<?php
// Simple diagnostic test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>ABO-WBO Diagnostic Test</h1>";

try {
    // Test 1: Basic includes
    echo "<p>1. Testing basic includes...</p>";
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../app/helpers.php';
    echo "<p>✅ Includes successful</p>";
    
    // Test 2: Check if APP_ROOT is defined
    echo "<p>2. Testing constants...</p>";
    if (defined('APP_ROOT')) {
        echo "<p>✅ APP_ROOT defined: " . APP_ROOT . "</p>";
    } else {
        define('APP_ROOT', dirname(__DIR__));
        echo "<p>⚠️ APP_ROOT not defined, set to: " . APP_ROOT . "</p>";
    }
    
    // Test 3: Database connection
    echo "<p>3. Testing database connection...</p>";
    try {
        $db = \App\Utils\Database::getInstance();
        echo "<p>✅ Database connection successful</p>";
    } catch (Exception $e) {
        echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    }
    
    // Test 4: Test Application class
    echo "<p>4. Testing Application class...</p>";
    try {
        $app = \App\Core\Application::getInstance();
        echo "<p>✅ Application class instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ Application error: " . $e->getMessage() . "</p>";
    }
    
    // Test 5: Test Router
    echo "<p>5. Testing Router...</p>";
    try {
        $router = new \App\Core\Router();
        echo "<p>✅ Router class instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ Router error: " . $e->getMessage() . "</p>";
    }
    
    // Test 6: Test BaseController
    echo "<p>6. Testing BaseController...</p>";
    try {
        $controller = new \App\Core\BaseController();
        echo "<p>✅ BaseController instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ BaseController error: " . $e->getMessage() . "</p>";
    }
    
    // Test 7: Test specific controllers
    echo "<p>7. Testing HierarchyController...</p>";
    try {
        $hierarchyController = new \App\Controllers\HierarchyController();
        echo "<p>✅ HierarchyController instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ HierarchyController error: " . $e->getMessage() . "</p>";
    }
    
    // Test 8: Test session
    echo "<p>8. Testing session...</p>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p>✅ Session started, ID: " . session_id() . "</p>";
    
    // Test 9: Test auth functions
    echo "<p>9. Testing auth functions...</p>";
    $authCheck = auth_check();
    echo "<p>Auth check result: " . ($authCheck ? 'Authenticated' : 'Not authenticated') . "</p>";
    
    // Test 10: Simulate route access
    echo "<p>10. Testing route simulation...</p>";
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/hierarchy';
    echo "<p>Simulated request: GET /hierarchy</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Fatal error: " . $e->getMessage() . "</p>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
}

?>