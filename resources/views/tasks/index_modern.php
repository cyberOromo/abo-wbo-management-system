<?php
$currentPage = 'tasks';
?>

<!-- Modern Tasks Management Interface -->
<style>
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .task-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .task-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
    
    .task-priority-high {
        border-left: 4px solid var(--primary-red);
    }
    
    .task-priority-normal {
        border-left: 4px solid var(--primary-green);
    }
    
    .view-toggle {
        background: white;
        border-radius: 12px;
        padding: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .user-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .progress-modern {
        height: 8px;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar-modern {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-green), var(--primary-green-light));
        border-radius: 4px;
        transition: width 0.3s ease;
    }
</style>

<div class="page-header">
    <h1 class="page-title">Tasks Management</h1>
    <p class="page-description">Efficiently manage and track all tasks with advanced hierarchy-based controls</p>
</div>

<!-- Enhanced Statistics Dashboard -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-green);"><?= $task_stats['total'] ?? 0 ?></div>
            <div class="text-muted fw-500">Total Tasks</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #3b82f6;"><?= $task_stats['in_progress'] ?? 0 ?></div>
            <div class="text-muted fw-500">In Progress</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-red);"><?= $task_stats['overdue'] ?? 0 ?></div>
            <div class="text-muted fw-500">Overdue</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #10b981;"><?= $task_stats['completed'] ?? 0 ?></div>
            <div class="text-muted fw-500">Completed</div>
        </div>
    </div>
</div>

<!-- Advanced Control Panel -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="view-toggle">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="cardView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary" for="cardView">
                            <i class="bi bi-grid-3x3-gap"></i> Cards
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="tableView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="tableView">
                            <i class="bi bi-table"></i> Table
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="kanbanView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="kanbanView">
                            <i class="bi bi-kanban"></i> Kanban
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="d-flex gap-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">📋 Pending</option>
                        <option value="in_progress">🚀 In Progress</option>
                        <option value="completed">✅ Completed</option>
                        <option value="overdue">⚠️ Overdue</option>
                    </select>
                    
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="urgent">🔥 Urgent</option>
                        <option value="high">⚡ High</option>
                        <option value="normal">📄 Normal</option>
                        <option value="low">💤 Low</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="d-flex gap-2 justify-content-end">
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showCreateModal()">
                            <i class="bi bi-plus-circle"></i> Create Task
                        </button>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/tasks/export?format=csv">📊 CSV Export</a></li>
                            <li><a class="dropdown-item" href="/tasks/export?format=pdf">📄 PDF Report</a></li>
                            <li><a class="dropdown-item" href="/tasks/export?format=excel">📈 Excel Export</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card View -->
