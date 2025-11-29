<?php
/**
 * ABO-WBO Management System
 * Application Configuration
 */

return [
    'app_name' => $_ENV['APP_NAME'] ?? 'ABO-WBO Management System',
    'app_version' => $_ENV['APP_VERSION'] ?? '1.0.0',
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'timezone' => 'UTC',
    
    // Security Keys
    'app_key' => $_ENV['APP_KEY'] ?? '',
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? '',
    'jwt_expire' => (int)($_ENV['JWT_EXPIRE'] ?? 86400),
    
    // Database Configuration
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'name' => $_ENV['DB_NAME'] ?? 'abo_wbo_db',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci'
    ],
    
    // External Services
    'services' => [
        'zoom' => [
            'api_key' => $_ENV['ZOOM_API_KEY'] ?? '',
            'api_secret' => $_ENV['ZOOM_API_SECRET'] ?? '',
            'webhook_secret' => $_ENV['ZOOM_WEBHOOK_SECRET'] ?? '',
            'api_url' => 'https://api.zoom.us/v2'
        ],
        'paypal' => [
            'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
            'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? '',
            'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox',
            'api_url' => ($_ENV['PAYPAL_MODE'] ?? 'sandbox') === 'live' 
                ? 'https://api.paypal.com' 
                : 'https://api.sandbox.paypal.com'
        ],
        'stripe' => [
            'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '',
            'secret_key' => $_ENV['STRIPE_SECRET_KEY'] ?? '',
            'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
            'api_version' => '2023-10-16'
        ],
        'mail' => [
            'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
            'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
            'username' => $_ENV['MAIL_USERNAME'] ?? '',
            'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@abo-wbo.local',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'ABO-WBO System'
        ],
        'sms' => [
            'twilio_sid' => $_ENV['TWILIO_SID'] ?? '',
            'twilio_token' => $_ENV['TWILIO_TOKEN'] ?? '',
            'twilio_from' => $_ENV['TWILIO_FROM'] ?? ''
        ]
    ],
    
    // File Upload Configuration
    'uploads' => [
        'max_file_size' => (int)($_ENV['MAX_FILE_SIZE'] ?? 10485760), // 10MB
        'allowed_types' => explode(',', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'),
        'upload_path' => __DIR__ . '/../public/uploads/',
        'temp_path' => __DIR__ . '/../storage/temp/'
    ],
    
    // Security Configuration
    'security' => [
        'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
        'rate_limit_enabled' => filter_var($_ENV['RATE_LIMIT_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'rate_limit_max_attempts' => (int)($_ENV['RATE_LIMIT_MAX_ATTEMPTS'] ?? 60),
        'rate_limit_window' => (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 60),
        'password_min_length' => 8,
        'password_require_special' => true,
        'password_require_numbers' => true,
        'password_require_uppercase' => true
    ],
    
    // Cache Configuration
    'cache' => [
        'enabled' => filter_var($_ENV['CACHE_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'lifetime' => (int)($_ENV['CACHE_LIFETIME'] ?? 3600),
        'path' => __DIR__ . '/../storage/cache/'
    ],
    
    // Logging Configuration
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        'max_files' => (int)($_ENV['LOG_MAX_FILES'] ?? 30),
        'path' => __DIR__ . '/../storage/logs/'
    ],
    
    // Language Configuration
    'languages' => [
        'default' => 'en',
        'supported' => ['en', 'om'],
        'fallback' => 'en'
    ],
    
    // Application Paths
    'paths' => [
        'views' => __DIR__ . '/../resources/views',
        'logs' => __DIR__ . '/../storage/logs',
        'uploads' => __DIR__ . '/../public/uploads',
        'temp' => __DIR__ . '/../storage/temp'
    ],
    
    // Pagination
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100
    ],
    
    // Middleware configuration
    'middleware' => [
        'auth' => \App\Middleware\AuthMiddleware::class,
        'admin' => \App\Middleware\AdminMiddleware::class,
    ]
];