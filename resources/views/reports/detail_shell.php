<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$reportTitle = $report_title ?? ($title ?? 'Report Details');
$filters = $filters ?? [];
$userScope = $user_scope ?? [];

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

$reportType = 'reports';
if (isset($task_report)) {
    $reportType = 'tasks';
} elseif (isset($meeting_report)) {
    $reportType = 'meetings';
} elseif (isset($event_report)) {
    $reportType = 'events';
} elseif (isset($donation_report)) {
    $reportType = 'donations';
} elseif (isset($hierarchy_data)) {
    $reportType = 'hierarchy';
} elseif (isset($report_data)) {
    $reportType = 'users';
} elseif (isset($course_report)) {
    $reportType = 'courses';
}

$themeClass = match ($reportType) {
    'tasks' => 'theme-tasks',
    'meetings' => 'theme-meetings',
    'events' => 'theme-events',
    'donations' => 'theme-donations',
    'users' => 'theme-users',
    'hierarchy' => 'theme-hierarchy',
    'courses' => 'theme-courses',
    default => 'theme-reports',
};

$reportIcon = match ($reportType) {
    'tasks' => 'bi-list-task',
    'meetings' => 'bi-calendar-event',
    'events' => 'bi-calendar2-week',
    'donations' => 'bi-cash-coin',
    'users' => 'bi-people',
    'hierarchy' => 'bi-diagram-3',
    'courses' => 'bi-mortarboard',
    default => 'bi-file-earmark-bar-graph',
};

$reportSubtitle = match ($reportType) {
    'tasks' => 'Structured task analytics for the current hierarchy scope.',
    'meetings' => 'Meeting analytics with schema-tolerant summaries and attendance context.',
    'events' => 'Event visibility, participation, and impact signals for the current scope.',
    'donations' => 'Donation reporting presented as readable metrics and tables instead of debug output.',
    'users' => 'Membership reporting for leader-visible users and organizational roles.',
    'hierarchy' => 'Hierarchy coverage, health, and distribution metrics for the organization.',
    'courses' => 'Education and completion reporting for scoped learning activity.',
    default => 'Report sections are rendered using a shared structured analytics layout.',
};

$isAssoc = static function (array $array): bool {
    return array_keys($array) !== range(0, count($array) - 1);
};

$isScalarValue = static function ($value): bool {
    return is_scalar($value) || $value === null;
};

$isScalarMap = static function ($value) use ($isAssoc, $isScalarValue): bool {
    if (!is_array($value) || !$isAssoc($value)) {
        return false;
    }

    foreach ($value as $item) {
        if (!$isScalarValue($item)) {
            return false;
        }
    }

    return true;
};

$isRowList = static function ($value): bool {
    return is_array($value)
        && $value !== []
        && array_keys($value) === range(0, count($value) - 1)
        && is_array($value[0] ?? null);
};

$formatLabel = static function (string $value): string {
    return ucwords(str_replace('_', ' ', $value));
};

$formatValue = static function ($value, ?string $key = null): string {
    if ($value === null || $value === '') {
        return 'None';
    }

    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }

    if (is_numeric($value)) {
        $valueString = (string) $value;
        $normalizedKey = strtolower((string) $key);

        if (str_contains($normalizedKey, 'amount') || str_contains($normalizedKey, 'revenue') || str_contains($normalizedKey, 'donation')) {
            return number_format((float) $value, 2);
        }

        if (preg_match('/^-?\d+$/', $valueString) === 1) {
            return number_format((int) $value);
        }

        return number_format((float) $value, 2);
    }

    if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}:\d{2})?/', $value) === 1) {
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date(str_contains($value, ':') ? 'M j, Y g:i A' : 'M j, Y', $timestamp);
        }
    }

    if (is_array($value)) {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    return ucwords(str_replace('_', ' ', (string) $value));
};

$summaryMetrics = [];
foreach ($sections as $label => $value) {
    if ($isScalarMap($value)) {
        foreach ($value as $metricKey => $metricValue) {
            $summaryMetrics[] = [
                'label' => $formatLabel((string) $metricKey),
                'value' => $formatValue($metricValue, (string) $metricKey),
                'context' => $label,
            ];
        }
    } elseif ($isRowList($value)) {
        $summaryMetrics[] = [
            'label' => $label,
            'value' => number_format(count($value)) . ' rows',
            'context' => 'Visible dataset',
        ];
    }
}

if (!empty($filters)) {
    $activeFilterCount = 0;
    foreach ($filters as $filterValue) {
        if ($filterValue !== 'all' && $filterValue !== '' && $filterValue !== null) {
            $activeFilterCount++;
        }
    }
    $summaryMetrics[] = [
        'label' => 'Active Filters',
        'value' => number_format($activeFilterCount),
        'context' => 'Current route filters',
    ];
}

$summaryMetrics = array_slice($summaryMetrics, 0, 6);

$renderScalarMap = static function (array $data) use ($formatLabel, $formatValue): string {
    ob_start();
    ?>
    <div class="module-metric-grid">
        <?php foreach ($data as $key => $value): ?>
            <div class="module-metric-card">
                <div class="module-metric-label"><?= htmlspecialchars($formatLabel((string) $key)) ?></div>
                <div class="module-metric-value"><?= htmlspecialchars($formatValue($value, (string) $key)) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return (string) ob_get_clean();
};

