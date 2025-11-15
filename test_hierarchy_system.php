<?php
// Test script for enhanced hierarchy system
require_once 'vendor/autoload.php';
require_once 'app/helpers.php';

use App\Utils\Database;
use App\Controllers\DashboardController;
use App\Services\HierarchyService;

echo "=== ENHANCED HIERARCHY SYSTEM TEST ===\n\n";

// 1. Test Database Schema Updates
echo "1. Testing enhanced database schema...\n";
$db = Database::getInstance();

// Check if hierarchy columns exist in tasks table
$taskColumns = $db->fetchAll("SHOW COLUMNS FROM tasks WHERE Field LIKE '%scope%' OR Field LIKE '%hierarchy%' OR Field LIKE '%gurmu%'");
echo "Enhanced task columns:\n";
foreach ($taskColumns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}
echo "\n";

// Check hierarchy tables
$hierarchyTables = $db->fetchAll("SHOW TABLES LIKE '%hierarchy%'");
echo "Hierarchy management tables:\n";
foreach ($hierarchyTables as $table) {
    echo "  - " . array_values($table)[0] . "\n";
}
echo "\n";

// 2. Test Dhangaa's Hierarchy Scope
echo "2. Testing Dhangaa Stream's hierarchy scope...\n";
$dhangaaScope = $db->fetch("
    SELECT ua.*, u.first_name, u.last_name, u.role,
           gu.name as gurmu_name, ga.name as gamta_name, go.name as godina_name
    FROM user_assignments ua 
    JOIN users u ON ua.user_id = u.id
    LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
    LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
    LEFT JOIN godinas go ON ua.godina_id = go.id
    WHERE ua.user_id = 33 AND ua.status = 'active'
");

if ($dhangaaScope) {
    echo "Dhangaa's Hierarchy Context:\n";
    echo "  - Name: {$dhangaaScope['first_name']} {$dhangaaScope['last_name']}\n";
    echo "  - Role: {$dhangaaScope['role']}\n";
    echo "  - Level: {$dhangaaScope['level_scope']}\n";
    echo "  - Gurmu: {$dhangaaScope['gurmu_name']} (ID: {$dhangaaScope['gurmu_id']})\n";
    echo "  - Gamta: {$dhangaaScope['gamta_name']} (ID: {$dhangaaScope['gamta_id']})\n";
    echo "  - Godina: {$dhangaaScope['godina_name']} (ID: {$dhangaaScope['godina_id']})\n\n";
} else {
    echo "ERROR: Could not find Dhangaa's hierarchy scope!\n\n";
}

// 3. Test Enhanced Hierarchy Service
echo "3. Testing Enhanced HierarchyService...\n";
try {
    $hierarchyService = new HierarchyService();
    
    if ($dhangaaScope) {
        $overview = $hierarchyService->getHierarchyOverview($dhangaaScope);
        if ($overview) {
            echo "Hierarchy Overview for {$dhangaaScope['gurmu_name']}:\n";
            echo "  - Total Members: {$overview['total_members']}\n";
            echo "  - Total Tasks: {$overview['total_tasks']}\n";
            echo "  - Total Meetings: {$overview['total_meetings']}\n";
            echo "  - Total Events: {$overview['total_events']}\n";
        } else {
            echo "No hierarchy overview data found.\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "HierarchyService Error: " . $e->getMessage() . "\n\n";
}

// 4. Test Enhanced Task Filtering
echo "4. Testing hierarchy-based task filtering...\n";

if ($dhangaaScope) {
    // Test the new getHierarchyScopedData method
    try {
        $hierarchyService = new HierarchyService();
        $scopedTasks = $hierarchyService->getHierarchyScopedData($dhangaaScope, 'tasks');
        
        echo "Tasks visible to Dhangaa (Minneapolis Gurmu scope):\n";
        if (empty($scopedTasks)) {
            echo "  - No hierarchy-scoped tasks found\n";
        } else {
            foreach ($scopedTasks as $task) {
                echo "  - {$task['title']} (Level: {$task['level_scope']}, Priority: {$task['priority']})\n";
            }
        }
    } catch (Exception $e) {
        echo "Task filtering error: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// 5. Test Dashboard Controller with Enhanced Methods
echo "5. Testing enhanced DashboardController methods...\n";
try {
    $dashboard = new DashboardController();
    
    // Test getUserHierarchicalScope method
    $userScope = $dashboard->getUserHierarchicalScope(33);
    if ($userScope) {
        echo "getUserHierarchicalScope() works!\n";
        echo "  - User: {$userScope['user_id']}\n";
        echo "  - Level: {$userScope['level_scope']}\n";
        echo "  - Scope ID: {$userScope['scope_id']}\n";
    } else {
        echo "ERROR: getUserHierarchicalScope() returned null\n";
    }
    
} catch (Exception $e) {
    echo "Dashboard method error: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Check for Sample Data
echo "6. Checking for sample hierarchy data...\n";
$taskCount = $db->fetch("SELECT COUNT(*) as count FROM tasks WHERE gurmu_id IS NOT NULL");
$eventCount = $db->fetch("SELECT COUNT(*) as count FROM events WHERE gurmu_id IS NOT NULL");
$communicationCount = $db->fetch("SELECT COUNT(*) as count FROM hierarchy_communications");
$metricsCount = $db->fetch("SELECT COUNT(*) as count FROM hierarchy_metrics");

echo "Hierarchy data in system:\n";
echo "  - Tasks with hierarchy: {$taskCount['count']}\n";
echo "  - Events with hierarchy: {$eventCount['count']}\n";
echo "  - Hierarchy communications: {$communicationCount['count']}\n";
echo "  - Hierarchy metrics: {$metricsCount['count']}\n\n";

// 7. Test Access Control
echo "7. Testing hierarchy-based access control...\n";
if ($dhangaaScope) {
    // Check what tasks Dhangaa should see based on his Minneapolis Gurmu scope
    $visibleTasks = $db->fetchAll("
        SELECT title, level_scope, 
               CASE 
                   WHEN gurmu_id = ? THEN 'Same Gurmu'
                   WHEN gamta_id = ? THEN 'Same Gamta'
                   WHEN godina_id = ? THEN 'Same Godina'
                   WHEN level_scope = 'global' THEN 'Global'
                   ELSE 'Not Accessible'
               END as access_reason
        FROM tasks 
        WHERE (
            (level_scope = 'gurmu' AND gurmu_id = ?) OR
            (level_scope = 'gamta' AND gamta_id = ?) OR
            (level_scope = 'godina' AND godina_id = ?) OR
            (level_scope = 'global') OR
            (level_scope = 'personal' AND (created_by = 33 OR assigned_to = 33))
        )
        ORDER BY level_scope, title
    ", [
        $dhangaaScope['gurmu_id'], $dhangaaScope['gamta_id'], $dhangaaScope['godina_id'],
        $dhangaaScope['gurmu_id'], $dhangaaScope['gamta_id'], $dhangaaScope['godina_id']
    ]);
    
    echo "Tasks accessible to Dhangaa:\n";
    foreach ($visibleTasks as $task) {
        echo "  - {$task['title']} ({$task['level_scope']}) - {$task['access_reason']}\n";
    }
}

echo "\n=== HIERARCHY SYSTEM TEST COMPLETE ===\n";
?>