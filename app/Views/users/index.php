<?php
/**
 * Users Index View Template
 * Comprehensive user management with hierarchical filtering
 */

// Page metadata
$pageTitle = __('users.title');
$pageDescription = __('users.description');
$bodyClass = 'users-page';

// User data
$users = $users ?? [];
$totalUsers = $totalUsers ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];

// User permissions
$canCreateUsers = $permissions['can_create_users'] ?? false;
$canApproveUsers = $permissions['can_approve_users'] ?? false;
$canManageUsers = $permissions['can_manage_users'] ?? false;

// Hierarchy data for filters
$godinas = $hierarchyData['godinas'] ?? [];
$gamtas = $hierarchyData['gamtas'] ?? [];
$gurmus = $hierarchyData['gurmus'] ?? [];
$positions = $hierarchyData['positions'] ?? [];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('users.user_management') ?></h1>
        <p class="text-muted mb-0"><?= __('users.manage_organization_members') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canCreateUsers): ?>
            <a href="/users/create" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> <?= __('users.add_user') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> <?= __('users.export') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/users/export?format=excel">
                    <i class="bi bi-file-earmark-excel me-2"></i><?= __('users.export_excel') ?>
                </a></li>
                <li><a class="dropdown-item" href="/users/export?format=pdf">
                    <i class="bi bi-file-earmark-pdf me-2"></i><?= __('users.export_pdf') ?>
                </a></li>
                <li><a class="dropdown-item" href="/users/export?format=csv">
                    <i class="bi bi-file-earmark-text me-2"></i><?= __('users.export_csv') ?>
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/users" class="filters-form">
            <div class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                               placeholder="<?= __('users.search_placeholder') ?>">
                        <label for="search"><?= __('users.search') ?></label>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-md-2">
                    <div class="form-floating">
                        <select class="form-select" id="status" name="status">
                            <option value=""><?= __('users.all_statuses') ?></option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                                <?= __('users.active') ?>
                            </option>
                            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>
                                <?= __('users.pending') ?>
                            </option>
                            <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>
                                <?= __('users.suspended') ?>
                            </option>
                            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                <?= __('users.inactive') ?>
                            </option>
                        </select>
                        <label for="status"><?= __('users.status') ?></label>
                    </div>
                </div>
                
                <!-- Role Filter -->
                <div class="col-md-2">
                    <div class="form-floating">
                        <select class="form-select" id="role" name="role">
                            <option value=""><?= __('users.all_roles') ?></option>
                            <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                <?= __('users.admin') ?>
                            </option>
                            <option value="leader" <?= ($filters['role'] ?? '') === 'leader' ? 'selected' : '' ?>>
                                <?= __('users.leader') ?>
                            </option>
                            <option value="member" <?= ($filters['role'] ?? '') === 'member' ? 'selected' : '' ?>>
                                <?= __('users.member') ?>
                            </option>
                        </select>
                        <label for="role"><?= __('users.role') ?></label>
                    </div>
                </div>
                
                <!-- Godina Filter -->
                <div class="col-md-2">
                    <div class="form-floating">
                        <select class="form-select" id="godina_id" name="godina_id">
                            <option value=""><?= __('users.all_godinas') ?></option>
                            <?php foreach ($godinas as $godina): ?>
                                <option value="<?= htmlspecialchars($godina['id']) ?>" 
                                        <?= ($filters['godina_id'] ?? '') == $godina['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($godina['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="godina_id"><?= __('users.godina') ?></label>
                    </div>
                </div>
                
                <!-- Gamta Filter -->
                <div class="col-md-2">
                    <div class="form-floating">
                        <select class="form-select" id="gamta_id" name="gamta_id">
                            <option value=""><?= __('users.all_gamtas') ?></option>
                            <?php foreach ($gamtas as $gamta): ?>
                                <option value="<?= htmlspecialchars($gamta['id']) ?>" 
                                        <?= ($filters['gamta_id'] ?? '') == $gamta['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($gamta['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="gamta_id"><?= __('users.gamta') ?></label>
                    </div>
                </div>
                
                <!-- Gurmu Filter -->
                <div class="col-md-1">
                    <div class="form-floating">
                        <select class="form-select" id="gurmu_id" name="gurmu_id">
                            <option value=""><?= __('users.all_gurmus') ?></option>
                            <?php foreach ($gurmus as $gurmu): ?>
                                <option value="<?= htmlspecialchars($gurmu['id']) ?>" 
                                        <?= ($filters['gurmu_id'] ?? '') == $gurmu['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($gurmu['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="gurmu_id"><?= __('users.gurmu') ?></label>
                    </div>
                </div>
            </div>
            
            <div class="row g-2 mt-2">
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> <?= __('users.filter') ?>
                    </button>
                </div>
                <div class="col-auto">
                    <a href="/users" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> <?= __('users.clear_filters') ?>
                    </a>
                </div>
                <div class="col-auto ms-auto">
                    <div class="d-flex align-items-center text-muted">
                        <span><?= __('users.showing') ?> <?= count($users) ?> <?= __('users.of') ?> <?= number_format($totalUsers) ?> <?= __('users.users') ?></span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h4 class="text-muted mt-3"><?= __('users.no_users_found') ?></h4>
                <p class="text-muted"><?= __('users.no_users_description') ?></p>
                <?php if ($canCreateUsers): ?>
                    <a href="/users/create" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> <?= __('users.add_first_user') ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Bulk Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="bulk-actions" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted"><span id="selected-count">0</span> <?= __('users.selected') ?></span>
                        <?php if ($canApproveUsers): ?>
                            <button type="button" class="btn btn-sm btn-success bulk-approve">
                                <i class="bi bi-check-circle"></i> <?= __('users.approve') ?>
                            </button>
                        <?php endif; ?>
                        <?php if ($canManageUsers): ?>
                            <button type="button" class="btn btn-sm btn-warning bulk-suspend">
                                <i class="bi bi-pause-circle"></i> <?= __('users.suspend') ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-info bulk-activate">
                                <i class="bi bi-play-circle"></i> <?= __('users.activate') ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="view-options">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="view-mode" id="table-view" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="table-view">
                            <i class="bi bi-table"></i>
                        </label>
                        <input type="radio" class="btn-check" name="view-mode" id="card-view">
                        <label class="btn btn-outline-secondary btn-sm" for="card-view">
                            <i class="bi bi-grid"></i>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Table View -->
            <div id="table-view-content">
                <div class="table-responsive">
                    <table class="table table-hover users-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                    </div>
                                </th>
                                <th><?= __('users.user') ?></th>
                                <th><?= __('users.role_position') ?></th>
                                <th><?= __('users.organization') ?></th>
                                <th><?= __('users.contact') ?></th>
                                <th><?= __('users.status') ?></th>
                                <th><?= __('users.joined') ?></th>
                                <th style="width: 120px;"><?= __('users.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input user-checkbox" type="checkbox" 
                                                   value="<?= htmlspecialchars($user['id']) ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                <?php if (!empty($user['profile_image'])): ?>
                                                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" 
                                                         alt="<?= htmlspecialchars($user['first_name']) ?>" 
                                                         class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">
                                                    <a href="/users/<?= htmlspecialchars($user['id']) ?>" 
                                                       class="text-decoration-none">
                                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                    </a>
                                                </div>
                                                <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'leader' ? 'warning' : 'secondary') ?>">
                                                <?= __(ucfirst($user['role'])) ?>
                                            </span>
                                            <?php if (!empty($user['position_name'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($user['position_name']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hierarchy-path">
                                            <small class="text-muted">
                                                <?php if (!empty($user['godina_name'])): ?>
                                                    <?= htmlspecialchars($user['godina_name']) ?>
                                                    <?php if (!empty($user['gamta_name'])): ?>
                                                        → <?= htmlspecialchars($user['gamta_name']) ?>
                                                        <?php if (!empty($user['gurmu_name'])): ?>
                                                            → <?= htmlspecialchars($user['gurmu_name']) ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php if (!empty($user['phone'])): ?>
                                                <small class="d-block">
                                                    <i class="bi bi-telephone me-1"></i>
                                                    <?= htmlspecialchars($user['phone']) ?>
                                                </small>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope me-1"></i>
                                                <?= htmlspecialchars($user['email']) ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'suspended' => 'danger',
                                            'inactive' => 'secondary'
                                        ];
                                        $statusColor = $statusColors[$user['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?>">
                                            <?= __(ucfirst($user['status'])) ?>
                                        </span>
                                        <?php if ($user['status'] === 'pending' && $canApproveUsers): ?>
                                            <br><small class="text-muted"><?= __('users.needs_approval') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= format_date($user['created_at']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/users/<?= $user['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i><?= __('users.view_profile') ?>
                                                </a></li>
                                                <?php if ($canManageUsers): ?>
                                                    <li><a class="dropdown-item" href="/users/<?= $user['id'] ?>/edit">
                                                        <i class="bi bi-pencil me-2"></i><?= __('users.edit') ?>
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php if ($user['status'] === 'pending' && $canApproveUsers): ?>
                                                        <li><a class="dropdown-item text-success approve-user" 
                                                               href="#" data-user-id="<?= $user['id'] ?>">
                                                            <i class="bi bi-check-circle me-2"></i><?= __('users.approve') ?>
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <li><a class="dropdown-item text-warning suspend-user" 
                                                               href="#" data-user-id="<?= $user['id'] ?>">
                                                            <i class="bi bi-pause-circle me-2"></i><?= __('users.suspend') ?>
                                                        </a></li>
                                                    <?php elseif ($user['status'] === 'suspended'): ?>
                                                        <li><a class="dropdown-item text-success activate-user" 
                                                               href="#" data-user-id="<?= $user['id'] ?>">
                                                            <i class="bi bi-play-circle me-2"></i><?= __('users.activate') ?>
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger delete-user" 
                                                           href="#" data-user-id="<?= $user['id'] ?>">
                                                        <i class="bi bi-trash me-2"></i><?= __('users.delete') ?>
                                                    </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Card View -->
            <div id="card-view-content" style="display: none;">
                <div class="row g-3">
                    <?php foreach ($users as $user): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card user-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="form-check me-3">
                                            <input class="form-check-input user-checkbox" type="checkbox" 
                                                   value="<?= htmlspecialchars($user['id']) ?>">
                                        </div>
                                        <div class="avatar me-3">
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <img src="<?= htmlspecialchars($user['profile_image']) ?>" 
                                                     alt="<?= htmlspecialchars($user['first_name']) ?>" 
                                                     class="rounded-circle" width="50" height="50">
                                            <?php else: ?>
                                                <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1">
                                                <a href="/users/<?= htmlspecialchars($user['id']) ?>" 
                                                   class="text-decoration-none">
                                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <!-- Same dropdown menu as table view -->
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'leader' ? 'warning' : 'secondary') ?> me-2">
                                            <?= __(ucfirst($user['role'])) ?>
                                        </span>
                                        <span class="badge bg-<?= $statusColor ?>">
                                            <?= __(ucfirst($user['status'])) ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($user['position_name'])): ?>
                                        <p class="mb-2 small text-muted">
                                            <i class="bi bi-person-badge me-1"></i>
                                            <?= htmlspecialchars($user['position_name']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="mb-2 small text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?php if (!empty($user['godina_name'])): ?>
                                            <?= htmlspecialchars($user['godina_name']) ?>
                                            <?php if (!empty($user['gamta_name'])): ?>
                                                → <?= htmlspecialchars($user['gamta_name']) ?>
                                                <?php if (!empty($user['gurmu_name'])): ?>
                                                    → <?= htmlspecialchars($user['gurmu_name']) ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <?php if (!empty($user['phone'])): ?>
                                        <p class="mb-1 small text-muted">
                                            <i class="bi bi-telephone me-1"></i>
                                            <?= htmlspecialchars($user['phone']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="mb-0 small text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= __('users.joined') ?> <?= format_date($user['created_at']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="<?= __('users.pagination') ?>" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= build_query_string($filters) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= build_query_string($filters) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= build_query_string($filters) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Additional Styles -->
<style>
.user-card {
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.user-card:hover {
    border-color: #007bff;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.avatar-placeholder {
    font-size: 0.875rem;
    font-weight: 600;
}

.hierarchy-path {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bulk-actions {
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
}

.users-table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
}

.users-table td {
    vertical-align: middle;
}

.filters-form .form-floating > label {
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .hierarchy-path {
        max-width: 150px;
    }
}
</style>

<!-- Users Management JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View mode switching
    const tableView = document.getElementById('table-view');
    const cardView = document.getElementById('card-view');
    const tableContent = document.getElementById('table-view-content');
    const cardContent = document.getElementById('card-view-content');
    
    tableView.addEventListener('change', function() {
        if (this.checked) {
            tableContent.style.display = 'block';
            cardContent.style.display = 'none';
        }
    });
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            tableContent.style.display = 'none';
            cardContent.style.display = 'block';
        }
    });
    
    // Bulk selection
    const selectAll = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    selectAll?.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
    
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        const count = selectedUsers.length;
        
        if (selectedCount) {
            selectedCount.textContent = count;
        }
        
        if (bulkActions) {
            bulkActions.style.display = count > 0 ? 'block' : 'none';
        }
        
        // Update select all checkbox state
        if (selectAll) {
            selectAll.indeterminate = count > 0 && count < userCheckboxes.length;
            selectAll.checked = count === userCheckboxes.length;
        }
    }
    
    // Hierarchy cascade for filters
    const godinaSelect = document.getElementById('godina_id');
    const gamtaSelect = document.getElementById('gamta_id');
    const gurmuSelect = document.getElementById('gurmu_id');
    
    godinaSelect?.addEventListener('change', function() {
        const godinaId = this.value;
        
        // Reset dependent dropdowns
        gamtaSelect.innerHTML = '<option value=""><?= __('users.all_gamtas') ?></option>';
        gurmuSelect.innerHTML = '<option value=""><?= __('users.all_gurmus') ?></option>';
        
        if (godinaId) {
            fetch(`/api/hierarchy/gamtas?godina_id=${godinaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.gamtas.forEach(gamta => {
                            const option = document.createElement('option');
                            option.value = gamta.id;
                            option.textContent = gamta.name;
                            gamtaSelect.appendChild(option);
                        });
                    }
                });
        }
    });
    
    gamtaSelect?.addEventListener('change', function() {
        const gamtaId = this.value;
        
        gurmuSelect.innerHTML = '<option value=""><?= __('users.all_gurmus') ?></option>';
        
        if (gamtaId) {
            fetch(`/api/hierarchy/gurmus?gamta_id=${gamtaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.gurmus.forEach(gurmu => {
                            const option = document.createElement('option');
                            option.value = gurmu.id;
                            option.textContent = gurmu.name;
                            gurmuSelect.appendChild(option);
                        });
                    }
                });
        }
    });
    
    // User actions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('approve-user') || e.target.closest('.approve-user')) {
            e.preventDefault();
            const userId = e.target.dataset.userId || e.target.closest('.approve-user').dataset.userId;
            approveUser(userId);
        }
        
        if (e.target.classList.contains('suspend-user') || e.target.closest('.suspend-user')) {
            e.preventDefault();
            const userId = e.target.dataset.userId || e.target.closest('.suspend-user').dataset.userId;
            suspendUser(userId);
        }
        
        if (e.target.classList.contains('activate-user') || e.target.closest('.activate-user')) {
            e.preventDefault();
            const userId = e.target.dataset.userId || e.target.closest('.activate-user').dataset.userId;
            activateUser(userId);
        }
        
        if (e.target.classList.contains('delete-user') || e.target.closest('.delete-user')) {
            e.preventDefault();
            const userId = e.target.dataset.userId || e.target.closest('.delete-user').dataset.userId;
            deleteUser(userId);
        }
    });
    
    // Bulk actions
    document.querySelector('.bulk-approve')?.addEventListener('click', function() {
        const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        bulkApproveUsers(selectedUsers);
    });
    
    document.querySelector('.bulk-suspend')?.addEventListener('click', function() {
        const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        bulkSuspendUsers(selectedUsers);
    });
    
    document.querySelector('.bulk-activate')?.addEventListener('click', function() {
        const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        bulkActivateUsers(selectedUsers);
    });
    
    // Action functions
    function approveUser(userId) {
        if (confirm('<?= __('users.confirm_approve') ?>')) {
            updateUserStatus(userId, 'approve');
        }
    }
    
    function suspendUser(userId) {
        if (confirm('<?= __('users.confirm_suspend') ?>')) {
            updateUserStatus(userId, 'suspend');
        }
    }
    
    function activateUser(userId) {
        if (confirm('<?= __('users.confirm_activate') ?>')) {
            updateUserStatus(userId, 'activate');
        }
    }
    
    function deleteUser(userId) {
        if (confirm('<?= __('users.confirm_delete') ?>')) {
            updateUserStatus(userId, 'delete');
        }
    }
    
    function updateUserStatus(userId, action) {
        fetch(`/api/users/${userId}/${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '<?= __('users.action_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('users.action_error') ?>');
        });
    }
    
    function bulkApproveUsers(userIds) {
        if (confirm(`<?= __('users.confirm_bulk_approve') ?> ${userIds.length} <?= __('users.users') ?>?`)) {
            bulkUpdateUsers(userIds, 'approve');
        }
    }
    
    function bulkSuspendUsers(userIds) {
        if (confirm(`<?= __('users.confirm_bulk_suspend') ?> ${userIds.length} <?= __('users.users') ?>?`)) {
            bulkUpdateUsers(userIds, 'suspend');
        }
    }
    
    function bulkActivateUsers(userIds) {
        if (confirm(`<?= __('users.confirm_bulk_activate') ?> ${userIds.length} <?= __('users.users') ?>?`)) {
            bulkUpdateUsers(userIds, 'activate');
        }
    }
    
    function bulkUpdateUsers(userIds, action) {
        fetch(`/api/users/bulk/${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '<?= __('users.bulk_action_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('users.bulk_action_error') ?>');
        });
    }
});
</script>