<?php
$title = $title ?? 'Edit Meeting';
$meeting = $meeting ?? [];

$meetingTypeOptions = ['regular' => 'Regular', 'emergency' => 'Emergency', 'training' => 'Training', 'social' => 'Social', 'planning' => 'Planning'];
$platformOptions = ['in_person' => 'In Person', 'virtual' => 'Virtual', 'zoom' => 'Zoom', 'hybrid' => 'Hybrid'];
$currentMeetingType = (string) ($meeting['meeting_type'] ?? 'regular');
$currentPlatform = (string) ($meeting['platform'] ?? (!empty($meeting['is_virtual']) ? 'virtual' : 'in_person'));

if (!isset($meetingTypeOptions[$currentMeetingType])) {
    $meetingTypeOptions[$currentMeetingType] = ucfirst(str_replace('_', ' ', $currentMeetingType));
}

if (!isset($platformOptions[$currentPlatform])) {
    $currentPlatform = 'in_person';
}

$agendaItems = $meeting['agenda'] ?? [];
if (!is_array($agendaItems)) {
    $agendaItems = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $agendaItems)));
}

$agendaItems = array_values(array_map(
    static fn($item) => is_array($item) ? (string) ($item['title'] ?? json_encode($item)) : (string) $item,
    $agendaItems
));

if ($agendaItems === []) {
    $agendaItems = [''];
}

$tagValue = $meeting['tags'] ?? [];
if (is_array($tagValue)) {
    $tagValue = implode(', ', array_map('strval', $tagValue));
}

$formatDateTimeLocal = static function ($value): string {
    if (empty($value)) {
        return '';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date('Y-m-d\TH:i', $timestamp) : '';
};
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-pencil-square me-2"></i>Edit Meeting</h1>
            <p class="text-muted mb-0">Update schedule, platform, and meeting notes for <?= htmlspecialchars((string) ($meeting['title'] ?? 'this meeting')) ?>.</p>
        </div>
        <a href="/meetings/<?= (int) ($meeting['id'] ?? 0) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Meeting</a>
    </div>

    <form method="POST" action="/meetings/<?= (int) ($meeting['id'] ?? 0) ?>/update" class="card shadow-sm border-0">
        <div class="card-body p-4">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="level_scope" value="<?= htmlspecialchars((string) ($meeting['level_scope'] ?? '')) ?>">
            <input type="hidden" name="scope_id" value="<?= htmlspecialchars((string) ($meeting['scope_id'] ?? '')) ?>">

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars((string) ($meeting['title'] ?? '')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Meeting Type</label>
                    <select name="meeting_type" class="form-select">
                        <?php foreach ($meetingTypeOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $currentMeetingType === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Platform</label>
                    <select name="platform" class="form-select">
                        <?php foreach ($platformOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $currentPlatform === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars((string) ($meeting['description'] ?? '')) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Start</label>
                    <input type="datetime-local" name="start_datetime" class="form-control" value="<?= htmlspecialchars($formatDateTimeLocal($meeting['start_datetime'] ?? null)) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">End</label>
                    <input type="datetime-local" name="end_datetime" class="form-control" value="<?= htmlspecialchars($formatDateTimeLocal($meeting['end_datetime'] ?? null)) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Timezone</label>
                    <input type="text" name="timezone" class="form-control" value="<?= htmlspecialchars((string) ($meeting['timezone'] ?? 'UTC')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Location</label>
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars((string) ($meeting['location'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Participants</label>
                    <input type="number" min="1" name="max_participants" class="form-control" value="<?= htmlspecialchars((string) ($meeting['max_participants'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Agenda</label>
                    <div class="d-grid gap-2">
                        <?php foreach ($agendaItems as $index => $agendaItem): ?>
                            <input type="text" name="agenda[]" class="form-control" value="<?= htmlspecialchars($agendaItem) ?>" placeholder="Agenda item <?= $index + 1 ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tags</label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars((string) $tagValue) ?>" placeholder="comma,separated,tags">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_public" value="1" id="meeting-is-public" <?= !empty($meeting['is_public']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="meeting-is-public">Visible to scoped audience</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="requires_approval" value="1" id="meeting-requires-approval" <?= !empty($meeting['requires_approval']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="meeting-requires-approval">Requires approval before join</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="/meetings" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Meeting</button>
        </div>
    </form>
</div>
