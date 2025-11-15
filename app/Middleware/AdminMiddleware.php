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
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }
        
        // Handle both object and array user data
        $userRole = null;
        if (is_object($user)) {
            if (method_exists($user, 'hasRole')) {
                if (!$user->hasRole('admin')) {
                    $this->accessDenied();
                }
                return;
            } else {
                $userRole = $user->role ?? null;
            }
        } else if (is_array($user)) {
            $userRole = $user['role'] ?? null;
        }
        
        // Check if user has admin role
        if ($userRole !== 'admin') {
            $this->accessDenied();
        }
    }
    
    /**
     * Return access denied response
     */
    private function accessDenied(): void
    {
        http_response_code(403);
        echo "<h1>403 - Access Denied</h1>";
        echo "<p>You don't have permission to access this resource.</p>";
        exit;
    }
}