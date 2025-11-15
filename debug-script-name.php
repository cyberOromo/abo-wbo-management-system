<?php
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "dirname(SCRIPT_NAME): " . dirname($_SERVER['SCRIPT_NAME']) . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";

// Show how the URL is being built
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($script);

echo "Protocol: " . $protocol . "\n";
echo "Host: " . $host . "\n"; 
echo "Script: " . $script . "\n";
echo "BasePath: " . $basePath . "\n";

if ($basePath !== '/') {
    $baseUrl = $protocol . '://' . $host . $basePath;
} else {
    $baseUrl = $protocol . '://' . $host;
}

echo "Final baseUrl: " . $baseUrl . "\n";
echo "Redirect URL would be: " . $baseUrl . "/auth/login\n";
?>