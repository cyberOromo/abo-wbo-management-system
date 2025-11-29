<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\HierarchyController;

echo "Testing HierarchyController fixes...\n\n";

// Simulate authenticated session
$_SESSION['user'] = [
    'id' => 1,
    'email' => 'admin@abo-wbo.org',
    'role' => 'admin',
    'first_name' => 'Admin',
    'last_name' => 'User'
];

try {
    echo "1. Testing Godina page (ID 8)...\n";
    $_GET['type'] = 'godina';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    $controller = new HierarchyController();
    ob_start();
    $controller->show(8);
    $output = ob_get_clean();
    
    if (strpos($output, '500') !== false || strpos($output, 'ERROR') !== false) {
        echo "   ❌ FAILED - Contains error\n";
    } else if (strpos($output, 'Godina Details') !== false || strpos($output, 'Afrikaa') !== false) {
        echo "   ✅ SUCCESS - Page renders\n";
    } else {
        echo "   ⚠️  UNKNOWN - No clear success/fail\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
}

try {
    echo "\n2. Testing Gamta page (ID 10)...\n";
    $_GET['type'] = 'gamta';
    
    $controller = new HierarchyController();
    ob_start();
    $controller->show(10);
    $output = ob_get_clean();
    
    if (strpos($output, '500') !== false || strpos($output, 'ERROR') !== false) {
        echo "   ❌ FAILED - Contains error\n";
    } else if (strpos($output, 'Gamta Details') !== false) {
        echo "   ✅ SUCCESS - Page renders\n";
    } else {
        echo "   ⚠️  UNKNOWN - Checking output...\n";
        echo "   First 200 chars: " . substr($output, 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
}

try {
    echo "\n3. Testing Gurmu page (ID 15)...\n";
    $_GET['type'] = 'gurmu';
    
    $controller = new HierarchyController();
    ob_start();
    $controller->show(15);
    $output = ob_get_clean();
    
    if (strpos($output, '500') !== false || strpos($output, 'ERROR') !== false) {
        echo "   ❌ FAILED - Contains error\n";
    } else if (strpos($output, 'Gurmu Details') !== false) {
        echo "   ✅ SUCCESS - Page renders\n";
    } else {
        echo "   ⚠️  UNKNOWN - Checking output...\n";
        echo "   First 200 chars: " . substr($output, 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\nTest complete!\n";
