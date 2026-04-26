<?php
$reportTitle = $report_title ?? ($title ?? 'Report Details');
$sections = [
    'Report Data' => $report_data ?? null,
    'Hierarchy Data' => $hierarchy_data ?? null,
    'Position Distribution' => $position_distribution ?? null,
    'Hierarchy Health' => $hierarchy_health ?? null,
    'Task Report' => $task_report ?? null,
    'Task Metrics' => $task_metrics ?? null,
    'Productivity Data' => $productivity_data ?? null,
    'Meeting Report' => $meeting_report ?? null,
    'Attendance Data' => $attendance_data ?? null,
    'Effectiveness Metrics' => $effectiveness_metrics ?? null,
    'Event Report' => $event_report ?? null,
    'Participation Data' => $participation_data ?? null,
    'Event Impact' => $event_impact ?? null,
    'Donation Report' => $donation_report ?? null,
    'Donation Trends' => $donation_trends ?? null,
    'Donor Analysis' => $donor_analysis ?? null,
    'Course Report' => $course_report ?? null,
    'Enrollment Data' => $enrollment_data ?? null,
    'Completion Rates' => $completion_rates ?? null,
];

$sections = array_filter($sections, static fn($value) => $value !== null);

$renderValue = static function ($value): string {
    if (is_scalar($value) || $value === null) {
        return '<div class="text-body-secondary">' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</div>';
    }

    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return '<pre class="mb-0 small bg-light border rounded p-3 overflow-auto">' .
        htmlspecialchars($json ?: '[]', ENT_QUOTES, 'UTF-8') .
        '</pre>';
};
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($reportTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-muted mb-0">Active report route stabilized through the current resources/views render path.</p>
        </div>
        <a href="/reports" class="btn btn-outline-secondary">Back to Reports</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Filters</strong>
        </div>
        <div class="card-body">
            <?= $renderValue($filters ?? []) ?>
        </div>
    </div>

    <?php foreach ($sections as $label => $value): ?>
        <div class="card mb-4">
            <div class="card-header">
                <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="card-body">
                <?= $renderValue($value) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>