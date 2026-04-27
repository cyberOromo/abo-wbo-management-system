<?php
$pageTitle = $title ?? 'User Management';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Users', 'url' => '/users', 'active' => true]
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-people me-2"></i>
        User Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/user-leader-registration" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>
                Add User
            </a>
        </div>
        <div class="btn-group me-2" role="group" aria-label="Users display mode">
            <button type="button" class="btn btn-outline-secondary active" data-user-view-mode="table">
                <i class="bi bi-table me-1"></i>
                Table
            </button>
            <button type="button" class="btn btn-outline-secondary" data-user-view-mode="cards">
                <i class="bi bi-grid-3x3-gap me-1"></i>
                Cards
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>
                Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/users/export?format=csv">CSV</a></li>
                <li><a class="dropdown-item" href="/users/export?format=xlsx">Excel</a></li>
                <li><a class="dropdown-item" href="/users/export?format=pdf">PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="/users" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($search ?? '') ?>" 
                               placeholder="Search by name or email...">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= ($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($statusFilter ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="">All Roles</option>
                            <option value="system_admin" <?= ($roleFilter ?? '') === 'system_admin' ? 'selected' : '' ?>>System Admin</option>
                            <option value="executive" <?= ($roleFilter ?? '') === 'executive' ? 'selected' : '' ?>>Executive</option>
                            <option value="member" <?= ($roleFilter ?? '') === 'member' ? 'selected' : '' ?>>Member</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>
                                Search
                            </button>
                            <a href="/users" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-people text-primary mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title"><?= number_format($totalUsers ?? 0) ?></h4>
                <p class="card-text text-muted">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-person-check text-success mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title"><?= number_format(count(array_filter($users ?? [], fn($u) => $u['status'] === 'active'))) ?></h4>
                <p class="card-text text-muted">Active Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-person-exclamation text-warning mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title"><?= number_format(count(array_filter($users ?? [], fn($u) => $u['status'] === 'pending'))) ?></h4>
                <p class="card-text text-muted">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-shield-shaded text-info mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title"><?= number_format(count(array_filter($users ?? [], fn($u) => in_array($u['role_key'] ?? '', ['admin', 'system_admin'], true)))) ?></h4>
                <p class="card-text text-muted">Administrators</p>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0">
                <i class="bi bi-table me-2"></i>
                Users List
            </h5>
            <small class="text-muted">Switch between dense table scanning and modern card browsing.</small>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">No Users Found</h4>
                <p class="text-muted">No users match your current filters.</p>
                <a href="/admin/user-leader-registration" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>
                    Add First User
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive user-view-surface" data-user-view-surface="table">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Position</th>
                            <th scope="col">Last Login</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <img src="/uploads/profiles/<?= htmlspecialchars($user['profile_image']) ?>" 
                                                     alt="Profile" class="rounded-circle" width="40" height="40">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                            </div>
                                            <small class="text-muted">ID: <?= $user['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?= htmlspecialchars($user['email']) ?>
                                        <?php if ($user['email_verified_at']): ?>
                                            <i class="bi bi-check-circle-fill text-success ms-1" title="Email Verified"></i>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($user['phone'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars($user['phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $roleColors = [
                                        'admin' => 'danger',
                                        'system_admin' => 'danger',
                                        'executive' => 'warning',
                                        'member' => 'primary'
                                    ];
                                    $roleColor = $roleColors[$user['role_key'] ?? ''] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $roleColor ?>">
                                        <?= htmlspecialchars($user['role_label'] ?? 'Member') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'active' => 'success',
                                        'inactive' => 'secondary', 
                                        'pending' => 'warning',
                                        'deleted' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$user['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $statusColor ?>">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($user['position_summary'])): ?>
                                        <div class="fw-semibold"><?= htmlspecialchars($user['primary_position'] ?? '') ?></div>
                                        <?php if (count($user['position_summary']) > 1): ?>
                                            <small class="text-muted">+<?= count($user['position_summary']) - 1 ?> more assignment(s)</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No Position</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['last_login_at']): ?>
                                        <span title="<?= $user['last_login_at'] ?>">
                                            <?= time_ago($user['last_login_at']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" title="View" onclick="openUserDetails(<?= (int) $user['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" title="Edit Assignments" onclick="openEditAssignments(<?= (int) $user['id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($user['id'] != auth_user()['id']): ?>
                                            <button type="button" class="btn btn-outline-danger" title="Delete" 
                                                    onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-3 user-view-surface d-none" data-user-view-surface="cards">
                <?php foreach ($users as $user): ?>
                    <?php
                    $roleColors = [
                        'admin' => 'danger',
                        'system_admin' => 'danger',
                        'executive' => 'warning',
                        'member' => 'primary'
                    ];
                    $roleColor = $roleColors[$user['role_key'] ?? ''] ?? 'secondary';
                    $statusColors = [
                        'active' => 'success',
                        'inactive' => 'secondary',
                        'pending' => 'warning',
                        'deleted' => 'danger'
                    ];
                    $statusColor = $statusColors[$user['status']] ?? 'secondary';
                    ?>
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100 border user-card-surface">
                            <div class="card-body d-flex flex-column gap-3">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-semibold user-card-avatar">
                                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold fs-5"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                            <div class="text-muted small">ID: <?= (int) $user['id'] ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <span class="badge bg-<?= $roleColor ?>"><?= htmlspecialchars($user['role_label'] ?? 'Member') ?></span>
                                        <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($user['status']) ?></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="small text-uppercase text-muted fw-semibold mb-1">Contacts</div>
                                    <div><?= htmlspecialchars($user['internal_email'] ?: $user['email']) ?></div>
                                    <?php if (!empty($user['phone'])): ?>
                                        <div class="text-muted small mt-1"><?= htmlspecialchars($user['phone']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <div class="small text-uppercase text-muted fw-semibold mb-1">Assignments</div>
                                    <?php if (!empty($user['position_summary'])): ?>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach (array_slice($user['position_summary'], 0, 3) as $assignmentLabel): ?>
                                                <span class="badge text-bg-light border"><?= htmlspecialchars($assignmentLabel) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($user['position_summary']) > 3): ?>
                                                <span class="badge text-bg-secondary">+<?= count($user['position_summary']) - 3 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No active position assignments</span>
                                    <?php endif; ?>
                                </div>

                                <div class="small text-muted mt-auto">
                                    Last login:
                                    <?php if ($user['last_login_at']): ?>
                                        <?= htmlspecialchars(time_ago($user['last_login_at'])) ?>
                                    <?php else: ?>
                                        Never
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openUserDetails(<?= (int) $user['id'] ?>)">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="openEditAssignments(<?= (int) $user['id'] ?>)">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </button>
                                    <?php if ($user['id'] != auth_user()['id']): ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?= (int) $user['id'] ?>, '<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>')">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="userDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>User Overview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailContent">
                <div class="text-center py-5"><div class="spinner-border" role="status"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userAssignmentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-diagram-3 me-2"></i>Edit User Assignments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userAssignmentsForm">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?? '' ?>">
                    <input type="hidden" name="user_id" id="editAssignmentsUserId" value="">
                    <div id="userAssignmentsFeedback" class="d-none"></div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <div class="fw-semibold" id="editAssignmentsHeading">Update assignments</div>
                            <small class="text-muted">This uses the canonical leadership registration assignment flow.</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addEditAssignmentRow()">
                            <i class="bi bi-plus me-1"></i>Add Assignment
                        </button>
                    </div>
                    <div id="editAssignmentsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if (($totalPages ?? 0) > 1): ?>
    <nav class="mt-4" aria-label="Users pagination">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($statusFilter ?? '') ?>&role=<?= urlencode($roleFilter ?? '') ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            <?php endif; ?>

            <?php 
            $start = max(1, $currentPage - 2);
            $end = min($totalPages, $currentPage + 2);
            ?>

            <?php if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($statusFilter ?? '') ?>&role=<?= urlencode($roleFilter ?? '') ?>">1</a>
                </li>
                <?php if ($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($statusFilter ?? '') ?>&role=<?= urlencode($roleFilter ?? '') ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($statusFilter ?? '') ?>&role=<?= urlencode($roleFilter ?? '') ?>">
                        <?= $totalPages ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($statusFilter ?? '') ?>&role=<?= urlencode($roleFilter ?? '') ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                <p class="text-muted small">This action will deactivate the user account. It can be restored later if needed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const usersHierarchyData = {
    positions: <?= json_encode($positions ?? []) ?>,
    godinas: <?= json_encode($godinas ?? []) ?>,
    gamtas: <?= json_encode($gamtas ?? []) ?>,
    gurmus: <?= json_encode($gurmus ?? []) ?>
};

let editAssignmentIndex = 0;

function confirmDelete(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteForm').action = '/users/' + userId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function normalizeDateInputValue(value) {
    const text = String(value || '').trim();
    if (!text) {
        return '';
    }

    const isoMatch = text.match(/^(\d{4}-\d{2}-\d{2})/);
    if (isoMatch) {
        return isoMatch[1];
    }

    const usMatch = text.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (usMatch) {
        return `${usMatch[3]}-${usMatch[1].padStart(2, '0')}-${usMatch[2].padStart(2, '0')}`;
    }

    return '';
}

function setAssignmentsFeedback(message, type = 'danger') {
    const feedback = document.getElementById('userAssignmentsFeedback');
    if (!feedback) {
        return;
    }

    if (!message) {
        feedback.className = 'd-none';
        feedback.innerHTML = '';
        return;
    }

    feedback.className = `alert alert-${type} mb-3`;
    feedback.innerHTML = escapeHtml(message);
}

async function parseJsonResponse(response, fallbackMessage) {
    const text = await response.text();

    try {
        return JSON.parse(text);
    } catch (error) {
        throw new Error(text.startsWith('<') ? fallbackMessage : (text || fallbackMessage));
    }
}

function applyUserViewMode(mode) {
    document.querySelectorAll('[data-user-view-surface]').forEach(element => {
        const shouldShow = element.getAttribute('data-user-view-surface') === mode;
        element.classList.toggle('d-none', !shouldShow);
    });

    document.querySelectorAll('[data-user-view-mode]').forEach(button => {
        const active = button.getAttribute('data-user-view-mode') === mode;
        button.classList.toggle('active', active);
        button.classList.toggle('btn-outline-secondary', !active);
        button.classList.toggle('btn-secondary', active);
    });

    localStorage.setItem('usersViewMode', mode);
}

function openUserDetails(userId) {
    const modalElement = document.getElementById('userDetailModal');
    const modal = new bootstrap.Modal(modalElement);
    const content = document.getElementById('userDetailContent');
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>';
    modal.show();

    fetch(`/users/${userId}?format=json`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.json())
        .then(payload => {
            if (!payload.success) {
                throw new Error(payload.message || 'Failed to load user details');
            }

            const user = payload.data.user || {};
            const assignments = payload.data.assignments || [];
            const summary = payload.data.responsibility_summary || { shared: 0, individual: 0, total: 0 };

            content.innerHTML = `
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card border-0 bg-light-subtle h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-semibold user-card-avatar">
                                        ${escapeHtml((user.first_name || '').slice(0, 1) + (user.last_name || '').slice(0, 1)).toUpperCase()}
                                    </div>
                                    <div>
                                        <div class="fw-semibold fs-4">${escapeHtml(user.first_name)} ${escapeHtml(user.last_name)}</div>
                                        <div class="text-muted">${escapeHtml(user.role_label || user.user_type || 'Member')}</div>
                                    </div>
                                </div>
                                <div class="small text-uppercase text-muted fw-semibold mb-2">Contact</div>
                                <div class="mb-1">${escapeHtml(user.internal_email || user.email || '')}</div>
                                ${user.phone ? `<div class="text-muted mb-3">${escapeHtml(user.phone)}</div>` : '<div class="text-muted mb-3">No phone on file</div>'}
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge text-bg-primary">${summary.total} Responsibilities</span>
                                    <span class="badge text-bg-light border">${summary.shared} Shared</span>
                                    <span class="badge text-bg-light border">${summary.individual} Individual</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card border-0 h-100">
                            <div class="card-body">
                                <div class="small text-uppercase text-muted fw-semibold mb-3">Active Assignments</div>
                                ${assignments.length ? assignments.map(assignment => `
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="fw-semibold">${escapeHtml(assignment.position_name || 'Position')}</div>
                                        <div class="text-muted small">${escapeHtml(assignment.organizational_unit_name || assignment.level_scope || '')}</div>
                                        <div class="small mt-2">Start: ${escapeHtml(assignment.start_date || 'Not set')}</div>
                                        ${assignment.notes ? `<div class="small text-muted mt-1">${escapeHtml(assignment.notes)}</div>` : ''}
                                    </div>`).join('') : '<div class="text-muted">No active assignments found.</div>'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(error.message || 'Unable to load user details.')}</div>`;
        });
}

function getHierarchyOptions(level) {
    if (level === 'godina') {
        return usersHierarchyData.godinas.map(godina => ({ id: godina.id, label: godina.name }));
    }

    if (level === 'gamta') {
        return usersHierarchyData.gamtas.map(gamta => ({ id: gamta.id, label: gamta.name }));
    }

    if (level === 'gurmu') {
        return usersHierarchyData.gurmus.map(gurmu => ({ id: gurmu.id, label: gurmu.name }));
    }

    return [];
}

function buildEditAssignmentRow(index, assignment = {}) {
    return `
        <div class="card border assignment-editor-row mb-3" data-edit-assignment-row="${index}">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Hierarchy Level *</label>
                        <select class="form-select" name="assignments[${index}][hierarchy_level]" onchange="loadEditHierarchyOptions(${index})" required>
                            <option value="">Select Level</option>
                            <option value="global" ${assignment.level_scope === 'global' ? 'selected' : ''}>Global</option>
                            <option value="godina" ${assignment.level_scope === 'godina' ? 'selected' : ''}>Godina</option>
                            <option value="gamta" ${assignment.level_scope === 'gamta' ? 'selected' : ''}>Gamta</option>
                            <option value="gurmu" ${assignment.level_scope === 'gurmu' ? 'selected' : ''}>Gurmu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Organizational Unit</label>
                        <select class="form-select" name="assignments[${index}][hierarchy_id]"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Position *</label>
                        <select class="form-select" name="assignments[${index}][position_id]" required></select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="assignments[${index}][start_date]" value="${escapeHtml(normalizeDateInputValue(assignment.start_date || ''))}">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger" onclick="removeEditAssignmentRow(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Assignment Notes</label>
                        <input type="text" class="form-control" name="assignments[${index}][notes]" value="${escapeHtml(assignment.notes || '')}" placeholder="Optional notes about this assignment">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" name="assignments[${index}][end_date]" value="${escapeHtml(normalizeDateInputValue(assignment.end_date || ''))}">
                    </div>
                </div>
                <div class="assignment-responsibilities mt-3" data-edit-assignment-preview="${index}"></div>
            </div>
        </div>
    `;
}

function loadEditHierarchyOptions(index, preset = {}) {
    const row = document.querySelector(`[data-edit-assignment-row="${index}"]`);
    if (!row) {
        return;
    }

    const levelSelect = row.querySelector(`select[name="assignments[${index}][hierarchy_level]"]`);
    const hierarchySelect = row.querySelector(`select[name="assignments[${index}][hierarchy_id]"]`);
    const positionSelect = row.querySelector(`select[name="assignments[${index}][position_id]"]`);
    const previewContainer = row.querySelector(`[data-edit-assignment-preview="${index}"]`);
    const level = levelSelect.value;

    hierarchySelect.innerHTML = '<option value="">Select Unit</option>';
    positionSelect.innerHTML = '<option value="">Select Position</option>';
    positionSelect.disabled = true;
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }

    if (level === 'global') {
        hierarchySelect.disabled = true;
        loadEditPositionsForLevel(index, 'global', '', preset.position_id || '');
        return;
    }

    hierarchySelect.disabled = false;
    getHierarchyOptions(level).forEach(optionData => {
        const option = document.createElement('option');
        option.value = optionData.id;
        option.textContent = optionData.label;
        if (String(optionData.id) === String(preset.hierarchy_id || '')) {
            option.selected = true;
        }
        hierarchySelect.appendChild(option);
    });

    if (preset.hierarchy_id) {
        loadEditPositionsForLevel(index, level, preset.hierarchy_id, preset.position_id || '');
    }

    hierarchySelect.onchange = function() {
        loadEditPositionsForLevel(index, level, this.value, '');
    };
}

function loadEditPositionsForLevel(index, level, hierarchyId, selectedPositionId) {
    const row = document.querySelector(`[data-edit-assignment-row="${index}"]`);
    if (!row) {
        return;
    }

    const positionSelect = row.querySelector(`select[name="assignments[${index}][position_id]"]`);
    const previewContainer = row.querySelector(`[data-edit-assignment-preview="${index}"]`);
    const params = new URLSearchParams({ level });
    if (hierarchyId) {
        params.set('hierarchy_id', hierarchyId);
    }

    positionSelect.innerHTML = '<option value="">Loading positions...</option>';
    positionSelect.disabled = true;

    fetch(`/admin/user-leader-registration/positions-for-level?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load positions');
            }

            const positions = Array.isArray(data.data) ? data.data : [];
            positionSelect._positions = positions;
            positionSelect.innerHTML = '<option value="">Select Position</option>';

            positions.forEach(position => {
                const option = document.createElement('option');
                option.value = position.id;
                option.textContent = position.name + (position.is_occupied && String(position.id) !== String(selectedPositionId) ? ' (Occupied)' : '');
                option.disabled = Boolean(position.is_occupied) && String(position.id) !== String(selectedPositionId);
                if (String(position.id) === String(selectedPositionId)) {
                    option.selected = true;
                }
                positionSelect.appendChild(option);
            });

            positionSelect.disabled = false;
            positionSelect.onchange = function() {
                renderEditResponsibilityPreview(index, this.value);
            };
            renderEditResponsibilityPreview(index, selectedPositionId || positionSelect.value);
        })
        .catch(error => {
            positionSelect.innerHTML = '<option value="">No positions available</option>';
            positionSelect.disabled = true;
            if (previewContainer) {
                previewContainer.innerHTML = `<div class="alert alert-danger py-2 mb-0">${escapeHtml(error.message || 'Unable to load positions.')}</div>`;
            }
        });
}

function renderEditResponsibilityPreview(index, positionId) {
    const row = document.querySelector(`[data-edit-assignment-row="${index}"]`);
    if (!row) {
        return;
    }

    const positionSelect = row.querySelector(`select[name="assignments[${index}][position_id]"]`);
    const previewContainer = row.querySelector(`[data-edit-assignment-preview="${index}"]`);
    const positions = Array.isArray(positionSelect?._positions) ? positionSelect._positions : [];
    const selectedPosition = positions.find(position => String(position.id) === String(positionId));

    if (!previewContainer || !selectedPosition || !selectedPosition.responsibility_preview) {
        if (previewContainer) {
            previewContainer.innerHTML = '';
        }
        return;
    }

    const shared = selectedPosition.responsibility_preview.shared || [];
    const individual = selectedPosition.responsibility_preview.individual || [];
    previewContainer.innerHTML = `
        <div class="responsibility-preview card border-0 bg-light-subtle">
            <div class="card-body py-3">
                <div class="small text-uppercase text-muted fw-semibold mb-2">Responsibilities that will remain active</div>
                <div class="d-flex flex-wrap gap-2">
                    ${shared.concat(individual).map(item => `<span class="badge text-bg-light border">${escapeHtml(item.name_en)}</span>`).join('')}
                </div>
            </div>
        </div>
    `;
}

function removeEditAssignmentRow(index) {
    const row = document.querySelector(`[data-edit-assignment-row="${index}"]`);
    if (row) {
        row.remove();
    }
}

function addEditAssignmentRow(assignment = {}) {
    const container = document.getElementById('editAssignmentsContainer');
    const index = editAssignmentIndex++;
    container.insertAdjacentHTML('beforeend', buildEditAssignmentRow(index, assignment));
    loadEditHierarchyOptions(index, {
        hierarchy_id: assignment.organizational_unit_id || assignment.godina_id || assignment.gamta_id || assignment.gurmu_id || '',
        position_id: assignment.position_id || ''
    });
}

function openEditAssignments(userId) {
    const modalElement = document.getElementById('userAssignmentsModal');
    const modal = new bootstrap.Modal(modalElement);
    const container = document.getElementById('editAssignmentsContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>';
    document.getElementById('editAssignmentsUserId').value = userId;
    setAssignmentsFeedback('');
    modal.show();

    fetch(`/users/${userId}?format=json`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => parseJsonResponse(response, 'Unable to load user assignments right now.'))
        .then(payload => {
            if (!payload.success) {
                throw new Error(payload.message || 'Failed to load user assignments');
            }

            const user = payload.data.user || {};
            const assignments = payload.data.assignments || [];
            document.getElementById('editAssignmentsHeading').textContent = `Update assignments for ${user.first_name || ''} ${user.last_name || ''}`.trim();
            container.innerHTML = '';
            editAssignmentIndex = 0;

            if (assignments.length === 0) {
                addEditAssignmentRow();
                return;
            }

            assignments.forEach(assignment => addEditAssignmentRow(assignment));
        })
        .catch(error => {
            container.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(error.message || 'Unable to load assignments.')}</div>`;
        });
}

document.getElementById('userAssignmentsForm').addEventListener('submit', function (event) {
    event.preventDefault();
    setAssignmentsFeedback('');

    const submitButton = this.querySelector('button[type="submit"]');
    const originalHtml = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';

    const formData = new FormData(this);

    fetch('/admin/user-leader-registration/update-assignments', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => parseJsonResponse(response, 'The server returned an invalid response while updating assignments.'))
        .then(payload => {
            if (!payload.success) {
                throw new Error(payload.message || 'Failed to update assignments');
            }

            window.location.href = '/users?success=' + encodeURIComponent('assignments_updated');
        })
        .catch(error => {
            setAssignmentsFeedback(error.message || 'Unable to update assignments.');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalHtml;
        });
});

// Auto-submit search form on filter change
document.getElementById('status').addEventListener('change', function() {
    this.closest('form').submit();
});

document.getElementById('role').addEventListener('change', function() {
    this.closest('form').submit();
});

document.querySelectorAll('[data-user-view-mode]').forEach(button => {
    button.addEventListener('click', function () {
        applyUserViewMode(this.getAttribute('data-user-view-mode'));
    });
});

applyUserViewMode(localStorage.getItem('usersViewMode') || 'table');

const userPageParams = new URLSearchParams(window.location.search);
if (userPageParams.has('view')) {
    openUserDetails(userPageParams.get('view'));
}
if (userPageParams.has('edit')) {
    openEditAssignments(userPageParams.get('edit'));
}
</script>

<style>
.user-card-avatar {
    width: 3rem;
    height: 3rem;
}

.user-card-surface {
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.user-card-surface:hover {
    transform: translateY(-2px);
    box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.08);
}

.responsibility-preview {
    border-left: 4px solid #0d6efd;
}

#userAssignmentsModal .modal-body {
    max-height: min(78vh, 900px);
    overflow-y: auto;
    overscroll-behavior: contain;
}

#editAssignmentsContainer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.assignment-editor-row {
    scroll-margin-top: 5rem;
}
</style>