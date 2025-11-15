<?php
/**
 * Member Registration View
 * Restricted registration based on user's hierarchy permissions
 */

$title = $title ?? 'Member Registration';
$allowed_gurmus = $allowed_gurmus ?? [];
$recent_registrations = $recent_registrations ?? [];
$current_user = $current_user ?? [];
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">👥 Member Registration</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerMemberModal">
                        <i class="bi bi-person-plus"></i> Register New Member
                    </button>
                    <button type="button" class="btn btn-info" onclick="refreshStats()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Notice -->
    <div class="row mb-4">
        <div class="col-12">
            <?php if (in_array($current_user['role'], ['system_admin', 'super_admin'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-shield-check"></i>
                    <strong>System Administrator:</strong> You can register members for any Gurmu in the system.
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    <strong>Restricted Access:</strong> You can only register members for Gurmus where you have leadership positions.
                    <?php if (empty($allowed_gurmus)): ?>
                        <br><strong>No Gurmus Available:</strong> You don't currently have permission to register members for any Gurmu.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-total">0</h4>
                            <p class="mb-0">Total Members</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-month">0</h4>
                            <p class="mb-0">This Month</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-month fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-week">0</h4>
                            <p class="mb-0">This Week</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-week fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="stat-today">0</h4>
                            <p class="mb-0">Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-day fs-1"></i>
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
                    <h5 class="card-title mb-0">Recent Member Registrations</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_registrations)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Email</th>
                                        <th>Gurmu</th>
                                        <th>Gamta</th>
                                        <th>Godina</th>
                                        <th>Registered By</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_registrations as $member): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></strong>
                                                <?php if ($member['phone']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($member['phone']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($member['email']) ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= htmlspecialchars($member['gurmu_name'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= htmlspecialchars($member['gamta_name'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= htmlspecialchars($member['godina_name'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <?php if (isset($member['registered_by_name'])): ?>
                                                    <?= htmlspecialchars($member['registered_by_name']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">System</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= date('M j, Y g:i A', strtotime($member['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = match($member['status']) {
                                                    'active' => 'success',
                                                    'inactive' => 'secondary',
                                                    'suspended' => 'danger',
                                                    default => 'warning'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($member['status']) ?></span>
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
                            <p class="text-muted">Members you register will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Member Modal -->
<div class="modal fade" id="registerMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register New Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="registerMemberForm">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?? '' ?>">
                    
                    <?php if (empty($allowed_gurmus)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>No Permission:</strong> You don't have permission to register members for any Gurmu.
                            Please contact your system administrator.
                        </div>
                    <?php else: ?>
                        
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
                                    <div class="form-text">Member will receive login credentials via email</div>
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="language_preference" class="form-label">Language Preference</label>
                                    <select class="form-control" id="language_preference" name="language_preference">
                                        <option value="en">English</option>
                                        <option value="om">Oromo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organizational Assignment -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Organizational Assignment</h6>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gurmu_id" class="form-label">Gurmu Assignment *</label>
                            <select class="form-control" id="gurmu_id" name="gurmu_id" required>
                                <option value="">Select Gurmu</option>
                                <?php
                                $groupedGurmus = [];
                                foreach ($allowed_gurmus as $gurmu) {
                                    // Group by Gamta for better organization
                                    if (!isset($groupedGurmus[$gurmu['gamta_id']])) {
                                        $groupedGurmus[$gurmu['gamta_id']] = [];
                                    }
                                    $groupedGurmus[$gurmu['gamta_id']][] = $gurmu;
                                }
                                ?>
                                <?php foreach ($groupedGurmus as $gamtaId => $gurmus): ?>
                                    <?php
                                    // Get Gamta info for optgroup
                                    $gamta = $this->db->fetchOne("SELECT name FROM gamtas WHERE id = ?", [$gamtaId]);
                                    ?>
                                    <optgroup label="<?= htmlspecialchars($gamta['name'] ?? 'Unknown Gamta') ?>">
                                        <?php foreach ($gurmus as $gurmu): ?>
                                            <option value="<?= $gurmu['id'] ?>"><?= htmlspecialchars($gurmu['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">You can only assign members to Gurmus where you have leadership positions</div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Additional Information</h6>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" maxlength="255">
                            <div class="form-text">Name and phone number of emergency contact</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Registration Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            <div class="form-text">Any additional notes about this member's registration</div>
                        </div>
                        
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <?php if (!empty($allowed_gurmus)): ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Register Member
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CSRF token
    if (!window.csrfToken) {
        window.csrfToken = '<?= $_SESSION['_token'] ?? '' ?>';
    }
    
    // Load statistics on page load
    refreshStats();
    
    // Handle form submission
    document.getElementById('registerMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Registering...';
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        
        fetch('/member-registration/register', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and reset form
                document.getElementById('registerMemberModal').querySelector('.btn-close').click();
                this.reset();
                
                // Show success message
                showAlert('Member registered successfully! Login credentials have been sent via email.', 'success');
                
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

function refreshStats() {
    fetch('/member-registration/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-total').textContent = data.data.total_members;
                document.getElementById('stat-month').textContent = data.data.this_month;
                document.getElementById('stat-week').textContent = data.data.this_week;
                document.getElementById('stat-today').textContent = data.data.today;
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
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
.modal-lg {
    max-width: 800px;
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

.alert-info {
    border-left-color: #0dcaf0;
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
</style>