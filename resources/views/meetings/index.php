<?php
$title = $title ?? 'My Meetings';
$meetings = $meetings ?? [];
$meeting_stats = $meeting_stats ?? [];
$user_scope = $user_scope ?? [];
$can_create = $can_create ?? true;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-users text-info"></i>
                <?php echo htmlspecialchars($title); ?>
            </h1>
            <?php if (!empty($user_scope)): ?>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($user_scope['scope_name'] ?? 'My Meetings'); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div>
            <?php if ($can_create): ?>
            <a href="/meetings/create" class="btn btn-success">
                <i class="fas fa-plus"></i> Schedule Meeting
            </a>
            <?php endif; ?>
            <a href="/meetings?view=calendar" class="btn btn-outline-info ml-2">
                <i class="fas fa-calendar-alt"></i> Calendar View
            </a>
        </div>
    </div>

    <!-- Meeting Statistics -->
    <?php if (!empty($meeting_stats)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Meetings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($meeting_stats['total'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Upcoming
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($meeting_stats['upcoming'] ?? 0); ?>
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
                                <?php echo number_format($meeting_stats['completed'] ?? 0); ?>
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
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                This Week
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($meeting_stats['this_week'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Meetings Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Meeting Schedule</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($meetings)): ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="meetingsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Organizer</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meetings as $meeting): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($meeting['title'] ?? 'Untitled Meeting'); ?></strong>
                                <?php if (!empty($meeting['location'])): ?>
                                <br><small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($meeting['location']); ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo ucfirst($meeting['meeting_type'] ?? 'general'); ?>
                            </td>
                            <td>
                                <?php if (!empty($meeting['start_date'])): ?>
                                    <?php 
                                    $startTime = strtotime($meeting['start_date'] . ' ' . ($meeting['start_time'] ?? ''));
                                    $isUpcoming = $startTime > time();
                                    ?>
                                    <div class="<?php echo $isUpcoming ? 'text-primary' : 'text-muted'; ?>">
                                        <?php echo date('M j, Y', $startTime); ?>
                                        <br><small><?php echo date('g:i A', $startTime); ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">TBD</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($meeting['duration_minutes'])): ?>
                                    <?php 
                                    $hours = floor($meeting['duration_minutes'] / 60);
                                    $minutes = $meeting['duration_minutes'] % 60;
                                    echo $hours > 0 ? $hours . 'h ' : '';
                                    echo $minutes > 0 ? $minutes . 'm' : '';
                                    ?>
                                <?php else: ?>
                                    <span class="text-muted">TBD</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($meeting['organizer_first_name'])): ?>
                                    <?php echo htmlspecialchars($meeting['organizer_first_name'] . ' ' . ($meeting['organizer_last_name'] ?? '')); ?>
                                <?php else: ?>
                                    <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?php echo number_format($meeting['participant_count'] ?? 0); ?> participants
                                </span>
                            </td>
                            <td>
                                <?php
                                $status = $meeting['status'] ?? 'scheduled';
                                $statusClass = [
                                    'draft' => 'secondary',
                                    'scheduled' => 'primary',
                                    'in_progress' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ][$status] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?php echo $statusClass; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $status)); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/meetings/<?php echo $meeting['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/meetings/<?php echo $meeting['id']; ?>/edit" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($status === 'scheduled' && $startTime <= time() + 1800): // 30 minutes before ?>
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="startMeeting(<?php echo $meeting['id']; ?>)">
                                    <i class="fas fa-play"></i>
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
                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-600">No meetings found</h5>
                <p class="text-muted">You don't have any meetings scheduled yet.</p>
                <?php if ($can_create): ?>
                <a href="/meetings/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Schedule Your First Meeting
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($meetings)): ?>
<script>
$(document).ready(function() {
    $('#meetingsTable').DataTable({
        "pageLength": 25,
        "order": [[ 2, "asc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [5, 7] }
        ]
    });
});

function startMeeting(meetingId) {
    if (confirm('Are you sure you want to start this meeting?')) {
        fetch('/meetings/' + meetingId + '/start', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/meetings/' + meetingId;
            } else {
                alert('Failed to start meeting: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error starting meeting: ' + error.message);
        });
    }
}
</script>
<?php endif; ?>