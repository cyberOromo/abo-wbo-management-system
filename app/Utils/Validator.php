<?php

namespace App\Utils;

/**
 * Validator - Input validation utilities
 * 
 * Provides validation rules, sanitization, and error handling
 * with support for complex validation scenarios and custom rules.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class Validator
{
    private array $data = [];
    private array $rules = [];
    private array $messages = [];
    private array $errors = [];
    private array $customRules = [];

    /**
     * Create validator instance
     */
    public function __construct(array $data = [], array $rules = [], array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = array_merge($this->getDefaultMessages(), $messages);
    }

    /**
     * Static factory method
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }

    /**
     * Validate data against rules
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($fieldRules as $rule) {
                if (!$this->validateRule($field, $value, $rule)) {
                    break; // Stop on first error for this field
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate single rule
     */
    private function validateRule(string $field, $value, string $rule): bool
    {
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];

        // Skip validation if field is nullable and empty
        if ($ruleName !== 'required' && $this->isEmpty($value)) {
            return true;
        }

        // Check for custom rules first
        if (isset($this->customRules[$ruleName])) {
            return $this->validateCustomRule($field, $value, $ruleName, $parameters);
        }

        // Built-in validation rules
        switch ($ruleName) {
            case 'required':
                return $this->validateRequired($field, $value);
            case 'nullable':
                return true; // Always passes
            case 'string':
                return $this->validateString($field, $value);
            case 'integer':
            case 'int':
                return $this->validateInteger($field, $value);
            case 'numeric':
                return $this->validateNumeric($field, $value);
            case 'boolean':
            case 'bool':
                return $this->validateBoolean($field, $value);
            case 'array':
                return $this->validateArray($field, $value);
            case 'email':
                return $this->validateEmail($field, $value);
            case 'url':
                return $this->validateUrl($field, $value);
            case 'date':
                return $this->validateDate($field, $value);
            case 'min':
                return $this->validateMin($field, $value, $parameters[0] ?? 0);
            case 'max':
                return $this->validateMax($field, $value, $parameters[0] ?? PHP_INT_MAX);
            case 'between':
                return $this->validateBetween($field, $value, $parameters[0] ?? 0, $parameters[1] ?? PHP_INT_MAX);
            case 'in':
                return $this->validateIn($field, $value, $parameters);
            case 'not_in':
                return $this->validateNotIn($field, $value, $parameters);
            case 'regex':
                return $this->validateRegex($field, $value, $parameters[0] ?? '');
            case 'alpha':
                return $this->validateAlpha($field, $value);
            case 'alpha_num':
                return $this->validateAlphaNum($field, $value);
            case 'alpha_dash':
                return $this->validateAlphaDash($field, $value);
            case 'confirmed':
                return $this->validateConfirmed($field, $value);
            case 'same':
                return $this->validateSame($field, $value, $parameters[0] ?? '');
            case 'different':
                return $this->validateDifferent($field, $value, $parameters[0] ?? '');
            case 'unique':
                return $this->validateUnique($field, $value, $parameters[0] ?? '', $parameters[1] ?? null);
            case 'exists':
                return $this->validateExists($field, $value, $parameters[0] ?? '', $parameters[1] ?? null);
            case 'phone':
                return $this->validatePhone($field, $value);
            case 'json':
                return $this->validateJson($field, $value);
            case 'ip':
                return $this->validateIp($field, $value);
            case 'ipv4':
                return $this->validateIpv4($field, $value);
            case 'ipv6':
                return $this->validateIpv6($field, $value);
            case 'mac_address':
                return $this->validateMacAddress($field, $value);
            case 'uuid':
                return $this->validateUuid($field, $value);
            case 'file':
                return $this->validateFile($field, $value);
            case 'image':
                return $this->validateImage($field, $value);
            case 'mimes':
                return $this->validateMimes($field, $value, $parameters);
            case 'size':
                return $this->validateSize($field, $value, $parameters[0] ?? 0);
            case 'dimensions':
                return $this->validateDimensions($field, $value, $parameters);
            default:
                return true; // Unknown rules pass by default
        }
    }

    /**
     * Validation rule implementations
     */
    private function validateRequired(string $field, $value): bool
    {
        if ($this->isEmpty($value)) {
            $this->addError($field, 'required');
            return false;
        }
        return true;
    }

    private function validateString(string $field, $value): bool
    {
        if (!is_string($value)) {
            $this->addError($field, 'string');
            return false;
        }
        return true;
    }

    private function validateInteger(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'integer');
            return false;
        }
        return true;
    }

    private function validateNumeric(string $field, $value): bool
    {
        if (!is_numeric($value)) {
            $this->addError($field, 'numeric');
            return false;
        }
        return true;
    }

    private function validateBoolean(string $field, $value): bool
    {
        if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
            $this->addError($field, 'boolean');
            return false;
        }
        return true;
    }

    private function validateArray(string $field, $value): bool
    {
        if (!is_array($value)) {
            $this->addError($field, 'array');
            return false;
        }
        return true;
    }

    private function validateEmail(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
            return false;
        }
        return true;
    }

    private function validateUrl(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
            return false;
        }
        return true;
    }

    private function validateDate(string $field, $value): bool
    {
        if (!strtotime($value)) {
            $this->addError($field, 'date');
            return false;
        }
        return true;
    }

    private function validateMin(string $field, $value, $min): bool
    {
        if (is_string($value) && strlen($value) < $min) {
            $this->addError($field, 'min', ['min' => $min]);
            return false;
        }
        if (is_numeric($value) && $value < $min) {
            $this->addError($field, 'min', ['min' => $min]);
            return false;
        }
        if (is_array($value) && count($value) < $min) {
            $this->addError($field, 'min', ['min' => $min]);
            return false;
        }
        return true;
    }

    private function validateMax(string $field, $value, $max): bool
    {
        if (is_string($value) && strlen($value) > $max) {
            $this->addError($field, 'max', ['max' => $max]);
            return false;
        }
        if (is_numeric($value) && $value > $max) {
            $this->addError($field, 'max', ['max' => $max]);
            return false;
        }
        if (is_array($value) && count($value) > $max) {
            $this->addError($field, 'max', ['max' => $max]);
            return false;
        }
        return true;
    }

    private function validateBetween(string $field, $value, $min, $max): bool
    {
        if (is_string($value)) {
            $length = strlen($value);
            if ($length < $min || $length > $max) {
                $this->addError($field, 'between', ['min' => $min, 'max' => $max]);
                return false;
            }
        } elseif (is_numeric($value)) {
            if ($value < $min || $value > $max) {
                $this->addError($field, 'between', ['min' => $min, 'max' => $max]);
                return false;
            }
        } elseif (is_array($value)) {
            $count = count($value);
            if ($count < $min || $count > $max) {
                $this->addError($field, 'between', ['min' => $min, 'max' => $max]);
                return false;
            }
        }
        return true;
    }

    private function validateIn(string $field, $value, array $allowed): bool
    {
        if (!in_array($value, $allowed)) {
            $this->addError($field, 'in', ['values' => implode(', ', $allowed)]);
            return false;
        }
        return true;
    }

    private function validateNotIn(string $field, $value, array $forbidden): bool
    {
        if (in_array($value, $forbidden)) {
            $this->addError($field, 'not_in', ['values' => implode(', ', $forbidden)]);
            return false;
        }
        return true;
    }

    private function validateRegex(string $field, $value, string $pattern): bool
    {
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'regex');
            return false;
        }
        return true;
    }

    private function validateAlpha(string $field, $value): bool
    {
        if (!preg_match('/^[a-zA-Z]+$/', $value)) {
            $this->addError($field, 'alpha');
            return false;
        }
        return true;
    }

    private function validateAlphaNum(string $field, $value): bool
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->addError($field, 'alpha_num');
            return false;
        }
        return true;
    }

    private function validateAlphaDash(string $field, $value): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $this->addError($field, 'alpha_dash');
            return false;
        }
        return true;
    }

    private function validateConfirmed(string $field, $value): bool
    {
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->data[$confirmationField] ?? null;
        
        if ($value !== $confirmationValue) {
            $this->addError($field, 'confirmed');
            return false;
        }
        return true;
    }

    private function validateSame(string $field, $value, string $otherField): bool
    {
        $otherValue = $this->data[$otherField] ?? null;
        
        if ($value !== $otherValue) {
            $this->addError($field, 'same', ['other' => $otherField]);
            return false;
        }
        return true;
    }

    private function validateDifferent(string $field, $value, string $otherField): bool
    {
        $otherValue = $this->data[$otherField] ?? null;
        
        if ($value === $otherValue) {
            $this->addError($field, 'different', ['other' => $otherField]);
            return false;
        }
        return true;
    }

    private function validatePhone(string $field, $value): bool
    {
        if (!preg_match('/^[+]?[0-9\s\-\(\)]{10,20}$/', $value)) {
            $this->addError($field, 'phone');
            return false;
        }
        return true;
    }

    private function validateJson(string $field, $value): bool
    {
        if (!is_string($value)) {
            $this->addError($field, 'json');
            return false;
        }
        
        json_decode($value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError($field, 'json');
            return false;
        }
        return true;
    }

    private function validateIp(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            $this->addError($field, 'ip');
            return false;
        }
        return true;
    }

    private function validateIpv4(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->addError($field, 'ipv4');
            return false;
        }
        return true;
    }

    private function validateIpv6(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->addError($field, 'ipv6');
            return false;
        }
        return true;
    }

    private function validateMacAddress(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_MAC)) {
            $this->addError($field, 'mac_address');
            return false;
        }
        return true;
    }

    private function validateUuid(string $field, $value): bool
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'uuid');
            return false;
        }
        return true;
    }

    private function validateFile(string $field, $value): bool
    {
        if (!is_array($value) || !isset($value['tmp_name']) || !is_uploaded_file($value['tmp_name'])) {
            $this->addError($field, 'file');
            return false;
        }
        return true;
    }

    private function validateImage(string $field, $value): bool
    {
        if (!$this->validateFile($field, $value)) {
            return false;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($value['type'], $allowedTypes)) {
            $this->addError($field, 'image');
            return false;
        }
        return true;
    }

    private function validateMimes(string $field, $value, array $mimes): bool
    {
        if (!$this->validateFile($field, $value)) {
            return false;
        }
        
        if (!in_array($value['type'], $mimes)) {
            $this->addError($field, 'mimes', ['values' => implode(', ', $mimes)]);
            return false;
        }
        return true;
    }

    private function validateSize(string $field, $value, int $size): bool
    {
        if (is_array($value) && isset($value['size'])) {
            // File size validation (in KB)
            if ($value['size'] > $size * 1024) {
                $this->addError($field, 'size', ['size' => $size]);
                return false;
            }
        } elseif (is_string($value)) {
            if (strlen($value) !== $size) {
                $this->addError($field, 'size', ['size' => $size]);
                return false;
            }
        }
        return true;
    }

    private function validateDimensions(string $field, $value, array $constraints): bool
    {
        if (!$this->validateImage($field, $value)) {
            return false;
        }
        
        $imageInfo = getimagesize($value['tmp_name']);
        if (!$imageInfo) {
            $this->addError($field, 'dimensions');
            return false;
        }
        
        [$width, $height] = $imageInfo;
        
        foreach ($constraints as $constraint) {
            [$key, $val] = explode('=', $constraint, 2);
            $val = (int) $val;
            
            switch ($key) {
                case 'min_width':
                    if ($width < $val) {
                        $this->addError($field, 'dimensions');
                        return false;
                    }
                    break;
                case 'max_width':
                    if ($width > $val) {
                        $this->addError($field, 'dimensions');
                        return false;
                    }
                    break;
                case 'min_height':
                    if ($height < $val) {
                        $this->addError($field, 'dimensions');
                        return false;
                    }
                    break;
                case 'max_height':
                    if ($height > $val) {
                        $this->addError($field, 'dimensions');
                        return false;
                    }
                    break;
                case 'width':
                    if ($width !== $val) {
                        $this->addError($field, 'dimensions');
                        return false;
                    }
                    break;
                case 'height':
                    if ($height !== $val) {
                        $this->addError($field, 'dimensions');
                        return false;
                    }
                    break;
            }
        }
        
        return true;
    }

    private function validateUnique(string $field, $value, string $table, ?string $column = null): bool
    {
        // This would require database connection
        // Placeholder implementation
        return true;
    }

    private function validateExists(string $field, $value, string $table, ?string $column = null): bool
    {
        // This would require database connection
        // Placeholder implementation
        return true;
    }

    /**
     * Helper methods
     */
    private function isEmpty($value): bool
    {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }

    private function addError(string $field, string $rule, array $parameters = []): void
    {
        $message = $this->messages[$rule] ?? "The {$field} field is invalid.";
        
        // Replace placeholders
        $message = str_replace(':field', $field, $message);
        foreach ($parameters as $key => $value) {
            $message = str_replace(":{$key}", $value, $message);
        }
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get first error for field
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Add custom validation rule
     */
    public function addCustomRule(string $name, callable $callback): void
    {
        $this->customRules[$name] = $callback;
    }

    /**
     * Validate custom rule
     */
    private function validateCustomRule(string $field, $value, string $ruleName, array $parameters): bool
    {
        $callback = $this->customRules[$ruleName];
        $result = $callback($value, $parameters, $field, $this->data);
        
        if (!$result) {
            $this->addError($field, $ruleName);
            return false;
        }
        
        return true;
    }

    /**
     * Get default error messages
     */
    private function getDefaultMessages(): array
    {
        return [
            'required' => 'The :field field is required.',
            'string' => 'The :field must be a string.',
            'integer' => 'The :field must be an integer.',
            'numeric' => 'The :field must be a number.',
            'boolean' => 'The :field must be true or false.',
            'array' => 'The :field must be an array.',
            'email' => 'The :field must be a valid email address.',
            'url' => 'The :field must be a valid URL.',
            'date' => 'The :field must be a valid date.',
            'min' => 'The :field must be at least :min.',
            'max' => 'The :field may not be greater than :max.',
            'between' => 'The :field must be between :min and :max.',
            'in' => 'The selected :field is invalid.',
            'not_in' => 'The selected :field is invalid.',
            'regex' => 'The :field format is invalid.',
            'alpha' => 'The :field may only contain letters.',
            'alpha_num' => 'The :field may only contain letters and numbers.',
            'alpha_dash' => 'The :field may only contain letters, numbers, dashes and underscores.',
            'confirmed' => 'The :field confirmation does not match.',
            'same' => 'The :field and :other must match.',
            'different' => 'The :field and :other must be different.',
            'phone' => 'The :field must be a valid phone number.',
            'json' => 'The :field must be a valid JSON string.',
            'ip' => 'The :field must be a valid IP address.',
            'ipv4' => 'The :field must be a valid IPv4 address.',
            'ipv6' => 'The :field must be a valid IPv6 address.',
            'mac_address' => 'The :field must be a valid MAC address.',
            'uuid' => 'The :field must be a valid UUID.',
            'file' => 'The :field must be a file.',
            'image' => 'The :field must be an image.',
            'mimes' => 'The :field must be a file of type: :values.',
            'size' => 'The :field must be :size.',
            'dimensions' => 'The :field has invalid image dimensions.'
        ];
    }

    /**
     * Sanitize input data
     */
    public function sanitize(): array
    {
        $sanitized = [];
        
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Quick validation static method
     */
    public static function quick(array $data, array $rules, array $messages = []): array
    {
        $validator = new self($data, $rules, $messages);
        
        return [
            'valid' => $validator->validate(),
            'errors' => $validator->errors(),
            'sanitized' => $validator->sanitize()
        ];
    }
}