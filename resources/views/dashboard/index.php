<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download me-1"></i>Export
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-calendar-plus me-1"></i>
            New Event
        </button>
    </div>
</div>

<!-- Welcome Message -->
<div class="alert alert-info border-0 rounded-3 mb-4" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
    <div class="row align-items-center">
        <div class="col">
            <h4 class="alert-heading">
                <i class="bi bi-sun me-2"></i>
                Welcome back, <?= htmlspecialchars($user['first_name'] ?? 'User') ?>!
            </h4>
            <p class="mb-0">
                Here's what's happening in your ABO-WBO organization today.
            </p>
        </div>
        <div class="col-auto">
            <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.3;"></i>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card card-stats border-0 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Users</h5>
                        <span class="h2 font-weight-bold mb-0"><?= number_format($stats['total_users']) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
                    <span class="text-success mr-2">
                        <i class="bi bi-arrow-up"></i> 0.5%
                    </span>
                    <span class="text-nowrap">Since last month</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card card-stats border-0 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Positions</h5>
                        <span class="h2 font-weight-bold mb-0"><?= number_format($stats['total_positions']) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
                    <span class="text-success mr-2">
                        <i class="bi bi-arrow-up"></i> 1.2%
                    </span>
                    <span class="text-nowrap">Since last month</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card card-stats border-0 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Active Tasks</h5>
                        <span class="h2 font-weight-bold mb-0"><?= number_format($stats['total_tasks']) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="bi bi-check-square-fill"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
                    <span class="text-danger mr-2">
                        <i class="bi bi-arrow-down"></i> 2.1%
                    </span>
                    <span class="text-nowrap">Since last month</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card card-stats border-0 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Meetings</h5>
                        <span class="h2 font-weight-bold mb-0"><?= number_format($stats['total_meetings']) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="bi bi-camera-video-fill"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
                    <span class="text-success mr-2">
                        <i class="bi bi-arrow-up"></i> 0.8%
                    </span>
                    <span class="text-nowrap">Since last month</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card card-stats border-0 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Events</h5>
                        <span class="h2 font-weight-bold mb-0"><?= number_format($stats['total_events']) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
                    <span class="text-success mr-2">
                        <i class="bi bi-arrow-up"></i> 3.1%
                    </span>
                    <span class="text-nowrap">Since last month</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card card-stats border-0 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Donations</h5>
                        <span class="h2 font-weight-bold mb-0">$<?= number_format($stats['total_donations']) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted text-sm">
                    <span class="text-success mr-2">
                        <i class="bi bi-arrow-up"></i> 5.2%
                    </span>
                    <span class="text-nowrap">Since last month</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Recent Activity -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 h-100">
            <div class="card-header bg-transparent border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted ls-1 mb-1">Recent Activity</h6>
                        <h2 class="mb-0">Latest Updates</h2>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-info">
                            <span class="badge bg-success rounded-pill">New</span>
                        </div>
                        <div class="timeline-marker">
                            <i class="bi bi-person-plus text-success"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">System initialized</h6>
                            <p class="timeline-text">ABO-WBO Management System has been successfully set up with the initial database structure.</p>
                            <span class="timeline-date">Just now</span>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-info">
                            <span class="badge bg-primary rounded-pill">Admin</span>
                        </div>
                        <div class="timeline-marker">
                            <i class="bi bi-shield-check text-primary"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Admin account created</h6>
                            <p class="timeline-text">Administrator account has been created and is ready for use.</p>
                            <span class="timeline-date">Just now</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 h-100">
            <div class="card-header bg-transparent border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted ls-1 mb-1">Quick Actions</h6>
                        <h2 class="mb-0">Get Started</h2>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="/admin/user-leader-registration" class="list-group-item list-group-item-action border-0 px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-sm bg-primary text-white rounded-circle">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                            </div>
                            <div class="col ml-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Add New User</h6>
                                        <p class="text-sm text-muted mb-0">Invite team members to join</p>
                                    </div>
                                    <i class="bi bi-arrow-right text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                    
                    <a href="/hierarchy/create" class="list-group-item list-group-item-action border-0 px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-sm bg-success text-white rounded-circle">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                            </div>
                            <div class="col ml-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Setup Organization Structure</h6>
                                        <p class="text-sm text-muted mb-0">Create regions and districts</p>
                                    </div>
                                    <i class="bi bi-arrow-right text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                    
                    <a href="/positions/create" class="list-group-item list-group-item-action border-0 px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-sm bg-warning text-white rounded-circle">
                                    <i class="bi bi-briefcase"></i>
                                </div>
                            </div>
                            <div class="col ml-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Define Positions</h6>
                                        <p class="text-sm text-muted mb-0">Create leadership roles</p>
                                    </div>
                                    <i class="bi bi-arrow-right text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                    
                    <a href="/meetings/create" class="list-group-item list-group-item-action border-0 px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-sm bg-info text-white rounded-circle">
                                    <i class="bi bi-camera-video"></i>
                                </div>
                            </div>
                            <div class="col ml-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Schedule Meeting</h6>
                                        <p class="text-sm text-muted mb-0">Plan your next meeting</p>
                                    </div>
                                    <i class="bi bi-arrow-right text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .icon-sm {
        width: 32px;
        height: 32px;
    }
    
    .timeline {
        position: relative;
        padding-left: 20px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .timeline-item:not(:last-child):before {
        content: '';
        position: absolute;
        left: -9px;
        top: 30px;
        width: 2px;
        height: calc(100% - 30px);
        background-color: #e9ecef;
    }
    
    .timeline-marker {
        position: absolute;
        left: -20px;
        top: 0;
        width: 20px;
        height: 20px;
        background-color: #fff;
        border: 2px solid #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
    
    .timeline-content {
        margin-left: 1rem;
    }
    
    .timeline-title {
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .timeline-text {
        margin-bottom: 0.25rem;
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .timeline-date {
        font-size: 0.75rem;
        color: #adb5bd;
    }
</style>