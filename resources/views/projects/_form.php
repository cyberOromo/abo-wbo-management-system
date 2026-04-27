<?php
use App\Services\AttachmentUploadService;

$project = $project ?? [];
$scope = $scope ?? [];
$availableUsers = $availableUsers ?? [];
$selectedTeamUserIds = $selectedTeamUserIds ?? [];
$formAction = $formAction ?? '/projects';
$submitLabel = $submitLabel ?? 'Save Project';
$ownerUserId = (int) ($project['owner_user_id'] ?? 0);
$attachments = $project['attachments'] ?? [];
if (!is_array($attachments)) {
    $decodedAttachments = json_decode((string) $attachments, true);
    $attachments = is_array($decodedAttachments) ? $decodedAttachments : [];
}
?>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= htmlspecialchars($formAction) ?>" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Project Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars((string) ($project['title'] ?? '')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Project Code</label>
                    <input type="text" name="project_code" class="form-control" value="<?= htmlspecialchars((string) ($project['project_code'] ?? '')) ?>" placeholder="Optional auto-generate">
                </div>
                <div class="col-12">
                    <label class="form-label">Summary</label>
                    <input type="text" name="summary" class="form-control" maxlength="500" value="<?= htmlspecialchars((string) ($project['summary'] ?? '')) ?>" placeholder="One-line portfolio summary">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Business case, scope, and delivery intent"><?= htmlspecialchars((string) ($project['description'] ?? '')) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['proposed' => 'Proposed', 'active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'archived' => 'Archived'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($project['status'] ?? 'proposed') === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <?php foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($project['priority'] ?? 'medium') === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Project Type</label>
                    <select name="project_type" class="form-select">
                        <?php foreach (['initiative' => 'Initiative', 'program' => 'Program', 'campaign' => 'Campaign', 'improvement' => 'Improvement'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($project['project_type'] ?? 'initiative') === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars((string) ($project['start_date'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Target Date</label>
                    <input type="date" name="target_date" class="form-control" value="<?= htmlspecialchars((string) ($project['target_date'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Completion %</label>
                    <input type="number" name="completion_percentage" class="form-control" value="<?= (int) ($project['completion_percentage'] ?? 0) ?>" min="0" max="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Project Lead</label>
                    <select name="owner_user_id" class="form-select">
                        <?php foreach ($availableUsers as $availableUser): ?>
                            <?php $userId = (int) ($availableUser['id'] ?? 0); ?>
                            <option value="<?= $userId ?>" <?= $ownerUserId === $userId ? 'selected' : '' ?>><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? ''))) ?><?= !empty($availableUser['level_scope']) ? ' - ' . htmlspecialchars(ucfirst((string) $availableUser['level_scope'])) : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Budget Amount</label>
                    <input type="number" name="budget_amount" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars((string) ($project['budget_amount'] ?? '')) ?>" placeholder="Optional budget">
                </div>
                <div class="col-12">
                    <label class="form-label">Project Team</label>
                    <select name="team_user_ids[]" class="form-select" multiple size="7">
                        <?php foreach ($availableUsers as $availableUser): ?>
                            <?php $userId = (int) ($availableUser['id'] ?? 0); ?>
                            <option value="<?= $userId ?>" <?= in_array($userId, $selectedTeamUserIds, true) ? 'selected' : '' ?>><?= htmlspecialchars(trim((string) ($availableUser['full_name'] ?? ''))) ?><?= !empty($availableUser['level_scope']) ? ' - ' . htmlspecialchars(ucfirst((string) $availableUser['level_scope'])) : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Assign multiple individuals across the current hierarchy chain to one project.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Success Metrics</label>
                    <input type="text" name="success_metrics" class="form-control" value="<?= htmlspecialchars((string) ($project['success_metrics'] ?? '')) ?>" placeholder="KPIs, outcomes, adoption targets">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Current Scope</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($scope['scope_name'] ?? 'Current project scope')) ?>" disabled>
                </div>
                <div class="col-12">
                    <label class="form-label">Status Notes</label>
                    <textarea name="status_notes" class="form-control" rows="3" placeholder="Risks, blockers, executive notes"><?= htmlspecialchars((string) ($project['status_notes'] ?? '')) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Delivery Notes</label>
                    <textarea name="delivery_notes" class="form-control" rows="3" placeholder="Milestones, dependencies, execution details"><?= htmlspecialchars((string) ($project['delivery_notes'] ?? '')) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Project Attachments</label>
                    <input type="file" name="attachments[]" class="form-control" multiple>
                    <div class="form-text">Upload up to <?= AttachmentUploadService::getMaxFileSizeLabel() ?> per file. Allowed formats: <?= htmlspecialchars(AttachmentUploadService::getAllowedExtensionsLabel()) ?>.</div>
                    <?php if (!empty($attachments) && !empty($project['id'])): ?>
                        <div class="mt-3">
                            <?php
                            $resource = 'projects';
                            $resourceId = (int) ($project['id'] ?? 0);
                            $contextLabel = 'Project attachment';
                            $emptyMessage = 'No project attachments uploaded yet.';
                            require dirname(__DIR__) . '/partials/attachment_list.php';
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="/projects" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($submitLabel) ?></button>
            </div>
        </form>
    </div>
</div>