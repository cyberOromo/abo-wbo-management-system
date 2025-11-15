<?php

namespace App\Middleware;

use Exception;

class RoleMiddleware
{
    private $userService;
    private $hierarchyPermissions;

    public function __construct()
    {
        $this->userService = new \App\Services\UserService();
        $this->initializeHierarchyPermissions();
    }

    /**
     * Handle role-based access control
     */
    public function handle($request, $next, ...$roles)
    {
        try {
            // Get current user from session
            $currentUser = $this->getCurrentUser();
            
            if (!$currentUser) {
                return $this->unauthorized('Authentication required');
            }
            
            // Check if user has required role
            if (!$this->hasRequiredRole($currentUser, $roles)) {
                return $this->forbidden('Insufficient permissions');
            }
            
            // Check hierarchical permissions
            if (!$this->hasHierarchicalAccess($currentUser, $request)) {
                return $this->forbidden('Access denied for this organizational level');
            }
            
            // Add user context to request
            $request->setUser($currentUser);
            
            return $next($request);
            
        } catch (Exception $e) {
            error_log("RoleMiddleware error: " . $e->getMessage());
            return $this->serverError('Access control error');
        }
    }

    /**
     * Check if user has any of the required roles
     */
    private function hasRequiredRole(array $user, array $requiredRoles): bool
    {
        if (empty($requiredRoles)) {
            return true; // No specific roles required
        }
        
        $userRole = $user['role'] ?? 'member';
        $userLevelScope = $user['level_scope'] ?? 'gurmu';
        
        foreach ($requiredRoles as $role) {
            // Handle compound roles like 'admin@global' or 'leader@gamta'
            if (strpos($role, '@') !== false) {
                [$requiredRole, $requiredScope] = explode('@', $role, 2);
                
                if ($userRole === $requiredRole && $userLevelScope === $requiredScope) {
                    return true;
                }
            } else {
                // Simple role check
                if ($userRole === $role) {
                    return true;
                }
                
                // Check role hierarchy
                if ($this->roleHierarchyCheck($userRole, $role)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check role hierarchy (admin > leader > member)
     */
    private function roleHierarchyCheck(string $userRole, string $requiredRole): bool
    {
        $roleHierarchy = [
            'admin' => 3,
            'leader' => 2,
            'member' => 1
        ];
        
        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }

    /**
     * Check hierarchical access permissions
     */
    private function hasHierarchicalAccess(array $user, $request): bool
    {
        $requestPath = $request->getPath();
        $requestMethod = $request->getMethod();
        
        // Admin has access to everything
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Get user's organizational scope
        $userScope = $this->getUserOrganizationalScope($user);
        
        // Check if the request requires specific hierarchical permissions
        $requiredPermissions = $this->getRequiredPermissions($requestPath, $requestMethod);
        
        if (empty($requiredPermissions)) {
            return true; // No specific permissions required
        }
        
        return $this->checkScopePermissions($userScope, $requiredPermissions, $request);
    }

    /**
     * Get user's organizational scope and hierarchy
     */
    private function getUserOrganizationalScope(array $user): array
    {
        $scope = [
            'level' => $user['level_scope'] ?? 'gurmu',
            'gurmu_id' => $user['gurmu_id'] ?? null,
            'gamta_id' => null,
            'godina_id' => null,
            'role' => $user['role'] ?? 'member'
        ];
        
        // Get full hierarchy information
        if ($scope['gurmu_id']) {
            $hierarchy = $this->userService->getUserHierarchy($user['id']);
            $scope['gamta_id'] = $hierarchy['gamta_id'] ?? null;
            $scope['godina_id'] = $hierarchy['godina_id'] ?? null;
        }
        
        return $scope;
    }

    /**
     * Get required permissions for a request path
     */
    private function getRequiredPermissions(string $path, string $method): array
    {
        // Define path-based permissions
        $pathPermissions = [
            // User management
            '/users' => ['scope' => 'hierarchy', 'action' => 'manage_users'],
            '/users/create' => ['scope' => 'hierarchy', 'action' => 'create_users'],
            '/users/*/edit' => ['scope' => 'hierarchy', 'action' => 'edit_users'],
            '/users/*/approve' => ['scope' => 'hierarchy', 'action' => 'approve_users'],
            
            // Task management
            '/tasks' => ['scope' => 'hierarchy', 'action' => 'view_tasks'],
            '/tasks/create' => ['scope' => 'hierarchy', 'action' => 'create_tasks'],
            '/tasks/*/assign' => ['scope' => 'hierarchy', 'action' => 'assign_tasks'],
            
            // Meeting management
            '/meetings' => ['scope' => 'hierarchy', 'action' => 'view_meetings'],
            '/meetings/create' => ['scope' => 'hierarchy', 'action' => 'create_meetings'],
            '/meetings/*/manage' => ['scope' => 'hierarchy', 'action' => 'manage_meetings'],
            
            // Financial management
            '/donations' => ['scope' => 'hierarchy', 'action' => 'view_donations'],
            '/donations/reports' => ['scope' => 'hierarchy', 'action' => 'view_financial_reports'],
            
            // Event management
            '/events' => ['scope' => 'hierarchy', 'action' => 'view_events'],
            '/events/create' => ['scope' => 'hierarchy', 'action' => 'create_events'],
            '/events/*/manage' => ['scope' => 'hierarchy', 'action' => 'manage_events'],
            
            // Reports
            '/reports' => ['scope' => 'hierarchy', 'action' => 'view_reports'],
            '/reports/generate' => ['scope' => 'hierarchy', 'action' => 'generate_reports'],
            
            // Admin areas
            '/admin' => ['scope' => 'global', 'action' => 'admin_access', 'role' => 'admin'],
            '/admin/users' => ['scope' => 'global', 'action' => 'admin_manage_users', 'role' => 'admin'],
            '/admin/hierarchy' => ['scope' => 'global', 'action' => 'admin_manage_hierarchy', 'role' => 'admin']
        ];
        
        // Find matching permission pattern
        foreach ($pathPermissions as $pattern => $permissions) {
            if ($this->matchesPattern($path, $pattern)) {
                return $permissions;
            }
        }
        
        return [];
    }

    /**
     * Check if path matches permission pattern
     */
    private function matchesPattern(string $path, string $pattern): bool
    {
        // Convert pattern to regex
        $regex = str_replace('\*', '[^/]+', preg_quote($pattern, '/'));
        return preg_match('/^' . $regex . '$/', $path);
    }

    /**
     * Check scope-based permissions
     */
    private function checkScopePermissions(array $userScope, array $requiredPermissions, $request): bool
    {
        $requiredScope = $requiredPermissions['scope'] ?? 'any';
        $requiredAction = $requiredPermissions['action'] ?? null;
        $requiredRole = $requiredPermissions['role'] ?? null;
        
        // Check role requirement
        if ($requiredRole && $userScope['role'] !== $requiredRole) {
            return false;
        }
        
        // Check scope requirements
        switch ($requiredScope) {
            case 'global':
                return $userScope['level'] === 'global';
                
            case 'godina':
                return in_array($userScope['level'], ['global', 'godina']);
                
            case 'gamta':
                return in_array($userScope['level'], ['global', 'godina', 'gamta']);
                
            case 'gurmu':
                return true; // All users have gurmu-level access
                
            case 'hierarchy':
                return $this->checkHierarchicalScope($userScope, $request);
                
            default:
                return true;
        }
    }

    /**
     * Check hierarchical scope access
     */
    private function checkHierarchicalScope(array $userScope, $request): bool
    {
        $resourceId = $this->extractResourceId($request);
        $resourceType = $this->extractResourceType($request);
        
        if (!$resourceId || !$resourceType) {
            // For listing/creation operations, allow based on user's scope
            return true;
        }
        
        // Check if user has access to specific resource
        return $this->hasResourceAccess($userScope, $resourceType, $resourceId);
    }

    /**
     * Extract resource ID from request
     */
    private function extractResourceId($request): ?int
    {
        $path = $request->getPath();
        $pathParams = $request->getPathParams();
        
        // Look for ID in path parameters
        if (isset($pathParams['id'])) {
            return (int)$pathParams['id'];
        }
        
        // Extract from URL pattern
        if (preg_match('/\/(\d+)(?:\/|$)/', $path, $matches)) {
            return (int)$matches[1];
        }
        
        return null;
    }

    /**
     * Extract resource type from request
     */
    private function extractResourceType($request): ?string
    {
        $path = $request->getPath();
        
        if (strpos($path, '/users') === 0) return 'user';
        if (strpos($path, '/tasks') === 0) return 'task';
        if (strpos($path, '/meetings') === 0) return 'meeting';
        if (strpos($path, '/events') === 0) return 'event';
        if (strpos($path, '/donations') === 0) return 'donation';
        if (strpos($path, '/courses') === 0) return 'course';
        
        return null;
    }

    /**
     * Check if user has access to specific resource
     */
    private function hasResourceAccess(array $userScope, string $resourceType, int $resourceId): bool
    {
        // Admin always has access
        if ($userScope['role'] === 'admin') {
            return true;
        }
        
        // Get resource hierarchy information
        $resourceHierarchy = $this->getResourceHierarchy($resourceType, $resourceId);
        
        if (!$resourceHierarchy) {
            return false;
        }
        
        // Check if user's scope encompasses the resource
        switch ($userScope['level']) {
            case 'global':
                return true;
                
            case 'godina':
                return $resourceHierarchy['godina_id'] === $userScope['godina_id'];
                
            case 'gamta':
                return $resourceHierarchy['gamta_id'] === $userScope['gamta_id'];
                
            case 'gurmu':
                return $resourceHierarchy['gurmu_id'] === $userScope['gurmu_id'];
                
            default:
                return false;
        }
    }

    /**
     * Get hierarchy information for a resource
     */
    private function getResourceHierarchy(string $resourceType, int $resourceId): ?array
    {
        try {
            // This would typically query the database to get hierarchy info
            // For now, return a placeholder structure
            return [
                'gurmu_id' => 1,
                'gamta_id' => 1,
                'godina_id' => 1
            ];
        } catch (Exception $e) {
            error_log("Error getting resource hierarchy: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current user from session
     */
    private function getCurrentUser(): ?array
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        // Get user from session cache or database
        if (isset($_SESSION['user_data'])) {
            return $_SESSION['user_data'];
        }
        
        // Fetch from database if not in session
        $user = $this->userService->getUserProfile($_SESSION['user_id']);
        
        if ($user) {
            $_SESSION['user_data'] = $user;
        }
        
        return $user;
    }

    /**
     * Initialize hierarchy-based permissions
     */
    private function initializeHierarchyPermissions(): void
    {
        $this->hierarchyPermissions = [
            'global' => [
                'manage_all_users',
                'manage_all_tasks',
                'manage_all_meetings',
                'manage_all_events',
                'view_all_reports',
                'admin_access'
            ],
            'godina' => [
                'manage_godina_users',
                'manage_godina_tasks',
                'manage_godina_meetings',
                'manage_godina_events',
                'view_godina_reports'
            ],
            'gamta' => [
                'manage_gamta_users',
                'manage_gamta_tasks',
                'manage_gamta_meetings',
                'manage_gamta_events',
                'view_gamta_reports'
            ],
            'gurmu' => [
                'manage_gurmu_users',
                'manage_gurmu_tasks',
                'manage_gurmu_meetings',
                'manage_gurmu_events',
                'view_gurmu_reports'
            ]
        ];
    }

    /**
     * Return unauthorized response
     */
    private function unauthorized(string $message = 'Unauthorized'): array
    {
        http_response_code(401);
        return [
            'success' => false,
            'error' => $message,
            'code' => 401,
            'redirect' => '/auth/login'
        ];
    }

    /**
     * Return forbidden response
     */
    private function forbidden(string $message = 'Forbidden'): array
    {
        http_response_code(403);
        return [
            'success' => false,
            'error' => $message,
            'code' => 403
        ];
    }

    /**
     * Return server error response
     */
    private function serverError(string $message = 'Internal Server Error'): array
    {
        http_response_code(500);
        return [
            'success' => false,
            'error' => $message,
            'code' => 500
        ];
    }

    /**
     * Check if user can perform action on resource
     */
    public function canPerformAction(array $user, string $action, string $resourceType = null, int $resourceId = null): bool
    {
        try {
            $userScope = $this->getUserOrganizationalScope($user);
            
            // Admin can do everything
            if ($user['role'] === 'admin') {
                return true;
            }
            
            // Check action permissions
            $allowedActions = $this->hierarchyPermissions[$userScope['level']] ?? [];
            
            if (in_array($action, $allowedActions)) {
                // If no specific resource, allow the action
                if (!$resourceType || !$resourceId) {
                    return true;
                }
                
                // Check resource-specific access
                return $this->hasResourceAccess($userScope, $resourceType, $resourceId);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error checking action permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's allowed actions
     */
    public function getUserAllowedActions(array $user): array
    {
        $userScope = $this->getUserOrganizationalScope($user);
        
        if ($user['role'] === 'admin') {
            return array_merge(...array_values($this->hierarchyPermissions));
        }
        
        return $this->hierarchyPermissions[$userScope['level']] ?? [];
    }

    /**
     * Middleware factory for specific roles
     */
    public static function roles(...$roles)
    {
        return function ($request, $next) use ($roles) {
            $middleware = new self();
            return $middleware->handle($request, $next, ...$roles);
        };
    }

    /**
     * Middleware factory for specific permissions
     */
    public static function permission(string $permission)
    {
        return function ($request, $next) use ($permission) {
            $middleware = new self();
            
            $user = $middleware->getCurrentUser();
            if (!$user) {
                return $middleware->unauthorized();
            }
            
            if (!$middleware->canPerformAction($user, $permission)) {
                return $middleware->forbidden("Permission '{$permission}' required");
            }
            
            return $next($request);
        };
    }
}