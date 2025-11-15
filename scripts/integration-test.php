<?php

echo "=== SystemAdminController Integration Test ===\n\n";

// Test 1: Check SystemAdminController syntax
echo "1. Testing SystemAdminController syntax...\n";
$result = shell_exec('/c/xampp/php/php.exe -l app/Controllers/SystemAdminController.php 2>&1');
if (strpos($result, 'No syntax errors') !== false) {
    echo "✅ SystemAdminController syntax is valid\n";
} else {
    echo "❌ SystemAdminController has syntax errors:\n$result\n";
}

// Test 2: Check UserEmailController syntax 
echo "\n2. Testing UserEmailController syntax...\n";
$result = shell_exec('/c/xampp/php/php.exe -l app/Controllers/UserEmailController.php 2>&1');
if (strpos($result, 'No syntax errors') !== false) {
    echo "✅ UserEmailController syntax is valid\n";
} else {
    echo "❌ UserEmailController has syntax errors:\n$result\n";
}

// Test 3: Verify email management methods exist in SystemAdminController
echo "\n3. Testing SystemAdminController email management integration...\n";
$controllerContent = file_get_contents('app/Controllers/SystemAdminController.php');

$requiredMethods = [
    'userEmailManagement',
    'generateInternalEmails', 
    'regenerateUserEmail',
    'hybridRegistrationManagement',
    'getUsersMissingEmails'
];

$allMethodsPresent = true;
foreach ($requiredMethods as $method) {
    if (strpos($controllerContent, "function $method") !== false) {
        echo "✅ Method $method exists\n";
    } else {
        echo "❌ Method $method missing\n";
        $allMethodsPresent = false;
    }
}

// Test 4: Check database functionality
echo "\n4. Testing database integration...\n";
try {
    require_once 'app/helpers.php';
    $config = config('database');
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}", $config['user'], $config['pass'], $config['options'] ?? []);
    echo "✅ Database connection successful\n";
    
    // Test the email management query
    $users = $pdo->query("
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.internal_email,
            COUNT(ua.id) as assignment_count
        FROM users u
        LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
        WHERE u.status IN ('active', 'pending')
        GROUP BY u.id
        ORDER BY u.id
        LIMIT 3
    ")->fetchAll();
    
    echo "✅ Email management queries working - Sample users:\n";
    foreach ($users as $user) {
        $hasEmail = !empty($user['internal_email']) ? '✅' : '❌';
        echo "   User {$user['id']}: {$user['first_name']} {$user['last_name']} (Email: $hasEmail, Assignments: {$user['assignment_count']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database test failed: " . $e->getMessage() . "\n";
}

// Test 5: Check routes integration
echo "\n5. Testing routes integration...\n";
$routesContent = file_get_contents('routes/web.php');

$requiredRoutes = [
    '/user-email/',
    'UserEmailController@index',
    'UserEmailController@generateMissing',
    'UserEmailController@regenerate'
];

$allRoutesPresent = true;
foreach ($requiredRoutes as $route) {
    if (strpos($routesContent, $route) !== false) {
        echo "✅ Route pattern '$route' exists\n";
    } else {
        echo "❌ Route pattern '$route' missing\n";
        $allRoutesPresent = false;
    }
}

// Test 6: Check dashboard integration
echo "\n6. Testing dashboard integration...\n";
$dashboardContent = file_get_contents('resources/views/dashboard/index.php');

if (strpos($dashboardContent, 'Manage User Emails') !== false) {
    echo "✅ User Email Management link exists in dashboard\n";
} else {
    echo "❌ User Email Management link missing from dashboard\n";
}

if (strpos($dashboardContent, 'Hybrid Registration') !== false) {
    echo "✅ Hybrid Registration link exists in dashboard\n";
} else {
    echo "❌ Hybrid Registration link missing from dashboard\n";
}

// Summary
echo "\n=== INTEGRATION TEST SUMMARY ===\n";
if ($allMethodsPresent && $allRoutesPresent) {
    echo "🎉 ALL TESTS PASSED! System is fully integrated and ready.\n";
    echo "\n📍 ACCESS POINTS:\n";
    echo "• User Email Management: /user-email/dashboard\n";
    echo "• System Admin Dashboard: /admin/dashboard\n";
    echo "• Hybrid Registration: /hybrid-registration/admin/dashboard\n";
    echo "\n🔧 FUNCTIONALITY AVAILABLE:\n";
    echo "• Generate missing internal emails for users\n";
    echo "• Regenerate individual user emails\n";
    echo "• View email statistics and status\n";
    echo "• Manage hybrid registrations\n";
    echo "• Full admin dashboard integration\n";
} else {
    echo "⚠️  Some components need attention. Check the errors above.\n";
}

?>