<?php
// admin/views/project_delete.php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['csrf_token'] ?? '';

if (!$id || !verifyCSRFToken($token)) {
    $_SESSION['project_message'] = 'Invalid request.';
    $_SESSION['project_message_class'] = 'alert alert-danger';
    redirect('projects.php');
}

$stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
$stmt->execute([$id]);

logActivity($_SESSION['user_id'], 'Deleted project', "Project ID: $id");
$_SESSION['project_message'] = 'Project deleted successfully.';
$_SESSION['project_message_class'] = 'alert alert-success';
redirect('projects.php');