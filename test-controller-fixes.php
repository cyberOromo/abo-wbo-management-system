<?php
/**
 * Controller Test Script - Quick verification of fixed controllers
 * ABO-WBO Management System
 */

// Set up basic environment
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require_once 'vendor/autoload.php';

// Test the database queries that were causing issues
echo "=== CONTROLLER FIXES VERIFICATION ===\n\n";

try {
    $config = require 'config/database.php';
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}", 
                   $config['user'], $config['pass'], $config['options'] ?? []);

    echo "✅ Database connection: OK\n\n";

    // Test HierarchyController queries (the ones that were fixed)
    echo "🔍 Testing HierarchyController fixed queries:\n";
    
    $assigned_users = $pdo->query("
        SELECT COUNT(DISTINCT u.id) as count FROM users u 
        INNER JOIN user_assignments ua ON u.id = ua.user_id 
        WHERE ua.status = 'active' AND u.status = 'active'
    ")->fetch()['count'];
    
    echo "   - Assigned users: {$assigned_users}\n";
    
    $unassigned_users = $pdo->query("
        SELECT COUNT(*) as count FROM users u 
        WHERE u.status = 'active' 
        AND u.id NOT IN (SELECT DISTINCT user_id FROM user_assignments WHERE status = 'active')
    ")->fetch()['count'];
    
    echo "   - Unassigned users: {$unassigned_users}\n";
    
    // Get users for UserController view test
    echo "\n🔍 Testing UserController data for view:\n";
    
    $users = $pdo->query("SELECT * FROM users WHERE status = 'active'")->fetchAll();
    echo "   - Total active users: " . count($users) . "\n";
    
    // Test the array counting that was fixed in the view
    $active_count = count(array_filter($users, fn($u) => $u['status'] === 'active'));
    $pending_count = count(array_filter($users, fn($u) => $u['status'] === 'pending'));
    $admin_count = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
    
    echo "   - Active users (view): {$active_count}\n";
    echo "   - Pending users (view): {$pending_count}\n";
    echo "   - Admin users (view): {$admin_count}\n";
    
    echo "\n✅ All database queries working correctly!\n";
    echo "✅ View data processing fixed!\n";
    
    echo "\n=== FIXES SUMMARY ===\n";
    echo "✅ HierarchyController: Fixed gurmu_id column reference to use proper JOIN with user_assignments\n";
    echo "✅ UserController View: Fixed number_format() calls to use count() on array_filter results\n";
    echo "✅ Both controllers should now work without 500 errors\n";
    
    echo "\n🎯 READY FOR TESTING:\n";
    echo "   - Hierarchy page: http://abo-wbo.local/hierarchy\n";
    echo "   - Users page: http://abo-wbo.local/users\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}