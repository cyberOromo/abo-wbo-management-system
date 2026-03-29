<?php
/**
 * ABO-WBO Management System - Main Application Entry Point
 * Handles all web requests through proper MVC routing system
 */

// Define application constants
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);
define('STORAGE_PATH', APP_ROOT . '/storage');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoloader and helpers
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/helpers.php';

// Load environment variables from .env file
loadEnv(APP_ROOT . '/.env');

try {
    // Initialize and run the MVC application
    $app = \App\Core\Application::getInstance();
    $app->bootstrap();
    $app->run();
    
} catch (\Exception $e) {
    // Handle application errors gracefully
    http_response_code(500);
    
    // Show detailed errors in development
    if (config('app.debug', false)) {
        echo "<div style='font-family: monospace; background: #f8f8f8; padding: 20px; margin: 20px; border: 1px solid #ddd;'>";
        echo "<h2 style='color: #d32f2f;'>Application Error</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
        echo "</div>";
    } else {
        // Production error page
        if (file_exists(__DIR__ . '/error.html')) {
            include __DIR__ . '/error.html';
        } else {
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Something went wrong. Please try again later.</p>";
        }
    }
}

