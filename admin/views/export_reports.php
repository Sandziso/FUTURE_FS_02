<?php
// admin/export_reports.php
// Export reports data as CSV based on date range

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../../config/database.php'; // $pdo
// Get date range from GET, default to last 30 days
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));

// Validate dates (simple)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    die('Invalid date format.');
}

// --- Fetch data (similar to reports.php) ---

// Summary stats
$totalLeads = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE DATE(created_at) BETWEEN ? AND ?");
$totalLeads->execute([$start_date, $end_date]);
$totalLeads = $totalLeads->fetchColumn();

$convertedLeads = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE status='converted' AND DATE(created_at) BETWEEN ? AND ?");
$convertedLeads->execute([$start_date, $end_date]);
$convertedLeads = $convertedLeads->fetchColumn();

$totalClients = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE DATE(created_at) BETWEEN ? AND ?");
$totalClients->execute([$start_date, $end_date]);
$totalClients = $totalClients->fetchColumn();

$pipelineValue = $pdo->prepare("SELECT SUM(estimated_value) FROM leads WHERE status != 'converted' AND DATE(created_at) BETWEEN ? AND ?");
$pipelineValue->execute([$start_date, $end_date]);
$pipelineValue = $pipelineValue->fetchColumn() ?: 0;

// Leads by source
$sourceStmt = $pdo->prepare("SELECT source, COUNT(*) as count FROM leads WHERE source IS NOT NULL AND DATE(created_at) BETWEEN ? AND ? GROUP BY source ORDER BY count DESC");
$sourceStmt->execute([$start_date, $end_date]);
$sources = $sourceStmt->fetchAll(PDO::FETCH_ASSOC);

// Daily trend
$trendStmt = $pdo->prepare("SELECT DATE(created_at) as day, COUNT(*) as count FROM leads WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY day");
$trendStmt->execute([$start_date, $end_date]);
$trend = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

// Status breakdown
$statusStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM leads WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY status");
$statusStmt->execute([$start_date, $end_date]);
$statuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

// Optional: new clients list? Not needed.

// --- Output CSV ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=report_' . $start_date . '_to_' . $end_date . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

// Helper to write a section header
function writeSection($fp, $title) {
    fputcsv($fp, []);
    fputcsv($fp, ['=== ' . $title . ' ===']);
}

// 1. Report parameters
fputcsv($output, ['Report Period', $start_date, 'to', $end_date]);
fputcsv($output, []); // blank line

// 2. Summary stats
writeSection($output, 'Summary');
fputcsv($output, ['Metric', 'Value']);
fputcsv($output, ['Total Leads', $totalLeads]);
fputcsv($output, ['Converted Leads', $convertedLeads]);
fputcsv($output, ['New Clients', $totalClients]);
fputcsv($output, ['Pipeline Value (non-converted)', number_format($pipelineValue, 2)]);

// 3. Daily trend
writeSection($output, 'Daily Lead Trend');
fputcsv($output, ['Date', 'Leads Count']);
foreach ($trend as $row) {
    fputcsv($output, [$row['day'], $row['count']]);
}

// 4. Source breakdown
writeSection($output, 'Lead Sources');
fputcsv($output, ['Source', 'Count']);
foreach ($sources as $row) {
    fputcsv($output, [$row['source'] ?: 'Unknown', $row['count']]);
}

// 5. Status breakdown
writeSection($output, 'Lead Status');
fputcsv($output, ['Status', 'Count']);
foreach ($statuses as $row) {
    fputcsv($output, [ucfirst($row['status']), $row['count']]);
}

// Optional: add conversion funnel percentages?
writeSection($output, 'Conversion Funnel');
$contactedCount = 0;
foreach ($statuses as $s) {
    if ($s['status'] == 'contacted') $contactedCount = $s['count'];
}
$newToContactedPct = $totalLeads ? round(($contactedCount / $totalLeads) * 100, 1) : 0;
$contactedToConvertedPct = $contactedCount ? round(($convertedLeads / $contactedCount) * 100, 1) : 0;
$overallConversionPct = $totalLeads ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
fputcsv($output, ['New → Contacted %', $newToContactedPct . '%']);
fputcsv($output, ['Contacted → Converted %', $contactedToConvertedPct . '%']);
fputcsv($output, ['Overall Conversion %', $overallConversionPct . '%']);

fclose($output);
exit;