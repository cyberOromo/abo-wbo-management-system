<?php

namespace App\Middleware;

use Exception;

class CsrfMiddleware
{
    const TOKEN_LENGTH = 32;
    const TOKEN_LIFETIME = 3600; // 1 hour
    const TOKEN_NAME = '_token';
    const HEADER_NAME = 'X-CSRF-Token';

    private $exemptRoutes = [
        '/api/webhooks/*',
        '/api/callbacks/*'
    ];

    /**
     * Handle CSRF token validation
     */
    public function handle($request, $next)
    {
        try {
            // Skip CSRF validation for safe methods
            if ($this->isSafeMethod($request->getMethod())) {
                return $next($request);
            }

            // Skip CSRF validation for exempt routes
            if ($this->isExemptRoute($request->getPath())) {
                return $next($request);
            }

            // Validate CSRF token
            if (!$this->validateToken($request)) {
                return $this->tokenMismatchResponse();
            }

            // Regenerate token for enhanced security
            $this->regenerateToken();

            return $next($request);

        } catch (Exception $e) {
            error_log("CSRF Middleware error: " . $e->getMessage());
            return $this->serverErrorResponse();
        }
    }

    /**
     * Check if HTTP method is safe (doesn't modify data)
     */
    private function isSafeMethod(string $method): bool
    {
        return in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * Check if route is exempt from CSRF validation
     */
    private function isExemptRoute(string $path): bool
    {
        foreach ($this->exemptRoutes as $pattern) {
            if ($this->matchesPattern($path, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Match path against pattern (supports wildcards)
     */
    private function matchesPattern(string $path, string $pattern): bool
    {
        $regex = str_replace('\*', '.*', preg_quote($pattern, '/'));
        return preg_match('/^' . $regex . '$/', $path);
    }

    /**
     * Validate CSRF token from request
     */
    private function validateToken($request): bool
    {
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return false;
        }

        $sessionToken = $this->getTokenFromSession();
        
        if (!$sessionToken) {
            return false;
        }

        // Check if token has expired
        if ($this->isTokenExpired()) {
            $this->clearToken();
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($sessionToken, $token);
    }

    /**
     * Get CSRF token from request (form data, header, or query string)
     */
    private function getTokenFromRequest($request): ?string
    {
        // Try POST/PUT data first
        $postData = $request->getParsedBody();
        if (isset($postData[self::TOKEN_NAME])) {
            return $postData[self::TOKEN_NAME];
        }

        // Try custom header
        $headers = $request->getHeaders();
        if (isset($headers[self::HEADER_NAME])) {
            return is_array($headers[self::HEADER_NAME]) 
                ? $headers[self::HEADER_NAME][0] 
                : $headers[self::HEADER_NAME];
        }

        // Try X-Requested-With header (for AJAX requests)
        if (isset($headers['X-CSRF-Token'])) {
            return is_array($headers['X-CSRF-Token']) 
                ? $headers['X-CSRF-Token'][0] 
                : $headers['X-CSRF-Token'];
        }

        // Try query string (not recommended but supported)
        $queryParams = $request->getQueryParams();
        if (isset($queryParams[self::TOKEN_NAME])) {
            return $queryParams[self::TOKEN_NAME];
        }

        return null;
    }

    /**
     * Get CSRF token from session
     */
    private function getTokenFromSession(): ?string
    {
        $this->startSecureSession();
        return $_SESSION['csrf_token'] ?? null;
    }

    /**
     * Check if current token has expired
     */
    private function isTokenExpired(): bool
    {
        $this->startSecureSession();
        
        if (!isset($_SESSION['csrf_token_time'])) {
            return true;
        }

        return (time() - $_SESSION['csrf_token_time']) > self::TOKEN_LIFETIME;
    }

    /**
     * Generate new CSRF token
     */
    public function generateToken(): string
    {
        $this->startSecureSession();
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }

    /**
     * Get current CSRF token (generate if doesn't exist)
     */
    public function getToken(): string
    {
        $this->startSecureSession();
        
        if (!isset($_SESSION['csrf_token']) || $this->isTokenExpired()) {
            return $this->generateToken();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Regenerate CSRF token for enhanced security
     */
    private function regenerateToken(): void
    {
        // Only regenerate periodically to avoid issues with multiple forms
        $this->startSecureSession();
        
        if (!isset($_SESSION['csrf_token_last_regen'])) {
            $_SESSION['csrf_token_last_regen'] = time();
            return;
        }
        
        // Regenerate every 15 minutes
        if ((time() - $_SESSION['csrf_token_last_regen']) > 900) {
            $this->generateToken();
            $_SESSION['csrf_token_last_regen'] = time();
        }
    }

    /**
     * Clear CSRF token from session
     */
    private function clearToken(): void
    {
        $this->startSecureSession();
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        unset($_SESSION['csrf_token_last_regen']);
    }

    /**
     * Start secure session with proper configuration
     */
    private function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
        }
    }

    /**
     * Return token mismatch error response
     */
    private function tokenMismatchResponse(): array
    {
        http_response_code(419); // Laravel-style CSRF error code
        
        $message = 'CSRF token mismatch. Please refresh the page and try again.';
        
        // Return JSON response for AJAX requests
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            return [
                'success' => false,
                'error' => $message,
                'code' => 419,
                'csrf_token' => $this->generateToken() // Provide new token
            ];
        }
        
        // Return HTML response for regular requests
        return [
            'success' => false,
            'error' => $message,
            'code' => 419,
            'redirect' => $_SERVER['HTTP_REFERER'] ?? '/'
        ];
    }

    /**
     * Return server error response
     */
    private function serverErrorResponse(): array
    {
        http_response_code(500);
        return [
            'success' => false,
            'error' => 'Internal server error during CSRF validation',
            'code' => 500
        ];
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Generate CSRF token field for forms
     */
    public function tokenField(): string
    {
        $token = $this->getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Generate CSRF meta tag for HTML head
     */
    public function metaTag(): string
    {
        $token = $this->getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Get token for JavaScript usage
     */
    public function getTokenForJs(): array
    {
        return [
            'token' => $this->getToken(),
            'header_name' => self::HEADER_NAME
        ];
    }

    /**
     * Verify token manually (for custom validation)
     */
    public function verifyToken(string $token): bool
    {
        $sessionToken = $this->getTokenFromSession();
        
        if (!$sessionToken || $this->isTokenExpired()) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }

    /**
     * Add exempt route pattern
     */
    public function addExemptRoute(string $pattern): void
    {
        if (!in_array($pattern, $this->exemptRoutes)) {
            $this->exemptRoutes[] = $pattern;
        }
    }

    /**
     * Remove exempt route pattern
     */
    public function removeExemptRoute(string $pattern): void
    {
        $this->exemptRoutes = array_filter($this->exemptRoutes, function($route) use ($pattern) {
            return $route !== $pattern;
        });
    }

    /**
     * Get all exempt routes
     */
    public function getExemptRoutes(): array
    {
        return $this->exemptRoutes;
    }

    /**
     * Create middleware instance with custom configuration
     */
    public static function create(array $config = []): self
    {
        $middleware = new self();
        
        if (isset($config['exempt_routes'])) {
            $middleware->exemptRoutes = array_merge($middleware->exemptRoutes, $config['exempt_routes']);
        }
        
        return $middleware;
    }

    /**
     * Global CSRF protection helper
     */
    public static function protect($request, $next)
    {
        $middleware = new self();
        return $middleware->handle($request, $next);
    }

    /**
     * CSRF token validation for API endpoints
     */
    public function validateApiRequest($request): bool
    {
        // For API endpoints, we might use different validation
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            // Check for API key or JWT token as alternative
            return $this->validateAlternativeAuth($request);
        }
        
        return $this->validateToken($request);
    }

    /**
     * Validate alternative authentication for API
     */
    private function validateAlternativeAuth($request): bool
    {
        $headers = $request->getHeaders();
        
        // Check for API key
        if (isset($headers['X-API-Key'])) {
            return $this->validateApiKey($headers['X-API-Key']);
        }
        
        // Check for JWT token
        if (isset($headers['Authorization'])) {
            return $this->validateJwtToken($headers['Authorization']);
        }
        
        return false;
    }

    /**
     * Validate API key (placeholder implementation)
     */
    private function validateApiKey(string $apiKey): bool
    {
        // Implement API key validation logic
        // This would typically check against a database of valid API keys
        return false; // Placeholder
    }

    /**
     * Validate JWT token (placeholder implementation)
     */
    private function validateJwtToken(string $authHeader): bool
    {
        // Implement JWT token validation logic
        // Extract and validate JWT token from Authorization header
        return false; // Placeholder
    }

    /**
     * Get CSRF configuration for JavaScript
     */
    public function getJsConfig(): string
    {
        $config = [
            'token' => $this->getToken(),
            'header_name' => self::HEADER_NAME,
            'token_name' => self::TOKEN_NAME
        ];
        
        return json_encode($config);
    }

    /**
     * Middleware for file upload protection
     */
    public function protectFileUpload($request, $next)
    {
        // Additional validation for file uploads
        if ($request->hasFiles()) {
            // Validate CSRF for file uploads
            if (!$this->validateToken($request)) {
                return $this->tokenMismatchResponse();
            }
            
            // Additional file upload security checks can be added here
        }
        
        return $this->handle($request, $next);
    }
}