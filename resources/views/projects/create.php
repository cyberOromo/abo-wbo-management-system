<?php
$title = $title ?? 'Create Project';
$scope = $scope ?? [];
$project = $project ?? [];
$availableUsers = $availableUsers ?? [];
$selectedTeamUserIds = $selectedTeamUserIds ?? [];
$formAction = '/projects';
$submitLabel = 'Create Project';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-plus-circle me-2"></i>Create Project</h1>
            <p class="text-muted mb-0">New Projectoota record for <?= htmlspecialchars($scope['scope_name'] ?? 'your current hierarchy scope') ?>.</p>
        </div>
        <a href="/projects" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Projects</a>
    </div>

    <?php require __DIR__ . '/_form.php'; ?>
</div>