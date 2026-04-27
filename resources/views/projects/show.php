<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$project = $project ?? [];
$title = $title ?? ($project['title'] ?? 'Project Detail');
$scope = $scope ?? [];
$assignments = $assignments ?? [];
$milestones = $milestones ?? [];
$tasks = $tasks ?? [];
$activity = $activity ?? [];
$availableUsers = $availableUsers ?? [];
$taskScopeOptions = $taskScopeOptions ?? [];
$taskParentOptions = $taskParentOptions ?? [];

$status = (string) ($project['status'] ?? 'proposed');
$priority = (string) ($project['priority'] ?? 'medium');
$progress = (int) ($project['completion_percentage'] ?? 0);

$statusClass = match ($status) {
    'completed' => 'status-success',
    'active' => 'status-info',
    'on_hold' => 'status-warning',
    'archived' => 'status-neutral',
    default => 'status-neutral',
};

$priorityClass = match ($priority) {
    'critical', 'high' => 'status-danger',
    'medium' => 'status-warning',
    default => 'status-info',
};

$formatDate = static function (?string $value, string $fallback = 'Not set'): string {
    if (empty($value)) {
        return $fallback;
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('M j, Y', $timestamp) : $fallback;
};

$renderTasks = function (array $nodes) use (&$renderTasks): void {
    if (empty($nodes)) {
        return;
    }

    echo '<div class="module-stack-list">';
    foreach ($nodes as $task) {
        echo '<div class="module-stack-item">';
        echo '<div class="w-100">';
        echo '<div class="d-flex justify-content-between gap-3 flex-wrap">';
        echo '<div>'; 
        echo '<div class="module-row-title">' . htmlspecialchars((string) ($task['title'] ?? 'Untitled task')) . '</div>';
        echo '<div class="module-row-meta">' . htmlspecialchars(ucfirst((string) ($task['level_scope'] ?? 'scope'))) . ' scope';
        if (!empty($task['creator_name'])) {
            echo ' · Created by ' . htmlspecialchars((string) $task['creator_name']);
        }
        echo ' · ' . count($task['assigned_to'] ?? []) . ' assignees</div>';
        if (!empty($task['description'])) {
            echo '<div class="module-row-meta">' . htmlspecialchars((string) $task['description']) . '</div>';
        }
        echo '</div>';
        echo '<div class="text-end">';
        echo '<span class="module-status status-info">' . htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($task['status'] ?? 'pending')))) . '</span>';
        echo '<div class="module-row-meta mt-2">' . (int) ($task['completion_percentage'] ?? 0) . '% complete</div>';
        echo '</div>';
        echo '</div>';
        if (!empty($task['children'])) {
            echo '<div class="ms-3 mt-3 border-start ps-3">';
            $renderTasks($task['children']);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
};
?>