<div id="cardViewContainer">
    <div class="row g-4" id="tasksGrid">
        <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 task-item" 
                     data-status="<?= $task['status'] ?? 'pending' ?>" 
                     data-priority="<?= $task['priority'] ?? 'normal' ?>">
                    <div class="task-card task-priority-<?= $task['priority'] ?? 'normal' ?>">
                        <!-- Task Header -->
                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start p-3">
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-<?= getStatusColor($task['status'] ?? 'pending') ?>">
                                    <?= getStatusIcon($task['status'] ?? 'pending') ?> 
                                    <?= ucfirst(str_replace('_', ' ', $task['status'] ?? 'pending')) ?>
                                </span>
                                <?php if (isset($task['priority']) && $task['priority'] !== 'normal'): ?>
                                    <span class="badge bg-outline-<?= getPriorityColor($task['priority']) ?>">
                                        <?= getPriorityIcon($task['priority']) ?> <?= ucfirst($task['priority']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>">
                                        <i class="bi bi-eye"></i> View Details
                                    </a></li>
                                    <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>/edit">
                                        <i class="bi bi-pencil"></i> Edit Task
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask(<?= $task['id'] ?>)">
                                        <i class="bi bi-trash"></i> Delete
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Task Content -->
                        <div class="card-body p-3 pt-0">
                            <h5 class="card-title mb-2 fw-600"><?= htmlspecialchars($task['title'] ?? 'Untitled Task') ?></h5>
                            <p class="card-text text-muted mb-3">
                                <?= htmlspecialchars(substr($task['description'] ?? 'No description provided', 0, 120)) ?>
                                <?= strlen($task['description'] ?? '') > 120 ? '...' : '' ?>
                            </p>
                            
                            <!-- Progress Bar -->
                            <?php if (isset($task['progress']) && $task['progress'] > 0): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted fw-500">Progress</small>
                                        <small class="fw-600"><?= $task['progress'] ?>%</small>
                                    </div>
                                    <div class="progress-modern">
                                        <div class="progress-bar-modern" style="width: <?= $task['progress'] ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Task Footer -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm">
                                        <?= substr($task['assigned_first_name'] ?? $task['first_name'] ?? 'U', 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="fw-500" style="font-size: 0.875rem;">
                                            <?= $task['assigned_first_name'] ?? $task['first_name'] ?? 'Unassigned' ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?= isset($task['created_at']) ? 'Created ' . timeAgo($task['created_at']) : '' ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (isset($task['due_date'])): ?>
                                    <div class="text-end">
                                        <div class="text-muted" style="font-size: 0.75rem;">Due Date</div>
                                        <div class="fw-500" style="font-size: 0.875rem; color: <?= isOverdue($task['due_date']) ? 'var(--primary-red)' : 'var(--gray-700)' ?>;">
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('M j', strtotime($task['due_date'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-clipboard-x" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Tasks Found</h4>
                    <p class="text-muted mb-4">Create your first task to get started with task management</p>
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showCreateModal()">
                            <i class="bi bi-plus-circle"></i> Create Your First Task
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Table View (Hidden by default) -->
<div id="tableViewContainer" style="display: none;">
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tasksTable">
                <thead class="bg-light">
                    <tr>
                        <th class="fw-600">Task Details</th>
                        <th class="fw-600">Status</th>
                        <th class="fw-600">Assignee</th>
                        <th class="fw-600">Progress</th>
                        <th class="fw-600">Due Date</th>
                        <th class="fw-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr class="task-row" 
                                data-status="<?= $task['status'] ?? 'pending' ?>" 
                                data-priority="<?= $task['priority'] ?? 'normal' ?>">
                                <td>
                                    <div>
                                        <h6 class="mb-1 fw-600"><?= htmlspecialchars($task['title'] ?? 'Untitled') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars(substr($task['description'] ?? '', 0, 80)) ?>...</small>
                                        <?php if (isset($task['priority']) && $task['priority'] !== 'normal'): ?>
                                            <span class="badge bg-<?= getPriorityColor($task['priority']) ?> ms-2">
                                                <?= getPriorityIcon($task['priority']) ?> <?= ucfirst($task['priority']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= getStatusColor($task['status'] ?? 'pending') ?>">
                                        <?= getStatusIcon($task['status'] ?? 'pending') ?> 
                                        <?= ucfirst(str_replace('_', ' ', $task['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm">
                                            <?= substr($task['assigned_first_name'] ?? 'U', 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="fw-500"><?= $task['assigned_first_name'] ?? 'Unassigned' ?> <?= $task['assigned_last_name'] ?? '' ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (isset($task['progress'])): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress-modern" style="width: 60px;">
                                                <div class="progress-bar-modern" style="width: <?= $task['progress'] ?>%"></div>
                                            </div>
                                            <small class="fw-500"><?= $task['progress'] ?>%</small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($task['due_date'])): ?>
                                        <span class="<?= isOverdue($task['due_date']) ? 'text-danger' : 'text-muted' ?>">
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('M j, Y', strtotime($task['due_date'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/tasks/<?= $task['id'] ?>" class="btn btn-outline-primary">View</a>
                                        <a href="/tasks/<?= $task['id'] ?>/edit" class="btn btn-outline-secondary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/tasks/create">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Task Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Enter descriptive task title">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Priority Level</label>
                            <select name="priority" class="form-select">
                                <option value="normal">📄 Normal</option>
                                <option value="high">⚡ High Priority</option>
                                <option value="urgent">🔥 Urgent</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Provide detailed task description, requirements, and expected outcomes..."></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Select team member...</option>
                                <!-- Populated via AJAX based on hierarchy -->
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Due Date</label>
                            <input type="date" name="due_date" class="form-control" min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label fw-500">Tags (Optional)</label>
                            <input type="text" name="tags" class="form-control" 
                                   placeholder="Enter comma-separated tags (e.g., urgent, meeting, review)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modern JavaScript for enhanced functionality
document.addEventListener('DOMContentLoaded', function() {
    // View switching functionality
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const cardContainer = document.getElementById('cardViewContainer');
    const tableContainer = document.getElementById('tableViewContainer');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'block';
            tableContainer.style.display = 'none';
        }
    });
    
    tableView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            tableContainer.style.display = 'block';
        }
    });
    
    // Advanced filtering
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    
    function applyFilters() {
        const statusValue = statusFilter.value;
        const priorityValue = priorityFilter.value;
        
        // Filter cards
        document.querySelectorAll('.task-item').forEach(item => {
            const showStatus = !statusValue || item.dataset.status === statusValue;
            const showPriority = !priorityValue || item.dataset.priority === priorityValue;
            item.style.display = showStatus && showPriority ? 'block' : 'none';
        });
        
        // Filter table rows
        document.querySelectorAll('.task-row').forEach(row => {
            const showStatus = !statusValue || row.dataset.status === statusValue;
            const showPriority = !priorityValue || row.dataset.priority === priorityValue;
            row.style.display = showStatus && showPriority ? '' : 'none';
        });
    }
    
    statusFilter.addEventListener('change', applyFilters);
    priorityFilter.addEventListener('change', applyFilters);
    
    // Smooth animations
    document.querySelectorAll('.task-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

function showCreateModal() {
    new bootstrap.Modal(document.getElementById('createTaskModal')).show();
}

function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
        fetch(`/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.ok ? location.reload() : alert('Error deleting task'))
        .catch(() => alert('Error deleting task'));
    }
}
</script>

<?php
// Helper functions for UI
function getStatusColor($status) {
    return [
        'pending' => 'warning',
        'in_progress' => 'primary',
        'completed' => 'success',
        'overdue' => 'danger'
    ][$status] ?? 'secondary';
}

function getStatusIcon($status) {
    return [
        'pending' => '📋',
        'in_progress' => '🚀', 
        'completed' => '✅',
        'overdue' => '⚠️'
    ][$status] ?? '📄';
}

function getPriorityColor($priority) {
    return [
        'urgent' => 'danger',
        'high' => 'warning',
        'normal' => 'primary',
        'low' => 'secondary'
    ][$priority] ?? 'secondary';
}

function getPriorityIcon($priority) {
    return [
        'urgent' => '🔥',
        'high' => '⚡',
        'normal' => '📄',
        'low' => '💤'
    ][$priority] ?? '📄';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 3600) return 'Just now';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    return floor($time/86400) . 'd ago';
}

function isOverdue($dueDate) {
    return strtotime($dueDate) < time();
}
?>