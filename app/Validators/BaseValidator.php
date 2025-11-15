<?php

namespace App\Validators;

/**
 * BaseValidator - Abstract base class for input validation
 * 
 * Provides common validation methods and a foundation for all
 * specific validators. Includes rule parsing, error handling,
 * and sanitization methods.
 * 
 * @package App\Validators
 * @version 1.0.0
 */
abstract class BaseValidator
{
    protected array $data = [];
    protected array $rules = [];
    protected array $messages = [];
    protected array $errors = [];
    protected array $sanitizedData = [];
    
    /**
     * Create validator instance
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->defineRules();
        $this->defineMessages();
    }

    /**
     * Define validation rules - must be implemented by subclasses
     */
    abstract protected function defineRules(): void;

    /**
     * Define custom error messages
     */
    protected function defineMessages(): void
    {
        $this->messages = [
            'required' => 'The :field field is required.',
            'email' => 'The :field must be a valid email address.',
            'min' => 'The :field must be at least :min characters.',
            'max' => 'The :field may not be greater than :max characters.',
            'numeric' => 'The :field must be a number.',
            'integer' => 'The :field must be an integer.',
            'boolean' => 'The :field must be true or false.',
            'date' => 'The :field must be a valid date.',
            'url' => 'The :field must be a valid URL.',
            'regex' => 'The :field format is invalid.',
            'in' => 'The selected :field is invalid.',
            'unique' => 'The :field has already been taken.',
            'confirmed' => 'The :field confirmation does not match.',
            'file' => 'The :field must be a file.',
            'image' => 'The :field must be an image.',
            'mimes' => 'The :field must be a file of type: :values.',
            'size' => 'The :field must be exactly :size.',
            'between' => 'The :field must be between :min and :max.',
            'alpha' => 'The :field may only contain letters.',
            'alpha_num' => 'The :field may only contain letters and numbers.',
            'array' => 'The :field must be an array.',
            'json' => 'The :field must be a valid JSON string.'
        ];
    }

    /**
     * Validate input data
     */
    public function validate(array $data = null): bool
    {
        if ($data !== null) {
            $this->data = $data;
        }

        $this->errors = [];
        $this->sanitizedData = [];

        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($fieldRules as $rule) {
                if (!$this->validateRule($field, $value, $rule)) {
                    // Stop on first error for this field unless 'continue_on_error' is set
                    if (!in_array('continue_on_error', $fieldRules)) {
                        break;
                    }
                }
            }

            // Sanitize the field
            $this->sanitizedData[$field] = $this->sanitizeField($field, $value, $fieldRules);
        }

