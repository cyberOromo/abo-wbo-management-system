<?php
$data = $data ?? [];
$filters = $filters ?? [];
$rows = $data['donations'] ?? [];
$summary = $data['summary'] ?? [];
$breakdowns = $data['breakdowns'] ?? [];
$userScope = $user_scope ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($title ?? 'Detailed Donation Report') ?></h1>
            <p class="text-muted mb-0">Detailed read-only donation reporting for the current hierarchy scope.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/donations" class="btn btn-outline-secondary">Back to Donations</a>
            <a href="/donations/reports/export?format=csv&type=detailed" class="btn btn-outline-primary">Export CSV</a>
        </div>
    </div>

    <div class="card mb-4"><div class="card-header"><strong>Filters</strong></div><div class="card-body"><div><strong>Scope:</strong> <?= htmlspecialchars($userScope['scope_name'] ?? 'Current scope') ?></div><div><strong>Start:</strong> <?= htmlspecialchars($filters['start_date'] ?? 'n/a') ?></div><div><strong>End:</strong> <?= htmlspecialchars($filters['end_date'] ?? 'n/a') ?></div><div><strong>Type:</strong> <?= htmlspecialchars($filters['type'] ?? 'all') ?></div><div><strong>Category:</strong> <?= htmlspecialchars($filters['category'] ?? 'all') ?></div><div><strong>Status:</strong> <?= htmlspecialchars($filters['status'] ?? 'all') ?></div></div></div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Donations</div><div class="h3 mb-0"><?= number_format($summary['total_donations'] ?? 0) ?></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Amount</div><div class="h3 mb-0">$<?= number_format((float) ($summary['total_amount'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Average Amount</div><div class="h3 mb-0">$<?= number_format((float) ($summary['average_amount'] ?? 0), 2) ?></div></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <?php foreach ($breakdowns as $label => $items): ?>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><strong><?= htmlspecialchars(ucfirst($label)) ?> Breakdown</strong></div>
                    <div class="card-body">
                        <?php if (!empty($items)): ?>
                            <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Label</th><th>Count</th><th>Amount</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= htmlspecialchars($item['label']) ?></td><td><?= number_format($item['count']) ?></td><td>$<?= number_format((float) $item['amount'], 2) ?></td></tr><?php endforeach; ?></tbody></table></div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No <?= htmlspecialchars($label) ?> breakdown is available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"><strong>Donation Rows</strong><span class="badge bg-light text-dark"><?= number_format(count($rows)) ?> loaded</span></div>
        <div class="card-body">
            <?php if (!empty($rows)): ?>
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Reference</th><th>Donor</th><th>Amount</th><th>Type</th><th>Category</th><th>Status</th><th>Date</th><th>Scope</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= htmlspecialchars($row['reference']) ?></td><td><?= htmlspecialchars($row['donor']) ?></td><td>$<?= number_format((float) $row['amount'], 2) ?></td><td><?= htmlspecialchars($row['type']) ?></td><td><?= htmlspecialchars($row['category']) ?></td><td><?= htmlspecialchars($row['status']) ?></td><td><?= htmlspecialchars($row['date']) ?></td><td><?= htmlspecialchars($row['scope']) ?></td></tr><?php endforeach; ?></tbody></table></div>
            <?php else: ?>
                <p class="text-muted mb-0">No donation rows matched the selected report filters.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
