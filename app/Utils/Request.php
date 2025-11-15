<?php

namespace App\Utils;

/**
 * Request - HTTP request handling and data extraction
 * 
 * Handles HTTP request data, headers, files, and provides
 * convenient methods for accessing request information.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class Request
{
    private array $server;
    private array $get;
    private array $post;
    private array $files;
    private array $cookies;
    private array $headers;
    private ?string $body = null;
    private array $parsedBody = [];
    private string $method;
    private string $uri;
    private string $protocol;

    public function __construct()
    {
        $this->server = $_SERVER ?? [];
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->files = $_FILES ?? [];
        $this->cookies = $_COOKIE ?? [];
        
        $this->parseHeaders();
        $this->parseMethod();
        $this->parseUri();
        $this->parseProtocol();
        $this->parseBody();
    }

    /**
     * Parse HTTP headers
     */
    private function parseHeaders(): void
    {
        $this->headers = [];
        
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $header = ucwords(strtolower($header), '-');
                $this->headers[$header] = $value;
            }
        }

        // Add common headers that don't start with HTTP_
        $commonHeaders = [
            'CONTENT_TYPE' => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'AUTHORIZATION' => 'Authorization'
        ];

        foreach ($commonHeaders as $serverKey => $headerName) {
            if (isset($this->server[$serverKey])) {
                $this->headers[$headerName] = $this->server[$serverKey];
            }
        }
    }

    /**
     * Parse HTTP method
     */
    private function parseMethod(): void
    {
        $this->method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        
        // Check for method override
        if ($this->method === 'POST') {
            $override = $this->post('_method') ?? $this->header('X-HTTP-Method-Override');
            if ($override) {
                $this->method = strtoupper($override);
            }
        }
    }

    /**
     * Parse request URI
     */
    private function parseUri(): void
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        $this->uri = $uri;
    }

    /**
     * Parse HTTP protocol
     */
    private function parseProtocol(): void
    {
        $this->protocol = $this->server['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    }

    /**
     * Parse request body
     */
    private function parseBody(): void
    {
        if (in_array($this->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->body = file_get_contents('php://input');
            
            $contentType = $this->header('Content-Type');
            
            if ($contentType) {
                if (strpos($contentType, 'application/json') !== false) {
                    $this->parsedBody = json_decode($this->body, true) ?? [];
                } elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
                    parse_str($this->body, $this->parsedBody);
                }
            }
        }
    }

    /**
     * Get HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get HTTP protocol
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * Check if request method matches
     */
    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /**
     * Check if request is GET
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if request is POST
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Check if request is PUT
     */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    /**
     * Check if request is DELETE
     */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Check if request is PATCH
     */
    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With')) === 'xmlhttprequest';
    }

    /**
     * Check if request expects JSON response
     */
    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return strpos($accept, 'application/json') !== false || $this->isAjax();
    }

    /**
     * Check if request is secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return (
            (isset($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ||
            (isset($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] == 443) ||
            (strtolower($this->header('X-Forwarded-Proto')) === 'https')
        );
    }

    /**
     * Get request scheme
     */
    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Get server host
     */
    public function getHost(): string
    {
        return $this->header('Host') ?? $this->server['SERVER_NAME'] ?? 'localhost';
    }

    /**
     * Get server port
     */
    public function getPort(): int
    {
        return (int) ($this->server['SERVER_PORT'] ?? 80);
    }

    /**
     * Get full URL
     */
    public function getUrl(): string
    {
        $scheme = $this->getScheme();
        $host = $this->getHost();
        $port = $this->getPort();
        $uri = $this->getUri();
        
        $url = $scheme . '://' . $host;
        
        if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
            $url .= ':' . $port;
        }
        
        return $url . $uri;
    }

    /**
     * Get client IP address
     */
    public function getIp(): string
    {
        $ipHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($this->server[$header])) {
                $ip = $this->server[$header];
                
                // Handle comma-separated IPs (for X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Get user agent
     */
    public function getUserAgent(): string
    {
        return $this->header('User-Agent', '');
    }

    /**
     * Get referrer
     */
    public function getReferrer(): ?string
    {
        return $this->header('Referer');
    }

    /**
     * Get query parameter
     */
    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        
        return $this->get[$key] ?? $default;
    }

    /**
     * Get POST parameter
     */
    public function post(string $key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($this->post, $this->parsedBody);
        }
        
        return $this->post[$key] ?? $this->parsedBody[$key] ?? $default;
    }

    /**
     * Get input parameter (from query or body)
     */
    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($this->get, $this->post, $this->parsedBody);
        }
        
        return $this->get[$key] ?? $this->post[$key] ?? $this->parsedBody[$key] ?? $default;
    }

    /**
     * Get multiple input parameters
     */
    public function only(array $keys): array
    {
        $result = [];
        $input = $this->input();
        
        foreach ($keys as $key) {
            if (isset($input[$key])) {
                $result[$key] = $input[$key];
            }
        }
        
        return $result;
    }

    /**
     * Get all input except specified keys
     */
    public function except(array $keys): array
    {
        $input = $this->input();
        
        foreach ($keys as $key) {
            unset($input[$key]);
        }
        
        return $input;
    }

    /**
     * Check if input key exists
     */
    public function has(string $key): bool
    {
        $input = $this->input();
        return isset($input[$key]);
    }

    /**
     * Check if input key exists and is not empty
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    /**
     * Get header value
     */
    public function header(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get cookie value
     */
    public function cookie(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->cookies;
        }
        
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get uploaded file
     */
    public function file(string $key = null)
    {
        if ($key === null) {
            return $this->files;
        }
        
        if (!isset($this->files[$key])) {
            return null;
        }
        
        return new UploadedFile($this->files[$key]);
    }

    /**
     * Check if file was uploaded
     */
    public function hasFile(string $key): bool
    {
        $file = $this->file($key);
        return $file && $file->isValid();
    }

    /**
     * Get raw request body
     */
    public function getBody(): string
    {
        return $this->body ?? '';
    }

    /**
     * Get parsed body (for JSON requests)
     */
    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    /**
     * Get server parameter
     */
    public function server(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->server;
        }
        
        return $this->server[$key] ?? $default;
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return $this->input();
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $sessionToken): bool
    {
        $token = $this->input('_token') ?? $this->header('X-CSRF-Token');
        return $token && hash_equals($sessionToken, $token);
    }

    /**
     * Get bearer token from Authorization header
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Get path info
     */
    public function getPathInfo(): string
    {
        return $this->server['PATH_INFO'] ?? $this->uri;
    }

    /**
     * Create request from globals
     */
    public static function createFromGlobals(): self
    {
        return new self();
    }

    /**
     * Get route parameters (set by router)
     */
    private array $routeParams = [];

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->routeParams;
        }
        
        return $this->routeParams[$key] ?? $default;
    }
}

