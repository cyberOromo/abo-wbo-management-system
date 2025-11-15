<?php
/**
 * Meetings Index View Template
 * Comprehensive meeting management with calendar integration and Zoom support
 */

// Page metadata
$pageTitle = __('meetings.title');
$pageDescription = __('meetings.description');
$bodyClass = 'meetings-page';

// Meeting data
$meetings = $meetings ?? [];
$meetingStats = $meetingStats ?? [];
$calendarEvents = $calendarEvents ?? [];
$filters = $filters ?? [];

// User permissions
$canCreateMeetings = $permissions['can_create_meetings'] ?? false;
$canManageMeetings = $permissions['can_manage_meetings'] ?? false;
$canScheduleMeetings = $permissions['can_schedule_meetings'] ?? false;

// Meeting types
$meetingTypes = [
    'general' => ['name' => __('meetings.general'), 'color' => 'primary', 'icon' => 'people'],
    'board' => ['name' => __('meetings.board'), 'color' => 'success', 'icon' => 'diagram-3'],
    'committee' => ['name' => __('meetings.committee'), 'color' => 'info', 'icon' => 'collection'],
    'training' => ['name' => __('meetings.training'), 'color' => 'warning', 'icon' => 'book'],
    'emergency' => ['name' => __('meetings.emergency'), 'color' => 'danger', 'icon' => 'exclamation-triangle']
];

