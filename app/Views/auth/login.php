<?php
/**
 * Login View Template
 * Enhanced login form with multi-language support and security features
 */

// Set page metadata
$pageTitle = __('auth.login_title');
$pageDescription = __('auth.login_description');
$bodyClass = 'login-page';

// CSRF token
$csrfToken = $_SESSION['csrf_token'] ?? '';

// Language settings
$currentLang = $_SESSION['language'] ?? 'en';
$languages = [
    'en' => ['name' => 'English', 'flag' => '🇺🇸'],
    'om' => ['name' => 'Afaan Oromoo', 'flag' => '🇪🇹']
];

// Flash messages
$flashMessages = $_SESSION['flash_messages'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['flash_messages'], $_SESSION['errors']);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang) ?>" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Security Headers -->
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'ABO-WBO Management') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/auth.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .organization-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #28a745; }
        
        .language-switcher {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        .btn-language {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .btn-language:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .form-floating .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-floating .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0;
        }
        
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .footer-links a {
            color: #6c757d;
            text-decoration: none;
            margin: 0 0.5rem;
        }
        
        .footer-links a:hover {
            color: #667eea;
        }
    </style>
</head>

<body class="<?= htmlspecialchars($bodyClass) ?>">
    <!-- Language Switcher -->
    <div class="language-switcher">
        <div class="dropdown">
            <button class="btn btn-language btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <?= $languages[$currentLang]['flag'] ?> <?= htmlspecialchars($languages[$currentLang]['name']) ?>
            </button>
            <ul class="dropdown-menu">
                <?php foreach ($languages as $code => $lang): ?>
                    <li>
                        <a class="dropdown-item" href="?lang=<?= htmlspecialchars($code) ?>">
                            <?= $lang['flag'] ?> <?= htmlspecialchars($lang['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="login-card p-4">
                        <!-- Organization Logo -->
                        <div class="organization-logo">
                            ABO
                        </div>
                        
                        <!-- Page Title -->
                        <h2 class="text-center mb-1 fw-bold"><?= __('auth.welcome_back') ?></h2>
                        <p class="text-center text-muted mb-4"><?= __('auth.login_subtitle') ?></p>
                        
                        <!-- Flash Messages -->
                        <?php if (!empty($flashMessages)): ?>
                            <?php foreach ($flashMessages as $type => $messages): ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
                                        <i class="bi bi-<?= $type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                                        <?= htmlspecialchars($message) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Login Form -->
                        <form method="POST" action="/auth/login" class="login-form" novalidate>
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            
                            <!-- Email Field -->
                            <div class="form-floating mb-3">
                                <input type="email" 
                                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" 
                                       name="email" 
                                       placeholder="<?= __('auth.email_placeholder') ?>"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       required
                                       autocomplete="email"
                                       autofocus>
                                <label for="email">
                                    <i class="bi bi-envelope me-2"></i><?= __('auth.email') ?>
                                </label>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['email'][0]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Password Field -->
                            <div class="form-floating mb-3">
                                <input type="password" 
                                       class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" 
                                       name="password" 
                                       placeholder="<?= __('auth.password_placeholder') ?>"
                                       required
                                       autocomplete="current-password">
                                <label for="password">
                                    <i class="bi bi-lock me-2"></i><?= __('auth.password') ?>
                                </label>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-3 p-0 border-0 bg-transparent toggle-password">
                                    <i class="bi bi-eye text-muted"></i>
                                </button>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password'][0]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Remember Me & Forgot Password -->
                            <div class="remember-forgot">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                                    <label class="form-check-label" for="remember">
                                        <?= __('auth.remember_me') ?>
                                    </label>
                                </div>
                                <a href="/auth/forgot-password" class="text-decoration-none">
                                    <?= __('auth.forgot_password') ?>
                                </a>
                            </div>
                            
                            <!-- Login Button -->
                            <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                                <span class="btn-text"><?= __('auth.login') ?></span>
                                <div class="loading-spinner"></div>
                            </button>
                            
                            <!-- Social Login -->
                            <div class="text-center mb-3">
                                <div class="divider">
                                    <span class="text-muted"><?= __('auth.or') ?></span>
                                </div>
                            </div>
                            
                            <!-- Register Link -->
                            <div class="text-center">
                                <span class="text-muted"><?= __('auth.no_account') ?></span>
                                <a href="/auth/register" class="text-decoration-none fw-bold">
                                    <?= __('auth.register_now') ?>
                                </a>
                            </div>
                        </form>
                        
                        <!-- Footer Links -->
                        <div class="footer-links">
                            <a href="/privacy"><?= __('common.privacy_policy') ?></a>
                            <a href="/terms"><?= __('common.terms_of_service') ?></a>
                            <a href="/help"><?= __('common.help') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Login Form JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('.login-form');
            const loginBtn = loginForm.querySelector('button[type="submit"]');
            const btnText = loginBtn.querySelector('.btn-text');
            const spinner = loginBtn.querySelector('.loading-spinner');
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('password');
            
            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
            
            // Form submission handling
            loginForm.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;
                
                // Basic validation
                if (!email || !password) {
                    e.preventDefault();
                    showAlert('error', '<?= __('auth.fill_all_fields') ?>');
                    return;
                }
                
                if (!isValidEmail(email)) {
                    e.preventDefault();
                    showAlert('error', '<?= __('auth.invalid_email') ?>');
                    return;
                }
                
                // Show loading state
                btnText.style.display = 'none';
                spinner.style.display = 'inline-block';
                loginBtn.disabled = true;
                
                // Simulate delay for better UX (remove in production)
                setTimeout(() => {
                    // Form will submit naturally
                }, 500);
            });
            
            // Email validation
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            // Alert helper
            function showAlert(type, message) {
                const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
                const iconClass = type === 'error' ? 'bi-exclamation-triangle' : 'bi-check-circle';
                
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="bi ${iconClass} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                loginForm.insertAdjacentHTML('afterbegin', alertHtml);
            }
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Real-time email validation
            document.getElementById('email').addEventListener('blur', function() {
                const email = this.value.trim();
                if (email && !isValidEmail(email)) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
            
            // Focus management
            const firstInput = document.getElementById('email');
            if (firstInput && !firstInput.value) {
                firstInput.focus();
            }
        });
        
        // CSRF token setup for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Set default headers for fetch requests
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            options.headers = options.headers || {};
            options.headers['X-CSRF-Token'] = csrfToken;
            return originalFetch(url, options);
        };
    </script>
</body>
</html>