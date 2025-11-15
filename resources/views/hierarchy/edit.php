<?php
$pageTitle = 'Edit ' . ucfirst($type ?? 'Unit');
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Hierarchy', 'url' => '/hierarchy'],
    ['title' => 'Edit ' . ucfirst($type ?? 'Unit'), 'url' => '', 'active' => true]
];

$isGodina = ($type ?? '') === 'godina';
$isGamta = ($type ?? '') === 'gamta';
$unit = $unit ?? null;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-<?= $isGodina ? 'globe' : 'house' ?> me-2"></i>
        Edit <?= ucfirst($type ?? 'Unit') ?>
        <?php if ($unit): ?>
            <small class="text-muted">(<?= $unit['name'] ?>)</small>
        <?php endif; ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <?php if ($unit): ?>
                <a href="/hierarchy/<?= $unit['id'] ?>?type=<?= $type ?>" class="btn btn-outline-info">
                    <i class="bi bi-eye me-1"></i>
                    View Details
                </a>
            <?php endif; ?>
            <a href="/hierarchy" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Hierarchy
            </a>
        </div>
    </div>
</div>

<?php if (!$unit): ?>
<!-- Unit Not Found -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger">
            <h4 class="alert-heading">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Unit Not Found
            </h4>
            <p>The requested <?= $type ?? 'unit' ?> could not be found or you don't have permission to edit it.</p>
            <hr>
            <div class="mb-0">
                <a href="/hierarchy" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-left me-1"></i>
                    Return to Hierarchy
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Form Card -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-<?= $isGodina ? 'globe' : 'house' ?> me-2"></i>
                    Edit <?= $isGodina ? 'Godina' : 'Gamta' ?> Information
                </h5>
            </div>
            <div class="card-body">
                <form id="hierarchyForm" method="POST" action="/hierarchy/<?= $unit['id'] ?>/update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="type" value="<?= $type ?>">
                    <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= htmlspecialchars($unit['name'] ?? '') ?>"
                                       placeholder="Enter <?= $isGodina ? 'godina' : 'gamta' ?> name">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" required
                                       value="<?= htmlspecialchars($unit['code'] ?? '') ?>"
                                       placeholder="Enter unique code" maxlength="10" style="text-transform: uppercase;">
                                <div class="form-text">Unique identifier</div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <?php if ($isGamta): ?>
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
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= ($unit['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($unit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                                       value="<?= htmlspecialchars($unit['location'] ?? '') ?>"
                                       placeholder="Enter full address">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                       value="<?= htmlspecialchars($unit['postal_code'] ?? '') ?>"
                                       placeholder="Enter postal code">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= htmlspecialchars($unit['city'] ?? '') ?>"
                                       placeholder="Enter city">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       value="<?= htmlspecialchars($unit['state'] ?? '') ?>"
                                       placeholder="Enter state or province">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                       value="<?= htmlspecialchars($unit['country'] ?? 'Nepal') ?>"
                                       placeholder="Enter country">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    <option value="Asia/Kathmandu" <?= ($unit['timezone'] ?? '') === 'Asia/Kathmandu' ? 'selected' : '' ?>>Asia/Kathmandu (Nepal Time)</option>
                                    <option value="Asia/Kolkata" <?= ($unit['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata (India Time)</option>
                                    <option value="UTC" <?= ($unit['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
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
                                       value="<?= htmlspecialchars($unit['contact_phone'] ?? '') ?>"
                                       placeholder="Enter phone number">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email"
                                       value="<?= htmlspecialchars($unit['contact_email'] ?? '') ?>"
                                       placeholder="Enter email address">
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                          placeholder="Enter description or notes about this <?= $isGodina ? 'godina' : 'gamta' ?>"><?= htmlspecialchars($unit['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Metadata -->
                        <div class="col-12">
                            <div class="alert alert-light border">
                                <div class="row text-sm">
                                    <div class="col-md-6">
                                        <strong>Created:</strong> <?= date('M j, Y g:i A', strtotime($unit['created_at'] ?? 'now')) ?><br>
                                        <strong>Last Updated:</strong> <?= date('M j, Y g:i A', strtotime($unit['updated_at'] ?? 'now')) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>ID:</strong> <?= $unit['id'] ?><br>
                                        <strong>Type:</strong> <?= ucfirst($type) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                <i class="bi bi-x-circle me-1"></i>
                                Cancel
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="deleteButton">
                                <i class="bi bi-trash me-1"></i>
                                Delete
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" id="resetButton">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset Changes
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                Update <?= ucfirst($type ?? 'Unit') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info Cards -->
    <div class="col-lg-4">
        <!-- Statistics Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    <?= ucfirst($type) ?> Statistics
                </h6>
            </div>
            <div class="card-body">
                <?php if ($isGodina): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Gamtas:</span>
                    <span class="badge bg-primary" id="totalGamtas"><?= $unit['gamta_count'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Active Gamtas:</span>
                    <span class="badge bg-success" id="activeGamtas"><?= $unit['active_gamtas'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total Users:</span>
                    <span class="badge bg-info" id="totalUsers"><?= $unit['user_count'] ?? 0 ?></span>
                </div>
                <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Assigned Users:</span>
                    <span class="badge bg-primary" id="assignedUsers"><?= $unit['user_count'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Active Users:</span>
                    <span class="badge bg-success" id="activeUsers"><?= $unit['active_users'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Parent Godina:</span>
                    <span class="badge bg-secondary" id="parentGodina"><?= $unit['godina_name'] ?? 'None' ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-tools me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($isGodina): ?>
                    <a href="/hierarchy/create?type=gamta&godina_id=<?= $unit['id'] ?>" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add New Gamta
                    </a>
                    <a href="/users?filter=unassigned&assign_to=godina_<?= $unit['id'] ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-people me-2"></i>
                        Assign Users
                    </a>
                    <?php else: ?>
                    <a href="/users?gamta_id=<?= $unit['id'] ?>" class="btn btn-outline-primary">
                        <i class="bi bi-people me-2"></i>
                        Manage Users
                    </a>
                    <a href="/hierarchy/<?= $unit['godina_id'] ?? '' ?>?type=godina" class="btn btn-outline-secondary">
                        <i class="bi bi-globe me-2"></i>
                        View Parent Godina
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-outline-info" id="viewHistoryBtn">
                        <i class="bi bi-clock-history me-2"></i>
                        View Change History
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Validation Info -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Important Notes
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small text-muted mb-0">
                    <li><i class="bi bi-exclamation-circle text-warning me-1"></i> Changing the code may affect existing references</li>
                    <li><i class="bi bi-exclamation-circle text-warning me-1"></i> Deactivating will hide from active lists</li>
                    <?php if ($isGamta): ?>
                    <li><i class="bi bi-exclamation-circle text-warning me-1"></i> Moving to different Godina will affect user assignments</li>
                    <?php endif; ?>
                    <li><i class="bi bi-info-circle text-info me-1"></i> All changes are logged for audit purposes</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this <?= $type ?>?</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action cannot be undone.
                    <?php if ($isGodina): ?>
                    All associated Gamtas will also be deleted.
                    <?php endif; ?>
                    All user assignments will be removed.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDelete">
                    <label class="form-check-label" for="confirmDelete">
                        I understand this action cannot be undone
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                    <i class="bi bi-trash me-1"></i>
                    Delete <?= ucfirst($type) ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
const originalFormData = {};
const unitId = <?= json_encode($unit['id'] ?? null) ?>;
const unitType = '<?= $type ?>';
const currentGodinaId = <?= json_encode($unit['godina_id'] ?? null) ?>;

document.addEventListener('DOMContentLoaded', function() {
    <?php if ($unit): ?>
    initializeForm();
    storeOriginalData();
    
    <?php if ($isGamta): ?>
    loadGodinaOptions();
    <?php endif; ?>
    
    initializeEventListeners();
    <?php endif; ?>
});

function initializeForm() {
    const form = document.getElementById('hierarchyForm');
    const codeInput = document.getElementById('code');
    
    // Validate code format
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        validateCode(this.value, unitId);
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            submitForm();
        }
    });
}

function storeOriginalData() {
    const form = document.getElementById('hierarchyForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        originalFormData[input.name] = input.value;
    });
}

function validateCode(code, excludeId = null) {
    const codeInput = document.getElementById('code');
    const pattern = /^[A-Z]{3,10}$/;
    
    if (code && !pattern.test(code)) {
        codeInput.setCustomValidity('Code must be 3-10 uppercase letters');
        codeInput.classList.add('is-invalid');
    } else {
        codeInput.setCustomValidity('');
        codeInput.classList.remove('is-invalid');
        
        if (code && code !== originalFormData.code) {
            // Check if code is unique
            checkCodeUniqueness(code, excludeId);
        }
    }
}

function checkCodeUniqueness(code, excludeId = null) {
    fetch('/hierarchy/check-code', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ 
            code: code, 
            type: unitType,
            exclude_id: excludeId
        })
    })
    .then(response => response.json())
    .then(data => {
        const codeInput = document.getElementById('code');
        
        if (!data.unique) {
            codeInput.setCustomValidity('This code is already in use');
            codeInput.classList.add('is-invalid');
            const feedback = codeInput.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = 'This code is already in use';
            }
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

<?php if ($isGamta): ?>
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
                    
                    if (godina.id == currentGodinaId) {
                        option.selected = true;
                    }
                    
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

function initializeEventListeners() {
    // Reset button
    document.getElementById('resetButton').addEventListener('click', function() {
        if (confirm('Are you sure you want to reset all changes?')) {
            resetForm();
        }
    });
    
    // Delete button
    document.getElementById('deleteButton').addEventListener('click', function() {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
    
    // Delete confirmation checkbox
    document.getElementById('confirmDelete').addEventListener('change', function() {
        document.getElementById('confirmDeleteBtn').disabled = !this.checked;
    });
    
    // Confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        deleteUnit();
    });
    
    // View history button
    document.getElementById('viewHistoryBtn').addEventListener('click', function() {
        viewChangeHistory();
    });
}

function resetForm() {
    const form = document.getElementById('hierarchyForm');
    
    Object.keys(originalFormData).forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) {
            input.value = originalFormData[name];
            input.classList.remove('is-invalid', 'is-valid');
        }
    });
    
    // Clear any alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-light)');
    alerts.forEach(alert => alert.remove());
}

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

function submitForm() {
    const form = document.getElementById('hierarchyForm');
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Disable button
    submitButton.disabled = true;
    
    // Update button text
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
    
    fetch(`/hierarchy/${unitId}/update`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `${unitType.charAt(0).toUpperCase() + unitType.slice(1)} updated successfully!`);
            
            // Update original data to reflect changes
            storeOriginalData();
            
            // Optionally redirect after a delay
            setTimeout(() => {
                window.location.href = `/hierarchy/${unitId}?type=${unitType}`;
            }, 2000);
        } else {
            // Show validation errors
            if (data.errors) {
                showValidationErrors(data.errors);
            } else {
                showAlert('danger', data.message || 'An error occurred while updating.');
            }
        }
        
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        showAlert('danger', 'An error occurred while updating. Please try again.');
        
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

function deleteUnit() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const originalText = confirmBtn.innerHTML;
    
    // Disable button and show loading
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
    
    fetch(`/hierarchy/${unitId}?type=${unitType}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal and redirect
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            deleteModal.hide();
            
            showAlert('success', `${unitType.charAt(0).toUpperCase() + unitType.slice(1)} deleted successfully!`);
            
            setTimeout(() => {
                window.location.href = '/hierarchy?deleted=' + unitId;
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Failed to delete the unit.');
            
            // Re-enable button
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error deleting unit:', error);
        showAlert('danger', 'An error occurred while deleting. Please try again.');
        
        // Re-enable button
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
    });
}

function viewChangeHistory() {
    // Open change history in a new window or modal
    window.open(`/hierarchy/${unitId}/history?type=${unitType}`, '_blank', 'width=800,height=600');
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
    const existingAlerts = document.querySelectorAll('.alert:not(.alert-light)');
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
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
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

.alert-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.text-sm {
    font-size: 0.875rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.card-header h6 {
    color: #495057;
}

.list-unstyled li {
    margin-bottom: 0.25rem;
}
</style>