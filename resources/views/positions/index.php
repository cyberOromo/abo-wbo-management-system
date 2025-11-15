<?php
$pageTitle = $title ?? 'Position Management';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Positions', 'url' => '/positions', 'active' => true]
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-person-badge me-2"></i>
        Position Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/positions/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Create Position
            </a>
        </div>
        <div class="btn-group">
            <a href="/positions/assignments" class="btn btn-outline-secondary">
                <i class="bi bi-person-check me-1"></i>
                Manage Assignments
            </a>
        </div>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <?php if (!empty($stats)): ?>
        <?php foreach ($stats as $stat): ?>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-stats border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-diagram-3 text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= number_format($stat['total_positions'] ?? 0) ?></h4>
                            <p class="text-muted mb-0"><?= ucfirst($stat['level_scope']) ?> Positions</p>
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i>
                                <?= $stat['active_positions'] ?? 0 ?> Active
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pending Approvals Alert -->
<?php if (!empty($pendingApprovals)): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong><?= count($pendingApprovals) ?> position assignment(s)</strong> are pending approval.
    <a href="/positions/assignments?approval_status=pending" class="alert-link">Review pending approvals</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Expiring Positions Alert -->
<?php if (!empty($expiringSoon)): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-clock me-2"></i>
    <strong><?= count($expiringSoon) ?> position(s)</strong> are expiring within 30 days.
    <a href="/positions/assignments?expiring=30" class="alert-link">View expiring positions</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Positions by Level -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Organizational Positions
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($positions)): ?>
                    <div class="accordion" id="positionsAccordion">
                        <?php
                        $levels = ['global' => 'Global Level', 'godina' => 'Godina Level', 'gamta' => 'Gamta Level', 'gurmu' => 'Gurmu Level'];
                        $groupedPositions = [];
                        foreach ($positions as $position) {
                            $groupedPositions[$position['level_scope']][] = $position;
                        }
                        
                        $index = 0;
                        foreach ($levels as $levelKey => $levelName):
                            if (empty($groupedPositions[$levelKey])) continue;
                            $index++;
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $index > 1 ? 'collapsed' : '' ?>" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#level<?= $index ?>" 
                                        aria-expanded="<?= $index === 1 ? 'true' : 'false' ?>">
                                    <i class="bi bi-diagram-<?= $index + 1 ?> me-2"></i>
                                    <?= $levelName ?>
                                    <span class="badge bg-primary ms-2"><?= count($groupedPositions[$levelKey]) ?></span>
                                </button>
                            </h2>
                            <div id="level<?= $index ?>" class="accordion-collapse collapse <?= $index === 1 ? 'show' : '' ?>" 
                                 data-bs-parent="#positionsAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Position</th>
                                                    <th>Key Name</th>
                                                    <th>Term</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($groupedPositions[$levelKey] as $position): ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong><?= htmlspecialchars($position['name_en']) ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?= htmlspecialchars($position['name_om']) ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <code><?= htmlspecialchars($position['key_name']) ?></code>
                                                    </td>
                                                    <td>
                                                        <?= $position['term_length'] ?> months
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $position['election_cycle'] === 'elected' ? 'primary' : 'secondary' ?>">
                                                            <?= ucfirst($position['election_cycle']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $position['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                            <?= ucfirst($position['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="/positions/<?= $position['id'] ?>" class="btn btn-outline-primary" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="/positions/<?= $position['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-outline-success" onclick="showAssignModal(<?= $position['id'] ?>)" title="Assign">
                                                                <i class="bi bi-person-plus"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-person-badge text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">No Positions Found</h4>
                        <p class="text-muted">Start by creating organizational positions for your hierarchy.</p>
                        <a href="/positions/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Create First Position
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="assignModalContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showAssignModal(positionId) {
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    
    // Load assignment form via AJAX
    fetch(`/positions/${positionId}/assign`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('assignModalContent').innerHTML = html;
            modal.show();
        })
        .catch(error => {
            console.error('Error loading assignment form:', error);
            document.getElementById('assignModalContent').innerHTML = 
                '<div class="alert alert-danger">Error loading assignment form. Please try again.</div>';
        });
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>