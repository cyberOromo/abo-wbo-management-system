<?php

namespace App\Services;

use App\Models\User;
use Exception;

class AuthService
{
    private $userModel;
    private $notificationService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->notificationService = new NotificationService();
    }

    /**
     * Authenticate user with email and password
     */
    public function authenticate(string $email, string $password, bool $rememberMe = false): array
    {
        try {
            // Find user by email
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                throw new Exception('Invalid email or password');
            }
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                throw new Exception('Account is not active. Please contact administrator.');
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                // Log failed attempt
                $this->logLoginAttempt($email, false, 'Invalid password');
                throw new Exception('Invalid email or password');
            }
            
            // Check if email is verified
            if (!$user['email_verified']) {
                throw new Exception('Email not verified. Please check your email for verification link.');
            }
            
            // Generate session
            $sessionData = $this->createUserSession($user, $rememberMe);
            
            // Update last login
            $this->userModel->updateLastLogin($user['id']);
            
            // Log successful attempt
            $this->logLoginAttempt($email, true);
            
            return [
                'success' => true,
                'user' => $this->sanitizeUserData($user),
                'session' => $sessionData
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Register new user with hierarchical approval
     */
    public function register(array $userData): array
    {
        try {
            // Validate registration data
            $this->validateRegistrationData($userData);
            
            // Check if email already exists
            if ($this->userModel->findByEmail($userData['email'])) {
                throw new Exception('Email address is already registered');
            }
            
            // Hash password
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_ARGON2ID);
            unset($userData['password']);
            
            // Generate verification token
            $userData['email_verification_token'] = bin2hex(random_bytes(32));
            $userData['uuid'] = $this->generateUuid('user_');
            $userData['status'] = 'pending';
            
            // Create user
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                // Send email verification
                $this->sendEmailVerification($userId, $userData['email'], $userData['email_verification_token']);
                
                // Notify appropriate approvers based on hierarchy
                $this->notifyApprovers($userId);
                
                return [
                    'success' => true,
                    'message' => 'Registration successful. Please check your email for verification link.',
                    'user_id' => $userId
                ];
            }
            
            throw new Exception('Failed to create user account');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify email address
     */
    public function verifyEmail(string $token): array
    {
        try {
            $user = $this->userModel->findByVerificationToken($token);
            
            if (!$user) {
                throw new Exception('Invalid or expired verification token');
            }
            
            // Update user as email verified
            $success = $this->userModel->verifyEmail($user['id']);
            
            if ($success) {
                // Send welcome notification
                $this->notificationService->sendWelcomeNotification($user['id']);
                
                return [
                    'success' => true,
                    'message' => 'Email verified successfully. Your account is pending approval.'
                ];
            }
            
            throw new Exception('Failed to verify email');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve user registration
     */
    public function approveUser(int $userId, int $approvedBy): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            if ($user['status'] !== 'pending') {
                throw new Exception('User is not pending approval');
            }
            
            // Check if approver has permission
            if (!$this->canApproveUser($approvedBy, $user)) {
                throw new Exception('You do not have permission to approve this user');
            }
            
            // Approve user
            $success = $this->userModel->approveUser($userId, $approvedBy);
            
            if ($success) {
                // Send approval notification
                $this->notificationService->sendUserApprovalNotification($userId);
                
                return [
                    'success' => true,
                    'message' => 'User approved successfully'
                ];
            }
            
            throw new Exception('Failed to approve user');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                // Don't reveal if email exists
                return [
                    'success' => true,
                    'message' => 'If the email exists, a reset link has been sent.'
                ];
            }
            
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save reset token
            $success = $this->userModel->createPasswordResetToken($user['id'], $resetToken, $expiresAt);
            
            if ($success) {
                // Send reset email
                $this->sendPasswordResetEmail($user['email'], $resetToken);
            }
            
            return [
                'success' => true,
                'message' => 'If the email exists, a reset link has been sent.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to process password reset request'
            ];
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        try {
            // Validate token
            $resetData = $this->userModel->findPasswordResetToken($token);
            
            if (!$resetData || strtotime($resetData['expires_at']) < time()) {
                throw new Exception('Invalid or expired reset token');
            }
            
            // Hash new password
            $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
            
            // Update password
            $success = $this->userModel->updatePassword($resetData['user_id'], $passwordHash);
            
            if ($success) {
                // Delete reset token
                $this->userModel->deletePasswordResetToken($token);
                
                // Send confirmation email
                $this->sendPasswordChangeConfirmation($resetData['user_id']);
                
                return [
                    'success' => true,
                    'message' => 'Password reset successfully'
                ];
            }
            
            throw new Exception('Failed to reset password');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Hash new password
            $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
            
            // Update password
            $success = $this->userModel->updatePassword($userId, $passwordHash);
            
            if ($success) {
                // Send confirmation email
                $this->sendPasswordChangeConfirmation($userId);
                
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            }
            
            throw new Exception('Failed to change password');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout(string $sessionId): bool
    {
        try {
            // Destroy session
            $this->destroyUserSession($sessionId);
            
            return true;
            
        } catch (Exception $e) {
            error_log("AuthService::logout failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate session
     */
    public function validateSession(string $sessionId): ?array
    {
        try {
            $sessionData = $this->getUserSession($sessionId);
            
            if (!$sessionData || strtotime($sessionData['expires_at']) < time()) {
                return null;
            }
            
            // Get user data
            $user = $this->userModel->findById($sessionData['user_id']);
            
            if (!$user || $user['status'] !== 'active') {
                return null;
            }
            
            // Update last activity
            $this->updateSessionActivity($sessionId);
            $this->userModel->updateLastActivity($user['id']);
            
            return $this->sanitizeUserData($user);
            
        } catch (Exception $e) {
            error_log("AuthService::validateSession failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create user session
     */
    private function createUserSession(array $user, bool $rememberMe = false): array
    {
        $sessionId = bin2hex(random_bytes(32));
        $expiresAt = $rememberMe ? 
            date('Y-m-d H:i:s', strtotime('+30 days')) : 
            date('Y-m-d H:i:s', strtotime('+8 hours'));
        
        $sessionData = [
            'session_id' => $sessionId,
            'user_id' => $user['id'],
            'expires_at' => $expiresAt,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Store session (this would typically be in database or cache)
        $this->storeUserSession($sessionData);
        
        // Set session cookie
        setcookie('session_id', $sessionId, strtotime($expiresAt), '/', '', true, true);
        
        return $sessionData;
    }

    /**
     * Store user session
     */
    private function storeUserSession(array $sessionData): void
    {
        // This would typically store in database table user_sessions
        $_SESSION['user_session'] = $sessionData;
    }

    /**
     * Get user session
     */
    private function getUserSession(string $sessionId): ?array
    {
        // This would typically query from database
        return $_SESSION['user_session'] ?? null;
    }

    /**
     * Update session activity
     */
    private function updateSessionActivity(string $sessionId): void
    {
        // Update last_activity in session data
        if (isset($_SESSION['user_session'])) {
            $_SESSION['user_session']['last_activity'] = date('Y-m-d H:i:s');
        }
    }

    /**
     * Destroy user session
     */
    private function destroyUserSession(string $sessionId): void
    {
        unset($_SESSION['user_session']);
        setcookie('session_id', '', time() - 3600, '/');
    }

    /**
     * Send email verification
     */
    private function sendEmailVerification(int $userId, string $email, string $token): void
    {
        $this->notificationService->sendEmailVerificationNotification($userId, $email, $token);
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail(string $email, string $token): void
    {
        $this->notificationService->sendPasswordResetNotification($email, $token);
    }

    /**
     * Send password change confirmation
     */
    private function sendPasswordChangeConfirmation(int $userId): void
    {
        $this->notificationService->sendPasswordChangeConfirmationNotification($userId);
    }

    /**
     * Notify approvers based on hierarchy
     */
    private function notifyApprovers(int $userId): void
    {
        $this->notificationService->sendUserRegistrationNotification($userId);
    }

    /**
     * Check if user can approve another user
     */
    private function canApproveUser(int $approverId, array $user): bool
    {
        $approver = $this->userModel->findById($approverId);
        
        if (!$approver || !in_array($approver['role'], ['admin', 'leader'])) {
            return false;
        }
        
        // Admin can approve anyone
        if ($approver['role'] === 'admin') {
            return true;
        }
        
        // Check hierarchical permissions
        return $this->isInApprovalHierarchy($approver, $user);
    }

    /**
     * Check if approver is in approval hierarchy for user
     */
    private function isInApprovalHierarchy(array $approver, array $user): bool
    {
        // This would implement hierarchical approval logic
        // Simplified for now
        return true;
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData(array $data): void
    {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }
        
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (empty($data['gurmu_id'])) {
            $errors[] = 'Gurmu selection is required';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
    }

    /**
     * Log login attempt
     */
    private function logLoginAttempt(string $email, bool $success, string $reason = ''): void
    {
        // This would log to login_attempts table
        error_log("Login attempt: {$email} - " . ($success ? 'SUCCESS' : 'FAILED: ' . $reason));
    }

    /**
     * Sanitize user data for session
     */
    private function sanitizeUserData(array $user): array
    {
        unset($user['password_hash'], $user['email_verification_token']);
        return $user;
    }

    /**
     * Generate UUID
     */
    private function generateUuid(string $prefix = ''): string
    {
        return $prefix . uniqid() . '_' . bin2hex(random_bytes(8));
    }
}