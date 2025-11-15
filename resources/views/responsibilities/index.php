<?php
$title = $title ?? 'Shared Responsibilities & Tasks Management';
$section = $section ?? 'responsibilities';
$stats = $stats ?? [];
$shared_responsibilities = $shared_responsibilities ?? [];
$position_responsibilities = $position_responsibilities ?? [];
$recent_assignments = $recent_assignments ?? [];
$overdue_assignments = $overdue_assignments ?? [];
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>
                        Shared Responsibilities & Tasks Management
                    </h1>
                    <p class="text-muted mb-0">5 Core Areas Applied to ALL Positions and Levels</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#initializeModal">
                        <i class="bi bi-gear"></i> Initialize System
                    </button>
                    <a href="/responsibilities/assign" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Assign Responsibilities
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Shared Responsibilities</h6>
                            <h3 class="mb-0"><?= count($shared_responsibilities) ?></h3>
                            <small>5 Core Areas</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-share fs-2 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Executive Positions</h6>
                            <h3 class="mb-0"><?= count($position_responsibilities) ?></h3>
                            <small>7 Leadership Roles</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-2 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Active Assignments</h6>
                            <h3 class="mb-0"><?= count($recent_assignments) ?></h3>
                            <small>In Progress</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clipboard-check fs-2 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Overdue Tasks</h6>
                            <h3 class="mb-0"><?= count($overdue_assignments) ?></h3>
                            <small>Need Attention</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-exclamation-triangle fs-2 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Shared Responsibilities (5 Core Areas) -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>
                        Shared Responsibilities (5 Core Areas)
                    </h5>
                    <small>Applied to ALL positions at ALL levels</small>
                </div>
                <div class="card-body">
                    <?php if (empty($shared_responsibilities)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle text-muted fs-1 mb-3"></i>
                            <h6 class="text-muted">No shared responsibilities found</h6>
                            <p class="text-muted mb-3">Initialize the system to create the 5 core shared responsibilities</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#initializeModal">
                                Initialize Responsibilities
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($shared_responsibilities as $responsibility): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card border-start border-primary border-3 h-100">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">
                                                <?= htmlspecialchars($responsibility['name_en']) ?>
                                            </h6>
                                            <p class="card-subtitle text-muted mb-2">
                                                <?= htmlspecialchars($responsibility['name_om']) ?>
                                            </p>
                                            <p class="card-text small">
                                                <?= htmlspecialchars($responsibility['description_en']) ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary">Core Area</span>
                                                <a href="/responsibilities/view/<?= $responsibility['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Position-Specific Responsibilities -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-badge me-2"></i>
                        Executive Positions & Individual Responsibilities
                    </h5>
                    <small>7 positions with specific responsibilities</small>
                </div>
                <div class="card-body">
                    <?php if (empty($position_responsibilities)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-people text-muted fs-1 mb-3"></i>
                            <h6 class="text-muted">No position responsibilities found</h6>
                            <p class="text-muted">Individual position responsibilities will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="positionAccordion">
                            <?php foreach ($position_responsibilities as $key => $positionData): ?>
                                <?php $position = $positionData['position']; ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?= $key ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $key ?>" aria-expanded="false">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <div>
                                                    <strong><?= htmlspecialchars($position['name_en']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($position['name_om']) ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-secondary me-2">
                                                        <?= count($positionData['individual']) ?> Individual
                                                    </span>
                                                    <span class="badge bg-primary">
                                                        <?= count($positionData['shared']) ?> Shared
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $key ?>" class="accordion-collapse collapse" data-bs-parent="#positionAccordion">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="text-secondary mb-3">Individual Responsibilities</h6>
                                                    <?php if (!empty($positionData['individual'])): ?>
                                                        <ul class="list-unstyled">
                                                            <?php foreach ($positionData['individual'] as $resp): ?>
                                                                <li class="mb-2">
                                                                    <i class="bi bi-check-circle text-success me-2"></i>
                                                                    <strong><?= htmlspecialchars($resp['name_en']) ?></strong>
                                                                    <br>
                                                                    <small class="text-muted ms-4"><?= htmlspecialchars($resp['name_om']) ?></small>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <p class="text-muted">No individual responsibilities defined</p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-primary mb-3">Shared Responsibilities (5 Core Areas)</h6>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($positionData['shared'] as $resp): ?>
                                                            <li class="mb-2">
                                                                <i class="bi bi-share text-primary me-2"></i>
                                                                <strong><?= htmlspecialchars($resp['name_en']) ?></strong>
                                                                <br>
                                                                <small class="text-muted ms-4"><?= htmlspecialchars($resp['name_om']) ?></small>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-3 border-top">
                                                <a href="/responsibilities/assign?position=<?= $position['id'] ?>" class="btn btn-sm btn-primary me-2">
                                                    <i class="bi bi-plus-circle"></i> Assign to Users
                                                </a>
                                                <a href="/responsibilities/assignments?position_id=<?= $position['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-list-check"></i> View Assignments
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Assignments -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Recent Assignments
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_assignments)): ?>
                        <p class="text-muted text-center py-3">No recent assignments</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($recent_assignments, 0, 5) as $assignment): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($assignment['responsibility_name_en']) ?></h6>
                                            <p class="mb-1 small text-muted">
                                                Assigned to: <?= htmlspecialchars($assignment['user_name']) ?>
                                            </p>
                                            <small class="text-muted">
                                                Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?= $assignment['status'] === 'completed' ? 'success' : ($assignment['status'] === 'overdue' ? 'danger' : 'primary') ?>">
                                            <?= ucfirst($assignment['status']) ?>
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= $assignment['completion_percentage'] ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/responsibilities/assignments" class="btn btn-sm btn-outline-primary">View All Assignments</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Overdue Assignments -->
            <?php if (!empty($overdue_assignments)): ?>
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Overdue Assignments
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($overdue_assignments, 0, 3) as $assignment): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-danger"><?= htmlspecialchars($assignment['responsibility_name_en']) ?></h6>
                                            <p class="mb-1 small text-muted">
                                                <?= htmlspecialchars($assignment['user_name']) ?>
                                            </p>
                                            <small class="text-danger">
                                                Overdue by <?= (new DateTime())->diff(new DateTime($assignment['due_date']))->days ?> days
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/responsibilities/assignments?overdue=1" class="btn btn-sm btn-warning">View All Overdue</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/responsibilities/assign" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Assign Responsibilities
                        </a>
                        <a href="/responsibilities/assignments" class="btn btn-outline-primary">
                            <i class="bi bi-list-check me-2"></i>
                            View All Assignments
                        </a>
                        <a href="/positions" class="btn btn-outline-secondary">
                            <i class="bi bi-people me-2"></i>
                            Manage Positions
                        </a>
                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize System Modal -->