// Meeting statuses
$meetingStatuses = [
    'scheduled' => ['name' => __('meetings.scheduled'), 'color' => 'primary'],
    'in_progress' => ['name' => __('meetings.in_progress'), 'color' => 'warning'],
    'completed' => ['name' => __('meetings.completed'), 'color' => 'success'],
    'cancelled' => ['name' => __('meetings.cancelled'), 'color' => 'danger'],
    'postponed' => ['name' => __('meetings.postponed'), 'color' => 'secondary']
];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('meetings.meeting_management') ?></h1>
        <p class="text-muted mb-0"><?= __('meetings.manage_organization_meetings') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canCreateMeetings): ?>
            <a href="/meetings/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?= __('meetings.schedule_meeting') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> <?= __('meetings.export') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/meetings/export?format=ical">
                    <i class="bi bi-calendar-event me-2"></i><?= __('meetings.export_calendar') ?>
                </a></li>
                <li><a class="dropdown-item" href="/meetings/export?format=pdf">
                    <i class="bi bi-file-earmark-pdf me-2"></i><?= __('meetings.export_pdf') ?>
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Meeting Statistics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('meetings.total_meetings') ?></h5>
                        <h2 class="mb-0"><?= number_format($meetingStats['total'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-calendar3 fs-1 opacity-25"></i>
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
                        <h5 class="card-title mb-1"><?= __('meetings.upcoming') ?></h5>
                        <h2 class="mb-0"><?= number_format($meetingStats['upcoming'] ?? 0) ?></h2>
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
                        <h5 class="card-title mb-1"><?= __('meetings.completed') ?></h5>
                        <h2 class="mb-0"><?= number_format($meetingStats['completed'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-check-circle fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('meetings.avg_attendance') ?></h5>
                        <h2 class="mb-0"><?= number_format($meetingStats['avg_attendance'] ?? 0) ?>%</h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-people fs-1 opacity-25"></i>
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
                <input type="radio" class="btn-check" name="view-mode" id="calendar-view" checked>
                <label class="btn btn-outline-primary" for="calendar-view">
                    <i class="bi bi-calendar3"></i> <?= __('meetings.calendar_view') ?>
                </label>
                <input type="radio" class="btn-check" name="view-mode" id="list-view">
                <label class="btn btn-outline-primary" for="list-view">
                    <i class="bi bi-list-ul"></i> <?= __('meetings.list_view') ?>
                </label>
            </div>
            
            <!-- Quick Filters -->
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel"></i> <?= __('meetings.filters') ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                        <form class="filters-form">
                            <div class="mb-3">
                                <label class="form-label"><?= __('meetings.search') ?></label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('meetings.type') ?></label>
                                    <select class="form-select" name="type">
                                        <option value=""><?= __('meetings.all_types') ?></option>
                                        <?php foreach ($meetingTypes as $type => $config): ?>
                                            <option value="<?= $type ?>" 
                                                    <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('meetings.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value=""><?= __('meetings.all_statuses') ?></option>
                                        <?php foreach ($meetingStatuses as $status => $config): ?>
                                            <option value="<?= $status ?>" 
                                                    <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('meetings.date_from') ?></label>
                                    <input type="date" class="form-control" name="date_from" 
                                           value="<?= $filters['date_from'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('meetings.date_to') ?></label>
                                    <input type="date" class="form-control" name="date_to" 
                                           value="<?= $filters['date_to'] ?? '' ?>">
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <?= __('meetings.apply_filters') ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-filters">
                                    <?= __('meetings.clear') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar View -->
<div id="calendar-view-content">
    <div class="card">
        <div class="card-body">
            <div id="meeting-calendar"></div>
        </div>
    </div>
</div>

<!-- List View -->
<div id="list-view-content" style="display: none;">
    <div class="card">
        <div class="card-body">
            <?php if (empty($meetings)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar3 display-1 text-muted"></i>
                    <h4 class="text-muted mt-3"><?= __('meetings.no_meetings_found') ?></h4>
                    <p class="text-muted"><?= __('meetings.no_meetings_description') ?></p>
                    <?php if ($canCreateMeetings): ?>
                        <a href="/meetings/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> <?= __('meetings.schedule_first_meeting') ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover meetings-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-meetings">
                                    </div>
                                </th>
                                <th><?= __('meetings.meeting') ?></th>
                                <th><?= __('meetings.type') ?></th>
                                <th><?= __('meetings.date_time') ?></th>
                                <th><?= __('meetings.participants') ?></th>
                                <th><?= __('meetings.status') ?></th>
                                <th><?= __('meetings.location') ?></th>
                                <th style="width: 120px;"><?= __('meetings.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($meetings as $meeting): ?>
                                <tr class="meeting-row" data-meeting-id="<?= $meeting['id'] ?>">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input meeting-checkbox" type="checkbox" 
                                                   value="<?= $meeting['id'] ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="/meetings/<?= $meeting['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($meeting['title']) ?>
                                                </a>
                                            </h6>
                                            <?php if (!empty($meeting['description'])): ?>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars(substr($meeting['description'], 0, 80)) ?>
                                                    <?= strlen($meeting['description']) > 80 ? '...' : '' ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $meetingTypes[$meeting['type']]['color'] ?>">
                                            <i class="bi bi-<?= $meetingTypes[$meeting['type']]['icon'] ?> me-1"></i>
                                            <?= $meetingTypes[$meeting['type']]['name'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= format_date($meeting['start_date']) ?></strong><br>
                                            <small class="text-muted">
                                                <?= format_time($meeting['start_time']) ?> - 
                                                <?= format_time($meeting['end_time']) ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($meeting['participants'])): ?>
                                                <?php foreach (array_slice($meeting['participants'], 0, 3) as $participant): ?>
                                                    <img src="<?= $participant['avatar'] ?? '/assets/images/default-avatar.svg' ?>" 
                                                         alt="<?= htmlspecialchars($participant['name']) ?>"
                                                         class="rounded-circle me-1" width="24" height="24"
                                                         title="<?= htmlspecialchars($participant['name']) ?>">
                                                <?php endforeach; ?>
                                                <?php if (count($meeting['participants']) > 3): ?>
                                                    <span class="badge bg-secondary">+<?= count($meeting['participants']) - 3 ?></span>
                                                <?php endif; ?>
                                                <small class="text-muted ms-2">
                                                    <?= count($meeting['participants']) ?> <?= __('meetings.participants') ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted"><?= __('meetings.no_participants') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $meetingStatuses[$meeting['status']]['color'] ?>">
                                            <?= $meetingStatuses[$meeting['status']]['name'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($meeting['location'])): ?>
                                            <small><?= htmlspecialchars($meeting['location']) ?></small>
                                        <?php elseif (!empty($meeting['zoom_link'])): ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-camera-video"></i> <?= __('meetings.online') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i><?= __('meetings.view') ?>
                                                </a></li>
                                                
                                                <?php if ($meeting['status'] === 'scheduled' || $meeting['status'] === 'in_progress'): ?>
                                                    <?php if (!empty($meeting['zoom_link'])): ?>
                                                        <li><a class="dropdown-item" href="<?= $meeting['zoom_link'] ?>" 
                                                               target="_blank">
                                                            <i class="bi bi-camera-video me-2"></i><?= __('meetings.join_zoom') ?>
                                                        </a></li>
                                                    <?php endif; ?>
                                                    
                                                    <li><a class="dropdown-item mark-attendance" href="#" 
                                                           data-meeting-id="<?= $meeting['id'] ?>">
                                                        <i class="bi bi-check-square me-2"></i><?= __('meetings.mark_attendance') ?>
                                                    </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canManageMeetings): ?>
                                                    <li><a class="dropdown-item" href="/meetings/<?= $meeting['id'] ?>/edit">
                                                        <i class="bi bi-pencil me-2"></i><?= __('meetings.edit') ?>
                                                    </a></li>
                                                    
                                                    <?php if ($meeting['status'] === 'scheduled'): ?>
                                                        <li><a class="dropdown-item cancel-meeting" href="#" 
                                                               data-meeting-id="<?= $meeting['id'] ?>">
                                                            <i class="bi bi-x-circle me-2"></i><?= __('meetings.cancel') ?>
                                                        </a></li>
                                                    <?php endif; ?>
                                                    
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger delete-meeting" href="#" 
                                                           data-meeting-id="<?= $meeting['id'] ?>">
                                                        <i class="bi bi-trash me-2"></i><?= __('meetings.delete') ?>
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

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('meetings.mark_attendance') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm">
                    <input type="hidden" id="attendance_meeting_id" name="meeting_id">
                    
                    <div class="mb-3">
                        <h6><?= __('meetings.meeting_participants') ?></h6>
                        <div id="participants-list">
                            <!-- Participants will be loaded via JavaScript -->
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('meetings.meeting_notes') ?></label>
                        <textarea class="form-control" name="meeting_notes" rows="4" 
                                  placeholder="<?= __('meetings.meeting_notes_placeholder') ?>"></textarea>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label"><?= __('meetings.actual_start_time') ?></label>
                            <input type="time" class="form-control" name="actual_start_time">
                        </div>
                        <div class="col-6">
                            <label class="form-label"><?= __('meetings.actual_end_time') ?></label>
                            <input type="time" class="form-control" name="actual_end_time">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= __('common.cancel') ?>
                </button>
                <button type="button" class="btn btn-primary" id="saveAttendance">
                    <?= __('meetings.save_attendance') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Meeting Quick View Modal -->
<div class="modal fade" id="meetingQuickView" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickViewTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <!-- Content will be loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= __('common.close') ?>
                </button>
                <a href="#" class="btn btn-primary" id="viewFullMeeting">
                    <?= __('meetings.view_full_details') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Calendar and Meeting Styles -->
<style>
.stats-card {
    border: none;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
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

#meeting-calendar {
    height: 600px;
}

.fc-event {
    border: none !important;
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 0.8rem;
}

.fc-event:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

.fc-event-title {
    font-weight: 600;
}

.meeting-type-general { background-color: #0d6efd !important; }
.meeting-type-board { background-color: #198754 !important; }
.meeting-type-committee { background-color: #0dcaf0 !important; }
.meeting-type-training { background-color: #ffc107 !important; color: #000 !important; }
.meeting-type-emergency { background-color: #dc3545 !important; }

.participants-attendance {
    max-height: 300px;
    overflow-y: auto;
}

.participant-item {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

.participant-item:last-child {
    margin-bottom: 0;
}

.attendance-status {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.attendance-status .form-check {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    #meeting-calendar {
        height: 400px;
    }
    
    .fc-toolbar {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
    }
}
</style>

<!-- Meetings JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar
    initializeCalendar();
    
    // View switching
    const calendarView = document.getElementById('calendar-view');
    const listView = document.getElementById('list-view');
    const calendarContent = document.getElementById('calendar-view-content');
    const listContent = document.getElementById('list-view-content');
    
    calendarView.addEventListener('change', function() {
        if (this.checked) {
            calendarContent.style.display = 'block';
            listContent.style.display = 'none';
        }
    });
    
    listView.addEventListener('change', function() {
        if (this.checked) {
            calendarContent.style.display = 'none';
            listContent.style.display = 'block';
        }
    });
    
    // Filter form handling
    document.querySelector('.filters-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/meetings?' + params.toString();
    });
    
    document.querySelector('.clear-filters').addEventListener('click', function() {
        window.location.href = '/meetings';
    });
    
    // Meeting actions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('mark-attendance') || e.target.closest('.mark-attendance')) {
            e.preventDefault();
            const meetingId = e.target.dataset.meetingId || e.target.closest('.mark-attendance').dataset.meetingId;
            openAttendanceModal(meetingId);
        }
        
        if (e.target.classList.contains('cancel-meeting') || e.target.closest('.cancel-meeting')) {
            e.preventDefault();
            const meetingId = e.target.dataset.meetingId || e.target.closest('.cancel-meeting').dataset.meetingId;
            cancelMeeting(meetingId);
        }
        
        if (e.target.classList.contains('delete-meeting') || e.target.closest('.delete-meeting')) {
            e.preventDefault();
            const meetingId = e.target.dataset.meetingId || e.target.closest('.delete-meeting').dataset.meetingId;
            deleteMeeting(meetingId);
        }
    });
    
    // Attendance modal
    document.getElementById('saveAttendance').addEventListener('click', function() {
        const form = document.getElementById('attendanceForm');
        const formData = new FormData(form);
        
        // Collect attendance data
        const attendanceData = {};
        document.querySelectorAll('.participant-attendance').forEach(item => {
            const participantId = item.dataset.participantId;
            const status = item.querySelector('input[type="radio"]:checked').value;
            attendanceData[participantId] = status;
        });
        formData.append('attendance', JSON.stringify(attendanceData));
        
        fetch('/api/meetings/attendance', {
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
                alert(data.message || '<?= __('meetings.attendance_save_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('meetings.attendance_save_error') ?>');
        });
    });
    
    // Functions
    function initializeCalendar() {
        const calendarEl = document.getElementById('meeting-calendar');
        if (!calendarEl) return;
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: <?= json_encode($calendarEvents) ?>,
            eventClick: function(info) {
                showMeetingQuickView(info.event.id);
            },
            eventClassNames: function(info) {
                return 'meeting-type-' + info.event.extendedProps.type;
            },
            height: 600,
            locale: '<?= $currentLanguage ?>',
            selectable: true,
            select: function(info) {
                <?php if ($canCreateMeetings): ?>
                    const startDate = info.startStr;
                    window.location.href = `/meetings/create?date=${startDate}`;
                <?php endif; ?>
            }
        });
        
        calendar.render();
    }
    
    function openAttendanceModal(meetingId) {
        document.getElementById('attendance_meeting_id').value = meetingId;
        
        // Load meeting participants
        fetch(`/api/meetings/${meetingId}/participants`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const participantsList = document.getElementById('participants-list');
                    participantsList.innerHTML = '';
                    
                    data.participants.forEach(participant => {
                        const participantItem = document.createElement('div');
                        participantItem.className = 'participant-item participant-attendance';
                        participantItem.dataset.participantId = participant.id;
                        
                        participantItem.innerHTML = `
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <img src="${participant.avatar || '/assets/images/default-avatar.svg'}" 
                                         alt="${participant.name}" class="rounded-circle me-2" width="32" height="32">
                                    <div>
                                        <h6 class="mb-0">${participant.name}</h6>
                                        <small class="text-muted">${participant.role || ''}</small>
                                    </div>
                                </div>
                                <div class="attendance-status">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="attendance_${participant.id}" value="present" id="present_${participant.id}">
                                        <label class="form-check-label" for="present_${participant.id}">
                                            <?= __('meetings.present') ?>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="attendance_${participant.id}" value="absent" id="absent_${participant.id}">
                                        <label class="form-check-label" for="absent_${participant.id}">
                                            <?= __('meetings.absent') ?>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="attendance_${participant.id}" value="late" id="late_${participant.id}">
                                        <label class="form-check-label" for="late_${participant.id}">
                                            <?= __('meetings.late') ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        participantsList.appendChild(participantItem);
                    });
                }
            });
        
        const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
        modal.show();
    }
    
    function showMeetingQuickView(meetingId) {
        fetch(`/api/meetings/${meetingId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const meeting = data.meeting;
                    document.getElementById('quickViewTitle').textContent = meeting.title;
                    document.getElementById('viewFullMeeting').href = `/meetings/${meetingId}`;
                    
                    const content = document.getElementById('quickViewContent');
                    content.innerHTML = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong><?= __('meetings.type') ?>:</strong><br>
                                <span class="badge bg-${meeting.type_color}">${meeting.type_name}</span>
                            </div>
                            <div class="col-md-6">
                                <strong><?= __('meetings.status') ?>:</strong><br>
                                <span class="badge bg-${meeting.status_color}">${meeting.status_name}</span>
                            </div>
                            <div class="col-md-6">
                                <strong><?= __('meetings.date_time') ?>:</strong><br>
                                ${meeting.start_date} ${meeting.start_time} - ${meeting.end_time}
                            </div>
                            <div class="col-md-6">
                                <strong><?= __('meetings.participants') ?>:</strong><br>
                                ${meeting.participants_count} <?= __('meetings.participants') ?>
                            </div>
                            ${meeting.location ? `
                                <div class="col-12">
                                    <strong><?= __('meetings.location') ?>:</strong><br>
                                    ${meeting.location}
                                </div>
                            ` : ''}
                            ${meeting.zoom_link ? `
                                <div class="col-12">
                                    <strong><?= __('meetings.zoom_link') ?>:</strong><br>
                                    <a href="${meeting.zoom_link}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="bi bi-camera-video"></i> <?= __('meetings.join_meeting') ?>
                                    </a>
                                </div>
                            ` : ''}
                            ${meeting.description ? `
                                <div class="col-12">
                                    <strong><?= __('meetings.description') ?>:</strong><br>
                                    ${meeting.description}
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('meetingQuickView'));
                    modal.show();
                }
            });
    }
    
    function cancelMeeting(meetingId) {
        if (confirm('<?= __('meetings.confirm_cancel') ?>')) {
            fetch(`/api/meetings/${meetingId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?= __('meetings.cancel_failed') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('meetings.cancel_error') ?>');
            });
        }
    }
    
    function deleteMeeting(meetingId) {
        if (confirm('<?= __('meetings.confirm_delete') ?>')) {
            fetch(`/api/meetings/${meetingId}`, {
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
                    alert(data.message || '<?= __('meetings.delete_failed') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('meetings.delete_error') ?>');
            });
        }
    }
});
</script>

<!-- FullCalendar CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>