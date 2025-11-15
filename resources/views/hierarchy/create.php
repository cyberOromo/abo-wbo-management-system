<?php
$pageTitle = 'Create New ' . ucfirst($type ?? 'Unit');
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Hierarchy', 'url' => '/hierarchy'],
    ['title' => 'Create ' . ucfirst($type ?? 'Unit'), 'url' => '', 'active' => true]
];

$isGodina = ($type ?? '') === 'godina';
$isGamta = ($type ?? '') === 'gamta';
$isGurmu = ($type ?? '') === 'gurmu';
$godinaId = $_GET['godina_id'] ?? '';
$gamtaId = $_GET['gamta_id'] ?? '';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-<?= $isGodina ? 'globe' : ($isGamta ? 'house' : 'people') ?> me-2"></i>
        Create New <?= ucfirst($type ?? 'Unit') ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/hierarchy" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Hierarchy
        </a>
    </div>
</div>

<!-- Form Card -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-<?= $isGodina ? 'globe' : ($isGamta ? 'house' : 'people') ?> me-2"></i>
                    <?= $isGodina ? 'Godina' : ($isGamta ? 'Gamta' : 'Gurmu') ?> Information
                </h5>
            </div>
            <div class="card-body">
                <form id="hierarchyForm" method="POST" action="/hierarchy/store">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="<?= $type ?? '' ?>">
                    <?php if ($isGamta && $godinaId): ?>
                        <input type="hidden" name="godina_id" value="<?= $godinaId ?>">
                    <?php endif; ?>
                    <?php if ($isGurmu && $gamtaId): ?>
                        <input type="hidden" name="gamta_id" value="<?= $gamtaId ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       placeholder="Enter <?= $isGodina ? 'godina' : ($isGamta ? 'gamta' : 'gurmu') ?> name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" required
                                       placeholder="Enter unique code" maxlength="10" style="text-transform: uppercase;">
                                <div class="form-text">Unique identifier (e.g., <?= $isGodina ? 'GOD001' : 'GAM001' ?>)</div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <?php if ($isGamta && !$godinaId): ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="godina_id" class="form-label">Parent Godina <span class="text-danger">*</span></label>
                                <select class="form-select" id="godina_id" name="godina_id" required>
                                    <option value="">Select Godina...</option>
                                    <!-- Options will be populated via JavaScript -->
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($isGurmu && !$gamtaId): ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gamta_id" class="form-label">Parent Gamta <span class="text-danger">*</span></label>
                                <select class="form-select" id="gamta_id" name="gamta_id" required>
                                    <option value="">Select Gamta...</option>
                                    <!-- Options will be populated via JavaScript -->
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Location Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Location Details</h6>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="location" class="form-label">Address</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       placeholder="Enter full address">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                       placeholder="Enter postal code">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       placeholder="Enter city">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       placeholder="Enter state or province">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                       placeholder="Enter country" value="Nepal">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    <option value="Asia/Kathmandu" selected>Asia/Kathmandu (Nepal Time)</option>
                                    <option value="Asia/Kolkata">Asia/Kolkata (India Time)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="contact_phone" name="contact_phone"
                                       placeholder="Enter phone number">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email"
                                       placeholder="Enter email address">
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                          placeholder="Enter description or notes about this <?= $isGodina ? 'godina' : 'gamta' ?>"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                            <i class="bi bi-x-circle me-1"></i>
                            Cancel
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" id="saveAndAddAnother">
                                <i class="bi bi-plus-circle me-1"></i>
                                Save & Add Another
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                Save <?= ucfirst($type ?? 'Unit') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Help Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Guidelines
                </h6>
            </div>
            <div class="card-body">
                <?php if ($isGodina): ?>
                <h6>Creating a Godina</h6>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-check text-success me-1"></i> Godina represents a regional unit</li>
                    <li><i class="bi bi-check text-success me-1"></i> Can contain multiple Gamtas</li>
                    <li><i class="bi bi-check text-success me-1"></i> Use descriptive names like "Kathmandu Region"</li>
                    <li><i class="bi bi-check text-success me-1"></i> Code should be unique (e.g., GOD001)</li>
                </ul>
                <?php else: ?>
                <h6>Creating a Gamta</h6>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-check text-success me-1"></i> Gamta represents a local community</li>
                    <li><i class="bi bi-check text-success me-1"></i> Must belong to a parent Godina</li>
                    <li><i class="bi bi-check text-success me-1"></i> Use specific location names</li>
                    <li><i class="bi bi-check text-success me-1"></i> Code should be unique (e.g., GAM001)</li>
                </ul>
                <?php endif; ?>
                
                <hr class="my-3">
                
                <h6>Required Fields</h6>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-asterisk text-danger me-1" style="font-size: 0.6rem;"></i> Name</li>
                    <li><i class="bi bi-asterisk text-danger me-1" style="font-size: 0.6rem;"></i> Code</li>
                    <?php if ($isGamta): ?>
                    <li><i class="bi bi-asterisk text-danger me-1" style="font-size: 0.6rem;"></i> Parent Godina</li>
                    <?php endif; ?>
                </ul>
                
                <hr class="my-3">
                
                <h6>Tips</h6>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-lightbulb text-warning me-1"></i> Use consistent naming conventions</li>
                    <li><i class="bi bi-lightbulb text-warning me-1"></i> Fill in contact details for communication</li>
                    <li><i class="bi bi-lightbulb text-warning me-1"></i> Add timezone for scheduling events</li>
                    <li><i class="bi bi-lightbulb text-warning me-1"></i> Description helps with identification</li>
                </ul>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Current Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Godinas:</span>
                    <span class="badge bg-primary" id="totalGodinas">-</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Gamtas:</span>
                    <span class="badge bg-secondary" id="totalGamtas">-</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Active Units:</span>
                    <span class="badge bg-success" id="activeUnits">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    loadStatistics();
    
    <?php if ($isGamta && !$godinaId): ?>
    loadGodinaOptions();
    <?php endif; ?>
    
    <?php if ($isGurmu && !$gamtaId): ?>
    loadGamtaOptions();
    <?php endif; ?>
});

