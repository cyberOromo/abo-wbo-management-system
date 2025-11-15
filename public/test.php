<?php
// Simple web test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current directory: " . getcwd() . "<br>";
echo "File exists (autoload): " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'YES' : 'NO') . "<br>";
echo "File exists (helpers): " . (file_exists(__DIR__ . '/../app/helpers.php') ? 'YES' : 'NO') . "<br>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Autoload: OK<br>";
    
    require_once __DIR__ . '/../app/helpers.php';
    echo "Helpers: OK<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "Test complete!";
?>
