<?php

namespace App\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Logger - Application logging utility
 * 
 * Provides comprehensive logging with multiple handlers,
 * formatters, and log levels with performance monitoring.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class Logger
{
    private MonologLogger $logger;
    private array $config = [
        'name' => 'ABO-WBO-System',
        'level' => 'DEBUG', // DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
        'timezone' => 'Asia/Kathmandu',
        'handlers' => [
            'file' => [
                'enabled' => true,
                'path' => '/storage/logs/application.log',
                'level' => 'DEBUG',
                'max_files' => 30, // For rotating handler
                'formatter' => 'line' // line, json
            ],
            'error_file' => [
                'enabled' => true,
                'path' => '/storage/logs/error.log',
                'level' => 'ERROR',
                'formatter' => 'line'
            ],
            'system' => [
                'enabled' => false, // PHP error_log()
                'level' => 'ERROR'
            ],
            'firephp' => [
                'enabled' => false, // Browser console (FirePHP)
                'level' => 'DEBUG'
            ],
            'chromephp' => [
                'enabled' => false, // Chrome console
                'level' => 'DEBUG'
            ]
        ],
        'processors' => [
            'uid' => true,        // Unique request ID
            'web' => true,        // Web request info
            'introspection' => false // Code location info
        ],
        'performance' => [
            'enabled' => true,
            'slow_query_threshold' => 1.0, // seconds
            'memory_threshold' => 50 * 1024 * 1024 // 50MB
        ]
    ];

    private static ?Logger $instance = null;
    private array $contexts = [];
    private float $requestStartTime;
    private array $timers = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge_recursive($this->config, $config);
        $this->requestStartTime = microtime(true);
        $this->initializeLogger();
    }

    /**
     * Initialize Monolog logger
     */
    private function initializeLogger(): void
    {
        $this->logger = new MonologLogger($this->config['name']);

        // Set timezone
        if ($this->config['timezone']) {
            $this->logger->setTimezone(new \DateTimeZone($this->config['timezone']));
        }

        // Add handlers
        $this->setupHandlers();

        // Add processors
        $this->setupProcessors();
    }

    /**
     * Setup log handlers
     */
    private function setupHandlers(): void
    {
        $handlers = $this->config['handlers'];

        // File handler
        if ($handlers['file']['enabled']) {
            $logPath = $_SERVER['DOCUMENT_ROOT'] . $handlers['file']['path'];
            $this->ensureLogDirectory(dirname($logPath));

            $handler = new RotatingFileHandler(
                $logPath,
                $handlers['file']['max_files'] ?? 30,
                $this->getLogLevel($handlers['file']['level'])
            );

            $formatter = $this->getFormatter($handlers['file']['formatter']);
            $handler->setFormatter($formatter);
            $this->logger->pushHandler($handler);
        }

        // Error file handler
        if ($handlers['error_file']['enabled']) {
            $errorLogPath = $_SERVER['DOCUMENT_ROOT'] . $handlers['error_file']['path'];
            $this->ensureLogDirectory(dirname($errorLogPath));

            $handler = new StreamHandler(
                $errorLogPath,
                $this->getLogLevel($handlers['error_file']['level'])
            );

            $formatter = $this->getFormatter($handlers['error_file']['formatter']);
            $handler->setFormatter($formatter);
            $this->logger->pushHandler($handler);
        }

        // System error log handler
        if ($handlers['system']['enabled']) {
            $handler = new ErrorLogHandler(
                ErrorLogHandler::OPERATING_SYSTEM,
                $this->getLogLevel($handlers['system']['level'])
            );
            $this->logger->pushHandler($handler);
        }

        // FirePHP handler
        if ($handlers['firephp']['enabled']) {
            $handler = new FirePHPHandler($this->getLogLevel($handlers['firephp']['level']));
            $this->logger->pushHandler($handler);
        }

        // ChromePHP handler
        if ($handlers['chromephp']['enabled']) {
            $handler = new ChromePHPHandler($this->getLogLevel($handlers['chromephp']['level']));
            $this->logger->pushHandler($handler);
        }
    }

    /**
     * Setup log processors
     */
    private function setupProcessors(): void
    {
        $processors = $this->config['processors'];

        if ($processors['uid']) {
            $this->logger->pushProcessor(new UidProcessor());
        }

        if ($processors['web']) {
            $this->logger->pushProcessor(new WebProcessor());
        }

        if ($processors['introspection']) {
            $this->logger->pushProcessor(new IntrospectionProcessor());
        }

        // Custom processor for additional context
        $this->logger->pushProcessor(function ($record) {
            $record['extra']['memory_usage'] = memory_get_usage(true);
            $record['extra']['memory_peak'] = memory_get_peak_usage(true);
            $record['extra']['request_time'] = microtime(true) - $this->requestStartTime;
            
            // Add custom contexts
            if (!empty($this->contexts)) {
                $record['extra']['contexts'] = $this->contexts;
            }

            return $record;
        });
    }

    /**
     * Get log level constant
     */
    private function getLogLevel(string $level): int
    {
        return match (strtoupper($level)) {
            'DEBUG' => MonologLogger::DEBUG,
            'INFO' => MonologLogger::INFO,
            'NOTICE' => MonologLogger::NOTICE,
            'WARNING' => MonologLogger::WARNING,
            'ERROR' => MonologLogger::ERROR,
            'CRITICAL' => MonologLogger::CRITICAL,
            'ALERT' => MonologLogger::ALERT,
            'EMERGENCY' => MonologLogger::EMERGENCY,
            default => MonologLogger::DEBUG
        };
    }

    /**
     * Get formatter instance
     */
    private function getFormatter(string $type): object
    {
        return match ($type) {
            'json' => new JsonFormatter(),
            'line' => new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true,
                true
            ),
            default => new LineFormatter()
        };
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Log notice message
     */
    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Log critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * Log alert message
     */
    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * Log emergency message
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * Log exception
     */
    public function exception(\Throwable $exception, array $context = []): void
    {
        $context = array_merge($context, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        $this->error('Exception caught', $context);
    }

    /**
     * Log database query
     */
    public function query(string $sql, array $params = [], float $executionTime = 0): void
    {
        $context = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime . 's'
        ];

        $level = 'debug';
        if ($this->config['performance']['enabled'] && 
            $executionTime > $this->config['performance']['slow_query_threshold']) {
            $level = 'warning';
            $context['slow_query'] = true;
        }

        $this->$level('Database query executed', $context);
    }

    /**
     * Log HTTP request
     */
    public function httpRequest(
        string $method,
        string $url,
        int $statusCode,
        float $responseTime,
        array $context = []
    ): void {
        $context = array_merge($context, [
            'method' => $method,
            'url' => $url,
            'status_code' => $statusCode,
            'response_time' => $responseTime . 's'
        ]);

        $level = $statusCode >= 400 ? 'error' : 'info';
        $this->$level('HTTP request processed', $context);
    }

    /**
     * Log user action
     */
    public function userAction(
        int $userId,
        string $action,
        array $details = []
    ): void {
        $context = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $this->info('User action performed', $context);
    }

    /**
     * Log security event
     */
    public function security(string $event, array $context = []): void
    {
        $context = array_merge($context, [
            'security_event' => $event,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $this->warning('Security event detected', $context);
    }

    /**
     * Log performance metrics
     */
    public function performance(string $operation, float $duration, array $context = []): void
    {
        $context = array_merge($context, [
            'operation' => $operation,
            'duration' => $duration . 's',
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true))
        ]);

        $level = 'debug';
        if ($this->config['performance']['enabled']) {
            $memoryUsage = memory_get_usage(true);
            if ($memoryUsage > $this->config['performance']['memory_threshold']) {
                $level = 'warning';
                $context['high_memory_usage'] = true;
            }
        }

        $this->$level('Performance metric', $context);
    }

    /**
     * Start performance timer
     */
    public function startTimer(string $name): void
    {
        $this->timers[$name] = microtime(true);
    }

    /**
     * Stop performance timer and log
     */
    public function stopTimer(string $name, array $context = []): float
    {
        if (!isset($this->timers[$name])) {
            $this->warning("Timer '{$name}' was not started");
            return 0.0;
        }

        $duration = microtime(true) - $this->timers[$name];
        unset($this->timers[$name]);

        $this->performance($name, $duration, $context);
        return $duration;
    }

    /**
     * Add context for all subsequent logs
     */
    public function addContext(string $key, mixed $value): void
    {
        $this->contexts[$key] = $value;
    }

    /**
     * Remove context
     */
    public function removeContext(string $key): void
    {
        unset($this->contexts[$key]);
    }

    /**
     * Clear all contexts
     */
    public function clearContexts(): void
    {
        $this->contexts = [];
    }

    /**
     * Log with custom level
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log($this->getLogLevel($level), $message, $context);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get log statistics
     */
    public function getStats(): array
    {
        $logPath = $_SERVER['DOCUMENT_ROOT'] . $this->config['handlers']['file']['path'];
        $errorLogPath = $_SERVER['DOCUMENT_ROOT'] . $this->config['handlers']['error_file']['path'];

        $stats = [
            'request_time' => microtime(true) - $this->requestStartTime,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'active_timers' => count($this->timers),
            'contexts_count' => count($this->contexts)
        ];

        // Add file sizes if files exist
        if (file_exists($logPath)) {
            $stats['log_file_size'] = filesize($logPath);
        }

        if (file_exists($errorLogPath)) {
            $stats['error_file_size'] = filesize($errorLogPath);
        }

        return $stats;
    }

    /**
     * Rotate log files manually
     */
    public function rotate(): bool
    {
        try {
            $logPath = $_SERVER['DOCUMENT_ROOT'] . $this->config['handlers']['file']['path'];
            
            if (file_exists($logPath)) {
                $rotatedPath = $logPath . '.' . date('Y-m-d-H-i-s');
                rename($logPath, $rotatedPath);
                
                // Compress old log
                if (function_exists('gzencode')) {
                    $content = file_get_contents($rotatedPath);
                    file_put_contents($rotatedPath . '.gz', gzencode($content));
                    unlink($rotatedPath);
                }
                
                $this->info('Log file rotated', ['rotated_to' => $rotatedPath]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->error('Failed to rotate log file', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clear log files
     */
    public function clearLogs(): bool
    {
        try {
            $logPaths = [
                $_SERVER['DOCUMENT_ROOT'] . $this->config['handlers']['file']['path'],
                $_SERVER['DOCUMENT_ROOT'] . $this->config['handlers']['error_file']['path']
            ];

            foreach ($logPaths as $path) {
                if (file_exists($path)) {
                    file_put_contents($path, '');
                }
            }

            $this->info('Log files cleared');
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to clear log files', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        
        return self::$instance;
    }

    /**
     * Static convenience methods
     */
    public static function logDebug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }

    public static function logInfo(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }

    public static function logWarning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }

    public static function logError(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function logException(\Throwable $exception, array $context = []): void
    {
        self::getInstance()->exception($exception, $context);
    }

    /**
     * Update configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge_recursive($this->config, $config);
        $this->initializeLogger();
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Static factory method
     */
    public static function create(array $config = []): self
    {
        return new self($config);
    }
}