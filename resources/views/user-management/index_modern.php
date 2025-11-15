<?php
$currentPage = 'user-management';
?>

<!-- Modern User Management Interface -->
<style>
    .user-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .user-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }
    
    .user-card.admin-user {
        border-left: 4px solid var(--primary-red);
    }
    
    .user-card.executive-user {
        border-left: 4px solid var(--primary-green);
    }
    
    .user-card.member-user {
        border-left: 4px solid #3b82f6;
    }
    
    .user-card.inactive-user {
        border-left: 4px solid #6b7280;
        opacity: 0.75;
    }
    
    .user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
        border: 4px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        position: relative;
    }
    
    .user-avatar.admin-avatar {
        background: linear-gradient(135deg, var(--primary-red), #dc2626);
    }
    
    .user-avatar.executive-avatar {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
    }
    
    .user-avatar.member-avatar {
        background: linear-gradient(135deg, #3b82f6, #1e40af);
    }
    
    .user-status-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid white;
    }
    
    .status-online {
        background: #10b981;
    }
    
    .status-offline {
        background: #6b7280;
    }
    
    .status-away {
        background: #f59e0b;
    }
    
    .role-badge {
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .role-admin {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border: 1px solid rgba(139, 21, 56, 0.2);
    }
    
    .role-executive {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border: 1px solid rgba(45, 80, 22, 0.2);
    }
    
    .role-member {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    .role-guest {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }
    
    .user-activity-indicator {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .activity-recent {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    .activity-moderate {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    
    .activity-inactive {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }
    
    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .permission-category {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }
    
    .permission-category h6 {
        color: var(--primary-green);
        margin-bottom: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .permission-item {
        display: flex;
        justify-content: between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .permission-item:last-child {
        border-bottom: none;
    }
    
    .permission-toggle {
        width: 40px;
        height: 20px;
        background: #e5e7eb;
        border-radius: 10px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .permission-toggle.active {
        background: var(--primary-green);
    }
    
    .permission-toggle::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    
    .permission-toggle.active::after {
        left: 22px;
    }
    
    .user-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .user-stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .user-stat-card:hover {
        border-color: var(--primary-green);
        box-shadow: 0 4px 12px rgba(45, 80, 22, 0.15);
    }
    
    .user-activity-timeline {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }
    
    .activity-item {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    
    .activity-content {
        flex-grow: 1;
    }
    
    .activity-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .activity-description {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .activity-timestamp {
        color: #9ca3af;
        font-size: 0.75rem;
    }
    
    .create-user-btn {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(45, 80, 22, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .create-user-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .user-search-bar {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }
    
    .bulk-action-panel {
        background: linear-gradient(135deg, #f8fafc 0%, white 100%);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
        display: none;
    }
    
    .bulk-action-panel.active {
        display: block;
    }
    
    .user-table-container {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }
    
    .user-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .user-table th {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        padding: 1rem;
        font-weight: 600;
        text-align: left;
        border: none;
    }
    
    .user-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .user-table tbody tr:hover {
        background: #f8fafc;
    }
</style>

<div class="page-header">
    <h1 class="page-title">User Management</h1>
    <p class="page-description">Comprehensive user administration with role-based access control and activity monitoring</p>
</div>

<!-- User Statistics -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stats-number" style="color: var(--primary-green);"><?= $user_stats['total_users'] ?? 0 ?></div>
        <div class="text-muted fw-500">Total Users</div>
    </div>
    <div class="user-stat-card">
        <div class="stats-number" style="color: #10b981;"><?= $user_stats['active_users'] ?? 0 ?></div>
        <div class="text-muted fw-500">Active</div>
    </div>
    <div class="user-stat-card">
        <div class="stats-number" style="color: var(--primary-red);"><?= $user_stats['admin_users'] ?? 0 ?></div>
        <div class="text-muted fw-500">Administrators</div>
    </div>
    <div class="user-stat-card">
        <div class="stats-number" style="color: #3b82f6;"><?= $user_stats['executive_users'] ?? 0 ?></div>
        <div class="text-muted fw-500">Executives</div>
    </div>
    <div class="user-stat-card">
        <div class="stats-number" style="color: #f59e0b;"><?= $user_stats['member_users'] ?? 0 ?></div>
        <div class="text-muted fw-500">Members</div>
    </div>
    <div class="user-stat-card">
        <div class="stats-number" style="color: #6b7280;"><?= $user_stats['inactive_users'] ?? 0 ?></div>
        <div class="text-muted fw-500">Inactive</div>
    </div>
</div>

<!-- Search and Control Panel -->
<div class="user-search-bar">
    <div class="row align-items-center g-3">
        <div class="col-md-4">
            <div class="view-toggle">
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="viewMode" id="cardView" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="cardView">
                        <i class="bi bi-grid-3x2-gap"></i> Cards
                    </label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="tableView" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="tableView">
                        <i class="bi bi-table"></i> Table
                    </label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="listView">
                        <i class="bi bi-list-ul"></i> List
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="userSearch" placeholder="Search users by name, email, role...">
                </div>
                
                <select class="form-select" id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="admin">🔧 Admin</option>
                    <option value="executive">👔 Executive</option>
                    <option value="member">👤 Member</option>
                    <option value="guest">👻 Guest</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="d-flex gap-2 justify-content-end">
                <?php if ($can_create ?? true): ?>
                    <button class="btn btn-primary" onclick="showCreateUserModal()">
                        <i class="bi bi-person-plus"></i> Create User
                    </button>
                <?php endif; ?>
                
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" onclick="toggleBulkActions()">
                            <i class="bi bi-check2-square"></i> Bulk Actions
                        </a></li>
                        <li><a class="dropdown-item" href="/users/export">
                            <i class="bi bi-download"></i> Export Users
                        </a></li>
                        <li><a class="dropdown-item" onclick="showImportModal()">
                            <i class="bi bi-upload"></i> Import Users
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/users/permissions">
                            <i class="bi bi-shield-lock"></i> Manage Permissions
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Panel -->
<div id="bulkActionPanel" class="bulk-action-panel">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span id="selectedCount" class="fw-600">0 users selected</span>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary btn-sm" onclick="bulkUpdateRole()">
                <i class="bi bi-person-gear"></i> Update Role
            </button>
            <button class="btn btn-outline-warning btn-sm" onclick="bulkDeactivate()">
                <i class="bi bi-person-x"></i> Deactivate
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="bulkActivate()">
                <i class="bi bi-person-check"></i> Activate
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="bulkDelete()">
                <i class="bi bi-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Card View (Default) -->
<div id="cardViewContainer">
    <div class="row g-4" id="usersGrid">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 user-item" 
                     data-role="<?= $user['role'] ?? 'member' ?>" 
                     data-status="<?= $user['status'] ?? 'active' ?>">
                    <div class="user-card <?= ($user['role'] ?? 'member') ?>-user">
                        <!-- User Card Header -->
                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar <?= ($user['role'] ?? 'member') ?>-avatar">
                                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                                    <div class="user-status-indicator status-<?= $user['online_status'] ?? 'offline' ?>"></div>
                                </div>
                                
                                <div>
                                    <h5 class="fw-600 mb-1"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h5>
                                    <div class="role-badge role-<?= $user['role'] ?? 'member' ?>">
                                        <?= getUserRoleIcon($user['role'] ?? 'member') ?> 
                                        <?= ucfirst($user['role'] ?? 'member') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/users/<?= $user['id'] ?>">
                                        <i class="bi bi-eye"></i> View Profile
                                    </a></li>
                                    <li><a class="dropdown-item" href="/users/<?= $user['id'] ?>/edit">
                                        <i class="bi bi-pencil"></i> Edit User
                                    </a></li>
                                    <li><a class="dropdown-item" href="/users/<?= $user['id'] ?>/permissions">
                                        <i class="bi bi-shield-lock"></i> Permissions
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/users/<?= $user['id'] ?>/reset-password">
                                        <i class="bi bi-key"></i> Reset Password
                                    </a></li>
                                    <?php if (($user['status'] ?? 'active') === 'active'): ?>
                                        <li><a class="dropdown-item text-warning" onclick="deactivateUser(<?= $user['id'] ?>)">
                                            <i class="bi bi-person-x"></i> Deactivate
                                        </a></li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item text-success" onclick="activateUser(<?= $user['id'] ?>)">
                                            <i class="bi bi-person-check"></i> Activate
                                        </a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- User Information -->
                        <div class="card-body p-4 pt-0">
                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <small class="text-muted d-block">Email</small>
                                    <div class="fw-500"><?= htmlspecialchars($user['email'] ?? 'No email') ?></div>
                                </div>
                                
                                <div class="col-6">
                                    <small class="text-muted d-block">Department</small>
                                    <div class="fw-500"><?= htmlspecialchars($user['department'] ?? 'Not assigned') ?></div>
                                </div>
                                
                                <div class="col-6">
                                    <small class="text-muted d-block">Position</small>
                                    <div class="fw-500"><?= htmlspecialchars($user['position'] ?? 'Not assigned') ?></div>
                                </div>
                            </div>
                            
                            <!-- Activity Status -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="text-muted d-block">Last Activity</small>
                                    <div class="fw-500"><?= date('M j, Y', strtotime($user['last_login'] ?? '')) ?></div>
                                </div>
                                
                                <div class="user-activity-indicator activity-<?= getUserActivityLevel($user['last_login'] ?? '') ?>">
                                    <?= getUserActivityLevel($user['last_login'] ?? '') ?>
                                </div>
                            </div>
                            
                            <!-- User Stats -->
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <div class="fw-600" style="color: var(--primary-green);"><?= $user['login_count'] ?? 0 ?></div>
                                        <small class="text-muted">Logins</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <div class="fw-600" style="color: #3b82f6;"><?= $user['task_count'] ?? 0 ?></div>
                                        <small class="text-muted">Tasks</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <div class="fw-600" style="color: #f59e0b;"><?= $user['event_count'] ?? 0 ?></div>
                                        <small class="text-muted">Events</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-people" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Users Found</h4>
                    <p class="text-muted mb-4">Start by creating your first user account</p>
                    <?php if ($can_create ?? true): ?>
                        <button class="create-user-btn" onclick="showCreateUserModal()">
                            <i class="bi bi-person-plus"></i> Create First User
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Table View (Hidden by default) -->
<div id="tableViewContainer" style="display: none;">
    <div class="user-table-container">
        <table class="user-table">
            <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" id="selectAllUsers" class="form-check-input">
                    </th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Last Activity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="user-item" data-role="<?= $user['role'] ?? 'member' ?>" data-status="<?= $user['status'] ?? 'active' ?>">
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" value="<?= $user['id'] ?>">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar <?= ($user['role'] ?? 'member') ?>-avatar" style="width: 48px; height: 48px; font-size: 1.1rem;">
                                        <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                                        <div class="user-status-indicator status-<?= $user['online_status'] ?? 'offline' ?>"></div>
                                    </div>
                                    <div>
                                        <div class="fw-600"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?= $user['role'] ?? 'member' ?>">
                                    <?= getUserRoleIcon($user['role'] ?? 'member') ?> 
                                    <?= ucfirst($user['role'] ?? 'member') ?>
                                </span>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($user['department'] ?? 'Not assigned') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($user['position'] ?? 'No position') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= ($user['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($user['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td>
                                <div><?= date('M j, Y', strtotime($user['last_login'] ?? '')) ?></div>
                                <div class="user-activity-indicator activity-<?= getUserActivityLevel($user['last_login'] ?? '') ?>">
                                    <?= getUserActivityLevel($user['last_login'] ?? '') ?>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/users/<?= $user['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-danger" onclick="confirmDeleteUser(<?= $user['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- List View (Hidden by default) -->
<div id="listViewContainer" style="display: none;">
    <div class="user-activity-timeline">
        <h5 class="mb-4 fw-600">👥 All Users</h5>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <div class="activity-item user-item" data-role="<?= $user['role'] ?? 'member' ?>" data-status="<?= $user['status'] ?? 'active' ?>">
                    <div class="activity-icon">
                        <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    
                    <div class="activity-content">
                        <div class="activity-title">
                            <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
                            <span class="role-badge role-<?= $user['role'] ?? 'member' ?> ms-2">
                                <?= getUserRoleIcon($user['role'] ?? 'member') ?> 
                                <?= ucfirst($user['role'] ?? 'member') ?>
                            </span>
                        </div>
                        <div class="activity-description">
                            <?= htmlspecialchars($user['email'] ?? 'No email') ?> • 
                            <?= htmlspecialchars($user['department'] ?? 'No department') ?> • 
                            <?= htmlspecialchars($user['position'] ?? 'No position') ?>
                        </div>
                        <div class="activity-timestamp">
                            Last login: <?= date('M j, Y g:i A', strtotime($user['last_login'] ?? '')) ?>
                        </div>
                    </div>
                    
                    <div class="btn-group btn-group-sm">
                        <a href="/users/<?= $user['id'] ?>" class="btn btn-outline-primary">View</a>
                        <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-outline-secondary">Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Recent User Activity Panel -->
<div class="row g-4 mt-4">
    <div class="col-md-8">
        <div class="user-activity-timeline">
            <h5 class="mb-4 fw-600">🔄 Recent User Activity</h5>
            <?php if (!empty($recent_activity ?? [])): ?>
                <?php foreach (array_slice($recent_activity, 0, 10) as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="bi bi-<?= getActivityIcon($activity['type'] ?? 'info') ?>"></i>
                        </div>
                        
                        <div class="activity-content">
                            <div class="activity-title">
                                <?= htmlspecialchars($activity['title'] ?? 'User activity') ?>
                            </div>
                            <div class="activity-description">
                                <?= htmlspecialchars($activity['description'] ?? 'No description') ?>
                            </div>
                            <div class="activity-timestamp">
                                <?= date('M j, Y g:i A', strtotime($activity['created_at'] ?? '')) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                    <div class="mt-2">No recent activity</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="user-activity-timeline">
            <h5 class="mb-4 fw-600">📊 User Insights</h5>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">User Engagement</span>
                    <span class="fw-600"><?= ($user_metrics['engagement_rate'] ?? 78) ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: <?= ($user_metrics['engagement_rate'] ?? 78) ?>%"></div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Active This Week</span>
                    <span class="fw-600"><?= ($user_metrics['weekly_active'] ?? 85) ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-info" style="width: <?= ($user_metrics['weekly_active'] ?? 85) ?>%"></div>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-6">
                    <div class="text-center p-2">
                        <div class="fw-600" style="color: var(--primary-green);"><?= $user_metrics['new_users_month'] ?? 12 ?></div>
                        <small class="text-muted">New This Month</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2">
                        <div class="fw-600" style="color: #f59e0b;"><?= $user_metrics['pending_approvals'] ?? 3 ?></div>
                        <small class="text-muted">Pending Approval</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/users/create">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="Enter first name">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="Enter last name">
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label fw-500">Email Address *</label>
                            <input type="email" name="email" class="form-control" required placeholder="Enter email address">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Role *</label>
                            <select name="role" class="form-select" required>
                                <option value="">Select role</option>
                                <option value="admin">🔧 Administrator</option>
                                <option value="executive">👔 Executive</option>
                                <option value="member">👤 Member</option>
                                <option value="guest">👻 Guest</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Password *</label>
                            <input type="password" name="password" class="form-control" required placeholder="Enter password">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Confirm Password *</label>
                            <input type="password" name="password_confirm" class="form-control" required placeholder="Confirm password">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Department</label>
                            <select name="department" class="form-select">
                                <option value="">Select department</option>
                                <option value="administration">Administration</option>
                                <option value="finance">Finance</option>
                                <option value="operations">Operations</option>
                                <option value="community">Community Affairs</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Position</label>
                            <input type="text" name="position" class="form-control" placeholder="Enter position title">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="Enter phone number">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">✅ Active</option>
                                <option value="inactive">💤 Inactive</option>
                                <option value="pending">⏳ Pending</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes about the user..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_welcome_email" value="1" id="sendWelcomeEmail" checked>
                                <label class="form-check-label fw-500" for="sendWelcomeEmail">
                                    Send welcome email with login instructions
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switching functionality
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const listView = document.getElementById('listView');
    const cardContainer = document.getElementById('cardViewContainer');
    const tableContainer = document.getElementById('tableViewContainer');
    const listContainer = document.getElementById('listViewContainer');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'block';
            tableContainer.style.display = 'none';
            listContainer.style.display = 'none';
        }
    });
    
    tableView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            tableContainer.style.display = 'block';
            listContainer.style.display = 'none';
        }
    });
    
    listView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            tableContainer.style.display = 'none';
            listContainer.style.display = 'block';
        }
    });
    
    // Search and filter functionality
    const searchInput = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');
    
    function applyFilters() {
        const searchValue = searchInput.value.toLowerCase();
        const roleValue = roleFilter.value;
        
        document.querySelectorAll('.user-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            const showSearch = !searchValue || text.includes(searchValue);
            const showRole = !roleValue || item.dataset.role === roleValue;
            
            item.style.display = showSearch && showRole ? 'block' : 'none';
        });
    }
    
    searchInput.addEventListener('input', applyFilters);
    roleFilter.addEventListener('change', applyFilters);
    
    // Bulk selection
    const selectAllCheckbox = document.getElementById('selectAllUsers');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkPanel = document.getElementById('bulkActionPanel');
    const selectedCount = document.getElementById('selectedCount');
    
    function updateBulkPanel() {
        const checked = document.querySelectorAll('.user-checkbox:checked').length;
        selectedCount.textContent = `${checked} users selected`;
        bulkPanel.classList.toggle('active', checked > 0);
    }
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkPanel();
        });
    }
    
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkPanel);
    });
});

