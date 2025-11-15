<?php
/**
 * Debug role variables in layout
 */

// Define application constants
define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Simulate Dhangaa's authenticated session
    $_SESSION['user'] = [
        'id' => 33,
        'first_name' => 'Dhangaa',
        'last_name' => 'Stream',
        'email' => 'dhangaatorbanii@gmail.com',
        'role' => 'executive'
    ];
    $_SESSION['user_id'] = 33;
    $_SESSION['is_authenticated'] = true;
    
    // Replicate the role check logic from the layout
    $user = auth_user(); 
    $userRole = $user['role'] ?? 'member';
    $isAdmin = $userRole === 'admin';
    $isExecutive = $userRole === 'executive';
    $isMember = $userRole === 'member';
    
    echo "=== Role Variables Debug ===\n";
    echo "User data: " . print_r($user, true) . "\n";
    echo "User Role: " . $userRole . "\n";
    echo "isAdmin: " . ($isAdmin ? 'true' : 'false') . "\n";
    echo "isExecutive: " . ($isExecutive ? 'true' : 'false') . "\n";
    echo "isMember: " . ($isMember ? 'true' : 'false') . "\n";
    echo "Executive OR Admin: " . (($isExecutive || $isAdmin) ? 'true' : 'false') . "\n";
    
    // Test the conditional sections
    echo "\n=== Navigation Sections ===\n";
    echo "Should show Leadership section: " . (($isExecutive || $isAdmin) ? 'YES' : 'NO') . "\n";
    echo "Should show Administration section: " . ($isAdmin ? 'YES' : 'NO') . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>