/**
 * ABO-WBO Hybrid Registration System JavaScript
 * Handles multi-step registration workflow with AJAX
 */

class HybridRegistration {
    constructor() {
        this.currentStep = 1;
        this.registrationId = null;
        this.countdownTimer = null;
        this.maxSteps = 4;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeStep();
        this.setupFormValidation();
    }
    
    bindEvents() {
        // Email form submission
        $('#emailForm').on('submit', (e) => this.handleEmailSubmission(e));
        
        // Verification form submission
        $('#verificationForm').on('submit', (e) => this.handleVerificationSubmission(e));
        
        // Resend verification code
        $('#resendCode').on('click', () => this.resendVerificationCode());
        
        // Change email address
        $('#changeEmail').on('click', (e) => {
            e.preventDefault();
            this.goToStep(1);
        });
        
        // Check status button
        $('#checkStatus').on('click', () => this.checkRegistrationStatus());
        
        // Real-time verification code formatting
        $('#verificationCode').on('input', function() {
            let value = $(this).val().replace(/\D/g, '').substring(0, 6);
            $(this).val(value);
        });
        
        // Email input validation
        $('#personalEmail').on('blur', function() {
            const email = $(this).val();
            if (email && !isValidEmail(email)) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('Please enter a valid email address');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    }
    
    setupFormValidation() {
        // Custom validation styles
        $('form').addClass('needs-validation');
        
        // Bootstrap validation
        window.addEventListener('load', function() {
            const forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    }
    
    initializeStep() {
        // Check URL parameters for registration continuation
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        
        if (error) {
            this.showAlert('error', this.getErrorMessage(error));
        }
        
        // Set initial step
        this.goToStep(1);
    }
    
    goToStep(stepNumber) {
        if (stepNumber < 1 || stepNumber > this.maxSteps) return;
        
        // Hide all steps
        $('.registration-step').removeClass('active');
        
        // Show target step
        $(`#step${stepNumber}`).addClass('active');
        
        // Update progress indicator
        this.updateProgressIndicator(stepNumber);
        
        // Update current step
        this.currentStep = stepNumber;
        
        // Step-specific initialization
        this.initializeCurrentStep();
    }
    
    updateProgressIndicator(activeStep) {
        $('.progress-step').each(function(index) {
            const stepNumber = index + 1;
            const $step = $(this);
            
            $step.removeClass('active completed');
            
            if (stepNumber < activeStep) {
                $step.addClass('completed');
            } else if (stepNumber === activeStep) {
                $step.addClass('active');
            }
        });
    }
    
    initializeCurrentStep() {
        switch (this.currentStep) {
            case 1:
                $('#personalEmail').focus();
                break;
            case 2:
                $('#verificationCode').focus();
                this.startCountdown();
                break;
            case 3:
                // Step 3 content is loaded via redirect
                break;
            case 4:
                this.updateRegistrationStatus();
                break;
        }
    }
    
    async handleEmailSubmission(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Validate form
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading
        this.showLoading(true);
        
        try {
            const response = await this.apiRequest('/hybrid-registration/submit-email', {
                method: 'POST',
                body: formData
            });
            
            if (response.success) {
                this.registrationId = response.registration_id;
                $('#registrationId').val(this.registrationId);
                $('#displayEmail').text(formData.get('personal_email'));
                
                this.showAlert('success', response.message);
                setTimeout(() => this.goToStep(2), 1500);
            } else {
                this.showAlert('error', response.message);
            }
        } catch (error) {
            this.showAlert('error', 'An error occurred while processing your request. Please try again.');
            console.error('Email submission error:', error);
        } finally {
            this.showLoading(false);
        }
    }
    
    async handleVerificationSubmission(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Validate form
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Check code format
        const code = formData.get('verification_code');
        if (!/^\d{6}$/.test(code)) {
            this.showAlert('error', 'Please enter a valid 6-digit verification code');
            return;
        }
        
        // Show loading
        this.showLoading(true);
        
        try {
            const response = await this.apiRequest('/hybrid-registration/verify-email', {
                method: 'POST',
                body: formData
            });
            
            if (response.success) {
                this.showAlert('success', response.message);
                
                // Redirect to complete registration form
                setTimeout(() => {
                    window.location.href = response.redirect;
                }, 1500);
            } else {
                this.showAlert('error', response.message);
                
                // Shake the verification code input
                $('#verificationCode').addClass('is-invalid');
                setTimeout(() => {
                    $('#verificationCode').removeClass('is-invalid').focus().select();
                }, 500);
            }
        } catch (error) {
            this.showAlert('error', 'Verification failed. Please try again.');
            console.error('Verification error:', error);
        } finally {
            this.showLoading(false);
        }
    }
    
    async resendVerificationCode() {
        if (!this.registrationId) {
            this.showAlert('error', 'Registration session not found. Please start over.');
            return;
        }
        
        // Disable button temporarily
        const $button = $('#resendCode');
        const originalText = $button.text();
        $button.prop('disabled', true).text('Sending...');
        
        try {
            const response = await this.apiRequest('/hybrid-registration/resend-verification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    registration_id: this.registrationId
                })
            });
            
            if (response.success) {
                this.showAlert('success', response.message);
                this.startCountdown(); // Restart countdown
            } else {
                this.showAlert('error', response.message);
            }
        } catch (error) {
            this.showAlert('error', 'Failed to resend verification code. Please try again.');
            console.error('Resend error:', error);
        } finally {
            setTimeout(() => {
                $button.prop('disabled', false).text(originalText);
            }, 5000); // 5-second cooldown
        }
    }
    
    startCountdown() {
        // Clear existing timer
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }
        
        // 24 hours in seconds
        let timeLeft = 24 * 60 * 60;
        
        const updateCountdown = () => {
            const hours = Math.floor(timeLeft / 3600);
            const minutes = Math.floor((timeLeft % 3600) / 60);
            const seconds = timeLeft % 60;
            
            const display = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            $('#countdown').text(display);
            
            if (timeLeft <= 0) {
                clearInterval(this.countdownTimer);
                $('#countdown').text('Expired').addClass('text-danger');
                this.showAlert('warning', 'Verification code has expired. Please request a new one.');
            }
            
            timeLeft--;
        };
        
        // Update immediately and then every second
        updateCountdown();
        this.countdownTimer = setInterval(updateCountdown, 1000);
    }
    
