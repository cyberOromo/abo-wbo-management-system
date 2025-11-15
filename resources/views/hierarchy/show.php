<?php
$pageTitle = ucfirst($type ?? 'Unit') . ' Details';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Hierarchy', 'url' => '/hierarchy'],
    ['title' => ucfirst($type ?? 'Unit') . ' Details', 'url' => '', 'active' => true]
];

$isGodina = ($type ?? '') === 'godina';
$isGamta = ($type ?? '') === 'gamta';
$unit = $unit ?? null;
?>

<?php if (!$unit): ?>
<!-- Unit Not Found -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger">
            <h4 class="alert-heading">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Unit Not Found
            </h4>
            <p>The requested <?= $type ?? 'unit' ?> could not be found or you don't have permission to view it.</p>
            <hr>
            <div class="mb-0">
                <a href="/hierarchy" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-left me-1"></i>
                    Return to Hierarchy
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">
            <i class="bi bi-<?= $isGodina ? 'globe' : 'house' ?> me-2"></i>
            <?= htmlspecialchars($unit['name']) ?>
            <span class="badge bg-<?= ($unit['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> ms-2">
                <?= ucfirst($unit['status'] ?? 'active') ?>
            </span>
        </h1>
        <p class="text-muted mb-0">
            <?= ucfirst($type) ?> Code: <strong><?= htmlspecialchars($unit['code']) ?></strong>
            <?php if ($unit['location']): ?>
                • <?= htmlspecialchars($unit['location']) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/hierarchy/<?= $unit['id'] ?>/edit?type=<?= $type ?>" class="btn btn-outline-warning">
                <i class="bi bi-pencil me-1"></i>
                Edit
            </a>
            <div class="btn-group">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots me-1"></i>
                    More
                </button>
                <ul class="dropdown-menu">
                    <?php if ($isGodina): ?>
                    <li><a class="dropdown-item" href="/hierarchy/create?type=gamta&godina_id=<?= $unit['id'] ?>">
                        <i class="bi bi-plus-circle me-2"></i>Add New Gamta
                    </a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="/users?<?= $isGodina ? 'godina_id' : 'gamta_id' ?>=<?= $unit['id'] ?>">
                        <i class="bi bi-people me-2"></i>Manage Users
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData()">
                        <i class="bi bi-download me-2"></i>Export Data
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete()">
                        <i class="bi bi-trash me-2"></i>Delete <?= ucfirst($type) ?>
                    </a></li>
                </ul>
            </div>
        </div>
        <a href="/hierarchy" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Hierarchy
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Left Column - Main Information -->
    <div class="col-lg-8">
        <!-- Basic Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Name:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($unit['name']) ?></dd>
                            
                            <dt class="col-sm-4">Code:</dt>
                            <dd class="col-sm-8"><code><?= htmlspecialchars($unit['code']) ?></code></dd>
                            
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-<?= ($unit['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($unit['status'] ?? 'active') ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4">Type:</dt>
                            <dd class="col-sm-8"><?= ucfirst($type) ?></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Created:</dt>
                            <dd class="col-sm-8"><?= date('M j, Y g:i A', strtotime($unit['created_at'] ?? 'now')) ?></dd>
                            
                            <dt class="col-sm-4">Updated:</dt>
                            <dd class="col-sm-8"><?= date('M j, Y g:i A', strtotime($unit['updated_at'] ?? 'now')) ?></dd>
                            
                            <dt class="col-sm-4">ID:</dt>
                            <dd class="col-sm-8"><code><?= $unit['id'] ?></code></dd>
                            
                            <?php if ($isGamta && !empty($unit['godina_name'])): ?>
                            <dt class="col-sm-4">Parent:</dt>
                            <dd class="col-sm-8">
                                <a href="/hierarchy/<?= $unit['godina_id'] ?>?type=godina" class="text-decoration-none">
                                    <i class="bi bi-globe me-1"></i>
                                    <?= htmlspecialchars($unit['godina_name']) ?>
                                </a>
                            </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
                
                <?php if (!empty($unit['description'])): ?>
                <hr>
                <div>
                    <h6>Description</h6>
                    <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($unit['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Location Information Card -->
        <?php if (!empty($unit['location']) || !empty($unit['city']) || !empty($unit['country'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    Location Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <?php if (!empty($unit['location'])): ?>
                            <dt class="col-sm-4">Address:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($unit['location']) ?></dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($unit['city'])): ?>
                            <dt class="col-sm-4">City:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($unit['city']) ?></dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($unit['state'])): ?>
                            <dt class="col-sm-4">State:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($unit['state']) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <?php if (!empty($unit['country'])): ?>
                            <dt class="col-sm-4">Country:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($unit['country']) ?></dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($unit['postal_code'])): ?>
                            <dt class="col-sm-4">Postal Code:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($unit['postal_code']) ?></dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($unit['timezone'])): ?>
                            <dt class="col-sm-4">Timezone:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($unit['timezone']) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Contact Information Card -->
        <?php if (!empty($unit['contact_phone']) || !empty($unit['contact_email'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-telephone me-2"></i>
                    Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($unit['contact_phone'])): ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-phone text-primary me-2"></i>
                            <div>
                                <small class="text-muted d-block">Phone Number</small>
                                <a href="tel:<?= htmlspecialchars($unit['contact_phone']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($unit['contact_phone']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($unit['contact_email'])): ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <div>
                                <small class="text-muted d-block">Email Address</small>
                                <a href="mailto:<?= htmlspecialchars($unit['contact_email']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($unit['contact_email']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Sub-units Card (for Godina) -->
        <?php if ($isGodina): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-house me-2"></i>
                    Gamtas (<?= count($unit['gamtas'] ?? []) ?>)
                </h5>
                <a href="/hierarchy/create?type=gamta&godina_id=<?= $unit['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus me-1"></i>
                    Add Gamta
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($unit['gamtas'])): ?>
                <div class="row">
                    <?php foreach ($unit['gamtas'] as $gamta): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card border-start border-4 border-<?= ($gamta['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-1">
                                            <a href="/hierarchy/<?= $gamta['id'] ?>?type=gamta" class="text-decoration-none">
                                                <?= htmlspecialchars($gamta['name']) ?>
                                            </a>
                                        </h6>
                                        <p class="card-text text-muted small mb-2">
                                            Code: <?= htmlspecialchars($gamta['code']) ?>
                                            <?php if (!empty($gamta['location'])): ?>
                                                <br><?= htmlspecialchars($gamta['location']) ?>
                                            <?php endif; ?>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-<?= ($gamta['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($gamta['status'] ?? 'active') ?>
                                            </span>
                                            <span class="badge bg-info">
                                                <?= $gamta['user_count'] ?? 0 ?> users
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="/hierarchy/<?= $gamta['id'] ?>?type=gamta">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a></li>
                                            <li><a class="dropdown-item" href="/hierarchy/<?= $gamta['id'] ?>/edit?type=gamta">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-house text-muted" style="font-size: 3rem;"></i>
                    <h6 class="text-muted mt-3">No Gamtas Yet</h6>
                    <p class="text-muted">This Godina doesn't have any Gamtas assigned yet.</p>
                    <a href="/hierarchy/create?type=gamta&godina_id=<?= $unit['id'] ?>" class="btn btn-outline-primary">
                        <i class="bi bi-plus me-1"></i>
                        Create First Gamta
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column - Statistics and Actions -->
    <div class="col-lg-4">
        <!-- Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Statistics
                </h6>
            </div>
            <div class="card-body">
                <?php if ($isGodina): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Gamtas</span>
                    <span class="badge bg-primary fs-6"><?= count($unit['gamtas'] ?? []) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Active Gamtas</span>
                    <span class="badge bg-success fs-6"><?= count(array_filter($unit['gamtas'] ?? [], fn($g) => ($g['status'] ?? 'active') === 'active')) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Users</span>
                    <span class="badge bg-info fs-6"><?= array_sum(array_column($unit['gamtas'] ?? [], 'user_count')) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Avg Users/Gamta</span>
                    <span class="badge bg-secondary fs-6">
                        <?= count($unit['gamtas'] ?? []) > 0 ? round(array_sum(array_column($unit['gamtas'] ?? [], 'user_count')) / count($unit['gamtas']), 1) : 0 ?>
                    </span>
                </div>
                <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Assigned Users</span>
                    <span class="badge bg-primary fs-6"><?= $unit['user_count'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Active Users</span>
                    <span class="badge bg-success fs-6"><?= $unit['active_users'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Recent Activities</span>
                    <span class="badge bg-info fs-6"><?= $unit['recent_activities'] ?? 0 ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Last Activity</span>
                    <span class="badge bg-secondary fs-6">
                        <?= !empty($unit['last_activity']) ? date('M j', strtotime($unit['last_activity'])) : 'None' ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($isGodina): ?>
                    <a href="/hierarchy/create?type=gamta&godina_id=<?= $unit['id'] ?>" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add New Gamta
                    </a>
                    <a href="/users?godina_id=<?= $unit['id'] ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-people me-2"></i>
                        View All Users
                    </a>
                    <?php else: ?>
                    <a href="/users?gamta_id=<?= $unit['id'] ?>" class="btn btn-outline-primary">
                        <i class="bi bi-people me-2"></i>
                        Manage Users
                    </a>
                    <a href="/hierarchy/<?= $unit['godina_id'] ?? '' ?>?type=godina" class="btn btn-outline-secondary">
                        <i class="bi bi-globe me-2"></i>
                        View Parent Godina
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-outline-info" onclick="generateReport()">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Generate Report
                    </button>
                    <button class="btn btn-outline-warning" onclick="scheduleEvent()">
                        <i class="bi bi-calendar-event me-2"></i>
                        Schedule Event
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <div id="recentActivity">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-muted" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Loading recent activity...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
const unitId = <?= json_encode($unit['id'] ?? null) ?>;
const unitType = '<?= $type ?>';

document.addEventListener('DOMContentLoaded', function() {
    <?php if ($unit): ?>
    loadRecentActivity();
    <?php endif; ?>
});

function loadRecentActivity() {
    fetch(`/hierarchy/${unitId}/activity?type=${unitType}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentActivity');
            
            if (data.success && data.data.length > 0) {
                let html = '<div class="timeline">';
                
                data.data.forEach(activity => {
                    const timeAgo = formatTimeAgo(activity.created_at);
                    const icon = getActivityIcon(activity.type);
                    
                    html += `
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker me-3">
                                    <i class="bi ${icon} text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">${activity.description}</div>
                                    <small class="text-muted">${timeAgo}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-3">
                        <i class="bi bi-clock-history text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No recent activity</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading activity:', error);
            document.getElementById('recentActivity').innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-exclamation-circle text-warning"></i>
                    <p class="text-muted mt-2 mb-0">Failed to load activity</p>
                </div>
            `;
        });
}

function getActivityIcon(type) {
    const icons = {
        'created': 'bi-plus-circle',
        'updated': 'bi-pencil',
        'user_assigned': 'bi-person-plus',
        'user_removed': 'bi-person-dash',
        'status_changed': 'bi-arrow-repeat',
        'default': 'bi-circle'
    };
    
    return icons[type] || icons.default;
}

function formatTimeAgo(timestamp) {
    const now = new Date();
    const past = new Date(timestamp);
    const diffInSeconds = Math.floor((now - past) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    
    return past.toLocaleDateString();
}

function confirmDelete() {
    if (confirm(`Are you sure you want to delete this ${unitType}? This action cannot be undone.`)) {
        deleteUnit();
    }
}

function deleteUnit() {
    fetch(`/hierarchy/${unitId}?type=${unitType}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${unitType.charAt(0).toUpperCase() + unitType.slice(1)} deleted successfully!`);
            window.location.href = '/hierarchy?deleted=' + unitId;
        } else {
            alert('Failed to delete: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error deleting unit:', error);
        alert('An error occurred while deleting.');
    });
}

function exportData() {
    window.open(`/hierarchy/${unitId}/export?type=${unitType}`, '_blank');
}

function generateReport() {
    window.open(`/hierarchy/${unitId}/report?type=${unitType}`, '_blank');
}

function scheduleEvent() {
    // Redirect to event scheduling page
    window.location.href = `/events/create?${unitType}_id=${unitId}`;
}
</script>

<style>
.timeline-marker {
    width: 24px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 2px;
}

.timeline-item:not(:last-child) .timeline-marker::after {
    content: '';
    position: absolute;
    width: 2px;
    height: 100%;
    background-color: #dee2e6;
    left: 50%;
    top: 24px;
    transform: translateX(-50%);
}

.border-start {
    border-left-width: 4px !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.card-title {
    color: #495057;
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}

dl.row dt {
    font-weight: 600;
    color: #495057;
}

dl.row dd {
    color: #6c757d;
}

.timeline {
    position: relative;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>