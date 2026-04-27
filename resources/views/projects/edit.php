<?php
$title = $title ?? 'Edit Project';
$scope = $scope ?? [];
$project = $project ?? [];
$availableUsers = $availableUsers ?? [];
$selectedTeamUserIds = $selectedTeamUserIds ?? [];
$formAction = '/projects/' . (int) ($project['id'] ?? 0) . '/update';
$submitLabel = 'Update Project';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-pencil-square me-2"></i>Edit Project</h1>
            <p class="text-muted mb-0">Update execution, ownership, and delivery settings for <?= htmlspecialchars($project['title'] ?? 'this project') ?>.</p>
        </div>
        <a href="/projects/<?= (int) ($project['id'] ?? 0) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Project</a>
    </div>

    <?php require __DIR__ . '/_form.php'; ?>
</div>