<?php

namespace App\Utils;

/**
 * CSRF - Cross-Site Request Forgery protection
 * 
 * Provides CSRF token generation, validation, and management
 * with time-based expiration and secure storage.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class CSRF
{
    private const TOKEN_LENGTH = 32;
    private const DEFAULT_EXPIRE_TIME = 3600; // 1 hour
    private const SESSION_KEY = '_csrf_tokens';
    private const HEADER_NAME = 'X-CSRF-Token';
    private const FIELD_NAME = '_token';

    /**
     * Generate CSRF token
     */
    public static function generateToken(string $action = 'default'): string
    {
        Session::init();
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $tokenData = [
            'token' => $token,
            'action' => $action,
            'created_at' => time(),
            'expires_at' => time() + self::DEFAULT_EXPIRE_TIME,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        // Store token in session
        $tokens = Session::get(self::SESSION_KEY, []);
        $tokens[$token] = $tokenData;
        
        // Clean expired tokens
        $tokens = self::cleanExpiredTokens($tokens);
        
        Session::set(self::SESSION_KEY, $tokens);

        return $token;
    }

    /**
     * Verify CSRF token
     */
    public static function verifyToken(string $token, string $action = 'default'): bool
    {
        Session::init();
        
        if (empty($token)) {
            return false;
        }

        $tokens = Session::get(self::SESSION_KEY, []);
        
        if (!isset($tokens[$token])) {
            return false;
        }

        $tokenData = $tokens[$token];

        // Check if token is expired
        if (time() > $tokenData['expires_at']) {
            self::removeToken($token);
            return false;
        }

        // Check action match
        if ($tokenData['action'] !== $action) {
            return false;
        }

        // Check IP address for additional security (optional)
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($tokenData['ip'] !== $currentIp) {
            // IP mismatch - could be legitimate (mobile networks, proxies)
            // Log this for security monitoring but don't fail validation
            error_log("CSRF token IP mismatch: stored={$tokenData['ip']}, current={$currentIp}");
        }

        // Token is valid - remove it to prevent reuse
        self::removeToken($token);

        return true;
    }

    /**
     * Verify token from request
     */
    public static function verifyRequest(string $action = 'default'): bool
    {
        $token = self::getTokenFromRequest();
        return self::verifyToken($token, $action);
    }

    /**
     * Get CSRF token from request
     */
    public static function getTokenFromRequest(): string
    {
        // Check POST data
        if (isset($_POST[self::FIELD_NAME])) {
            return $_POST[self::FIELD_NAME];
        }

        // Check headers
        if (isset($_SERVER['HTTP_' . str_replace('-', '_', strtoupper(self::HEADER_NAME))])) {
            return $_SERVER['HTTP_' . str_replace('-', '_', strtoupper(self::HEADER_NAME))];
        }

        // Check custom header format
        $headers = getallheaders();
        if (isset($headers[self::HEADER_NAME])) {
            return $headers[self::HEADER_NAME];
        }

        return '';
    }

    /**
     * Get current valid token
     */
    public static function getToken(string $action = 'default'): string
    {
        Session::init();
        
        $tokens = Session::get(self::SESSION_KEY, []);
        
        // Find existing valid token for this action
        foreach ($tokens as $tokenData) {
            if ($tokenData['action'] === $action && time() <= $tokenData['expires_at']) {
                return $tokenData['token'];
            }
        }

        // No valid token found, generate new one
        return self::generateToken($action);
    }

    /**
     * Remove specific token
     */
    public static function removeToken(string $token): void
    {
        Session::init();
        
        $tokens = Session::get(self::SESSION_KEY, []);
        unset($tokens[$token]);
        Session::set(self::SESSION_KEY, $tokens);
    }

    /**
     * Remove all tokens for specific action
     */
    public static function removeActionTokens(string $action): void
    {
        Session::init();
        
        $tokens = Session::get(self::SESSION_KEY, []);
        
        foreach ($tokens as $token => $tokenData) {
            if ($tokenData['action'] === $action) {
                unset($tokens[$token]);
            }
        }
        
        Session::set(self::SESSION_KEY, $tokens);
    }

    /**
     * Clear all CSRF tokens
     */
    public static function clearAllTokens(): void
    {
        Session::init();
        Session::set(self::SESSION_KEY, []);
    }

    /**
     * Clean expired tokens
     */
    private static function cleanExpiredTokens(array $tokens): array
    {
        $currentTime = time();
        
        foreach ($tokens as $token => $tokenData) {
            if ($currentTime > $tokenData['expires_at']) {
                unset($tokens[$token]);
            }
        }
        
        return $tokens;
    }

    /**
     * Generate HTML hidden input field
     */
    public static function field(string $action = 'default'): string
    {
        $token = self::getToken($action);
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Generate meta tag for AJAX requests
     */
    public static function metaTag(string $action = 'default'): string
    {
        $token = self::getToken($action);
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }

    /**
     * Get JavaScript code for AJAX setup
     */
    public static function ajaxSetup(string $action = 'default'): string
    {
        $token = self::getToken($action);
        $headerName = self::HEADER_NAME;
        
        return "
        // CSRF Token Setup for AJAX
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    xhr.setRequestHeader('{$headerName}', '{$token}');
                }
            }
        });
        
        // For fetch API
        window.csrfToken = '{$token}';
        window.csrfHeader = '{$headerName}';
        ";
    }

    /**
     * Validate form submission
     */
    public static function validateForm(string $action = 'default'): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // Only validate POST requests
        }

        return self::verifyRequest($action);
    }

    /**
     * Middleware for CSRF protection
     */
    public static function middleware(string $action = 'default'): callable
    {
        return function($request, $next) use ($action) {
            if (!self::verifyRequest($action)) {
                http_response_code(419); // Laravel uses 419 for CSRF token mismatch
                
                if ($request->expectsJson()) {
                    echo json_encode([
                        'error' => 'CSRF token mismatch',
                        'message' => 'The request was rejected due to invalid CSRF token.'
                    ]);
                } else {
                    echo '<h1>419 - CSRF Token Mismatch</h1>';
                    echo '<p>The request was rejected due to invalid CSRF token.</p>';
                }
                
                exit;
            }
            
            return $next($request);
        };
    }

    /**
     * Get token statistics
     */
    public static function getTokenStats(): array
    {
        Session::init();
        
        $tokens = Session::get(self::SESSION_KEY, []);
        $stats = [
            'total' => count($tokens),
            'expired' => 0,
            'valid' => 0,
            'actions' => []
        ];

        $currentTime = time();
        
        foreach ($tokens as $tokenData) {
            if ($currentTime > $tokenData['expires_at']) {
                $stats['expired']++;
            } else {
                $stats['valid']++;
            }
            
            $action = $tokenData['action'];
            if (!isset($stats['actions'][$action])) {
                $stats['actions'][$action] = 0;
            }
            $stats['actions'][$action]++;
        }

        return $stats;
    }

    /**
     * Configure CSRF settings
     */
    public static function configure(array $config): void
    {
        // This would allow runtime configuration
        // For now, constants are used for security
    }

    /**
     * Generate token with custom expiration
     */
    public static function generateTokenWithExpiry(int $expireTime, string $action = 'default'): string
    {
        Session::init();
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $tokenData = [
            'token' => $token,
            'action' => $action,
            'created_at' => time(),
            'expires_at' => time() + $expireTime,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        $tokens = Session::get(self::SESSION_KEY, []);
        $tokens[$token] = $tokenData;
        $tokens = self::cleanExpiredTokens($tokens);
        Session::set(self::SESSION_KEY, $tokens);

        return $token;
    }

    /**
     * Check if token exists and is valid
     */
    public static function isValidToken(string $token, string $action = 'default'): bool
    {
        Session::init();
        
        $tokens = Session::get(self::SESSION_KEY, []);
        
        if (!isset($tokens[$token])) {
            return false;
        }

        $tokenData = $tokens[$token];
        
        return time() <= $tokenData['expires_at'] && 
               $tokenData['action'] === $action;
    }

    /**
     * Get all valid tokens for debugging
     */
    public static function getAllValidTokens(): array
    {
        Session::init();
        
        $tokens = Session::get(self::SESSION_KEY, []);
        $validTokens = [];
        $currentTime = time();
        
        foreach ($tokens as $token => $tokenData) {
            if ($currentTime <= $tokenData['expires_at']) {
                $validTokens[$token] = [
                    'action' => $tokenData['action'],
                    'created_at' => date('Y-m-d H:i:s', $tokenData['created_at']),
                    'expires_at' => date('Y-m-d H:i:s', $tokenData['expires_at']),
                    'time_left' => $tokenData['expires_at'] - $currentTime
                ];
            }
        }
        
        return $validTokens;
    }

    /**
     * Exception for CSRF token validation failures
     */
    public static function throwTokenMismatchException(): void
    {
        throw new \Exception('CSRF token mismatch', 419);
    }

    /**
     * Get field name for forms
     */
    public static function getFieldName(): string
    {
        return self::FIELD_NAME;
    }

    /**
     * Get header name for AJAX
     */
    public static function getHeaderName(): string
    {
        return self::HEADER_NAME;
    }
}