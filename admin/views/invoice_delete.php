<?php
// admin/views/invoice_delete.php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['csrf_token'] ?? '';

if (!$id || !verifyCSRFToken($token)) {
    $_SESSION['invoice_message'] = 'Invalid request.';
    $_SESSION['invoice_message_class'] = 'alert alert-danger';
    redirect('invoices.php');
}

$stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
$stmt->execute([$id]);

logActivity($_SESSION['user_id'], 'Deleted invoice', "Invoice ID: $id");
$_SESSION['invoice_message'] = 'Invoice deleted successfully.';
$_SESSION['invoice_message_class'] = 'alert alert-success';
redirect('invoices.php');