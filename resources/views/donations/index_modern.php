<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$title = $title ?? 'Donations Management';
$donations = $donations ?? [];
$stats = $stats ?? [];
$user_scope = $user_scope ?? [];
$can_create = $can_create ?? false;
$viewMode = (($_GET['view'] ?? 'table') === 'cards') ? 'cards' : 'table';

$resolveDonorName = static function (array $donation): string {
    $donorName = trim(($donation['first_name'] ?? '') . ' ' . ($donation['last_name'] ?? ''));

    if ($donorName === '') {
        $donorName = $donation['donor_name']
            ?? $donation['group_name']
            ?? $donation['organization_name']
            ?? (isset($donation['member_id']) ? 'Member #' . $donation['member_id'] : 'Unknown donor');
    }

    return $donorName !== '' ? $donorName : ($donation['email'] ?? 'Unknown donor');
};

$resolveScopeLabel = static function (array $donation): string {
    $scopeLabel = $donation['gurmu_name'] ?? $donation['gamta_name'] ?? $donation['godina_name'] ?? 'Current scope';

    if ($scopeLabel === 'Current scope' && !empty($donation['level_scope'])) {
        $scopeLabel = ucfirst((string) $donation['level_scope']);
    }

    return $scopeLabel;
};

$resolveReference = static function (array $donation): string {
    return (string) (
        $donation['reference_number']
        ?? $donation['donation_number']
        ?? $donation['receipt_number']
        ?? $donation['uuid']
        ?? 'No reference'
    );
};

$resolveType = static function (array $donation): string {
    return (string) (
        $donation['type']
        ?? $donation['donation_type']
        ?? $donation['donor_type']
        ?? 'General'
    );
};

$resolveDate = static function (array $donation): string {
    $rawValue = $donation['donation_date'] ?? $donation['payment_date'] ?? $donation['created_at'] ?? null;
    $timestamp = $rawValue ? strtotime((string) $rawValue) : false;

    return $timestamp ? date('M j, Y', $timestamp) : 'Unknown date';
};
?>

