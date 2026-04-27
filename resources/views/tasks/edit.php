<?php
$title = $title ?? 'Edit Task';
$task = $task ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-pencil-square me-2"></i>Edit Task</h1>
            <p class="text-muted mb-0">Update execution details for <?= htmlspecialchars((string) ($task['title'] ?? 'this task')) ?>.</p>
        </div>
        <a href="/tasks/<?= (int) ($task['id'] ?? 0) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Task</a>
    </div>

    <?php require __DIR__ . '/_form.php'; ?>
</div>