<?php
$pageTitle = $title ?? 'Meeting Management';
$layout = 'modern';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-calendar-event me-2"></i>
            Meeting Management
        </h1>
        <p class="text-muted mb-0">Schedule, organize, and track organizational meetings</p>
    </div>
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMeetingModal">
                <i class="bi bi-calendar-plus me-1"></i>
                Schedule Meeting
            </button>
        </div>
        <div class="btn-group">
            <a href="/meetings/calendar" class="btn btn-outline-secondary">
                <i class="bi bi-calendar3 me-1"></i>
                Calendar View
            </a>
        </div>
    </div>
</div>

<!-- Meeting Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_meetings'] ?? 24) ?></h3>
                    <p class="text-muted mb-0">Total Meetings</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        <?= $stats['this_month'] ?? 6 ?> This Month
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['upcoming'] ?? 5) ?></h3>
                    <p class="text-muted mb-0">Upcoming</p>
                    <small class="text-info">
                        <i class="bi bi-calendar"></i>
                        Next 7 Days
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['avg_attendance'] ?? 87) ?>%</h3>
                    <p class="text-muted mb-0">Avg Attendance</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        +5% This Month
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-camera-video"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['virtual_meetings'] ?? 18) ?></h3>
                    <p class="text-muted mb-0">Virtual Meetings</p>
                    <small class="text-info">
                        <i class="bi bi-globe"></i>
                        75% Online
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
                        <button class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-bs-toggle="modal" data-bs-target="#createMeetingModal">
                            <div class="action-icon bg-primary text-white mb-2">
                                <i class="bi bi-calendar-plus"></i>
                            </div>
                            <span class="fw-bold">Schedule Meeting</span>
                            <small class="text-muted">Create new meeting</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='?filter=today'">
                            <div class="action-icon bg-success text-white mb-2">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <span class="fw-bold">Today's Meetings</span>
                            <small class="text-muted">View today's agenda</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='/meetings/templates'">
                            <div class="action-icon bg-info text-white mb-2">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <span class="fw-bold">Meeting Templates</span>
                            <small class="text-muted">Predefined agendas</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='/reports/meetings'">
                            <div class="action-icon bg-warning text-white mb-2">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="fw-bold">Meeting Reports</span>
                            <small class="text-muted">Analytics & insights</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Meetings List & Calendar -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list me-2"></i>
                    Upcoming Meetings
                </h5>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary active" data-view="list">
                        <i class="bi bi-list"></i> List
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="calendar">
                        <i class="bi bi-calendar3"></i> Calendar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="meetings-list-view">
                    <?php 
                    $sampleMeetings = [
                        ['id' => 1, 'title' => 'Executive Board Meeting', 'type' => 'board', 'date' => '2025-11-18', 'time' => '14:00', 'location' => 'Virtual', 'attendees' => 8, 'status' => 'confirmed'],
                        ['id' => 2, 'title' => 'Community Outreach Planning', 'type' => 'planning', 'date' => '2025-11-20', 'time' => '10:00', 'location' => 'Conference Room A', 'attendees' => 15, 'status' => 'pending'],
                        ['id' => 3, 'title' => 'Financial Review Committee', 'type' => 'committee', 'date' => '2025-11-22', 'time' => '16:30', 'location' => 'Virtual', 'attendees' => 6, 'status' => 'confirmed'],
                        ['id' => 4, 'title' => 'Monthly General Assembly', 'type' => 'assembly', 'date' => '2025-11-25', 'time' => '19:00', 'location' => 'Main Hall', 'attendees' => 45, 'status' => 'confirmed'],
                        ['id' => 5, 'title' => 'Project Team Sync', 'type' => 'team', 'date' => '2025-11-27', 'time' => '13:00', 'location' => 'Virtual', 'attendees' => 12, 'status' => 'draft']
                    ];
                    
                    foreach ($sampleMeetings as $meeting): 
                        $typeClass = [
                            'board' => 'primary',
                            'planning' => 'info',
                            'committee' => 'success',
                            'assembly' => 'warning',
                            'team' => 'secondary'
                        ][$meeting['type']] ?? 'secondary';
                        
                        $statusClass = [
                            'confirmed' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            'draft' => 'secondary'
                        ][$meeting['status']] ?? 'secondary';
                        
                        $isVirtual = $meeting['location'] === 'Virtual';
                        $isPast = strtotime($meeting['date']) < time();
                    ?>
                        <div class="meeting-item border rounded p-3 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="meeting-icon bg-<?= $typeClass ?> bg-opacity-10 text-<?= $typeClass ?> me-3">
                                            <i class="bi bi-<?= $isVirtual ? 'camera-video' : 'building' ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($meeting['title']) ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?= htmlspecialchars($meeting['location']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <div class="fw-bold"><?= date('M j', strtotime($meeting['date'])) ?></div>
                                        <small class="text-muted"><?= date('g:i A', strtotime($meeting['time'])) ?></small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($meeting['status']) ?></span>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="bi bi-people me-1"></i>
                                                <?= $meeting['attendees'] ?> attendees
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a></li>
                                            <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>/edit">
                                                <i class="bi bi-pencil me-2"></i>Edit Meeting
                                            </a></li>
                                            <?php if ($meeting['status'] === 'confirmed' && !$isPast): ?>
                                                <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>/join">
                                                    <i class="bi bi-box-arrow-in-right me-2"></i>Join Meeting
                                                </a></li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="cancelMeeting(<?= $meeting['id'] ?>)">
                                                <i class="bi bi-x-circle me-2"></i>Cancel
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="meetings-calendar-view" style="display: none;">
                    <div class="calendar-view">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>November 2025</h6>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="previousMonth()">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="nextMonth()">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="calendar-grid">
                            <div class="row text-center small text-muted fw-bold">
                                <div class="col">Sun</div>
                                <div class="col">Mon</div>
                                <div class="col">Tue</div>
                                <div class="col">Wed</div>
                                <div class="col">Thu</div>
                                <div class="col">Fri</div>
                                <div class="col">Sat</div>
                            </div>
                            
                            <?php for ($week = 0; $week < 5; $week++): ?>
                                <div class="row">
                                    <?php for ($day = 0; $day < 7; $day++): 
                                        $date = $week * 7 + $day - 2; // Adjust for calendar start
                                        if ($date > 0 && $date <= 30):
                                            $hasMeeting = in_array($date, [18, 20, 22, 25, 27]);
                                    ?>
                                        <div class="col p-1">
                                            <div class="calendar-day-full <?= $hasMeeting ? 'has-meeting' : '' ?>">
                                                <div class="day-number"><?= $date ?></div>
                                                <?php if ($hasMeeting): ?>
                                                    <div class="meeting-dot"></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="col p-1">
                                            <div class="calendar-day-full empty"></div>
                                        </div>
                                    <?php endif; endfor; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Meeting Sidebar -->
    <div class="col-lg-4">
        <!-- Today's Schedule -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-calendar-day me-2"></i>
                    Today's Schedule
                </h6>
            </div>
            <div class="card-body">
                <div class="schedule-item d-flex align-items-center p-2 rounded mb-2 bg-primary bg-opacity-10">
                    <div class="time-badge bg-primary text-white me-3">2:00 PM</div>
                    <div>
                        <h6 class="mb-0 small">Executive Board Meeting</h6>
                        <small class="text-muted">Virtual - 8 attendees</small>
                    </div>
                </div>
                
                <div class="text-center py-3">
                    <i class="bi bi-calendar-check text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 small">No other meetings scheduled for today</p>
                </div>
            </div>
        </div>
        
        <!-- Meeting Types -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-collection me-2"></i>
                    Meeting Types
                </h6>
            </div>
            <div class="card-body">
                <div class="meeting-type-item d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-primary me-2"></div>
                        <span class="small">Board Meetings</span>
                    </div>
                    <span class="badge bg-secondary">3</span>
                </div>
                <div class="meeting-type-item d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-info me-2"></div>
                        <span class="small">Planning Sessions</span>
                    </div>
                    <span class="badge bg-secondary">2</span>
                </div>
                <div class="meeting-type-item d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-success me-2"></div>
                        <span class="small">Committee Meetings</span>
                    </div>
                    <span class="badge bg-secondary">4</span>
                </div>
                <div class="meeting-type-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-warning me-2"></div>
                        <span class="small">General Assembly</span>
                    </div>
                    <span class="badge bg-secondary">1</span>
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
                                <h6 class="mb-1">Meeting Scheduled</h6>
                                <p class="text-muted mb-0 small">Executive Board Meeting set for Nov 18</p>
                            </div>
                            <small class="text-muted">1h ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Meeting Completed</h6>
                                <p class="text-muted mb-0 small">Community Outreach completed with 12 attendees</p>
                            </div>
                            <small class="text-muted">2d ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Agenda Updated</h6>
                                <p class="text-muted mb-0 small">Financial Review agenda modified</p>
                            </div>
                            <small class="text-muted">3d ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Meeting Modal -->
