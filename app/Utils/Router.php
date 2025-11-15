<?php

namespace App\Utils;

/**
 * Router - URL routing and request handling
 * 
 * Handles URL routing, middleware execution, and request dispatching
 * to appropriate controllers with parameter extraction and validation.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private array $groups = [];
    private array $namedRoutes = [];
    private string $currentGroupPrefix = '';
    private array $currentGroupMiddleware = [];
    private Request $request;
    private Response $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Register GET route
     */
    public function get(string $pattern, $handler, array $middleware = []): Route
    {
        return $this->addRoute('GET', $pattern, $handler, $middleware);
    }

    /**
     * Register POST route
     */
    public function post(string $pattern, $handler, array $middleware = []): Route
    {
        return $this->addRoute('POST', $pattern, $handler, $middleware);
    }

    /**
     * Register PUT route
     */
    public function put(string $pattern, $handler, array $middleware = []): Route
    {
        return $this->addRoute('PUT', $pattern, $handler, $middleware);
    }

    /**
     * Register DELETE route
     */
    public function delete(string $pattern, $handler, array $middleware = []): Route
    {
        return $this->addRoute('DELETE', $pattern, $handler, $middleware);
    }

    /**
     * Register PATCH route
     */
    public function patch(string $pattern, $handler, array $middleware = []): Route
    {
        return $this->addRoute('PATCH', $pattern, $handler, $middleware);
    }

    /**
     * Register OPTIONS route
     */
    public function options(string $pattern, $handler, array $middleware = []): Route
    {
        return $this->addRoute('OPTIONS', $pattern, $handler, $middleware);
    }

    /**
     * Register route for any HTTP method
     */
    public function any(string $pattern, $handler, array $middleware = []): Route
    {
        $route = null;
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'] as $method) {
            $route = $this->addRoute($method, $pattern, $handler, $middleware);
        }
        return $route;
    }

    /**
     * Register route for multiple HTTP methods
     */
    public function match(array $methods, string $pattern, $handler, array $middleware = []): Route
    {
        $route = null;
        foreach ($methods as $method) {
            $route = $this->addRoute(strtoupper($method), $pattern, $handler, $middleware);
        }
        return $route;
    }

    /**
     * Add route to routing table
     */
    private function addRoute(string $method, string $pattern, $handler, array $middleware = []): Route
    {
        $pattern = $this->currentGroupPrefix . $pattern;
        $middleware = array_merge($this->currentGroupMiddleware, $middleware);
        
        $route = new Route($method, $pattern, $handler, $middleware);
        $this->routes[] = $route;
        
        return $route;
    }

    /**
     * Group routes with common prefix and middleware
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->currentGroupPrefix;
        $previousMiddleware = $this->currentGroupMiddleware;

        // Set group attributes
        if (isset($attributes['prefix'])) {
            $this->currentGroupPrefix .= '/' . trim($attributes['prefix'], '/');
        }
        
        if (isset($attributes['middleware'])) {
            $this->currentGroupMiddleware = array_merge(
                $this->currentGroupMiddleware,
                is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']]
            );
        }

        // Execute callback to register grouped routes
        $callback($this);

        // Restore previous values
        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddleware = $previousMiddleware;
    }

    /**
     * Register global middleware
     */
    public function middleware(string $name, string $class): void
    {
        $this->middlewares[$name] = $class;
    }

    /**
     * Dispatch request to matching route
     */
    public function dispatch(?string $uri = null, ?string $method = null): mixed
    {
        $uri = $uri ?? $this->request->getUri();
        $method = $method ?? $this->request->getMethod();

        // Find matching route
        $matchedRoute = $this->findRoute($method, $uri);
        
        if (!$matchedRoute) {
            return $this->handleNotFound();
        }

        try {
            // Execute middleware chain
            $response = $this->executeMiddleware($matchedRoute['route'], $matchedRoute['params']);
            
            if ($response !== null) {
                return $response; // Middleware returned early response
            }

            // Execute route handler
            return $this->executeHandler($matchedRoute['route'], $matchedRoute['params']);
            
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Find matching route for given method and URI
     */
    private function findRoute(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {
            if ($route->getMethod() !== $method) {
                continue;
            }

            $params = $this->matchPattern($route->getPattern(), $uri);
            if ($params !== false) {
                return [
                    'route' => $route,
                    'params' => $params
                ];
            }
        }

        return null;
    }

    /**
     * Match URI against route pattern
     */
    private function matchPattern(string $pattern, string $uri): array|false
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Extract named parameters
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }

        return false;
    }

    /**
     * Execute middleware chain
     */
    private function executeMiddleware(Route $route, array $params): mixed
    {
        $middlewareList = $route->getMiddleware();
        
        foreach ($middlewareList as $middlewareName) {
            if (!isset($this->middlewares[$middlewareName])) {
                throw new \Exception("Middleware '{$middlewareName}' not found");
            }

            $middlewareClass = $this->middlewares[$middlewareName];
            $middleware = new $middlewareClass();

            // Execute middleware handle method
            $response = $middleware->handle($this->request, function($request) {
                return null; // Continue to next middleware
            });

            if ($response !== null) {
                return $response; // Middleware returned early response
            }
        }

        return null; // All middleware passed
    }

    /**
     * Execute route handler
     */
    private function executeHandler(Route $route, array $params): mixed
    {
        $handler = $route->getHandler();

        if (is_callable($handler)) {
            // Closure handler
            return call_user_func_array($handler, array_values($params));
        }

        if (is_string($handler)) {
            // Controller@method format
            [$controllerName, $methodName] = explode('@', $handler);
            
            // Add namespace if not present
            if (strpos($controllerName, '\\') === false) {
                $controllerName = 'App\\Controllers\\' . $controllerName;
            }

            if (!class_exists($controllerName)) {
                throw new \Exception("Controller '{$controllerName}' not found");
            }

            $controller = new $controllerName();

            if (!method_exists($controller, $methodName)) {
                throw new \Exception("Method '{$methodName}' not found in controller '{$controllerName}'");
            }

            // Inject request and response objects
            if (method_exists($controller, 'setRequest')) {
                $controller->setRequest($this->request);
            }
            if (method_exists($controller, 'setResponse')) {
                $controller->setResponse($this->response);
            }

            return call_user_func_array([$controller, $methodName], array_values($params));
        }

        throw new \Exception("Invalid route handler");
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): mixed
    {
        http_response_code(404);
        
        if (isset($this->middlewares['NotFoundHandler'])) {
            $handler = new $this->middlewares['NotFoundHandler']();
            return $handler->handle($this->request, function() {
                return $this->response->json(['error' => 'Route not found'], 404);
            });
        }

        return $this->response->json(['error' => 'Route not found'], 404);
    }

    /**
     * Handle exceptions
     */
    private function handleException(\Exception $e): mixed
    {
        http_response_code(500);
        
        if (isset($this->middlewares['ExceptionHandler'])) {
            $handler = new $this->middlewares['ExceptionHandler']();
            return $handler->handle($this->request, function() use ($e) {
                return $this->response->json([
                    'error' => 'Internal server error',
                    'message' => $e->getMessage()
                ], 500);
            });
        }

        return $this->response->json([
            'error' => 'Internal server error',
            'message' => $e->getMessage()
        ], 500);
    }

    /**
     * Generate URL for named route
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Named route '{$name}' not found");
        }

        $route = $this->namedRoutes[$name];
        $pattern = $route->getPattern();

        // Replace parameters in pattern
        foreach ($params as $key => $value) {
            $pattern = str_replace('{' . $key . '}', $value, $pattern);
        }

        return $pattern;
    }

    /**
     * Get current request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get response object
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Load routes from file
     */
    public function loadRoutes(string $file): void
    {
        if (!file_exists($file)) {
            throw new \Exception("Routes file '{$file}' not found");
        }

        require $file;
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Clear all routes
     */
    public function clearRoutes(): void
    {
        $this->routes = [];
        $this->namedRoutes = [];
    }
}

/**
 * Single route representation
 */
class Route
{
    private string $method;
    private string $pattern;
    private $handler;
    private array $middleware;
    private ?string $name = null;

    public function __construct(string $method, string $pattern, $handler, array $middleware = [])
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->middleware = $middleware;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function middleware(array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }
}