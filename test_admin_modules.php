<?php
/**
 * Test script to verify all admin dashboard modules work correctly
 */

define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Simulate admin user session
    $_SESSION['user'] = [
        'id' => 1,
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin@abo-wbo.local',
        'role' => 'admin'
    ];
    $_SESSION['user_id'] = 1;
    $_SESSION['is_authenticated'] = true;
    
    echo "🔧 === ADMIN DASHBOARD MODULES TEST === 🔧\n\n";
    
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
    
    $results = [];
    
    foreach ($modules as $name => $route) {
        echo "Testing {$name} ({$route})... ";
        
        try {
            // Simulate GET request to the route
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI'] = $route;
            $_SERVER['PATH_INFO'] = $route;
            
            // Try to get the controller and method
            $router = $app->getRouter();
            
            // Parse the route to find controller and method
            $routeFound = false;
            $controllerClass = '';
            $method = '';
            
            // Check common patterns
            if ($route === '/tasks') {
                $controllerClass = \App\Controllers\TaskController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/meetings') {
                $controllerClass = \App\Controllers\MeetingController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/events') {
                $controllerClass = \App\Controllers\EventController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/donations') {
                $controllerClass = \App\Controllers\DonationController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/reports') {
                $controllerClass = \App\Controllers\ReportController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/member-registration') {
                $controllerClass = \App\Controllers\MemberRegistrationController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/responsibilities') {
                $controllerClass = \App\Controllers\ResponsibilityController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/hierarchy') {
                $controllerClass = \App\Controllers\HierarchyController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/users') {
                $controllerClass = \App\Controllers\UserController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/positions') {
                $controllerClass = \App\Controllers\PositionController::class;
                $method = 'index';
                $routeFound = true;
            } elseif ($route === '/settings') {
                $controllerClass = \App\Controllers\SettingsController::class;
                $method = 'index';
                $routeFound = true;
            }
            
            if ($routeFound && class_exists($controllerClass)) {
                $controller = new $controllerClass();
                
                if (method_exists($controller, $method)) {
                    // Start output buffering to catch any output
                    ob_start();
                    $result = $controller->$method();
                    $output = ob_get_clean();
                    
                    // Check if result is valid
                    if ($result !== false && $result !== null) {
                        $results[$name] = 'WORKING ✅';
                        echo "WORKING ✅\n";
                    } else {
                        $results[$name] = 'ERROR ❌';
                        echo "ERROR ❌ (null/false result)\n";
                    }
                } else {
                    $results[$name] = 'ERROR ❌';
                    echo "ERROR ❌ (method {$method} not found)\n";
                }
            } else {
                $results[$name] = 'ERROR ❌';
                echo "ERROR ❌ (controller not found or route not mapped)\n";
            }
            
        } catch (\Exception $e) {
            $results[$name] = 'ERROR ❌';
            echo "ERROR ❌ (" . $e->getMessage() . ")\n";
        }
    }
    
    echo "\n📊 === SUMMARY REPORT === 📊\n";
    
    $working = 0;
    $total = count($results);
    
    foreach ($results as $module => $status) {
        echo "• {$module}: {$status}\n";
        if (strpos($status, 'WORKING') !== false) {
            $working++;
        }
    }
    
    echo "\n🎯 OVERALL STATUS: {$working}/{$total} modules working (" . 
         round(($working/$total)*100) . "%)\n";
    
    if ($working === $total) {
        echo "\n🎉 SUCCESS: All admin dashboard modules are working correctly!\n";
    } else {
        echo "\n⚠️ ISSUES: Some modules need attention\n";
    }
    
} catch (\Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>