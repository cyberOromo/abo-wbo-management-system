<?php
/**
 * Tasks Index View Template
 * Comprehensive task management with Kanban board and list views
 */

// Page metadata
$pageTitle = __('tasks.title');
$pageDescription = __('tasks.description');
$bodyClass = 'tasks-page';

// Task data
$tasks = $tasks ?? [];
$taskStats = $taskStats ?? [];
$kanbanData = $kanbanData ?? [];
$filters = $filters ?? [];

// User permissions
$canCreateTasks = $permissions['can_create_tasks'] ?? false;
$canAssignTasks = $permissions['can_assign_tasks'] ?? false;
$canManageTasks = $permissions['can_manage_tasks'] ?? false;

// Task statuses for Kanban
$taskStatuses = [
    'pending' => ['name' => __('tasks.pending'), 'color' => 'secondary'],
    'in_progress' => ['name' => __('tasks.in_progress'), 'color' => 'primary'],
    'under_review' => ['name' => __('tasks.under_review'), 'color' => 'warning'],
    'completed' => ['name' => __('tasks.completed'), 'color' => 'success'],
    'on_hold' => ['name' => __('tasks.on_hold'), 'color' => 'info'],
    'cancelled' => ['name' => __('tasks.cancelled'), 'color' => 'danger']
];

