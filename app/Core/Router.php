<?php
namespace App\Core;

/**
 * Router Class
 * ABO-WBO Management System
 */
class Router
{
    protected $routes = [];
    protected $middlewareStack = [];
    protected $currentGroup = [];
    protected $registeredMiddleware = [];
    
    /**
     * Register middleware class
     */
    public function registerMiddleware(string $name, string $class): void
    {
        $this->registeredMiddleware[$name] = $class;
    }
    
    /**
     * Add GET route
     */
    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Add POST route
     */
    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Add PUT route
     */
    public function put(string $path, $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Add DELETE route
     */
    public function delete(string $path, $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Add PATCH route
     */
    public function patch(string $path, $handler): self
    {
        return $this->addRoute('PATCH', $path, $handler);
    }
    
    /**
     * Add route for multiple methods
     */
    public function match(array $methods, string $path, $handler): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler);
        }
        return $this;
    }
    
    /**
     * Add route for all HTTP methods
     */
    public function any(string $path, $handler): self
    {
        return $this->match(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $path, $handler);
    }
    
    /**
     * Create route group with shared attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousGroup = $this->currentGroup;
        
        $this->currentGroup = array_merge($previousGroup, $attributes);
        
        call_user_func($callback, $this);
        
        $this->currentGroup = $previousGroup;
    }
    
    /**
     * Add middleware to the last registered route
     */
    public function middleware($middleware): self
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        
        // Apply middleware to the last route immediately
        if (!empty($this->routes)) {
            $lastIndex = count($this->routes) - 1;
            $existingMiddleware = $this->routes[$lastIndex]['middleware'] ?? [];
            $this->routes[$lastIndex]['middleware'] = array_merge($existingMiddleware, $middleware);
        }
        
        return $this;
    }
    
    /**
     * Add route with prefix
     */
    public function prefix(string $prefix): self
    {
        $this->currentGroup['prefix'] = ($this->currentGroup['prefix'] ?? '') . '/' . trim($prefix, '/');
        return $this;
    }
    
    /**
     * Add route to routes array
     */
    protected function addRoute(string $method, string $path, $handler): self
    {
        // Apply group prefix
        if (!empty($this->currentGroup['prefix'])) {
            $path = rtrim($this->currentGroup['prefix'], '/') . '/' . ltrim($path, '/');
        }
        
        // Normalize path
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        
        // Collect middleware from group only (not middleware stack)
        $groupMiddleware = $this->currentGroup['middleware'] ?? [];
        if (!is_array($groupMiddleware)) {
            $groupMiddleware = [$groupMiddleware];
        }
        
        // Add route (middleware will be applied via ->middleware() method if needed)
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $groupMiddleware,  // Only group middleware initially
            'regex' => $this->convertToRegex($path),
            'parameters' => $this->extractParameters($path)
        ];
        
        return $this;
    }
    
    /**
     * Convert route path to regex pattern
     */
    protected function convertToRegex(string $path): string
    {
        // Use # as delimiter to avoid issues with forward slashes
        $pattern = preg_quote($path, '#');
        
        // Convert parameter placeholders to regex groups
        // preg_quote escapes { and } to \{ and \}, so we need to match those
        $pattern = preg_replace('/\\\{([^}]+)\\\}/', '([^/]+)', $pattern);
        
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Extract parameter names from route path
     */
    protected function extractParameters(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }
    
    /**
     * Dispatch current request
     */
    public function dispatch(): void
    {
        $method = $this->getRequestMethod();
        $path = $this->getCurrentPath();
        
        // DEBUG: Log route debugging info
        error_log("Router: Dispatching $method $path");
        error_log("Router: Total routes registered: " . count($this->routes));
        
        // Find matching route
        $route = $this->findRoute($method, $path);
        
        if (!$route) {
            error_log("Router: No route found for $method $path");
            $this->handleNotFound();
            return;
        }
        
        error_log("Router: Found route for $method $path");
        
        try {
            // Execute middleware
            $this->executeMiddleware($route['middleware']);
            
            // Execute route handler
            $this->executeHandler($route);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Resolve the effective HTTP method, honoring form method spoofing.
     */
    protected function getRequestMethod(): string
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($method === 'POST') {
            $override = strtoupper((string) ($_POST['_method'] ?? ''));
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        return $method;
    }
    
    /**
     * Get current request path
     */
    protected function getCurrentPath(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Fix double slash URLs that browsers sometimes generate
        // parse_url treats //path as a host, so fix it before parsing
        if (strpos($requestUri, '//') === 0) {
            $requestUri = substr($requestUri, 1); // Remove one slash: //auth/login → /auth/login
        }
        
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Handle /index.php/path format first - extract path after index.php
        if (preg_match('#/index\.php/(.+)#', $path, $matches)) {
            $path = '/' . $matches[1];
        } elseif (preg_match('#index\.php/(.+)#', $path, $matches)) {
            $path = '/' . $matches[1];
        } elseif (preg_match('#/index\.php$#', $path) || preg_match('#^index\.php$#', $path)) {
            $path = '/';
        } else {
            // Remove base path if running in subdirectory (for normal paths)
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath !== '/' && strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath));
            }
        }
        
        // Normalize path
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        
        return $path;
    }
    
    /**
     * Find matching route
     */
    protected function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['regex'], $path, $matches)) {
                // Extract parameter values
                $parameters = [];
                for ($i = 1; $i < count($matches); $i++) {
                    $parameterName = $route['parameters'][$i - 1] ?? null;
                    if ($parameterName) {
                        $parameters[$parameterName] = $matches[$i];
                    }
                }
                
                $route['matched_parameters'] = $parameters;
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Execute middleware stack
     */
    protected function executeMiddleware(array $middleware): void
    {
        foreach ($middleware as $middlewareName) {
            $middlewareClass = $this->resolveMiddleware($middlewareName);
            
            if (!$middlewareClass) {
                throw new \Exception("Middleware not found: {$middlewareName}");
            }
            
            $middlewareInstance = new $middlewareClass();
            
            if (!method_exists($middlewareInstance, 'handle')) {
                throw new \Exception("Middleware {$middlewareName} must have a handle method");
            }
            
            $middlewareInstance->handle();
        }
    }
    
    /**
     * Resolve middleware class name
     */
    protected function resolveMiddleware(string $middleware): ?string
    {
        // First check registered middleware
        if (isset($this->registeredMiddleware[$middleware])) {
            return $this->registeredMiddleware[$middleware];
        }
        
        // Fallback to config-based middleware
        $middlewareMap = config('app.middleware', []);
        
        if (isset($middlewareMap[$middleware])) {
            return $middlewareMap[$middleware];
        }
        
        // Try to resolve as class name
        if (class_exists($middleware)) {
            return $middleware;
        }
        
        // Try with App\Middleware namespace
        $fullClass = "App\\Middleware\\{$middleware}";
        if (class_exists($fullClass)) {
            return $fullClass;
        }
        
        return null;
    }
    
    /**
     * Execute route handler
     */
    protected function executeHandler(array $route): void
    {
        $handler = $route['handler'];
        $parameters = $route['matched_parameters'] ?? [];
        
        if (is_callable($handler)) {
            // Closure handler
            error_log("Router: Executing closure handler");
            $result = call_user_func_array($handler, array_values($parameters));
            error_log("Router: Closure returned: " . var_export($result, true));
            if ($result !== null) {
                echo $result;
                exit(); // Exit immediately after closure to prevent output issues
            }
            
        } elseif (is_string($handler)) {
            // Controller@method handler
            error_log("Router: Executing controller handler: {$handler}");
            $this->executeControllerMethod($handler, $parameters);
            
        } else {
            error_log("Router: Invalid route handler type");
            throw new \Exception("Invalid route handler");
        }
    }
    
    /**
     * Execute controller method
     */
    protected function executeControllerMethod(string $handler, array $parameters): void
    {
        if (strpos($handler, '@') === false) {
            throw new \Exception("Controller method must be in format 'Controller@method'");
        }
        
        [$controllerName, $method] = explode('@', $handler, 2);
        
        error_log("Router: Executing controller {$controllerName}@{$method}");
        
        // Resolve controller class
        $controllerClass = $this->resolveController($controllerName);
        
        if (!$controllerClass || !class_exists($controllerClass)) {
            error_log("Router: Controller class not found: {$controllerName}");
            throw new \Exception("Controller not found: {$controllerName}");
        }
        
        error_log("Router: Instantiating controller: {$controllerClass}");
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            error_log("Router: Method {$method} not found in controller {$controllerName}");
            throw new \Exception("Method {$method} not found in controller {$controllerName}");
        }
        
        error_log("Router: Calling method {$method} on controller {$controllerName}");
        
        // Set route parameters in controller if it has a setParameters method
        if (method_exists($controller, 'setParameters')) {
            $controller->setParameters($parameters);
        }
        
        echo call_user_func_array([$controller, $method], array_values($parameters));
    }
    
    /**
     * Resolve controller class name
     */
    protected function resolveController(string $controller): string
    {
        // Try exact class name first
        if (class_exists($controller)) {
            return $controller;
        }
        
        // Try with App\Controllers namespace
        $fullClass = "App\\Controllers\\{$controller}";
        if (class_exists($fullClass)) {
            return $fullClass;
        }
        
        // Try with Controller suffix
        $withSuffix = "App\\Controllers\\{$controller}Controller";
        if (class_exists($withSuffix)) {
            return $withSuffix;
        }
        
        return $controller;
    }
    
    /**
     * Handle 404 Not Found
     */
    protected function handleNotFound(): void
    {
        http_response_code(404);
        
        // Try to load 404 view
        $notFoundView = config('app.paths.views') . '/errors/404.php';
        if (file_exists($notFoundView)) {
            include $notFoundView;
        } else {
            echo "<h1>404 - Page Not Found</h1>";
            echo "<p>The requested page could not be found.</p>";
        }
    }
    
    /**
     * Handle exceptions
     */
    protected function handleException(\Exception $e): void
    {
        http_response_code(500);
        
        // Log error
        log_error("Route exception: " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Show error page
        if (config('app.debug', false)) {
            echo "<h1>Error</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            $errorView = config('app.paths.views') . '/errors/500.php';
            if (file_exists($errorView)) {
                include $errorView;
            } else {
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>Something went wrong. Please try again later.</p>";
            }
        }
    }
    
    /**
     * Generate URL for named route
     */
    public function url(string $name, array $parameters = []): string
    {
        $route = $this->findRouteByName($name);
        
        if (!$route) {
            throw new \Exception("Route not found: {$name}");
        }
        
        $path = $route['path'];
        
        // Replace parameters in path
        foreach ($parameters as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }
        
        return $path;
    }
    
    /**
     * Find route by name
     */
    protected function findRouteByName(string $name): ?array
    {
        foreach ($this->routes as $route) {
            if (isset($route['name']) && $route['name'] === $name) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Name the previous route
     */
    public function name(string $name): self
    {
        if (!empty($this->routes)) {
            $lastIndex = count($this->routes) - 1;
            $this->routes[$lastIndex]['name'] = $name;
        }
        
        return $this;
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}