function initializeForm() {
    const form = document.getElementById('hierarchyForm');
    const codeInput = document.getElementById('code');
    const nameInput = document.getElementById('name');
    
    // Auto-generate code from name
    nameInput.addEventListener('input', function() {
        if (!codeInput.value) {
            const code = generateCodeFromName(this.value, '<?= $isGodina ? 'GOD' : ($isGamta ? 'GAM' : 'GUR') ?>');
            codeInput.value = code;
        }
    });
    
    // Validate code format
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        validateCode(this.value);
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            submitForm(false);
        }
    });
    
    // Save and add another
    document.getElementById('saveAndAddAnother').addEventListener('click', function() {
        if (validateForm()) {
            submitForm(true);
        }
    });
}

function generateCodeFromName(name, prefix) {
    if (!name) return '';
    
    // Take first 3 letters of name and add random number
    const letters = name.replace(/[^a-zA-Z]/g, '').substring(0, 3).toUpperCase();
    const number = Math.floor(Math.random() * 900) + 100; // Random 3-digit number
    
    return prefix + letters + number;
}

function validateCode(code) {
    const codeInput = document.getElementById('code');
    const pattern = /^[A-Z]{3,10}$/;
    
    if (code && !pattern.test(code)) {
        codeInput.setCustomValidity('Code must be 3-10 uppercase letters');
        codeInput.classList.add('is-invalid');
    } else {
        codeInput.setCustomValidity('');
        codeInput.classList.remove('is-invalid');
        
        if (code) {
            // Check if code is unique
            checkCodeUniqueness(code);
        }
    }
}

