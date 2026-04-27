<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$event = $event ?? [];
$participants = $participants ?? [];
$statistics = $statistics ?? [];
$activities = $activities ?? [];
$userRegistration = $userRegistration ?? null;
$canEdit = $canEdit ?? false;
$canDelete = $canDelete ?? false;
$canRegister = $canRegister ?? false;

$status = (string) ($event['status'] ?? 'upcoming');
$requirements = $event['requirements'] ?? [];
$whatToBring = $event['what_to_bring'] ?? [];
$tags = $event['tags'] ?? [];

foreach (['requirements', 'whatToBring', 'tags'] as $variableName) {
    if (!is_array($$variableName) && is_string($$variableName)) {
        $decoded = json_decode($$variableName, true);
        $$variableName = is_array($decoded) ? $decoded : array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $$variableName)));
    }
}

$statusClass = match ($status) {
    'completed' => 'status-success',
    'ongoing', 'in_progress' => 'status-info',
    'open_registration', 'upcoming', 'registration_closed' => 'status-warning',
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

$feeLabel = !empty($event['requires_payment']) || ((float) ($event['registration_fee'] ?? 0) > 0)
    ? (($event['currency'] ?? 'USD') . ' ' . number_format((float) ($event['registration_fee'] ?? 0), 2))
    : 'Free';
?>

<div class="module-surface theme-events">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-calendar2-week"></i> Event Detail</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-calendar2-week me-2"></i><?= htmlspecialchars((string) ($event['title'] ?? 'Event')) ?></h1>
                    <p class="module-subtitle"><?= htmlspecialchars((string) ($event['description'] ?? 'No event description provided.')) ?></p>
                </div>
                <div class="module-actions">
                    <?php if ($canEdit): ?>
                        <a href="/events/<?= (int) ($event['id'] ?? 0) ?>/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil-square me-1"></i>Edit Event</a>
                    <?php endif; ?>
                    <a href="/events" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Events</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-calendar3"></i><?= htmlspecialchars($formatDateTime($event['start_datetime'] ?? null)) ?></span>
                <span class="module-chip"><i class="bi bi-pin-map"></i><?= htmlspecialchars((string) ($event['venue_name'] ?? $event['location'] ?? 'Venue pending')) ?></span>
                <span class="module-chip"><i class="bi bi-cash-coin"></i><?= htmlspecialchars($feeLabel) ?></span>
            </div>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count($participants)) ?></div><div class="stat-label">Participants</div></div><span class="stat-icon"><i class="bi bi-people"></i></span></div><div class="stat-footnote">People currently registered or attached to this event.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($statistics['confirmed_registrations'] ?? $statistics['confirmed'] ?? 0)) ?></div><div class="stat-label">Confirmed</div></div><span class="stat-icon"><i class="bi bi-person-check"></i></span></div><div class="stat-footnote">Confirmed participant count from the current stats payload.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format(count($activities)) ?></div><div class="stat-label">Activities</div></div><span class="stat-icon"><i class="bi bi-clock-history"></i></span></div><div class="stat-footnote">Event activity entries captured so far.</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= htmlspecialchars($feeLabel) ?></div><div class="stat-label">Access</div></div><span class="stat-icon"><i class="bi bi-ticket-perforated"></i></span></div><div class="stat-footnote">Registration pricing and access posture.</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Event Snapshot</h2><span class="module-status <?= $statusClass ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span></div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Event type</span><span class="module-key-value"><?= htmlspecialchars(ucfirst((string) ($event['event_type'] ?? 'general'))) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Start</span><span class="module-key-value"><?= htmlspecialchars($formatDateTime($event['start_datetime'] ?? null)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">End</span><span class="module-key-value"><?= htmlspecialchars($formatDateTime($event['end_datetime'] ?? null)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Registration</span><span class="module-key-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($event['registration_type'] ?? 'open')))) ?></span></div>
                    </div>
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
                                    <div class="module-stack-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($participant['status'] ?? 'registered')))) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="module-empty py-4"><i class="bi bi-people"></i><p class="mb-0 mt-2">No participants are attached yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-card-checklist me-2"></i>Requirements and Preparation</h2></div>
                <div class="module-panel-body">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="module-row-title mb-3">Requirements</div>
                            <?php if (!empty($requirements)): ?>
                                <div class="module-stack-list"><?php foreach ($requirements as $item): ?><div class="module-stack-item"><div><div class="module-row-title"><?= htmlspecialchars((string) $item) ?></div></div></div><?php endforeach; ?></div>
                            <?php else: ?>
                                <p class="module-muted-note mb-0">No special requirements were provided.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="module-row-title mb-3">What To Bring</div>
                            <?php if (!empty($whatToBring)): ?>
                                <div class="module-stack-list"><?php foreach ($whatToBring as $item): ?><div class="module-stack-item"><div><div class="module-row-title"><?= htmlspecialchars((string) $item) ?></div></div></div><?php endforeach; ?></div>
                            <?php else: ?>
                                <p class="module-muted-note mb-0">No preparation checklist was provided.</p>
                            <?php endif; ?>
                        </div>
                    </div>
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
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-person-check me-2"></i>Your Registration</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($userRegistration)): ?>
                        <div class="module-key-grid mb-3">
                            <div class="module-key-row"><span class="module-key-label">Status</span><span class="module-key-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($userRegistration['status'] ?? 'registered')))) ?></span></div>
                            <div class="module-key-row"><span class="module-key-label">Registered at</span><span class="module-key-value"><?= htmlspecialchars($formatDateTime($userRegistration['registered_at'] ?? null, 'Recorded')) ?></span></div>
                        </div>
                    <?php elseif ($canRegister): ?>
                        <p class="module-muted-note mb-0">Registration appears open for your account, but this detail refresh keeps mutation controls hidden until the event registration path is stabilized end to end.</p>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">Registration is not currently available from this account or event state.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-geo-alt me-2"></i>Venue and Access</h2></div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Venue</span><span class="module-key-value"><?= htmlspecialchars((string) ($event['venue_name'] ?? $event['location'] ?? 'TBD')) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Address</span><span class="module-key-value"><?= htmlspecialchars((string) ($event['venue_address'] ?? 'Not provided')) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Virtual</span><span class="module-key-value"><?= !empty($event['is_virtual']) ? 'Yes' : 'No' ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Max participants</span><span class="module-key-value"><?= htmlspecialchars((string) ($event['max_participants'] ?? 'Open')) ?></span></div>
                    </div>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header"><h2 class="module-panel-title"><i class="bi bi-tags me-2"></i>Tags</h2></div>
                <div class="module-panel-body">
                    <?php if (!empty($tags)): ?>
                        <div class="module-chip-row mt-0"><?php foreach ($tags as $tag): ?><span class="module-chip"><?= htmlspecialchars((string) $tag) ?></span><?php endforeach; ?></div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No tags have been assigned to this event.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>