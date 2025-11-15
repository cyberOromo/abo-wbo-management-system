<?php
/**
 * Simple layout test to isolate APP_LANGUAGE issue
 */

// Define application constants
define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', __DIR__ . '/public');
define('STORAGE_PATH', APP_ROOT . '/storage');

// Load Composer autoloader and helpers
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

// Bootstrap the application
try {
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    
    // Check if APP_LANGUAGE constant is defined
    echo "APP_LANGUAGE: " . (defined('APP_LANGUAGE') ? APP_LANGUAGE : 'NOT DEFINED') . "\n";
    
    // Test loading the layout directly
    $title = "Test Dashboard";
    echo "About to load layout...\n";
    
    ob_start();
    include APP_ROOT . '/resources/views/layouts/app.php';
    $layoutOutput = ob_get_clean();
    
    echo "Layout loaded successfully!\n";
    echo "Layout output first 300 chars:\n";
    echo substr($layoutOutput, 0, 300) . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>