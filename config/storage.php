<?php

/**
 * Storage Configuration
 * 
 * Configuration settings for file storage, caching, logging,
 * and backup management in ABO-WBO Management System.
 * 
 * @package Config
 * @version 1.0.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default storage disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application.
    |
    */

    'default' => env('STORAGE_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Storage Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many storage "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path(),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'private',
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => storage_path('uploads'),
            'url' => env('APP_URL') . '/storage/uploads',
            'visibility' => 'private',
        ],

        'documents' => [
            'driver' => 'local',
            'root' => storage_path('uploads/documents'),
            'url' => env('APP_URL') . '/storage/uploads/documents',
            'visibility' => 'private',
            'allowed_extensions' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
            'max_file_size' => 10 * 1024 * 1024, // 10MB
        ],

        'images' => [
            'driver' => 'local',
            'root' => storage_path('uploads/images'),
            'url' => env('APP_URL') . '/storage/uploads/images',
            'visibility' => 'private',
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_file_size' => 5 * 1024 * 1024, // 5MB
        ],

        'receipts' => [
            'driver' => 'local',
            'root' => storage_path('uploads/receipts'),
            'url' => env('APP_URL') . '/storage/uploads/receipts',
            'visibility' => 'private',
            'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_file_size' => 5 * 1024 * 1024, // 5MB
        ],

        'meeting_attachments' => [
            'driver' => 'local',
            'root' => storage_path('uploads/meeting-attachments'),
            'url' => env('APP_URL') . '/storage/uploads/meeting-attachments',
            'visibility' => 'private',
            'allowed_extensions' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'],
            'max_file_size' => 25 * 1024 * 1024, // 25MB
        ],

        'backups' => [
            'driver' => 'local',
            'root' => storage_path('backups'),
            'visibility' => 'private',
        ],

        'temp' => [
            'driver' => 'local',
            'root' => storage_path('temp'),
            'visibility' => 'private',
            'cleanup_after' => 3600, // 1 hour
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure default file upload settings including size limits,
    | allowed types, and security measures.
    |
    */

    'uploads' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB default
        'max_files_per_request' => 10,
        'allowed_extensions' => [
            'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'],
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'],
            'spreadsheets' => ['xls', 'xlsx', 'csv', 'ods'],
            'presentations' => ['ppt', 'pptx', 'odp'],
            'archives' => ['zip', 'rar', '7z', 'tar', 'gz'],
        ],
        'forbidden_extensions' => [
            'php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 
            'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'asp', 'aspx'
        ],
        'scan_for_viruses' => env('SCAN_UPLOADS', false),
        'generate_thumbnails' => true,
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for file operations and system performance.
    |
    */

    'cache' => [
        'driver' => 'file',
        'path' => storage_path('cache'),
        'default_ttl' => 3600, // 1 hour
        'file_cache_ttl' => 86400, // 24 hours
        'query_cache_ttl' => 1800, // 30 minutes
        'template_cache_ttl' => 7200, // 2 hours
        'auto_cleanup' => true,
        'cleanup_interval' => 3600, // 1 hour
        'max_cache_size' => 100 * 1024 * 1024, // 100MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging settings for system monitoring and debugging.
    |
    */

    'logging' => [
        'default_channel' => 'file',
        'path' => storage_path('logs'),
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'max_files' => 30, // Keep 30 days of logs
        'log_level' => env('LOG_LEVEL', 'info'),
        'channels' => [
            'application' => 'app.log',
            'security' => 'security.log',
            'error' => 'error.log',
            'access' => 'access.log',
            'audit' => 'audit.log',
            'performance' => 'performance.log',
        ],
        'rotate_logs' => true,
        'compress_old_logs' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Storage
    |--------------------------------------------------------------------------
    |
    | Configure session storage settings for user authentication.
    |
    */

    'sessions' => [
        'driver' => 'file',
        'path' => storage_path('sessions'),
        'lifetime' => 120, // 2 hours
        'encrypt' => true,
        'cookie_httponly' => true,
        'cookie_secure' => env('SESSION_SECURE_COOKIE', false),
        'same_site' => 'lax',
        'cleanup_probability' => 2,
        'cleanup_divisor' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automated backup settings for data protection.
    |
    */

    'backups' => [
        'enabled' => env('BACKUPS_ENABLED', true),
        'path' => storage_path('backups'),
        'schedule' => [
            'database' => 'daily', // daily, weekly, monthly
            'files' => 'weekly',
            'full_system' => 'monthly',
        ],
        'retention' => [
            'daily' => 7, // Keep 7 daily backups
            'weekly' => 4, // Keep 4 weekly backups
            'monthly' => 12, // Keep 12 monthly backups
        ],
        'compression' => true,
        'encryption' => env('BACKUP_ENCRYPTION', false),
        'exclude_patterns' => [
            '*.tmp',
            '*.log',
            'cache/*',
            'temp/*',
            'sessions/*',
        ],
        'remote_storage' => [
            'enabled' => false,
            'driver' => 's3', // s3, ftp, sftp
            'sync_after_backup' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Maintenance
    |--------------------------------------------------------------------------
    |
    | Configure automatic maintenance tasks for storage optimization.
    |
    */

    'maintenance' => [
        'auto_cleanup' => true,
        'cleanup_schedule' => 'daily',
        'temp_file_lifetime' => 3600, // 1 hour
        'log_cleanup_after' => 30, // 30 days
        'cache_cleanup_size_threshold' => 500 * 1024 * 1024, // 500MB
        'orphaned_file_cleanup' => true,
        'duplicate_file_detection' => false,
        'storage_monitoring' => [
            'enabled' => true,
            'alert_threshold' => 85, // Alert at 85% disk usage
            'critical_threshold' => 95, // Critical at 95% disk usage
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security measures for file operations.
    |
    */

    'security' => [
        'scan_uploads' => env('SCAN_UPLOADS', false),
        'quarantine_suspicious' => true,
        'log_file_access' => true,
        'encrypt_sensitive_files' => true,
        'access_control' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_uploads_per_minute' => 10,
            'max_downloads_per_minute' => 50,
        ],
        'integrity_checking' => [
            'enabled' => true,
            'algorithm' => 'sha256',
            'verify_on_access' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    |
    | Define helper functions for common storage paths.
    |
    */

    'paths' => [
        'storage' => realpath(__DIR__ . '/../storage'),
        'uploads' => realpath(__DIR__ . '/../storage/uploads'),
        'cache' => realpath(__DIR__ . '/../storage/cache'),
        'logs' => realpath(__DIR__ . '/../storage/logs'),
        'sessions' => realpath(__DIR__ . '/../storage/sessions'),
        'backups' => realpath(__DIR__ . '/../storage/backups'),
        'temp' => realpath(__DIR__ . '/../storage/temp'),
    ],
];

/**
 * Helper function to get storage path
 */
if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        $basePath = realpath(__DIR__ . '/../storage');
        return $path ? $basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $basePath;
    }
}