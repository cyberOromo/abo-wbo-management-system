<?php
// Admin Module Diagnostic - Test authenticated admin access
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>ABO-WBO Admin Module Diagnostic</h2>";

try {
    // Set up environment
    define('APP_ROOT', dirname(__DIR__));
    require_once APP_ROOT . '/vendor/autoload.php';
    require_once APP_ROOT . '/app/helpers.php';
    
    echo "<p>✅ Environment setup complete</p>";
    
    // Start session
    session_start();
    echo "<p>✅ Session started</p>";
    
    // Mock admin user session (simulate logged-in admin)
    $_SESSION['user_id'] = 1;
    $_SESSION['user'] = [
        'id' => 1,
        'email' => 'admin@test.com',
        'role' => 'admin',
        'permissions' => ['admin'],
        'first_name' => 'Test',
        'last_name' => 'Admin'
    ];
    
    echo "<p>✅ Mock admin session created</p>";
    
    // Test auth check
    if (auth_check()) {
        echo "<p>✅ auth_check() returns TRUE</p>";
    } else {
        echo "<p>❌ auth_check() returns FALSE</p>";
    }
    
    // Test user retrieval
    $user = auth_user();
    if ($user) {
        echo "<p>✅ auth_user() returns: " . htmlspecialchars($user['email']) . " (Role: " . htmlspecialchars($user['role']) . ")</p>";
    } else {
        echo "<p>❌ auth_user() returns NULL</p>";
    }
    
    // Test Database connection
    try {
        $db = \App\Utils\Database::getInstance();
        echo "<p>✅ Database connection successful</p>";
        
        // Test a simple query
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>✅ Database query test successful (found {$result['count']} users)</p>";
    } catch (Exception $e) {
        echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr><h3>Testing Admin Controllers</h3>";
    
    // Test HierarchyController instantiation
    try {
        $hierarchyController = new \App\Controllers\HierarchyController();
        echo "<p>✅ HierarchyController instantiated successfully</p>";
        
        // Try to call some methods that might be causing issues
        try {
            // Test getHierarchyStats (commonly used method)
            $reflection = new ReflectionClass($hierarchyController);
            if ($reflection->hasMethod('getHierarchyStats')) {
                echo "<p>✅ getHierarchyStats method exists</p>";
            }
        } catch (Exception $e) {
            echo "<p>⚠️ Method reflection error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ HierarchyController error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    }
    
    // Test UserController
    try {
        $userController = new \App\Controllers\UserController();
        echo "<p>✅ UserController instantiated successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ UserController error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    }
    
    // Test PositionController
    try {
        $positionController = new \App\Controllers\PositionController();
        echo "<p>✅ PositionController instantiated successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ PositionController error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    }
    
    // Test SettingsController
    try {
        $settingsController = new \App\Controllers\SettingsController();
        echo "<p>✅ SettingsController instantiated successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ SettingsController error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    }
    
    echo "<hr><h3>Testing Model Dependencies</h3>";
    
    // Test models that admin controllers depend on
    try {
        $godinaModel = new \App\Models\Godina();
        echo "<p>✅ Godina model instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ Godina model error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    try {
        $gamtaModel = new \App\Models\Gamta();
        echo "<p>✅ Gamta model instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ Gamta model error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    try {
        $gurmuModel = new \App\Models\Gurmu();
        echo "<p>✅ Gurmu model instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ Gurmu model error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr><h3>Testing View Files</h3>";
    
    // Test if the modern view files exist and are readable
    $modernViews = [
        'hierarchy/index_modern.php',
        'user-management/index_modern.php',
        'position-management/index_modern.php',
        'settings/index_modern.php',
        'responsibilities/index_modern.php'
    ];
    
    foreach ($modernViews as $view) {
        $viewPath = APP_ROOT . '/resources/views/' . $view;
        if (file_exists($viewPath)) {
            if (is_readable($viewPath)) {
                echo "<p>✅ View exists and readable: {$view}</p>";
            } else {
                echo "<p>⚠️ View exists but not readable: {$view}</p>";
            }
        } else {
            echo "<p>❌ View missing: {$view}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ FATAL ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
}
?>