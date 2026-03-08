<?php
// api/dashboard_stats.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

// Get counts
$total    = (int)$pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$new      = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
$contacted= (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'contacted'")->fetchColumn();
$converted= (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'converted'")->fetchColumn();

// Conversion rate
$conversionRate = ($total > 0) ? round(($converted / $total) * 100, 1) : 0;

// Pipeline value
$pipelineValue = $pdo->query("SELECT SUM(estimated_value) FROM leads WHERE status != 'converted'")->fetchColumn();
$pipelineValue = $pipelineValue ?: 0;

// Recent leads
$recentStmt = $pdo->query("SELECT id, name, email, status, estimated_value, created_at FROM leads ORDER BY created_at DESC LIMIT 5");
$recent = [];
while ($row = $recentStmt->fetch(PDO::FETCH_ASSOC)) {
    $row['status_badge'] = statusBadge($row['status']);
    $row['created'] = formatDate($row['created_at'], 'd M Y');
    $row['estimated_value'] = $row['estimated_value'] ? (float)$row['estimated_value'] : null;
    $recent[] = $row;
}

// Trend data (last 7 days)
$trendLabels = [];
$trendCounts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $trendLabels[] = date('D', strtotime($date));
    $trendCounts[] = (int)$stmt->fetchColumn();
}

// Source breakdown
$sourceStats = $pdo->query("
    SELECT source, COUNT(*) as count 
    FROM leads 
    WHERE source IS NOT NULL 
    GROUP BY source 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'total'         => $total,
    'new'           => $new,
    'contacted'     => $contacted,
    'converted'     => $converted,
    'conversion_rate' => $conversionRate,
    'pipeline_value' => $pipelineValue,
    'recent'        => $recent,
    'trend'         => [
        'labels' => $trendLabels,
        'counts' => $trendCounts
    ],
    'sources'       => $sourceStats
]);