<?php
/**
 * Registration View Template
 * Multi-step registration form with organizational hierarchy integration
 */

// Set page metadata
$pageTitle = __('auth.register_title');
$pageDescription = __('auth.register_description');
$bodyClass = 'register-page';

// CSRF token
$csrfToken = $_SESSION['csrf_token'] ?? '';

// Language settings
$currentLang = $_SESSION['language'] ?? 'en';
$languages = [
    'en' => ['name' => 'English', 'flag' => '🇺🇸'],
    'om' => ['name' => 'Afaan Oromoo', 'flag' => '🇪🇹']
];

// Flash messages and errors
$flashMessages = $_SESSION['flash_messages'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['flash_messages'], $_SESSION['errors'], $_SESSION['old_input']);

// Get organizational hierarchy data
$godinas = $hierarchyData['godinas'] ?? [];
$gamtas = $hierarchyData['gamtas'] ?? [];
$gurmus = $hierarchyData['gurmus'] ?? [];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang) ?>" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'ABO-WBO Management') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .register-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
            color: #6c757d;
        }
        
        .step.active {
            color: #667eea;
        }
        
        .step.completed {
            color: #28a745;
        }
        
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .step.active .step-number {
            background-color: #667eea;
            color: white;
        }
        
        .step.completed .step-number {
            background-color: #28a745;
            color: white;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
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
        
        .hierarchy-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .btn-register {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        
        .organization-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="<?= htmlspecialchars($bodyClass) ?>">
    <!-- Language Switcher -->
    <div style="position: fixed; top: 1rem; right: 1rem; z-index: 1000;">
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
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

    <div class="register-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="register-card p-4">
                        <!-- Logo and Title -->
                        <div class="text-center mb-4">
                            <div class="organization-logo">ABO</div>
                            <h2 class="fw-bold mb-1"><?= __('auth.join_organization') ?></h2>
                            <p class="text-muted"><?= __('auth.register_subtitle') ?></p>
                        </div>
                        
                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step active" data-step="1">
                                <div class="step-number">1</div>
                                <span><?= __('auth.personal_info') ?></span>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-number">2</div>
                                <span><?= __('auth.organization') ?></span>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-number">3</div>
                                <span><?= __('auth.verification') ?></span>
                            </div>
                        </div>
                        
                        <!-- Flash Messages -->
                        <?php if (!empty($flashMessages)): ?>
                            <?php foreach ($flashMessages as $type => $messages): ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show">
                                        <?= htmlspecialchars($message) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Registration Form -->
                        <form method="POST" action="/auth/register" class="register-form" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="current_step" value="1" id="current_step">
                            
                            <!-- Step 1: Personal Information -->
                            <div class="form-step active" id="step-1">
                                <h4 class="mb-3"><?= __('auth.personal_information') ?></h4>
                                
                                <!-- Profile Image -->
                                <div class="text-center mb-4">
                                    <div class="profile-image-container">
                                        <img src="/assets/images/default-avatar.svg" alt="Profile" class="profile-preview rounded-circle" width="100" height="100">
                                        <input type="file" class="form-control d-none" id="profile_image" name="profile_image" accept="image/*">
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="document.getElementById('profile_image').click()">
                                            <i class="bi bi-camera"></i> <?= __('auth.upload_photo') ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- First Name -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                                   id="first_name" name="first_name" 
                                                   value="<?= htmlspecialchars($oldInput['first_name'] ?? '') ?>" 
                                                   required>
                                            <label for="first_name"><?= __('auth.first_name') ?></label>
                                            <?php if (isset($errors['first_name'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name'][0]) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Last Name -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                                   id="last_name" name="last_name" 
                                                   value="<?= htmlspecialchars($oldInput['last_name'] ?? '') ?>" 
                                                   required>
                                            <label for="last_name"><?= __('auth.last_name') ?></label>
                                            <?php if (isset($errors['last_name'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name'][0]) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           id="email" name="email" 
                                           value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" 
                                           required>
                                    <label for="email"><?= __('auth.email') ?></label>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Phone -->
                                <div class="form-floating mb-3">
                                    <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                           id="phone" name="phone" 
                                           value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>" 
                                           required>
                                    <label for="phone"><?= __('auth.phone') ?></label>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['phone'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="row">
                                    <!-- Date of Birth -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="date" class="form-control <?= isset($errors['date_of_birth']) ? 'is-invalid' : '' ?>" 
                                                   id="date_of_birth" name="date_of_birth" 
                                                   value="<?= htmlspecialchars($oldInput['date_of_birth'] ?? '') ?>">
                                            <label for="date_of_birth"><?= __('auth.date_of_birth') ?></label>
                                            <?php if (isset($errors['date_of_birth'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['date_of_birth'][0]) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Gender -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>" 
                                                    id="gender" name="gender">
                                                <option value=""><?= __('auth.select_gender') ?></option>
                                                <option value="male" <?= ($oldInput['gender'] ?? '') === 'male' ? 'selected' : '' ?>><?= __('auth.male') ?></option>
                                                <option value="female" <?= ($oldInput['gender'] ?? '') === 'female' ? 'selected' : '' ?>><?= __('auth.female') ?></option>
                                                <option value="other" <?= ($oldInput['gender'] ?? '') === 'other' ? 'selected' : '' ?>><?= __('auth.other') ?></option>
                                            </select>
                                            <label for="gender"><?= __('auth.gender') ?></label>
                                            <?php if (isset($errors['gender'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['gender'][0]) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Password -->
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                           id="password" name="password" required>
                                    <label for="password"><?= __('auth.password') ?></label>
                                    <div class="password-strength" id="password-strength"></div>
                                    <div class="form-text"><?= __('auth.password_requirements') ?></div>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['password'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                                           id="password_confirmation" name="password_confirmation" required>
                                    <label for="password_confirmation"><?= __('auth.confirm_password') ?></label>
                                    <?php if (isset($errors['password_confirmation'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['password_confirmation'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="button" class="btn btn-primary btn-lg w-100 next-step">
                                    <?= __('auth.next_step') ?> <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                            
                            <!-- Step 2: Organization Selection -->
                            <div class="form-step" id="step-2">
                                <h4 class="mb-3"><?= __('auth.organization_details') ?></h4>
                                
                                <!-- Hierarchy Info -->
                                <div class="hierarchy-info">
                                    <h6><?= __('auth.hierarchy_explanation') ?></h6>
                                    <p class="mb-0 small text-muted"><?= __('auth.hierarchy_description') ?></p>
                                </div>
                                
                                <!-- Godina Selection -->
                                <div class="form-floating mb-3">
                                    <select class="form-select <?= isset($errors['godina_id']) ? 'is-invalid' : '' ?>" 
                                            id="godina_id" name="godina_id" required>
                                        <option value=""><?= __('auth.select_godina') ?></option>
                                        <?php foreach ($godinas as $godina): ?>
                                            <option value="<?= htmlspecialchars($godina['id']) ?>" 
                                                    <?= ($oldInput['godina_id'] ?? '') == $godina['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($godina['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="godina_id"><?= __('auth.godina') ?></label>
                                    <?php if (isset($errors['godina_id'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['godina_id'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Gamta Selection -->
                                <div class="form-floating mb-3">
                                    <select class="form-select <?= isset($errors['gamta_id']) ? 'is-invalid' : '' ?>" 
                                            id="gamta_id" name="gamta_id" required disabled>
                                        <option value=""><?= __('auth.select_gamta') ?></option>
                                    </select>
                                    <label for="gamta_id"><?= __('auth.gamta') ?></label>
                                    <?php if (isset($errors['gamta_id'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['gamta_id'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Gurmu Selection -->
                                <div class="form-floating mb-4">
                                    <select class="form-select <?= isset($errors['gurmu_id']) ? 'is-invalid' : '' ?>" 
                                            id="gurmu_id" name="gurmu_id" required disabled>
                                        <option value=""><?= __('auth.select_gurmu') ?></option>
                                    </select>
                                    <label for="gurmu_id"><?= __('auth.gurmu') ?></label>
                                    <?php if (isset($errors['gurmu_id'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['gurmu_id'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-lg flex-fill prev-step">
                                        <i class="bi bi-arrow-left"></i> <?= __('auth.previous') ?>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg flex-fill next-step">
                                        <?= __('auth.next_step') ?> <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Terms and Submit -->
                            <div class="form-step" id="step-3">
                                <h4 class="mb-3"><?= __('auth.review_and_submit') ?></h4>
                                
                                <!-- Registration Summary -->
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= __('auth.registration_summary') ?></h6>
                                        <div id="registration-summary">
                                            <!-- Summary will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Terms and Conditions -->
                                <div class="form-check mb-3">
                                    <input class="form-check-input <?= isset($errors['terms']) ? 'is-invalid' : '' ?>" 
                                           type="checkbox" id="terms" name="terms" value="1" required>
                                    <label class="form-check-label" for="terms">
                                        <?= __('auth.agree_to') ?> 
                                        <a href="/terms" target="_blank"><?= __('auth.terms_conditions') ?></a>
                                    </label>
                                    <?php if (isset($errors['terms'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['terms'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Privacy Policy -->
                                <div class="form-check mb-4">
                                    <input class="form-check-input <?= isset($errors['privacy']) ? 'is-invalid' : '' ?>" 
                                           type="checkbox" id="privacy" name="privacy" value="1" required>
                                    <label class="form-check-label" for="privacy">
                                        <?= __('auth.agree_to') ?> 
                                        <a href="/privacy" target="_blank"><?= __('auth.privacy_policy') ?></a>
                                    </label>
                                    <?php if (isset($errors['privacy'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['privacy'][0]) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-lg flex-fill prev-step">
                                        <i class="bi bi-arrow-left"></i> <?= __('auth.previous') ?>
                                    </button>
                                    <button type="submit" class="btn btn-register btn-primary btn-lg flex-fill">
                                        <i class="bi bi-person-plus"></i> <?= __('auth.register') ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Login Link -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <span class="text-muted"><?= __('auth.already_have_account') ?></span>
                            <a href="/auth/login" class="text-decoration-none fw-bold">
                                <?= __('auth.login_here') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Registration Form JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 1;
            const totalSteps = 3;
            
            // Multi-step form navigation
            document.querySelectorAll('.next-step').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (validateStep(currentStep)) {
                        nextStep();
                    }
                });
            });
            
            document.querySelectorAll('.prev-step').forEach(btn => {
                btn.addEventListener('click', prevStep);
            });
            
            function nextStep() {
                if (currentStep < totalSteps) {
                    document.getElementById(`step-${currentStep}`).classList.remove('active');
                    document.querySelector(`[data-step="${currentStep}"]`).classList.add('completed');
                    document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
                    
                    currentStep++;
                    
                    document.getElementById(`step-${currentStep}`).classList.add('active');
                    document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
                    document.getElementById('current_step').value = currentStep;
                    
                    if (currentStep === 3) {
                        updateRegistrationSummary();
                    }
                }
            }
            
            function prevStep() {
                if (currentStep > 1) {
                    document.getElementById(`step-${currentStep}`).classList.remove('active');
                    document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
                    
                    currentStep--;
                    
                    document.getElementById(`step-${currentStep}`).classList.add('active');
                    document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
                    document.querySelector(`[data-step="${currentStep}"]`).classList.remove('completed');
                    document.getElementById('current_step').value = currentStep;
                }
            }
            
            function validateStep(step) {
                const stepElement = document.getElementById(`step-${step}`);
                const requiredFields = stepElement.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (step === 1) {
                    // Additional validation for step 1
                    const email = document.getElementById('email');
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('password_confirmation');
                    
                    if (email.value && !isValidEmail(email.value)) {
                        email.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    if (password.value && password.value.length < 8) {
                        password.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    if (password.value !== confirmPassword.value) {
                        confirmPassword.classList.add('is-invalid');
                        isValid = false;
                    }
                }
                
                return isValid;
            }
            
            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }
            
            // Password strength indicator
            document.getElementById('password').addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('password-strength');
                const strength = calculatePasswordStrength(password);
                
                strengthBar.className = 'password-strength';
                
                if (strength < 30) {
                    strengthBar.classList.add('strength-weak');
                } else if (strength < 70) {
                    strengthBar.classList.add('strength-medium');
                } else {
                    strengthBar.classList.add('strength-strong');
                }
                
                strengthBar.style.width = `${strength}%`;
            });
            
            function calculatePasswordStrength(password) {
                let strength = 0;
                
                if (password.length >= 8) strength += 20;
                if (password.length >= 12) strength += 10;
                if (/[a-z]/.test(password)) strength += 20;
                if (/[A-Z]/.test(password)) strength += 20;
                if (/[0-9]/.test(password)) strength += 20;
                if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
                
                return Math.min(strength, 100);
            }
            
            // Organizational hierarchy cascade
            document.getElementById('godina_id').addEventListener('change', function() {
                const godinaId = this.value;
                const gamtaSelect = document.getElementById('gamta_id');
                const gurmuSelect = document.getElementById('gurmu_id');
                
                // Reset dependent dropdowns
                gamtaSelect.innerHTML = '<option value=""><?= __('auth.select_gamta') ?></option>';
                gurmuSelect.innerHTML = '<option value=""><?= __('auth.select_gurmu') ?></option>';
                gamtaSelect.disabled = !godinaId;
                gurmuSelect.disabled = true;
                
                if (godinaId) {
                    // Fetch gamtas for selected godina
                    fetch(`/api/hierarchy/gamtas?godina_id=${godinaId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                data.gamtas.forEach(gamta => {
                                    const option = document.createElement('option');
                                    option.value = gamta.id;
                                    option.textContent = gamta.name;
                                    gamtaSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error fetching gamtas:', error));
                }
            });
            
            document.getElementById('gamta_id').addEventListener('change', function() {
                const gamtaId = this.value;
                const gurmuSelect = document.getElementById('gurmu_id');
                
                gurmuSelect.innerHTML = '<option value=""><?= __('auth.select_gurmu') ?></option>';
                gurmuSelect.disabled = !gamtaId;
                
                if (gamtaId) {
                    // Fetch gurmus for selected gamta
                    fetch(`/api/hierarchy/gurmus?gamta_id=${gamtaId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                data.gurmus.forEach(gurmu => {
                                    const option = document.createElement('option');
                                    option.value = gurmu.id;
                                    option.textContent = gurmu.name;
                                    gurmuSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error fetching gurmus:', error));
                }
            });
            
            // Profile image preview
            document.getElementById('profile_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('.profile-preview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Update registration summary
            function updateRegistrationSummary() {
                const summary = document.getElementById('registration-summary');
                const firstName = document.getElementById('first_name').value;
                const lastName = document.getElementById('last_name').value;
                const email = document.getElementById('email').value;
                const phone = document.getElementById('phone').value;
                
                const godinaText = document.getElementById('godina_id').selectedOptions[0]?.text || '';
                const gamtaText = document.getElementById('gamta_id').selectedOptions[0]?.text || '';
                const gurmuText = document.getElementById('gurmu_id').selectedOptions[0]?.text || '';
                
                summary.innerHTML = `
                    <p><strong><?= __('auth.name') ?>:</strong> ${firstName} ${lastName}</p>
                    <p><strong><?= __('auth.email') ?>:</strong> ${email}</p>
                    <p><strong><?= __('auth.phone') ?>:</strong> ${phone}</p>
                    <p><strong><?= __('auth.organization') ?>:</strong> ${godinaText} → ${gamtaText} → ${gurmuText}</p>
                `;
            }
        });
    </script>
</body>
</html>