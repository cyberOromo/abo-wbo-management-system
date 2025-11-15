<?php
$title = $title ?? 'Reports & Analytics';
$available_reports = $available_reports ?? [];
$quick_stats = $quick_stats ?? [];
$recent_reports = $recent_reports ?? [];
$user_scope = $user_scope ?? [];
$user_role = $user_role ?? 'member';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-bar text-info"></i>
                <?php echo htmlspecialchars($title); ?>
            </h1>
            <?php if (!empty($user_scope)): ?>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($user_scope['scope_name'] ?? 'System Wide'); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-download"></i> Export Reports
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/reports/export/summary?format=pdf">
                        <i class="fas fa-file-pdf"></i> Summary Report (PDF)
                    </a>
                    <a class="dropdown-item" href="/reports/export/detailed?format=excel">
                        <i class="fas fa-file-excel"></i> Detailed Report (Excel)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Statistics -->
    <?php if (!empty($quick_stats)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Members
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($quick_stats['total_members'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Tasks
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($quick_stats['active_tasks'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Upcoming Meetings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($quick_stats['upcoming_meetings'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Monthly Donations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($quick_stats['monthly_donations'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Reports -->
    <div class="row">
        <?php if (!empty($available_reports)): ?>
        <?php foreach ($available_reports as $key => $report): ?>
        <?php if ($report['available']): ?>
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="<?php echo $report['icon']; ?> fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($report['title']); ?></h5>
                            <p class="card-text text-muted small mb-0">
                                <?php echo htmlspecialchars($report['description']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/reports/<?php echo $key; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-chart-bar"></i> View Report
                        </a>
                        <div class="btn-group btn-group-sm ml-2">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="/reports/export/<?php echo $key; ?>?format=pdf">PDF</a>
                                <a class="dropdown-item" href="/reports/export/<?php echo $key; ?>?format=excel">Excel</a>
                                <a class="dropdown-item" href="/reports/export/<?php echo $key; ?>?format=csv">CSV</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No Reports Available</h5>
                    <p class="text-muted">Reports will be available based on your role and permissions.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Report Access -->
    <?php if (!empty($recent_reports)): ?>
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recently Accessed Reports</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Report Type</th>
                            <th>Accessed On</th>
                            <th>Filters Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['type'] ?? 'Unknown'); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($report['accessed_at'] ?? 'now')); ?></td>
                            <td>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($report['filters'] ?? 'Default'); ?>
                                </small>
                            </td>
                            <td>
                                <a href="/reports/<?php echo $report['type']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-redo"></i> Run Again
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Add any JavaScript for report interactions
    $('.dropdown-toggle').dropdown();
});
</script>