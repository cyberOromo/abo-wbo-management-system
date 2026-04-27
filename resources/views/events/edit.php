<?php
$title = $title ?? 'Edit Event';
$event = $event ?? [];

$eventTypeOptions = ['social' => 'Social', 'educational' => 'Educational', 'cultural' => 'Cultural', 'fundraising' => 'Fundraising', 'community' => 'Community', 'political' => 'Political', 'memorial' => 'Memorial', 'celebration' => 'Celebration', 'conference' => 'Conference'];
$registrationTypeOptions = ['open' => 'Open', 'approval_required' => 'Approval Required', 'invitation_only' => 'Invitation Only', 'closed' => 'Closed'];
$statusOptions = ['planning' => 'Planning', 'open_registration' => 'Open Registration', 'registration_closed' => 'Registration Closed', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'postponed' => 'Postponed'];
$currentEventType = (string) ($event['event_type'] ?? 'social');
$currentRegistrationType = (string) ($event['registration_type'] ?? 'open');
$currentStatus = (string) ($event['status'] ?? 'planning');

if (!isset($eventTypeOptions[$currentEventType])) {
    $eventTypeOptions[$currentEventType] = ucfirst(str_replace('_', ' ', $currentEventType));
}
if (!isset($registrationTypeOptions[$currentRegistrationType])) {
    $registrationTypeOptions[$currentRegistrationType] = ucfirst(str_replace('_', ' ', $currentRegistrationType));
}
if (!isset($statusOptions[$currentStatus])) {
    $currentStatus = 'planning';
}

$formatDateTimeLocal = static function ($value): string {
    if (empty($value)) {
        return '';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date('Y-m-d\TH:i', $timestamp) : '';
};

$tagsValue = $event['tags'] ?? [];
if (is_array($tagsValue)) {
    $tagsValue = implode(', ', array_map('strval', $tagsValue));
}

$requirementsItems = $event['requirements'] ?? [];
if (!is_array($requirementsItems)) {
    $requirementsItems = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $requirementsItems)));
}

$requirementsItems = array_values(array_map('strval', $requirementsItems));

if ($requirementsItems === []) {
    $requirementsItems = [''];
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-pencil-square me-2"></i>Edit Event</h1>
            <p class="text-muted mb-0">Update schedule, registration, and venue settings for <?= htmlspecialchars((string) ($event['title'] ?? 'this event')) ?>.</p>
        </div>
        <a href="/events/<?= (int) ($event['id'] ?? 0) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Event</a>
    </div>

    <form method="POST" action="/events/<?= (int) ($event['id'] ?? 0) ?>/update" class="card shadow-sm border-0">
        <div class="card-body p-4">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="level_scope" value="<?= htmlspecialchars((string) ($event['level_scope'] ?? '')) ?>">
            <input type="hidden" name="scope_id" value="<?= htmlspecialchars((string) ($event['scope_id'] ?? '')) ?>">

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars((string) ($event['title'] ?? '')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Event Type</label>
                    <select name="event_type" class="form-select">
                        <?php foreach ($eventTypeOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $currentEventType === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $currentStatus === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars((string) ($event['description'] ?? '')) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Start</label>
                    <input type="datetime-local" name="start_datetime" class="form-control" value="<?= htmlspecialchars($formatDateTimeLocal($event['start_datetime'] ?? null)) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">End</label>
                    <input type="datetime-local" name="end_datetime" class="form-control" value="<?= htmlspecialchars($formatDateTimeLocal($event['end_datetime'] ?? null)) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Timezone</label>
                    <input type="text" name="timezone" class="form-control" value="<?= htmlspecialchars((string) ($event['timezone'] ?? 'UTC')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Venue Name</label>
                    <input type="text" name="venue_name" class="form-control" value="<?= htmlspecialchars((string) ($event['venue_name'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Registration Type</label>
                    <select name="registration_type" class="form-select">
                        <?php foreach ($registrationTypeOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $currentRegistrationType === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Venue Address</label>
                    <input type="text" name="venue_address" class="form-control" value="<?= htmlspecialchars((string) ($event['venue_address'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Participants</label>
                    <input type="number" min="1" name="max_participants" class="form-control" value="<?= htmlspecialchars((string) ($event['max_participants'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Min Participants</label>
                    <input type="number" min="0" name="min_participants" class="form-control" value="<?= htmlspecialchars((string) ($event['min_participants'] ?? '0')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Registration Fee</label>
                    <input type="number" step="0.01" min="0" name="registration_fee" class="form-control" value="<?= htmlspecialchars((string) ($event['registration_fee'] ?? '0')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Currency</label>
                    <input type="text" name="currency" class="form-control" value="<?= htmlspecialchars((string) ($event['currency'] ?? 'USD')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars((string) ($event['contact_email'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars((string) ($event['contact_phone'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Requirements</label>
                    <div class="d-grid gap-2">
                        <?php foreach ($requirementsItems as $index => $requirementItem): ?>
                            <input type="text" name="requirements[]" class="form-control" value="<?= htmlspecialchars($requirementItem) ?>" placeholder="Requirement <?= $index + 1 ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tags</label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars((string) $tagsValue) ?>" placeholder="comma,separated,tags">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_virtual" value="1" id="event-is-virtual" <?= !empty($event['is_virtual']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="event-is-virtual">Virtual event</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="requires_payment" value="1" id="event-requires-payment" <?= !empty($event['requires_payment']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="event-requires-payment">Requires payment</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="/events" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Event</button>
        </div>
    </form>
</div>