    async checkRegistrationStatus() {
        if (!this.registrationId) return;
        
        const $button = $('#checkStatus');
        const originalText = $button.text();
        $button.prop('disabled', true).html('<i class="fas fa-sync fa-spin me-2"></i>Checking...');
        
        try {
            const response = await this.apiRequest('/hybrid-registration/check-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    registration_id: this.registrationId
                })
            });
            
            if (response.success) {
                this.updateRegistrationStatus(response.status);
            } else {
                this.showAlert('error', 'Failed to check status');
            }
        } catch (error) {
            this.showAlert('error', 'Status check failed');
            console.error('Status check error:', error);
        } finally {
            setTimeout(() => {
                $button.prop('disabled', false).html(originalText);
            }, 2000);
        }
    }
    
    updateRegistrationStatus(status = null) {
        if (!status) return;
        
        // Update status display
        const statusConfig = this.getStatusConfig(status.current_status);
        $('.status-badge').removeClass().addClass(`badge ${statusConfig.class}`).text(statusConfig.text);
        
        // Update details
        if (status.registration_id) {
            $('#finalRegistrationId').text(status.registration_id);
        }
        
        if (status.submitted_at) {
            $('#submissionTime').text(new Date(status.submitted_at).toLocaleString());
        }
        
        if (status.internal_email_preview) {
            $('#emailPreview').text(status.internal_email_preview);
        }
        
        // Update approval steps
        if (status.approval_steps) {
            this.updateApprovalSteps(status.approval_steps);
        }
    }
    
    updateApprovalSteps(steps) {
        const $container = $('#approvalSteps');
        $container.empty();
        
        steps.forEach((step, index) => {
            const stepHtml = `
                <div class="approval-step ${step.status}">
                    <div class="step-icon">
                        <i class="fas ${this.getStepIcon(step.status)}"></i>
                    </div>
                    <div class="step-info">
                        <h6>${step.approver_type.replace('_', ' ').toUpperCase()}</h6>
                        <p>${step.approver_name || 'Pending Assignment'}</p>
                        <small>${step.status_text}</small>
                    </div>
                </div>
            `;
            $container.append(stepHtml);
        });
    }
    
    getStatusConfig(status) {
        const configs = {
            'email_verification_pending': { class: 'bg-warning', text: 'Email Verification Pending' },
            'email_verified': { class: 'bg-info', text: 'Email Verified' },
            'approval_pending': { class: 'bg-warning', text: 'Pending Approval' },
            'approved': { class: 'bg-success', text: 'Approved' },
            'rejected': { class: 'bg-danger', text: 'Rejected' },
            'completed': { class: 'bg-success', text: 'Completed' }
        };
        
        return configs[status] || { class: 'bg-secondary', text: 'Unknown' };
    }
    
    getStepIcon(status) {
        const icons = {
            'pending': 'fa-clock',
            'approved': 'fa-check-circle',
            'rejected': 'fa-times-circle',
            'completed': 'fa-check-double'
        };
        
        return icons[status] || 'fa-question-circle';
    }
    
    async apiRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            }
        };
        
        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        } else {
            throw new Error('Response is not JSON');
        }
    }
    
    showAlert(type, message) {
        const alertClass = type === 'error' ? 'danger' : type;
        const icon = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-triangle',
            'danger': 'fa-exclamation-triangle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        }[type] || 'fa-info-circle';
        
        const alertHtml = `
            <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Auto-dismiss success alerts
        if (type === 'success') {
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
        
        // Scroll to alert
        $('html, body').animate({
            scrollTop: $('#alertContainer').offset().top - 100
        }, 500);
    }
    
    showLoading(show) {
        if (show) {
            $('#loadingOverlay').fadeIn(300);
        } else {
            $('#loadingOverlay').fadeOut(300);
        }
    }
    
    getErrorMessage(errorCode) {
        const messages = {
            'invalid_registration': 'Invalid registration session. Please start over.',
            'system_error': 'A system error occurred. Please try again later.',
            'email_in_use': 'This email address is already registered.',
            'invalid_code': 'Invalid or expired verification code.',
            'session_expired': 'Your session has expired. Please start over.'
        };
        
        return messages[errorCode] || 'An unknown error occurred.';
    }
}

// Utility functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function formatPhone(phone) {
    // Remove all non-digits
    let cleaned = phone.replace(/\D/g, '');
    
    // Add + if not present and number doesn't start with +
    if (!phone.startsWith('+') && cleaned.length > 0) {
        cleaned = '+' + cleaned;
    }
    
    return cleaned;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize when document is ready
$(document).ready(function() {
    // Initialize hybrid registration system
    window.hybridRegistration = new HybridRegistration();
    
    // Global error handling
    window.addEventListener('unhandledrejection', function(event) {
        console.error('Unhandled promise rejection:', event.reason);
        window.hybridRegistration.showAlert('error', 'An unexpected error occurred. Please refresh the page and try again.');
    });
    
    // Handle browser back/forward
    window.addEventListener('popstate', function(event) {
        // Handle navigation if needed
    });
    
    // Handle page visibility changes (pause/resume timers)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, pause timers if needed
        } else {
            // Page is visible, resume timers if needed
        }
    });
});