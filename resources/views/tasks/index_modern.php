<?php
require __DIR__ . '/index_shell.php';
return;

$title = $title ?? 'Task Management';
$tasks = $tasks ?? [];
$taskStats = $task_stats ?? [];
$userScope = $user_scope ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-check-square me-2"></i>
            <?= htmlspecialchars($title) ?>
        </h1>
        <p class="text-muted mb-0">Real task visibility for the current hierarchy scope.</p>
    </div>
    <div class="btn-toolbar gap-2">
        <a href="/tasks" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Refresh
        </a>
        <a href="/reports/tasks" class="btn btn-outline-primary">
            <i class="bi bi-graph-up me-1"></i>
            Task Reports
        </a>
    </div>
</div>

<div class="alert alert-warning border-0 rounded-3 mb-4" style="background: linear-gradient(135deg, #fff3cd, #ffe69c);">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-circle fs-4"></i>
        <div>
            <h5 class="mb-1">Current Staging Scope</h5>
            <p class="mb-1">This page shows real task records currently visible to your executive scope.</p>
            <p class="mb-0 small text-muted">Create, edit, templates, kanban, and calendar flows are hidden until their backing task views are implemented.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($taskStats['total'] ?? 0) ?></h3><p class="text-muted mb-0">Total Tasks</p></div></div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($taskStats['pending'] ?? 0) ?></h3><p class="text-muted mb-0">Pending</p></div></div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($taskStats['in_progress'] ?? 0) ?></h3><p class="text-muted mb-0">In Progress</p></div></div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($taskStats['overdue'] ?? 0) ?></h3><p class="text-muted mb-0">Overdue</p></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-list-task me-2"></i>Scoped Tasks</h5>
                <span class="badge bg-light text-dark"><?= number_format(count($tasks)) ?> loaded</span>
            </div>
            <div class="card-body">
                <?php if (!empty($tasks)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <?php
                                    $assignee = trim((string) (($task['assigned_first_name'] ?? '') . ' ' . ($task['assigned_last_name'] ?? '')));
                                    if ($assignee === '') {
                                        $assignee = 'Unassigned';
                                    }
                                    $status = ucfirst(str_replace('_', ' ', (string) ($task['status'] ?? 'pending')));
                                    $priority = ucfirst((string) ($task['priority'] ?? 'medium'));
                                    $dueDate = !empty($task['due_date']) ? date('M j, Y', strtotime((string) $task['due_date'])) : 'No due date';
                                    $progress = (int) ($task['progress'] ?? 0);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($task['title'] ?? 'Untitled task') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($task['description'] ?? 'No description provided.') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($priority) ?></td>
                                        <td><?= htmlspecialchars($status) ?></td>
                                        <td><?= htmlspecialchars($assignee) ?></td>
                                        <td><?= htmlspecialchars($dueDate) ?></td>
                                        <td><?= number_format($progress) ?>%</td>
                                        <td><span class="text-muted small">Read-only in current build</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No task records are visible</h5>
                        <p class="text-muted mb-0">There are no task entries available for your current hierarchy scope.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="card-title mb-0"><i class="bi bi-bullseye me-2"></i>Scope Summary</h6></div>
            <div class="card-body">
                <p class="mb-2"><strong>Scope:</strong> <?= htmlspecialchars($userScope['scope_name'] ?? 'Current hierarchy scope') ?></p>
                <p class="mb-2"><strong>Level:</strong> <?= htmlspecialchars(ucfirst($userScope['level_scope'] ?? 'all')) ?></p>
                <p class="mb-0 text-muted small">Task actions are restricted to read-only visibility in this staging build.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Build Notes</h6></div>
            <div class="card-body">
                <ul class="small text-muted mb-0 ps-3">
                    <li>Tasks listed here are pulled from the live tasks table with hierarchy-aware filtering.</li>
                    <li>Create, edit, templates, kanban, and calendar affordances were removed because their views are not present in the current build.</li>
                    <li>Use task reports for deeper analytics until mutation flows are completed.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
