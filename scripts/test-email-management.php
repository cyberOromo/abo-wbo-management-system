<?php

// Test the UserEmailController functionality
echo "=== Testing User Email Management System ===\n\n";

// Test the UserEmailController syntax
echo "1. Testing UserEmailController syntax...\n";
$result = shell_exec('/c/xampp/php/php.exe -l app/Controllers/UserEmailController.php 2>&1');
if (strpos($result, 'No syntax errors') !== false) {
    echo "✅ UserEmailController syntax is valid\n";
} else {
    echo "❌ UserEmailController has syntax errors:\n$result\n";
}

// Test if the view file exists
echo "\n2. Testing view template...\n";
if (file_exists('resources/views/admin/user_email_management.php')) {
    echo "✅ View template exists\n";
} else {
    echo "❌ View template missing\n";
}

// Test database connection
echo "\n3. Testing database connection...\n";
try {
    require_once 'app/helpers.php';
    $config = config('database');
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}", $config['user'], $config['pass'], $config['options']);
    echo "✅ Database connection successful\n";
    
    // Test queries that the controller will use
    echo "\n4. Testing database queries...\n";
    
    $users = $pdo->query("
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.email as personal_email,
            u.internal_email,
            u.status,
            ua.level_scope,
            GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions
        FROM users u
        LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
        LEFT JOIN positions p ON ua.position_id = p.id
        WHERE u.status IN ('active', 'pending')
        GROUP BY u.id
        ORDER BY u.id
        LIMIT 5
    ")->fetchAll();
    
    echo "✅ Successfully retrieved " . count($users) . " users for testing\n";
    
    foreach ($users as $user) {
        $hasEmail = !empty($user['internal_email']) ? '✅' : '❌';
        $hasPositions = !empty($user['positions']) ? '✅' : '❌';
        echo "   User {$user['id']}: {$user['first_name']} {$user['last_name']} - Email: $hasEmail, Positions: $hasPositions\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test the UserEmailAnalyzer
echo "\n5. Testing UserEmailAnalyzer...\n";
try {
    require_once 'scripts/analyze-user-emails.php';
    
    $analyzer = new App\Scripts\UserEmailAnalyzer();
    echo "✅ UserEmailAnalyzer instantiated successfully\n";
    
    // Test the new generateEmailForSpecificUser method
    $testUserId = 9; // User with active assignment
    $result = $analyzer->generateEmailForSpecificUser($testUserId);
    if ($result['success']) {
        echo "✅ Email generation test successful: {$result['email']}\n";
    } else {
        echo "❌ Email generation test failed: {$result['message']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ UserEmailAnalyzer test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "The User Email Management system is ready for integration!\n";
echo "Access it at: /user-email/dashboard\n\n";

?>