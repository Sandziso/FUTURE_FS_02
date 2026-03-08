<?php
// admin/views/email_template_delete.php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['csrf_token'] ?? '';

if (!$id || !verifyCSRFToken($token)) {
    $_SESSION['template_message'] = 'Invalid request.';
    $_SESSION['template_message_class'] = 'alert alert-danger';
    redirect('settings.php?tab=email_templates');
}

$stmt = $pdo->prepare("DELETE FROM email_templates WHERE id = ?");
$stmt->execute([$id]);

logActivity($_SESSION['user_id'], 'Deleted email template', "Template ID: $id");
$_SESSION['template_message'] = 'Template deleted successfully.';
$_SESSION['template_message_class'] = 'alert alert-success';
redirect('settings.php?tab=email_templates');