<?php
$pageTitle = $title ?? 'Create User';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Users', 'url' => '/users'],
    ['title' => 'Create User', 'url' => '/users/create', 'active' => true]
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-person-plus me-2"></i>
        Create New User
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/users" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge me-2"></i>
                    User Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/users" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <!-- Personal Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-1"></i>
                                Personal Information
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= has_error('first_name') ? 'is-invalid' : '' ?>" 
                                   id="first_name" name="first_name" value="<?= old('first_name') ?>" required>
                            <?= error_message('first_name') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= has_error('last_name') ? 'is-invalid' : '' ?>" 
                                   id="last_name" name="last_name" value="<?= old('last_name') ?>" required>
                            <?= error_message('last_name') ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= old('email') ?>" required>
                            <?= error_message('email') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>" 
                                   id="phone" name="phone" value="<?= old('phone') ?>" required>
                            <?= error_message('phone') ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control <?= has_error('date_of_birth') ? 'is-invalid' : '' ?>" 
                                   id="date_of_birth" name="date_of_birth" value="<?= old('date_of_birth') ?>">
                            <?= error_message('date_of_birth') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('gender') ? 'is-invalid' : '' ?>" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <?= error_message('gender') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control <?= has_error('profile_image') ? 'is-invalid' : '' ?>" 
                                   id="profile_image" name="profile_image" accept="image/*">
                            <?= error_message('profile_image') ?>
                            <div class="form-text">JPG, PNG or GIF. Max size: 2MB</div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-geo-alt me-1"></i>
                                Address Information
                            </h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">Street Address</label>
                            <textarea class="form-control <?= has_error('address') ? 'is-invalid' : '' ?>" 
                                      id="address" name="address" rows="2"><?= old('address') ?></textarea>
                            <?= error_message('address') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control <?= has_error('city') ? 'is-invalid' : '' ?>" 
                                   id="city" name="city" value="<?= old('city') ?>">
                            <?= error_message('city') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control <?= has_error('state') ? 'is-invalid' : '' ?>" 
                                   id="state" name="state" value="<?= old('state') ?>">
                            <?= error_message('state') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('country') ? 'is-invalid' : '' ?>" id="country" name="country" required>
                                <option value="">Select Country</option>
                                <option value="Ethiopia" <?= old('country') === 'Ethiopia' ? 'selected' : '' ?>>Ethiopia</option>
                                <option value="United States" <?= old('country') === 'United States' ? 'selected' : '' ?>>United States</option>
                                <option value="Canada" <?= old('country') === 'Canada' ? 'selected' : '' ?>>Canada</option>
                                <option value="United Kingdom" <?= old('country') === 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                                <option value="Germany" <?= old('country') === 'Germany' ? 'selected' : '' ?>>Germany</option>
                                <option value="Australia" <?= old('country') === 'Australia' ? 'selected' : '' ?>>Australia</option>
                                <option value="Other" <?= old('country') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <?= error_message('country') ?>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-shield-lock me-1"></i>
                                Account Information
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">System Role <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('role') ? 'is-invalid' : '' ?>" id="role" name="role" required>
                                <option value="">Select System Role</option>
                                <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                                <option value="executive" <?= old('role') === 'executive' ? 'selected' : '' ?>>Executive</option>
                                <option value="member" <?= old('role') === 'member' ? 'selected' : '' ?>>Member</option>
                                <option value="guest" <?= old('role') === 'guest' ? 'selected' : '' ?>>Guest</option>
                            </select>
                            <?= error_message('role') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('status') ? 'is-invalid' : '' ?>" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= old('status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="suspended" <?= old('status') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                            <?= error_message('status') ?>
                        </div>
                    </div>

                    <!-- Organizational Assignment -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-diagram-3 me-1"></i>
                                Organizational Assignment
                            </h6>
                        </div>
                        
                        <!-- Hierarchy Level Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="hierarchy_level" class="form-label">Assignment Level <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('hierarchy_level') ? 'is-invalid' : '' ?>" 
                                    id="hierarchy_level" name="hierarchy_level" required>
                                <option value="">Select Assignment Level</option>
                                <option value="global" <?= old('hierarchy_level') === 'global' ? 'selected' : '' ?>>Global Level</option>
                                <option value="godina" <?= old('hierarchy_level') === 'godina' ? 'selected' : '' ?>>Godina Level</option>
                                <option value="gamta" <?= old('hierarchy_level') === 'gamta' ? 'selected' : '' ?>>Gamta Level</option>
                                <option value="gurmu" <?= old('hierarchy_level') === 'gurmu' ? 'selected' : '' ?>>Gurmu Level</option>
                            </select>
                            <?= error_message('hierarchy_level') ?>
                        </div>

                        <!-- Position Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="position_id" class="form-label">Position <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('position_id') ? 'is-invalid' : '' ?>" 
                                    id="position_id" name="position_id" required>
                                <option value="">Select Position</option>
                                <?php foreach ($positions ?? [] as $position): ?>
                                    <option value="<?= $position['id'] ?>" <?= old('position_id') == $position['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($position['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= error_message('position_id') ?>
                        </div>

                        <!-- Godina Selection -->
                        <div class="col-md-4 mb-3" id="godina_field" style="display: none;">
                            <label for="godina_id" class="form-label">Parent Godina <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('godina_id') ? 'is-invalid' : '' ?>" 
                                    id="godina_id" name="godina_id">
                                <option value="">Select Godina</option>
                                <?php foreach ($hierarchy_data['godinas'] ?? [] as $godina): ?>
                                    <option value="<?= $godina['id'] ?>" <?= old('godina_id') == $godina['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($godina['name']) ?> (<?= htmlspecialchars($godina['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= error_message('godina_id') ?>
                        </div>

                        <!-- Gamta Selection -->
                        <div class="col-md-4 mb-3" id="gamta_field" style="display: none;">
                            <label for="gamta_id" class="form-label">Parent Gamta <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('gamta_id') ? 'is-invalid' : '' ?>" 
                                    id="gamta_id" name="gamta_id">
                                <option value="">Select Gamta</option>
                            </select>
                            <?= error_message('gamta_id') ?>
                        </div>

                        <!-- Gurmu Selection -->
                        <div class="col-md-4 mb-3" id="gurmu_field" style="display: none;">
                            <label for="gurmu_id" class="form-label">Gurmu <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('gurmu_id') ? 'is-invalid' : '' ?>" 
                                    id="gurmu_id" name="gurmu_id">
                                <option value="">Select Gurmu</option>
                            </select>
                            <?= error_message('gurmu_id') ?>
                        </div>
                    </div>

                    <!-- Password Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-key me-1"></i>
                                Password Information
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" 
                                       id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            <?= error_message('password') ?>
                            <div class="form-text">Minimum 8 characters with letters and numbers</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control <?= has_error('password_confirmation') ? 'is-invalid' : '' ?>" 
                                       id="password_confirmation" name="password_confirmation" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="password_confirmation-icon"></i>
                                </button>
                            </div>
                            <?= error_message('password_confirmation') ?>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control <?= has_error('notes') ? 'is-invalid' : '' ?>" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Any additional notes about this user..."><?= old('notes') ?></textarea>
                            <?= error_message('notes') ?>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>
                                    Create User
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Reset Form
                                </button>
                                <a href="/users" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>
                                    Cancel
                                </a>
                            </div>
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
                    User Creation Guidelines
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Required Fields</h6>
                    <ul class="small text-muted">
                        <li>First Name and Last Name</li>
                        <li>Email Address (must be unique)</li>
                        <li>Phone Number</li>
                        <li>Gender</li>
                        <li>Country</li>
                        <li>System Role and Status</li>
                        <li>Assignment Level and Position</li>
                        <li>Password (minimum 8 characters)</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <h6 class="text-primary">System Role Descriptions</h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong class="text-danger">Administrator:</strong>
                            <span class="text-muted">Full system access and user management</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-warning">Executive:</strong>
                            <span class="text-muted">Senior leadership with advanced permissions</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-primary">Member:</strong>
                            <span class="text-muted">Standard organizational member access</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-secondary">Guest:</strong>
                            <span class="text-muted">Limited access for visitors</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="text-primary">Assignment Levels</h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong class="text-success">Global:</strong>
                            <span class="text-muted">Organization-wide responsibilities</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-info">Godina:</strong>
                            <span class="text-muted">Regional level management</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-warning">Gamta:</strong>
                            <span class="text-muted">District/country level operations</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-primary">Gurmu:</strong>
                            <span class="text-muted">Local community level</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <small>
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Tip:</strong> Select the assignment level first, then choose the appropriate organizational units. The hierarchy works as Global → Godina → Gamta → Gurmu.
                    </small>
                </div>

                <div class="alert alert-warning">
                    <small>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Note:</strong> Users with pending status will need admin approval before they can access the system.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.querySelector('form').reset();
        // Reset hierarchy fields visibility
        updateHierarchyFieldsVisibility();
    }
}

