<?php
/**
 * COMPREHENSIVE ERROR DEBUG SCRIPT
 * Upload to: /public_html/staging.j-abo-wbo.org/public/check-errors.php
 * Access: https://staging.j-abo-wbo.org/check-errors.php
 * DELETE THIS FILE AFTER DEBUGGING!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Comprehensive Error Check</h1><hr>";

// 1. Check if we can start session and get user
session_start();
echo "<h2>1. Session & Authentication Check</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "User ID in session: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "User data exists: " . (isset($_SESSION['user']) ? 'YES ✅' : 'NO ❌') . "<br>";

if (isset($_SESSION['user'])) {
    echo "User Type: " . ($_SESSION['user']['user_type'] ?? 'NOT SET') . "<br>";
    echo "User Email: " . ($_SESSION['user']['email'] ?? 'NOT SET') . "<br>";
}
echo "<hr>";

// 2. Check if files exist
echo "<h2>2. Critical Files Check</h2>";
$files = [
    'vendor/autoload.php' => __DIR__ . '/../vendor/autoload.php',
    'app/helpers.php' => __DIR__ . '/../app/helpers.php',
    'app/Controllers/DashboardController.php' => __DIR__ . '/../app/Controllers/DashboardController.php',
    'app/Core/Controller.php' => __DIR__ . '/../app/Core/Controller.php',
    'config/app.php' => __DIR__ . '/../config/app.php',
    '.env' => __DIR__ . '/../.env',
    'resources/views/layouts/app.php' => __DIR__ . '/../resources/views/layouts/app.php',
    'resources/views/dashboard/admin.php' => __DIR__ . '/../resources/views/dashboard/admin.php',
];

foreach ($files as $name => $path) {
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    echo "$name: " . ($exists ? '✅ EXISTS' : '❌ MISSING');
    if ($exists) {
        echo " | " . ($readable ? '✅ READABLE' : '❌ NOT READABLE');
        echo " | Size: " . filesize($path) . " bytes";
    }
    echo "<br>";
}
echo "<hr>";

// 3. Try to load autoloader and helpers
echo "<h2>3. Autoloader & Helpers Test</h2>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoloader loaded<br>";
} catch (Exception $e) {
    echo "❌ Autoloader failed: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/../app/helpers.php';
    echo "✅ Helpers loaded<br>";
    
    // Test helper functions
    if (function_exists('auth_user')) {
        echo "✅ auth_user() function exists<br>";
        $user = auth_user();
        echo "auth_user() result: " . ($user ? 'USER FOUND' : 'NULL') . "<br>";
    } else {
        echo "❌ auth_user() function NOT FOUND<br>";
    }
    
    if (function_exists('csrf_token')) {
        echo "✅ csrf_token() function exists<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Helpers failed: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 4. Try to instantiate DashboardController
echo "<h2>4. DashboardController Test</h2>";
try {
    require_once __DIR__ . '/../app/Utils/Database.php';
    require_once __DIR__ . '/../app/Core/Controller.php';
    require_once __DIR__ . '/../app/Controllers/DashboardController.php';
    
    echo "✅ All controller files loaded<br>";
    
    $controller = new \App\Controllers\DashboardController();
    echo "✅ DashboardController instantiated<br>";
    
    // Try to call index method
    ob_start();
    try {
        $controller->index();
        $output = ob_get_clean();
        echo "✅ index() method executed<br>";
        echo "Output length: " . strlen($output) . " bytes<br>";
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ index() method failed: " . $e->getMessage() . "<br>";
        echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Controller instantiation failed: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// 5. Check Database Connection
echo "<h2>5. Database Connection Test</h2>";
try {
    require_once __DIR__ . '/../app/Utils/Database.php';
    $db = \App\Utils\Database::getInstance();
    echo "✅ Database connection successful<br>";
    
    // Try a simple query
    $result = $db->fetch("SELECT COUNT(*) as count FROM users");
    echo "✅ Query executed: " . $result['count'] . " users in database<br>";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// 6. Check PHP errors
echo "<h2>6. PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . error_reporting() . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "<hr>";

// 7. Check if error log exists
echo "<h2>7. Error Logs Check</h2>";
$logPath = __DIR__ . '/../storage/logs/';
if (is_dir($logPath)) {
    echo "Logs directory exists: ✅<br>";
    echo "Logs directory writable: " . (is_writable($logPath) ? '✅' : '❌') . "<br>";
    
    $logs = glob($logPath . '*.log');
    if ($logs) {
        echo "Found " . count($logs) . " log files:<br>";
        foreach ($logs as $log) {
            echo "- " . basename($log) . " (" . filesize($log) . " bytes)<br>";
        }
    } else {
        echo "No log files found<br>";
    }
} else {
    echo "❌ Logs directory does not exist<br>";
}
echo "<hr>";

echo "<p style='color: red;'><strong>⚠️ DELETE THIS FILE AFTER DEBUGGING!</strong></p>";
?>
