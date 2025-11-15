<?php
namespace App\Core;

use App\Utils\Database;

/**
 * Application Class
 * ABO-WBO Management System - Main Application Bootstrap
 */
class Application
{
    protected static $instance;
    protected $router;
    protected $config = [];
    protected $services = [];
    protected $booted = false;
    
    public function __construct()
    {
        static::$instance = $this;
        $this->router = new Router();
    }
    
    /**
     * Get application instance
     */
    public static function getInstance(): self
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }
    
    /**
     * Bootstrap the application
     */
    public function bootstrap(): void
    {
        if ($this->booted) {
            return;
        }
        
        // Load environment variables
        $this->loadEnvironment();
        
        // Load configuration
        $this->loadConfiguration();
        
        // Load application helpers (before services)
        $this->loadHelpers();
        
        // Initialize services
        $this->initializeServices();
        
        // Register error handlers
        $this->registerErrorHandlers();
        
        // Load routes
        $this->loadRoutes();
        
        $this->booted = true;
    }
    
    /**
     * Load environment variables
     */
    protected function loadEnvironment(): void
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (!file_exists($envFile)) {
            throw new \Exception('.env file not found');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue; // Skip comments
            }
            
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
    
    /**
     * Load configuration files
     */
    protected function loadConfiguration(): void
    {
        $configPath = dirname(__DIR__, 2) . '/config';
        
        if (!is_dir($configPath)) {
            throw new \Exception('Config directory not found');
        }
        
        foreach (glob($configPath . '/*.php') as $configFile) {
            $configName = basename($configFile, '.php');
            $this->config[$configName] = require $configFile;
        }
    }
    
    /**
     * Initialize core services
     */
    protected function initializeServices(): void
    {
        // Initialize database
        try {
            $database = Database::getInstance();
            $this->services['database'] = $database;
        } catch (\Exception $e) {
            log_error('Database initialization failed: ' . $e->getMessage());
            throw $e;
        }
        
        // Initialize session
        $this->initializeSession();
        
        // Initialize other services as needed
        $this->initializeLanguage();
        
        // Register middleware
        $this->registerMiddleware();
    }
    
    /**
     * Register application middleware
     */
    protected function registerMiddleware(): void
    {
        // Register core middleware
        $this->router->registerMiddleware('auth', \App\Middleware\AuthMiddleware::class);
        $this->router->registerMiddleware('admin', \App\Middleware\AdminMiddleware::class);
        $this->router->registerMiddleware('role', \App\Middleware\RoleMiddleware::class);
        $this->router->registerMiddleware('csrf', \App\Middleware\CsrfMiddleware::class);
        $this->router->registerMiddleware('language', \App\Middleware\LanguageMiddleware::class);
        $this->router->registerMiddleware('validation', \App\Middleware\ValidationMiddleware::class);
        
        // System admin middleware for restricted admin routes
        $this->router->registerMiddleware('system_admin', \App\Middleware\AdminMiddleware::class);
    }
    
    /**
     * Initialize session
     */
    protected function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', env('APP_ENV') === 'production' ? 1 : 0);
            ini_set('session.use_strict_mode', 1);
            
            // Start session
            session_start();
            
            // Regenerate session ID periodically
            if (!isset($_SESSION['_session_started'])) {
                session_regenerate_id(true);
                $_SESSION['_session_started'] = time();
            } elseif (time() - $_SESSION['_session_started'] > 1800) { // 30 minutes
                session_regenerate_id(true);
                $_SESSION['_session_started'] = time();
            }
        }
    }
    
    /**
     * Initialize language system
     */
    protected function initializeLanguage(): void
    {
        $defaultLanguage = 'en';
        $sessionLanguage = null;
        
        // Try to get language from session if session is started
        if (session_status() === PHP_SESSION_ACTIVE && function_exists('session_get')) {
            $sessionLanguage = session_get('language');
        }
        
        if ($sessionLanguage && in_array($sessionLanguage, ['en', 'om'])) {
            if (!defined('APP_LANGUAGE')) {
                define('APP_LANGUAGE', $sessionLanguage);
            }
        } else {
            if (!defined('APP_LANGUAGE')) {
                define('APP_LANGUAGE', $defaultLanguage);
            }
        }
    }
    
    /**
     * Load helper functions
     */
    protected function loadHelpers(): void
    {
        $helperFile = dirname(__DIR__) . '/helpers.php';
        if (file_exists($helperFile)) {
            require_once $helperFile;
        }
    }
    
    /**
     * Register error handlers
     */
    protected function registerErrorHandlers(): void
    {
        // Set error reporting
        if (config('app.debug', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
        
        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        
        // Set custom exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Register shutdown function for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    /**
     * Load application routes
     */
    protected function loadRoutes(): void
    {
        $routesFile = dirname(__DIR__, 2) . '/routes/web.php';
        
        if (file_exists($routesFile)) {
            // Pass router to routes file to avoid circular dependency
            $router = $this->router;
            require $routesFile;
        }
        
        // Load API routes if they exist
        $apiRoutesFile = dirname(__DIR__, 2) . '/routes/api.php';
        if (file_exists($apiRoutesFile)) {
            $this->router->group(['prefix' => 'api'], function() use ($apiRoutesFile) {
                require $apiRoutesFile;
            });
        }
    }
    
    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            $this->bootstrap();
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Get router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
    
    /**
     * Get configuration value
     */
    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (is_array($value) && array_key_exists($k, $value)) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    /**
     * Get service instance
     */
    public function service(string $name)
    {
        return $this->services[$name] ?? null;
    }
    
    /**
     * Register service
     */
    public function registerService(string $name, $service): void
    {
        $this->services[$name] = $service;
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }
        
        log_error("PHP Error [{$level}]: {$message}", [
            'file' => $file,
            'line' => $line
        ]);
        
        if (config('app.debug', false)) {
            echo "<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px;'>";
            echo "<strong>Error [{$level}]:</strong> {$message}<br>";
            echo "<strong>File:</strong> {$file}<br>";
            echo "<strong>Line:</strong> {$line}";
            echo "</div>";
        }
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handleException(\Throwable $exception): void
    {
        log_error("Uncaught Exception: " . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        if (!headers_sent()) {
            http_response_code(500);
        }
        
        if (config('app.debug', false)) {
            echo "<div style='background: #fee; border: 2px solid #f00; padding: 20px; margin: 20px;'>";
            echo "<h2>Uncaught Exception</h2>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
            echo "<h3>Stack Trace:</h3>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            echo "</div>";
        } else {
            $errorView = config('app.paths.views') . '/errors/500.php';
            if (file_exists($errorView)) {
                include $errorView;
            } else {
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>Something went wrong. Please try again later.</p>";
            }
        }
    }
    
    /**
     * Handle fatal errors
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            log_error("Fatal Error: {$error['message']}", [
                'file' => $error['file'],
                'line' => $error['line']
            ]);
            
            if (config('app.debug', false)) {
                echo "<div style='background: #fee; border: 2px solid #f00; padding: 20px; margin: 20px;'>";
                echo "<h2>Fatal Error</h2>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . "</p>";
                echo "<p><strong>Line:</strong> " . $error['line'] . "</p>";
                echo "</div>";
            }
        }
    }
    
    /**
     * Get base URL
     */
    public function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['SCRIPT_NAME']);
        
        return $protocol . '://' . $host . rtrim($path, '/');
    }
    
    /**
     * Get current URL
     */
    public function getCurrentUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}