<div class="module-surface theme-projects">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-kanban-fill"></i> Project Detail</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-kanban-fill me-2"></i><?= htmlspecialchars($title) ?></h1>
                    <p class="module-subtitle"><?= htmlspecialchars((string) ($project['summary'] ?? 'No summary provided.')) ?></p>
                </div>
                <div class="module-actions">
                    <a href="/projects/<?= (int) ($project['id'] ?? 0) ?>/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil-square me-1"></i>Edit Project</a>
                    <?php if ($status !== 'archived'): ?>
                        <form method="POST" action="/projects/<?= (int) ($project['id'] ?? 0) ?>/archive">
                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-archive me-1"></i>Archive</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-upc-scan"></i><?= htmlspecialchars((string) ($project['project_code'] ?? 'Uncoded')) ?></span>
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars((string) ($scope['scope_name'] ?? 'Current scope')) ?></span>
                <span class="module-chip"><i class="bi bi-calendar3"></i>Target <?= htmlspecialchars($formatDate($project['target_date'] ?? null, 'Not set')) ?></span>
            </div>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= $progress ?>%</div><div class="stat-label">Progress</div></div><span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span></div><div class="stat-footnote">Portfolio completion blended from milestones and project tasks.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count($assignments)) ?></div><div class="stat-label">Team Members</div></div><span class="stat-icon"><i class="bi bi-people"></i></span></div><div class="stat-footnote">Assigned individuals across the active hierarchy chain.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count($milestones)) ?></div><div class="stat-label">Milestones</div></div><span class="stat-icon"><i class="bi bi-signpost-2"></i></span></div><div class="stat-footnote">Delivery checkpoints tracked on this initiative.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count($taskParentOptions)) ?></div><div class="stat-label">Project Tasks</div></div><span class="stat-icon"><i class="bi bi-diagram-2"></i></span></div><div class="stat-footnote">Top-level tasks and subtasks linked to the project record.</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Project Snapshot</h2><span class="module-status <?= $statusClass ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span></div>
                <div class="module-panel-body">
                    <div class="module-key-grid mb-4">
                        <div class="module-key-row"><span class="module-key-label">Priority</span><span class="module-key-value"><span class="module-status <?= $priorityClass ?>"><?= htmlspecialchars(ucfirst($priority)) ?></span></span></div>
                        <div class="module-key-row"><span class="module-key-label">Project type</span><span class="module-key-value"><?= htmlspecialchars(ucfirst((string) ($project['project_type'] ?? 'initiative'))) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Owner</span><span class="module-key-value"><?= htmlspecialchars(trim((string) ($project['owner_name'] ?? ''))) ?: 'Unassigned' ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Budget</span><span class="module-key-value"><?= isset($project['budget_amount']) && $project['budget_amount'] !== null ? htmlspecialchars('$' . number_format((float) $project['budget_amount'], 2)) : 'Not set' ?></span></div>
                    </div>
                    <div class="module-row-title">Completion progress</div>
                    <div class="module-progress-track"><div class="module-progress-fill" style="width: <?= $progress ?>%;"></div></div>
                    <div class="module-row-meta"><?= $progress ?>% complete</div>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-file-earmark-text me-2"></i>Project Brief</h2></div>
                <div class="module-panel-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars((string) ($project['description'] ?? 'No project brief provided yet.'))) ?></p>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-truck me-2"></i>Delivery Notes</h2></div>
                <div class="module-panel-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars((string) ($project['delivery_notes'] ?? 'No delivery notes captured yet.'))) ?></p>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-signpost-2 me-2"></i>Milestones</h2><span class="module-soft-badge"><?= number_format(count($milestones)) ?> tracked</span></div>
                <div class="module-panel-body">
                    <?php if (!empty($milestones)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($milestones as $milestone): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars((string) ($milestone['title'] ?? 'Untitled milestone')) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars((string) ($milestone['summary'] ?? 'No summary provided.')) ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="module-stack-value"><?= (int) ($milestone['completion_percentage'] ?? 0) ?>%</div>
                                        <div class="module-row-meta"><?= htmlspecialchars($formatDate($milestone['due_date'] ?? null, 'No due date')) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="module-empty py-4"><i class="bi bi-signpost-2"></i><p class="mb-0 mt-2">No milestones have been added yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-diagram-2 me-2"></i>Project Tasks and Subtasks</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($tasks)): ?>
                        <?php $renderTasks($tasks); ?>
                    <?php else: ?>
                        <div class="module-empty py-4"><i class="bi bi-diagram-2"></i><p class="mb-0 mt-2">No project tasks have been created yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-clock-history me-2"></i>Activity Timeline</h2><span class="module-soft-badge"><?= number_format(count($activity)) ?> recent events</span></div>
                <div class="module-panel-body">
                    <?php if (!empty($activity)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($activity as $item): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars((string) ($item['description'] ?? 'Activity')) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars((string) ($item['actor_name'] ?? 'System')) ?> · <?= htmlspecialchars((string) ($item['created_at'] ?? '')) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No activity has been recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Outcome Metrics</h2></div>
                <div class="module-panel-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars((string) ($project['success_metrics'] ?? 'No success metrics defined yet.'))) ?></p>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-people me-2"></i>Project Team</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($assignments)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($assignments as $assignment): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars((string) ($assignment['full_name'] ?? 'Unassigned user')) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars(ucfirst((string) ($assignment['assignment_role'] ?? 'contributor'))) ?><?= !empty($assignment['internal_email']) ? ' · ' . htmlspecialchars((string) $assignment['internal_email']) : '' ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No project assignments captured yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-plus-circle me-2"></i>Add Milestone</h2></div>
                <div class="module-panel-body">
                    <form method="POST" action="/projects/<?= (int) ($project['id'] ?? 0) ?>/milestones" class="row g-3">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <div class="col-12"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">Summary</label><textarea name="summary" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="planned">Planned</option><option value="in_progress">In Progress</option><option value="completed">Completed</option><option value="blocked">Blocked</option></select></div>
                        <div class="col-md-6"><label class="form-label">Completion %</label><input type="number" name="completion_percentage" class="form-control" min="0" max="100" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" min="0" value="0"></div>
                        <div class="col-12"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle me-1"></i>Add Milestone</button></div>
                    </form>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-diagram-2 me-2"></i>Add Project Task</h2></div>
                <div class="module-panel-body">
                    <form method="POST" action="/projects/<?= (int) ($project['id'] ?? 0) ?>/tasks" class="row g-3">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <div class="col-12"><label class="form-label">Task Title</label><input type="text" name="title" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                        <div class="col-12"><label class="form-label">Target Scope</label><select name="scope_selection" class="form-select" required><?php foreach ($taskScopeOptions as $option): ?><option value="<?= htmlspecialchars((string) ($option['value'] ?? '')) ?>"><?= htmlspecialchars((string) ($option['label'] ?? '')) ?></option><?php endforeach; ?></select></div>
                        <div class="col-12"><label class="form-label">Parent Task</label><select name="parent_task_id" class="form-select"><option value="">Top-level project task</option><?php foreach ($taskParentOptions as $taskOption): ?><option value="<?= (int) ($taskOption['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($taskOption['title'] ?? 'Untitled task')) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                        <div class="col-md-6"><label class="form-label">Category</label><select name="category" class="form-select"><option value="administrative">Administrative</option><option value="financial">Financial</option><option value="educational">Educational</option><option value="social">Social</option><option value="technical">Technical</option></select></div>
                        <div class="col-md-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Assign Users</label><select name="assigned_to[]" class="form-select" multiple size="6"><?php foreach ($availableUsers as $availableUser): ?><option value="<?= (int) ($availableUser['id'] ?? 0) ?>"><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? ''))) ?><?= !empty($availableUser['level_scope']) ? ' - ' . htmlspecialchars(ucfirst((string) $availableUser['level_scope'])) : '' ?></option><?php endforeach; ?></select><div class="form-text">Use a parent task to create a subtask under an existing project task.</div></div>
                        <div class="col-12"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle me-1"></i>Save Task</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>