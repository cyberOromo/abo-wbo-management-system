<?php
/**
 * Helper Functions for ABO-WBO Management System
 */

if (!function_exists('require_env')) {
    /**
     * Enforce required environment variable
     */
    function require_env($key) {
        if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
            throw new \Exception("Missing required environment variable: $key");
        }
        return $_ENV[$key];
    }
}

if (!function_exists('loadEnv')) {
    /**
     * Load environment variables from .env file
     */
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config($key, $default = null) {
        static $config = null;
        
        if ($config === null) {
            $config = require __DIR__ . '/../config/app.php';
        }
        
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path
     */
    function base_path($path = '') {
        return __DIR__ . '/../' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    /**
     * Get public path
     */
    function public_path($path = '') {
        return __DIR__ . '/../public/' . ltrim($path, '/');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get storage path
     */
    function storage_path($path = '') {
        return __DIR__ . '/../storage/' . ltrim($path, '/');
    }
}

if (!function_exists('view_path')) {
    /**
     * Get view path
     */
    function view_path($path = '') {
        return __DIR__ . '/Views/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset($path) {
        $baseUrl = rtrim(config('app_url'), '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url($path = '') {
        $baseUrl = rtrim(config('app_url'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     */
    function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back
     */
    function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? url();
        redirect($referer);
    }
}

if (!function_exists('session')) {
    /**
     * Get/set session data
     */
    function session($key = null, $value = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($key === null) {
            return $_SESSION;
        }
        
        if ($value !== null) {
            $_SESSION[$key] = $value;
            return $value;
        }
        
        return $_SESSION[$key] ?? null;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate CSRF token
     */
    function csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF field
     */
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old($key, $default = '') {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    /**
     * Set flash message
     */
    function flash($key, $message = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return;
        }
        
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}

if (!function_exists('auth')) {
    /**
     * Get authenticated user
     */
    function auth() {
        return App\Core\Auth::user();
    }
}

if (!function_exists('guest')) {
    /**
     * Check if user is guest
     */
    function guest() {
        return !auth();
    }
}

if (!function_exists('can')) {
    /**
     * Check if user has permission
     */
    function can($permission) {
        $user = auth();
        if (!$user) return false;
        
        // Implementation will be added with RBAC system
        return true;
    }
}

if (!function_exists('trans')) {
    /**
     * Translate text
     */
    function trans($key, $replace = [], $locale = null) {
        static $translations = [];
        
        $locale = $locale ?? session('locale', config('languages.default', 'en'));
        
        if (!isset($translations[$locale])) {
            $file = __DIR__ . "/../lang/{$locale}/common.php";
            $translations[$locale] = file_exists($file) ? require $file : [];
        }
        
        $keys = explode('.', $key);
        $value = $translations[$locale];
        
        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $key; // Return key if translation not found
            }
        }
        
        // Replace placeholders
        foreach ($replace as $search => $replacement) {
            $value = str_replace(":{$search}", $replacement, $value);
        }
        
        return $value;
    }
}

if (!function_exists('__')) {
    /**
     * Alias for trans function
     */
    function __($key, $replace = [], $locale = null) {
        return trans($key, $replace, $locale);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date for display
     */
    function format_date($date, $format = 'Y-m-d H:i:s') {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency
     */
    function format_currency($amount, $currency = 'USD') {
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize input
     */
    function sanitize($input) {
        if (is_array($input)) {
            return array_map('sanitize', $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('validate_email')) {
    /**
     * Validate email
     */
    function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('generate_uuid')) {
    /**
     * Generate UUID v4
     */
    function generate_uuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('log_info')) {
    /**
     * Log info message
     */
    function log_info($message, $context = []) {
        error_log("[INFO] " . $message . " " . json_encode($context));
    }
}

if (!function_exists('log_error')) {
    /**
     * Log error message
     */
    function log_error($message, $context = []) {
        error_log("[ERROR] " . $message . " " . json_encode($context));
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (for debugging)
     */
    function dd(...$vars) {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('old_input')) {
    /**
     * Get old input value from session
     */
    function old_input($key, $default = '') {
        $oldInput = session_get('old_input', []);
        return $oldInput[$key] ?? $default;
    }
}

if (!function_exists('session_has_error')) {
    /**
     * Check if session has error for specific field
     */
    function session_has_error($field) {
        $errors = session_get('errors', []);
        return isset($errors[$field]);
    }
}

if (!function_exists('session_get_error')) {
    /**
     * Get error message for specific field
     */
    function session_get_error($field) {
        $errors = session_get('errors', []);
        return $errors[$field] ?? '';
    }
}

if (!function_exists('auth_check')) {
    /**
     * Check if user is authenticated
     */
    function auth_check() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('auth_user')) {
    /**
     * Get authenticated user data
     */
    function auth_user() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        
        // Map user_type to role for backward compatibility
        if ($user && isset($user['user_type'])) {
            $user['role'] = $user['user_type'] === 'system_admin' ? 'admin' : $user['user_type'];
        }
        
        return $user;
    }
}

// ============================================================================
// USER TYPE & ROLE HELPERS (Member/Executive/Admin Logic)
// ============================================================================

if (!function_exists('is_member')) {
    /**
     * Check if authenticated user is a regular member (no positions assigned)
     */
    function is_member() {
        $user = auth_user();
        return $user && ($user['user_type'] ?? 'member') === 'member';
    }
}

if (!function_exists('is_executive')) {
    /**
     * Check if authenticated user is an executive (has position assignments)
     */
    function is_executive() {
        $user = auth_user();
        return $user && ($user['user_type'] ?? 'member') === 'executive';
    }
}

if (!function_exists('is_system_admin')) {
    /**
     * Check if authenticated user is a system administrator
     */
    function is_system_admin() {
        $user = auth_user();
        return $user && ($user['user_type'] ?? 'member') === 'system_admin';
    }
}

if (!function_exists('has_position')) {
    /**
     * Check if user has any active position assignments
     */
    function has_position($userId = null) {
        $userId = $userId ?? auth_user()['id'] ?? null;
        if (!$userId) return false;
        
        $db = \App\Utils\Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as count FROM user_assignments 
             WHERE user_id = ? AND status = 'active'",
            [$userId]
        );
        
        return ($result['count'] ?? 0) > 0;
    }
}

if (!function_exists('get_user_positions')) {
    /**
     * Get all active positions for a user
     */
    function get_user_positions($userId = null) {
        $userId = $userId ?? auth_user()['id'] ?? null;
        if (!$userId) return [];
        
        $db = \App\Utils\Database::getInstance();
        return $db->fetchAll(
            "SELECT ua.*, p.key_name, p.name_en, p.name_om, p.level_scope as position_level
             FROM user_assignments ua
             JOIN positions p ON ua.position_id = p.id
             WHERE ua.user_id = ? AND ua.status = 'active'
             ORDER BY FIELD(ua.level_scope, 'global', 'godina', 'gamta', 'gurmu')",
            [$userId]
        );
    }
}

if (!function_exists('get_primary_position')) {
    /**
     * Get user's primary (highest-level) position
     */
    function get_primary_position($userId = null) {
        $positions = get_user_positions($userId);
        return $positions[0] ?? null;
    }
}

if (!function_exists('user_type_label')) {
    /**
     * Get human-readable label for user type
     */
    function user_type_label($userType = null) {
        $userType = $userType ?? auth_user()['user_type'] ?? 'member';
        $labels = [
            'member' => 'Member',
            'executive' => 'Executive',
            'system_admin' => 'System Administrator'
        ];
        return $labels[$userType] ?? 'Member';
    }
}

if (!function_exists('can_manage_gurmu')) {
    /**
     * Check if user can manage a specific Gurmu (is executive in that Gurmu or above)
     */
    function can_manage_gurmu($gurmuId) {
        if (is_system_admin()) return true;
        
        $positions = get_user_positions();
        foreach ($positions as $position) {
            // Can manage if position is at this Gurmu
            if ($position['level_scope'] === 'gurmu' && $position['organizational_unit_id'] == $gurmuId) {
                return true;
            }
            
            // Can manage if position is at parent Gamta
            if ($position['level_scope'] === 'gamta') {
                $db = \App\Utils\Database::getInstance();
                $gurmu = $db->fetch("SELECT gamta_id FROM gurmus WHERE id = ?", [$gurmuId]);
                if ($gurmu && $gurmu['gamta_id'] == $position['organizational_unit_id']) {
                    return true;
                }
            }
            
            // Can manage if position is at parent Godina or Global
            if (in_array($position['level_scope'], ['godina', 'global'])) {
                return true; // Higher level executives can manage all Gurmus in their scope
            }
        }
        
        return false;
    }
}

if (!function_exists('session_set')) {
    /**
     * Set session data
     */
    function session_set($key, $value) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[$key] = $value;
        return $value;
    }
}

if (!function_exists('session_get')) {
    /**
     * Get session data
     */
    function session_get($key, $default = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('session_flash')) {
    /**
     * Set flash message in session
     */
    function session_flash($key, $message = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return;
        }
        
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}

if (!function_exists('has_error')) {
    /**
     * Check if field has validation error
     */
    function has_error($field) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $errors = $_SESSION['_flash']['errors'] ?? [];
        return isset($errors[$field]);
    }
}

if (!function_exists('error_message')) {
    /**
     * Display error message for field
     */
    function error_message($field) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $errors = $_SESSION['_flash']['errors'] ?? [];
        if (isset($errors[$field])) {
            return '<div class="invalid-feedback">' . htmlspecialchars($errors[$field]) . '</div>';
        }
        return '';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value after validation error
     */
    function old($field, $default = '') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $oldInput = $_SESSION['_flash']['old_input'] ?? [];
        return $oldInput[$field] ?? $default;
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify CSRF token
     */
    function csrf_verify($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        return hash_equals($sessionToken, $token);
    }
}

if (!function_exists('time_ago')) {
    /**
     * Get human readable time ago
     */
    function time_ago($datetime) {
        if (is_string($datetime)) {
            $time = strtotime($datetime);
        } else {
            $time = $datetime;
        }
        
        $time_difference = time() - $time;
        
        if ($time_difference < 1) {
            return 'less than 1 second ago';
        }
        
        $condition = [
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second'
        ];
        
        foreach ($condition as $secs => $str) {
            $d = $time_difference / $secs;
            
            if ($d >= 1) {
                $t = round($d);
                return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
            }
        }
        
        return 'just now';
    }
}

if (!function_exists('log_activity')) {
    /**
     * Log user activity
     */
    function log_activity($action, $description = '', $metadata = []) {
        if (!auth_check()) {
            return;
        }
        
        try {
            $db = \App\Utils\Database::getInstance();
            $db->insert('activity_logs', [
                'user_id' => auth_user()['id'],
                'action' => $action,
                'description' => $description,
                'metadata' => json_encode($metadata),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            log_error('Failed to log activity: ' . $e->getMessage());
        }
    }
}

if (!function_exists('session_has')) {
    /**
     * Check if session has a key
     */
    function session_has($key) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['_flash'][$key]);
    }
}