        return empty($this->errors);
    }

    /**
     * Validate a single rule
     */
    protected function validateRule(string $field, $value, string $rule): bool
    {
        // Parse rule and parameters
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];

        // Skip validation if field is not required and value is empty
        if ($ruleName !== 'required' && $this->isEmpty($value) && !in_array('required', $this->getRulesForField($field))) {
            return true;
        }

        switch ($ruleName) {
            case 'required':
                return $this->validateRequired($field, $value);
            case 'email':
                return $this->validateEmail($field, $value);
            case 'min':
                return $this->validateMin($field, $value, $parameters[0]);
            case 'max':
                return $this->validateMax($field, $value, $parameters[0]);
            case 'numeric':
                return $this->validateNumeric($field, $value);
            case 'integer':
                return $this->validateInteger($field, $value);
            case 'boolean':
                return $this->validateBoolean($field, $value);
            case 'date':
                return $this->validateDate($field, $value);
            case 'url':
                return $this->validateUrl($field, $value);
            case 'regex':
                return $this->validateRegex($field, $value, $parameters[0]);
            case 'in':
                return $this->validateIn($field, $value, $parameters);
            case 'unique':
                return $this->validateUnique($field, $value, $parameters[0], $parameters[1] ?? null);
            case 'confirmed':
                return $this->validateConfirmed($field, $value);
            case 'file':
                return $this->validateFile($field, $value);
            case 'image':
                return $this->validateImage($field, $value);
            case 'mimes':
                return $this->validateMimes($field, $value, $parameters);
            case 'size':
                return $this->validateSize($field, $value, $parameters[0]);
            case 'between':
                return $this->validateBetween($field, $value, $parameters[0], $parameters[1]);
            case 'alpha':
                return $this->validateAlpha($field, $value);
            case 'alpha_num':
                return $this->validateAlphaNum($field, $value);
            case 'array':
                return $this->validateArray($field, $value);
            case 'json':
                return $this->validateJson($field, $value);
            default:
                return true; // Unknown rules pass by default
        }
    }

    /**
     * Validation methods
     */
    protected function validateRequired(string $field, $value): bool
    {
        if ($this->isEmpty($value)) {
            $this->addError($field, 'required');
            return false;
        }
        return true;
    }

    protected function validateEmail(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
            return false;
        }
        return true;
    }

    protected function validateMin(string $field, $value, $min): bool
    {
        if (is_string($value) && strlen($value) < $min) {
            $this->addError($field, 'min', ['min' => $min]);
            return false;
        }
        if (is_numeric($value) && $value < $min) {
            $this->addError($field, 'min', ['min' => $min]);
            return false;
        }
        return true;
    }

    protected function validateMax(string $field, $value, $max): bool
    {
        if (is_string($value) && strlen($value) > $max) {
            $this->addError($field, 'max', ['max' => $max]);
            return false;
        }
        if (is_numeric($value) && $value > $max) {
            $this->addError($field, 'max', ['max' => $max]);
            return false;
        }
        return true;
    }

    protected function validateNumeric(string $field, $value): bool
    {
        if (!is_numeric($value)) {
            $this->addError($field, 'numeric');
            return false;
        }
        return true;
    }

    protected function validateInteger(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'integer');
            return false;
        }
        return true;
    }

    protected function validateBoolean(string $field, $value): bool
    {
        if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
            $this->addError($field, 'boolean');
            return false;
        }
        return true;
    }

    protected function validateDate(string $field, $value): bool
    {
        if (!strtotime($value)) {
            $this->addError($field, 'date');
            return false;
        }
        return true;
    }

    protected function validateUrl(string $field, $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
            return false;
        }
        return true;
    }

    protected function validateRegex(string $field, $value, string $pattern): bool
    {
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'regex');
            return false;
        }
        return true;
    }

    protected function validateIn(string $field, $value, array $allowedValues): bool
    {
        if (!in_array($value, $allowedValues)) {
            $this->addError($field, 'in', ['values' => implode(', ', $allowedValues)]);
            return false;
        }
        return true;
    }

    protected function validateUnique(string $field, $value, string $table, ?string $column = null): bool
    {
        $column = $column ?? $field;
        
        // This would typically check database uniqueness
        // Implementation depends on your database setup
        // For now, returning true as placeholder
        return true;
    }

    protected function validateConfirmed(string $field, $value): bool
    {
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->data[$confirmationField] ?? null;
        
        if ($value !== $confirmationValue) {
            $this->addError($field, 'confirmed');
            return false;
        }
        return true;
    }

    protected function validateFile(string $field, $value): bool
    {
        if (!is_array($value) || !isset($value['tmp_name'])) {
            $this->addError($field, 'file');
            return false;
        }
        return true;
    }

    protected function validateImage(string $field, $value): bool
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

    protected function validateMimes(string $field, $value, array $allowedMimes): bool
    {
        if (!$this->validateFile($field, $value)) {
            return false;
        }
        
        if (!in_array($value['type'], $allowedMimes)) {
            $this->addError($field, 'mimes', ['values' => implode(', ', $allowedMimes)]);
            return false;
        }
        return true;
    }

    protected function validateSize(string $field, $value, $expectedSize): bool
    {
        if (is_string($value) && strlen($value) !== (int)$expectedSize) {
            $this->addError($field, 'size', ['size' => $expectedSize]);
            return false;
        }
        return true;
    }

    protected function validateBetween(string $field, $value, $min, $max): bool
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
        }
        return true;
    }

    protected function validateAlpha(string $field, $value): bool
    {
        if (!preg_match('/^[a-zA-Z]+$/', $value)) {
            $this->addError($field, 'alpha');
            return false;
        }
        return true;
    }

    protected function validateAlphaNum(string $field, $value): bool
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->addError($field, 'alpha_num');
            return false;
        }
        return true;
    }

    protected function validateArray(string $field, $value): bool
    {
        if (!is_array($value)) {
            $this->addError($field, 'array');
            return false;
        }
        return true;
    }

    protected function validateJson(string $field, $value): bool
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

    /**
     * Sanitization methods
     */
    protected function sanitizeField(string $field, $value, array $rules)
    {
        if ($this->isEmpty($value)) {
            return $value;
        }

        // Apply sanitization based on rules
        foreach ($rules as $rule) {
            $ruleName = explode(':', $rule)[0];
            
            switch ($ruleName) {
                case 'email':
                    $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                    break;
                case 'url':
                    $value = filter_var($value, FILTER_SANITIZE_URL);
                    break;
                case 'integer':
                    $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                    break;
                case 'numeric':
                    $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    break;
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    break;
                case 'alpha':
                    $value = preg_replace('/[^a-zA-Z]/', '', $value);
                    break;
                case 'alpha_num':
                    $value = preg_replace('/[^a-zA-Z0-9]/', '', $value);
                    break;
            }
        }

        // Always sanitize strings for basic security
        if (is_string($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }

    /**
     * Helper methods
     */
    protected function isEmpty($value): bool
    {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }

    protected function getRulesForField(string $field): array
    {
        $rules = $this->rules[$field] ?? [];
        return is_string($rules) ? explode('|', $rules) : $rules;
    }

    protected function addError(string $field, string $rule, array $parameters = []): void
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
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get sanitized data
     */
    public function getSanitizedData(): array
    {
        return $this->sanitizedData;
    }

    /**
     * Get specific sanitized value
     */
    public function getSanitized(string $field)
    {
        return $this->sanitizedData[$field] ?? null;
    }

    /**
     * Set custom message for a field
     */
    public function setMessage(string $field, string $rule, string $message): self
    {
        $this->messages["{$field}.{$rule}"] = $message;
        return $this;
    }

    /**
     * Set multiple custom messages
     */
    public function setMessages(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);
        return $this;
    }
}