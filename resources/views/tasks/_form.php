<?php
$task = $task ?? [];
$userScope = $userScope ?? [];
$availableUsers = $availableUsers ?? [];
$parentTasks = $parentTasks ?? [];
$categories = $categories ?? [];
$priorities = $priorities ?? [];
$scopes = $scopes ?? [];
$formAction = $formAction ?? '/tasks';
$submitLabel = $submitLabel ?? 'Save Task';
$selectedAssignees = array_map('intval', (array) ($task['assigned_to'] ?? []));
$selectedScope = (string) ($task['level_scope'] ?? ($userScope['level_scope'] ?? ''));
?>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= htmlspecialchars($formAction) ?>" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Task Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars((string) ($task['title'] ?? '')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ([
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'under_review' => 'Under Review',
                            'completed' => 'Completed',
                            'on_hold' => 'On Hold',
                            'cancelled' => 'Cancelled',
                        ] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($task['status'] ?? 'pending') === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Execution notes, dependencies, and expected outcome"><?= htmlspecialchars((string) ($task['description'] ?? '')) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Scope</label>
                    <select name="level_scope" class="form-select">
                        <?php foreach ($scopes as $scope): ?>
                            <option value="<?= htmlspecialchars((string) $scope) ?>" <?= $selectedScope === $scope ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst((string) $scope)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Current hierarchy anchor: <?= htmlspecialchars((string) ($userScope['scope_name'] ?? 'Current scope')) ?></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <?php foreach ($priorities as $value => $label): ?>
                            <option value="<?= htmlspecialchars((string) $value) ?>" <?= ($task['priority'] ?? 'medium') === $value ? 'selected' : '' ?>><?= htmlspecialchars((string) $label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach ($categories as $value => $label): ?>
                            <option value="<?= htmlspecialchars((string) $value) ?>" <?= ($task['category'] ?? 'administrative') === $value ? 'selected' : '' ?>><?= htmlspecialchars((string) $label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parent Task</label>
                    <select name="parent_task_id" class="form-select">
                        <option value="">Top-level task</option>
                        <?php foreach ($parentTasks as $parentTask): ?>
                            <?php $parentId = (int) ($parentTask['id'] ?? 0); ?>
                            <option value="<?= $parentId ?>" <?= (int) ($task['parent_task_id'] ?? 0) === $parentId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($parentTask['title'] ?? 'Untitled task')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars((string) ($task['start_date'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars((string) ($task['due_date'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estimated Hours</label>
                    <input type="number" name="estimated_hours" class="form-control" min="0" step="1" value="<?= htmlspecialchars((string) ($task['estimated_hours'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Actual Hours</label>
                    <input type="number" name="actual_hours" class="form-control" min="0" step="1" value="<?= htmlspecialchars((string) ($task['actual_hours'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Completion %</label>
                    <input type="number" name="completion_percentage" class="form-control" min="0" max="100" value="<?= (int) ($task['completion_percentage'] ?? $task['progress_percentage'] ?? 0) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars(implode(', ', (array) ($task['tags'] ?? []))) ?>" placeholder="comma,separated,tags">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Attachments</label>
                    <input type="file" name="attachments[]" class="form-control" multiple>
                    <div class="form-text">Upload working files, screenshots, or supporting documents.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Assign Users</label>
                    <select name="assigned_to[]" class="form-select" multiple size="8">
                        <?php foreach ($availableUsers as $availableUser): ?>
                            <?php $userId = (int) ($availableUser['id'] ?? 0); ?>
                            <option value="<?= $userId ?>" <?= in_array($userId, $selectedAssignees, true) ? 'selected' : '' ?>><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? 'Unknown user'))) ?><?= !empty($availableUser['level_scope']) ? ' - ' . htmlspecialchars(ucfirst((string) $availableUser['level_scope'])) : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Assign one or more users within your visible hierarchy chain.</div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="/tasks" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($submitLabel) ?></button>
            </div>
        </form>
    </div>
</div>