$renderLabelValueList = static function (array $rows) use ($formatValue): string {
    $maxValue = 0.0;
    foreach ($rows as $row) {
        if (isset($row['value']) && is_numeric($row['value'])) {
            $maxValue = max($maxValue, (float) $row['value']);
        }
    }

    ob_start();
    ?>
    <div class="module-stack-list">
        <?php foreach ($rows as $row): ?>
            <?php
            $value = isset($row['value']) ? (float) $row['value'] : 0.0;
            $percent = $maxValue > 0 ? (int) round(($value / $maxValue) * 100) : 0;
            ?>
            <div class="module-stack-item">
                <div class="flex-grow-1">
                    <div class="module-row-title"><?= htmlspecialchars((string) ($row['label'] ?? 'Item')) ?></div>
                    <div class="module-progress-track">
                        <div class="module-progress-fill" style="width: <?= $percent ?>%;"></div>
                    </div>
                </div>
                <div class="module-stack-value"><?= htmlspecialchars($formatValue($row['value'] ?? 0, 'value')) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return (string) ob_get_clean();
};

$renderTable = static function (array $rows) use ($formatLabel, $formatValue): string {
    $rowsToRender = array_slice($rows, 0, 25);
    $headers = [];
    foreach ($rowsToRender as $row) {
        foreach (array_keys($row) as $key) {
            if (!in_array($key, $headers, true)) {
                $headers[] = $key;
            }
        }
    }

    ob_start();
    ?>
    <?php if (count($rows) > count($rowsToRender)): ?>
        <p class="module-muted-note mb-3">Showing the first <?= number_format(count($rowsToRender)) ?> rows out of <?= number_format(count($rows)) ?> returned rows.</p>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="module-table">
            <thead>
                <tr>
                    <?php foreach ($headers as $header): ?>
                        <th><?= htmlspecialchars($formatLabel((string) $header)) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rowsToRender as $row): ?>
                    <tr>
                        <?php foreach ($headers as $header): ?>
                            <td><?= htmlspecialchars($formatValue($row[$header] ?? null, (string) $header)) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return (string) ob_get_clean();
};

$renderMixedMap = static function (array $data) use ($formatLabel, $formatValue): string {
    ob_start();
    ?>
    <div class="module-key-grid">
        <?php foreach ($data as $key => $value): ?>
            <div class="module-key-row">
                <span class="module-key-label"><?= htmlspecialchars($formatLabel((string) $key)) ?></span>
                <span class="module-key-value"><?= htmlspecialchars($formatValue($value, (string) $key)) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return (string) ob_get_clean();
};

$renderSection = static function ($value) use ($isScalarMap, $isRowList, $renderScalarMap, $renderLabelValueList, $renderTable, $renderMixedMap, $formatValue): string {
    if ($value === [] || $value === null) {
        return '<div class="module-empty py-4"><i class="bi bi-inbox"></i><p class="mb-0 mt-2">No data returned for this section.</p></div>';
    }

    if ($isScalarMap($value)) {
        return $renderScalarMap($value);
    }

    if ($isRowList($value)) {
        $isLabelValueRows = true;
        foreach ($value as $row) {
            if (!is_array($row) || !array_key_exists('label', $row) || !array_key_exists('value', $row)) {
                $isLabelValueRows = false;
                break;
            }
        }

        return $isLabelValueRows ? $renderLabelValueList($value) : $renderTable($value);
    }

    if (is_array($value)) {
        return $renderMixedMap($value);
    }

    return '<div class="module-row-title">' . htmlspecialchars($formatValue($value)) . '</div>';
};
?>

<div class="module-surface <?= htmlspecialchars($themeClass) ?>">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-bar-chart-line"></i> Structured Analytics</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi <?= htmlspecialchars($reportIcon) ?> me-2"></i><?= htmlspecialchars($reportTitle) ?></h1>
                    <p class="module-subtitle"><?= htmlspecialchars($reportSubtitle) ?></p>
                </div>
                <div class="module-actions">
                    <a href="/reports" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Reports</a>
                    <?php if ($reportType !== 'reports'): ?>
                        <a href="/reports/export/<?= htmlspecialchars($reportType) ?>?format=csv" class="btn btn-primary"><i class="bi bi-download me-1"></i>Export CSV</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="module-chip-row">
                <?php if (!empty($userScope['scope_name'])): ?>
                    <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($userScope['scope_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($userScope['level_scope'])): ?>
                    <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) $userScope['level_scope'])) ?> level</span>
                <?php endif; ?>
                <span class="module-chip"><i class="bi bi-grid"></i><?= number_format(count($sections)) ?> sections</span>
            </div>
        </div>
    </section>

    <?php if (!empty($filters)): ?>
        <div class="module-callout mb-4">
            <strong>Active filters:</strong>
            <div class="module-chip-row mt-3">
                <?php foreach ($filters as $key => $value): ?>
                    <span class="module-chip"><i class="bi bi-funnel"></i><?= htmlspecialchars($formatLabel((string) $key)) ?>: <?= htmlspecialchars($formatValue($value, (string) $key)) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($summaryMetrics)): ?>
        <div class="row g-4 mb-4">
            <?php foreach ($summaryMetrics as $metric): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="module-stat-card">
                        <div class="stat-topline">
                            <div>
                                <div class="stat-value"><?= htmlspecialchars($metric['value']) ?></div>
                                <div class="stat-label"><?= htmlspecialchars($metric['label']) ?></div>
                            </div>
                            <span class="stat-icon"><i class="bi bi-activity"></i></span>
                        </div>
                        <div class="stat-footnote"><?= htmlspecialchars($metric['context']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($sections as $label => $value): ?>
            <div class="col-12">
                <div class="module-panel">
                    <div class="module-panel-header">
                        <h2 class="module-panel-title"><i class="bi bi-layout-text-window-reverse me-2"></i><?= htmlspecialchars($label) ?></h2>
                        <?php if (is_array($value)): ?>
                            <span class="module-soft-badge"><i class="bi bi-grid-3x3-gap"></i><?= number_format(count($value)) ?> item<?= count($value) === 1 ? '' : 's' ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="module-panel-body">
                        <?= $renderSection($value) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>