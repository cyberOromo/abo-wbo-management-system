<?php

namespace App\Utils;

/**
 * Session - Secure session management
 * 
 * Handles session lifecycle, security, and data management
 * with advanced security features and anti-session fixation.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class Session
{
    private static bool $started = false;
    private static array $config = [
        'name' => 'ABO_WBO_SESSION',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
        'regenerate_interval' => 300, // 5 minutes
        'flash_key' => '_flash_messages',
        'old_input_key' => '_old_input',
        'csrf_key' => '_csrf_token'
    ];

    /**
     * Initialize session with security settings
     */
    public static function init(array $config = []): void
    {
        if (self::$started) {
            return;
        }

        // Merge configuration
        self::$config = array_merge(self::$config, $config);

        // Configure session settings
        ini_set('session.name', self::$config['name']);
        ini_set('session.gc_maxlifetime', self::$config['lifetime']);
        ini_set('session.cookie_lifetime', self::$config['lifetime']);
        ini_set('session.cookie_path', self::$config['path']);
        ini_set('session.cookie_domain', self::$config['domain']);
        ini_set('session.cookie_secure', self::$config['secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', self::$config['httponly'] ? '1' : '0');
        ini_set('session.cookie_samesite', self::$config['samesite']);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        self::$started = true;

        // Initialize security measures
        self::initSecurity();
    }

    /**
     * Initialize session security measures
     */
    private static function initSecurity(): void
    {
        // Check if session needs regeneration
        if (self::shouldRegenerateId()) {
            self::regenerateId();
        }

        // Set session fingerprint for hijacking protection
        if (!self::has('_fingerprint')) {
            self::set('_fingerprint', self::generateFingerprint());
        } elseif (self::get('_fingerprint') !== self::generateFingerprint()) {
            // Potential session hijacking detected
            self::destroy();
            throw new \RuntimeException('Session security violation detected');
        }

        // Set session start time
        if (!self::has('_started_at')) {
            self::set('_started_at', time());
        }

        // Check session expiry
        if (self::isExpired()) {
            self::destroy();
            throw new \RuntimeException('Session expired');
        }

        // Update last activity
        self::set('_last_activity', time());
    }

    /**
     * Generate session fingerprint for security
     */
    private static function generateFingerprint(): string
    {
        $factors = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        return hash('sha256', implode('|', $factors));
    }

    /**
     * Check if session ID should be regenerated
     */
    private static function shouldRegenerateId(): bool
    {
        $lastRegeneration = self::get('_last_regeneration', 0);
        return (time() - $lastRegeneration) > self::$config['regenerate_interval'];
    }

    /**
     * Check if session is expired
     */
    private static function isExpired(): bool
    {
        $startTime = self::get('_started_at', time());
        $lastActivity = self::get('_last_activity', time());
        
        return (time() - $startTime) > self::$config['lifetime'] ||
               (time() - $lastActivity) > (self::$config['lifetime'] / 2);
    }

    /**
     * Regenerate session ID
     */
    public static function regenerateId(bool $deleteOldSession = true): bool
    {
        if (!self::$started) {
            self::init();
        }

        $result = session_regenerate_id($deleteOldSession);
        
        if ($result) {
            self::set('_last_regeneration', time());
            self::set('_fingerprint', self::generateFingerprint());
        }

        return $result;
    }

    /**
     * Set session value
     */
    public static function set(string $key, $value): void
    {
        if (!self::$started) {
            self::init();
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$started) {
            self::init();
        }

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        if (!self::$started) {
            self::init();
        }

        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove(string $key): void
    {
        if (!self::$started) {
            self::init();
        }

        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public static function all(): array
    {
        if (!self::$started) {
            self::init();
        }

        return $_SESSION;
    }

    /**
     * Clear all session data
     */
    public static function clear(): void
    {
        if (!self::$started) {
            self::init();
        }

        $_SESSION = [];
    }

    /**
     * Destroy session
     */
    public static function destroy(): bool
    {
        if (!self::$started) {
            return true;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        $result = session_destroy();
        self::$started = false;

        return $result;
    }

    /**
     * Flash message management
     */
    public static function flash(string $key, $value = null)
    {
        if ($value === null) {
            // Get flash message
            $flash = self::get(self::$config['flash_key'], []);
            $message = $flash[$key] ?? null;
            
            // Remove message after reading
            if (isset($flash[$key])) {
                unset($flash[$key]);
                self::set(self::$config['flash_key'], $flash);
            }
            
            return $message;
        } else {
            // Set flash message
            $flash = self::get(self::$config['flash_key'], []);
            $flash[$key] = $value;
            self::set(self::$config['flash_key'], $flash);
        }
    }

    /**
     * Flash success message
     */
    public static function flashSuccess(string $message): void
    {
        self::flash('success', $message);
    }

    /**
     * Flash error message
     */
    public static function flashError(string $message): void
    {
        self::flash('error', $message);
    }

    /**
     * Flash warning message
     */
    public static function flashWarning(string $message): void
    {
        self::flash('warning', $message);
    }

    /**
     * Flash info message
     */
    public static function flashInfo(string $message): void
    {
        self::flash('info', $message);
    }

    /**
     * Get all flash messages
     */
    public static function getFlashMessages(): array
    {
        $flash = self::get(self::$config['flash_key'], []);
        self::set(self::$config['flash_key'], []);
        return $flash;
    }

    /**
     * Store old input data
     */
    public static function flashInput(array $input): void
    {
        self::set(self::$config['old_input_key'], $input);
    }

    /**
     * Get old input value
     */
    public static function old(string $key, $default = null)
    {
        $oldInput = self::get(self::$config['old_input_key'], []);
        return $oldInput[$key] ?? $default;
    }

    /**
     * Clear old input
     */
    public static function clearOldInput(): void
    {
        self::remove(self::$config['old_input_key']);
    }

    /**
     * User authentication helpers
     */
    public static function setUser(array $user): void
    {
        self::set('user_id', $user['id']);
        self::set('user_data', $user);
        self::set('auth_time', time());
    }

    /**
     * Get authenticated user
     */
    public static function getUser(): ?array
    {
        return self::get('user_data');
    }

    /**
     * Get authenticated user ID
     */
    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return self::has('user_id') && self::has('user_data');
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        self::remove('user_id');
        self::remove('user_data');
        self::remove('auth_time');
        self::remove('permissions');
        self::remove('roles');
    }

    /**
     * Set user permissions
     */
    public static function setPermissions(array $permissions): void
    {
        self::set('permissions', $permissions);
    }

    /**
     * Get user permissions
     */
    public static function getPermissions(): array
    {
        return self::get('permissions', []);
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission(string $permission): bool
    {
        $permissions = self::getPermissions();
        return in_array($permission, $permissions);
    }

    /**
     * Set user roles
     */
    public static function setRoles(array $roles): void
    {
        self::set('roles', $roles);
    }

    /**
     * Get user roles
     */
    public static function getRoles(): array
    {
        return self::get('roles', []);
    }

    /**
     * Check if user has role
     */
    public static function hasRole(string $role): bool
    {
        $roles = self::getRoles();
        return in_array($role, $roles);
    }

    /**
     * Session token for CSRF protection
     */
    public static function token(): string
    {
        if (!self::has(self::$config['csrf_key'])) {
            self::set(self::$config['csrf_key'], bin2hex(random_bytes(32)));
        }
        
        return self::get(self::$config['csrf_key']);
    }

    /**
     * Verify CSRF token
     */
    public static function verifyToken(string $token): bool
    {
        $sessionToken = self::get(self::$config['csrf_key']);
        return $sessionToken && hash_equals($sessionToken, $token);
    }

    /**
     * Get session ID
     */
    public static function getId(): string
    {
        if (!self::$started) {
            self::init();
        }
        
        return session_id();
    }

    /**
     * Get session status
     */
    public static function getStatus(): int
    {
        return session_status();
    }

    /**
     * Check if session is active
     */
    public static function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Get session configuration
     */
    public static function getConfig(): array
    {
        return self::$config;
    }

    /**
     * Update session configuration
     */
    public static function updateConfig(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * Get session cookie parameters
     */
    public static function getCookieParams(): array
    {
        return session_get_cookie_params();
    }

    /**
     * Set session save path
     */
    public static function setSavePath(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        session_save_path($path);
    }

    /**
     * Get session save path
     */
    public static function getSavePath(): string
    {
        return session_save_path();
    }

    /**
     * Session garbage collection
     */
    public static function gc(): int
    {
        return session_gc();
    }

    /**
     * Get session information for debugging
     */
    public static function getDebugInfo(): array
    {
        return [
            'id' => self::getId(),
            'status' => self::getStatus(),
            'started' => self::$started,
            'config' => self::$config,
            'cookie_params' => self::getCookieParams(),
            'save_path' => self::getSavePath(),
            'data_count' => count($_SESSION ?? []),
            'last_activity' => self::get('_last_activity'),
            'started_at' => self::get('_started_at'),
            'last_regeneration' => self::get('_last_regeneration')
        ];
    }
}