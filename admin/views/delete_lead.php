<?php
// admin/views/delete_lead.php
require_once '../includes/header.php';

$lead_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['csrf_token'] ?? '';

if (!$lead_id || !verifyCSRFToken($token)) {
    $_SESSION['flash_message'] = 'Invalid request.';
    $_SESSION['flash_class'] = 'alert alert-danger';
    redirect('leads.php');
}

$stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
$stmt->execute([$lead_id]);

$_SESSION['flash_message'] = 'Lead deleted successfully.';
$_SESSION['flash_class'] = 'alert alert-success';
redirect('leads.php');