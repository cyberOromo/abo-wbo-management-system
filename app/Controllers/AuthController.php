<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Utils\Database;

/**
 * Authentication Controller
 * ABO-WBO Management System
 */
class AuthController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Show login form
     */
    public function showLogin()
    {
        // If already authenticated, redirect to dashboard
        if (auth_check()) {
            $this->redirect('/dashboard');
        }
        
        $this->layout = 'auth';
        echo $this->render('auth.login', [
            'title' => 'Login'
        ]);
    }
    
    /**
     * Handle login attempt
     */
    public function login()
    {
        $this->requireCsrf();
        
        $data = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$data['email']]
        );
        
        // Support both 'password_hash' and 'password' column names for compatibility
        $storedHash = $user['password_hash'] ?? $user['password'] ?? null;
        
        if ($user && $storedHash && password_verify($data['password'], $storedHash)) {
            // Login successful
            session_regenerate_id(true);
            session_set('user_id', $user['id']);
            session_set('user', $user);
            
            // Update last login
            $this->db->update('users', 
                ['last_login_at' => date('Y-m-d H:i:s')], 
                ['id' => $user['id']]
            );
            
            $this->redirectWithMessage('/dashboard', 'Welcome back!', 'success');
        } else {
            $this->redirectBack(['email' => 'Invalid email or password'], ['email' => $data['email'] ?? '']);
        }
    }
    
    /**
     * Show registration form
     */
    public function showRegister()
    {
        // If already authenticated, redirect to dashboard
        if (auth_check()) {
            $this->redirect('/dashboard');
        }
        
        $this->layout = 'auth';
        echo $this->render('auth.register', [
            'title' => 'Register'
        ]);
    }
    
    /**
     * Handle registration
     */
    public function register()
    {
        $this->requireCsrf();
        
        $data = $this->validate([
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|min:10',
            'password' => 'required|min:8|confirmed'
        ]);
        
        try {
            $userId = $this->db->insert('users', [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'status' => 'pending', // Requires admin activation
                'email_verification_token' => bin2hex(random_bytes(32))
            ]);
            
            if ($userId) {
                $this->redirectWithMessage('/auth/login', 
                    'Registration successful! Please wait for admin approval.', 
                    'success'
                );
            } else {
                $this->redirectBack(['general' => 'Registration failed. Please try again.']);
            }
            
        } catch (\Exception $e) {
            log_error('Registration error: ' . $e->getMessage());
            $this->redirectBack(['general' => 'Registration failed. Please try again.']);
        }
    }
    
    /**
     * Handle logout
     */
    public function logout()
    {
        $this->requireCsrf();
        
        session_destroy();
        $this->redirectWithMessage('/', 'You have been logged out successfully.', 'success');
    }
    
    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        $this->layout = 'auth';
        echo $this->render('auth.forgot-password', [
            'title' => 'Forgot Password'
        ]);
    }
    
    /**
     * Handle forgot password
     */
    public function forgotPassword()
    {
        $this->requireCsrf();
        
        $data = $this->validate([
            'email' => 'required|email'
        ]);
        
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$data['email']]
        );
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->db->update('users', [
                'password_reset_token' => $token,
                'password_reset_expires' => $expires
            ], ['id' => $user['id']]);
            
            // TODO: Send password reset email
            log_info("Password reset requested for: {$data['email']}");
        }
        
        // Always show success message for security
        $this->redirectWithMessage('/auth/login', 
            'If your email exists in our system, you will receive a password reset link.', 
            'info'
        );
    }
    
    /**
     * Show reset password form
     */
    public function showResetPassword($token)
    {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$token]
        );
        
        if (!$user) {
            $this->redirectWithMessage('/auth/login', 
                'Invalid or expired password reset link.', 
                'error'
            );
        }
        
        $this->layout = 'auth';
        echo $this->render('auth.reset-password', [
            'title' => 'Reset Password',
            'token' => $token
        ]);
    }
    
    /**
     * Handle password reset
     */
    public function resetPassword()
    {
        $this->requireCsrf();
        
        $data = $this->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);
        
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$data['token']]
        );
        
        if (!$user) {
            $this->redirectWithMessage('/auth/login', 
                'Invalid or expired password reset link.', 
                'error'
            );
        }
        
        $this->db->update('users', [
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'password_reset_token' => null,
            'password_reset_expires' => null
        ], ['id' => $user['id']]);
        
        $this->redirectWithMessage('/auth/login', 
            'Password reset successfully! You can now login with your new password.', 
            'success'
        );
    }
}