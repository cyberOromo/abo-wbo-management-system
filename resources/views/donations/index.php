<?php
$title = $title ?? 'My Donations';
$donations = $donations ?? [];
$stats = $stats ?? [];
$user_scope = $user_scope ?? [];
$can_create = $can_create ?? false;
$can_manage = $can_manage ?? false;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-hand-holding-usd text-warning"></i>
                <?php echo htmlspecialchars($title); ?>
            </h1>
            <?php if (!empty($user_scope)): ?>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($user_scope['scope_name'] ?? 'My Scope'); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <?php if ($can_create): ?>
        <div>
            <a href="/donations/create" class="btn btn-success">
                <i class="fas fa-plus"></i> Make Donation
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Statistics Cards -->
    <?php if (!empty($stats)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Donations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total_donations'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
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
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($stats['total_amount'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($stats['month_amount'] ?? 0, 2); ?>
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
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Average Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($stats['average_amount'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Donations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Donations</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($donations)): ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="donationsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($donation['reference_number'] ?? 'N/A'); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    $<?php echo number_format($donation['amount'] ?? 0, 2); ?>
                                </span>
                            </td>
                            <td><?php echo ucfirst($donation['type'] ?? 'N/A'); ?></td>
                            <td><?php echo ucfirst($donation['category'] ?? 'General'); ?></td>
                            <td><?php echo date('M j, Y', strtotime($donation['donation_date'] ?? 'now')); ?></td>
                            <td>
                                <?php
                                $status = $donation['status'] ?? 'pending';
                                $statusClass = [
                                    'pending' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ][$status] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/donations/<?php echo $donation['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($can_manage || ($donation['donor_id'] ?? 0) == (auth_user()['id'] ?? 0)): ?>
                                <a href="/donations/<?php echo $donation['id']; ?>/edit" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-hand-holding-usd fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-600">No donations found</h5>
                <p class="text-muted">You haven't made any donations yet.</p>
                <?php if ($can_create): ?>
                <a href="/donations/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Make Your First Donation
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($donations)): ?>
<script>
$(document).ready(function() {
    $('#donationsTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 6 }
        ]
    });
});
</script>
<?php endif; ?>