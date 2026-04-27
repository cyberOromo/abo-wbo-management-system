<?php
$title = $title ?? 'Create Task';
$userScope = $userScope ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-plus-circle me-2"></i>Create Task</h1>
            <p class="text-muted mb-0">Create a standalone task within <?= htmlspecialchars((string) ($userScope['scope_name'] ?? 'your current hierarchy scope')) ?>.</p>
        </div>
        <a href="/tasks" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Tasks</a>
    </div>

    <?php require __DIR__ . '/_form.php'; ?>
</div>