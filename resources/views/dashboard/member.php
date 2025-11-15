<?php
/**
 * Member Dashboard View
 * ABO-WBO Management System
 */
?>

<div class="container-fluid">
    <div class="row">
        <!-- Welcome Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Welcome, <?= htmlspecialchars($user['first_name'] ?? 'Member') ?>!</h1>
                    <p class="text-muted mb-0">
                        <?= $hierarchy_scope['name'] ?? 'ABO-WBO Community' ?> • 
                        Member Dashboard
                    </p>
                </div>
                <div>
                    <button class="btn btn-outline-primary">
                        <i class="fas fa-bell"></i>
                        Notifications
                        <span class="badge bg-danger ms-1">3</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Stats -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-tasks fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?= count($personal_tasks ?? []) ?></h4>
                    <p class="text-muted mb-0">Active Tasks</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?= count($personal_meetings ?? []) ?></h4>
                    <p class="text-muted mb-0">Upcoming Meetings</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?= $engagement_score ?? 0 ?>%</h4>
                    <p class="text-muted mb-0">Engagement Score</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?= count($donations ?? []) ?></h4>
                    <p class="text-muted mb-0">Donations Made</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activities -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($member_activities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($member_activities, 0, 5) as $activity): ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($activity['title'] ?? 'Activity') ?></h6>
                                            <p class="mb-1 text-muted"><?= htmlspecialchars($activity['description'] ?? '') ?></p>
                                            <small class="text-muted"><?= $activity['created_at'] ?? 'Recently' ?></small>
                                        </div>
                                        <span class="badge bg-primary"><?= ucfirst($activity['type'] ?? 'activity') ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-3x mb-3"></i>
                            <p>No recent activities found.</p>
                            <small>Start participating in community events to see your activities here.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/tasks" class="btn btn-outline-primary">
                            <i class="fas fa-tasks me-2"></i>
                            View My Tasks
                        </a>
                        <a href="/meetings" class="btn btn-outline-success">
                            <i class="fas fa-calendar me-2"></i>
                            My Meetings
                        </a>
                        <a href="/events" class="btn btn-outline-info">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Community Events
                        </a>
                        <a href="/donations" class="btn btn-outline-warning">
                            <i class="fas fa-heart me-2"></i>
                            Make Donation
                        </a>
                        <a href="/profile" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>
                            Update Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Community Events -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Upcoming Community Events
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($community_events)): ?>
                        <?php foreach (array_slice($community_events, 0, 3) as $event): ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($event['title'] ?? 'Community Event') ?></h6>
                                    <p class="mb-1 text-muted small"><?= $event['date'] ?? 'TBD' ?></p>
                                    <small class="text-muted"><?= htmlspecialchars($event['location'] ?? 'Community Center') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center">
                            <a href="/events" class="btn btn-sm btn-outline-primary">View All Events</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                            <p class="mb-0">No upcoming events.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Community News -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-newspaper me-2"></i>
                        Community News
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($community_news)): ?>
                        <?php foreach (array_slice($community_news, 0, 3) as $news): ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($news['title'] ?? 'Community News') ?></h6>
                                    <p class="mb-1 text-muted small"><?= substr($news['content'] ?? 'Latest community updates...', 0, 80) ?>...</p>
                                    <small class="text-muted"><?= $news['published_at'] ?? 'Recently' ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center">
                            <a href="/news" class="btn btn-sm btn-outline-info">View All News</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-newspaper fa-2x mb-2"></i>
                            <p class="mb-0">No recent news.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.list-group-item:last-child {
    border-bottom: none !important;
}
</style>