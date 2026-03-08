<?php
// staff/api/dashboard_stats.php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SESSION['user_role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$total   = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$new     = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
$contacted = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'contacted'")->fetchColumn();
$converted = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'converted'")->fetchColumn();

echo json_encode([
    'total' => $total,
    'new' => $new,
    'contacted' => $contacted,
    'converted' => $converted
]);