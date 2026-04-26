<?php

namespace App\Middleware;

class ModuleAccessMiddleware
{
    /**
     * Enforce access to a module based on the current request path.
     */
    public function handle(): void
    {
        if (!auth_check()) {
            header('Location: /auth/login');
            exit;
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $module = $this->resolveModule($path);

        if ($module === null || can_access_module($module)) {
            return;
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        session_set('error', 'You do not have permission to access that module.');
        header('Location: /dashboard');
        exit;
    }

    private function resolveModule(string $path): ?string
    {
        $map = [
            '/users/profile' => 'profile',
            '/users' => 'users',
            '/hierarchy' => 'hierarchy',
            '/positions' => 'positions',
            '/responsibilities' => 'responsibilities',
            '/reports' => 'reports',
            '/notifications' => 'notifications',
            '/settings' => 'settings',
            '/user-emails' => 'user_emails',
        ];

        foreach ($map as $prefix => $module) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $module;
            }
        }

        return null;
    }
}