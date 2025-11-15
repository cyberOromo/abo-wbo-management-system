<?php

/**
 * English Authentication Translations
 * 
 * Authentication-related messages, labels, and error messages
 * for login, registration, password management, and security.
 * 
 * @package Lang\EN
 * @version 1.0.0
 */

return [
    // Authentication Pages
    'login' => [
        'title' => 'Login',
        'subtitle' => 'Sign in to your ABO-WBO account',
        'email_label' => 'Email Address',
        'password_label' => 'Password',
        'remember_me' => 'Remember me',
        'forgot_password' => 'Forgot password?',
        'login_button' => 'Sign In',
        'no_account' => "Don't have an account?",
        'create_account' => 'Create account',
        'social_login' => 'Or sign in with',
        'welcome_back' => 'Welcome back!',
        'please_login' => 'Please login to continue'
    ],

    'register' => [
        'title' => 'Register',
        'subtitle' => 'Create your ABO-WBO account',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'middle_name' => 'Middle Name (Optional)',
        'email_label' => 'Email Address',
        'phone_label' => 'Phone Number',
        'password_label' => 'Password',
        'confirm_password' => 'Confirm Password',
        'date_of_birth' => 'Date of Birth',
        'gender' => 'Gender',
        'address' => 'Address',
        'city' => 'City',
        'country' => 'Country',
        'select_gurmu' => 'Select Gurmu',
        'select_position' => 'Select Position (Optional)',
        'language_preference' => 'Language Preference',
        'terms_agreement' => 'I agree to the Terms of Service and Privacy Policy',
        'register_button' => 'Create Account',
        'already_account' => 'Already have an account?',
        'sign_in' => 'Sign in',
        'registration_info' => 'Your account will require approval from your Gurmu leadership.',
        'required_fields' => 'Fields marked with * are required'
    ],

    'password_reset' => [
        'title' => 'Reset Password',
        'subtitle' => 'Forgot your password? No problem.',
        'instruction' => 'Enter your email address and we\'ll send you a password reset link.',
        'email_label' => 'Email Address',
        'send_reset_link' => 'Send Password Reset Link',
        'back_to_login' => 'Back to login',
        'reset_sent' => 'Password reset link sent!',
        'reset_sent_message' => 'We have sent a password reset link to your email address.',
        'reset_title' => 'Reset Your Password',
        'new_password' => 'New Password',
        'confirm_new_password' => 'Confirm New Password',
        'reset_password_button' => 'Reset Password',
        'token_expired' => 'This password reset token has expired.',
        'token_invalid' => 'This password reset token is invalid.'
    ],

    'email_verification' => [
        'title' => 'Verify Email',
        'subtitle' => 'Please verify your email address',
        'instruction' => 'We have sent a verification link to your email address. Please click the link to verify your account.',
        'resend_verification' => 'Resend verification email',
        'verification_sent' => 'Verification email sent!',
        'already_verified' => 'Your email is already verified.',
        'verification_success' => 'Email verified successfully!',
        'verification_failed' => 'Email verification failed.',
        'invalid_link' => 'Invalid verification link.',
        'expired_link' => 'Verification link has expired.'
    ],

    'two_factor' => [
        'title' => 'Two-Factor Authentication',
        'subtitle' => 'Enter your authentication code',
        'instruction' => 'Please enter the 6-digit code from your authenticator app.',
        'code_label' => 'Authentication Code',
        'verify_button' => 'Verify',
        'backup_codes' => 'Use backup code',
        'remember_device' => 'Remember this device for 30 days',
        'invalid_code' => 'Invalid authentication code.',
        'setup_title' => 'Setup Two-Factor Authentication',
        'setup_instruction' => 'Scan the QR code with your authenticator app.',
        'setup_manual' => 'Or enter this code manually:',
        'setup_verify' => 'Enter a code from your app to verify setup:',
        'setup_complete' => 'Two-factor authentication enabled successfully!',
        'backup_codes_title' => 'Backup Codes',
        'backup_codes_instruction' => 'Save these backup codes in a safe place. You can use them to access your account if you lose your authenticator device.',
        'disable_2fa' => 'Disable Two-Factor Authentication',
        'disable_confirm' => 'Are you sure you want to disable two-factor authentication?'
    ],

    // User Status & Account States
    'account_status' => [
        'pending' => 'Account Pending Approval',
        'pending_message' => 'Your account is awaiting approval from your Gurmu leadership.',
        'approved' => 'Account Approved',
        'approved_message' => 'Your account has been approved and is active.',
        'suspended' => 'Account Suspended',
        'suspended_message' => 'Your account has been suspended. Please contact support.',
        'inactive' => 'Account Inactive',
        'inactive_message' => 'Your account is inactive. Please contact your administrator.',
        'email_unverified' => 'Email Not Verified',
        'email_unverified_message' => 'Please verify your email address to access all features.',
        'approval_workflow' => [
            'gurmu_pending' => 'Pending Gurmu Approval',
            'gamta_pending' => 'Pending Gamta Approval',
            'godina_pending' => 'Pending Godina Approval',
            'global_pending' => 'Pending Global Approval',
            'gurmu_approved' => 'Approved by Gurmu',
            'gamta_approved' => 'Approved by Gamta',
            'godina_approved' => 'Approved by Godina',
            'global_approved' => 'Approved by Global Leadership'
        ]
    ],

    // Authentication Messages
    'messages' => [
        'login_successful' => 'Login successful. Welcome back!',
        'login_failed' => 'Login failed. Please check your credentials.',
        'logout_successful' => 'You have been logged out successfully.',
        'registration_successful' => 'Registration successful! Please check your email for verification.',
        'registration_failed' => 'Registration failed. Please try again.',
        'password_reset_sent' => 'Password reset link has been sent to your email.',
        'password_reset_successful' => 'Your password has been reset successfully.',
        'password_reset_failed' => 'Password reset failed. Please try again.',
        'email_verified' => 'Your email has been verified successfully.',
        'email_verification_failed' => 'Email verification failed.',
        'account_locked' => 'Your account has been locked due to too many failed login attempts.',
        'session_expired' => 'Your session has expired. Please login again.',
        'unauthorized_access' => 'Unauthorized access. Please login to continue.',
        'insufficient_permissions' => 'You do not have permission to access this resource.',
        'account_approval_required' => 'Your account requires approval before you can access the system.',
        'email_verification_required' => 'Please verify your email address to continue.',
        'two_factor_required' => 'Two-factor authentication is required for your account.',
        'password_changed' => 'Your password has been changed successfully.',
        'profile_updated' => 'Your profile has been updated successfully.'
    ],

    // Validation Messages
    'validation' => [
        'email_required' => 'Email address is required.',
        'email_invalid' => 'Please enter a valid email address.',
        'email_exists' => 'This email address is already registered.',
        'password_required' => 'Password is required.',
        'password_min_length' => 'Password must be at least 8 characters long.',
        'password_complexity' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        'password_confirmation' => 'Password confirmation does not match.',
        'first_name_required' => 'First name is required.',
        'last_name_required' => 'Last name is required.',
        'phone_invalid' => 'Please enter a valid phone number.',
        'date_of_birth_required' => 'Date of birth is required.',
        'gurmu_required' => 'Please select your Gurmu.',
        'terms_required' => 'You must agree to the terms and conditions.',
        'captcha_required' => 'Please complete the captcha verification.',
        'token_required' => 'Security token is required.',
        'token_invalid' => 'Invalid security token.',
        'code_required' => 'Authentication code is required.',
        'code_invalid' => 'Invalid authentication code.',
        'backup_code_invalid' => 'Invalid backup code.'
    ],

    // Security Messages
    'security' => [
        'login_attempt_failed' => 'Failed login attempt detected.',
        'login_attempt_suspicious' => 'Suspicious login activity detected.',
        'password_changed_notification' => 'Your password was changed.',
        'email_changed_notification' => 'Your email address was changed.',
        'new_device_login' => 'New device login detected.',
        'unusual_activity' => 'Unusual account activity detected.',
        'account_locked_security' => 'Account locked for security reasons.',
        'ip_blocked' => 'Your IP address has been temporarily blocked.',
        'rate_limit_exceeded' => 'Too many requests. Please wait before trying again.',
        'csrf_token_mismatch' => 'Security token mismatch. Please refresh and try again.',
        'session_hijack_detected' => 'Session security violation detected.',
        'multiple_sessions' => 'Multiple active sessions detected.',
        'secure_connection_required' => 'Secure connection required for this operation.',
        'password_compromised' => 'This password has been found in data breaches. Please choose a different password.'
    ],

    // Password Requirements
    'password_requirements' => [
        'title' => 'Password Requirements',
        'min_length' => 'At least 8 characters long',
        'uppercase' => 'At least one uppercase letter (A-Z)',
        'lowercase' => 'At least one lowercase letter (a-z)',
        'number' => 'At least one number (0-9)',
        'special_char' => 'At least one special character (!@#$%^&*)',
        'no_common' => 'Not a commonly used password',
        'no_personal' => 'Should not contain personal information',
        'strength_weak' => 'Weak',
        'strength_fair' => 'Fair',
        'strength_good' => 'Good',
        'strength_strong' => 'Strong',
        'strength_very_strong' => 'Very Strong'
    ],

    // Account Actions
    'actions' => [
        'approve_account' => 'Approve Account',
        'reject_account' => 'Reject Account',
        'suspend_account' => 'Suspend Account',
        'activate_account' => 'Activate Account',
        'delete_account' => 'Delete Account',
        'reset_password' => 'Reset Password',
        'verify_email' => 'Verify Email',
        'resend_verification' => 'Resend Verification',
        'enable_2fa' => 'Enable Two-Factor Authentication',
        'disable_2fa' => 'Disable Two-Factor Authentication',
        'generate_backup_codes' => 'Generate Backup Codes',
        'view_login_history' => 'View Login History',
        'end_all_sessions' => 'End All Sessions',
        'change_password' => 'Change Password',
        'update_profile' => 'Update Profile'
    ],

    // Login History
    'login_history' => [
        'title' => 'Login History',
        'date_time' => 'Date & Time',
        'ip_address' => 'IP Address',
        'location' => 'Location',
        'device' => 'Device',
        'browser' => 'Browser',
        'status' => 'Status',
        'successful' => 'Successful',
        'failed' => 'Failed',
        'blocked' => 'Blocked',
        'current_session' => 'Current Session',
        'no_history' => 'No login history available',
        'suspicious_activity' => 'Suspicious Activity',
        'end_session' => 'End Session'
    ],

    // Account Settings
    'settings' => [
        'account_settings' => 'Account Settings',
        'security_settings' => 'Security Settings',
        'privacy_settings' => 'Privacy Settings',
        'notification_settings' => 'Notification Settings',
        'language_settings' => 'Language Settings',
        'change_email' => 'Change Email Address',
        'change_phone' => 'Change Phone Number',
        'delete_account_title' => 'Delete Account',
        'delete_account_warning' => 'This action cannot be undone. All your data will be permanently deleted.',
        'deactivate_account' => 'Deactivate Account',
        'download_data' => 'Download My Data',
        'data_export' => 'Data Export',
        'privacy_policy' => 'Privacy Policy',
        'terms_of_service' => 'Terms of Service'
    ]
];