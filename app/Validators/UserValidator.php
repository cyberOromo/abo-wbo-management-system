<?php

namespace App\Validators;

/**
 * UserValidator - Validates user-related input data
 * 
 * Handles validation for user registration, profile updates,
 * authentication, and user management operations.
 * 
 * @package App\Validators
 * @version 1.0.0
 */
class UserValidator extends BaseValidator
{
    /**
     * Define validation rules for user operations
     */
    protected function defineRules(): void
    {
        // Default rules - can be overridden by specific validation methods
        $this->rules = [];
    }

    /**
     * Validate user registration data
     */
    public function validateRegistration(array $data): bool
    {
        $this->rules = [
            'first_name' => 'required|alpha|min:2|max:50',
            'middle_name' => 'alpha|max:50',
            'last_name' => 'required|alpha|min:2|max:50',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|regex:/^[+]?[0-9]{10,15}$/|unique:users,phone',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'password' => 'required|min:8|max:255|confirmed',
            'password_confirmation' => 'required',
            'level' => 'required|in:global,godina,gamta,gurmu',
            'level_id' => 'required|integer|min:1',
            'preferred_language' => 'in:en,oro',
            'terms_accepted' => 'required|boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate user login data
     */
    public function validateLogin(array $data): bool
    {
        $this->rules = [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'remember_me' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate profile update data
     */
    public function validateProfileUpdate(array $data, int $userId): bool
    {
        $this->rules = [
            'first_name' => 'required|alpha|min:2|max:50',
            'middle_name' => 'alpha|max:50',
            'last_name' => 'required|alpha|min:2|max:50',
            'phone' => 'required|regex:/^[+]?[0-9]{10,15}$/',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'preferred_language' => 'in:en,oro',
            'bio' => 'max:1000',
            'skills' => 'array',
            'interests' => 'array'
        ];

        // For updates, we need to handle unique validation differently
        // This is a simplified version - in real implementation, you'd check uniqueness excluding current user
        if (isset($data['email'])) {
            $this->rules['email'] = 'required|email|max:255';
        }

        return $this->validate($data);
    }

    /**
     * Validate password change data
     */
    public function validatePasswordChange(array $data): bool
    {
        $this->rules = [
            'current_password' => 'required|min:8',
            'new_password' => 'required|min:8|max:255|confirmed',
            'new_password_confirmation' => 'required'
        ];

        return $this->validate($data);
    }

    /**
     * Validate password reset request
     */
    public function validatePasswordResetRequest(array $data): bool
    {
        $this->rules = [
            'email' => 'required|email|max:255'
        ];

        return $this->validate($data);
    }

    /**
     * Validate password reset data
     */
    public function validatePasswordReset(array $data): bool
    {
        $this->rules = [
            'token' => 'required|min:64|max:64',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|max:255|confirmed',
            'password_confirmation' => 'required'
        ];

        return $this->validate($data);
    }

    /**
     * Validate email verification
     */
    public function validateEmailVerification(array $data): bool
    {
        $this->rules = [
            'token' => 'required|min:64|max:64',
            'email' => 'required|email|max:255'
        ];

        return $this->validate($data);
    }

    /**
     * Validate phone verification
     */
    public function validatePhoneVerification(array $data): bool
    {
        $this->rules = [
            'phone' => 'required|regex:/^[+]?[0-9]{10,15}$/',
            'verification_code' => 'required|numeric|min:100000|max:999999'
        ];

        return $this->validate($data);
    }

    /**
     * Validate user search/filter data
     */
    public function validateUserSearch(array $data): bool
    {
        $this->rules = [
            'search' => 'max:255',
            'level' => 'in:global,godina,gamta,gurmu',
            'level_id' => 'integer|min:1',
            'status' => 'in:pending,active,suspended,banned',
            'gender' => 'in:male,female,other',
            'age_from' => 'integer|min:18|max:120',
            'age_to' => 'integer|min:18|max:120',
            'registration_from' => 'date',
            'registration_to' => 'date',
            'sort_by' => 'in:name,email,registration_date,last_active',
            'sort_order' => 'in:asc,desc',
            'per_page' => 'integer|min:10|max:100'
        ];

        return $this->validate($data);
    }

    /**
     * Validate user status update
     */
    public function validateStatusUpdate(array $data): bool
    {
        $this->rules = [
            'status' => 'required|in:active,suspended,banned',
            'reason' => 'required_if:status,suspended,banned|max:500',
            'suspended_until' => 'required_if:status,suspended|date'
        ];

        return $this->validate($data);
    }

    /**
     * Validate user position assignment
     */
    public function validatePositionAssignment(array $data): bool
    {
        $this->rules = [
            'position_id' => 'required|integer|min:1',
            'effective_date' => 'required|date',
            'notes' => 'max:500'
        ];

        return $this->validate($data);
    }

    /**
     * Validate user avatar upload
     */
    public function validateAvatarUpload(array $data): bool
    {
        $this->rules = [
            'avatar' => 'required|file|image|mimes:image/jpeg,image/png,image/gif|max:2048'
        ];

        return $this->validate($data);
    }

    /**
     * Validate bulk user import data
     */
    public function validateBulkImport(array $data): bool
    {
        $this->rules = [
            'import_file' => 'required|file|mimes:text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'level' => 'required|in:global,godina,gamta,gurmu',
            'level_id' => 'required|integer|min:1',
            'send_notifications' => 'boolean',
            'auto_activate' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Define custom error messages for user validation
     */
    protected function defineMessages(): void
    {
        parent::defineMessages();
        
        $this->messages = array_merge($this->messages, [
            'first_name.required' => 'First name is required.',
            'first_name.alpha' => 'First name may only contain letters.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.max' => 'First name may not exceed 50 characters.',
            
            'last_name.required' => 'Last name is required.',
            'last_name.alpha' => 'Last name may only contain letters.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.max' => 'Last name may not exceed 50 characters.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please provide a valid phone number.',
            'phone.unique' => 'This phone number is already registered.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.date' => 'Please provide a valid date of birth.',
            
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Please select a valid gender option.',
            
            'level.required' => 'Organization level is required.',
            'level.in' => 'Please select a valid organization level.',
            
            'level_id.required' => 'Organization unit is required.',
            'level_id.integer' => 'Please select a valid organization unit.',
            
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.boolean' => 'Please accept or decline the terms and conditions.',
            
            'verification_code.required' => 'Verification code is required.',
            'verification_code.numeric' => 'Verification code must be numeric.',
            'verification_code.min' => 'Verification code must be 6 digits.',
            'verification_code.max' => 'Verification code must be 6 digits.',
            
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters long.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            
            'avatar.required' => 'Please select an image file.',
            'avatar.image' => 'The uploaded file must be an image.',
            'avatar.mimes' => 'Avatar must be a JPEG, PNG, or GIF image.',
            'avatar.max' => 'Avatar size may not exceed 2MB.',
            
            'reason.required_if' => 'Please provide a reason for this status change.',
            'suspended_until.required_if' => 'Please specify the suspension end date.',
            
            'import_file.required' => 'Please select a file to import.',
            'import_file.mimes' => 'Import file must be CSV or Excel format.'
        ]);
    }

    /**
     * Validate individual user data from bulk import
     */
    public function validateImportRow(array $rowData, int $rowNumber = 0): bool
    {
        $this->rules = [
            'first_name' => 'required|alpha|min:2|max:50',
            'last_name' => 'required|alpha|min:2|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[+]?[0-9]{10,15}$/',
            'date_of_birth' => 'date',
            'gender' => 'in:male,female,other'
        ];

        $result = $this->validate($rowData);
        
        // Add row number to error messages for bulk import
        if (!$result && $rowNumber > 0) {
            $modifiedErrors = [];
            foreach ($this->errors as $field => $messages) {
                $modifiedErrors[$field] = array_map(function($message) use ($rowNumber) {
                    return "Row {$rowNumber}: {$message}";
                }, $messages);
            }
            $this->errors = $modifiedErrors;
        }
        
        return $result;
    }

    /**
     * Validate two-factor authentication setup
     */
    public function validateTwoFactorSetup(array $data): bool
    {
        $this->rules = [
            'secret' => 'required|min:16|max:32',
            'verification_code' => 'required|numeric|min:100000|max:999999',
            'backup_codes' => 'array|min:8'
        ];

        return $this->validate($data);
    }

    /**
     * Validate two-factor authentication verification
     */
    public function validateTwoFactorVerification(array $data): bool
    {
        $this->rules = [
            'verification_code' => 'required|numeric|min:100000|max:999999'
        ];

        return $this->validate($data);
    }

    /**
     * Custom validation for password strength
     */
    protected function validateStrongPassword(string $field, $value): bool
    {
        if (strlen($value) < 8) {
            $this->addError($field, 'Password must be at least 8 characters long.');
            return false;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $this->addError($field, 'Password must contain at least one uppercase letter.');
            return false;
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->addError($field, 'Password must contain at least one lowercase letter.');
            return false;
        }

        if (!preg_match('/[0-9]/', $value)) {
            $this->addError($field, 'Password must contain at least one number.');
            return false;
        }

        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $this->addError($field, 'Password must contain at least one special character.');
            return false;
        }

        return true;
    }
}