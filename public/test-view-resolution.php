<?php
// Direct controller test
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize basic environment
$_ENV['APP_DEBUG'] = true;

// Test view resolution with correct base path
$basePath = dirname(__DIR__); // Go up from public to project root
$viewPath = str_replace('.', DIRECTORY_SEPARATOR, 'auth.login');
$fullPath = $basePath . '/resources/views/' . $viewPath . '.php';

echo "View path calculation:\n";
echo "1. View name: auth.login\n";
echo "2. Base path: " . $basePath . "\n";
echo "3. After str_replace: " . $viewPath . "\n";
echo "4. Full path: " . $fullPath . "\n";
echo "5. File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

if (file_exists($fullPath)) {
    echo "6. File contents (first 100 chars): " . substr(file_get_contents($fullPath), 0, 100) . "...\n";
}

// Test config loading
try {
    $config = require $basePath . '/config/app.php';
    echo "7. Config loaded: YES\n";
    echo "8. Views path from config: " . $config['paths']['views'] . "\n";
    echo "9. Config views path exists: " . (is_dir($config['paths']['views']) ? 'YES' : 'NO') . "\n";
    
    // Test exact config view path calculation
    $configViewFile = $config['paths']['views'] . DIRECTORY_SEPARATOR . $viewPath . '.php';
    echo "10. Config calculated path: " . $configViewFile . "\n";
    echo "11. Config path exists: " . (file_exists($configViewFile) ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "7. Config error: " . $e->getMessage() . "\n";
}
?>