/**
 * Uploaded file wrapper
 */
class UploadedFile
{
    private array $file;

    public function __construct(array $file)
    {
        $this->file = $file;
    }

    public function getName(): string
    {
        return $this->file['name'] ?? '';
    }

    public function getType(): string
    {
        return $this->file['type'] ?? '';
    }

    public function getSize(): int
    {
        return (int) ($this->file['size'] ?? 0);
    }

    public function getTmpName(): string
    {
        return $this->file['tmp_name'] ?? '';
    }

    public function getError(): int
    {
        return (int) ($this->file['error'] ?? UPLOAD_ERR_NO_FILE);
    }

    public function isValid(): bool
    {
        return $this->getError() === UPLOAD_ERR_OK && is_uploaded_file($this->getTmpName());
    }

    public function move(string $destination): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $directory = dirname($destination);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return move_uploaded_file($this->getTmpName(), $destination);
    }

    public function getContents(): string|false
    {
        if (!$this->isValid()) {
            return false;
        }

        return file_get_contents($this->getTmpName());
    }

    public function getExtension(): string
    {
        return pathinfo($this->getName(), PATHINFO_EXTENSION);
    }

    public function getMimeType(): string
    {
        if (!$this->isValid()) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->getTmpName());
        finfo_close($finfo);

        return $mimeType ?: $this->getType();
    }
}