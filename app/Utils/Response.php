<?php

namespace App\Utils;

/**
 * Response - HTTP response handling and formatting
 * 
 * Handles HTTP response generation, headers, status codes,
 * and various response formats (JSON, HTML, redirects).
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';
    private array $cookies = [];
    private bool $sent = false;

    /**
     * HTTP status codes
     */
    private const STATUS_CODES = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable'
    ];

    /**
     * Set HTTP status code
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set response header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set multiple headers
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    /**
     * Get response header
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Remove header
     */
    public function removeHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * Check if header exists
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * Set response body
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get response body
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Append to response body
     */
    public function appendBody(string $content): self
    {
        $this->body .= $content;
        return $this;
    }

    /**
     * Send JSON response
     */
    public function json($data, int $statusCode = 200, array $headers = []): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->setHeaders($headers);
        
        $this->setBody(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $this->send();
    }

    /**
     * Send HTML response
     */
    public function html(string $content, int $statusCode = 200, array $headers = []): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->setHeaders($headers);
        $this->setBody($content);
        
        return $this->send();
    }

    /**
     * Send plain text response
     */
    public function text(string $content, int $statusCode = 200, array $headers = []): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $this->setHeaders($headers);
        $this->setBody($content);
        
        return $this->send();
    }

    /**
     * Send XML response
     */
    public function xml(string $content, int $statusCode = 200, array $headers = []): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->setHeaders($headers);
        $this->setBody($content);
        
        return $this->send();
    }

    /**
     * Send file download response
     */
    public function download(string $filePath, ?string $filename = null, array $headers = []): self
    {
        if (!file_exists($filePath)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        $filename = $filename ?? basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->setStatusCode(200);
        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->setHeader('Content-Length', (string) $fileSize);
        $this->setHeader('Cache-Control', 'must-revalidate');
        $this->setHeaders($headers);

        $this->setBody(file_get_contents($filePath));

        return $this->send();
    }

    /**
     * Send file inline response
     */
    public function file(string $filePath, ?string $filename = null, array $headers = []): self
    {
        if (!file_exists($filePath)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        $filename = $filename ?? basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->setStatusCode(200);
        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
        $this->setHeader('Content-Length', (string) $fileSize);
        $this->setHeaders($headers);

        $this->setBody(file_get_contents($filePath));

        return $this->send();
    }

    /**
     * Send redirect response
     */
    public function redirect(string $url, int $statusCode = 302, array $headers = []): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->setHeaders($headers);
        
        return $this->send();
    }

    /**
     * Send redirect back response
     */
    public function back(string $fallback = '/', array $headers = []): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        return $this->redirect($referer, 302, $headers);
    }

    /**
     * Set cookie
     */
    public function setCookie(
        string $name,
        string $value = '',
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
            'sameSite' => $sameSite
        ];
        
        return $this;
    }

    /**
     * Delete cookie
     */
    public function deleteCookie(string $name, string $path = '/', string $domain = ''): self
    {
        return $this->setCookie($name, '', time() - 3600, $path, $domain);
    }

    /**
     * Send success response
     */
    public function success($data = null, string $message = 'Success', int $statusCode = 200): self
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->json($response, $statusCode);
    }

    /**
     * Send error response
     */
    public function error(string $message = 'Error', int $statusCode = 400, $errors = null): self
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->json($response, $statusCode);
    }

    /**
     * Send validation error response
     */
    public function validationError(array $errors, string $message = 'Validation failed'): self
    {
        return $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Send unauthorized response
     */
    public function unauthorized(string $message = 'Unauthorized'): self
    {
        return $this->json([
            'success' => false,
            'message' => $message
        ], 401);
    }

    /**
     * Send forbidden response
     */
    public function forbidden(string $message = 'Forbidden'): self
    {
        return $this->json([
            'success' => false,
            'message' => $message
        ], 403);
    }

    /**
     * Send not found response
     */
    public function notFound(string $message = 'Not found'): self
    {
        return $this->json([
            'success' => false,
            'message' => $message
        ], 404);
    }

    /**
     * Send server error response
     */
    public function serverError(string $message = 'Internal server error'): self
    {
        return $this->json([
            'success' => false,
            'message' => $message
        ], 500);
    }

    /**
     * Send paginated response
     */
    public function paginated(array $data, array $meta, string $message = 'Success'): self
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta
        ]);
    }

    /**
     * Set CORS headers
     */
    public function cors(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Csrf-Token'],
        bool $allowCredentials = true,
        int $maxAge = 86400
    ): self {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $this->setHeader('Access-Control-Allow-Origin', in_array('*', $allowedOrigins) ? '*' : $origin);
        }

        $this->setHeader('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
        $this->setHeader('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        $this->setHeader('Access-Control-Max-Age', (string) $maxAge);

        if ($allowCredentials && !in_array('*', $allowedOrigins)) {
            $this->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $this;
    }

    /**
     * Set cache headers
     */
    public function cache(int $maxAge = 3600, bool $public = true): self
    {
        $this->setHeader('Cache-Control', ($public ? 'public' : 'private') . ', max-age=' . $maxAge);
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
        
        return $this;
    }

    /**
     * Set no-cache headers
     */
    public function noCache(): self
    {
        $this->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('Expires', '0');
        
        return $this;
    }

    /**
     * Send the response
     */
    public function send(): self
    {
        if ($this->sent) {
            return $this;
        }

        // Send status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        // Send cookies
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                [
                    'expires' => $cookie['expires'],
                    'path' => $cookie['path'],
                    'domain' => $cookie['domain'],
                    'secure' => $cookie['secure'],
                    'httponly' => $cookie['httpOnly'],
                    'samesite' => $cookie['sameSite']
                ]
            );
        }

        // Send body
        echo $this->body;

        $this->sent = true;

        return $this;
    }

    /**
     * Check if response has been sent
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * Get status text for code
     */
    public function getStatusText(): string
    {
        return self::STATUS_CODES[$this->statusCode] ?? 'Unknown';
    }

    /**
     * Create response with view
     */
    public function view(string $view, array $data = [], int $statusCode = 200): self
    {
        $viewContent = $this->renderView($view, $data);
        return $this->html($viewContent, $statusCode);
    }

    /**
     * Render view file
     */
    private function renderView(string $view, array $data = []): string
    {
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$view}");
        }

        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        include $viewPath;
        
        // Get content and clean buffer
        $content = ob_get_clean();
        
        return $content;
    }

    /**
     * Get view file path
     */
    private function getViewPath(string $view): string
    {
        $viewPath = str_replace('.', '/', $view) . '.php';
        return __DIR__ . '/../../resources/views/' . $viewPath;
    }

    /**
     * Create new response instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Output buffering methods
     */
    public function startBuffer(): void
    {
        ob_start();
    }

    public function getBuffer(): string
    {
        return ob_get_contents() ?: '';
    }

    public function cleanBuffer(): void
    {
        ob_clean();
    }

    public function endBuffer(): string
    {
        return ob_get_clean() ?: '';
    }
}