<?php
/**
 * System Admin User & Leader Registration View
 * CRITICAL: Only accessible by System Administrators
 */

$title = $title ?? 'User & Leader Registration';
$godinas = $godinas ?? [];
$gamtas = $gamtas ?? [];
$gurmus = $gurmus ?? [];
$positions = $positions ?? [];
$recent_registrations = $recent_registrations ?? [];
$statistics = $statistics ?? [];
$current_user = $current_user ?? [];
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">👤 User & Leader Registration</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerUserModal">
                        <i class="bi bi-person-plus"></i> Register User/Leader
                    </button>
                    <button type="button" class="btn btn-info" onclick="refreshStats()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="showUsersList()">
                        <i class="bi bi-list-ul"></i> View All Users
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- System Admin Notice -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-shield-exclamation"></i>
                <strong>System Administrator Module:</strong> This module allows you to register users with leadership positions and assign them to any organizational level. Use with caution as these users will have significant organizational responsibilities.
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-total"><?= $statistics['total_users'] ?? 0 ?></h4>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-month"><?= $statistics['this_month'] ?? 0 ?></h4>
                            <p class="mb-0">This Month</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-month fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-week"><?= $statistics['this_week'] ?? 0 ?></h4>
                            <p class="mb-0">This Week</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-week fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-today"><?= $statistics['today'] ?? 0 ?></h4>
                            <p class="mb-0">Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-day fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-assignments"><?= $statistics['active_assignments'] ?? 0 ?></h4>
                            <p class="mb-0">Active Assignments</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-diagram-3 fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent User Registrations</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_registrations)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Positions</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_registrations as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <?php
                                                $roleClass = match($user['role']) {
                                                    'system_admin' => 'danger',
                                                    'admin' => 'warning',
                                                    'executive' => 'info',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $roleClass ?>"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($user['positions']): ?>
                                                    <small><?= htmlspecialchars($user['positions']) ?></small>
                                                    <br><span class="badge bg-success"><?= $user['position_count'] ?> Position(s)</span>
                                                <?php else: ?>
                                                    <span class="text-muted">No positions</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = match($user['status']) {
                                                    'active' => 'success',
                                                    'inactive' => 'secondary',
                                                    'suspended' => 'danger',
                                                    default => 'warning'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($user['status']) ?></span>
                                            </td>
                                            <td>
                                                <small><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" onclick="viewUser(<?= $user['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning" onclick="editUserAssignments(<?= $user['id'] ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-person-plus display-1 text-muted"></i>
                            <h4 class="text-muted">No Recent Registrations</h4>
                            <p class="text-muted">Users you register will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register User/Leader Modal -->
<div class="modal fade" id="registerUserModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register User with Leadership Positions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="registerUserForm">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
                    
                    <!-- Personal Information -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Personal Information</h6>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required maxlength="255">
                                <div class="form-text">User will receive login credentials via email</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" maxlength="50">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="role" class="form-label">User Role *</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="member">Member</option>
                                    <option value="executive">Executive</option>
                                    <option value="admin">Administrator</option>
                                    <option value="system_admin">System Administrator</option>
                                </select>
                                <div class="form-text" id="roleGuidanceText">Executives and administrators require a leadership position assignment. Members can only receive an organizational placement.</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                    <option value="prefer_not_to_say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="language_preference" class="form-label">Language</label>
                                <select class="form-control" id="language_preference" name="language_preference">
                                    <option value="en">English</option>
                                    <option value="om">Oromo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="emergency_contact" class="form-label">Emergency Contact</label>
                                <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" maxlength="255">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Position Assignments -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Position Assignments</h6>
                            <p class="text-muted" id="assignmentSectionHelp">Assign leadership positions to this user. At least one position is required for Executive, Administrator, and System Administrator roles.</p>
                        </div>
                    </div>
                    
                    <div id="assignmentsContainer">
                        <div class="assignment-row card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Hierarchy Level *</label>
                                        <select class="form-control hierarchy-level" name="assignments[0][hierarchy_level]" required onchange="loadHierarchyOptions(0)">
                                            <option value="">Select Level</option>
                                            <option value="global">Global</option>
                                            <option value="godina">Godina</option>
                                            <option value="gamta">Gamta</option>
                                            <option value="gurmu">Gurmu</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Organizational Unit</label>
                                        <select class="form-control hierarchy-select" name="assignments[0][hierarchy_id]" disabled>
                                            <option value="">Select Unit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Position *</label>
                                        <select class="form-control position-select" name="assignments[0][position_id]" required disabled>
                                            <option value="">Select Position</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="assignments[0][start_date]" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger d-block" onclick="removeAssignment(0)" style="display: none !important;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-8">
                                        <label class="form-label">Assignment Notes</label>
                                        <input type="text" class="form-control" name="assignments[0][notes]" placeholder="Optional notes about this assignment">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">End Date (Optional)</label>
                                        <input type="date" class="form-control" name="assignments[0][end_date]">
                                    </div>
                                </div>
                                <div class="assignment-responsibilities mt-3" data-assignment-preview="0"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" onclick="addAssignment()">
                            <i class="bi bi-plus"></i> Add Another Position
                        </button>
                    </div>
                    <div class="alert alert-info d-none" id="memberAssignmentNotice">
                        Members can be placed in an organizational unit, but they cannot receive leadership positions or responsibility assignments unless the role is changed to Executive.
                    </div>
                    
                    <!-- Additional Notes -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Additional Information</h6>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Registration Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        <div class="form-text">Any additional notes about this user's registration and role</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Register User & Assign Positions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Users List Modal -->
<div class="modal fade" id="usersListModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">All Registered Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="usersListContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let assignmentCount = 1;
let godinasData = <?= json_encode($godinas) ?>;
let gamtasData = <?= json_encode($gamtas) ?>;
let gurmusData = <?= json_encode($gurmus) ?>;
let positionsData = <?= json_encode($positions) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize CSRF token
    if (!window.csrfToken) {
        window.csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>';
    }

    const roleSelect = document.getElementById('role');
    roleSelect.addEventListener('change', () => syncAssignmentMode(roleSelect.value));
    syncAssignmentMode(roleSelect.value);
    
    // Handle form submission
    document.getElementById('registerUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Registering...';
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        
        fetch('/admin/user-leader-registration/register', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and reset form
                document.getElementById('registerUserModal').querySelector('.btn-close').click();
                this.reset();
                resetAssignments();
                syncAssignmentMode(document.getElementById('role').value);
                
                // Show success message
                showAlert(data.message + ' Login credentials have been sent via email.', 'success');
                
                // Refresh the page to show new registration
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        })
        .finally(() => {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

function loadHierarchyOptions(index) {
    const levelSelect = document.querySelector(`select[name="assignments[${index}][hierarchy_level]"]`);
    const hierarchySelect = document.querySelector(`select[name="assignments[${index}][hierarchy_id]"]`);
    const positionSelect = document.querySelector(`select[name="assignments[${index}][position_id]"]`);
    const role = document.getElementById('role')?.value || 'member';
    
    const level = levelSelect.value;
    
    // Reset dependent selects
    hierarchySelect.innerHTML = '<option value="">Select Unit</option>';
    positionSelect.innerHTML = '<option value="">Select Position</option>';
    hierarchySelect.disabled = true;
    positionSelect.disabled = true;
    
    if (level === 'global') {
        // Global level doesn't need hierarchy selection
        hierarchySelect.disabled = true;
        if (role === 'member') {
            positionSelect.innerHTML = '<option value="">Not applicable for members</option>';
            positionSelect.disabled = true;
        } else {
            loadPositionsForLevel(index, 'global', null);
        }
    } else if (level) {
        // Load hierarchy options based on level
        hierarchySelect.disabled = false;

        getHierarchyOptions(level).forEach(unit => {
            const option = document.createElement('option');
            option.value = unit.id;
            option.textContent = unit.label;
            hierarchySelect.appendChild(option);
        });
        
        // When hierarchy is selected, load positions
        hierarchySelect.onchange = function() {
            if (this.value) {
                if (role === 'member') {
                    positionSelect.innerHTML = '<option value="">Not applicable for members</option>';
                    positionSelect.disabled = true;
                    renderResponsibilityPreview(index, null);
                } else {
                    loadPositionsForLevel(index, level, this.value);
                }
            } else {
                positionSelect.innerHTML = '<option value="">Select Position</option>';
                positionSelect.disabled = true;
            }
        };
    }
}

function syncAssignmentMode(role) {
    const isMember = role === 'member';
    const addButton = document.querySelector('button[onclick="addAssignment()"]');
    const memberNotice = document.getElementById('memberAssignmentNotice');
    const sectionHelp = document.getElementById('assignmentSectionHelp');
    const roleGuidanceText = document.getElementById('roleGuidanceText');

    if (sectionHelp) {
        sectionHelp.textContent = isMember
            ? 'Members must still be placed in an organizational unit, but they cannot hold leadership positions or receive responsibility assignments.'
            : 'Assign leadership positions to this user. At least one position is required for Executive, Administrator, and System Administrator roles.';
    }

    if (roleGuidanceText) {
        roleGuidanceText.textContent = isMember
            ? 'Members can only receive an organizational placement. Switch to Executive to enable positions and responsibilities.'
            : 'Executives and administrators require a leadership position assignment.';
    }

    if (memberNotice) {
        memberNotice.classList.toggle('d-none', !isMember);
    }

    if (addButton) {
        addButton.classList.toggle('d-none', isMember);
    }

    document.querySelectorAll('.assignment-row').forEach((row, index) => {
        const hierarchyField = row.querySelector(`select[name="assignments[${index}][hierarchy_level]"]`);
        const unitField = row.querySelector(`select[name="assignments[${index}][hierarchy_id]"]`);
        const positionField = row.querySelector(`select[name="assignments[${index}][position_id]"]`);
        const preview = row.querySelector(`[data-assignment-preview="${index}"]`);

        if (hierarchyField) {
            hierarchyField.required = true;
        }

        if (unitField) {
            unitField.required = hierarchyField?.value !== 'global' && hierarchyField?.value !== '';
        }

        if (positionField) {
            positionField.required = !isMember;

            if (isMember) {
                positionField.value = '';
                positionField.disabled = true;
                positionField.innerHTML = '<option value="">Not applicable for members</option>';
            } else if (positionField.options.length <= 1) {
                positionField.innerHTML = '<option value="">Select Position</option>';
                if (hierarchyField?.value) {
                    loadHierarchyOptions(index);
                }
            }
        }

        if (preview && isMember) {
            preview.innerHTML = '';
        }
    });
}

function loadPositionsForLevel(index, level, hierarchyId) {
    const positionSelect = document.querySelector(`select[name="assignments[${index}][position_id]"]`);
    const previewContainer = document.querySelector(`[data-assignment-preview="${index}"]`);

    positionSelect.innerHTML = '<option value="">Loading positions...</option>';
    positionSelect.disabled = true;
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }

    const params = new URLSearchParams({ level });
    if (hierarchyId) {
        params.set('hierarchy_id', hierarchyId);
    }

    fetch(`/admin/user-leader-registration/positions-for-level?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load positions');
            }

            const levelPositions = Array.isArray(data.data) ? data.data : [];
            positionSelect._positions = levelPositions;
            positionSelect.innerHTML = '<option value="">Select Position</option>';

            levelPositions.forEach(position => {
                const option = document.createElement('option');
                option.value = position.id;

                const suffix = [];
                if (position.hierarchy_type === 'all') {
                    suffix.push('All Levels');
                }
                if (position.is_occupied) {
                    suffix.push('Occupied');
                    option.disabled = true;
                }

                option.textContent = suffix.length > 0
                    ? `${position.name} (${suffix.join(', ')})`
                    : position.name;
                positionSelect.appendChild(option);
            });

            positionSelect.disabled = false;
            positionSelect.onchange = function() {
                renderResponsibilityPreview(index, this.value);
            };
        })
        .catch(error => {
            console.error('Error loading positions:', error);
            positionSelect._positions = [];
            positionSelect.innerHTML = '<option value="">No positions available</option>';
            positionSelect.disabled = true;
            if (previewContainer) {
                previewContainer.innerHTML = '<div class="alert alert-danger py-2 mb-0">Unable to load executive positions for the selected scope.</div>';
            }
        });
}

function renderResponsibilityPreview(index, positionId) {
    const positionSelect = document.querySelector(`select[name="assignments[${index}][position_id]"]`);
    const previewContainer = document.querySelector(`[data-assignment-preview="${index}"]`);
    if (!previewContainer) {
        return;
    }

    const positions = Array.isArray(positionSelect?._positions) ? positionSelect._positions : [];
    const selectedPosition = positions.find(position => String(position.id) === String(positionId));

    if (!selectedPosition || !selectedPosition.responsibility_preview) {
        previewContainer.innerHTML = '';
        return;
    }

    const shared = selectedPosition.responsibility_preview.shared || [];
    const individual = selectedPosition.responsibility_preview.individual || [];

    previewContainer.innerHTML = `
        <div class="responsibility-preview card border-0 bg-light-subtle">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <div class="fw-semibold">Responsibilities Applied to ${selectedPosition.name}</div>
                        <small class="text-muted">This assignment will attach 5 shared team responsibilities and 5 individual position responsibilities.</small>
                    </div>
                    <span class="badge text-bg-primary">${shared.length + individual.length} Total</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Shared Executive Responsibilities</div>
                        ${shared.map(item => `<div class="responsibility-pill"><span>${item.name_en}</span><small>${item.name_om}</small></div>`).join('')}
                    </div>
                    <div class="col-md-6">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Individual Position Responsibilities</div>
                        ${individual.map(item => `<div class="responsibility-pill"><span>${item.name_en}</span><small>${item.name_om}</small></div>`).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getHierarchyOptions(level) {
    if (level === 'godina') {
        return godinasData.map(godina => ({
            id: godina.id,
            label: godina.name
        }));
    }

    if (level === 'gamta') {
        return gamtasData.map(gamta => ({
            id: gamta.id,
            label: `${gamta.name}${gamta.godina_name ? ' - ' + gamta.godina_name : ''}`
        }));
    }

    if (level === 'gurmu') {
        return gurmusData.map(gurmu => {
            const labelParts = [gurmu.name];

            if (gurmu.gamta_name) {
                labelParts.push(gurmu.gamta_name);
            }

            if (gurmu.godina_name) {
                labelParts.push(gurmu.godina_name);
            }

            return {
                id: gurmu.id,
                label: labelParts.join(' - ')
            };
        });
    }

    return [];
}

function addAssignment() {
    const container = document.getElementById('assignmentsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'assignment-row card mb-3';
    newRow.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Hierarchy Level *</label>
                    <select class="form-control hierarchy-level" name="assignments[${assignmentCount}][hierarchy_level]" required onchange="loadHierarchyOptions(${assignmentCount})">
                        <option value="">Select Level</option>
                        <option value="global">Global</option>
                        <option value="godina">Godina</option>
                        <option value="gamta">Gamta</option>
                        <option value="gurmu">Gurmu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Organizational Unit</label>
                    <select class="form-control hierarchy-select" name="assignments[${assignmentCount}][hierarchy_id]" disabled>
                        <option value="">Select Unit</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Position *</label>
                    <select class="form-control position-select" name="assignments[${assignmentCount}][position_id]" required disabled>
                        <option value="">Select Position</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="assignments[${assignmentCount}][start_date]" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger d-block" onclick="removeAssignment(${assignmentCount})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="assignment-responsibilities mt-3" data-assignment-preview="${assignmentCount}"></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-8">
                    <label class="form-label">Assignment Notes</label>
                    <input type="text" class="form-control" name="assignments[${assignmentCount}][notes]" placeholder="Optional notes about this assignment">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date (Optional)</label>
                    <input type="date" class="form-control" name="assignments[${assignmentCount}][end_date]">
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    assignmentCount++;
}

function removeAssignment(index) {
    const assignmentRow = document.querySelector(`select[name="assignments[${index}][hierarchy_level]"]`).closest('.assignment-row');
    assignmentRow.remove();
}

function resetAssignments() {
    const container = document.getElementById('assignmentsContainer');
    container.innerHTML = `
        <div class="assignment-row card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Hierarchy Level *</label>
                        <select class="form-control hierarchy-level" name="assignments[0][hierarchy_level]" required onchange="loadHierarchyOptions(0)">
                            <option value="">Select Level</option>
                            <option value="global">Global</option>
                            <option value="godina">Godina</option>
                            <option value="gamta">Gamta</option>
                            <option value="gurmu">Gurmu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Organizational Unit</label>
                        <select class="form-control hierarchy-select" name="assignments[0][hierarchy_id]" disabled>
                            <option value="">Select Unit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Position *</label>
                        <select class="form-control position-select" name="assignments[0][position_id]" required disabled>
                            <option value="">Select Position</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="assignments[0][start_date]" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger d-block" onclick="removeAssignment(0)" style="display: none !important;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-8">
                        <label class="form-label">Assignment Notes</label>
                        <input type="text" class="form-control" name="assignments[0][notes]" placeholder="Optional notes about this assignment">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" name="assignments[0][end_date]">
                    </div>
                </div>
                <div class="assignment-responsibilities mt-3" data-assignment-preview="0"></div>
            </div>
        </div>
    `;
    assignmentCount = 1;
    syncAssignmentMode(document.getElementById('role')?.value || 'member');
}

function refreshStats() {
    fetch('/admin/user-leader-registration/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-total').textContent = data.data.total_users;
                document.getElementById('stat-month').textContent = data.data.this_month;
                document.getElementById('stat-week').textContent = data.data.this_week;
                document.getElementById('stat-today').textContent = data.data.today;
                document.getElementById('stat-assignments').textContent = data.data.active_assignments;
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}

function showUsersList() {
    const modal = new bootstrap.Modal(document.getElementById('usersListModal'));
    modal.show();
    
    // Load users list
    fetch('/admin/user-leader-registration/users')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUsersList(data.data);
            } else {
                document.getElementById('usersListContent').innerHTML = '<div class="alert alert-danger">Error loading users</div>';
            }
        })
        .catch(error => {
            document.getElementById('usersListContent').innerHTML = '<div class="alert alert-danger">Error loading users</div>';
        });
}

function displayUsersList(data) {
    let html = `
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Assignments</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.users.forEach(user => {
        const roleClass = user.role === 'system_admin' ? 'danger' : 
                         user.role === 'admin' ? 'warning' : 
                         user.role === 'executive' ? 'info' : 'secondary';
        
        const statusClass = user.status === 'active' ? 'success' : 
                           user.status === 'inactive' ? 'secondary' : 'danger';
        
        html += `
            <tr>
                <td><strong>${user.first_name} ${user.last_name}</strong></td>
                <td>${user.email}</td>
                <td><span class="badge bg-${roleClass}">${user.role.replace('_', ' ').toUpperCase()}</span></td>
                <td>
                    ${user.positions ? `<small>${user.positions}</small><br>` : ''}
                    <span class="badge bg-info">${user.assignment_count} Assignment(s)</span>
                </td>
                <td><span class="badge bg-${statusClass}">${user.status.toUpperCase()}</span></td>
                <td><small>${new Date(user.created_at).toLocaleDateString()}</small></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewUser(${user.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="editUserAssignments(${user.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('usersListContent').innerHTML = html;
}

function viewUser(userId) {
    window.location.href = '/users?view=' + encodeURIComponent(userId);
}

function editUserAssignments(userId) {
    window.location.href = '/users?edit=' + encodeURIComponent(userId);
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the container
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<style>
.modal-xl {
    max-width: 1200px;
}

.assignment-row {
    border-left: 4px solid #007bff;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.badge {
    font-size: 0.75rem;
}

.table td {
    vertical-align: middle;
}

.alert {
    border-left: 4px solid;
}

.alert-warning {
    border-left-color: #ffc107;
}

.alert-danger {
    border-left-color: #dc3545;
}

.alert-success {
    border-left-color: #198754;
}

.card-body {
    padding: 1rem;
}

.hierarchy-level {
    font-weight: 500;
}

.position-select option {
    padding: 0.25rem 0;
}

.responsibility-preview {
    border-left: 4px solid #0d6efd;
}

.responsibility-pill {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.5rem 0.75rem;
    background: #fff;
    border: 1px solid rgba(13, 110, 253, 0.12);
    border-radius: 0.75rem;
    margin-bottom: 0.5rem;
}

.responsibility-pill span {
    font-weight: 600;
}

.responsibility-pill small {
    color: #6c757d;
}
</style>