<?php
$title = $title ?? 'My Tasks';
$tasks = $tasks ?? [];
$task_stats = $task_stats ?? [];
$user_scope = $user_scope ?? [];
$can_create = $can_create ?? true;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tasks text-primary"></i>
                <?php echo htmlspecialchars($title); ?>
            </h1>
            <?php if (!empty($user_scope)): ?>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($user_scope['scope_name'] ?? 'My Tasks'); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <?php if ($can_create): ?>
        <div>
            <a href="/tasks/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Task
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Task Statistics -->
    <?php if (!empty($task_stats)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tasks
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($task_stats['total'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                In Progress
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($task_stats['in_progress'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($task_stats['completed'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Overdue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($task_stats['overdue'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Task Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-center">
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-control form-control-sm">
                        <option value="">All Priorities</option>
                        <option value="low" <?php echo ($_GET['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo ($_GET['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo ($_GET['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="urgent" <?php echo ($_GET['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="due_date" class="form-control form-control-sm" 
                           value="<?php echo htmlspecialchars($_GET['due_date'] ?? ''); ?>" 
                           placeholder="Due Date">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="/tasks" class="btn btn-outline-secondary btn-sm ml-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Task List</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($tasks)): ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="tasksTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($task['title'] ?? 'Untitled'); ?></strong>
                                <?php if (!empty($task['description'])): ?>
                                <br><small class="text-muted">
                                    <?php echo htmlspecialchars(substr($task['description'], 0, 50) . '...'); ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $priority = $task['priority'] ?? 'medium';
                                $priorityClass = [
                                    'low' => 'secondary',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ][$priority] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?php echo $priorityClass; ?>">
                                    <?php echo ucfirst($priority); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $status = $task['status'] ?? 'pending';
                                $statusClass = [
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ][$status] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?php echo $statusClass; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $status)); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($task['assigned_first_name'])): ?>
                                    <?php echo htmlspecialchars($task['assigned_first_name'] . ' ' . ($task['assigned_last_name'] ?? '')); ?>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($task['due_date'])): ?>
                                    <?php 
                                    $dueDate = strtotime($task['due_date']);
                                    $isOverdue = $dueDate < time() && $status !== 'completed';
                                    ?>
                                    <span class="<?php echo $isOverdue ? 'text-danger' : ''; ?>">
                                        <?php echo date('M j, Y', $dueDate); ?>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                        <i class="fas fa-exclamation-triangle text-danger"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">No due date</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $progress = intval($task['progress'] ?? 0); ?>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-<?php echo $progress == 100 ? 'success' : 'info'; ?>" 
                                         role="progressbar" style="width: <?php echo $progress; ?>%">
                                        <?php echo $progress; ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="/tasks/<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/tasks/<?php echo $task['id']; ?>/edit" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($status !== 'completed'): ?>
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'completed')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-tasks fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-600">No tasks found</h5>
                <p class="text-muted">You don't have any tasks assigned yet.</p>
                <?php if ($can_create): ?>
                <a href="/tasks/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Task
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($tasks)): ?>
<script>
$(document).ready(function() {
    $('#tasksTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "asc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [5, 6] }
        ]
    });
});

function updateTaskStatus(taskId, status) {
    if (confirm('Are you sure you want to mark this task as ' + status + '?')) {
        fetch('/tasks/' + taskId + '/status', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update task status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error updating task status: ' + error.message);
        });
    }
}
</script>
<?php endif; ?>