<?php
$pageTitle = $title ?? 'Task Management';
$layout = 'modern';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-check-square me-2"></i>
            Task Management
        </h1>
        <p class="text-muted mb-0">Organize, assign, and track tasks across your organization</p>
    </div>
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                <i class="bi bi-plus-circle me-1"></i>
                New Task
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>
                Filter
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?filter=all">All Tasks</a></li>
                <li><a class="dropdown-item" href="?filter=pending">Pending</a></li>
                <li><a class="dropdown-item" href="?filter=in_progress">In Progress</a></li>
                <li><a class="dropdown-item" href="?filter=completed">Completed</a></li>
                <li><a class="dropdown-item" href="?filter=overdue">Overdue</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Task Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-list-task"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_tasks'] ?? 45) ?></h3>
                    <p class="text-muted mb-0">Total Tasks</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        <?= $stats['new_this_week'] ?? 8 ?> This Week
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['in_progress'] ?? 12) ?></h3>
                    <p class="text-muted mb-0">In Progress</p>
                    <small class="text-info">
                        <i class="bi bi-person"></i>
                        <?= $stats['assigned_users'] ?? 8 ?> Users
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['completed'] ?? 28) ?></h3>
                    <p class="text-muted mb-0">Completed</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        85% Success Rate
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['overdue'] ?? 3) ?></h3>
                    <p class="text-muted mb-0">Overdue</p>
                    <small class="text-danger">
                        <i class="bi bi-calendar-x"></i>
                        Need Attention
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                            <div class="action-icon bg-primary text-white mb-2">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <span class="fw-bold">Create Task</span>
                            <small class="text-muted">Add new task</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='?filter=overdue'">
                            <div class="action-icon bg-warning text-white mb-2">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <span class="fw-bold">View Overdue</span>
                            <small class="text-muted">Check urgent tasks</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='/tasks/templates'">
                            <div class="action-icon bg-success text-white mb-2">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <span class="fw-bold">Task Templates</span>
                            <small class="text-muted">Predefined tasks</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='/reports/tasks'">
                            <div class="action-icon bg-info text-white mb-2">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="fw-bold">Task Reports</span>
                            <small class="text-muted">Analytics & insights</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tasks List -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-task me-2"></i>
                    Active Tasks
                </h5>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary active" data-view="list">
                        <i class="bi bi-list"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="board">
                        <i class="bi bi-kanban"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="tasks-list-view">
                    <?php 
                    $sampleTasks = [
                        ['id' => 1, 'title' => 'Update Member Database', 'priority' => 'high', 'status' => 'in_progress', 'assignee' => 'John Doe', 'due_date' => '2025-11-20', 'progress' => 75],
                        ['id' => 2, 'title' => 'Organize Community Event', 'priority' => 'medium', 'status' => 'pending', 'assignee' => 'Jane Smith', 'due_date' => '2025-11-25', 'progress' => 0],
                        ['id' => 3, 'title' => 'Financial Report Review', 'priority' => 'high', 'status' => 'overdue', 'assignee' => 'Mike Johnson', 'due_date' => '2025-11-10', 'progress' => 45],
                        ['id' => 4, 'title' => 'Website Content Update', 'priority' => 'low', 'status' => 'completed', 'assignee' => 'Sarah Wilson', 'due_date' => '2025-11-15', 'progress' => 100],
                        ['id' => 5, 'title' => 'Meeting Preparation', 'priority' => 'medium', 'status' => 'in_progress', 'assignee' => 'David Lee', 'due_date' => '2025-11-18', 'progress' => 60]
                    ];
                    
                    foreach ($sampleTasks as $task): 
                        $priorityClass = [
                            'high' => 'danger',
                            'medium' => 'warning', 
                            'low' => 'success'
                        ][$task['priority']] ?? 'secondary';
                        
                        $statusClass = [
                            'completed' => 'success',
                            'in_progress' => 'warning',
                            'pending' => 'secondary',
                            'overdue' => 'danger'
                        ][$task['status']] ?? 'secondary';
                    ?>
                        <div class="task-item border rounded p-3 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="checkbox" id="task<?= $task['id'] ?>" <?= $task['status'] === 'completed' ? 'checked' : '' ?>>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 <?= $task['status'] === 'completed' ? 'text-decoration-line-through text-muted' : '' ?>">
                                                <?= htmlspecialchars($task['title']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>
                                                <?= htmlspecialchars($task['assignee']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge bg-<?= $priorityClass ?>"><?= ucfirst($task['priority']) ?></span>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex flex-column align-items-end">
                                        <small class="text-muted mb-1"><?= date('M j', strtotime($task['due_date'])) ?></small>
                                        <div class="progress" style="width: 60px; height: 4px;">
                                            <div class="progress-bar bg-<?= $statusClass ?>" style="width: <?= $task['progress'] ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $task['progress'] ?>%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="tasks-board-view" style="display: none;">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="text-muted">Pending</h6>
                            <div class="kanban-column">
                                <!-- Kanban cards will be rendered here -->
                                <div class="card mb-2">
                                    <div class="card-body p-2">
                                        <h6 class="card-title small">Organize Community Event</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-warning small">Medium</span>
                                            <small class="text-muted">Nov 25</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">In Progress</h6>
                            <div class="kanban-column">
                                <div class="card mb-2">
                                    <div class="card-body p-2">
                                        <h6 class="card-title small">Update Member Database</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-danger small">High</span>
                                            <small class="text-muted">Nov 20</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Review</h6>
                            <div class="kanban-column">
                                <!-- Review tasks -->
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Completed</h6>
                            <div class="kanban-column">
                                <div class="card mb-2">
                                    <div class="card-body p-2">
                                        <h6 class="card-title small text-muted">Website Content Update</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-success small">Low</span>
                                            <small class="text-muted">Nov 15</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Task Sidebar -->
    <div class="col-lg-4">
        <!-- Task Calendar -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-calendar me-2"></i>
                    Task Calendar
                </h6>
            </div>
            <div class="card-body">
                <div class="mini-calendar">
                    <!-- Simple calendar view -->
                    <div class="text-center mb-3">
                        <h6>November 2025</h6>
                    </div>
                    <div class="calendar-grid">
                        <div class="row text-center small text-muted">
                            <div class="col">S</div>
                            <div class="col">M</div>
                            <div class="col">T</div>
                            <div class="col">W</div>
                            <div class="col">T</div>
                            <div class="col">F</div>
                            <div class="col">S</div>
                        </div>
                        <?php for ($week = 0; $week < 5; $week++): ?>
                            <div class="row text-center small">
                                <?php for ($day = 0; $day < 7; $day++): 
                                    $date = $week * 7 + $day - 2; // Start from Nov 3 (example)
                                    if ($date > 0 && $date <= 30):
                                ?>
                                    <div class="col p-1">
                                        <div class="calendar-day <?= in_array($date, [10, 15, 18, 20, 25]) ? 'has-task' : '' ?>">
                                            <?= $date ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col p-1"></div>
                                <?php endif; endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Task Completed</h6>
                                <p class="text-muted mb-0 small">Website Content Update finished by Sarah Wilson</p>
                            </div>
                            <small class="text-muted">2h ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">New Assignment</h6>
                                <p class="text-muted mb-0 small">Financial Report Review assigned to Mike Johnson</p>
                            </div>
                            <small class="text-muted">4h ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Progress Update</h6>
                                <p class="text-muted mb-0 small">Member Database update is 75% complete</p>
                            </div>
                            <small class="text-muted">6h ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Create New Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTaskForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="taskTitle" class="form-label">Task Title</label>
                                <input type="text" class="form-control" id="taskTitle" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="taskPriority" class="form-label">Priority</label>
                                <select class="form-select" id="taskPriority" name="priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskAssignee" class="form-label">Assign To</label>
                                <select class="form-select" id="taskAssignee" name="assignee_id">
                                    <option value="">Select User</option>
                                    <option value="1">John Doe</option>
                                    <option value="2">Jane Smith</option>
                                    <option value="3">Mike Johnson</option>
                                    <option value="4">Sarah Wilson</option>
                                    <option value="5">David Lee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskDueDate" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="taskDueDate" name="due_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskCategory" class="form-label">Category</label>
                                <select class="form-select" id="taskCategory" name="category">
                                    <option value="">Select Category</option>
                                    <option value="administrative">Administrative</option>
                                    <option value="financial">Financial</option>
                                    <option value="event">Event Management</option>
                                    <option value="community">Community Outreach</option>
                                    <option value="technical">Technical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskTags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="taskTags" name="tags" placeholder="Enter tags separated by commas">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createTask()">
                    <i class="bi bi-check me-1"></i>
                    Create Task
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.task-item {
    transition: var(--abo-transition);
    background: var(--abo-white);
}

.task-item:hover {
    transform: translateX(4px);
    box-shadow: var(--abo-shadow-md);
}

.calendar-day {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: var(--abo-transition);
}

.calendar-day.has-task {
    background-color: var(--abo-primary);
    color: white;
    font-weight: 600;
}

.calendar-day:hover {
    background-color: var(--abo-primary-light);
    color: white;
}

.kanban-column {
    min-height: 200px;
    background-color: var(--abo-gray-50);
    border-radius: var(--abo-radius);
    padding: 0.5rem;
}

.action-icon {
    width: 3rem;
    height: 3rem;
    border-radius: var(--abo-radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const viewButtons = document.querySelectorAll('[data-view]');
    const listView = document.getElementById('tasks-list-view');
    const boardView = document.getElementById('tasks-board-view');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide views
            if (viewType === 'list') {
                listView.style.display = 'block';
                boardView.style.display = 'none';
            } else if (viewType === 'board') {
                listView.style.display = 'none';
                boardView.style.display = 'block';
            }
        });
    });
});

function createTask() {
    const form = document.getElementById('createTaskForm');
    const formData = new FormData(form);
    
    // Here you would normally send the data to the server
    console.log('Creating task with data:', Object.fromEntries(formData));
    
    // Simulate success
    alert('Task created successfully!');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('createTaskModal'));
    modal.hide();
    form.reset();
    
    // Refresh page (in real app, you'd add the task to the DOM)
    window.location.reload();
}

// Task completion handler
document.querySelectorAll('.form-check-input').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const taskItem = this.closest('.task-item');
        const taskTitle = taskItem.querySelector('h6');
        
        if (this.checked) {
            taskTitle.classList.add('text-decoration-line-through', 'text-muted');
            taskItem.style.opacity = '0.7';
        } else {
            taskTitle.classList.remove('text-decoration-line-through', 'text-muted');
            taskItem.style.opacity = '1';
        }
    });
});
</script>