<div class="module-surface theme-donations">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-heart"></i> Finance Workspace</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-heart-fill me-2"></i><?= htmlspecialchars($title) ?></h1>
                    <p class="module-subtitle">A scope-aware donation review surface for contribution visibility, donor context, reporting access, and safer staging validation across the active hierarchy chain.</p>
                </div>
                <div class="module-actions">
                    <a href="/donations" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
                    <a href="/donations/reports/summary" class="btn btn-outline-success"><i class="bi bi-file-earmark-bar-graph me-1"></i>Summary Report</a>
                    <a href="/donations/reports/detailed" class="btn btn-outline-primary"><i class="bi bi-table me-1"></i>Detailed Report</a>
                    <a href="/donations/reports/export?format=csv&type=summary" class="btn btn-primary"><i class="bi bi-download me-1"></i>Export CSV</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($user_scope['scope_name'] ?? 'Current hierarchy scope') ?></span>
                <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) ($user_scope['level_scope'] ?? 'all'))) ?> level</span>
                <span class="module-chip"><i class="bi bi-shield-check"></i>Read-only donations surface</span>
            </div>
        </div>
    </section>

    <div class="module-callout warning">
        <strong>Current staging scope:</strong> donation visibility and reporting are active here, while create, edit, campaign, donor-management, and analytics flows remain hidden until their handlers are fully completed on the active path.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['total_donations'] ?? 0)) ?></div><div class="stat-label">Total Donations</div></div><span class="stat-icon"><i class="bi bi-receipt-cutoff"></i></span></div><div class="stat-footnote">Records visible within the current hierarchy span.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value">$<?= number_format((float) ($stats['total_amount'] ?? 0), 2) ?></div><div class="stat-label">Total Amount</div></div><span class="stat-icon"><i class="bi bi-cash-stack"></i></span></div><div class="stat-footnote">Combined contribution value in the current view.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value">$<?= number_format((float) ($stats['average_amount'] ?? 0), 2) ?></div><div class="stat-label">Average Gift</div></div><span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span></div><div class="stat-footnote">Average across the currently visible donation set.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value">$<?= number_format((float) ($stats['month_amount'] ?? 0), 2) ?></div><div class="stat-label">This Month</div></div><span class="stat-icon"><i class="bi bi-calendar3"></i></span></div><div class="stat-footnote"><?= number_format((int) ($stats['month_count'] ?? 0)) ?> records posted in the current month.</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-wallet2 me-2"></i>Scoped Donation Register</h2>
                    <div class="module-toolbar">
                        <span class="module-soft-badge"><i class="bi bi-eye"></i><?= number_format(count($donations)) ?> loaded</span>
                        <div class="module-view-toggle" role="group" aria-label="Donation list view">
                            <a href="/donations?view=table" class="btn btn-sm <?= $viewMode === 'table' ? 'active' : '' ?>"><i class="bi bi-table me-1"></i>Table</a>
                            <a href="/donations?view=cards" class="btn btn-sm <?= $viewMode === 'cards' ? 'active' : '' ?>"><i class="bi bi-grid-3x2-gap me-1"></i>Cards</a>
                        </div>
                    </div>
                </div>
                <div class="module-panel-body p-0">
                    <?php if (!empty($donations)): ?>
                        <?php if ($viewMode === 'cards'): ?>
                            <div class="module-panel-body">
                                <div class="module-resource-grid">
                                    <?php foreach ($donations as $donation): ?>
                                        <article class="module-resource-card d-flex flex-column">
                                            <div class="module-card-eyebrow">
                                                <span class="module-status status-success">$<?= number_format((float) ($donation['amount'] ?? 0), 2) ?></span>
                                                <span class="module-status status-neutral"><?= htmlspecialchars(ucfirst($resolveType($donation))) ?></span>
                                            </div>
                                            <div class="module-row-title fs-5"><?= htmlspecialchars($resolveDonorName($donation)) ?></div>
                                            <p class="module-card-summary">Reference <?= htmlspecialchars($resolveReference($donation)) ?> within the current donation scope.</p>
                                            <div class="module-card-metric-grid">
                                                <div class="module-card-metric"><div class="module-card-metric-label">Date</div><div class="module-card-metric-value"><?= htmlspecialchars($resolveDate($donation)) ?></div></div>
                                                <div class="module-card-metric"><div class="module-card-metric-label">Scope</div><div class="module-card-metric-value"><?= htmlspecialchars($resolveScopeLabel($donation)) ?></div></div>
                                                <div class="module-card-metric"><div class="module-card-metric-label">Reference</div><div class="module-card-metric-value"><?= htmlspecialchars($resolveReference($donation)) ?></div></div>
                                                <div class="module-card-metric"><div class="module-card-metric-label">Mode</div><div class="module-card-metric-value">Read-only</div></div>
                                            </div>
                                            <div class="module-card-actions">
                                                <span class="btn btn-sm btn-outline-secondary disabled">Review only</span>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="module-table">
                                    <thead>
                                        <tr>
                                            <th>Donor</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Scope</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($donations as $donation): ?>
                                            <tr>
                                                <td>
                                                    <div class="module-row-title"><?= htmlspecialchars($resolveDonorName($donation)) ?></div>
                                                    <div class="module-row-meta"><?= htmlspecialchars($resolveReference($donation)) ?></div>
                                                </td>
                                                <td><div class="module-row-title text-success">$<?= number_format((float) ($donation['amount'] ?? 0), 2) ?></div><div class="module-row-meta">Visible contribution amount</div></td>
                                                <td><span class="module-status status-neutral"><?= htmlspecialchars(ucfirst($resolveType($donation))) ?></span></td>
                                                <td><div class="module-row-title"><?= htmlspecialchars($resolveDate($donation)) ?></div><div class="module-row-meta">Recorded donation date</div></td>
                                                <td><div class="module-row-title"><?= htmlspecialchars($resolveScopeLabel($donation)) ?></div><div class="module-row-meta">Hierarchy-resolved scope</div></td>
                                                <td class="text-end"><span class="text-muted small">Read-only in current build</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="module-empty">
                            <i class="bi bi-inbox"></i>
                            <h3 class="h5 mt-3">No donation records are visible</h3>
                            <p class="mb-0">There are no donation entries available for your current hierarchy scope.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Scope Summary</h2></div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Scope</span><span class="module-key-value"><?= htmlspecialchars($user_scope['scope_name'] ?? 'Current hierarchy scope') ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Level</span><span class="module-key-value"><?= htmlspecialchars(ucfirst((string) ($user_scope['level_scope'] ?? 'all'))) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Mutation mode</span><span class="module-key-value">Read-only</span></div>
                    </div>
                    <p class="module-muted-note mt-3 mb-0">Executives can review donations within their visible hierarchy chain while create and edit flows remain intentionally disabled in staging.</p>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-lightning-charge me-2"></i>Safe Actions</h2></div>
                <div class="module-panel-body">
                    <div class="module-stack-list">
                        <div class="module-stack-item"><div><div class="module-row-title"><a href="/donations">Refresh Donations</a></div><div class="module-row-meta">Reload the current scoped donation register.</div></div></div>
                        <div class="module-stack-item"><div><div class="module-row-title"><a href="/donations/reports/summary">Summary Report</a></div><div class="module-row-meta">Open the aggregate donation reporting surface.</div></div></div>
                        <div class="module-stack-item"><div><div class="module-row-title"><a href="/donations/reports/detailed">Detailed Report</a></div><div class="module-row-meta">Inspect the row-level donation report path.</div></div></div>
                        <div class="module-stack-item"><div><div class="module-row-title"><a href="/donations/reports/export?format=csv&type=summary">Export CSV</a></div><div class="module-row-meta">Download the current summary dataset for offline review.</div></div></div>
                    </div>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-info-circle me-2"></i>Build Notes</h2></div>
                <div class="module-panel-body">
                    <div class="module-stack-list">
                        <div class="module-stack-item"><div><div class="module-row-title">Live data source</div><div class="module-row-meta">Records come from the active donations table with hierarchy-aware filtering.</div></div></div>
                        <div class="module-stack-item"><div><div class="module-row-title">Schema drift protection</div><div class="module-row-meta">Missing legacy columns are handled defensively so staging schema differences do not break the page.</div></div></div>
                        <div class="module-stack-item"><div><div class="module-row-title">Next enabled flows</div><div class="module-row-meta">Create, donor management, and campaign analytics should only be enabled after their handlers are completed on the active path.</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
