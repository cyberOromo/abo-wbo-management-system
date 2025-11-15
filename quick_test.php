<?php
/**
 * Simple Module Test - No Output
 */

require_once 'vendor/autoload.php';
require_once 'app/helpers.php';

// Mock session to avoid header issues
ob_start();

// Mock authentication for testing
$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@test.com',
    'role' => 'admin'
];

$modules = [
    'Tasks' => 'App\Controllers\TaskController',
    'Meetings' => 'App\Controllers\MeetingController', 
    'Events' => 'App\Controllers\EventController',
    'Donations' => 'App\Controllers\DonationController',
    'Reports' => 'App\Controllers\ReportController',
    'Member Registration' => 'App\Controllers\MemberRegistrationController',
    'Responsibilities' => 'App\Controllers\ResponsibilityController',
    'Hierarchy' => 'App\Controllers\HierarchyController',
    'User Management' => 'App\Controllers\UserController',
    'Position Management' => 'App\Controllers\PositionController',
    'Settings' => 'App\Controllers\SettingsController'
];

$working = [];
$errors = [];

foreach ($modules as $name => $controllerClass) {    
    try {
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            
            // Suppress all output
            ob_start();
            $result = $controller->index();
            ob_end_clean();
            
            $working[] = $name;
        } else {
            $errors[] = "$name - Controller not found";
        }
        
    } catch (Throwable $e) {
        $errors[] = "$name - " . $e->getMessage();
    }
}

ob_end_clean();

echo "🎯 WORKING MODULES (" . count($working) . "/" . count($modules) . "): " . implode(', ', $working) . "\n";
if (!empty($errors)) {
    echo "❌ ISSUES (" . count($errors) . "): " . implode(' | ', $errors) . "\n";
}
echo "\nProgress: " . round((count($working) / count($modules)) * 100, 1) . "% Complete\n";