function showCreateUserModal() {
    new bootstrap.Modal(document.getElementById('createUserModal')).show();
}

function toggleBulkActions() {
    const tableView = document.getElementById('tableView');
    tableView.checked = true;
    tableView.dispatchEvent(new Event('change'));
}

function deactivateUser(userId) {
    if (confirm('Deactivate this user? They will not be able to log in.')) {
        fetch(`/users/${userId}/deactivate`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.ok ? location.reload() : alert('Error deactivating user'))
        .catch(() => alert('Error deactivating user'));
    }
}

function activateUser(userId) {
    fetch(`/users/${userId}/activate`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.ok ? location.reload() : alert('Error activating user'))
    .catch(() => alert('Error activating user'));
}

function bulkUpdateRole() {
    const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return;
    
    const role = prompt('Enter new role (admin, executive, member, guest):');
    if (role && ['admin', 'executive', 'member', 'guest'].includes(role)) {
        // Would implement bulk role update
        alert(`Would update ${selected.length} users to ${role} role`);
    }
}

function bulkDeactivate() {
    const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return;
    
    if (confirm(`Deactivate ${selected.length} selected users?`)) {
        // Would implement bulk deactivation
        alert(`Would deactivate ${selected.length} users`);
    }
}

function bulkActivate() {
    const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return;
    
    // Would implement bulk activation
    alert(`Would activate ${selected.length} users`);
}

function bulkDelete() {
    const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return;
    
    if (confirm(`PERMANENTLY DELETE ${selected.length} selected users? This cannot be undone.`)) {
        // Would implement bulk deletion
        alert(`Would delete ${selected.length} users`);
    }
}
</script>

<?php
// Helper functions for UI
function getUserRoleIcon($role) {
    return [
        'admin' => '🔧',
        'executive' => '👔',
        'member' => '👤',
        'guest' => '👻'
    ][$role] ?? '👤';
}

function getUserActivityLevel($lastLogin) {
    if (empty($lastLogin)) return 'inactive';
    
    $lastLoginDate = strtotime($lastLogin);
    $now = time();
    $daysSince = ($now - $lastLoginDate) / (60 * 60 * 24);
    
    if ($daysSince <= 1) return 'recent';
    if ($daysSince <= 7) return 'moderate';
    return 'inactive';
}

function getActivityIcon($type) {
    return [
        'login' => 'box-arrow-in-right',
        'logout' => 'box-arrow-right',
        'create' => 'plus-circle',
        'update' => 'pencil',
        'delete' => 'trash',
        'view' => 'eye',
        'download' => 'download'
    ][$type] ?? 'info-circle';
}
?>