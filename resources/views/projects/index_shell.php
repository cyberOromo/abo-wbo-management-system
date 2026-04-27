<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$title = $title ?? 'Projects & Initiatives';
$projects = $projects ?? [];
$stats = $stats ?? [];
$scope = $scope ?? [];

$statusClass = static function (?string $value): string {
    return match ((string) $value) {
        'completed' => 'status-success',
        'active' => 'status-info',
        'on_hold' => 'status-warning',
        'archived' => 'status-neutral',
        default => 'status-neutral',
    };
};
?>

<div class="module-surface theme-reports">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-kanban"></i> Responsibility Type Workspace</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-kanban-fill me-2"></i><?= htmlspecialchars($title) ?></h1>
                    <p class="module-subtitle">A scope-aware portfolio workspace for Projectoota planning, delivery visibility, team ownership, milestone discipline, and executive review across the active hierarchy chain.</p>
                </div>
                <div class="module-actions">
                    <a href="/projects/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>New Project</a>
                    <a href="/projects" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($scope['scope_name'] ?? 'Current project scope') ?></span>
                <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) ($scope['level_scope'] ?? 'all'))) ?> level</span>
                <span class="module-chip"><i class="bi bi-stars"></i>Projectoota delivery focus</span>
            </div>
        </div>
    </section>

    <div class="module-callout info">
        <strong>Project module:</strong> scope-aware visibility, multi-user ownership, milestone tracking, hierarchical project tasks, and progress governance are active. Teams can now coordinate a global-to-local execution chain under one project record.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['total'] ?? 0)) ?></div><div class="stat-label">Total Projects</div></div><span class="stat-icon"><i class="bi bi-kanban"></i></span></div><div class="stat-footnote">Visible in the current hierarchy scope.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['active'] ?? 0)) ?></div><div class="stat-label">Active</div></div><span class="stat-icon"><i class="bi bi-lightning-charge"></i></span></div><div class="stat-footnote">Projects in active delivery.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['proposed'] ?? 0)) ?></div><div class="stat-label">Proposed</div></div><span class="stat-icon"><i class="bi bi-compass"></i></span></div><div class="stat-footnote">Ideas awaiting activation.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['avg_progress'] ?? 0)) ?>%</div><div class="stat-label">Average Progress</div></div><span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span></div><div class="stat-footnote"><?= number_format((int) ($stats['completed'] ?? 0)) ?> completed, <?= number_format((int) ($stats['on_hold'] ?? 0)) ?> on hold.</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-clipboard-data me-2"></i>Project Register</h2>
                    <span class="module-soft-badge"><i class="bi bi-eye"></i><?= number_format(count($projects)) ?> loaded</span>
                </div>
                <div class="module-panel-body p-0">
                    <?php if (!empty($projects)): ?>
                        <div class="table-responsive">
                            <table class="module-table">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Progress</th>
                                        <th>Team</th>
                                        <th>Owner</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td>
                                                <div class="module-row-title"><a href="/projects/<?= (int) $project['id'] ?>"><?= htmlspecialchars($project['title'] ?? 'Untitled project') ?></a></div>
                                                <div class="module-row-meta"><?= htmlspecialchars($project['summary'] ?? 'No summary provided.') ?></div>
                                                <div class="module-row-meta"><?= htmlspecialchars($project['project_code'] ?? '') ?></div>
                                            </td>
                                            <td><span class="module-status <?= $statusClass((string) ($project['status'] ?? 'proposed')) ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($project['status'] ?? 'proposed')))) ?></span></td>
                                            <td><div class="module-row-title"><?= htmlspecialchars(ucfirst((string) ($project['priority'] ?? 'medium'))) ?></div><div class="module-row-meta"><?= htmlspecialchars(ucfirst((string) ($project['project_type'] ?? 'initiative'))) ?></div></td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar" role="progressbar" style="width: <?= (int) ($project['completion_percentage'] ?? 0) ?>%"></div>
                                                </div>
                                                <div class="module-row-meta mt-2"><?= (int) ($project['completion_percentage'] ?? 0) ?>% complete</div>
                                            </td>
                                            <td><div class="module-row-title"><?= (int) ($project['team_members'] ?? 0) ?> people</div><div class="module-row-meta"><?= (int) ($project['milestones_completed'] ?? 0) ?>/<?= (int) ($project['milestones_total'] ?? 0) ?> milestones</div></td>
                                            <td><div class="module-row-title"><?= htmlspecialchars(trim((string) ($project['owner_name'] ?? ''))) ?: 'Unassigned' ?></div><div class="module-row-meta"><?= htmlspecialchars((string) ($project['target_date'] ?? 'No target date')) ?></div></td>
                                            <td class="text-end"><a href="/projects/<?= (int) $project['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="module-empty">
                            <i class="bi bi-kanban"></i>
                            <h3 class="h5 mt-3">No scoped projects are visible yet</h3>
                            <p class="mb-3">Create the first Projectoota record for this hierarchy scope to start tracking strategic initiatives, ownership, and progress.</p>
                            <a href="/projects/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Create Project</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Portfolio Guardrails</h2></div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Scope</span><span class="module-key-value"><?= htmlspecialchars($scope['scope_name'] ?? 'Current project scope') ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Core responsibility</span><span class="module-key-value">Projectoota</span></div>
                        <div class="module-key-row"><span class="module-key-label">Visible level</span><span class="module-key-value"><?= htmlspecialchars(ucfirst((string) ($scope['level_scope'] ?? 'all'))) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Tracked work</span><span class="module-key-value"><?= number_format((int) ($stats['tasks_total'] ?? 0)) ?> project tasks</span></div>
                    </div>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-lightbulb me-2"></i>High-End Features Included</h2></div>
                <div class="module-panel-body">
                    <div class="module-stack-list">
                        <div class="module-stack-item"><div><div class="module-row-title">Cascade visibility</div><div class="module-row-meta">Global projects remain visible to lower hierarchy scopes in the same chain.</div></div></div>
                        <div class="module-stack-item"><div><div class="module-row-title">Milestones and delivery telemetry</div><div class="module-row-meta">Status, priority, milestones, progress, budget, and outcome metrics are stored per initiative.</div></div></div>
                        <div class="module-stack-item"><div><div class="module-row-title">Project tasks and subtasks</div><div class="module-row-meta">Teams can assign project work to descendant scopes and keep subtasks nested under one project record.</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>