<div class="modal fade" id="initializeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Initialize Responsibilities System</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will create the default shared responsibilities and individual position responsibilities:</p>
                <div class="alert alert-info">
                    <h6>Shared Responsibilities (5 Core Areas):</h6>
                    <ul class="mb-0">
                        <li><strong>Qaboo Ya'ii</strong> - Meetings Management</li>
                        <li><strong>Karoora</strong> - Planning & Strategic Development</li>
                        <li><strong>Gabaasa</strong> - Reporting & Documentation</li>
                        <li><strong>Projectoota</strong> - Projects & Initiatives</li>
                        <li><strong>Gamaggama</strong> - Evaluation & Assessment</li>
                    </ul>
                </div>
                <p class="mb-0">These will be applied to ALL 7 executive positions at ALL organizational levels.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="/responsibilities/initialize" class="btn btn-primary">Initialize System</a>
            </div>
        </div>
    </div>
</div>

<!-- Report Generation Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Responsibility Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="mb-3">
                        <label class="form-label">Report Type</label>
                        <select class="form-select" name="report_type">
                            <option value="summary">Responsibility Summary</option>
                            <option value="assignments">Assignment Status</option>
                            <option value="performance">Performance Report</option>
                            <option value="overdue">Overdue Tasks</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" class="form-control" name="start_date" placeholder="Start Date">
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control" name="end_date" placeholder="End Date">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Filter by Position</label>
                        <select class="form-select" name="position_id">
                            <option value="">All Positions</option>
                            <?php foreach ($position_responsibilities as $key => $positionData): ?>
                                <option value="<?= $positionData['position']['id'] ?>">
                                    <?= htmlspecialchars($positionData['position']['name_en']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="generateReport()">Generate Report</button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh dashboard data every 30 seconds
setInterval(function() {
    fetch('/responsibilities/dashboard')
        .then(response => response.json())
        .then(data => {
            // Update stats if needed
            console.log('Dashboard data updated:', data);
        })
        .catch(error => console.error('Dashboard update error:', error));
}, 30000);

function generateReport() {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.open('/responsibilities/report?' + params.toString(), '_blank');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
    modal.hide();
}
</script>