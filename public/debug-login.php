<?php
/**
 * DEBUG SCRIPT - Check Login Issue
 * Upload to /public_html/staging.j-abo-wbo.org/public/debug-login.php
 * Access: https://staging.j-abo-wbo.org/debug-login.php
 * DELETE THIS FILE AFTER DEBUGGING!
 */

// Start session
session_start();

// Load environment
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

// Database connection
$host = 'localhost';
$dbname = 'jabowbo_abo_staging';
$username = 'jabowbo_abo_user';
$password = 'YOUR_DB_PASSWORD_HERE'; // REPLACE WITH ACTUAL PASSWORD

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔍 Login Debug Information</h1>";
    echo "<hr>";
    
    // 1. Check if user exists
    echo "<h2>1. User Existence Check</h2>";
    $stmt = $pdo->prepare("SELECT id, email, password, user_type, status FROM users WHERE email = ?");
    $stmt->execute(['admin@abo-wbo.org']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User found!<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "User Type: " . $user['user_type'] . "<br>";
        echo "Status: " . $user['status'] . "<br>";
        echo "Password Hash: " . substr($user['password'], 0, 20) . "...<br>";
        echo "Hash Length: " . strlen($user['password']) . " characters<br>";
        echo "Hash starts with \$2y\$: " . (str_starts_with($user['password'], '$2y$') ? 'YES ✅' : 'NO ❌') . "<br>";
        echo "<hr>";
        
        // 2. Test password verification
        echo "<h2>2. Password Verification Test</h2>";
        $testPassword = 'admin123';
        $verified = password_verify($testPassword, $user['password']);
        echo "Test Password: '{$testPassword}'<br>";
        echo "Verification Result: " . ($verified ? '✅ MATCH' : '❌ NO MATCH') . "<br>";
        echo "<hr>";
        
        // 3. Generate correct hash for comparison
        echo "<h2>3. Correct Password Hash for 'admin123'</h2>";
        $correctHash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "Newly Generated Hash: " . $correctHash . "<br>";
        echo "This hash would work: " . (password_verify('admin123', $correctHash) ? 'YES ✅' : 'NO ❌') . "<br>";
        echo "<hr>";
        
        // 4. Session test
        echo "<h2>4. Session Functionality</h2>";
        $_SESSION['test'] = 'Session Working!';
        echo "Session ID: " . session_id() . "<br>";
        echo "Session Test Value: " . $_SESSION['test'] . "<br>";
        echo "Session Save Path: " . session_save_path() . "<br>";
        echo "Session File Writable: " . (is_writable(session_save_path()) ? 'YES ✅' : 'NO ❌') . "<br>";
        echo "<hr>";
        
        // 5. CSRF Token test
        echo "<h2>5. CSRF Token Test</h2>";
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        echo "CSRF Token: " . substr($_SESSION['_csrf_token'], 0, 20) . "...<br>";
        echo "CSRF Token Length: " . strlen($_SESSION['_csrf_token']) . " characters<br>";
        echo "<hr>";
        
        // 6. Recommendation
        echo "<h2>6. Fix Recommendation</h2>";
        if (!$verified) {
            echo "<div style='background: #ff6b6b; padding: 15px; border-radius: 5px; color: white;'>";
            echo "<strong>❌ PASSWORD MISMATCH DETECTED!</strong><br><br>";
            echo "The password in the database doesn't match 'admin123'.<br><br>";
            echo "<strong>Solution: Run this SQL query in phpMyAdmin:</strong><br>";
            echo "<code style='background: white; color: black; padding: 10px; display: block; margin-top: 10px;'>";
            echo "UPDATE users SET password = '$correctHash' WHERE email = 'admin@abo-wbo.org';";
            echo "</code>";
            echo "</div>";
        } else {
            echo "<div style='background: #51cf66; padding: 15px; border-radius: 5px; color: white;'>";
            echo "<strong>✅ PASSWORD IS CORRECT!</strong><br><br>";
            echo "Password verification works. The issue must be elsewhere (sessions, CSRF, redirect logic).";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #ff6b6b; padding: 15px; border-radius: 5px; color: white;'>";
        echo "❌ USER NOT FOUND!<br><br>";
        echo "Email 'admin@abo-wbo.org' does not exist in the users table.<br>";
        echo "Check if data was imported correctly.";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ DELETE THIS FILE AFTER DEBUGGING!</strong></p>";
    
} catch (PDOException $e) {
    echo "<div style='background: #ff6b6b; padding: 15px; border-radius: 5px; color: white;'>";
    echo "❌ DATABASE CONNECTION ERROR:<br>";
    echo $e->getMessage();
    echo "</div>";
}
?>
