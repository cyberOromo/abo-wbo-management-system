<?php
/**
 * Internal Email Management View
 * Administrative interface for managing internal email accounts
 */

$pageTitle = $title ?? 'Internal Email Management';
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-envelope-at me-2"></i>
                Internal Email Management
            </h2>
            <p class="text-muted mb-0">Manage organizational email accounts</p>
        </div>
        <div>
            <a href="/admin/emails/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Create Email Account
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-envelope-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['total_emails'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">Total Emails</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['active_emails'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-pause-circle-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['inactive_emails'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">Inactive</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-info bg-opacity-10 text-info">
                                <i class="bi bi-hdd-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format(($stats['total_quota_mb'] ?? 0) / 1024, 1) ?> GB</h3>
                            <p class="text-muted mb-0 small">Total Quota</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/emails" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by email, name..." 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="pending_creation" <?= ($filters['status'] ?? '') === 'pending_creation' ? 'selected' : '' ?>>Pending Creation</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Email Type</label>
                        <select name="email_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="primary" <?= ($filters['email_type'] ?? '') === 'primary' ? 'selected' : '' ?>>Primary</option>
                            <option value="alias" <?= ($filters['email_type'] ?? '') === 'alias' ? 'selected' : '' ?>>Alias</option>
                            <option value="group" <?= ($filters['email_type'] ?? '') === 'group' ? 'selected' : '' ?>>Group</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Accounts Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Email Accounts (<?= number_format($total_count ?? 0) ?>)</h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" onclick="exportEmails('csv')">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                    Export CSV
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="exportEmails('pdf')">
                    <i class="bi bi-file-pdf me-1"></i>
                    Export PDF
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($emails)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="selectAll" 
                                           onchange="toggleSelectAll(this)">
                                </th>
                                <th>Email Address</th>
                                <th>User</th>
                                <th>Position</th>
                                <th>Type</th>
                                <th>Quota</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emails as $email): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input email-checkbox" 
                                               value="<?= $email['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-envelope-fill text-primary me-2"></i>
                                            <div>
                                                <a href="/admin/emails/<?= $email['id'] ?>" class="text-decoration-none fw-medium">
                                                    <?= htmlspecialchars($email['internal_email']) ?>
                                                </a>
                                                <?php if ($email['auto_forward_to']): ?>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-arrow-right me-1"></i>
                                                        Forwards to: <?= htmlspecialchars($email['auto_forward_to']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($email['user_id']): ?>
                                            <div>
                                                <div class="fw-medium">
                                                    <?= htmlspecialchars($email['first_name'] . ' ' . $email['last_name']) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?= htmlspecialchars($email['personal_email']) ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No user assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($email['position_name']): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <?= htmlspecialchars($email['position_name']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= ucfirst($email['email_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?= number_format($email['email_quota_mb']) ?> MB
                                            <div class="progress" style="height: 3px;">
                                                <div class="progress-bar bg-success" style="width: <?= rand(10, 80) ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($email['status']) {
                                            'active' => 'success',
                                            'inactive' => 'warning',
                                            'pending_creation' => 'info',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $email['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($email['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/emails/<?= $email['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-warning" 
                                                    onclick="resetPasswordModal(<?= $email['id'] ?>, '<?= htmlspecialchars($email['internal_email']) ?>')"
                                                    title="Reset Password">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            <?php if ($email['status'] === 'active'): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="deactivateEmail(<?= $email['id'] ?>)"
                                                        title="Deactivate">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" 
                                                        class="btn btn-outline-success" 
                                                        onclick="reactivateEmail(<?= $email['id'] ?>)"
                                                        title="Reactivate">
                                                    <i class="bi bi-play-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-0">
                        <nav aria-label="Email accounts pagination">
                            <ul class="pagination mb-0 justify-content-center">
                                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $current_page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                                        Previous
                                    </a>
                                </li>
                                
                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $current_page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                                        Next
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No Email Accounts Found</h4>
                    <p class="text-muted">Try adjusting your filters or create a new email account.</p>
                    <a href="/admin/emails/create" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i>
                        Create Email Account
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bulk Actions Bar (Hidden by default) -->
    <div id="bulkActionsBar" class="card border-0 shadow-lg position-fixed bottom-0 start-50 translate-middle-x" 
         style="display: none; width: 90%; max-width: 600px; z-index: 1050;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="selectedCount">0</span> email(s) selected
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkUpdateQuota()">
                        <i class="bi bi-hdd me-1"></i>
                        Update Quota
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="bulkDeactivate()">
                        <i class="bi bi-pause-circle me-1"></i>
                        Deactivate
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="bulkReactivate()">
                        <i class="bi bi-play-circle me-1"></i>
                        Reactivate
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                        <i class="bi bi-x-circle me-1"></i>
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Email Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Reset password for: <strong id="resetEmailAddress"></strong></p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    A new random password will be generated. Make sure to save it securely.
                </div>
                <input type="hidden" id="resetEmailId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmResetPassword()">
                    <i class="bi bi-key me-1"></i>
                    Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Password Display Modal -->
<div class="modal fade" id="newPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Password Reset Successful</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Save this password now! It will not be shown again.
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="newPasswordDisplay" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyPassword()">
                            <i class="bi bi-clipboard"></i>
                            Copy
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.progress {
    background-color: #e9ecef;
}
</style>

<script>
// Select all checkboxes
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.email-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActionsBar();
}

// Update bulk actions bar visibility
function updateBulkActionsBar() {
    const checkboxes = document.querySelectorAll('.email-checkbox:checked');
    const bar = document.getElementById('bulkActionsBar');
    const count = document.getElementById('selectedCount');
    
    count.textContent = checkboxes.length;
    bar.style.display = checkboxes.length > 0 ? 'block' : 'none';
}

// Listen to checkbox changes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.email-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionsBar);
    });
});

// Reset password modal
function resetPasswordModal(emailId, emailAddress) {
    document.getElementById('resetEmailId').value = emailId;
    document.getElementById('resetEmailAddress').textContent = emailAddress;
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

// Confirm reset password
function confirmResetPassword() {
    const emailId = document.getElementById('resetEmailId').value;
    
    fetch('/admin/emails/reset-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            email_id: emailId,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
            document.getElementById('newPasswordDisplay').value = data.new_password;
            new bootstrap.Modal(document.getElementById('newPasswordModal')).show();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while resetting the password');
    });
}

// Copy password to clipboard
function copyPassword() {
    const passwordInput = document.getElementById('newPasswordDisplay');
    passwordInput.select();
    document.execCommand('copy');
    
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
    setTimeout(() => {
        btn.innerHTML = originalHTML;
    }, 2000);
}

// Deactivate email
function deactivateEmail(emailId) {
    if (!confirm('Are you sure you want to deactivate this email account?')) {
        return;
    }
    
    const reason = prompt('Reason for deactivation (optional):');
    
    fetch('/admin/emails/deactivate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            email_id: emailId,
            reason: reason || '',
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Reactivate email
function reactivateEmail(emailId) {
    if (!confirm('Are you sure you want to reactivate this email account?')) {
        return;
    }
    
    fetch('/admin/emails/reactivate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            email_id: emailId,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Clear selection
function clearSelection() {
    document.querySelectorAll('.email-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActionsBar();
}

// Export emails
function exportEmails(format) {
    const params = new URLSearchParams(window.location.search);
    params.append('export', format);
    window.location.href = '/admin/emails/export?' + params.toString();
}
</script>
