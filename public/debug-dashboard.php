<?php
/**
 * Debug Dashboard 500 Error
 * NOTE: session_start() must be called before any output!
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define application constants
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);
define('STORAGE_PATH', APP_ROOT . '/storage');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Dashboard Error Debugging</h2>";

// Load autoloader and helpers
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

// Load .env
loadEnv(APP_ROOT . '/.env');

echo "<h3>1. Environment Variables Check</h3>";
echo "<pre>";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NOT SET') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'NOT SET') . "\n";
echo "DB_PASS: " . (isset($_ENV['DB_PASS']) ? '[SET - ' . strlen($_ENV['DB_PASS']) . ' chars]' : 'NOT SET') . "\n";
echo "</pre>";

echo "<h3>2. Config Check</h3>";
echo "<pre>";
$dbConfig = config('database');
echo "Config Host: " . ($dbConfig['host'] ?? 'NOT SET') . "\n";
echo "Config Name: " . ($dbConfig['name'] ?? 'NOT SET') . "\n";
echo "Config User: " . ($dbConfig['user'] ?? 'NOT SET') . "\n";
echo "Config Pass: " . (isset($dbConfig['pass']) ? '[SET - ' . strlen($dbConfig['pass']) . ' chars]' : 'NOT SET') . "\n";
echo "</pre>";

echo "<h3>3. Database Connection Test</h3>";
try {
    $db = \App\Utils\Database::getInstance();
    echo "<p style='color: green;'>✓ Database instance created</p>";
    
    $result = $db->fetch("SELECT 1 as test");
    echo "<p style='color: green;'>✓ Database query successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h3>4. Session Test</h3>";
session_start();
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "User Type: " . ($_SESSION['user']['user_type'] ?? 'NOT SET') . "\n";
echo "Logged In: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO') . "\n";
echo "</pre>";

echo "<h3>5. Try Loading DashboardController</h3>";
try {
    if (!isset($_SESSION['user_id'])) {
        echo "<p style='color: orange;'>⚠ Not logged in - using test session</p>";
        // Set test session
        $db = \App\Utils\Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = 'admin@abo-wbo.org'");
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        echo "<p style='color: green;'>✓ Test session created</p>";
    }
    
    $controller = new \App\Controllers\DashboardController();
    echo "<p style='color: green;'>✓ DashboardController instantiated</p>";
    
    // Try calling index method
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    if (strlen($output) > 0) {
        echo "<p style='color: green;'>✓ Dashboard index() executed successfully</p>";
        echo "<p>Output length: " . strlen($output) . " bytes</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Dashboard index() returned empty output</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ DashboardController error:</p>";
    echo "<pre>";
    echo "Message: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "File: " . htmlspecialchars($e->getFile()) . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}

echo "<h3>6. PHP Error Log (last 20 lines)</h3>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -20);
    echo "<pre>" . htmlspecialchars(implode('', $recentLines)) . "</pre>";
} else {
    echo "<p>Error log not accessible at: " . htmlspecialchars($errorLog) . "</p>";
    echo "<p>Check storage/logs/ directory</p>";
    
    $logFiles = glob(STORAGE_PATH . '/logs/*.log');
    if ($logFiles) {
        echo "<p>Found log files:</p><ul>";
        foreach ($logFiles as $logFile) {
            echo "<li>" . basename($logFile) . "</li>";
        }
        echo "</ul>";
    }
}