// Priority colors
$priorityColors = [
    'low' => 'success',
    'medium' => 'warning',
    'high' => 'danger',
    'urgent' => 'dark'
];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('tasks.task_management') ?></h1>
        <p class="text-muted mb-0"><?= __('tasks.manage_organization_tasks') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canCreateTasks): ?>
            <a href="/tasks/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?= __('tasks.create_task') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> <?= __('tasks.export') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/tasks/export?format=excel">
                    <i class="bi bi-file-earmark-excel me-2"></i><?= __('tasks.export_excel') ?>
                </a></li>
                <li><a class="dropdown-item" href="/tasks/export?format=pdf">
                    <i class="bi bi-file-earmark-pdf me-2"></i><?= __('tasks.export_pdf') ?>
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Task Statistics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('tasks.total_tasks') ?></h5>
                        <h2 class="mb-0"><?= number_format($taskStats['total'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-list-task fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('tasks.in_progress') ?></h5>
                        <h2 class="mb-0"><?= number_format($taskStats['in_progress'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-clock fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('tasks.completed') ?></h5>
                        <h2 class="mb-0"><?= number_format($taskStats['completed'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-check-circle fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('tasks.overdue') ?></h5>
                        <h2 class="mb-0"><?= number_format($taskStats['overdue'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-exclamation-triangle fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Toggle and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- View Toggle -->
            <div class="btn-group view-toggle" role="group">
                <input type="radio" class="btn-check" name="view-mode" id="kanban-view" checked>
                <label class="btn btn-outline-primary" for="kanban-view">
                    <i class="bi bi-kanban"></i> <?= __('tasks.kanban_view') ?>
                </label>
                <input type="radio" class="btn-check" name="view-mode" id="list-view">
                <label class="btn btn-outline-primary" for="list-view">
                    <i class="bi bi-list-ul"></i> <?= __('tasks.list_view') ?>
                </label>
            </div>
            
            <!-- Quick Filters -->
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel"></i> <?= __('tasks.filters') ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                        <form class="filters-form">
                            <div class="mb-3">
                                <label class="form-label"><?= __('tasks.search') ?></label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('tasks.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value=""><?= __('tasks.all_statuses') ?></option>
                                        <?php foreach ($taskStatuses as $status => $config): ?>
                                            <option value="<?= $status ?>" 
                                                    <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('tasks.priority') ?></label>
                                    <select class="form-select" name="priority">
                                        <option value=""><?= __('tasks.all_priorities') ?></option>
                                        <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>
                                            <?= __('tasks.low') ?>
                                        </option>
                                        <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>
                                            <?= __('tasks.medium') ?>
                                        </option>
                                        <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>
                                            <?= __('tasks.high') ?>
                                        </option>
                                        <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>
                                            <?= __('tasks.urgent') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('tasks.assigned_to') ?></label>
                                    <select class="form-select" name="assigned_to">
                                        <option value=""><?= __('tasks.all_assignees') ?></option>
                                        <option value="me" <?= ($filters['assigned_to'] ?? '') === 'me' ? 'selected' : '' ?>>
                                            <?= __('tasks.assigned_to_me') ?>
                                        </option>
                                        <option value="unassigned" <?= ($filters['assigned_to'] ?? '') === 'unassigned' ? 'selected' : '' ?>>
                                            <?= __('tasks.unassigned') ?>
                                        </option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('tasks.due_date') ?></label>
                                    <select class="form-select" name="due_date">
                                        <option value=""><?= __('tasks.any_time') ?></option>
                                        <option value="overdue" <?= ($filters['due_date'] ?? '') === 'overdue' ? 'selected' : '' ?>>
                                            <?= __('tasks.overdue') ?>
                                        </option>
                                        <option value="today" <?= ($filters['due_date'] ?? '') === 'today' ? 'selected' : '' ?>>
                                            <?= __('tasks.due_today') ?>
                                        </option>
                                        <option value="this_week" <?= ($filters['due_date'] ?? '') === 'this_week' ? 'selected' : '' ?>>
                                            <?= __('tasks.due_this_week') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <?= __('tasks.apply_filters') ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-filters">
                                    <?= __('tasks.clear') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kanban View -->
<div id="kanban-view-content">
    <div class="kanban-board">
        <div class="row g-3">
            <?php foreach ($taskStatuses as $status => $config): ?>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="kanban-column" data-status="<?= $status ?>">
                        <div class="kanban-header bg-<?= $config['color'] ?> text-white">
                            <h6 class="mb-0">
                                <?= $config['name'] ?>
                                <span class="badge bg-white text-<?= $config['color'] ?> ms-2">
                                    <?= count($kanbanData[$status] ?? []) ?>
                                </span>
                            </h6>
                        </div>
                        <div class="kanban-body" data-status="<?= $status ?>">
                            <?php foreach (($kanbanData[$status] ?? []) as $task): ?>
                                <div class="kanban-task" data-task-id="<?= $task['id'] ?>" draggable="true">
                                    <div class="task-header d-flex justify-content-between align-items-start">
                                        <div class="task-priority">
                                            <span class="badge bg-<?= $priorityColors[$task['priority']] ?> badge-sm">
                                                <?= __(ucfirst($task['priority'])) ?>
                                            </span>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i><?= __('tasks.view') ?>
                                                </a></li>
                                                <?php if ($canManageTasks): ?>
                                                    <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>/edit">
                                                        <i class="bi bi-pencil me-2"></i><?= __('tasks.edit') ?>
                                                    </a></li>
                                                    <?php if ($canAssignTasks): ?>
                                                        <li><a class="dropdown-item assign-task" href="#" 
                                                               data-task-id="<?= $task['id'] ?>">
                                                            <i class="bi bi-person-plus me-2"></i><?= __('tasks.assign') ?>
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger delete-task" href="#" 
                                                           data-task-id="<?= $task['id'] ?>">
                                                        <i class="bi bi-trash me-2"></i><?= __('tasks.delete') ?>
                                                    </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="task-content">
                                        <h6 class="task-title">
                                            <a href="/tasks/<?= $task['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($task['title']) ?>
                                            </a>
                                        </h6>
                                        
                                        <?php if (!empty($task['description'])): ?>
                                            <p class="task-description text-muted small">
                                                <?= htmlspecialchars(substr($task['description'], 0, 100)) ?>
                                                <?= strlen($task['description']) > 100 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="task-meta">
                                            <?php if (!empty($task['due_date'])): ?>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?= format_date($task['due_date']) ?>
                                                    <?php if (strtotime($task['due_date']) < time()): ?>
                                                        <span class="text-danger">(<?= __('tasks.overdue') ?>)</span>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($task['assigned_users'])): ?>
                                                <div class="task-assignees mt-2">
                                                    <?php foreach (array_slice($task['assigned_users'], 0, 3) as $user): ?>
                                                        <img src="<?= $user['avatar'] ?? '/assets/images/default-avatar.svg' ?>" 
                                                             alt="<?= htmlspecialchars($user['name']) ?>"
                                                             class="rounded-circle me-1" width="24" height="24"
                                                             title="<?= htmlspecialchars($user['name']) ?>">
                                                    <?php endforeach; ?>
                                                    <?php if (count($task['assigned_users']) > 3): ?>
                                                        <span class="badge bg-secondary">+<?= count($task['assigned_users']) - 3 ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($task['progress_percentage'])): ?>
                                        <div class="task-progress mt-2">
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar" style="width: <?= $task['progress_percentage'] ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $task['progress_percentage'] ?>% <?= __('tasks.complete') ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($kanbanData[$status] ?? [])): ?>
                                <div class="empty-column text-center text-muted py-4">
                                    <i class="bi bi-inbox display-6"></i>
                                    <p class="small mt-2"><?= __('tasks.no_tasks_in_status') ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- List View -->
<div id="list-view-content" style="display: none;">
    <div class="card">
        <div class="card-body">
            <?php if (empty($tasks)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-list-task display-1 text-muted"></i>
                    <h4 class="text-muted mt-3"><?= __('tasks.no_tasks_found') ?></h4>
                    <p class="text-muted"><?= __('tasks.no_tasks_description') ?></p>
                    <?php if ($canCreateTasks): ?>
                        <a href="/tasks/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> <?= __('tasks.create_first_task') ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover tasks-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-tasks">
                                    </div>
                                </th>
                                <th><?= __('tasks.task') ?></th>
                                <th><?= __('tasks.status') ?></th>
                                <th><?= __('tasks.priority') ?></th>
                                <th><?= __('tasks.assigned_to') ?></th>
                                <th><?= __('tasks.due_date') ?></th>
                                <th><?= __('tasks.progress') ?></th>
                                <th style="width: 120px;"><?= __('tasks.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr class="task-row" data-task-id="<?= $task['id'] ?>">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input task-checkbox" type="checkbox" 
                                                   value="<?= $task['id'] ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="/tasks/<?= $task['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($task['title']) ?>
                                                </a>
                                            </h6>
                                            <?php if (!empty($task['description'])): ?>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars(substr($task['description'], 0, 100)) ?>
                                                    <?= strlen($task['description']) > 100 ? '...' : '' ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $taskStatuses[$task['status']]['color'] ?>">
                                            <?= $taskStatuses[$task['status']]['name'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $priorityColors[$task['priority']] ?>">
                                            <?= __(ucfirst($task['priority'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($task['assigned_users'])): ?>
                                            <div class="d-flex align-items-center">
                                                <?php foreach (array_slice($task['assigned_users'], 0, 2) as $user): ?>
                                                    <img src="<?= $user['avatar'] ?? '/assets/images/default-avatar.svg' ?>" 
                                                         alt="<?= htmlspecialchars($user['name']) ?>"
                                                         class="rounded-circle me-1" width="24" height="24"
                                                         title="<?= htmlspecialchars($user['name']) ?>">
                                                <?php endforeach; ?>
                                                <?php if (count($task['assigned_users']) > 2): ?>
                                                    <span class="badge bg-secondary">+<?= count($task['assigned_users']) - 2 ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted"><?= __('tasks.unassigned') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($task['due_date'])): ?>
                                            <span class="<?= strtotime($task['due_date']) < time() ? 'text-danger' : '' ?>">
                                                <?= format_date($task['due_date']) ?>
                                                <?php if (strtotime($task['due_date']) < time()): ?>
                                                    <br><small class="text-danger"><?= __('tasks.overdue') ?></small>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($task['progress_percentage'])): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                    <div class="progress-bar" style="width: <?= $task['progress_percentage'] ?>%"></div>
                                                </div>
                                                <small><?= $task['progress_percentage'] ?>%</small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">0%</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i><?= __('tasks.view') ?>
                                                </a></li>
                                                <?php if ($canManageTasks): ?>
                                                    <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>/edit">
                                                        <i class="bi bi-pencil me-2"></i><?= __('tasks.edit') ?>
                                                    </a></li>
                                                    <?php if ($canAssignTasks): ?>
                                                        <li><a class="dropdown-item assign-task" href="#" 
                                                               data-task-id="<?= $task['id'] ?>">
                                                            <i class="bi bi-person-plus me-2"></i><?= __('tasks.assign') ?>
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger delete-task" href="#" 
                                                           data-task-id="<?= $task['id'] ?>">
                                                        <i class="bi bi-trash me-2"></i><?= __('tasks.delete') ?>
                                                    </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Task Assignment Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('tasks.assign_task') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignTaskForm">
                    <input type="hidden" id="assign_task_id" name="task_id">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('tasks.select_assignees') ?></label>
                        <select class="form-select" name="assignees[]" multiple>
                            <!-- Options will be populated via JavaScript -->
                        </select>
                        <div class="form-text"><?= __('tasks.hold_ctrl_multiple') ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('tasks.assignment_note') ?></label>
                        <textarea class="form-control" name="assignment_note" rows="3" 
                                  placeholder="<?= __('tasks.optional_note') ?>"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= __('common.cancel') ?>
                </button>
                <button type="button" class="btn btn-primary" id="confirmAssignment">
                    <?= __('tasks.assign') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Kanban Styles -->
<style>
.stats-card {
    border: none;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.kanban-board {
    min-height: 600px;
}

.kanban-column {
    background: #f8f9fa;
    border-radius: 8px;
    height: 600px;
    display: flex;
    flex-direction: column;
}

.kanban-header {
    padding: 1rem;
    border-radius: 8px 8px 0 0;
}

.kanban-body {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    min-height: 0;
}

.kanban-task {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    border: 1px solid #dee2e6;
    cursor: move;
    transition: all 0.2s ease;
}

.kanban-task:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.kanban-task.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.kanban-body.drag-over {
    background-color: rgba(0, 123, 255, 0.1);
    border: 2px dashed #007bff;
}

.task-title {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.task-description {
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.task-assignees img {
    border: 2px solid white;
    margin-left: -8px;
}

.task-assignees img:first-child {
    margin-left: 0;
}

.task-progress .progress {
    height: 4px;
}

.empty-column {
    height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.view-toggle .btn {
    border-radius: 0;
}

.view-toggle .btn:first-child {
    border-radius: 0.375rem 0 0 0.375rem;
}

.view-toggle .btn:last-child {
    border-radius: 0 0.375rem 0.375rem 0;
}

.filters-form .form-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
    .kanban-column {
        height: auto;
        min-height: 300px;
        margin-bottom: 1rem;
    }
}
</style>

<!-- Tasks JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switching
    const kanbanView = document.getElementById('kanban-view');
    const listView = document.getElementById('list-view');
    const kanbanContent = document.getElementById('kanban-view-content');
    const listContent = document.getElementById('list-view-content');
    
    kanbanView.addEventListener('change', function() {
        if (this.checked) {
            kanbanContent.style.display = 'block';
            listContent.style.display = 'none';
        }
    });
    
    listView.addEventListener('change', function() {
        if (this.checked) {
            kanbanContent.style.display = 'none';
            listContent.style.display = 'block';
        }
    });
    
    // Drag and drop for Kanban
    let draggedTask = null;
    
    document.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('kanban-task')) {
            draggedTask = e.target;
            e.target.classList.add('dragging');
        }
    });
    
    document.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('kanban-task')) {
            e.target.classList.remove('dragging');
            draggedTask = null;
        }
    });
    
    document.addEventListener('dragover', function(e) {
        e.preventDefault();
        const kanbanBody = e.target.closest('.kanban-body');
        if (kanbanBody) {
            kanbanBody.classList.add('drag-over');
        }
    });
    
    document.addEventListener('dragleave', function(e) {
        const kanbanBody = e.target.closest('.kanban-body');
        if (kanbanBody && !kanbanBody.contains(e.relatedTarget)) {
            kanbanBody.classList.remove('drag-over');
        }
    });
    
    document.addEventListener('drop', function(e) {
        e.preventDefault();
        const kanbanBody = e.target.closest('.kanban-body');
        
        if (kanbanBody && draggedTask) {
            kanbanBody.classList.remove('drag-over');
            
            const newStatus = kanbanBody.dataset.status;
            const taskId = draggedTask.dataset.taskId;
            
            // Move task visually
            kanbanBody.appendChild(draggedTask);
            
            // Update task status via API
            updateTaskStatus(taskId, newStatus);
        }
    });
    
    // Filter form handling
    document.querySelector('.filters-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/tasks?' + params.toString();
    });
    
    document.querySelector('.clear-filters').addEventListener('click', function() {
        window.location.href = '/tasks';
    });
    
    // Task assignment
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('assign-task') || e.target.closest('.assign-task')) {
            e.preventDefault();
            const taskId = e.target.dataset.taskId || e.target.closest('.assign-task').dataset.taskId;
            openAssignmentModal(taskId);
        }
        
        if (e.target.classList.contains('delete-task') || e.target.closest('.delete-task')) {
            e.preventDefault();
            const taskId = e.target.dataset.taskId || e.target.closest('.delete-task').dataset.taskId;
            deleteTask(taskId);
        }
    });
    
    // Assignment modal
    document.getElementById('confirmAssignment').addEventListener('click', function() {
        const form = document.getElementById('assignTaskForm');
        const formData = new FormData(form);
        
        fetch('/api/tasks/assign', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '<?= __('tasks.assignment_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('tasks.assignment_error') ?>');
        });
    });
    
    // Functions
    function updateTaskStatus(taskId, status) {
        fetch(`/api/tasks/${taskId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert the visual change
                location.reload();
                alert(data.message || '<?= __('tasks.status_update_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            location.reload();
        });
    }
    
    function openAssignmentModal(taskId) {
        document.getElementById('assign_task_id').value = taskId;
        
        // Load available users
        fetch('/api/users/available-for-assignment')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.querySelector('[name="assignees[]"]');
                    select.innerHTML = '';
                    
                    data.users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        select.appendChild(option);
                    });
                }
            });
        
        const modal = new bootstrap.Modal(document.getElementById('assignTaskModal'));
        modal.show();
    }
    
    function deleteTask(taskId) {
        if (confirm('<?= __('tasks.confirm_delete') ?>')) {
            fetch(`/api/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?= __('tasks.delete_failed') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('tasks.delete_error') ?>');
            });
        }
    }
    
    // Real-time updates (if WebSocket available)
    if (typeof io !== 'undefined') {
        const socket = io();
        
        socket.on('task_updated', function(data) {
            // Handle real-time task updates
            if (data.task_id) {
                // Update the specific task in the UI
                updateTaskInUI(data);
            }
        });
    }
});
</script>