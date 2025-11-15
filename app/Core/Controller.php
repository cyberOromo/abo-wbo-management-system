<?php
namespace App\Core;

/**
 * Base Controller Class
 * ABO-WBO Management System
 */
abstract class Controller
{
    protected $data = [];
    protected $layout = 'app';
    
    /**
     * Render view with data
     */
    protected function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        
        // Start output buffering
        ob_start();
        
        // Extract data for view
        extract($this->data);
        
        // Include view file
        $viewFile = $this->getViewPath($view);
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: {$view}");
        }
        
        $content = ob_get_clean();
        
        // If no layout specified, return content directly
        if ($this->layout === null) {
            return $content;
        }
        
        // Render with layout
        return $this->renderWithLayout($content);
    }
    
    /**
     * Render view with layout
     */
    protected function renderWithLayout(string $content): string
    {
        ob_start();
        
        // Extract data for layout
        extract($this->data);
        
        // Include layout file
        $layoutFile = $this->getLayoutPath($this->layout);
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            throw new \Exception("Layout not found: {$this->layout}");
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get view file path
     */
    protected function getViewPath(string $view): string
    {
        $viewPath = str_replace('.', DIRECTORY_SEPARATOR, $view);
        return config('paths.views') . DIRECTORY_SEPARATOR . $viewPath . '.php';
    }
    
    /**
     * Get layout file path
     */
    protected function getLayoutPath(string $layout): string
    {
        return config('paths.views') . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
    }
    
    /**
     * Set view data
     */
    protected function with(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * Set multiple view data
     */
    protected function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    /**
     * Set layout
     */
    protected function layout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Disable layout
     */
    protected function noLayout(): self
    {
        $this->layout = null;
        return $this;
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Return success JSON response
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): void
    {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->json($response, $status);
    }
    
    /**
     * Return error JSON response
     */
    protected function error(string $message = 'Error', $errors = null, int $status = 400): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $status);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Redirect back with input and errors
     */
    protected function redirectBack(array $errors = [], array $input = []): void
    {
        if (!empty($errors)) {
            session_flash('errors', $errors);
        }
        
        if (!empty($input)) {
            session_flash('old_input', $input);
        }
        
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referrer);
    }
    
    /**
     * Redirect with flash message
     */
    protected function redirectWithMessage(string $url, string $message, string $type = 'success'): void
    {
        session_flash($type, $message);
        $this->redirect($url);
    }
    
    /**
     * Get request method
     */
    protected function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }
    
    /**
     * Check if request is GET
     */
    protected function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get input data
     */
    protected function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($_GET, $_POST);
        }
        
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Get POST data
     */
    protected function post(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     */
    protected function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get file upload
     */
    protected function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
    
    /**
     * Validate input data
     */
    protected function validate(array $rules, array $messages = []): array
    {
        $data = $this->input();
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            
            foreach ($rules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;
                
                $error = $this->validateField($field, $value, $ruleName, $ruleValue, $data);
                if ($error) {
                    $messageKey = "{$field}.{$ruleName}";
                    $errors[$field] = $messages[$messageKey] ?? $error;
                    break; // Stop validating this field on first error
                }
            }
        }
        
        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->error('Validation failed', $errors, 422);
            } else {
                $this->redirectBack($errors, $data);
            }
        }
        
        return $data;
    }
    
    /**
     * Validate individual field
     */
    protected function validateField(string $field, $value, string $rule, $ruleValue, array $data): ?string
    {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    return "The {$field} field is required.";
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "The {$field} must be a valid email address.";
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleValue) {
                    return "The {$field} must be at least {$ruleValue} characters.";
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleValue) {
                    return "The {$field} may not be greater than {$ruleValue} characters.";
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "The {$field} must be a number.";
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    return "The {$field} must be an integer.";
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($data[$confirmField] ?? null)) {
                    return "The {$field} confirmation does not match.";
                }
                break;
                
            case 'unique':
                // Format: unique:table,column
                if ($ruleValue) {
                    $parts = explode(',', $ruleValue);
                    $table = $parts[0];
                    $column = $parts[1] ?? $field;
                    
                    $db = \App\Utils\Database::getInstance();
                    $exists = $db->fetch(
                        "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?",
                        [$value]
                    );
                    
                    if ($exists && $exists['count'] > 0) {
                        return "The {$field} has already been taken.";
                    }
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!auth_check()) {
            if ($this->isAjax()) {
                $this->error('Authentication required', null, 401);
            } else {
                $this->redirect('/auth/login');
            }
        }
    }
    
    /**
     * Require specific role
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        
        $user = auth_user();
        if (!$user || $user['role'] !== $role) {
            if ($this->isAjax()) {
                $this->error('Insufficient permissions', null, 403);
            } else {
                $this->redirect('/');
            }
        }
    }
    
    /**
     * Require specific permission
     */
    protected function requirePermission(string $permission): void
    {
        $this->requireAuth();
        
        $user = auth_user();
        if (!$user || !$this->userHasPermission($user, $permission)) {
            if ($this->isAjax()) {
                $this->error('Insufficient permissions', null, 403);
            } else {
                $this->redirectWithMessage('/', 'You do not have permission to perform this action.', 'error');
            }
        }
    }
    
    /**
     * Check if user has permission
     */
    protected function userHasPermission(array $user, string $permission): bool
    {
        // Admin has all permissions
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Define permission mappings
        $permissions = [
            'user.create' => ['admin'],
            'user.edit' => ['admin', 'executive'],
            'user.delete' => ['admin'],
            'user.view' => ['admin', 'executive', 'member'],
            'hierarchy.manage' => ['admin'],
            'position.manage' => ['admin', 'executive'],
            'task.create' => ['admin', 'executive'],
            'task.assign' => ['admin', 'executive'],
            'meeting.create' => ['admin', 'executive'],
            'event.create' => ['admin', 'executive'],
            'donation.view' => ['admin', 'executive'],
            'report.view' => ['admin', 'executive']
        ];
        
        $allowedRoles = $permissions[$permission] ?? [];
        return in_array($user['role'], $allowedRoles);
    }
    
    /**
     * Require CSRF token
     */
    protected function requireCsrf(): void
    {
        if (!csrf_verify($this->input('_token'))) {
            if ($this->isAjax()) {
                $this->error('CSRF token mismatch', null, 419);
            } else {
                $this->redirectBack(['csrf' => 'CSRF token mismatch']);
            }
        }
    }
}