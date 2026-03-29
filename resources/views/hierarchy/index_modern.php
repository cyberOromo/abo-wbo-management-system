<?php
$pageTitle = $title ?? 'Organizational Hierarchy';
$layout = 'modern'; // Use the modern layout
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-diagram-3 me-2"></i>
            Organizational Hierarchy
        </h1>
        <p class="text-muted mb-0">Manage your organizational structure and assignments</p>
    </div>
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-plus-circle me-1"></i>
                Add New
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/hierarchy/create/godina">
                    <i class="bi bi-globe me-2"></i>Create Godina
                </a></li>
                <li><a class="dropdown-item" href="/hierarchy/create/gamta">
                    <i class="bi bi-house me-2"></i>Create Gamta
                </a></li>
                <li><a class="dropdown-item" href="/hierarchy/create/gurmu">
                    <i class="bi bi-people me-2"></i>Create Gurmu
                </a></li>
            </ul>
        </div>
        <a href="/hierarchy/tree" class="btn btn-outline-secondary">
            <i class="bi bi-diagram-2 me-1"></i>
            Tree View
        </a>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-globe"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_godinas'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Godinas</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        <?= $stats['active_godinas'] ?? 0 ?> Active
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-houses"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_gamtas'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Gamtas</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        <?= $stats['active_gamtas'] ?? 0 ?> Active
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-building"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_gurmus'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Gurmus</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        <?= $stats['active_gurmus'] ?? 0 ?> Active
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['assigned_users'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Assigned Users</p>
                    <?php if (($stats['unassigned_users'] ?? 0) > 0): ?>
                        <small class="text-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?= $stats['unassigned_users'] ?> Unassigned
                        </small>
                    <?php else: ?>
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i>
                            All Assigned
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="/hierarchy/tree" class="text-decoration-none">
                            <div class="action-card">
                                <div class="action-icon">
                                    <i class="bi bi-diagram-2"></i>
                                </div>
                                <h6 class="fw-bold mb-2">View Hierarchy Tree</h6>
                                <p class="text-muted small mb-0">Interactive organization chart</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <a href="/hierarchy/create/godina" class="text-decoration-none">
                            <div class="action-card">
                                <div class="action-icon">
                                    <i class="bi bi-globe-americas"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Create Godina</h6>
                                <p class="text-muted small mb-0">Add new regional unit</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <a href="/hierarchy/create/gamta" class="text-decoration-none">
                            <div class="action-card">
                                <div class="action-icon">
                                    <i class="bi bi-house-add"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Create Gamta</h6>
                                <p class="text-muted small mb-0">Add new local unit</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <a href="/users?filter=unassigned" class="text-decoration-none">
                            <div class="action-card">
                                <div class="action-icon">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Assign Users</h6>
                                <p class="text-muted small mb-0">Assign users to hierarchy</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Hierarchy Overview -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Hierarchy Overview
                </h5>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary active" data-view="summary">
                        Summary
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="detailed">
                        Detailed
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="hierarchy-summary" class="hierarchy-view">
                    <div class="alert alert-info border-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Hierarchy Structure</h6>
                                <p class="mb-0">The organizational hierarchy consists of 
                                    <strong><?= $stats['total_godinas'] ?? 0 ?> Godinas</strong> (regional units) containing 
                                    <strong><?= $stats['total_gamtas'] ?? 0 ?> Gamtas</strong> (local units) with 
                                    <strong><?= $stats['assigned_users'] ?? 0 ?> assigned users</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (($stats['unassigned_users'] ?? 0) > 0): ?>
                        <div class="alert alert-warning border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">Action Required</h6>
                                    <p class="mb-0">
                                        <a href="/users?filter=unassigned" class="fw-bold"><?= $stats['unassigned_users'] ?> users</a> 
                                        are not assigned to any Gamta and need to be organized.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-diagram-2 text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-muted mb-3">Hierarchy Management</h4>
                        <p class="text-muted mb-4">
                            Use the Tree View to visualize and manage the complete organizational structure,
                            or use the quick actions above to add new organizational units.
                        </p>
                        <a href="/hierarchy/tree" class="btn btn-primary btn-lg">
                            <i class="bi bi-diagram-2 me-2"></i>
                            View Interactive Tree
                        </a>
                    </div>
                </div>
                
                <div id="hierarchy-detailed" class="hierarchy-view" style="display: none;">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-center align-items-center py-5">
                                <div class="spinner-border text-primary me-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="text-muted">Loading detailed hierarchy view...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Hierarchy Management Guide
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6 class="text-primary mb-3">Organizational Structure</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="d-flex align-items-start p-2 rounded bg-success bg-opacity-10">
                                <div class="badge bg-success me-2 mt-1">G</div>
                                <div>
                                    <strong class="text-success">Godina</strong>
                                    <p class="text-muted small mb-0">Regional or country-level organizational units</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start p-2 rounded bg-info bg-opacity-10">
                                <div class="badge bg-info me-2 mt-1">G</div>
                                <div>
                                    <strong class="text-info">Gamta</strong>
                                    <p class="text-muted small mb-0">Local community-level units within Godinas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start p-2 rounded bg-warning bg-opacity-10">
                                <div class="badge bg-warning me-2 mt-1">U</div>
                                <div>
                                    <strong class="text-warning">Users</strong>
                                    <p class="text-muted small mb-0">Members assigned to specific Gamtas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-primary mb-3">Best Practices</h6>
                    <ul class="list-unstyled">
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                            <small class="text-muted">Create Godinas first, then add Gamtas within them</small>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                            <small class="text-muted">Use clear, consistent naming conventions</small>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                            <small class="text-muted">Assign unique codes for easy identification</small>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                            <small class="text-muted">Regularly review and update contact information</small>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                            <small class="text-muted">Ensure all users are assigned to appropriate Gamtas</small>
                        </li>
                    </ul>
                </div>

                <div class="alert alert-light border-0">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-lightbulb text-warning me-2 mt-1"></i>
                        <small>
                            <strong>Tip:</strong> Use the Tree View to visualize the complete organizational 
                            structure and identify any gaps or inconsistencies in your hierarchy.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <?php if (!empty($recentActivity)): ?>
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activity
                </h6>
                <a href="/reports/hierarchy" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eye me-1"></i>
                    View All
                </a>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($activity['description']) ?></h6>
                                    <p class="text-muted mb-0 small">
                                        <i class="bi bi-person me-1"></i>
                                        User ID: <?= $activity['user_id'] ?>
                                    </p>
                                </div>
                                <small class="text-muted">
                                    <?= time_ago($activity['created_at']) ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const viewButtons = document.querySelectorAll('[data-view]');
    const hierarchyViews = document.querySelectorAll('.hierarchy-view');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide views
            hierarchyViews.forEach(view => {
                view.style.display = 'none';
            });
            
            const targetView = document.getElementById('hierarchy-' + viewType);
            if (targetView) {
                targetView.style.display = 'block';
                
                // Load detailed view via AJAX if needed
                if (viewType === 'detailed' && !targetView.dataset.loaded) {
                    loadDetailedView(targetView);
                }
            }
        });
    });
});

