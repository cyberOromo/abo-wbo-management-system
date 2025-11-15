<?php
/**
 * Health Check Script for Docker Container
 * 
 * Performs comprehensive health checks for the ABO-WBO application container
 */

$healthChecks = [
    'database' => false,
    'redis' => false,
    'filesystem' => false,
    'memory' => false
];

// Database health check
try {
    $pdo = new PDO(
        'mysql:host=' . ($_ENV['DB_HOST'] ?? 'mysql') . ';dbname=' . ($_ENV['DB_DATABASE'] ?? 'abo_wbo_production'),
        $_ENV['DB_USERNAME'] ?? 'abo_wbo_user',
        $_ENV['DB_PASSWORD'] ?? ''
    );
    $pdo->query('SELECT 1');
    $healthChecks['database'] = true;
} catch (Exception $e) {
    error_log('Database health check failed: ' . $e->getMessage());
}

// Redis health check
try {
    $redis = new Redis();
    $redis->connect($_ENV['REDIS_HOST'] ?? 'redis', 6379);
    $redis->ping();
    $healthChecks['redis'] = true;
    $redis->close();
} catch (Exception $e) {
    error_log('Redis health check failed: ' . $e->getMessage());
}

// Filesystem health check
$healthChecks['filesystem'] = is_writable('/var/www/html/storage/logs') && 
                             is_writable('/var/www/html/storage/cache') &&
                             is_readable('/var/www/html/public/index.php');

// Memory health check
$memoryUsage = memory_get_usage(true);
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = $memoryLimit === '-1' ? PHP_INT_MAX : 
    (int)$memoryLimit * (strpos($memoryLimit, 'G') ? 1024*1024*1024 : 
    (strpos($memoryLimit, 'M') ? 1024*1024 : 1024));

$healthChecks['memory'] = $memoryUsage < ($memoryLimitBytes * 0.8); // 80% threshold

// Overall health status
$allHealthy = array_reduce($healthChecks, function($carry, $item) {
    return $carry && $item;
}, true);

if ($allHealthy) {
    http_response_code(200);
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('c'),
        'checks' => $healthChecks
    ]);
    exit(0);
} else {
    http_response_code(503);
    echo json_encode([
        'status' => 'unhealthy',
        'timestamp' => date('c'),
        'checks' => $healthChecks
    ]);
    exit(1);
}