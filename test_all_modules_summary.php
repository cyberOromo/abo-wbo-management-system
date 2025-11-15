<?php
/**
 * Quick Test Summary for All Admin Modules
 * Tests each module and reports status
 */

require_once 'vendor/autoload.php';
require_once 'app/helpers.php';

echo "🔧 === ADMIN DASHBOARD MODULES SUMMARY === 🔧\n\n";

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
    'Tasks' => '/tasks',
    'Meetings' => '/meetings', 
    'Events' => '/events',
    'Donations' => '/donations',
    'Reports' => '/reports',
    'Member Registration' => '/member-registration',
    'Responsibilities' => '/responsibilities',
    'Hierarchy Management' => '/hierarchy',
    'User Management' => '/users',
    'Position Management' => '/positions',
    'Settings' => '/settings'
];

$working = [];
$errors = [];

foreach ($modules as $name => $route) {
    echo "Testing $name ($route)... ";
    
    try {
        // Get the controller name from route
        $controllerClass = '';
        switch ($route) {
            case '/tasks':
                $controllerClass = 'App\Controllers\TaskController';
                break;
            case '/meetings':
                $controllerClass = 'App\Controllers\MeetingController';
                break;
            case '/events':
                $controllerClass = 'App\Controllers\EventController';
                break;
            case '/donations':
                $controllerClass = 'App\Controllers\DonationController';
                break;
            case '/reports':
                $controllerClass = 'App\Controllers\ReportController';
                break;
            case '/member-registration':
                $controllerClass = 'App\Controllers\MemberRegistrationController';
                break;
            case '/responsibilities':
                $controllerClass = 'App\Controllers\ResponsibilityController';
                break;
            case '/hierarchy':
                $controllerClass = 'App\Controllers\HierarchyController';
                break;
            case '/users':
                $controllerClass = 'App\Controllers\UserController';
                break;
            case '/positions':
                $controllerClass = 'App\Controllers\PositionController';
                break;
            case '/settings':
                $controllerClass = 'App\Controllers\SettingsController';
                break;
        }
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            
            // Capture output to suppress it during testing
            ob_start();
            $result = $controller->index();
            $output = ob_get_clean();
            
            echo "WORKING ✅\n";
            $working[] = $name;
        } else {
            echo "ERROR - Controller not found\n";
            $errors[] = "$name - Controller not found";
        }
        
    } catch (Throwable $e) {
        echo "ERROR - " . $e->getMessage() . "\n";
        $errors[] = "$name - " . $e->getMessage();
    }
}

echo "\n";
echo "🎯 === SUMMARY === 🎯\n";
echo "Working Modules (" . count($working) . "/" . count($modules) . "):\n";
foreach ($working as $module) {
    echo "✅ $module\n";
}

if (!empty($errors)) {
    echo "\nModules with Issues (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "❌ $error\n";
    }
}

echo "\nProgress: " . round((count($working) / count($modules)) * 100) . "% Complete\n";