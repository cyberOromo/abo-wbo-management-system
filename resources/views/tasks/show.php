<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$task = $task ?? [];
$history = $history ?? [];
$subtasks = $subtasks ?? [];
$comments = $comments ?? [];
$assignedUsers = $assignedUsers ?? [];
$userScope = $userScope ?? [];
$availableUsers = $availableUsers ?? [];
$progress = (int) ($task['completion_percentage'] ?? $task['progress_percentage'] ?? 0);
$attachments = is_array($task['attachments'] ?? null) ? $task['attachments'] : [];

$status = (string) ($task['status'] ?? 'pending');
$priority = (string) ($task['priority'] ?? 'medium');
$category = (string) ($task['category'] ?? 'administrative');
$scopeName = (string) ($userScope['scope_name'] ?? ucfirst((string) ($task['level_scope'] ?? 'scope')));
$selectedAssignees = array_map('intval', (array) ($task['assigned_to'] ?? []));

$statusClass = match ($status) {
    'completed' => 'status-success',
    'in_progress', 'under_review' => 'status-info',
    'on_hold' => 'status-warning',
    'cancelled' => 'status-danger',
    default => 'status-neutral',
};

$priorityClass = match ($priority) {
    'urgent', 'high' => 'status-danger',
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
?>

<div class="module-surface theme-tasks">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-list-task"></i> Task Detail</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-list-task me-2"></i><?= htmlspecialchars((string) ($task['title'] ?? 'Task')) ?></h1>
                    <p class="module-subtitle"><?= htmlspecialchars((string) ($task['description'] ?? 'No description provided.')) ?></p>
                </div>
                <div class="module-actions">
                    <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>/edit" class="btn btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Edit Task</a>
                    <a href="/tasks" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Tasks</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($scopeName) ?></span>
                <span class="module-chip"><i class="bi bi-flag"></i><?= htmlspecialchars(ucfirst($category)) ?></span>
                <span class="module-chip"><i class="bi bi-calendar3"></i>Due <?= htmlspecialchars($formatDate($task['due_date'] ?? null, 'No deadline')) ?></span>
            </div>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= $progress ?>%</div><div class="stat-label">Progress</div></div><span class="stat-icon"><i class="bi bi-speedometer2"></i></span></div>
                <div class="stat-footnote">Current completion based on the live staging task schema.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format(count($assignedUsers)) ?></div><div class="stat-label">Assignees</div></div><span class="stat-icon"><i class="bi bi-people"></i></span></div>
                <div class="stat-footnote">People currently assigned to this task.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format(count($comments)) ?></div><div class="stat-label">Comments</div></div><span class="stat-icon"><i class="bi bi-chat-dots"></i></span></div>
                <div class="stat-footnote">Conversation and internal-note entries on this task.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format(count($subtasks)) ?></div><div class="stat-label">Subtasks</div></div><span class="stat-icon"><i class="bi bi-diagram-2"></i></span></div>
                <div class="stat-footnote">Linked child tasks under this execution item.</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel mb-4">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Execution Snapshot</h2>
                    <span class="module-status <?= $statusClass ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?></span>
                </div>
                <div class="module-panel-body">
                    <div class="module-key-grid mb-4">
                        <div class="module-key-row"><span class="module-key-label">Priority</span><span class="module-key-value"><span class="module-status <?= $priorityClass ?>"><?= htmlspecialchars(ucfirst($priority)) ?></span></span></div>
                        <div class="module-key-row"><span class="module-key-label">Category</span><span class="module-key-value"><?= htmlspecialchars(ucfirst($category)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Start date</span><span class="module-key-value"><?= htmlspecialchars($formatDate($task['start_date'] ?? null)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Due date</span><span class="module-key-value"><?= htmlspecialchars($formatDate($task['due_date'] ?? null, 'No deadline')) ?></span></div>
                    </div>
                    <div>
                        <div class="module-row-title">Completion progress</div>
                        <div class="module-progress-track">
                            <div class="module-progress-fill" style="width: <?= $progress ?>%;"></div>
                        </div>
                        <div class="module-row-meta"><?= $progress ?>% complete</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="module-panel h-100">
                        <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-arrow-repeat me-2"></i>Status Update</h2></div>
                        <div class="module-panel-body">
                            <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/status" class="module-form-grid">
                                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                <div>
                                    <label>Status</label>
                                    <select name="status" class="form-select">
                                        <?php foreach (['pending' => 'Pending', 'in_progress' => 'In Progress', 'under_review' => 'Under Review', 'completed' => 'Completed', 'on_hold' => 'On Hold', 'cancelled' => 'Cancelled'] as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-repeat me-1"></i>Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="module-panel h-100">
                        <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-speedometer2 me-2"></i>Progress Update</h2></div>
                        <div class="module-panel-body">
                            <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/progress" class="module-form-grid">
                                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                <div>
                                    <label>Completion Percentage</label>
                                    <input type="number" name="completion_percentage" class="form-control" min="0" max="100" value="<?= $progress ?>">
                                </div>
                                <div class="d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2-circle me-1"></i>Update Progress</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-paperclip me-2"></i>Attachments</h2></div>
                <div class="module-panel-body">
                    <?php
                    $resource = 'tasks';
                    $resourceId = (int) ($task['id'] ?? 0);
                    $contextLabel = 'Task attachment';
                    $emptyMessage = 'No attachments uploaded yet.';
                    require dirname(__DIR__) . '/partials/attachment_list.php';
                    ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-diagram-2 me-2"></i>Subtasks</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($subtasks)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($subtasks as $subtask): ?>
                                <a href="/tasks/<?= (int) ($subtask['id'] ?? 0) ?>" class="module-stack-item text-decoration-none">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars((string) ($subtask['title'] ?? 'Untitled subtask')) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars((string) ($subtask['description'] ?? 'No subtask summary provided.')) ?></div>
                                    </div>
                                    <div class="module-stack-value"><i class="bi bi-arrow-right"></i></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="module-empty py-4"><i class="bi bi-diagram-2"></i><p class="mb-0 mt-2">No subtasks yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-chat-square-text me-2"></i>Comments</h2><span class="module-soft-badge"><?= number_format(count($comments)) ?> entries</span></div>
                <div class="module-panel-body">
                    <?php if (!empty($comments)): ?>
                        <div class="module-stack-list mb-4">
                            <?php foreach ($comments as $comment): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars(trim((string) (($comment['first_name'] ?? '') . ' ' . ($comment['last_name'] ?? '')))) ?: 'Unknown user' ?></div>
                                        <div class="module-row-meta mb-2"><?= htmlspecialchars((string) ($comment['created_at'] ?? '')) ?></div>
                                        <div><?= nl2br(htmlspecialchars((string) ($comment['comment'] ?? ''))) ?></div>
                                        <?php if (!empty($comment['attachments']) && is_array($comment['attachments'])): ?>
                                            <div class="mt-3">
                                                <?php
                                                $attachments = $comment['attachments'];
                                                $resource = 'task-comments';
                                                $resourceId = (int) ($comment['id'] ?? 0);
                                                $contextLabel = 'Comment attachment';
                                                $emptyMessage = 'No comment attachments uploaded.';
                                                require dirname(__DIR__) . '/partials/attachment_list.php';
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note">No comments yet.</p>
                    <?php endif; ?>

                    <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/comments" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <div class="col-12">
                            <label class="form-label">Add Comment</label>
                            <textarea name="comment" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Comment Attachments</label>
                            <input type="file" name="comment_attachments[]" class="form-control" multiple>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="is_internal">
                                <label class="form-check-label" for="is_internal">Internal note</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-chat-square-text me-1"></i>Post Comment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-people me-2"></i>Assigned Users</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($assignedUsers)): ?>
                        <div class="module-stack-list mb-4">
                            <?php foreach ($assignedUsers as $assignedUser): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars(trim((string) (($assignedUser['first_name'] ?? '') . ' ' . ($assignedUser['last_name'] ?? '')))) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars((string) ($assignedUser['internal_email'] ?? '')) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note">No users are currently assigned.</p>
                    <?php endif; ?>

                    <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/assign">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <label class="form-label">Update Assignees</label>
                        <select name="assigned_to[]" class="form-select" multiple size="7">
                            <?php foreach ($availableUsers as $availableUser): ?>
                                <?php $availableUserId = (int) ($availableUser['id'] ?? 0); ?>
                                <option value="<?= $availableUserId ?>" <?= in_array($availableUserId, $selectedAssignees, true) ? 'selected' : '' ?>><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? 'Unknown user'))) ?><?= !empty($availableUser['level_scope']) ? ' - ' . htmlspecialchars(ucfirst((string) $availableUser['level_scope'])) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline-primary mt-3"><i class="bi bi-people me-1"></i>Save Assignees</button>
                    </form>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-eye me-2"></i>Available Assignees In Scope</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($availableUsers)): ?>
                        <div class="module-muted-note mb-3">Users visible from <?= htmlspecialchars($scopeName) ?>.</div>
                        <div class="module-stack-list">
                            <?php foreach ($availableUsers as $availableUser): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? 'Unknown user'))) ?></div>
                                    </div>
                                    <div class="module-stack-value"><?= htmlspecialchars(ucfirst((string) ($availableUser['level_scope'] ?? ''))) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No visible assignees were resolved for this scope.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-clock-history me-2"></i>Activity Timeline</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($history)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($history as $entry): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars((string) ($entry['description'] ?? $entry['action'] ?? 'Activity')) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars(trim((string) (($entry['first_name'] ?? '') . ' ' . ($entry['last_name'] ?? '')))) ?><?= !empty($entry['created_at']) ? ' · ' . htmlspecialchars((string) $entry['created_at']) : '' ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No activity has been logged yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>