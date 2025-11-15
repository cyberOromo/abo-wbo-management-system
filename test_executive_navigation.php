<?php
/**
 * Test role-based layout rendering for Executive user (Dhangaa)
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
    
    // Test the layout with executive role
    $title = "Executive Dashboard Test";
    
    ob_start();
    include APP_ROOT . '/resources/views/layouts/app.php';
    $layoutOutput = ob_get_clean();
    
    echo "=== Navigation Content Analysis ===\n";
    echo "Contains 'User Management': " . (strpos($layoutOutput, 'User Management') !== false ? 'true' : 'false') . "\n";
    echo "Contains 'Hierarchy Management': " . (strpos($layoutOutput, 'Hierarchy Management') !== false ? 'true' : 'false') . "\n";
    echo "Contains 'Position Management': " . (strpos($layoutOutput, 'Position Management') !== false ? 'true' : 'false') . "\n";
    echo "Contains 'Leadership': " . (strpos($layoutOutput, 'Leadership') !== false ? 'true' : 'false') . "\n";
    echo "Contains 'My Activities': " . (strpos($layoutOutput, 'My Activities') !== false ? 'true' : 'false') . "\n";
    echo "Contains 'Register Members': " . (strpos($layoutOutput, 'Register Members') !== false ? 'true' : 'false') . "\n";
    echo "Contains 'Reports': " . (strpos($layoutOutput, 'Reports') !== false ? 'true' : 'false') . "\n";
    echo "Contains 'Administration': " . (strpos($layoutOutput, 'Administration') !== false ? 'true' : 'false') . "\n";
    
    // Test navigation structure extraction
    echo "\n=== Sidebar Navigation Structure ===\n";
    if (preg_match_all('/<li class="nav-item[^>]*>.*?<a[^>]*href="([^"]*)"[^>]*>.*?<i[^>]*></i>([^<]+)</a>/s', $layoutOutput, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $url = $match[1];
            $text = trim($match[2]);
            if (!empty($text) && strpos($text, 'sidebar-heading') === false) {
                echo "- " . $text . " (" . $url . ")\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>