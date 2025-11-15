<?php
namespace App\Core;

use App\Utils\Database;

/**
 * Enhanced Base Controller Class
 * ABO-WBO Management System - Advanced Controller with Request/Response Management
 */
class EnhancedController
{
    protected $data = [];
    protected $middleware = [];
    protected $layout = 'app';
    protected $request;
    protected $response;
    protected $routeParameters = [];
    
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        
        // Set common view data
        $this->setCommonData();
        
        // Run controller middleware
        $this->runMiddleware();
    }
    
    /**
     * Set common data available to all views
     */
    protected function setCommonData(): void
    {
        $this->data['app_name'] = config('app.name', 'ABO-WBO Management System');
        $this->data['app_url'] = config('app.url', '/');
        $this->data['user'] = session_get('user');
        $this->data['csrf_token'] = $this->generateCsrfToken();
        $this->data['flash'] = session_get('flash', []);
        $this->data['errors'] = session_get('errors', []);
        $this->data['old'] = session_get('old', []);
        
        // Clear flash data after setting
        session_forget(['flash', 'errors', 'old']);
    }
    
    /**
     * Run controller middleware
     */
    protected function runMiddleware(): void
    {
        foreach ($this->middleware as $middlewareName) {
            $middlewareClass = $this->resolveMiddleware($middlewareName);
            
            if ($middlewareClass && class_exists($middlewareClass)) {
                $instance = new $middlewareClass();
                
                if (method_exists($instance, 'handle')) {
                    $result = $instance->handle();
                    
                    if ($result === false) {
                        return; // Stop execution if middleware returns false
                    }
                }
            }
        }
    }
    
    /**
     * Resolve middleware class
     */
    protected function resolveMiddleware(string $middleware): ?string
    {
        if (class_exists($middleware)) {
            return $middleware;
        }
        
        $middlewareClass = "App\\Middleware\\{$middleware}";
        if (class_exists($middlewareClass)) {
            return $middlewareClass;
        }
        
        $middlewareClass = "App\\Middleware\\{$middleware}Middleware";
        if (class_exists($middlewareClass)) {
            return $middlewareClass;
        }
        
        return null;
    }
    
    /**
     * Set route parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->routeParameters = $parameters;
    }
    
    /**
     * Get route parameter
     */
    protected function parameter(string $name, $default = null)
    {
        return $this->routeParameters[$name] ?? $default;
    }
    
    /**
     * Render view with layout
     */
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        $layout = $layout ?? $this->layout;
        $data = array_merge($this->data, $data);
        
        $viewContent = $this->renderView($view, $data);
        
        if ($layout) {
            $this->renderLayout($layout, array_merge($data, ['content' => $viewContent]));
        } else {
            echo $viewContent;
        }
    }
    
    /**
     * Render view without layout
     */
    protected function partial(string $view, array $data = []): string
    {
        return $this->renderView($view, array_merge($this->data, $data));
    }
    
    /**
     * Render view file
     */
    protected function renderView(string $view, array $data = []): string
    {
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$view} (Path: {$viewPath})");
        }
        
        // Start output buffering
        ob_start();
        
        // Extract data for view
        extract($data);
        
        try {
            include $viewPath;
            return ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
    
    /**
     * Render layout
     */
    protected function renderLayout(string $layout, array $data = []): void
    {
        $layoutPath = $this->getLayoutPath($layout);
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout} (Path: {$layoutPath})");
        }
        
        // Extract data for layout
        extract($data);
        
        include $layoutPath;
    }
    
    /**
     * Get view file path
     */
    protected function getViewPath(string $view): string
    {
        $viewsPath = config('app.paths.views', dirname(__DIR__, 2) . '/resources/views');
        return $viewsPath . '/' . str_replace('.', '/', $view) . '.php';
    }
    
    /**
     * Get layout file path
     */
    protected function getLayoutPath(string $layout): string
    {
        $layoutsPath = config('app.paths.views', dirname(__DIR__, 2) . '/resources/views') . '/layouts';
        return $layoutsPath . '/' . $layout . '.php';
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $status = 302): void
    {
        $this->response->redirect($url, $status);
    }
    
    /**
     * Redirect back to previous page
     */
    protected function back(): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referrer);
    }
    
    /**
     * Redirect with flash message
     */
    protected function redirectWith(string $url, array $data = []): void
    {
        foreach ($data as $key => $value) {
            session_set("flash.{$key}", $value);
        }
        
        $this->redirect($url);
    }
    
    /**
     * Redirect with input data
     */
    protected function redirectWithInput(string $url): void
    {
        session_set('old', $this->request->all());
        $this->redirect($url);
    }
    
    /**
     * Redirect with errors
     */
    protected function redirectWithErrors(string $url, array $errors): void
    {
        session_set('errors', $errors);
        session_set('old', $this->request->all());
        $this->redirect($url);
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): void
    {
        $this->response->json($data, $status);
    }
    
    /**
     * Return success JSON response
     */
    protected function success(string $message = 'Success', array $data = []): void
    {
        $this->json(array_merge([
            'success' => true,
            'message' => $message
        ], $data));
    }
    
    /**
     * Return error JSON response
     */
    protected function error(string $message = 'Error', array $errors = [], int $status = 400): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
    
    /**
     * Validate request data
     */
    protected function validate(array $rules, array $messages = []): array
    {
        $validator = new Validator($this->request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            if ($this->request->expectsJson()) {
                $this->error('Validation failed', $validator->errors(), 422);
            } else {
                $this->redirectWithErrors($this->request->url(), $validator->errors());
            }
        }
        
        return $validator->validated();
    }
    
    /**
     * Get request input
     */
    protected function input(string $key = null, $default = null)
    {
        return $this->request->input($key, $default);
    }
    
    /**
     * Get all request input
     */
    protected function all(): array
    {
        return $this->request->all();
    }
    
    /**
     * Get only specified input fields
     */
    protected function only(array $keys): array
    {
        return $this->request->only($keys);
    }
    
    /**
     * Get all except specified input fields
     */
    protected function except(array $keys): array
    {
        return $this->request->except($keys);
    }
    
    /**
     * Check if request has input
     */
    protected function has(string $key): bool
    {
        return $this->request->has($key);
    }
    
    /**
     * Get file from request
     */
    protected function file(string $key)
    {
        return $this->request->file($key);
    }
    
    /**
     * Check if request has file
     */
    protected function hasFile(string $key): bool
    {
        return $this->request->hasFile($key);
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken(): string
    {
        if (!session_has('csrf_token')) {
            session_set('csrf_token', bin2hex(random_bytes(32)));
        }
        
        return session_get('csrf_token');
    }
    
    /**
     * Verify CSRF token
     */
    protected function verifyCsrfToken(string $token): bool
    {
        return hash_equals(session_get('csrf_token', ''), $token);
    }
    
    /**
     * Set flash message
     */
    protected function flash(string $key, $value): void
    {
        session_set("flash.{$key}", $value);
    }
    
    /**
     * Get flash message
     */
    protected function getFlash(string $key, $default = null)
    {
        $value = session_get("flash.{$key}", $default);
        session_forget("flash.{$key}");
        return $value;
    }
    
    /**
     * Check user authentication
     */
    protected function auth(): ?array
    {
        return session_get('user');
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return !empty(session_get('user'));
    }
    
    /**
     * Get authenticated user ID
     */
    protected function userId(): ?int
    {
        $user = $this->auth();
        return $user['id'] ?? null;
    }
    
    /**
     * Check if user has permission
     */
    protected function can(string $permission): bool
    {
        $user = $this->auth();
        if (!$user) {
            return false;
        }
        
        // Check user permissions
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }
    
    /**
     * Abort with error code
     */
    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        
        $errorView = config('app.paths.views') . "/errors/{$code}.php";
        if (file_exists($errorView)) {
            $this->view("errors.{$code}", ['message' => $message], null);
        } else {
            echo "<h1>{$code}</h1>";
            if ($message) {
                echo "<p>" . htmlspecialchars($message) . "</p>";
            }
        }
        
        exit;
    }
    
    /**
     * Set response header
     */
    protected function header(string $name, string $value): void
    {
        $this->response->header($name, $value);
    }
    
    /**
     * Set multiple headers
     */
    protected function headers(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }
    }
    
    /**
     * Log information
     */
    protected function log(string $message, array $context = []): void
    {
        if (function_exists('log_info')) {
            log_info($message, $context);
        }
    }
    
    /**
     * Log error
     */
    protected function logError(string $message, array $context = []): void
    {
        if (function_exists('log_error')) {
            log_error($message, $context);
        }
    }
    
    /**
     * Get current user's position
     */
    protected function getUserPosition(): ?array
    {
        $user = $this->auth();
        
        if (!$user || !isset($user['position_id'])) {
            return null;
        }
        
        // Get position from database
        $database = Database::getInstance();
        $stmt = $database->prepare("
            SELECT p.*, h.name as hierarchy_name, h.level
            FROM positions p
            JOIN hierarchies h ON p.hierarchy_id = h.id
            WHERE p.id = ?
        ");
        
        $stmt->execute([$user['position_id']]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Check if user has hierarchical permission
     */
    protected function hasHierarchicalAccess(int $hierarchyId): bool
    {
        $userPosition = $this->getUserPosition();
        
        if (!$userPosition) {
            return false;
        }
        
        // Global admin has access to everything
        if ($userPosition['level'] === 1) {
            return true;
        }
        
        // Check if hierarchy is within user's scope
        $database = Database::getInstance();
        $stmt = $database->prepare("
            SELECT COUNT(*) FROM hierarchies 
            WHERE id = ? AND (
                parent_id = ? OR 
                id IN (
                    SELECT id FROM hierarchies 
                    WHERE parent_id = ? OR 
                    parent_id IN (SELECT id FROM hierarchies WHERE parent_id = ?)
                )
            )
        ");
        
        $stmt->execute([
            $hierarchyId,
            $userPosition['hierarchy_id'],
            $userPosition['hierarchy_id'],
            $userPosition['hierarchy_id']
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
}

/**
 * Simple Request Class
 */
class Request
{
    protected $input;
    protected $files;
    
    public function __construct()
    {
        $this->input = array_merge($_GET, $_POST);
        $this->files = $_FILES;
    }
    
    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->input;
        }
        
        return $this->input[$key] ?? $default;
    }
    
    public function all(): array
    {
        return $this->input;
    }
    
    public function only(array $keys): array
    {
        return array_intersect_key($this->input, array_flip($keys));
    }
    
    public function except(array $keys): array
    {
        return array_diff_key($this->input, array_flip($keys));
    }
    
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->input);
    }
    
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
    
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }
    
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    public function url(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    public function expectsJson(): bool
    {
        return strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
               strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    }
}

/**
 * Simple Response Class
 */
class Response
{
    protected $headers = [];
    
    public function header(string $name, string $value): void
    {
        $this->headers[$name] = $value;
        header("{$name}: {$value}");
    }
    
    public function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        $this->header('Content-Type', 'application/json');
        echo json_encode($data);
        exit;
    }
    
    public function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        $this->header('Location', $url);
        exit;
    }
}

