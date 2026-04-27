<?php
namespace App\Middleware;

/**
 * Admin Role Middleware
 * ABO-WBO Management System
 */
class AdminMiddleware
{
    /**
     * Handle the request
     */
    public function handle(): void
    {
        // First check if user is authenticated
        if (!auth_check()) {
            header('Location: /auth/login');
            exit;
        }
        
        // Check if user has admin role
        $user = auth_user();
        $role = (string) ($user['role'] ?? '');
        $userType = (string) ($user['user_type'] ?? '');

        if (!$user || !in_array($role, ['admin', 'system_admin', 'super_admin'], true) && $userType !== 'system_admin') {
            http_response_code(403);
            echo "<h1>403 - Access Denied</h1>";
            echo "<p>You don't have permission to access this resource.</p>";
            exit;
        }
    }
}