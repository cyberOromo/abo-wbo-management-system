<?php
$currentPage = 'responsibilities';
?>

<!-- Modern Responsibilities Management Interface -->
<style>
    .responsibility-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .responsibility-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -2px rgba(0, 0, 0, 0.15);
    }
    
    .responsibility-active {
        border-left: 4px solid var(--primary-green);
    }
    
    .responsibility-inactive {
        border-left: 4px solid #6b7280;
    }
    
    .responsibility-pending {
        border-left: 4px solid #fbbf24;
    }
    
    .responsibility-archived {
        border-left: 4px solid var(--primary-red);
    }
    
    .responsibility-priority-high {
        background: linear-gradient(135deg, rgba(139, 21, 56, 0.05) 0%, white 50%);
    }
    
    .position-badge {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 4px rgba(45, 80, 22, 0.2);
    }
    
    .responsibility-type-badge {
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .type-administrative {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border-color: rgba(45, 80, 22, 0.2);
    }
    
    .type-financial {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border-color: rgba(139, 21, 56, 0.2);
    }
    
    .type-operational {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .type-governance {
        background: rgba(139, 92, 246, 0.1);
        color: #7c3aed;
        border-color: rgba(139, 92, 246, 0.2);
    }
    
    .type-community {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border-color: rgba(245, 158, 11, 0.2);
    }
    
    .responsibility-status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-active {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border: 1px solid rgba(45, 80, 22, 0.2);
    }
    
    .status-inactive {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }
    
    .status-pending {
        background: rgba(251, 191, 36, 0.1);
        color: #d97706;
        border: 1px solid rgba(251, 191, 36, 0.2);
    }
    
    .status-archived {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border: 1px solid rgba(139, 21, 56, 0.2);
    }
    
    .assignee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .assignees-stack {
        display: flex;
        margin-left: -0.25rem;
    }
    
    .assignee-avatar:not(:first-child) {
        margin-left: -0.5rem;
    }
    
    .assignee-count {
        background: #6b7280;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: -0.5rem;
        border: 2px solid white;
    }
    
    .responsibility-matrix {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .matrix-header {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        padding: 1.5rem;
        text-align: center;
    }
    
    .matrix-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 1px;
        background: #e5e7eb;
    }
    
    .matrix-cell {
        background: white;
        padding: 1rem;
        text-align: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .matrix-cell:hover {
        background: #f9fafb;
    }
    
    .matrix-cell.assigned {
        background: rgba(45, 80, 22, 0.1);
        border-left: 3px solid var(--primary-green);
    }
    
    .priority-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }
    
    .priority-high {
        background: var(--primary-red);
        box-shadow: 0 0 0 3px rgba(139, 21, 56, 0.2);
    }
    
    .priority-medium {
        background: #fbbf24;
        box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.2);
    }
    
    .priority-low {
        background: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(45, 80, 22, 0.2);
    }
    
    .responsibility-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .responsibility-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary-green), var(--primary-green-light));
    }
    
    .timeline-responsibility {
        position: relative;
        margin-bottom: 2rem;
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-responsibility::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 1.5rem;
        width: 12px;
        height: 12px;
        background: var(--primary-green);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--primary-green);
    }
    
    .create-responsibility-btn {
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
    
    .create-responsibility-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .delegation-panel {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .delegation-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .delegation-card:hover {
        background: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="page-header">
    <h1 class="page-title">Responsibilities Management</h1>
    <p class="page-description">Define, assign, and track organizational responsibilities with hierarchy-based delegation and accountability</p>
</div>

<!-- Responsibility Statistics -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-green);"><?= $responsibility_stats['total'] ?? 0 ?></div>
            <div class="text-muted fw-500">Total Responsibilities</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #fbbf24;"><?= $responsibility_stats['active'] ?? 0 ?></div>
            <div class="text-muted fw-500">Active</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-red);"><?= $responsibility_stats['unassigned'] ?? 0 ?></div>
            <div class="text-muted fw-500">Unassigned</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #3b82f6;"><?= $responsibility_stats['positions'] ?? 0 ?></div>
            <div class="text-muted fw-500">Positions</div>
        </div>
    </div>
</div>

<!-- Control Panel -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="view-toggle">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="cardView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary" for="cardView">
                            <i class="bi bi-grid-3x3-gap"></i> Cards
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="matrixView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="matrixView">
                            <i class="bi bi-grid"></i> Matrix
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="timelineView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="timelineView">
                            <i class="bi bi-clock-history"></i> Timeline
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="d-flex gap-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">✅ Active</option>
                        <option value="inactive">💤 Inactive</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="archived">📦 Archived</option>
                    </select>
                    
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="administrative">🏛️ Administrative</option>
                        <option value="financial">💰 Financial</option>
                        <option value="operational">⚙️ Operational</option>
                        <option value="governance">🏛️ Governance</option>
                        <option value="community">👥 Community</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="d-flex gap-2 justify-content-end">
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showCreateResponsibilityModal()">
                            <i class="bi bi-plus-circle"></i> Create
                        </button>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/responsibilities/export?format=pdf">📄 PDF Matrix</a></li>
                            <li><a class="dropdown-item" href="/responsibilities/export?format=excel">📊 Excel Export</a></li>
                            <li><a class="dropdown-item" href="/responsibilities/delegation-report">📋 Delegation Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card View -->
<div id="cardViewContainer">
    <div class="row g-4" id="responsibilitiesGrid">
        <?php if (!empty($responsibilities)): ?>
            <?php foreach ($responsibilities as $responsibility): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 responsibility-item" 
                     data-status="<?= $responsibility['status'] ?? 'active' ?>" 
                     data-type="<?= $responsibility['type'] ?? 'administrative' ?>">
                    <div class="responsibility-card responsibility-<?= $responsibility['status'] ?? 'active' ?> <?= ($responsibility['priority'] ?? 'medium') === 'high' ? 'responsibility-priority-high' : '' ?>">
                        <!-- Responsibility Header -->
                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start p-4">
                            <div class="d-flex gap-2 flex-wrap">
                                <div class="priority-indicator priority-<?= $responsibility['priority'] ?? 'medium' ?>" title="<?= ucfirst($responsibility['priority'] ?? 'medium') ?> Priority"></div>
                                <span class="responsibility-type-badge type-<?= $responsibility['type'] ?? 'administrative' ?>">
                                    <?= getResponsibilityTypeIcon($responsibility['type'] ?? 'administrative') ?> 
                                    <?= ucfirst($responsibility['type'] ?? 'administrative') ?>
                                </span>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/responsibilities/<?= $responsibility['id'] ?>">
                                        <i class="bi bi-eye"></i> View Details
                                    </a></li>
                                    <li><a class="dropdown-item" href="/responsibilities/<?= $responsibility['id'] ?>/edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a></li>
                                    <li><a class="dropdown-item" href="/responsibilities/<?= $responsibility['id'] ?>/delegate">
                                        <i class="bi bi-person-gear"></i> Delegate
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-warning" href="#" onclick="archiveResponsibility(<?= $responsibility['id'] ?>)">
                                        <i class="bi bi-archive"></i> Archive
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Responsibility Content -->
                        <div class="card-body p-4 pt-0">
                            <h5 class="card-title mb-2 fw-600"><?= htmlspecialchars($responsibility['title'] ?? 'Untitled Responsibility') ?></h5>
                            <p class="card-text text-muted mb-3">
                                <?= htmlspecialchars(substr($responsibility['description'] ?? 'No description provided', 0, 120)) ?>
                                <?= strlen($responsibility['description'] ?? '') > 120 ? '...' : '' ?>
                            </p>
                            
                            <!-- Position Assignment -->
                            <?php if (isset($responsibility['position_name'])): ?>
                                <div class="mb-3">
                                    <span class="position-badge">
                                        <i class="bi bi-briefcase"></i>
                                        <?= htmlspecialchars($responsibility['position_name']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Responsibility Details -->
                            <div class="mb-3">
                                <div class="row g-2 text-sm">
                                    <div class="col-6">
                                        <div class="text-muted">Hierarchy Level</div>
                                        <div class="fw-500"><?= ucfirst($responsibility['hierarchy_level'] ?? 'Not specified') ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Frequency</div>
                                        <div class="fw-500"><?= ucfirst($responsibility['frequency'] ?? 'As needed') ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Assignees -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted fw-500 d-block mb-1">Assigned To</small>
                                    <div class="assignees-stack">
                                        <?php 
                                        $assignees = json_decode($responsibility['assignees'] ?? '[]', true);
                                        $maxVisible = 3;
                                        if (!empty($assignees)): 
                                            for ($i = 0; $i < min(count($assignees), $maxVisible); $i++): 
                                        ?>
                                            <div class="assignee-avatar" title="<?= htmlspecialchars($assignees[$i]['name'] ?? 'Assignee') ?>">
                                                <?= substr($assignees[$i]['name'] ?? 'A', 0, 1) ?>
                                            </div>
                                        <?php 
                                            endfor;
                                            if (count($assignees) > $maxVisible): 
                                        ?>
                                            <div class="assignee-count" title="<?= count($assignees) - $maxVisible ?> more assignees">
                                                +<?= count($assignees) - $maxVisible ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php else: ?>
                                            <small class="text-muted">Unassigned</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <span class="responsibility-status-badge status-<?= $responsibility['status'] ?? 'active' ?>">
                                        <?= getResponsibilityStatusIcon($responsibility['status'] ?? 'active') ?> 
                                        <?= ucfirst($responsibility['status'] ?? 'active') ?>
                                    </span>
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
                        <i class="bi bi-briefcase" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Responsibilities Defined</h4>
                    <p class="text-muted mb-4">Start building your organizational structure by defining responsibilities</p>
                    <?php if ($can_create ?? true): ?>
                        <button class="create-responsibility-btn" onclick="showCreateResponsibilityModal()">
                            <i class="bi bi-plus-circle"></i> Create First Responsibility
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Matrix View (Hidden by default) -->
<div id="matrixViewContainer" style="display: none;">
    <div class="responsibility-matrix">
        <div class="matrix-header">
            <h3 class="mb-2">Responsibility Assignment Matrix (RACI)</h3>
            <p class="mb-0 opacity-90">R=Responsible, A=Accountable, C=Consulted, I=Informed</p>
        </div>
        
        <div class="matrix-grid">
            <!-- Matrix content would be populated here -->
            <div class="matrix-cell">
                <strong>Responsibility</strong>
            </div>
            <div class="matrix-cell">
                <strong>Admin</strong>
            </div>
            <div class="matrix-cell">
                <strong>Executive</strong>
            </div>
            <div class="matrix-cell">
                <strong>Member</strong>
            </div>
            
            <?php if (!empty($responsibilities)): ?>
                <?php foreach ($responsibilities as $responsibility): ?>
                    <div class="matrix-cell">
                        <strong><?= htmlspecialchars($responsibility['title']) ?></strong>
                        <div class="text-muted"><?= htmlspecialchars($responsibility['type']) ?></div>
                    </div>
                    <div class="matrix-cell <?= ($responsibility['admin_role'] ?? '') ? 'assigned' : '' ?>">
                        <?= $responsibility['admin_role'] ?? '-' ?>
                    </div>
                    <div class="matrix-cell <?= ($responsibility['executive_role'] ?? '') ? 'assigned' : '' ?>">
                        <?= $responsibility['executive_role'] ?? '-' ?>
                    </div>
                    <div class="matrix-cell <?= ($responsibility['member_role'] ?? '') ? 'assigned' : '' ?>">
                        <?= $responsibility['member_role'] ?? '-' ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Timeline View (Hidden by default) -->
<div id="timelineViewContainer" style="display: none;">
    <div class="responsibility-timeline">
        <?php if (!empty($responsibilities)): ?>
            <?php foreach ($responsibilities as $responsibility): ?>
                <div class="timeline-responsibility responsibility-item" 
                     data-status="<?= $responsibility['status'] ?? 'active' ?>" 
                     data-type="<?= $responsibility['type'] ?? 'administrative' ?>">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex gap-3">
                            <div class="priority-indicator priority-<?= $responsibility['priority'] ?? 'medium' ?>"></div>
                            <div>
                                <h6 class="fw-600 mb-1"><?= htmlspecialchars($responsibility['title'] ?? 'Untitled') ?></h6>
                                <div class="d-flex gap-2 mb-2">
                                    <span class="responsibility-type-badge type-<?= $responsibility['type'] ?? 'administrative' ?>">
                                        <?= getResponsibilityTypeIcon($responsibility['type'] ?? 'administrative') ?> 
                                        <?= ucfirst($responsibility['type'] ?? 'administrative') ?>
                                    </span>
                                    <span class="responsibility-status-badge status-<?= $responsibility['status'] ?? 'active' ?>">
                                        <?= ucfirst($responsibility['status'] ?? 'active') ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-0"><?= htmlspecialchars($responsibility['description'] ?? '') ?></p>
                            </div>
                        </div>
                        <small class="text-muted">
                            <?= date('M j, Y', strtotime($responsibility['created_at'] ?? '')) ?>
                        </small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="assignees-stack">
                            <?php 
                            $assignees = json_decode($responsibility['assignees'] ?? '[]', true);
                            if (!empty($assignees)): 
                                for ($i = 0; $i < min(count($assignees), 3); $i++): 
                            ?>
                                <div class="assignee-avatar" title="<?= htmlspecialchars($assignees[$i]['name'] ?? 'Assignee') ?>">
                                    <?= substr($assignees[$i]['name'] ?? 'A', 0, 1) ?>
                                </div>
                            <?php endfor; endif; ?>
                        </div>
                        
                        <div class="btn-group btn-group-sm">
                            <a href="/responsibilities/<?= $responsibility['id'] ?>" class="btn btn-outline-primary">View</a>
                            <a href="/responsibilities/<?= $responsibility['id'] ?>/edit" class="btn btn-outline-secondary">Edit</a>
                            <button class="btn btn-outline-warning" onclick="delegateResponsibility(<?= $responsibility['id'] ?>)">Delegate</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delegation Panel -->
<div class="row g-4 mt-4">
    <div class="col-md-8">
        <div class="delegation-panel">
            <h5 class="mb-4 fw-600">🔄 Recent Delegations</h5>
            <?php if (!empty($recent_delegations ?? [])): ?>
                <?php foreach (array_slice($recent_delegations, 0, 5) as $delegation): ?>
                    <div class="delegation-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-600 mb-1"><?= htmlspecialchars($delegation['responsibility_title'] ?? '') ?></h6>
                                <div class="text-muted mb-2">
                                    Delegated from <strong><?= htmlspecialchars($delegation['from_user'] ?? '') ?></strong> 
                                    to <strong><?= htmlspecialchars($delegation['to_user'] ?? '') ?></strong>
                                </div>
                                <small class="text-muted">
                                    <?= date('M j, Y', strtotime($delegation['created_at'] ?? '')) ?>
                                </small>
                            </div>
                            <span class="badge bg-<?= ($delegation['status'] ?? 'pending') === 'accepted' ? 'success' : 'warning' ?>">
                                <?= ucfirst($delegation['status'] ?? 'pending') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-arrow-left-right"></i> No recent delegations
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="delegation-panel">
            <h5 class="mb-4 fw-600">📊 Quick Stats</h5>
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Assignment Rate</span>
                        <span class="fw-600"><?= $responsibility_metrics['assignment_rate'] ?? '75' ?>%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?= $responsibility_metrics['assignment_rate'] ?? 75 ?>%"></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Active Delegations</span>
                        <span class="fw-600"><?= $responsibility_metrics['active_delegations'] ?? 12 ?></span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Pending Reviews</span>
                        <span class="fw-600 text-warning"><?= $responsibility_metrics['pending_reviews'] ?? 3 ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Responsibility Modal -->
<div class="modal fade" id="createResponsibilityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Responsibility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/responsibilities/create">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Responsibility Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Enter responsibility title">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Type</label>
                            <select name="type" class="form-select">
                                <option value="administrative">🏛️ Administrative</option>
                                <option value="financial">💰 Financial</option>
                                <option value="operational">⚙️ Operational</option>
                                <option value="governance">🏛️ Governance</option>
                                <option value="community">👥 Community</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Provide detailed description of the responsibility, including objectives and expectations..."></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">🟢 Low</option>
                                <option value="medium" selected>🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Hierarchy Level</label>
                            <select name="hierarchy_level" class="form-select">
                                <option value="godina">Godina Level</option>
                                <option value="gamta">Gamta Level</option>
                                <option value="gurmu">Gurmu Level</option>
                                <option value="all">All Levels</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Frequency</label>
                            <select name="frequency" class="form-select">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                                <option value="as_needed">As Needed</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Associated Position</label>
                            <select name="position_id" class="form-select">
                                <option value="">No specific position</option>
                                <!-- Populated via AJAX -->
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Initial Assignee</label>
                            <select name="assignee_id" class="form-select">
                                <option value="">Assign later</option>
                                <!-- Populated via AJAX -->
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Success Criteria</label>
                            <textarea name="success_criteria" class="form-control" rows="3" 
                                      placeholder="Define measurable criteria for successful completion of this responsibility..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_delegable" value="1" id="isDelegable" checked>
                                <label class="form-check-label fw-500" for="isDelegable">
                                    Allow delegation to other users
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Responsibility
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
    const matrixView = document.getElementById('matrixView');
    const timelineView = document.getElementById('timelineView');
    const cardContainer = document.getElementById('cardViewContainer');
    const matrixContainer = document.getElementById('matrixViewContainer');
    const timelineContainer = document.getElementById('timelineViewContainer');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'block';
            matrixContainer.style.display = 'none';
            timelineContainer.style.display = 'none';
        }
    });
    
    matrixView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            matrixContainer.style.display = 'block';
            timelineContainer.style.display = 'none';
        }
    });
    
    timelineView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            matrixContainer.style.display = 'none';
            timelineContainer.style.display = 'block';
        }
    });
    
    // Advanced filtering
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    function applyFilters() {
        const statusValue = statusFilter.value;
        const typeValue = typeFilter.value;
        
        document.querySelectorAll('.responsibility-item').forEach(item => {
            const showStatus = !statusValue || item.dataset.status === statusValue;
            const showType = !typeValue || item.dataset.type === typeValue;
            item.style.display = showStatus && showType ? 'block' : 'none';
        });
    }
    
    statusFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
});

function showCreateResponsibilityModal() {
    new bootstrap.Modal(document.getElementById('createResponsibilityModal')).show();
}

function archiveResponsibility(id) {
    if (confirm('Archive this responsibility? It will be moved to archived status.')) {
        fetch(`/responsibilities/${id}/archive`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.ok ? location.reload() : alert('Error archiving responsibility'))
        .catch(() => alert('Error archiving responsibility'));
    }
}

function delegateResponsibility(id) {
    window.location.href = `/responsibilities/${id}/delegate`;
}
</script>

<?php
// Helper functions for UI
function getResponsibilityTypeIcon($type) {
    return [
        'administrative' => '🏛️',
        'financial' => '💰',
        'operational' => '⚙️',
        'governance' => '🏛️',
        'community' => '👥'
    ][$type] ?? '🏛️';
}

function getResponsibilityStatusIcon($status) {
    return [
        'active' => '✅',
        'inactive' => '💤',
        'pending' => '⏳',
        'archived' => '📦'
    ][$status] ?? '✅';
}
?>