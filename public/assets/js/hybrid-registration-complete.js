/**
 * ABO-WBO Hybrid Registration Complete Form JavaScript
 * Handles the complete registration form with hierarchy selection and validation
 */

class HybridRegistrationComplete {
    constructor() {
        this.hierarchyData = window.hierarchyData || {};
        this.selectedHierarchy = null;
        this.formData = {};
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupValidation();
        this.initializeFormSections();
    }
    
    bindEvents() {
        // Form submission
        $('#completeRegistrationForm').on('submit', (e) => this.handleFormSubmission(e));
        
        // Hierarchy level selection
        $('input[name="target_hierarchy_level"]').on('change', (e) => this.handleHierarchyLevelChange(e));
        
        // Specific hierarchy selection
        $('#hierarchySelect').on('change', (e) => this.handleSpecificHierarchyChange(e));
        
        // Position selection
        $('#targetPosition').on('change', (e) => this.handlePositionChange(e));
        
        // Real-time form updates
        $('#firstName, #lastName').on('input', debounce(() => this.updateEmailPreview(), 300));
        $('input[name="target_hierarchy_level"], #hierarchySelect, #targetPosition').on('change', () => this.updateEmailPreview());
        
        // Preview registration button
        $('#previewRegistration').on('click', () => this.showRegistrationPreview());
        
        // Phone number formatting
        $('#phone').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length > 0 && !$(this).val().startsWith('+')) {
                $(this).val('+' + value);
            }
        });
        
        // Date of birth validation
        $('#dateOfBirth').on('change', function() {
            const dob = new Date($(this).val());
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            
            if (age < 18) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('You must be at least 18 years old');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Real-time form validation
        $('.form-control, .form-select').on('blur', function() {
            validateField($(this));
        });
    }
    
    setupValidation() {
        // Custom validation messages
        const validationMessages = {
            'first_name': 'Please enter your first name (at least 2 characters)',
            'last_name': 'Please enter your last name (at least 2 characters)',
            'phone': 'Please enter a valid phone number with country code',
            'date_of_birth': 'Please enter your date of birth',
            'gender': 'Please select your gender',
            'target_hierarchy_level': 'Please select your organizational level'
        };
        
        // Apply validation to required fields
        Object.keys(validationMessages).forEach(fieldName => {
            const $field = $(`[name="${fieldName}"]`);
            if ($field.length) {
                $field.attr('data-validation-message', validationMessages[fieldName]);
            }
        });
    }
    
    initializeFormSections() {
        // Set maximum date for date of birth (18 years ago)
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 18);
        $('#dateOfBirth').attr('max', maxDate.toISOString().split('T')[0]);
        
        // Initialize checklist
        this.updateCompletionChecklist();
    }
    
    handleHierarchyLevelChange(e) {
        const selectedLevel = $(e.target).val();
        this.selectedHierarchy = { level: selectedLevel };
        
        // Show/hide specific hierarchy selection
        const $specificSelection = $('.specific-hierarchy-selection');
        
        if (selectedLevel === 'global') {
            $specificSelection.hide();
        } else {
            $specificSelection.show();
            this.loadHierarchyOptions(selectedLevel);
        }
        
        this.updateEmailPreview();
        this.updateCompletionChecklist();
    }
    
    loadHierarchyOptions(level) {
        const $select = $('#hierarchySelect');
        const $label = $('#hierarchySelectLabel');
        
        // Update label
        const labels = {
            'godina': 'Select Godina',
            'gamta': 'Select Gamta',
            'gurmu': 'Select Gurmu'
        };
        
        $label.text(labels[level] || 'Select Unit');
        
        // Clear and populate options
        $select.empty().append('<option value="">Loading...</option>');
        
        const hierarchyOptions = this.hierarchyData[level + 's'] || [];
        
        if (hierarchyOptions.length > 0) {
            $select.empty().append('<option value="">Select...</option>');
            
            hierarchyOptions.forEach(option => {
                $select.append(`<option value="${option.id}">${option.name}</option>`);
            });
        } else {
            $select.empty().append('<option value="">No options available</option>');
        }
    }
    
    handleSpecificHierarchyChange(e) {
        const selectedId = $(e.target).val();
        
        if (selectedId && this.selectedHierarchy) {
            this.selectedHierarchy.id = selectedId;
            this.selectedHierarchy.name = $(e.target).find('option:selected').text();
        }
        
        this.updateEmailPreview();
        this.updateCompletionChecklist();
    }
    
    handlePositionChange(e) {
        const $selected = $(e.target).find('option:selected');
        this.selectedPosition = {
            id: $selected.val(),
            name: $selected.text(),
            key: $selected.data('key') || 'member'
        };
        
        this.updateEmailPreview();
        this.updateCompletionChecklist();
    }
    
    updateEmailPreview() {
        const firstName = $('#firstName').val().trim();
        const lastName = $('#lastName').val().trim();
        
        if (!firstName || !lastName) {
            $('#emailPreviewDisplay').text('Enter your name to see preview');
            return;
        }
        
        // Generate preview
        const parts = [];
        
        // Position part
        const positionKey = this.selectedPosition?.key || 'member';
        parts.push(positionKey.toLowerCase());
        
        // Hierarchy part
        if (this.selectedHierarchy?.level === 'global') {
            parts.push('global');
        } else if (this.selectedHierarchy?.name) {
            parts.push(this.sanitizeForEmail(this.selectedHierarchy.name));
        } else {
            parts.push('general');
        }
        
        // Name parts
        parts.push(this.sanitizeForEmail(firstName));
        parts.push(this.sanitizeForEmail(lastName));
        
        const email = parts.join('.') + '@abo-wbo.org';
        $('#emailPreviewDisplay').text(email);
        
        // Update side panel preview too
        $('#emailPreviewDisplay').text(email);
    }
    
    sanitizeForEmail(text) {
        return text.toLowerCase()
                  .replace(/[^a-z0-9]/g, '')
                  .substring(0, 10); // Limit length
    }
    
    updateCompletionChecklist() {
        const checks = {
            'personal-info': this.isPersonalInfoComplete(),
            'hierarchy': this.isHierarchySelectionComplete()
        };
        
        // Update checklist items
        $('.checklist-item').each(function() {
            const $item = $(this);
            const checkType = $item.data('check');
            
            if (checks[checkType]) {
                $item.removeClass('active').addClass('completed');
            } else {
                $item.removeClass('completed').addClass('active');
            }
        });
    }
    
    isPersonalInfoComplete() {
        const required = ['#firstName', '#lastName', '#phone', '#dateOfBirth', '#gender'];
        return required.every(selector => $(selector).val().trim() !== '');
    }
    
    isHierarchySelectionComplete() {
        const level = $('input[name="target_hierarchy_level"]:checked').val();
        if (!level) return false;
        
        if (level === 'global') return true;
        
        return $('#hierarchySelect').val() !== '';
    }
    
    showRegistrationPreview() {
        if (!this.validateForm()) {
            this.showAlert('error', 'Please complete all required fields before previewing');
            return;
        }
        
        // Collect form data
        this.collectFormData();
        
        // Update summary section
        this.updateSummarySection();
        
        // Show summary section
        $('.registration-summary').slideDown();
        
        // Scroll to summary
        $('html, body').animate({
            scrollTop: $('.registration-summary').offset().top - 100
        }, 500);
    }
    
    collectFormData() {
        this.formData = {
            firstName: $('#firstName').val().trim(),
            lastName: $('#lastName').val().trim(),
            phone: $('#phone').val().trim(),
            dateOfBirth: $('#dateOfBirth').val(),
            gender: $('#gender').val(),
            hierarchyLevel: $('input[name="target_hierarchy_level"]:checked').val(),
            hierarchyId: $('#hierarchySelect').val(),
            positionId: $('#targetPosition').val(),
            additionalInfo: $('#additionalInfo').val().trim()
        };
    }
    
    updateSummarySection() {
        // Update summary display
        $('#summaryName').text(`${this.formData.firstName} ${this.formData.lastName}`);
        
        // Hierarchy level
        const levelText = this.formData.hierarchyLevel?.toUpperCase() || 'Not Selected';
        const hierarchyName = $('#hierarchySelect option:selected').text();
        const fullLevel = hierarchyName !== 'Select...' && hierarchyName ? 
                         `${levelText} - ${hierarchyName}` : levelText;
        $('#summaryLevel').text(fullLevel);
        
        // Position
        const positionText = $('#targetPosition option:selected').text() || 'General Member';
        $('#summaryPosition').text(positionText);
        
        // Email preview
        const emailPreview = $('#emailPreviewDisplay').text();
        $('#summaryEmail').text(emailPreview);
    }
    
    async handleFormSubmission(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            this.showAlert('error', 'Please correct the errors below');
            return;
        }
        
        // Show loading
        this.showLoading(true);
        
        try {
            const formData = new FormData(e.target);
            
            const response = await this.apiRequest('/hybrid-registration/submit-complete', {
                method: 'POST',
                body: formData
            });
            
            if (response.success) {
                this.showAlert('success', response.message);
                
                // Show completion step
                setTimeout(() => {
                    this.showCompletionStep(response.data);
                }, 2000);
            } else {
                this.showAlert('error', response.message);
            }
        } catch (error) {
            this.showAlert('error', 'Registration failed. Please try again.');
            console.error('Form submission error:', error);
        } finally {
            this.showLoading(false);
        }
    }
    
    showCompletionStep(data) {
        // Create completion content
        const completionHtml = `
            <div class="completion-content text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <h2>Registration Submitted Successfully!</h2>
                <p class="lead">Your registration is now under review by the organizational leaders.</p>
                
                <div class="completion-details">
                    <div class="detail-item">
                        <strong>Registration ID:</strong> ${data.registration_id}
                    </div>
                    <div class="detail-item">
                        <strong>Internal Email Preview:</strong> 
                        <span class="text-primary">${data.internal_email_preview.email}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Status:</strong> ${data.status}
                    </div>
                </div>
                
                <div class="completion-actions mt-4">
                    <button type="button" class="btn btn-outline-primary me-2" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Confirmation
                    </button>
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        `;
        
        // Replace form content
        $('.registration-form-container').html(completionHtml);
    }
    
    validateForm() {
        let isValid = true;
        
        // Validate required fields
        const requiredFields = [
            { selector: '#firstName', message: 'First name is required' },
            { selector: '#lastName', message: 'Last name is required' },
            { selector: '#phone', message: 'Phone number is required' },
            { selector: '#dateOfBirth', message: 'Date of birth is required' },
            { selector: '#gender', message: 'Gender selection is required' },
            { selector: 'input[name="target_hierarchy_level"]:checked', message: 'Organizational level is required' }
        ];
        
        requiredFields.forEach(field => {
            const $field = $(field.selector);
            if (!$field.val() || $field.val().trim() === '') {
                this.showFieldError($field, field.message);
                isValid = false;
            } else {
                this.clearFieldError($field);
            }
        });
        
        // Validate specific hierarchy selection if not global
        const hierarchyLevel = $('input[name="target_hierarchy_level"]:checked').val();
        if (hierarchyLevel && hierarchyLevel !== 'global') {
            const $hierarchySelect = $('#hierarchySelect');
            if (!$hierarchySelect.val()) {
                this.showFieldError($hierarchySelect, 'Please select a specific unit');
                isValid = false;
            } else {
                this.clearFieldError($hierarchySelect);
            }
        }
        
        // Validate phone format
        const phone = $('#phone').val();
        if (phone && !this.isValidPhone(phone)) {
            this.showFieldError($('#phone'), 'Please enter a valid phone number with country code');
            isValid = false;
        }
        
        // Validate age
        const dob = $('#dateOfBirth').val();
        if (dob && !this.isValidAge(dob)) {
            this.showFieldError($('#dateOfBirth'), 'You must be at least 18 years old');
            isValid = false;
        }
        
        return isValid;
    }
    
    isValidPhone(phone) {
        // Basic phone validation - starts with + and has at least 10 digits
        const phoneRegex = /^\+\d{10,15}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }
    
    isValidAge(dateOfBirth) {
        const dob = new Date(dateOfBirth);
        const today = new Date();
        const age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            return age - 1 >= 18;
        }
        
        return age >= 18;
    }
    
    showFieldError($field, message) {
        $field.addClass('is-invalid');
        let $feedback = $field.siblings('.invalid-feedback');
        if ($feedback.length === 0) {
            $feedback = $('<div class="invalid-feedback"></div>');
            $field.after($feedback);
        }
        $feedback.text(message);
    }
    
    clearFieldError($field) {
        $field.removeClass('is-invalid');
        $field.siblings('.invalid-feedback').empty();
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
}

// Utility function for debouncing
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

// Field validation helper
function validateField($field) {
    const value = $field.val();
    const fieldName = $field.attr('name');
    
    // Skip validation for optional fields if empty
    if (!$field.prop('required') && !value) {
        return true;
    }
    
    let isValid = true;
    let message = '';
    
    switch (fieldName) {
        case 'first_name':
        case 'last_name':
            if (value.length < 2) {
                isValid = false;
                message = 'Must be at least 2 characters';
            }
            break;
            
        case 'phone':
            if (!/^\+\d{10,15}$/.test(value.replace(/\s/g, ''))) {
                isValid = false;
                message = 'Enter phone with country code (e.g., +1234567890)';
            }
            break;
    }
    
    if (isValid) {
        $field.removeClass('is-invalid');
        $field.siblings('.invalid-feedback').empty();
    } else {
        $field.addClass('is-invalid');
        let $feedback = $field.siblings('.invalid-feedback');
        if ($feedback.length === 0) {
            $feedback = $('<div class="invalid-feedback"></div>');
            $field.after($feedback);
        }
        $feedback.text(message);
    }
    
    return isValid;
}

// Initialize when document is ready
$(document).ready(function() {
    window.hybridRegistrationComplete = new HybridRegistrationComplete();
});