function checkCodeUniqueness(code) {
    fetch('/hierarchy/check-code', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ 
            code: code, 
            type: '<?= $type ?? '' ?>' 
        })
    })
    .then(response => response.json())
    .then(data => {
        const codeInput = document.getElementById('code');
        
        if (!data.unique) {
            codeInput.setCustomValidity('This code is already in use');
            codeInput.classList.add('is-invalid');
            codeInput.nextElementSibling.nextElementSibling.textContent = 'This code is already in use';
        } else {
            codeInput.setCustomValidity('');
            codeInput.classList.remove('is-invalid');
            codeInput.classList.add('is-valid');
        }
    })
    .catch(error => {
        console.error('Error checking code uniqueness:', error);
    });
}

<?php if ($isGamta && !$godinaId): ?>
function loadGodinaOptions() {
    fetch('/hierarchy/godinas/list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('godina_id');
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(godina => {
                    const option = document.createElement('option');
                    option.value = godina.id;
                    option.textContent = `${godina.name} (${godina.code})`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No active Godinas available</option>';
                select.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error loading Godina options:', error);
            const select = document.getElementById('godina_id');
            select.innerHTML = '<option value="">Error loading Godinas</option>';
            select.disabled = true;
        });
}
<?php endif; ?>

<?php if ($isGurmu && !$gamtaId): ?>
function loadGamtaOptions() {
    fetch('/hierarchy/gamtas/list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('gamta_id');
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(gamta => {
                    const option = document.createElement('option');
                    option.value = gamta.id;
                    option.textContent = `${gamta.name} (${gamta.code})`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No active Gamtas available</option>';
                select.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error loading Gamta options:', error);
            const select = document.getElementById('gamta_id');
            select.innerHTML = '<option value="">Error loading Gamtas</option>';
            select.disabled = true;
        });
}
<?php endif; ?>

function validateForm() {
    const form = document.getElementById('hierarchyForm');
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    // Check for custom validation errors
    const invalidInputs = form.querySelectorAll('.is-invalid');
    if (invalidInputs.length > 0) {
        isValid = false;
    }
    
    return isValid;
}

function submitForm(addAnother = false) {
    const form = document.getElementById('hierarchyForm');
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const saveAndAddButton = document.getElementById('saveAndAddAnother');
    
    // Disable buttons
    submitButton.disabled = true;
    saveAndAddButton.disabled = true;
    
    // Update button text
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    
    fetch('/hierarchy/store', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (addAnother) {
                // Reset form and show success message
                form.reset();
                document.getElementById('status').value = 'active';
                document.getElementById('country').value = 'Nepal';
                document.getElementById('timezone').value = 'Asia/Kathmandu';
                
                showAlert('success', '<?= ucfirst($type ?? 'Unit') ?> created successfully! Ready to add another.');
                
                // Re-enable buttons
                submitButton.disabled = false;
                saveAndAddButton.disabled = false;
                submitButton.innerHTML = originalText;
            } else {
                // Redirect to hierarchy page
                window.location.href = '/hierarchy?created=' + data.id;
            }
        } else {
            // Show validation errors
            if (data.errors) {
                showValidationErrors(data.errors);
            } else {
                showAlert('danger', data.message || 'An error occurred while saving.');
            }
            
            // Re-enable buttons
            submitButton.disabled = false;
            saveAndAddButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        showAlert('danger', 'An error occurred while saving. Please try again.');
        
        // Re-enable buttons
        submitButton.disabled = false;
        saveAndAddButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

function showValidationErrors(errors) {
    // Clear previous errors
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    
    // Show new errors
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = errors[field][0];
            }
        }
    });
}

function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert alert at the top of the form card
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alert, cardBody.firstChild);
    
    // Auto-dismiss success alerts
    if (type === 'success') {
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
}

function loadStatistics() {
    fetch('/hierarchy/statistics')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalGodinas').textContent = data.data.total_godinas || 0;
                document.getElementById('totalGamtas').textContent = data.data.total_gamtas || 0;
                document.getElementById('activeUnits').textContent = data.data.active_units || 0;
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}
</script>

<style>
.form-control.is-valid,
.form-select.is-valid {
    border-color: #198754;
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
}

.card-header h6 {
    color: #495057;
}

.list-unstyled li {
    margin-bottom: 0.25rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.alert {
    margin-bottom: 1rem;
}
</style>