<?php

namespace App\Middleware;

use Exception;

class ValidationMiddleware
{
    private $validators = [];
    private $errors = [];
    private $rules = [];

    /**
     * Handle input validation pipeline
     */
    public function handle($request, $next, array $rules = [])
    {
        try {
            // Clear previous errors
            $this->errors = [];
            
            // Set validation rules
            $this->rules = $rules;
            
            // Get request data
            $data = $this->getRequestData($request);
            
            // Validate request data
            if (!$this->validate($data, $rules)) {
                return $this->validationFailedResponse();
            }
            
            // Sanitize and attach validated data to request
            $sanitizedData = $this->sanitizeData($data, $rules);
            $request->setValidatedData($sanitizedData);
            
            return $next($request);
            
        } catch (Exception $e) {
            error_log("Validation Middleware error: " . $e->getMessage());
            return $this->serverErrorResponse();
        }
    }

    /**
     * Get request data from various sources
     */
    private function getRequestData($request): array
    {
        $data = [];
        
        // Get data based on content type and method
        $method = strtoupper($request->getMethod());
        $contentType = $request->getHeader('Content-Type') ?? '';
        
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode($request->getBody(), true) ?? [];
            } else {
                $data = $request->getParsedBody() ?? [];
            }
        }
        
        // Merge with query parameters
        $data = array_merge($data, $request->getQueryParams() ?? []);
        
        // Add file data if present
        if ($request->hasFiles()) {
            $data['_files'] = $request->getUploadedFiles();
        }
        
        return $data;
    }

    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $fieldRules, $data);
        }
        
        return empty($this->errors);
    }

    /**
     * Validate individual field
     */
    private function validateField(string $field, $value, $rules, array $allData): void
    {
        // Convert string rules to array
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        foreach ($rules as $rule) {
            $this->applyRule($field, $value, $rule, $allData);
        }
    }

    /**
     * Apply validation rule
     */
    private function applyRule(string $field, $value, string $rule, array $allData): void
    {
        // Parse rule and parameters
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $parameters = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];
        
        // Skip further validation if field already has errors and not required
        if (isset($this->errors[$field]) && $ruleName !== 'required') {
            return;
        }
        
        // Apply the rule
        switch ($ruleName) {
            case 'required':
                $this->validateRequired($field, $value);
                break;
                
            case 'email':
                $this->validateEmail($field, $value);
                break;
                
            case 'string':
                $this->validateString($field, $value);
                break;
                
            case 'integer':
                $this->validateInteger($field, $value);
                break;
                
            case 'numeric':
                $this->validateNumeric($field, $value);
                break;
                
            case 'min':
                $this->validateMin($field, $value, $parameters[0] ?? 0);
                break;
                
            case 'max':
                $this->validateMax($field, $value, $parameters[0] ?? 0);
                break;
                
            case 'between':
                $this->validateBetween($field, $value, $parameters[0] ?? 0, $parameters[1] ?? 0);
                break;
                
            case 'in':
                $this->validateIn($field, $value, $parameters);
                break;
                
            case 'not_in':
                $this->validateNotIn($field, $value, $parameters);
                break;
                
            case 'unique':
                $this->validateUnique($field, $value, $parameters[0] ?? '', $parameters[1] ?? '');
                break;
                
            case 'exists':
                $this->validateExists($field, $value, $parameters[0] ?? '', $parameters[1] ?? '');
                break;
                
            case 'confirmed':
                $this->validateConfirmed($field, $value, $allData);
                break;
                
            case 'same':
                $this->validateSame($field, $value, $parameters[0] ?? '', $allData);
                break;
                
            case 'different':
                $this->validateDifferent($field, $value, $parameters[0] ?? '', $allData);
                break;
                
            case 'regex':
                $this->validateRegex($field, $value, $parameters[0] ?? '');
                break;
                
            case 'date':
                $this->validateDate($field, $value);
                break;
                
            case 'date_format':
                $this->validateDateFormat($field, $value, $parameters[0] ?? 'Y-m-d');
                break;
                
            case 'before':
                $this->validateBefore($field, $value, $parameters[0] ?? '');
                break;
                
            case 'after':
                $this->validateAfter($field, $value, $parameters[0] ?? '');
                break;
                
            case 'url':
                $this->validateUrl($field, $value);
                break;
                
            case 'ip':
                $this->validateIp($field, $value);
                break;
                
            case 'json':
                $this->validateJson($field, $value);
                break;
                
            case 'file':
                $this->validateFile($field, $value);
                break;
                
            case 'image':
                $this->validateImage($field, $value);
                break;
                
            case 'mimes':
                $this->validateMimes($field, $value, $parameters);
                break;
                
            case 'size':
                $this->validateSize($field, $value, $parameters[0] ?? 0);
                break;
                
            case 'password':
                $this->validatePassword($field, $value);
                break;
                
            case 'phone':
                $this->validatePhone($field, $value);
                break;
                
            case 'alpha':
                $this->validateAlpha($field, $value);
                break;
                
            case 'alpha_num':
                $this->validateAlphaNum($field, $value);
                break;
                
            case 'alpha_dash':
                $this->validateAlphaDash($field, $value);
                break;
                
            case 'uuid':
                $this->validateUuid($field, $value);
                break;
                
            default:
                // Check for custom validators
                if (isset($this->validators[$ruleName])) {
                    $validator = $this->validators[$ruleName];
                    if (!$validator($value, $parameters, $allData)) {
                        $this->addError($field, "The {$field} field is invalid.");
                    }
                }
                break;
        }
    }

    /**
     * Validation rule implementations
     */
    private function validateRequired(string $field, $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "The {$field} field is required.");
        }
    }

    private function validateEmail(string $field, $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "The {$field} must be a valid email address.");
        }
    }

    private function validateString(string $field, $value): void
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, "The {$field} must be a string.");
        }
    }

    private function validateInteger(string $field, $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "The {$field} must be an integer.");
        }
    }

    private function validateNumeric(string $field, $value): void
    {
        if ($value !== null && !is_numeric($value)) {
            $this->addError($field, "The {$field} must be numeric.");
        }
    }

    private function validateMin(string $field, $value, $min): void
    {
        if ($value === null) return;
        
        if (is_string($value) && strlen($value) < $min) {
            $this->addError($field, "The {$field} must be at least {$min} characters.");
        } elseif (is_numeric($value) && $value < $min) {
            $this->addError($field, "The {$field} must be at least {$min}.");
        } elseif (is_array($value) && count($value) < $min) {
            $this->addError($field, "The {$field} must have at least {$min} items.");
        }
    }

    private function validateMax(string $field, $value, $max): void
    {
        if ($value === null) return;
        
        if (is_string($value) && strlen($value) > $max) {
            $this->addError($field, "The {$field} may not be greater than {$max} characters.");
        } elseif (is_numeric($value) && $value > $max) {
            $this->addError($field, "The {$field} may not be greater than {$max}.");
        } elseif (is_array($value) && count($value) > $max) {
            $this->addError($field, "The {$field} may not have more than {$max} items.");
        }
    }

    private function validateBetween(string $field, $value, $min, $max): void
    {
        $this->validateMin($field, $value, $min);
        $this->validateMax($field, $value, $max);
    }

    private function validateIn(string $field, $value, array $options): void
    {
        if ($value !== null && !in_array($value, $options)) {
            $this->addError($field, "The selected {$field} is invalid.");
        }
    }

    private function validateNotIn(string $field, $value, array $options): void
    {
        if ($value !== null && in_array($value, $options)) {
            $this->addError($field, "The selected {$field} is invalid.");
        }
    }

    private function validateUnique(string $field, $value, string $table, string $column = ''): void
    {
        if ($value === null) return;
        
        $column = $column ?: $field;
        
        try {
            $db = \App\Utils\Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
            $stmt->execute([$value]);
            
            if ($stmt->fetchColumn() > 0) {
                $this->addError($field, "The {$field} has already been taken.");
            }
        } catch (Exception $e) {
            error_log("Unique validation error: " . $e->getMessage());
        }
    }

    private function validateExists(string $field, $value, string $table, string $column = ''): void
    {
        if ($value === null) return;
        
        $column = $column ?: $field;
        
        try {
            $db = \App\Utils\Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
            $stmt->execute([$value]);
            
            if ($stmt->fetchColumn() == 0) {
                $this->addError($field, "The selected {$field} is invalid.");
            }
        } catch (Exception $e) {
            error_log("Exists validation error: " . $e->getMessage());
        }
    }

    private function validateConfirmed(string $field, $value, array $allData): void
    {
        $confirmField = $field . '_confirmation';
        
        if (!isset($allData[$confirmField]) || $value !== $allData[$confirmField]) {
            $this->addError($field, "The {$field} confirmation does not match.");
        }
    }

    private function validateSame(string $field, $value, string $otherField, array $allData): void
    {
        if (!isset($allData[$otherField]) || $value !== $allData[$otherField]) {
            $this->addError($field, "The {$field} and {$otherField} must match.");
        }
    }

    private function validateDifferent(string $field, $value, string $otherField, array $allData): void
    {
        if (isset($allData[$otherField]) && $value === $allData[$otherField]) {
            $this->addError($field, "The {$field} and {$otherField} must be different.");
        }
    }

    private function validateRegex(string $field, $value, string $pattern): void
    {
        if ($value !== null && !preg_match($pattern, $value)) {
            $this->addError($field, "The {$field} format is invalid.");
        }
    }

    private function validateDate(string $field, $value): void
    {
        if ($value !== null && !strtotime($value)) {
            $this->addError($field, "The {$field} is not a valid date.");
        }
    }

    private function validateDateFormat(string $field, $value, string $format): void
    {
        if ($value !== null) {
            $date = \DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->addError($field, "The {$field} does not match the format {$format}.");
            }
        }
    }

    private function validateBefore(string $field, $value, string $beforeDate): void
    {
        if ($value !== null) {
            $valueTime = strtotime($value);
            $beforeTime = strtotime($beforeDate);
            
            if ($valueTime === false || $beforeTime === false || $valueTime >= $beforeTime) {
                $this->addError($field, "The {$field} must be a date before {$beforeDate}.");
            }
        }
    }

    private function validateAfter(string $field, $value, string $afterDate): void
    {
        if ($value !== null) {
            $valueTime = strtotime($value);
            $afterTime = strtotime($afterDate);
            
            if ($valueTime === false || $afterTime === false || $valueTime <= $afterTime) {
                $this->addError($field, "The {$field} must be a date after {$afterDate}.");
            }
        }
    }

    private function validateUrl(string $field, $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "The {$field} format is invalid.");
        }
    }

    private function validateIp(string $field, $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_IP)) {
            $this->addError($field, "The {$field} must be a valid IP address.");
        }
    }

    private function validateJson(string $field, $value): void
    {
        if ($value !== null) {
            json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError($field, "The {$field} must be a valid JSON string.");
            }
        }
    }

    private function validateFile(string $field, $value): void
    {
        if ($value !== null && !is_uploaded_file($value['tmp_name'] ?? '')) {
            $this->addError($field, "The {$field} must be a file.");
        }
    }

    private function validateImage(string $field, $value): void
    {
        if ($value !== null) {
            if (!is_uploaded_file($value['tmp_name'] ?? '')) {
                $this->addError($field, "The {$field} must be an image.");
                return;
            }
            
            $imageInfo = getimagesize($value['tmp_name']);
            if (!$imageInfo) {
                $this->addError($field, "The {$field} must be an image.");
            }
        }
    }

    private function validateMimes(string $field, $value, array $mimes): void
    {
        if ($value !== null && isset($value['type'])) {
            if (!in_array($value['type'], $mimes)) {
                $allowedTypes = implode(', ', $mimes);
                $this->addError($field, "The {$field} must be a file of type: {$allowedTypes}.");
            }
        }
    }

    private function validateSize(string $field, $value, int $size): void
    {
        if ($value !== null && isset($value['size'])) {
            $maxSize = $size * 1024; // Convert KB to bytes
            if ($value['size'] > $maxSize) {
                $this->addError($field, "The {$field} may not be greater than {$size} kilobytes.");
            }
        }
    }

    private function validatePassword(string $field, $value): void
    {
        if ($value !== null) {
            // Password must be at least 8 characters with uppercase, lowercase, number, and special character
            if (strlen($value) < 8) {
                $this->addError($field, "The {$field} must be at least 8 characters.");
                return;
            }
            
            if (!preg_match('/[a-z]/', $value)) {
                $this->addError($field, "The {$field} must contain at least one lowercase letter.");
                return;
            }
            
            if (!preg_match('/[A-Z]/', $value)) {
                $this->addError($field, "The {$field} must contain at least one uppercase letter.");
                return;
            }
            
            if (!preg_match('/[0-9]/', $value)) {
                $this->addError($field, "The {$field} must contain at least one number.");
                return;
            }
            
            if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
                $this->addError($field, "The {$field} must contain at least one special character.");
            }
        }
    }

    private function validatePhone(string $field, $value): void
    {
        if ($value !== null && !preg_match('/^\+?[1-9]\d{1,14}$/', $value)) {
            $this->addError($field, "The {$field} must be a valid phone number.");
        }
    }

    private function validateAlpha(string $field, $value): void
    {
        if ($value !== null && !preg_match('/^[a-zA-Z]+$/', $value)) {
            $this->addError($field, "The {$field} may only contain letters.");
        }
    }

    private function validateAlphaNum(string $field, $value): void
    {
        if ($value !== null && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->addError($field, "The {$field} may only contain letters and numbers.");
        }
    }

    private function validateAlphaDash(string $field, $value): void
    {
        if ($value !== null && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $this->addError($field, "The {$field} may only contain letters, numbers, dashes, and underscores.");
        }
    }

    private function validateUuid(string $field, $value): void
    {
        if ($value !== null && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            $this->addError($field, "The {$field} must be a valid UUID.");
        }
    }

    /**
     * Add validation error
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Sanitize data based on rules
     */
    private function sanitizeData(array $data, array $rules): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (isset($rules[$key])) {
                $sanitized[$key] = $this->sanitizeValue($value, $rules[$key]);
            } else {
                // Apply basic sanitization to unvalidated fields
                $sanitized[$key] = $this->basicSanitize($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize individual value
     */
    private function sanitizeValue($value, $rules)
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        foreach ($rules as $rule) {
            $ruleName = explode(':', $rule)[0];
            
            switch ($ruleName) {
                case 'string':
                    $value = is_string($value) ? trim($value) : $value;
                    break;
                case 'email':
                    $value = is_string($value) ? strtolower(trim($value)) : $value;
                    break;
                case 'integer':
                    $value = is_numeric($value) ? (int)$value : $value;
                    break;
                case 'numeric':
                    $value = is_numeric($value) ? (float)$value : $value;
                    break;
            }
        }
        
        return $this->basicSanitize($value);
    }

    /**
     * Apply basic sanitization
     */
    private function basicSanitize($value)
    {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if validation has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get first error for a field
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Add custom validator
     */
    public function addValidator(string $name, callable $validator): void
    {
        $this->validators[$name] = $validator;
    }

    /**
     * Return validation failed response
     */
    private function validationFailedResponse(): array
    {
        http_response_code(422);
        return [
            'success' => false,
            'error' => 'Validation failed',
            'errors' => $this->errors,
            'code' => 422
        ];
    }

    /**
     * Return server error response
     */
    private function serverErrorResponse(): array
    {
        http_response_code(500);
        return [
            'success' => false,
            'error' => 'Validation middleware error',
            'code' => 500
        ];
    }

    /**
     * Create validation middleware with rules
     */
    public static function rules(array $rules)
    {
        return function ($request, $next) use ($rules) {
            $middleware = new self();
            return $middleware->handle($request, $next, $rules);
        };
    }

    /**
     * Quick validation helper
     */
    public static function quick(array $data, array $rules): array
    {
        $validator = new self();
        $validator->validate($data, $rules);
        
        return [
            'passes' => !$validator->hasErrors(),
            'errors' => $validator->getErrors()
        ];
    }
}