<div class="modal fade" id="createMeetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-plus me-2"></i>
                    Schedule New Meeting
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createMeetingForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="meetingTitle" class="form-label">Meeting Title</label>
                                <input type="text" class="form-control" id="meetingTitle" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="meetingType" class="form-label">Meeting Type</label>
                                <select class="form-select" id="meetingType" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="board">Board Meeting</option>
                                    <option value="planning">Planning Session</option>
                                    <option value="committee">Committee Meeting</option>
                                    <option value="assembly">General Assembly</option>
                                    <option value="team">Team Meeting</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meetingAgenda" class="form-label">Agenda</label>
                        <textarea class="form-control" id="meetingAgenda" name="agenda" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meetingDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="meetingDate" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meetingTime" class="form-label">Time</label>
                                <input type="time" class="form-control" id="meetingTime" name="time" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meetingLocation" class="form-label">Location</label>
                                <select class="form-select" id="meetingLocation" name="location_type" onchange="toggleLocationInput()">
                                    <option value="virtual">Virtual Meeting</option>
                                    <option value="physical">Physical Location</option>
                                    <option value="hybrid">Hybrid Meeting</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meetingRoom" class="form-label">Room/Link</label>
                                <input type="text" class="form-control" id="meetingRoom" name="location_details" placeholder="Meeting room or video link">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meetingAttendees" class="form-label">Attendees</label>
                        <select class="form-select" id="meetingAttendees" name="attendees[]" multiple>
                            <option value="1">John Doe - Executive</option>
                            <option value="2">Jane Smith - Secretary</option>
                            <option value="3">Mike Johnson - Treasurer</option>
                            <option value="4">Sarah Wilson - Community Rep</option>
                            <option value="5">David Lee - IT Manager</option>
                            <option value="board">All Board Members</option>
                            <option value="executives">All Executives</option>
                            <option value="members">All Members</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple attendees</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="scheduleMeeting()">
                    <i class="bi bi-calendar-check me-1"></i>
                    Schedule Meeting
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.meeting-item {
    transition: var(--abo-transition);
    background: var(--abo-white);
}

