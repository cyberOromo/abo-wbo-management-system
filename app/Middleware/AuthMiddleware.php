<?php
namespace App\Middleware;

/**
 * Authentication Middleware
 * ABO-WBO Management System
 */
class AuthMiddleware
{
    /**
     * Handle the request
     */
    public function handle(): void
    {
        if (!auth_check()) {
            // Check if this is an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Authentication required', 'redirect' => '/auth/login']);
                exit;
            }
            
            // For regular requests, redirect to login
            $baseUrl = $this->getBaseUrl();
            header("Location: {$baseUrl}/auth/login");
            exit;
        }
    }
    
    /**
     * Get the base URL for proper redirects
     */
    private function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // For proper web-based redirects, just use the host
        // The .htaccess handles routing everything through /public/index.php
        return $protocol . '://' . $host;
    }
}