function loadDetailedView(container) {
    fetch('/hierarchy/tree/data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderDetailedHierarchy(container, data.data);
                container.dataset.loaded = 'true';
            } else {
                container.innerHTML = '<div class="alert alert-danger">Failed to load hierarchy data.</div>';
            }
        })
        .catch(error => {
            container.innerHTML = '<div class="alert alert-danger">Error loading hierarchy data.</div>';
        });
}

function renderDetailedHierarchy(container, hierarchyData) {
    let html = '<div class="hierarchy-tree">';
    
    hierarchyData.forEach(godina => {
        html += `
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-globe text-success me-2"></i>
                        <strong>${godina.name} (${godina.code})</strong>
                    </div>
                    <a href="/hierarchy/${godina.id}?type=godina" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>View
                    </a>
                </div>
                <div class="card-body">
        `;
        
        if (godina.children && godina.children.length > 0) {
            html += '<div class="row g-2">';
            godina.children.forEach(gamta => {
                html += `
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-house text-info me-2"></i>
                                <span>${gamta.name} (${gamta.code})</span>
                            </div>
                            <div>
                                <span class="badge bg-secondary me-2">${gamta.user_count || 0} users</span>
                                <a href="/hierarchy/${gamta.id}?type=gamta" class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        } else {
            html += '<div class="alert alert-light mb-0"><i class="bi bi-info-circle me-1"></i>No Gamtas in this Godina</div>';
        }
        
        html += '</div></div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}
</script>