// Hierarchy level change handler
document.getElementById('hierarchy_level').addEventListener('change', function() {
    updateHierarchyFieldsVisibility();
});

// Godina change handler for loading Gamtas
document.getElementById('godina_id').addEventListener('change', function() {
    const godinaId = this.value;
    const gamtaSelect = document.getElementById('gamta_id');
    const gurmuSelect = document.getElementById('gurmu_id');
    
    // Clear existing options
    gamtaSelect.innerHTML = '<option value="">Loading...</option>';
    gurmuSelect.innerHTML = '<option value="">Select Gurmu</option>';
    
    if (godinaId) {
        fetch(`/users/get-gamtas-by-godina?godina_id=${godinaId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            gamtaSelect.innerHTML = '<option value="">Select Gamta</option>';
            if (data.success && data.gamtas) {
                data.gamtas.forEach(gamta => {
                    const option = document.createElement('option');
                    option.value = gamta.id;
                    option.textContent = `${gamta.name} (${gamta.code})`;
                    gamtaSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading gamtas:', error);
            gamtaSelect.innerHTML = '<option value="">Error loading gamtas</option>';
        });
    } else {
        gamtaSelect.innerHTML = '<option value="">Select Gamta</option>';
    }
});

// Gamta change handler for loading Gurmus
document.getElementById('gamta_id').addEventListener('change', function() {
    const gamtaId = this.value;
    const gurmuSelect = document.getElementById('gurmu_id');
    
    // Clear existing options
    gurmuSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (gamtaId) {
        fetch(`/users/get-gurmus-by-gamta?gamta_id=${gamtaId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            gurmuSelect.innerHTML = '<option value="">Select Gurmu</option>';
            if (data.success && data.gurmus) {
                data.gurmus.forEach(gurmu => {
                    const option = document.createElement('option');
                    option.value = gurmu.id;
                    option.textContent = `${gurmu.name} (${gurmu.code})`;
                    gurmuSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading gurmus:', error);
            gurmuSelect.innerHTML = '<option value="">Error loading gurmus</option>';
        });
    } else {
        gurmuSelect.innerHTML = '<option value="">Select Gurmu</option>';
    }
});

function updateHierarchyFieldsVisibility() {
    const hierarchyLevel = document.getElementById('hierarchy_level').value;
    const godinaField = document.getElementById('godina_field');
    const gamtaField = document.getElementById('gamta_field');
    const gurmuField = document.getElementById('gurmu_field');
    
    // Hide all fields by default
    godinaField.style.display = 'none';
    gamtaField.style.display = 'none';
    gurmuField.style.display = 'none';
    
    // Reset required attributes
    document.getElementById('godina_id').removeAttribute('required');
    document.getElementById('gamta_id').removeAttribute('required');
    document.getElementById('gurmu_id').removeAttribute('required');
    
    // Show appropriate fields based on selection
    switch(hierarchyLevel) {
        case 'global':
            // No additional fields needed for global level
            break;
        case 'godina':
            godinaField.style.display = 'block';
            document.getElementById('godina_id').setAttribute('required', 'required');
            break;
        case 'gamta':
            godinaField.style.display = 'block';
            gamtaField.style.display = 'block';
            document.getElementById('godina_id').setAttribute('required', 'required');
            document.getElementById('gamta_id').setAttribute('required', 'required');
            break;
        case 'gurmu':
            godinaField.style.display = 'block';
            gamtaField.style.display = 'block';
            gurmuField.style.display = 'block';
            document.getElementById('godina_id').setAttribute('required', 'required');
            document.getElementById('gamta_id').setAttribute('required', 'required');
            document.getElementById('gurmu_id').setAttribute('required', 'required');
            break;
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long!');
        return false;
    }
    
    // Validate organizational assignment based on hierarchy level
    const hierarchyLevel = document.getElementById('hierarchy_level').value;
    const godinaId = document.getElementById('godina_id').value;
    const gamtaId = document.getElementById('gamta_id').value;
    const gurmuId = document.getElementById('gurmu_id').value;
    
    switch(hierarchyLevel) {
        case 'godina':
            if (!godinaId) {
                e.preventDefault();
                alert('Please select a Godina for godina-level assignment.');
                return false;
            }
            break;
        case 'gamta':
            if (!godinaId || !gamtaId) {
                e.preventDefault();
                alert('Please select both Godina and Gamta for gamta-level assignment.');
                return false;
            }
            break;
        case 'gurmu':
            if (!godinaId || !gamtaId || !gurmuId) {
                e.preventDefault();
                alert('Please select Godina, Gamta, and Gurmu for gurmu-level assignment.');
                return false;
            }
            break;
    }
    
    // If we reach here, all validation passed - allow form submission
    return true;
});

// Initialize hierarchy fields visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    updateHierarchyFieldsVisibility();
});
</script>