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
            <a href="/users/create" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>
                Add User
            </a>
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
                <h4 class="card-title"><?= number_format(array_filter($users ?? [], fn($u) => $u['role'] === 'admin')) ?></h4>
                <p class="card-text text-muted">Administrators</p>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-table me-2"></i>
            Users List
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">No Users Found</h4>
                <p class="text-muted">No users match your current filters.</p>
                <a href="/users/create" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>
                    Add First User
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
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
                                        'moderator' => 'warning',
                                        'user' => 'primary'
                                    ];
                                    $roleColor = $roleColors[$user['role']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $roleColor ?>">
                                        <?= ucfirst($user['role']) ?>
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
                                    <?php if (!empty($user['position_title'])): ?>
                                        <div class="fw-semibold"><?= htmlspecialchars($user['position_title']) ?></div>
                                        <?php if (!empty($user['gamta_name'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($user['gamta_name']) ?></small>
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
                                        <a href="/users/<?= $user['id'] ?>" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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
        <?php endif; ?>
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
function confirmDelete(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteForm').action = '/users/' + userId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Auto-submit search form on filter change
document.getElementById('status').addEventListener('change', function() {
    this.closest('form').submit();
});

document.getElementById('role').addEventListener('change', function() {
    this.closest('form').submit();
});
</script>