<?php

namespace App\Core;

/**
 * Base Controller with common functionality
 */
class BaseController extends Controller
{
    public function __construct()
    {
        // Base controller initialization
    }

    /**
     * Get authenticated user
     */
    protected function getAuthUser()
    {
        return auth_user();
    }

    /**
     * Render view with layout
     */
    protected function render(string $view, array $data = []): string
    {
        // Extract data to variables
        extract($data);
        
        // Include view file
        $viewFile = APP_ROOT . '/resources/views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            
            // Modern views are complete HTML documents - don't wrap in layout
            if (strpos($view, '_modern') !== false || strpos($content, '<!DOCTYPE html>') !== false) {
                echo $content;
                return $content;
            }
            
            // Include layout if not an AJAX request and not a modern view
            if (!$this->isAjaxRequest()) {
                $title = $title ?? 'ABO-WBO Management System';
                ob_start();
                include APP_ROOT . '/resources/views/layouts/app.php';
                $output = ob_get_clean();
                echo $output;
                return $output;
            }
            
            echo $content;
            return $content;
        }
        
        throw new \Exception("View file not found: $view");
    }

    /**
     * Return JSON response
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        $this->json($data, $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse($message, $statusCode = 500)
    {
        if ($this->isAjaxRequest()) {
            return $this->error($message, null, $statusCode);
        }
        
        $title = 'Error';
        $error = $message;
        include APP_ROOT . '/resources/views/errors/generic.php';
        exit;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest()
    {
        return $this->isAjax();
    }

    /**
     * Get user's hierarchical scope for data filtering
     */
    protected function getUserHierarchicalScope($user)
    {
        if ($user['role'] === 'admin') {
            return 'all';
        } elseif ($user['role'] === 'executive') {
            return ['godina' => $user['godina_id'] ?? null];
        } else {
            return [
                'godina' => $user['godina_id'] ?? null,
                'gamta' => $user['gamta_id'] ?? null,
                'gurmu' => $user['gurmu_id'] ?? null
            ];
        }
    }

    /**
     * Apply hierarchical filtering to query
     */
    protected function applyHierarchicalFilter($query, $user)
    {
        $scope = $this->getUserHierarchicalScope($user);
        
        if ($scope === 'all') {
            return $query; // Admin sees all
        }
        
        if (isset($scope['godina']) && $scope['godina']) {
            $query .= " AND godina_id = " . (int)$scope['godina'];
        }
        if (isset($scope['gamta']) && $scope['gamta']) {
            $query .= " AND gamta_id = " . (int)$scope['gamta'];
        }
        if (isset($scope['gurmu']) && $scope['gurmu']) {
            $query .= " AND gurmu_id = " . (int)$scope['gurmu'];
        }
        
        return $query;
    }
}