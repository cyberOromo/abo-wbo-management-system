<?php
$currentPage = 'meetings';
?>

<!-- Modern Meetings Management Interface -->
<style>
    .meeting-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .meeting-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -2px rgba(0, 0, 0, 0.15);
    }
    
    .meeting-status-upcoming {
        border-left: 4px solid var(--primary-green);
    }
    
    .meeting-status-today {
        border-left: 4px solid #fbbf24;
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.05) 0%, white 50%);
    }
    
    .meeting-status-completed {
        border-left: 4px solid #10b981;
    }
    
    .meeting-status-cancelled {
        border-left: 4px solid var(--primary-red);
    }
    
    .calendar-view {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .calendar-header {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        padding: 1.5rem;
        text-align: center;
    }
    
    .calendar-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e5e7eb;
    }
    
    .calendar-day {
        background: white;
        min-height: 100px;
        padding: 0.75rem;
        position: relative;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    
    .calendar-day:hover {
        background: #f9fafb;
        border-color: var(--primary-green);
    }
    
    .calendar-day.today {
        background: rgba(45, 80, 22, 0.1);
        border-color: var(--primary-green);
    }
    
    .calendar-day.other-month {
        color: #9ca3af;
        background: #f9fafb;
    }
    
    .calendar-day-number {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .calendar-meeting {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        margin-bottom: 0.25rem;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .calendar-meeting:hover {
        transform: scale(1.05);
    }
    
    .meeting-time-badge {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .meeting-type-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .meeting-type-formal {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border: 1px solid rgba(139, 21, 56, 0.2);
    }
    
    .meeting-type-casual {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border: 1px solid rgba(45, 80, 22, 0.2);
    }
    
    .attendee-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        margin-right: 0.5rem;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .attendees-stack {
        display: flex;
        margin-left: -0.5rem;
    }
    
    .attendee-avatar:not(:first-child) {
        margin-left: -0.5rem;
    }
    
    .attendee-count {
        background: #6b7280;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: -0.5rem;
    }
    
    .quick-actions-panel {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .quick-action-btn {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(45, 80, 22, 0.2);
    }
    
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .quick-action-btn.secondary {
        background: linear-gradient(135deg, #6b7280, #9ca3af);
        box-shadow: 0 2px 4px rgba(107, 114, 128, 0.2);
    }
    
    .quick-action-btn.secondary:hover {
        box-shadow: 0 8px 16px rgba(107, 114, 128, 0.3);
    }
    
    .agenda-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        position: relative;
    }
    
    .agenda-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary-green);
        border-radius: 2px 0 0 2px;
    }
    
    .meeting-location {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
</style>

<div class="page-header">
    <h1 class="page-title">Meetings Management</h1>
    <p class="page-description">Schedule, organize, and track meetings with advanced calendar integration and hierarchy-based access control</p>
</div>

<!-- Enhanced Statistics Dashboard -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-green);"><?= $meeting_stats['total'] ?? 0 ?></div>
            <div class="text-muted fw-500">Total Meetings</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #fbbf24;"><?= $meeting_stats['today'] ?? 0 ?></div>
            <div class="text-muted fw-500">Today</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #3b82f6;"><?= $meeting_stats['upcoming'] ?? 0 ?></div>
            <div class="text-muted fw-500">Upcoming</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-red);"><?= $meeting_stats['pending_approval'] ?? 0 ?></div>
            <div class="text-muted fw-500">Pending Approval</div>
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
                        <input type="radio" class="btn-check" name="viewMode" id="calendarView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary" for="calendarView">
                            <i class="bi bi-calendar-month"></i> Calendar
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="listView">
                            <i class="bi bi-list-ul"></i> List
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="agendaView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="agendaView">
                            <i class="bi bi-journal-text"></i> Agenda
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="d-flex gap-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="scheduled">📅 Scheduled</option>
                        <option value="in_progress">🚀 In Progress</option>
                        <option value="completed">✅ Completed</option>
                        <option value="cancelled">❌ Cancelled</option>
                        <option value="postponed">⏳ Postponed</option>
                    </select>
                    
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="board_meeting">🏛️ Board Meeting</option>
                        <option value="team_meeting">👥 Team Meeting</option>
                        <option value="planning">📋 Planning</option>
                        <option value="review">🔍 Review</option>
                        <option value="training">📚 Training</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="d-flex gap-2 justify-content-end">
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showScheduleMeetingModal()">
                            <i class="bi bi-plus-calendar"></i> Schedule Meeting
                        </button>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/meetings/export?format=ical">📅 iCal Export</a></li>
                            <li><a class="dropdown-item" href="/meetings/export?format=csv">📊 CSV Export</a></li>
                            <li><a class="dropdown-item" href="/meetings/export?format=pdf">📄 Agenda PDF</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar View -->
<div id="calendarViewContainer">
    <div class="calendar-view">
        <div class="calendar-header">
            <div class="calendar-nav">
                <button class="btn btn-light btn-sm" onclick="previousMonth()">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <h3 class="mb-0" id="currentMonthYear"><?= date('F Y') ?></h3>
                <button class="btn btn-light btn-sm" onclick="nextMonth()">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div class="d-flex justify-content-center gap-4">
                <div class="text-white-50"><small>📅 Meetings: <?= count($meetings ?? []) ?></small></div>
                <div class="text-white-50"><small>👥 Participants: <?= $total_participants ?? 0 ?></small></div>
            </div>
        </div>
        
        <div class="p-3">
            <!-- Calendar Day Headers -->
            <div class="calendar-grid mb-2">
                <div class="text-center fw-600 p-2" style="background: #f3f4f6;">Sun</div>
                <div class="text-center fw-600 p-2" style="background: #f3f4f6;">Mon</div>
                <div class="text-center fw-600 p-2" style="background: #f3f4f6;">Tue</div>
                <div class="text-center fw-600 p-2" style="background: #f3f4f6;">Wed</div>
                <div class="text-center fw-600 p-2" style="background: #f3f4f6;">Thu</div>
                <div class="text-center fw-600 p-2" style="background: #f3f4f6;">Fri</div>
                <div class="text-center fw-600 p-2" style="background: #f3f4f6;">Sat</div>
            </div>
            
            <!-- Calendar Days -->
            <div class="calendar-grid" id="calendarDays">
                <?= generateCalendarDays($meetings ?? []) ?>
            </div>
        </div>
    </div>
</div>

<!-- List View (Hidden by default) -->
<div id="listViewContainer" style="display: none;">
    <div class="row g-4" id="meetingsGrid">
        <?php if (!empty($meetings)): ?>
            <?php foreach ($meetings as $meeting): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 meeting-item" 
                     data-status="<?= $meeting['status'] ?? 'scheduled' ?>" 
                     data-type="<?= $meeting['type'] ?? 'team_meeting' ?>">
                    <div class="meeting-card meeting-status-<?= $meeting['status'] ?? 'upcoming' ?>">
                        <!-- Meeting Header -->
                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start p-3">
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <span class="meeting-time-badge">
                                    <i class="bi bi-clock"></i>
                                    <?= date('g:i A', strtotime($meeting['start_time'] ?? '')) ?>
                                </span>
                                <span class="meeting-type-badge meeting-type-<?= ($meeting['type'] === 'board_meeting') ? 'formal' : 'casual' ?>">
                                    <?= getMeetingTypeIcon($meeting['type'] ?? 'team_meeting') ?> 
                                    <?= ucfirst(str_replace('_', ' ', $meeting['type'] ?? 'team_meeting')) ?>
                                </span>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>">
                                        <i class="bi bi-eye"></i> View Details
                                    </a></li>
                                    <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>/edit">
                                        <i class="bi bi-pencil"></i> Edit Meeting
                                    </a></li>
                                    <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>/join">
                                        <i class="bi bi-camera-video"></i> Join Meeting
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="cancelMeeting(<?= $meeting['id'] ?>)">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Meeting Content -->
                        <div class="card-body p-3 pt-0">
                            <h5 class="card-title mb-2 fw-600"><?= htmlspecialchars($meeting['title'] ?? 'Untitled Meeting') ?></h5>
                            <p class="card-text text-muted mb-3">
                                <?= htmlspecialchars(substr($meeting['description'] ?? 'No description provided', 0, 120)) ?>
                                <?= strlen($meeting['description'] ?? '') > 120 ? '...' : '' ?>
                            </p>
                            
                            <!-- Meeting Details -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="d-flex align-items-center gap-1 text-muted">
                                        <i class="bi bi-calendar3"></i>
                                        <small><?= date('M j, Y', strtotime($meeting['meeting_date'] ?? '')) ?></small>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 text-muted">
                                        <i class="bi bi-clock"></i>
                                        <small><?= date('g:i A', strtotime($meeting['start_time'] ?? '')) ?> - <?= date('g:i A', strtotime($meeting['end_time'] ?? '')) ?></small>
                                    </div>
                                </div>
                                
                                <?php if (isset($meeting['location'])): ?>
                                    <div class="mb-2">
                                        <span class="meeting-location">
                                            <i class="bi bi-geo-alt"></i>
                                            <?= htmlspecialchars($meeting['location']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Attendees -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted fw-500 d-block mb-1">Attendees</small>
                                    <div class="attendees-stack">
                                        <?php 
                                        $attendees = json_decode($meeting['attendees'] ?? '[]', true);
                                        $maxVisible = 3;
                                        for ($i = 0; $i < min(count($attendees), $maxVisible); $i++): 
                                        ?>
                                            <div class="attendee-avatar" title="<?= htmlspecialchars($attendees[$i]['name'] ?? 'Attendee') ?>">
                                                <?= substr($attendees[$i]['name'] ?? 'A', 0, 1) ?>
                                            </div>
                                        <?php endfor; ?>
                                        
                                        <?php if (count($attendees) > $maxVisible): ?>
                                            <div class="attendee-count" title="<?= count($attendees) - $maxVisible ?> more attendees">
                                                +<?= count($attendees) - $maxVisible ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <span class="badge bg-<?= getMeetingStatusColor($meeting['status'] ?? 'scheduled') ?>">
                                        <?= getMeetingStatusIcon($meeting['status'] ?? 'scheduled') ?> 
                                        <?= ucfirst($meeting['status'] ?? 'scheduled') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-calendar-x" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Meetings Scheduled</h4>
                    <p class="text-muted mb-4">Schedule your first meeting to start organizing team collaborations</p>
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showScheduleMeetingModal()">
                            <i class="bi bi-plus-calendar"></i> Schedule Your First Meeting
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="row g-4 mt-4">
    <div class="col-md-8">
        <div class="quick-actions-panel">
            <h5 class="mb-3 fw-600">Meeting Analytics</h5>
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Average Duration</h6>
                            <small class="text-muted"><?= $meeting_analytics['avg_duration'] ?? '1.5 hours' ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Avg Attendance</h6>
                            <small class="text-muted"><?= $meeting_analytics['avg_attendance'] ?? '85%' ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="quick-actions-panel">
            <h5 class="mb-3 fw-600">Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="/meetings/rooms" class="quick-action-btn secondary">
                    <i class="bi bi-building"></i> Manage Rooms
                </a>
                <a href="/meetings/templates" class="quick-action-btn secondary">
                    <i class="bi bi-file-text"></i> Meeting Templates
                </a>
                <a href="/meetings/recurring" class="quick-action-btn secondary">
                    <i class="bi bi-arrow-repeat"></i> Recurring Meetings
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Meeting Modal -->
<div class="modal fade" id="scheduleMeetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule New Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/meetings/create">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Meeting Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Enter meeting title">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Meeting Type</label>
                            <select name="type" class="form-select">
                                <option value="team_meeting">👥 Team Meeting</option>
                                <option value="board_meeting">🏛️ Board Meeting</option>
                                <option value="planning">📋 Planning Session</option>
                                <option value="review">🔍 Review Meeting</option>
                                <option value="training">📚 Training Session</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Priority Level</label>
                            <select name="priority" class="form-select">
                                <option value="normal">📄 Normal</option>
                                <option value="high">⚡ High Priority</option>
                                <option value="urgent">🔥 Urgent</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Provide meeting objectives and agenda overview..."></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Meeting Date *</label>
                            <input type="date" name="meeting_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">End Time *</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Location</label>
                            <input type="text" name="location" class="form-control" 
                                   placeholder="Conference room, online link, or address">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Meeting Room</label>
                            <select name="room_id" class="form-select">
                                <option value="">Select room (optional)</option>
                                <!-- Populated via AJAX -->
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Attendees</label>
                            <select name="attendees[]" class="form-select" multiple>
                                <!-- Populated via AJAX based on hierarchy -->
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple attendees</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Is Recurring?</label>
                            <select name="is_recurring" class="form-select" onchange="toggleRecurrenceOptions(this.value)">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6" id="recurrencePattern" style="display: none;">
                            <label class="form-label fw-500">Recurrence Pattern</label>
                            <select name="recurrence_pattern" class="form-select">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-calendar"></i> Schedule Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switching functionality
    const calendarView = document.getElementById('calendarView');
    const listView = document.getElementById('listView');
    const calendarContainer = document.getElementById('calendarViewContainer');
    const listContainer = document.getElementById('listViewContainer');
    
    calendarView.addEventListener('change', function() {
        if (this.checked) {
            calendarContainer.style.display = 'block';
            listContainer.style.display = 'none';
        }
    });
    
    listView.addEventListener('change', function() {
        if (this.checked) {
            calendarContainer.style.display = 'none';
            listContainer.style.display = 'block';
        }
    });
    
    // Advanced filtering
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    function applyFilters() {
        const statusValue = statusFilter.value;
        const typeValue = typeFilter.value;
        
        document.querySelectorAll('.meeting-item').forEach(item => {
            const showStatus = !statusValue || item.dataset.status === statusValue;
            const showType = !typeValue || item.dataset.type === typeValue;
            item.style.display = showStatus && showType ? 'block' : 'none';
        });
    }
    
    statusFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
});

function showScheduleMeetingModal() {
    new bootstrap.Modal(document.getElementById('scheduleMeetingModal')).show();
}

function toggleRecurrenceOptions(value) {
    const recurrencePattern = document.getElementById('recurrencePattern');
    recurrencePattern.style.display = value === '1' ? 'block' : 'none';
}

function cancelMeeting(meetingId) {
    if (confirm('Are you sure you want to cancel this meeting? All attendees will be notified.')) {
        fetch(`/meetings/${meetingId}/cancel`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.ok ? location.reload() : alert('Error cancelling meeting'))
        .catch(() => alert('Error cancelling meeting'));
    }
}

function previousMonth() {
    // Calendar navigation logic
    console.log('Previous month');
}

function nextMonth() {
    // Calendar navigation logic
    console.log('Next month');
}
</script>

<?php
// Helper functions for UI
function getMeetingStatusColor($status) {
    return [
        'scheduled' => 'primary',
        'in_progress' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
        'postponed' => 'secondary'
    ][$status] ?? 'primary';
}

function getMeetingStatusIcon($status) {
    return [
        'scheduled' => '📅',
        'in_progress' => '🚀',
        'completed' => '✅',
        'cancelled' => '❌',
        'postponed' => '⏳'
    ][$status] ?? '📅';
}

function getMeetingTypeIcon($type) {
    return [
        'board_meeting' => '🏛️',
        'team_meeting' => '👥',
        'planning' => '📋',
        'review' => '🔍',
        'training' => '📚'
    ][$type] ?? '👥';
}

function generateCalendarDays($meetings) {
    $output = '';
    $today = date('j');
    $currentMonth = date('n');
    $currentYear = date('Y');
    $daysInMonth = date('t');
    $firstDayOfWeek = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
    
    // Previous month days
    $prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
    $prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
    $daysInPrevMonth = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));
    
    for ($i = $firstDayOfWeek - 1; $i >= 0; $i--) {
        $day = $daysInPrevMonth - $i;
        $output .= "<div class='calendar-day other-month'><div class='calendar-day-number'>$day</div></div>";
    }
    
    // Current month days
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $isToday = $day == $today ? 'today' : '';
        $dayMeetings = getDayMeetings($meetings, $currentYear, $currentMonth, $day);
        
        $output .= "<div class='calendar-day $isToday'>";
        $output .= "<div class='calendar-day-number'>$day</div>";
        
        foreach ($dayMeetings as $meeting) {
            $title = htmlspecialchars(substr($meeting['title'] ?? 'Meeting', 0, 20));
            $time = date('g:i A', strtotime($meeting['start_time'] ?? ''));
            $output .= "<div class='calendar-meeting' onclick='viewMeeting({$meeting['id']})' title='$title at $time'>$title</div>";
        }
        
        $output .= "</div>";
    }
    
    return $output;
}

function getDayMeetings($meetings, $year, $month, $day) {
    $targetDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
    return array_filter($meetings, function($meeting) use ($targetDate) {
        return ($meeting['meeting_date'] ?? '') === $targetDate;
    });
}
?>