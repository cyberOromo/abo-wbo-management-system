<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$meeting = $meeting ?? [];
$participants = $participants ?? [];
$activities = $activities ?? [];
$canEdit = $canEdit ?? false;
$canDelete = $canDelete ?? false;

$status = (string) ($meeting['status'] ?? 'scheduled');
$platform = (string) ($meeting['platform'] ?? ($meeting['is_virtual'] ?? false ? 'virtual' : 'in_person'));
$agenda = $meeting['agenda'] ?? [];
$minutes = $meeting['meeting_minutes'] ?? [];
$tags = $meeting['tags'] ?? [];

if (is_string($agenda)) {
    $decodedAgenda = json_decode($agenda, true);
    $agenda = is_array($decodedAgenda) ? $decodedAgenda : array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $agenda)));
}

if (is_string($minutes)) {
    $decodedMinutes = json_decode($minutes, true);
    $minutes = is_array($decodedMinutes) ? $decodedMinutes : [$minutes];
}

if (is_string($tags)) {
    $decodedTags = json_decode($tags, true);
    $tags = is_array($decodedTags) ? $decodedTags : array_filter(array_map('trim', explode(',', $tags)));
}

$statusClass = match ($status) {
    'completed' => 'status-success',
    'in_progress' => 'status-info',
    'scheduled', 'postponed' => 'status-warning',
    'cancelled' => 'status-danger',
    default => 'status-neutral',
};

$formatDateTime = static function (?string $value, string $fallback = 'TBD'): string {
    if (empty($value)) {
        return $fallback;
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('M j, Y g:i A', $timestamp) : $fallback;
};

$durationLabel = 'Not set';
if (!empty($meeting['start_datetime']) && !empty($meeting['end_datetime'])) {
    $start = strtotime((string) $meeting['start_datetime']);
    $end = strtotime((string) $meeting['end_datetime']);
    if ($start && $end && $end >= $start) {
        $minutesDiff = (int) round(($end - $start) / 60);
        $hours = intdiv($minutesDiff, 60);
        $minutesOnly = $minutesDiff % 60;
        $durationLabel = trim(($hours > 0 ? $hours . 'h ' : '') . ($minutesOnly > 0 ? $minutesOnly . 'm' : ''));
    }
}
?>

<div class="module-surface theme-meetings">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-calendar-event"></i> Meeting Detail</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-calendar-event me-2"></i><?= htmlspecialchars((string) ($meeting['title'] ?? 'Meeting')) ?></h1>
                    <p class="module-subtitle"><?= htmlspecialchars((string) ($meeting['description'] ?? 'No meeting description provided.')) ?></p>
                </div>
                <div class="module-actions">
                    <?php if ($canEdit): ?>
                        <a href="/meetings/<?= (int) ($meeting['id'] ?? 0) ?>/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil-square me-1"></i>Edit Meeting</a>
                        <a href="/meetings/<?= (int) ($meeting['id'] ?? 0) ?>/minutes" class="btn btn-outline-primary"><i class="bi bi-journal-text me-1"></i>Minutes</a>
                    <?php endif; ?>
                    <a href="/meetings" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Meetings</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-broadcast"></i><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $platform))) ?></span>
                <span class="module-chip"><i class="bi bi-clock"></i><?= htmlspecialchars($formatDateTime($meeting['start_datetime'] ?? null)) ?></span>
                <span class="module-chip"><i class="bi bi-pin-map"></i><?= htmlspecialchars((string) ($meeting['location'] ?? 'Location pending')) ?></span>
            </div>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count($participants)) ?></div><div class="stat-label">Participants</div></div><span class="stat-icon"><i class="bi bi-people"></i></span></div><div class="stat-footnote">People currently attached to this meeting record.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= htmlspecialchars($durationLabel) ?></div><div class="stat-label">Duration</div></div><span class="stat-icon"><i class="bi bi-hourglass-split"></i></span></div><div class="stat-footnote">Planned duration based on start and end times.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count($activities)) ?></div><div class="stat-label">Activities</div></div><span class="stat-icon"><i class="bi bi-clock-history"></i></span></div><div class="stat-footnote">Logged meeting activity entries.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count((array) ($meeting['moderators'] ?? []))) ?></div><div class="stat-label">Moderators</div></div><span class="stat-icon"><i class="bi bi-person-badge"></i></span></div><div class="stat-footnote">Moderation assignments on this session.</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Meeting Snapshot</h2><span class="module-status <?= $statusClass ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?></span></div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Meeting type</span><span class="module-key-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($meeting['meeting_type'] ?? 'regular')))) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Start</span><span class="module-key-value"><?= htmlspecialchars($formatDateTime($meeting['start_datetime'] ?? null)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">End</span><span class="module-key-value"><?= htmlspecialchars($formatDateTime($meeting['end_datetime'] ?? null)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Public access</span><span class="module-key-value"><?= !empty($meeting['is_public']) ? 'Visible to scoped audience' : 'Restricted' ?></span></div>
                    </div>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-list-check me-2"></i>Agenda</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($agenda)): ?>
                        <div class="module-stack-list">
                            <?php foreach ((array) $agenda as $agendaItem): ?>
                                <div class="module-stack-item"><div><div class="module-row-title"><?= htmlspecialchars(is_array($agendaItem) ? (string) ($agendaItem['title'] ?? json_encode($agendaItem)) : (string) $agendaItem) ?></div></div></div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="module-empty py-4"><i class="bi bi-list-check"></i><p class="mb-0 mt-2">No agenda has been captured yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-people me-2"></i>Participants</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($participants)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($participants as $participant): ?>
                                <?php $name = trim((string) (($participant['first_name'] ?? '') . ' ' . ($participant['last_name'] ?? ''))); ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars($name !== '' ? $name : (string) ($participant['name'] ?? 'Participant')) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars((string) ($participant['email'] ?? $participant['internal_email'] ?? '')) ?></div>
                                    </div>
                                    <div class="module-stack-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($participant['status'] ?? $participant['participation_status'] ?? 'invited')))) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="module-empty py-4"><i class="bi bi-people"></i><p class="mb-0 mt-2">No participants are attached yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-clock-history me-2"></i>Activity Timeline</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($activities)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($activities as $entry): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars((string) ($entry['description'] ?? $entry['action'] ?? 'Activity')) ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars((string) ($entry['created_at'] ?? '')) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No activity has been logged yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-geo-alt me-2"></i>Location and Access</h2></div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Platform</span><span class="module-key-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $platform))) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Location</span><span class="module-key-value"><?= htmlspecialchars((string) ($meeting['location'] ?? 'TBD')) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Timezone</span><span class="module-key-value"><?= htmlspecialchars((string) ($meeting['timezone'] ?? 'Server default')) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Recording</span><span class="module-key-value"><?= !empty($meeting['recording_url']) ? 'Available' : 'Not attached' ?></span></div>
                    </div>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-journal-text me-2"></i>Minutes</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($minutes)): ?>
                        <div class="module-stack-list">
                            <?php foreach ((array) $minutes as $minuteItem): ?>
                                <div class="module-stack-item"><div><div class="module-row-title"><?= htmlspecialchars(is_array($minuteItem) ? (string) ($minuteItem['title'] ?? json_encode($minuteItem)) : (string) $minuteItem) ?></div></div></div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">Meeting minutes have not been added yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-tags me-2"></i>Tags</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($tags)): ?>
                        <div class="module-chip-row mt-0">
                            <?php foreach ($tags as $tag): ?>
                                <span class="module-chip"><?= htmlspecialchars((string) $tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No tags have been assigned to this meeting.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>