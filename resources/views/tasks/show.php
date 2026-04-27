<?php
$task = $task ?? [];
$history = $history ?? [];
$subtasks = $subtasks ?? [];
$comments = $comments ?? [];
$assignedUsers = $assignedUsers ?? [];
$userScope = $userScope ?? [];
$availableUsers = $availableUsers ?? [];
$progress = (int) ($task['completion_percentage'] ?? $task['progress_percentage'] ?? 0);
$attachments = is_array($task['attachments'] ?? null) ? $task['attachments'] : [];
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <div class="text-muted small mb-1">Standalone task detail</div>
            <h1 class="h3 mb-1"><i class="bi bi-list-task me-2"></i><?= htmlspecialchars((string) ($task['title'] ?? 'Task')) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars((string) ($task['description'] ?? 'No description provided.')) ?></p>
        </div>
        <div class="d-flex gap-2 align-items-start">
            <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>/edit" class="btn btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
            <a href="/tasks" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Tasks</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="fw-semibold small text-muted text-uppercase mb-1">Status</div>
                            <div><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($task['status'] ?? 'pending')))) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold small text-muted text-uppercase mb-1">Priority</div>
                            <div><?= htmlspecialchars(ucfirst((string) ($task['priority'] ?? 'medium'))) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold small text-muted text-uppercase mb-1">Category</div>
                            <div><?= htmlspecialchars(ucfirst((string) ($task['category'] ?? 'administrative'))) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold small text-muted text-uppercase mb-1">Scope</div>
                            <div><?= htmlspecialchars((string) ($userScope['scope_name'] ?? ucfirst((string) ($task['level_scope'] ?? '')))) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold small text-muted text-uppercase mb-1">Start Date</div>
                            <div><?= !empty($task['start_date']) ? htmlspecialchars(date('M j, Y', strtotime((string) $task['start_date']))) : 'Not set' ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold small text-muted text-uppercase mb-1">Due Date</div>
                            <div><?= !empty($task['due_date']) ? htmlspecialchars(date('M j, Y', strtotime((string) $task['due_date']))) : 'Not set' ?></div>
                        </div>
                        <div class="col-12">
                            <div class="fw-semibold small text-muted text-uppercase mb-2">Progress</div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="small text-muted mt-2"><?= $progress ?>% complete</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Status Update</strong></div>
                <div class="card-body">
                    <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/status" class="row g-3 align-items-end">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <div class="col-md-8">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['pending' => 'Pending', 'in_progress' => 'In Progress', 'under_review' => 'Under Review', 'completed' => 'Completed', 'on_hold' => 'On Hold', 'cancelled' => 'Cancelled'] as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($task['status'] ?? 'pending') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-repeat me-1"></i>Update Status</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Progress Update</strong></div>
                <div class="card-body">
                    <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/progress" class="row g-3 align-items-end">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <div class="col-md-8">
                            <label class="form-label">Completion Percentage</label>
                            <input type="number" name="completion_percentage" class="form-control" min="0" max="100" value="<?= $progress ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-speedometer2 me-1"></i>Update Progress</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Assigned Users</strong></div>
                <div class="card-body">
                    <?php if (!empty($assignedUsers)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($assignedUsers as $assignedUser): ?>
                                <li class="list-group-item px-0">
                                    <div class="fw-semibold"><?= htmlspecialchars(trim((string) (($assignedUser['first_name'] ?? '') . ' ' . ($assignedUser['last_name'] ?? '')))) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($assignedUser['internal_email'] ?? '')) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No users are currently assigned.</p>
                    <?php endif; ?>

                    <hr>

                    <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/assign">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <label class="form-label">Update Assignees</label>
                        <select name="assigned_to[]" class="form-select" multiple size="7">
                            <?php $selectedAssignees = array_map('intval', (array) ($task['assigned_to'] ?? [])); ?>
                            <?php foreach ($availableUsers as $availableUser): ?>
                                <?php $availableUserId = (int) ($availableUser['id'] ?? 0); ?>
                                <option value="<?= $availableUserId ?>" <?= in_array($availableUserId, $selectedAssignees, true) ? 'selected' : '' ?>><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? 'Unknown user'))) ?><?= !empty($availableUser['level_scope']) ? ' - ' . htmlspecialchars(ucfirst((string) $availableUser['level_scope'])) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline-primary mt-3"><i class="bi bi-people me-1"></i>Save Assignees</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Attachments</strong></div>
                <div class="card-body">
                    <?php if (!empty($attachments)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($attachments as $attachment): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between gap-2">
                                    <span><?= htmlspecialchars((string) ($attachment['original_name'] ?? $attachment['stored_name'] ?? 'Attachment')) ?></span>
                                    <span class="text-muted small"><?= !empty($attachment['size']) ? number_format(((int) $attachment['size']) / 1024, 1) . ' KB' : '' ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No attachments uploaded yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>Subtasks</strong></div>
                <div class="card-body">
                    <?php if (!empty($subtasks)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($subtasks as $subtask): ?>
                                <a href="/tasks/<?= (int) ($subtask['id'] ?? 0) ?>" class="list-group-item list-group-item-action px-0">
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($subtask['title'] ?? 'Untitled subtask')) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($subtask['description'] ?? '')) ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No subtasks yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Comments</strong></div>
                <div class="card-body">
                    <?php if (!empty($comments)): ?>
                        <div class="list-group list-group-flush mb-3">
                            <?php foreach ($comments as $comment): ?>
                                <div class="list-group-item px-0">
                                    <div class="fw-semibold"><?= htmlspecialchars(trim((string) (($comment['first_name'] ?? '') . ' ' . ($comment['last_name'] ?? '')))) ?></div>
                                    <div class="small text-muted mb-2"><?= htmlspecialchars((string) ($comment['created_at'] ?? '')) ?></div>
                                    <div><?= nl2br(htmlspecialchars((string) ($comment['comment'] ?? ''))) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No comments yet.</p>
                    <?php endif; ?>

                    <form method="POST" action="/tasks/<?= (int) ($task['id'] ?? 0) ?>/comments" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label">Add Comment</label>
                            <textarea name="comment" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comment Attachments</label>
                            <input type="file" name="comment_attachments[]" class="form-control" multiple>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="is_internal">
                            <label class="form-check-label" for="is_internal">Internal note</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-chat-square-text me-1"></i>Post Comment</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Activity Timeline</strong></div>
                <div class="card-body">
                    <?php if (!empty($history)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($history as $entry): ?>
                                <div class="list-group-item px-0">
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($entry['description'] ?? $entry['action'] ?? 'Activity')) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars(trim((string) (($entry['first_name'] ?? '') . ' ' . ($entry['last_name'] ?? '')))) ?><?= !empty($entry['created_at']) ? ' · ' . htmlspecialchars((string) $entry['created_at']) : '' ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No activity has been logged yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>Available Assignees In Scope</strong></div>
                <div class="card-body">
                    <?php if (!empty($availableUsers)): ?>
                        <div class="small text-muted mb-2">Users visible from <?= htmlspecialchars((string) ($userScope['scope_name'] ?? 'this scope')) ?>.</div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($availableUsers as $availableUser): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between gap-2">
                                    <span><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? 'Unknown user'))) ?></span>
                                    <span class="text-muted"><?= htmlspecialchars(ucfirst((string) ($availableUser['level_scope'] ?? ''))) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No visible assignees were resolved for this scope.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>