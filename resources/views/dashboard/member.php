<?php
/**
 * Member Dashboard View
 * For regular members (non-executive users)
 */

$pageTitle = 'Member Dashboard';
?>

<div class="container-fluid mt-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-person-circle me-2"></i>
                        Welcome back, <?= htmlspecialchars($user['first_name'] ?? 'Member') ?>!
                    </h2>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt me-2"></i>
                        <?php if (!empty($user['gurmu_name'])): ?>
                            <?= htmlspecialchars($user['gurmu_name']) ?>
                        <?php else: ?>
                            Member Dashboard
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-calendar-event fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['upcoming_events'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">Upcoming Events</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-success bg-opacity-10 text-success">
                                <i class="bi bi-camera-video fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['my_meetings'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">My Meetings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-check-square fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['my_tasks'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">My Tasks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-heart fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['my_donations'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">My Donations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-info bg-opacity-10 text-info">
                                <i class="bi bi-mortarboard fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['active_courses'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">My Courses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-easel fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= number_format($stats['training_sessions'] ?? 0) ?></h3>
                            <p class="text-muted mb-0 small">Training Sessions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- My Tasks Section -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-check-square me-2 text-primary"></i>
                        My Tasks
                    </h5>
                    <a href="/tasks" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($myTasks) && count($myTasks) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($myTasks, 0, 5) as $task): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="/tasks/<?= $task['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($task['title']) ?>
                                                </a>
                                            </h6>
                                            <p class="mb-1 text-muted small"><?= htmlspecialchars(substr($task['description'] ?? '', 0, 100)) ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                Due: <?= date('M d, Y', strtotime($task['due_date'])) ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($task['priority'] ?? 'normal') ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No tasks assigned yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Meetings Section -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-camera-video me-2 text-success"></i>
                        Upcoming Meetings
                    </h5>
                    <a href="/meetings" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingMeetings) && count($upcomingMeetings) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($upcomingMeetings, 0, 5) as $meeting): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="/meetings/<?= $meeting['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($meeting['title']) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('M d, Y h:i A', strtotime($meeting['start_datetime'])) ?>
                                            </small>
                                            <?php if ($meeting['is_virtual']): ?>
                                                <span class="badge bg-info ms-2">
                                                    <i class="bi bi-camera-video me-1"></i>Virtual
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No upcoming meetings</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event me-2 text-warning"></i>
                        Community Events
                    </h5>
                    <a href="/events" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentEvents) && count($recentEvents) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($recentEvents, 0, 5) as $event): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="/events/<?= $event['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($event['title']) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= date('M d, Y', strtotime($event['start_datetime'])) ?>
                                            </small>
                                            <span class="badge bg-<?= $event['event_type'] === 'cultural' ? 'primary' : 'secondary' ?> ms-2">
                                                <?= ucfirst($event['event_type'] ?? 'general') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No upcoming events</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-mortarboard me-2 text-info"></i>
                        Courses and Training
                    </h5>
                    <a href="/courses" class="btn btn-sm btn-outline-info">View Learning</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($memberCourses) || !empty($trainingSessions)): ?>
                        <?php if (!empty($memberCourses)): ?>
                            <h6 class="text-muted text-uppercase small mb-3">My Courses</h6>
                            <div class="list-group list-group-flush mb-4">
                                <?php foreach ($memberCourses as $course): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between align-items-start gap-3">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold"><?= htmlspecialchars($course['title'] ?? 'Course') ?></div>
                                                <?php if (!empty($course['instructor_name'])): ?>
                                                    <div class="small text-muted"><?= htmlspecialchars($course['instructor_name']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-info-subtle text-info-emphasis border">
                                                <?= htmlspecialchars(ucfirst($course['enrollment_status'] ?? 'active')) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($trainingSessions)): ?>
                            <h6 class="text-muted text-uppercase small mb-3">Upcoming Training Sessions</h6>
                            <div class="list-group list-group-flush">
                                <?php foreach ($trainingSessions as $session): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between align-items-start gap-3">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold"><?= htmlspecialchars($session['title'] ?? 'Training Session') ?></div>
                                                <div class="small text-muted">
                                                    <?= date('M d, Y h:i A', strtotime($session['start_datetime'])) ?>
                                                </div>
                                            </div>
                                            <a href="/meetings/<?= $session['id'] ?>" class="btn btn-sm btn-outline-secondary">Open</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-mortarboard text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No courses or training sessions are assigned yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- My Gurmu Information -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2 text-info"></i>
                        My Gurmu
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($gurmuInfo)): ?>
                        <div class="mb-3">
                            <h6><?= htmlspecialchars($gurmuInfo['name'] ?? 'N/A') ?></h6>
                            <?php if (!empty($gurmuInfo['description'])): ?>
                                <p class="text-muted small"><?= htmlspecialchars($gurmuInfo['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-0"><?= number_format($gurmuInfo['member_count'] ?? 0) ?></h4>
                                    <small class="text-muted">Members</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-0"><?= number_format($gurmuInfo['meeting_count'] ?? 0) ?></h4>
                                    <small class="text-muted">Meetings</small>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($gurmuInfo['contact_email']) || !empty($gurmuInfo['contact_phone'])): ?>
                            <hr>
                            <div class="mt-3">
                                <h6 class="text-muted small mb-2">Contact Information</h6>
                                <?php if (!empty($gurmuInfo['contact_email'])): ?>
                                    <p class="mb-1">
                                        <i class="bi bi-envelope me-2"></i>
                                        <a href="mailto:<?= htmlspecialchars($gurmuInfo['contact_email']) ?>">
                                            <?= htmlspecialchars($gurmuInfo['contact_email']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($gurmuInfo['contact_phone'])): ?>
                                    <p class="mb-1">
                                        <i class="bi bi-telephone me-2"></i>
                                        <a href="tel:<?= htmlspecialchars($gurmuInfo['contact_phone']) ?>">
                                            <?= htmlspecialchars($gurmuInfo['contact_phone']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No Gurmu information available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
