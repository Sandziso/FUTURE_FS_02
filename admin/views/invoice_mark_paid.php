<?php
// admin/views/invoice_mark_paid.php
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('invoices.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['invoice_message'] = 'Invalid CSRF token.';
    $_SESSION['invoice_message_class'] = 'alert alert-danger';
    redirect('invoices.php');
}

$invoice_id = (int)($_POST['invoice_id'] ?? 0);
if (!$invoice_id) {
    $_SESSION['invoice_message'] = 'Invalid invoice ID.';
    $_SESSION['invoice_message_class'] = 'alert alert-danger';
    redirect('invoices.php');
}

$stmt = $pdo->prepare("UPDATE invoices SET status = 'paid', paid_date = CURDATE() WHERE id = ?");
$stmt->execute([$invoice_id]);

logActivity($_SESSION['user_id'], 'Marked invoice as paid', "Invoice ID: $invoice_id");
$_SESSION['invoice_message'] = 'Invoice marked as paid.';
$_SESSION['invoice_message_class'] = 'alert alert-success';
redirect('invoice_details.php?id=' . $invoice_id);