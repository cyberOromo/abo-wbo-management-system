<?php
// Quick status check for modernized views

$modules = [
    'Tasks' => 'resources/views/tasks/index_modern.php',
    'Meetings' => 'resources/views/meetings/index_modern.php', 
    'Events' => 'resources/views/events/index_modern.php',
    'Donations' => 'resources/views/donations/index_modern.php',
    'Reports' => 'resources/views/reports/index_modern.php',
    'Member Registration' => 'resources/views/member-registration/index_modern.php',
    'Responsibilities' => 'resources/views/responsibilities/index_modern.php', 
    'Hierarchy Management' => 'resources/views/hierarchy/index_modern.php',
    'User Management' => 'resources/views/user-management/index_modern.php',
    'Position Management' => 'resources/views/position-management/index_modern.php',
    'Settings' => 'resources/views/settings/index_modern.php'
];

echo "=== ABO-WBO Modern UI Progress ===\n\n";

$modernized = 0;
$total = count($modules);

foreach ($modules as $name => $path) {
    $isModernized = strpos($path, '_modern.php') !== false && file_exists($path);
    $status = $isModernized ? '✅ MODERNIZED' : '⏳ PENDING';
    
    if ($isModernized) $modernized++;
    
    echo sprintf("%-20s %s\n", $name, $status);
}

$percentage = round(($modernized / $total) * 100);

echo "\n=== Progress Summary ===\n";
echo "Modernized: $modernized/$total modules ($percentage%)\n";
echo "Status: ";

if ($percentage >= 100) {
    echo "🎉 COMPLETE! All modules modernized\n";
} elseif ($percentage >= 75) {
    echo "🚀 Nearly complete! Final push needed\n";
} elseif ($percentage >= 50) {
    echo "📈 Good progress! Half way there\n";  
} elseif ($percentage >= 25) {
    echo "🔨 Building momentum! Keep going\n";
} else {
    echo "🏁 Just getting started! Much work ahead\n";
}

echo "\nNext Priority: ";
foreach ($modules as $name => $path) {
    if (strpos($path, '_modern.php') === false) {
        echo "$name\n";
        break;
    }
}
?>