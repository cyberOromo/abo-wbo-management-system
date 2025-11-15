<?php
require_once 'vendor/autoload.php';
require_once 'app/helpers.php';

echo "🔧 Module Status Check\n\n";

$controllers = [
    'TaskController',
    'MeetingController', 
    'EventController',
    'DonationController',
    'ReportController',
    'MemberRegistrationController',
    'ResponsibilityController',
    'HierarchyController',
    'UserController',
    'PositionController',
    'SettingsController'
];

$working = 0;
$total = count($controllers);

foreach ($controllers as $controller) {
    $class = "App\\Controllers\\$controller";
    if (class_exists($class)) {
        echo "✅ $controller\n";
        $working++;
    } else {
        echo "❌ $controller - Not found\n";
    }
}

echo "\nProgress: $working/$total (" . round(($working/$total)*100, 1) . "%)\n";