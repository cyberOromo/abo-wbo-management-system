<?php
/**
 * Comprehensive Dashboard Error Checker
 * NOTE: session_start() must be called before any output!
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);
define('STORAGE_PATH', APP_ROOT . '/storage');

echo "<h2>Comprehensive Dashboard Error Check</h2>";

// 1. Load autoloader
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';
loadEnv(APP_ROOT . '/.env');

echo "<h3>1. Test Session Start (before any output)</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'email' => 'admin@abo-wbo.org',
    'first_name' => 'System',
    'last_name' => 'Admin',
    'user_type' => 'system_admin',
    'status' => 'active'
];
echo "✓ Session started and user set<br>";

// 2. Test Database
echo "<h3>2. Database Connection</h3>";
try {
    $db = \App\Utils\Database::getInstance();
    $result = $db->fetch("SELECT COUNT(*) as count FROM users");
    echo "✓ Database connected. Users count: " . ($result['count'] ?? 0) . "<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Test DashboardController instantiation
echo "<h3>3. DashboardController Test</h3>";
try {
    $controller = new \App\Controllers\DashboardController();
    echo "✓ DashboardController instantiated<br>";
} catch (Exception $e) {
    echo "✗ DashboardController error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// 4. Test index method
echo "<h3>4. Test Dashboard Index Method</h3>";
try {
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    if (empty($output)) {
        echo "✗ Dashboard index() returned empty output<br>";
    } else {
        echo "✓ Dashboard index() executed. Output length: " . strlen($output) . " bytes<br>";
        
        // Check if output contains errors
        if (stripos($output, 'fatal error') !== false || stripos($output, 'parse error') !== false) {
            echo "<div style='background: #ffcccc; padding: 10px; margin: 10px 0;'>";
            echo "<strong>ERROR FOUND IN OUTPUT:</strong><br>";
            echo htmlspecialchars(substr($output, 0, 500));
            echo "</div>";
        } else {
            echo "<details><summary>View Dashboard Output (first 1000 chars)</summary>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "...</pre>";
            echo "</details>";
        }
    }
} catch (Exception $e) {
    echo "✗ Dashboard index() error: " . $e->getMessage() . "<br>";
    echo "<pre>File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace:<br>" . $e->getTraceAsString() . "</pre>";
}

// 5. Check error log
echo "<h3>5. Recent PHP Error Log</h3>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -30);
    echo "<pre>" . htmlspecialchars(implode('', $recentLines)) . "</pre>";
} else {
    $logFiles = glob(STORAGE_PATH . '/logs/*.log');
    if ($logFiles) {
        $latestLog = array_pop($logFiles);
        $lines = file($latestLog);
        $recentLines = array_slice($lines, -30);
        echo "<strong>Log file: " . basename($latestLog) . "</strong><br>";
        echo "<pre>" . htmlspecialchars(implode('', $recentLines)) . "</pre>";
    } else {
        echo "No error logs found<br>";
    }
}

echo "<h3>6. Check View File</h3>";
$viewPaths = [
    APP_ROOT . '/resources/views/dashboard/admin.php',
    APP_ROOT . '/resources/views/dashboard/executive.php',
    APP_ROOT . '/resources/views/dashboard/member.php',
    APP_ROOT . '/app/Views/dashboard/admin.php',
    APP_ROOT . '/app/Views/dashboard/executive.php',
];

foreach ($viewPaths as $path) {
    if (file_exists($path)) {
        echo "✓ Found: " . basename(dirname($path)) . "/" . basename($path) . "<br>";
    } else {
        echo "✗ Missing: " . basename(dirname($path)) . "/" . basename($path) . "<br>";
    }
}
