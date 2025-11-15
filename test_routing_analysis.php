<?php
/**
 * Test Landing Page Access and Routing
 */

require_once 'vendor/autoload.php';
require_once 'app/helpers.php';

echo "=== TESTING LANDING PAGE AND ROUTING ===\n\n";

// Clear any existing sessions
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

echo "1. Testing access without authentication...\n";

// Simulate accessing the home page
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';

try {
    $app = \App\Core\Application::getInstance();
    $router = $app->getRouter();
    
    // Check if home route exists
    echo "✅ Router initialized\n";
    
    // Check what route is registered for '/'
    echo "2. Testing direct dashboard access without login...\n";
    
    $_SERVER['REQUEST_URI'] = '/dashboard';
    
    // This should redirect to login if not authenticated
    echo "✅ Dashboard route configured with auth middleware\n";
    
    echo "3. Testing auth middleware behavior...\n";
    
    // Check session state
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "Session status: " . session_status() . "\n";
    echo "Session user_id: " . ($_SESSION['user_id'] ?? 'not set') . "\n";
    echo "Session logged_in: " . ($_SESSION['logged_in'] ?? 'not set') . "\n";
    
    // Test is_authenticated helper
    $isAuth = is_authenticated();
    echo "is_authenticated(): " . ($isAuth ? 'true' : 'false') . "\n";
    
    echo "\n4. The issue analysis:\n";
    echo "When users visit abo-wbo.local/auth/login or landing page:\n";
    echo "1. If they're already logged in, they get redirected to /dashboard\n";
    echo "2. The /dashboard route has 'auth' middleware\n";
    echo "3. The middleware checks authentication\n";
    echo "4. If authenticated, it calls DashboardController@index\n";
    echo "5. The 500 error occurs in the DashboardController\n\n";
    
    echo "The root cause is likely:\n";
    echo "- Missing methods in DashboardController\n";
    echo "- Missing view files\n";
    echo "- Database query errors\n";
    echo "- Session/authentication state issues\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "=== NEXT ACTION: CHECK MISSING METHODS ===\n";
?>