/**
 * Simple Validator Class
 */
class Validator
{
    protected $data;
    protected $rules;
    protected $messages;
    protected $errors = [];
    
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
        
        $this->validate();
    }
    
    protected function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($rules as $rule) {
                $this->validateField($field, $rule);
            }
        }
    }
    
    protected function validateField(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;
        
        if ($rule === 'required' && empty($value)) {
            $this->addError($field, 'required');
        } elseif ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        } elseif (strpos($rule, 'min:') === 0) {
            $min = (int) substr($rule, 4);
            if (strlen($value) < $min) {
                $this->addError($field, 'min', ['min' => $min]);
            }
        } elseif (strpos($rule, 'max:') === 0) {
            $max = (int) substr($rule, 4);
            if (strlen($value) > $max) {
                $this->addError($field, 'max', ['max' => $max]);
            }
        }
    }
    
    protected function addError(string $field, string $rule, array $parameters = []): void
    {
        $message = $this->messages["{$field}.{$rule}"] ?? 
                  $this->messages[$rule] ?? 
                  $this->getDefaultMessage($field, $rule, $parameters);
        
        $this->errors[$field][] = $message;
    }
    
    protected function getDefaultMessage(string $field, string $rule, array $parameters = []): string
    {
        $messages = [
            'required' => "The {$field} field is required.",
            'email' => "The {$field} must be a valid email address.",
            'min' => "The {$field} must be at least {$parameters['min']} characters.",
            'max' => "The {$field} may not be greater than {$parameters['max']} characters."
        ];
        
        return $messages[$rule] ?? "The {$field} field is invalid.";
    }
    
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    public function errors(): array
    {
        return $this->errors;
    }
    
    public function validated(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }
}