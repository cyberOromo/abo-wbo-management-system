<?php
$summary = $summary ?? [];
$filters = $filters ?? [];
$userScope = $user_scope ?? [];
$recent = $summary['recent_donations'] ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($title ?? 'Donation Summary Report') ?></h1>
            <p class="text-muted mb-0">Read-only summary reporting for the current hierarchy scope.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/donations" class="btn btn-outline-secondary">Back to Donations</a>
            <a href="/donations/reports/export?format=csv&type=summary" class="btn btn-outline-primary">Export CSV</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Donations</div><div class="h3 mb-0"><?= number_format($summary['total_donations'] ?? 0) ?></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Amount</div><div class="h3 mb-0">$<?= number_format((float) ($summary['total_amount'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Average Amount</div><div class="h3 mb-0">$<?= number_format((float) ($summary['average_amount'] ?? 0), 2) ?></div></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Filters</strong></div>
        <div class="card-body">
            <div><strong>Scope:</strong> <?= htmlspecialchars($userScope['scope_name'] ?? 'Current scope') ?></div>
            <div><strong>Date Range:</strong> <?= htmlspecialchars($filters['dateRange'] ?? ($summary['date_range'] ?? '30_days')) ?></div>
            <div><strong>Type:</strong> <?= htmlspecialchars($filters['type'] ?? ($summary['type'] ?? 'all')) ?></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><strong>By Type</strong></div>
                <div class="card-body">
                    <?php if (!empty($summary['by_type'])): ?>
                        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Type</th><th>Count</th><th>Amount</th></tr></thead><tbody><?php foreach ($summary['by_type'] as $row): ?><tr><td><?= htmlspecialchars($row['label']) ?></td><td><?= number_format($row['count']) ?></td><td>$<?= number_format((float) $row['amount'], 2) ?></td></tr><?php endforeach; ?></tbody></table></div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No grouped donation data is available for the selected filters.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><strong>By Category</strong></div>
                <div class="card-body">
                    <?php if (!empty($summary['by_category'])): ?>
                        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Category</th><th>Count</th><th>Amount</th></tr></thead><tbody><?php foreach ($summary['by_category'] as $row): ?><tr><td><?= htmlspecialchars($row['label']) ?></td><td><?= number_format($row['count']) ?></td><td>$<?= number_format((float) $row['amount'], 2) ?></td></tr><?php endforeach; ?></tbody></table></div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No category breakdown is available for the selected filters.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><strong>Recent Donations</strong></div>
        <div class="card-body">
            <?php if (!empty($recent)): ?>
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Reference</th><th>Donor</th><th>Amount</th><th>Type</th><th>Date</th></tr></thead><tbody><?php foreach ($recent as $row): ?><tr><td><?= htmlspecialchars($row['reference']) ?></td><td><?= htmlspecialchars($row['donor']) ?></td><td>$<?= number_format((float) $row['amount'], 2) ?></td><td><?= htmlspecialchars($row['type']) ?></td><td><?= htmlspecialchars($row['date']) ?></td></tr><?php endforeach; ?></tbody></table></div>
            <?php else: ?>
                <p class="text-muted mb-0">No donation records matched the selected summary filters.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
