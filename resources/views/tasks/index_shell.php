<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$title = $title ?? 'Tasks Management';
$tasks = $tasks ?? [];
$taskStats = $task_stats ?? [];
$userScope = $user_scope ?? [];

$formatStatusClass = static function (?string $value): string {
    return match ((string) $value) {
        'completed' => 'status-success',
        'in_progress', 'under_review' => 'status-info',
        'pending' => 'status-warning',
        'cancelled', 'overdue' => 'status-danger',
        default => 'status-neutral',
    };
};

$formatPriorityClass = static function (?string $value): string {
    return match ((string) $value) {
        'high', 'urgent', 'critical' => 'status-danger',
        'medium' => 'status-warning',
        'low' => 'status-success',
        default => 'status-neutral',
    };
};

$totalTasks = (int) ($taskStats['total'] ?? 0);
$completedTasks = (int) ($taskStats['completed'] ?? 0);
$activeTasks = (int) (($taskStats['pending'] ?? 0) + ($taskStats['in_progress'] ?? 0));
$completionRate = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;
$viewMode = (($_GET['view'] ?? 'table') === 'cards') ? 'cards' : 'table';
?>

<div class="module-surface theme-tasks">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-compass"></i> Leader Workspace</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-list-task me-2"></i><?= htmlspecialchars($title) ?></h1>
                    <p class="module-subtitle">A unified task workspace for scoped execution, delivery tracking, assignment, progress updates, discussion, and reporting.</p>
                </div>
                <div class="module-actions">
                    <a href="/tasks/create" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Create Task</a>
                    <a href="/tasks" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
                    <a href="/reports/tasks" class="btn btn-outline-primary"><i class="bi bi-graph-up me-1"></i>Task Report</a>
                    <a href="/reports" class="btn btn-primary"><i class="bi bi-grid-1x2 me-1"></i>Reports Hub</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($userScope['scope_name'] ?? 'Current hierarchy scope') ?></span>
                <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) ($userScope['level_scope'] ?? 'all'))) ?> level</span>
                <span class="module-chip"><i class="bi bi-lightning-charge"></i>Interactive task surface</span>
            </div>
        </div>
    </section>

    <div class="module-callout success">
        <strong>Current task behavior:</strong> scoped tasks now support direct creation, task detail navigation, assignment changes, progress updates, comments, and attachments from the active module surface.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format($totalTasks) ?></div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-collection"></i></span>
                </div>
                <div class="stat-footnote">Scoped records currently visible to this leader.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format((int) ($taskStats['pending'] ?? 0)) ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <div class="stat-footnote">Tasks that still need active follow-through.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format((int) ($taskStats['in_progress'] ?? 0)) ?></div>
                        <div class="stat-label">In Progress</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-lightning-charge"></i></span>
                </div>
                <div class="stat-footnote">Live work items that are already moving.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format((int) ($taskStats['overdue'] ?? 0)) ?></div>
                        <div class="stat-label">Overdue</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="stat-footnote">Items that need recovery attention.</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-table me-2"></i>Scoped Task Queue</h2>
                    <div class="module-toolbar">
                        <span class="module-soft-badge"><i class="bi bi-eye"></i><?= number_format(count($tasks)) ?> loaded</span>
                        <div class="module-view-toggle" role="group" aria-label="Task list view">
                            <a href="/tasks?view=table" class="btn btn-sm <?= $viewMode === 'table' ? 'active' : '' ?>"><i class="bi bi-table me-1"></i>Table</a>
                            <a href="/tasks?view=cards" class="btn btn-sm <?= $viewMode === 'cards' ? 'active' : '' ?>"><i class="bi bi-grid-3x2-gap me-1"></i>Cards</a>
                        </div>
                    </div>
                </div>
                <div class="module-panel-body p-0">
                    <?php if (!empty($tasks)): ?>
                        <?php if ($viewMode === 'cards'): ?>
                            <div class="module-panel-body">
                                <div class="module-resource-grid">
                                    <?php foreach ($tasks as $task): ?>
                                        <?php
                                        $status = ucfirst(str_replace('_', ' ', (string) ($task['status'] ?? 'pending')));
                                        $priority = ucfirst((string) ($task['priority'] ?? 'medium'));
                                        $dueDate = !empty($task['due_date']) ? date('M j, Y', strtotime((string) $task['due_date'])) : 'No due date';
                                        $progress = max(0, min(100, (int) ($task['progress'] ?? 0)));
                                        $assigneeLabel = !empty($task['assignee_names']) ? implode(', ', (array) $task['assignee_names']) : 'Unassigned';
                                        ?>
                                        <article class="module-resource-card d-flex flex-column">
                                            <div class="module-card-eyebrow">
                                                <span class="module-status <?= $formatStatusClass((string) ($task['status'] ?? 'pending')) ?>"><?= htmlspecialchars($status) ?></span>
                                                <span class="module-status <?= $formatPriorityClass((string) ($task['priority'] ?? 'medium')) ?>"><?= htmlspecialchars($priority) ?></span>
                                            </div>
                                            <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>" class="module-row-title fs-5"><?= htmlspecialchars($task['title'] ?? 'Untitled task') ?></a>
                                            <p class="module-card-summary"><?= htmlspecialchars($task['description'] ?? 'No description provided.') ?></p>
                                            <div class="module-card-metric-grid">
                                                <div class="module-card-metric"><div class="module-card-metric-label">Assigned</div><div class="module-card-metric-value"><?= htmlspecialchars($assigneeLabel) ?></div></div>
                                                <div class="module-card-metric"><div class="module-card-metric-label">Due date</div><div class="module-card-metric-value"><?= htmlspecialchars($dueDate) ?></div></div>
                                            </div>
                                            <div class="module-card-metric">
                                                <div class="d-flex justify-content-between small fw-semibold"><span>Progress</span><span><?= number_format($progress) ?>%</span></div>
                                                <div class="module-progress-track"><div class="module-progress-fill" style="width: <?= $progress ?>%;"></div></div>
                                            </div>
                                            <div class="module-card-actions">
                                                <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
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
                                            <th>Task</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Assigned To</th>
                                            <th>Due Date</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tasks as $task): ?>
                                            <?php
                                            $status = ucfirst(str_replace('_', ' ', (string) ($task['status'] ?? 'pending')));
                                            $priority = ucfirst((string) ($task['priority'] ?? 'medium'));
                                            $dueDate = !empty($task['due_date']) ? date('M j, Y', strtotime((string) $task['due_date'])) : 'No due date';
                                            $progress = max(0, min(100, (int) ($task['progress'] ?? 0)));
                                            $assigneeLabel = !empty($task['assignee_names']) ? implode(', ', (array) $task['assignee_names']) : 'Unassigned';
                                            ?>
                                            <tr>
                                                <td>
                                                    <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>" class="module-row-title d-inline-block"><?= htmlspecialchars($task['title'] ?? 'Untitled task') ?></a>
                                                    <div class="module-row-meta"><?= htmlspecialchars($task['description'] ?? 'No description provided.') ?></div>
                                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                                        <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                        <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                    </div>
                                                </td>
                                                <td><span class="module-status <?= $formatStatusClass((string) ($task['status'] ?? 'pending')) ?>"><?= htmlspecialchars($status) ?></span></td>
                                                <td><span class="module-status <?= $formatPriorityClass((string) ($task['priority'] ?? 'medium')) ?>"><?= htmlspecialchars($priority) ?></span></td>
                                                <td>
                                                    <div class="module-row-title"><?= htmlspecialchars($assigneeLabel) ?></div>
                                                    <div class="module-row-meta">Hierarchy-visible assignee set</div>
                                                </td>
                                                <td>
                                                    <div class="module-row-title"><?= htmlspecialchars($dueDate) ?></div>
                                                    <div class="module-row-meta">Current target date</div>
                                                </td>
                                                <td style="min-width: 180px;">
                                                    <div class="d-flex justify-content-between small fw-semibold">
                                                        <span><?= number_format($progress) ?>%</span>
                                                        <span class="text-muted">completion</span>
                                                    </div>
                                                    <div class="module-progress-track">
                                                        <div class="module-progress-fill" style="width: <?= $progress ?>%;"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="module-empty">
                            <i class="bi bi-inbox"></i>
                            <h3 class="h5 mt-3">No scoped tasks are currently visible</h3>
                            <p class="mb-0">The current leader scope does not expose any task records yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-speedometer2 me-2"></i>Execution Snapshot</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row">
                            <span class="module-key-label">Active workload</span>
                            <span class="module-key-value"><?= number_format($activeTasks) ?> tasks</span>
                        </div>
                        <div class="module-key-row">
                            <span class="module-key-label">Completion rate</span>
                            <span class="module-key-value"><?= number_format($completionRate) ?>%</span>
                        </div>
                        <div class="module-key-row">
                            <span class="module-key-label">Overdue pressure</span>
                            <span class="module-key-value"><?= number_format((int) ($taskStats['overdue'] ?? 0)) ?> flagged</span>
                        </div>
                    </div>

                    <div class="module-progress-track mt-3">
                        <div class="module-progress-fill" style="width: <?= $completionRate ?>%;"></div>
                    </div>
                    <p class="module-muted-note mt-2 mb-0">This rate is based on completed tasks within the currently visible task pool.</p>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Scope Summary</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-stack-list">
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Scope</div>
                                <div class="module-row-meta"><?= htmlspecialchars($userScope['scope_name'] ?? 'Current hierarchy scope') ?></div>
                            </div>
                            <div class="module-stack-value"><i class="bi bi-diagram-3"></i></div>
                        </div>
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Level</div>
                                <div class="module-row-meta"><?= htmlspecialchars(ucfirst((string) ($userScope['level_scope'] ?? 'all'))) ?></div>
                            </div>
                            <div class="module-stack-value"><i class="bi bi-layers"></i></div>
                        </div>
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Reporting</div>
                                <div class="module-row-meta">Task analytics are available through the reports module.</div>
                            </div>
                            <div class="module-stack-value"><i class="bi bi-graph-up"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-info-circle me-2"></i>Build Notes</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-stack-list">
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Live source</div>
                                <div class="module-row-meta">Records come from the live tasks table with hierarchy-aware filtering.</div>
                            </div>
                        </div>
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Interface honesty</div>
                                <div class="module-row-meta">Only supported navigation and reporting actions remain visible in this build.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>