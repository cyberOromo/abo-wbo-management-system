<?php
$pageTitle = $title ?? 'Organizational Hierarchy';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Hierarchy', 'url' => '/hierarchy', 'active' => true]
];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-diagram-3 me-2"></i>
            Organizational Hierarchy
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
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
            <div class="btn-group">
                <a href="/hierarchy/tree" class="btn btn-outline-secondary">
                    <i class="bi bi-diagram-2 me-1"></i>
                    Tree View
                </a>
            </div>
        </div>
    </div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card card-stats border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-globe text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['total_godinas'] ?? 0) ?></h4>
                        <p class="text-muted mb-0">Total Godinas</p>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i>
                            <?= $stats['active_godinas'] ?? 0 ?> Active
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card card-stats border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-houses text-success" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['total_gamtas'] ?? 0) ?></h4>
                        <p class="text-muted mb-0">Total Gamtas</p>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i>
                            <?= $stats['active_gamtas'] ?? 0 ?> Active
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card card-stats border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-building text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['total_gurmus'] ?? 0) ?></h4>
                        <p class="text-muted mb-0">Total Gurmus</p>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i>
                            <?= $stats['active_gurmus'] ?? 0 ?> Active
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card card-stats border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['assigned_users'] ?? 0) ?></h4>
                        <p class="text-muted mb-0">Assigned Users</p>
                        <small class="text-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?= $stats['unassigned_users'] ?? 0 ?> Unassigned
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card card-stats border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-graph-up text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= count($recentActivity ?? []) ?></h4>
                        <p class="text-muted mb-0">Recent Activity</p>
                        <small class="text-muted">Last 7 days</small>
                    </div>
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
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="/hierarchy/tree" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-diagram-2 mb-2" style="font-size: 2rem;"></i>
                            <span>View Hierarchy Tree</span>
                            <small class="text-muted">Interactive organization chart</small>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/hierarchy/create/godina" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-globe-americas mb-2" style="font-size: 2rem;"></i>
                            <span>Create Godina</span>
                            <small class="text-muted">Add new regional unit</small>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/hierarchy/create/gamta" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-house-add mb-2" style="font-size: 2rem;"></i>
                            <span>Create Gamta</span>
                            <small class="text-muted">Add new local unit</small>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/users?filter=unassigned" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-person-plus mb-2" style="font-size: 2rem;"></i>
                            <span>Assign Users</span>
                            <small class="text-muted">Assign users to hierarchy</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<?php if (!empty($recentActivity)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activity
                </h5>
                <a href="/reports/hierarchy" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eye me-1"></i>
                    View All
                </a>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <?php
                                $actionIcons = [
                                    'godina.created' => 'bi-plus-circle text-success',
                                    'godina.updated' => 'bi-pencil text-warning',
                                    'godina.deleted' => 'bi-trash text-danger',
                                    'gamta.created' => 'bi-plus-circle text-success',
                                    'gamta.updated' => 'bi-pencil text-warning',
                                    'gamta.deleted' => 'bi-trash text-danger'
                                ];
                                $iconClass = $actionIcons[$activity['action']] ?? 'bi-info-circle text-info';
                                ?>
                                <i class="bi <?= $iconClass ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($activity['description']) ?></h6>
                                        <p class="text-muted mb-0">
                                            <small>
                                                <i class="bi bi-person me-1"></i>
                                                User ID: <?= $activity['user_id'] ?>
                                            </small>
                                        </p>
                                    </div>
                                    <small class="text-muted">
                                        <?= time_ago($activity['created_at']) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Hierarchy Overview -->
<div class="row">
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
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Hierarchy Structure:</strong> The organizational hierarchy consists of 
                        <strong><?= $stats['total_godinas'] ?? 0 ?> Godinas</strong> (regional units) containing 
                        <strong><?= $stats['total_gamtas'] ?? 0 ?> Gamtas</strong> (local units) with 
                        <strong><?= $stats['assigned_users'] ?? 0 ?> assigned users</strong>.
                    </div>
                    
                    <?php if (($stats['unassigned_users'] ?? 0) > 0): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Action Required:</strong> 
                            <a href="/users?filter=unassigned"><?= $stats['unassigned_users'] ?> users</a> 
                            are not assigned to any Gamta and need to be organized.
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center py-5">
                        <i class="bi bi-diagram-2 text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">Hierarchy Management</h4>
                        <p class="text-muted">
                            Use the Tree View to visualize and manage the complete organizational structure,
                            or use the quick actions above to add new organizational units.
                        </p>
                        <a href="/hierarchy/tree" class="btn btn-primary">
                            <i class="bi bi-diagram-2 me-1"></i>
                            View Interactive Tree
                        </a>
                    </div>
                </div>
                
                <div id="hierarchy-detailed" class="hierarchy-view" style="display: none;">
                    <div class="row">
                        <div class="col-12">
                            <p class="text-muted">Loading detailed hierarchy view...</p>
                            <!-- Detailed view will be loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Hierarchy Management Guide
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Organizational Structure</h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong class="text-success">Godina:</strong>
                            <span class="text-muted">Regional or country-level organizational units</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-info">Gamta:</strong>
                            <span class="text-muted">Local community-level units within Godinas</span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-warning">Users:</strong>
                            <span class="text-muted">Members assigned to specific Gamtas</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="text-primary">Best Practices</h6>
                    <ul class="small text-muted">
                        <li>Create Godinas first, then add Gamtas within them</li>
                        <li>Use clear, consistent naming conventions</li>
                        <li>Assign unique codes for easy identification</li>
                        <li>Regularly review and update contact information</li>
                        <li>Ensure all users are assigned to appropriate Gamtas</li>
                    </ul>
                </div>

                <div class="alert alert-light">
                    <small>
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Tip:</strong> Use the Tree View to visualize the complete organizational 
                        structure and identify any gaps or inconsistencies in your hierarchy.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-stats {
    transition: transform 0.2s ease-in-out;
}

.card-stats:hover {
    transform: translateY(-2px);
}

.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 1rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.4rem;
    top: 1.5rem;
    width: 2px;
    height: calc(100% - 0.5rem);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -1.75rem;
    top: 0.25rem;
    width: 1.5rem;
    height: 1.5rem;
    background-color: white;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-content {
    margin-left: 0.5rem;
}

.hierarchy-view {
    min-height: 300px;
}
</style>

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
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div><p class="mt-2">Loading...</p></div>';
    
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
            <div class="hierarchy-node godina-node mb-3">
                <div class="node-header">
                    <h6><i class="bi bi-globe me-2"></i>${godina.name} (${godina.code})</h6>
                    <div class="node-actions">
                        <a href="/hierarchy/${godina.id}?type=godina" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
                <div class="node-children">
        `;
        
        if (godina.children && godina.children.length > 0) {
            godina.children.forEach(gamta => {
                html += `
                    <div class="hierarchy-node gamta-node">
                        <div class="node-content">
                            <i class="bi bi-house me-2"></i>
                            <span>${gamta.name} (${gamta.code})</span>
                            <span class="badge bg-secondary ms-2">${gamta.user_count || 0} users</span>
                            <a href="/hierarchy/${gamta.id}?type=gamta" class="btn btn-sm btn-outline-primary ms-2">View</a>
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<div class="text-muted small"><i class="bi bi-info-circle me-1"></i>No Gamtas in this Godina</div>';
        }
        
        html += '</div></div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}
</script>

<style>
.hierarchy-tree .hierarchy-node {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
}

.godina-node {
    background-color: #f8f9fa;
}

.gamta-node {
    background-color: white;
    margin-left: 2rem;
    margin-top: 0.5rem;
}

.node-header {
    display: flex;
    justify-content: between;
    align-items: center;
}

.node-content {
    display: flex;
    align-items: center;
}

.node-children {
    margin-top: 1rem;
}
</style>

</div> <!-- End container-fluid -->
</style>