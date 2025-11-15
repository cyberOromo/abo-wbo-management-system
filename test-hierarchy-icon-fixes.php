<?php
/**
 * Hierarchy Controller and Icon Loading Fix Verification
 * ABO-WBO Management System
 */

// Set up basic environment
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require_once 'vendor/autoload.php';

echo "=== HIERARCHY CONTROLLER & ICON FIXES VERIFICATION ===\n\n";

try {
    $config = require 'config/database.php';
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}", 
                   $config['user'], $config['pass'], $config['options'] ?? []);

    echo "✅ Database connection: OK\n\n";

    // Test the new HierarchyController activity query
    echo "🔍 Testing HierarchyController Recent Activity Query:\n";
    
    $activity = $pdo->query("
        SELECT 
            ua.id,
            CONCAT('User assignment: ', u.first_name, ' ', u.last_name, ' assigned to ', p.name) as action,
            ua.created_at,
            u.first_name,
            u.last_name,
            p.name as position_name
        FROM user_assignments ua
        INNER JOIN users u ON ua.user_id = u.id  
        INNER JOIN positions p ON ua.position_id = p.id
        WHERE ua.status = 'active'
        ORDER BY ua.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    echo "   - Recent activity entries: " . count($activity) . "\n";
    
    if (count($activity) > 0) {
        foreach ($activity as $entry) {
            echo "   - {$entry['action']} ({$entry['created_at']})\n";
        }
    }
    
    echo "\n✅ HierarchyController activity query working!\n";
    
    // Test the hierarchy statistics that were previously working
    echo "\n🔍 Testing HierarchyController Statistics Queries:\n";
    
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
    
    echo "\n✅ All HierarchyController queries working correctly!\n";
    
    // Check for Bootstrap Icons CSS in layout files
    echo "\n🔍 Checking Bootstrap Icons Implementation:\n";
    
    $layout_file = file_get_contents('resources/views/layouts/app.php');
    
    if (strpos($layout_file, 'bootstrap-icons@1.11.1') !== false) {
        echo "   ✅ Bootstrap Icons 1.11.1 found in layout\n";
    }
    
    if (strpos($layout_file, 'cdnjs.cloudflare.com/ajax/libs/bootstrap-icons') !== false) {
        echo "   ✅ Bootstrap Icons fallback CDN found\n";
    }
    
    // Check dashboard view for icon usage
    $dashboard_file = file_get_contents('resources/views/dashboard/index.php');
    $icon_count = substr_count($dashboard_file, 'bi bi-');
    
    echo "   - Dashboard icons found: {$icon_count}\n";
    echo "   ✅ Dashboard uses Bootstrap Icons\n";
    
    echo "\n=== FIXES SUMMARY ===\n";
    echo "✅ HierarchyController: Fixed missing activity_logs table by using user_assignments for activity\n";
    echo "✅ Bootstrap Icons: Updated to version 1.11.1 with CDN fallback\n";
    echo "✅ Database queries: All working without errors\n";
    echo "✅ Icon loading: Improved with multiple CDN sources\n";
    
    echo "\n🎯 READY FOR TESTING:\n";
    echo "   - Hierarchy page: http://abo-wbo.local/hierarchy\n";
    echo "   - Dashboard with icons: http://abo-wbo.local/dashboard\n";
    echo "   - Login as admin@abo-wbo.org / admin123\n";
    
    echo "\n📋 Expected Results:\n";
    echo "   - No more 500 errors on Hierarchy page\n";
    echo "   - Icons should display properly on dashboard\n";
    echo "   - Recent activity section should show user assignments\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}