.meeting-item:hover {
    transform: translateX(4px);
    box-shadow: var(--abo-shadow-md);
}

.meeting-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--abo-radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.calendar-day-full {
    height: 80px;
    border: 1px solid var(--abo-gray-200);
    border-radius: var(--abo-radius);
    padding: 0.25rem;
    position: relative;
    background: var(--abo-white);
    transition: var(--abo-transition);
}

.calendar-day-full:hover {
    background-color: var(--abo-gray-50);
}

.calendar-day-full.has-meeting {
    background-color: var(--abo-primary);
    background-opacity: 0.1;
    border-color: var(--abo-primary);
}

.calendar-day-full.empty {
    background-color: var(--abo-gray-50);
    border-color: transparent;
}

.day-number {
    font-weight: 600;
    font-size: 0.875rem;
}

.meeting-dot {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 8px;
    height: 8px;
    background-color: var(--abo-primary);
    border-radius: 50%;
}

.schedule-item {
    transition: var(--abo-transition);
}

.schedule-item:hover {
    transform: scale(1.02);
}

.time-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: var(--abo-radius);
    min-width: 60px;
    text-align: center;
}

.meeting-type-item {
    padding: 0.25rem;
    border-radius: var(--abo-radius);
    transition: var(--abo-transition);
}

.meeting-type-item:hover {
    background-color: var(--abo-gray-50);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const viewButtons = document.querySelectorAll('[data-view]');
    const listView = document.getElementById('meetings-list-view');
    const calendarView = document.getElementById('meetings-calendar-view');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide views
            if (viewType === 'list') {
                listView.style.display = 'block';
                calendarView.style.display = 'none';
            } else if (viewType === 'calendar') {
                listView.style.display = 'none';
                calendarView.style.display = 'block';
            }
        });
    });
    
    // Set minimum date to today
    document.getElementById('meetingDate').min = new Date().toISOString().split('T')[0];
});

function scheduleMeeting() {
    const form = document.getElementById('createMeetingForm');
    const formData = new FormData(form);
    
    // Here you would normally send the data to the server
    console.log('Scheduling meeting with data:', Object.fromEntries(formData));
    
    // Simulate success
    alert('Meeting scheduled successfully!');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('createMeetingModal'));
    modal.hide();
    form.reset();
    
    // Refresh page (in real app, you'd add the meeting to the DOM)
    window.location.reload();
}

function cancelMeeting(meetingId) {
    if (confirm('Are you sure you want to cancel this meeting?')) {
        // Here you would send a request to cancel the meeting
        console.log('Canceling meeting:', meetingId);
        alert('Meeting cancelled successfully!');
        window.location.reload();
    }
}

function toggleLocationInput() {
    const locationType = document.getElementById('meetingLocation').value;
    const locationInput = document.getElementById('meetingRoom');
    
    if (locationType === 'virtual') {
        locationInput.placeholder = 'Zoom/Teams meeting link';
    } else if (locationType === 'physical') {
        locationInput.placeholder = 'Conference room or address';
    } else {
        locationInput.placeholder = 'Room + virtual link';
    }
}

function previousMonth() {
    // Implementation for calendar navigation
    console.log('Previous month');
}

function nextMonth() {
    // Implementation for calendar navigation
    console.log('Next month');
}
</script>
