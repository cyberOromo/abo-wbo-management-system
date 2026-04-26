<?php
$title = $title ?? 'Donations Management';
$donations = $donations ?? [];
$stats = $stats ?? [];
$user_scope = $user_scope ?? [];
$can_create = $can_create ?? false;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-heart me-2"></i>
            <?= htmlspecialchars($title) ?>
        </h1>
        <p class="text-muted mb-0">Scoped donation visibility and reporting for the current hierarchy level.</p>
    </div>
    <div class="btn-toolbar gap-2">
        <a href="/donations/reports/summary" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>
            Summary Report
        </a>
        <a href="/donations/reports/detailed" class="btn btn-outline-primary">
            <i class="bi bi-table me-1"></i>
            Detailed Report
        </a>
        <a href="/donations/reports/export?format=csv&type=summary" class="btn btn-outline-info">
            <i class="bi bi-download me-1"></i>
            Export CSV
        </a>
    </div>
</div>

<div class="alert alert-warning border-0 rounded-3 mb-4" style="background: linear-gradient(135deg, #fff3cd, #ffe69c);">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-circle fs-4"></i>
        <div>
            <h5 class="mb-1">Current Staging Scope</h5>
            <p class="mb-1">This page shows real donation records and reporting links for your current hierarchy scope.</p>
            <p class="mb-0 small text-muted">Create, edit, campaign, analytics, and donor-management flows are hidden until their backing views and handlers are completed.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-list-check"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= number_format($stats['total_donations'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Donations</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h3 class="mb-0">$<?= number_format((float) ($stats['total_amount'] ?? 0), 2) ?></h3>
                    <p class="text-muted mb-0">Total Amount</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div>
                    <h3 class="mb-0">$<?= number_format((float) ($stats['average_amount'] ?? 0), 2) ?></h3>
                    <p class="text-muted mb-0">Average Amount</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <h3 class="mb-0">$<?= number_format((float) ($stats['month_amount'] ?? 0), 2) ?></h3>
                    <p class="text-muted mb-0">This Month</p>
                    <small class="text-muted"><?= number_format($stats['month_count'] ?? 0) ?> records this month</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-receipt me-2"></i>
                    Recent Donations
                </h5>
                <span class="badge bg-light text-dark"><?= number_format(count($donations)) ?> loaded</span>
            </div>
            <div class="card-body">
                <?php if (!empty($donations)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Donor</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Scope</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <?php
                                    $donorName = trim(($donation['first_name'] ?? '') . ' ' . ($donation['last_name'] ?? ''));
                                    $scopeLabel = $donation['gurmu_name'] ?? $donation['gamta_name'] ?? $donation['godina_name'] ?? 'Current scope';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($donorName !== '' ? $donorName : ($donation['email'] ?? 'Unknown donor')) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($donation['reference_number'] ?? 'No reference') ?></small>
                                        </td>
                                        <td class="fw-semibold text-success">$<?= number_format((float) ($donation['amount'] ?? 0), 2) ?></td>
                                        <td><?= htmlspecialchars(ucfirst($donation['type'] ?? 'General')) ?></td>
                                        <td><?= htmlspecialchars(date('M j, Y', strtotime($donation['donation_date'] ?? $donation['created_at'] ?? 'now'))) ?></td>
                                        <td><?= htmlspecialchars($scopeLabel) ?></td>
                                        <td><span class="text-muted small">Read-only in current build</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No donation records are visible</h5>
                        <p class="text-muted mb-0">There are no donation entries available for your current hierarchy scope.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-bullseye me-2"></i>
                    Scope Summary
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Scope:</strong> <?= htmlspecialchars($user_scope['scope_name'] ?? 'Current hierarchy scope') ?></p>
                <p class="mb-2"><strong>Level:</strong> <?= htmlspecialchars(ucfirst($user_scope['level_scope'] ?? 'all')) ?></p>
                <p class="mb-0 text-muted small">Executives can review donations within their visible hierarchy. Mutation flows remain disabled in staging.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Safe Actions
                </h6>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="/donations" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Refresh Donations
                </a>
                <a href="/donations/reports/summary" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i>
                    Summary Report
                </a>
                <a href="/donations/reports/detailed" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-table me-1"></i>
                    Detailed Report
                </a>
                <a href="/donations/reports/export?format=csv&type=summary" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-download me-1"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Build Notes
                </h6>
            </div>
            <div class="card-body">
                <ul class="small text-muted mb-0 ps-3">
                    <li>Records come from the live donations table with hierarchy-aware filtering.</li>
                    <li>Missing legacy columns are handled defensively so staging schema drift does not break the page.</li>
                    <li>Create, edit, donor management, and campaign flows remain hidden until their views and handlers are implemented.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
