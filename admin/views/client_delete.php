<?php
// admin/views/client_delete.php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['csrf_token'] ?? '';

if (!$id || !verifyCSRFToken($token)) {
    $_SESSION['client_message'] = 'Invalid request.';
    $_SESSION['client_message_class'] = 'alert alert-danger';
    redirect('clients.php');
}

// Optionally check if client has related data (projects, invoices) and decide to block or cascade.
// For simplicity, we'll delete (foreign keys are SET NULL in schema)
$stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
$stmt->execute([$id]);

logActivity($_SESSION['user_id'], 'Deleted client', "Client ID: $id");
$_SESSION['client_message'] = 'Client deleted successfully.';
$_SESSION['client_message_class'] = 'alert alert-success';
redirect('clients.php');