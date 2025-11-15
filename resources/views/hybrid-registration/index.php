<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ABO-WBO Registration' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/hybrid-registration.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="registration-body">
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Panel - Branding & Info -->
            <div class="col-lg-5 col-xl-4 registration-left-panel d-flex align-items-center">
                <div class="registration-branding w-100">
                    <div class="text-center mb-5">
                        <img src="/assets/images/abo-wbo-logo.png" alt="ABO-WBO" class="registration-logo mb-3">
                        <h1 class="registration-title">ABO-WBO</h1>
                        <p class="registration-subtitle">Afaan Oromoo Business & Workers Organization</p>
                    </div>
                    
                    <div class="registration-features">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt feature-icon"></i>
                            <div class="feature-content">
                                <h5>Secure Registration</h5>
                                <p>Your data is protected with enterprise-grade security</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-envelope feature-icon"></i>
                            <div class="feature-content">
                                <h5>Internal Email</h5>
                                <p>Get your professional organizational email address</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-users feature-icon"></i>
                            <div class="feature-content">
                                <h5>Hierarchical Structure</h5>
                                <p>Join the structured organizational hierarchy</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-check-circle feature-icon"></i>
                            <div class="feature-content">
                                <h5>Approval Workflow</h5>
                                <p>Streamlined approval process by organizational leaders</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Registration Form -->
            <div class="col-lg-7 col-xl-8 registration-right-panel d-flex align-items-center">
                <div class="registration-form-container w-100">
                    
                    <!-- Progress Indicator -->
                    <div class="registration-progress mb-4">
                        <div class="progress-steps">
                            <div class="progress-step active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label">Email</div>
                            </div>
                            <div class="progress-connector"></div>
                            <div class="progress-step" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label">Verify</div>
                            </div>
                            <div class="progress-connector"></div>
                            <div class="progress-step" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label">Complete</div>
                            </div>
                            <div class="progress-connector"></div>
                            <div class="progress-step" data-step="4">
                                <div class="step-number">4</div>
                                <div class="step-label">Approval</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alert Container -->
                    <div id="alertContainer"></div>
                    
                    <!-- Step 1: Email Submission -->
                    <div id="step1" class="registration-step active">
                        <div class="step-header text-center mb-4">
                            <h2 class="step-title">Start Your Registration</h2>
                            <p class="step-description">Enter your personal email address to begin the registration process. We'll send you a verification code.</p>
                        </div>
                        
                        <form id="emailForm" class="registration-form">
                            <div class="form-group mb-4">
                                <label for="personalEmail" class="form-label">Personal Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control form-control-lg" 
                                           id="personalEmail" 
                                           name="personal_email" 
                                           placeholder="your.email@example.com"
                                           required>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    This should be your personal email address, not a work email.
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                    <label class="form-check-label" for="agreeTerms">
                                        I agree to the <a href="/terms" target="_blank">Terms of Service</a> and 
                                        <a href="/privacy" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Verification Code
                            </button>
                        </form>
                        
                        <div class="registration-help mt-4 text-center">
                            <p>Already have an account? <a href="/auth/login">Sign In</a></p>
                        </div>
                    </div>
                    
                    <!-- Step 2: Email Verification -->
                    <div id="step2" class="registration-step">
                        <div class="step-header text-center mb-4">
                            <h2 class="step-title">Verify Your Email</h2>
                            <p class="step-description">
                                We've sent a 6-digit verification code to your email address. 
                                Please enter it below to continue.
                            </p>
                            <div class="email-display">
                                <i class="fas fa-envelope-open"></i>
                                <span id="displayEmail"></span>
                            </div>
                        </div>
                        
                        <form id="verificationForm" class="registration-form">
                            <input type="hidden" id="registrationId" name="registration_id">
                            
                            <div class="form-group mb-4">
                                <label for="verificationCode" class="form-label">Verification Code</label>
                                <div class="verification-code-input">
                                    <input type="text" 
                                           class="form-control form-control-lg text-center" 
                                           id="verificationCode" 
                                           name="verification_code" 
                                           placeholder="123456"
                                           maxlength="6"
                                           required>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-clock"></i>
                                    Code expires in <span id="countdown">24:00:00</span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-check me-2"></i>
                                Verify Email
                            </button>
                            
                            <button type="button" id="resendCode" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-2"></i>
                                Resend Code
                            </button>
                        </form>
                        
                        <div class="registration-help mt-4 text-center">
                            <p>Didn't receive the code? Check your spam folder or 
                               <a href="#" id="changeEmail">change email address</a>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Step 3: Complete Registration (This will be loaded via AJAX) -->
                    <div id="step3" class="registration-step">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading registration form...</p>
                        </div>
                    </div>
                    
                    <!-- Step 4: Registration Status -->
                    <div id="step4" class="registration-step">
                        <div class="step-header text-center mb-4">
                            <div class="status-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <h2 class="step-title">Registration Submitted</h2>
                            <p class="step-description">
                                Your registration is now under review by the organizational leaders. 
                                You'll receive email notifications about the approval status.
                            </p>
                        </div>
                        
                        <div class="registration-status-card">
                            <div class="status-header">
                                <h5>Registration Status</h5>
                                <span class="status-badge badge bg-warning">Pending Approval</span>
                            </div>
                            
                            <div class="status-details">
                                <div class="status-item">
                                    <div class="status-label">Registration ID:</div>
                                    <div class="status-value" id="finalRegistrationId">-</div>
                                </div>
                                <div class="status-item">
                                    <div class="status-label">Submitted:</div>
                                    <div class="status-value" id="submissionTime">-</div>
                                </div>
                                <div class="status-item">
                                    <div class="status-label">Internal Email Preview:</div>
                                    <div class="status-value" id="emailPreview">-</div>
                                </div>
                            </div>
                            
                            <div class="approval-progress mt-4">
                                <h6>Approval Workflow</h6>
                                <div class="approval-steps" id="approvalSteps">
                                    <!-- Approval steps will be loaded dynamically -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="registration-actions mt-4">
                            <button type="button" id="checkStatus" class="btn btn-outline-primary">
                                <i class="fas fa-sync me-2"></i>
                                Check Status
                            </button>
                            <a href="/dashboard" class="btn btn-secondary">
                                <i class="fas fa-home me-2"></i>
                                Go to Dashboard
                            </a>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Processing...</span>
            </div>
            <p class="mt-3">Processing your request...</p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/hybrid-registration.js"></script>
</body>
</html>