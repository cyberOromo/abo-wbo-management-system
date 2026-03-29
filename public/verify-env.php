<?php
/**
 * Environment Variables Verification Script
 * Upload this to staging to verify .env file and database connection
 * 
 * IMPORTANT: DELETE THIS FILE AFTER DEBUGGING!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Environment Variables Verification</h1>";
echo "<style>body{font-family:monospace;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";

// Define app root
define('APP_ROOT', dirname(__DIR__));

echo "<h2>1. File System Check</h2>";
echo "<pre>";
echo "Script Location: " . __FILE__ . "\n";
echo "APP_ROOT: " . APP_ROOT . "\n";
echo ".env path: " . APP_ROOT . '/.env' . "\n";
echo ".env exists: " . (file_exists(APP_ROOT . '/.env') ? "✅ YES" : "❌ NO") . "\n";
if (file_exists(APP_ROOT . '/.env')) {
    echo ".env readable: " . (is_readable(APP_ROOT . '/.env') ? "✅ YES" : "❌ NO") . "\n";
    echo ".env size: " . filesize(APP_ROOT . '/.env') . " bytes\n";
}
echo "</pre>";

// Load .env manually
echo "<h2>2. Loading .env File</h2>";
$envFile = APP_ROOT . '/.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<pre>Found " . count($lines) . " lines in .env file\n";
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $envVars[$key] = $value;
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    echo "Loaded " . count($envVars) . " environment variables\n";
    echo "✅ .env file loaded successfully\n";
    echo "</pre>";
} else {
    echo "<pre class='error'>❌ .env file not found!</pre>";
}

// Show critical environment variables (masked)
echo "<h2>3. Critical Environment Variables</h2>";
echo "<pre>";

$criticalVars = ['APP_NAME', 'APP_ENV', 'APP_URL', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT'];

foreach ($criticalVars as $var) {
    $value = $_ENV[$var] ?? 'NOT SET';
    
    // Mask password
    if ($var === 'DB_PASS') {
        $maskedValue = $value !== 'NOT SET' ? str_repeat('*', strlen($value)) . ' (' . strlen($value) . ' chars)' : 'NOT SET';
        $status = $value !== 'NOT SET' ? '✅' : '❌';
        echo "$status $var: $maskedValue\n";
    } else {
        $status = $value !== 'NOT SET' ? '✅' : '❌';
        echo "$status $var: $value\n";
    }
}
echo "</pre>";

// Test database connection
echo "<h2>4. Database Connection Test</h2>";
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'abo_wbo_db';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $port = $_ENV['DB_PORT'] ?? '3306';
    
    echo "<pre>";
    echo "Attempting connection with:\n";
    echo "  Host: $host\n";
    echo "  Port: $port\n";
    echo "  Database: $dbname\n";
    echo "  User: $user\n";
    echo "  Password: " . (strlen($pass) > 0 ? str_repeat('*', strlen($pass)) . " ($" . strlen($pass) . " chars)" : "EMPTY") . "\n\n";
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<span class='success'>✅ Database connection successful!</span>\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "\n✅ Users table accessible\n";
    echo "✅ User count: " . $result['count'] . "\n";
    
    // Check admin user
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, user_type, status FROM users WHERE email = ?");
    $stmt->execute(['admin@abo-wbo.org']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "\n✅ Admin user found:\n";
        echo "   ID: {$admin['id']}\n";
        echo "   Email: {$admin['email']}\n";
        echo "   Name: {$admin['first_name']} {$admin['last_name']}\n";
        echo "   Type: {$admin['user_type']}\n";
        echo "   Status: {$admin['status']}\n";
    } else {
        echo "\n❌ Admin user NOT found!\n";
    }
    
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<pre class='error'>";
    echo "❌ Database connection FAILED!\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "</pre>";
}

// Test config loading
echo "<h2>5. Config File Test</h2>";
echo "<pre>";
try {
    $appConfig = require APP_ROOT . '/config/app.php';
    echo "✅ config/app.php loaded\n";
    echo "App Name: " . ($appConfig['app_name'] ?? 'NOT SET') . "\n";
    echo "Database Host: " . ($appConfig['database']['host'] ?? 'NOT SET') . "\n";
    echo "Database Name: " . ($appConfig['database']['name'] ?? 'NOT SET') . "\n";
    echo "Database User: " . ($appConfig['database']['user'] ?? 'NOT SET') . "\n";
    
    $dbConfig = require APP_ROOT . '/config/database.php';
    echo "\n✅ config/database.php loaded\n";
    echo "DB Config Host: " . ($dbConfig['host'] ?? 'NOT SET') . "\n";
    echo "DB Config Name: " . ($dbConfig['name'] ?? 'NOT SET') . "\n";
    echo "DB Config User: " . ($dbConfig['user'] ?? 'NOT SET') . "\n";
} catch (Exception $e) {
    echo "<span class='error'>❌ Config loading failed: " . $e->getMessage() . "</span>\n";
}
echo "</pre>";

// Session test
echo "<h2>6. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Active" : "❌ Not Active") . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Save Path Writable: " . (is_writable(session_save_path()) ? "✅ YES" : "❌ NO") . "\n";
echo "</pre>";

echo "<h2>7. Recommendations</h2>";
echo "<pre>";
if (!file_exists(APP_ROOT . '/.env')) {
    echo "<span class='error'>❌ Create .env file in: " . APP_ROOT . "</span>\n";
}

if (!isset($_ENV['DB_HOST']) || !isset($_ENV['DB_NAME']) || !isset($_ENV['DB_USER'])) {
    echo "<span class='error'>❌ Critical environment variables missing!</span>\n";
    echo "Make sure your .env file contains:\n";
    echo "DB_HOST=localhost\n";
    echo "DB_NAME=your_database_name\n";
    echo "DB_USER=your_database_user\n";
    echo "DB_PASS=your_database_password\n";
}

if (isset($pdo)) {
    echo "\n<span class='success'>✅ Everything looks good! Database is accessible.</span>\n";
} else {
    echo "\n<span class='error'>❌ Database connection failed. Check credentials in .env</span>\n";
}
echo "</pre>";

echo "<hr><p><strong style='color:red;'>⚠️ SECURITY WARNING: DELETE THIS FILE AFTER DEBUGGING!</strong></p>";
