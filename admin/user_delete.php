<?php
// admin/user_delete.php
require_once 'includes/header.php'; // this also enforces admin role

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$csrf_token = $_GET['csrf_token'] ?? '';

// CSRF verification
if (!verifyCSRFToken($csrf_token)) {
    $_SESSION['user_message'] = 'Invalid CSRF token.';
    $_SESSION['user_message_class'] = 'alert alert-danger';
    redirect('users.php');
}

if ($id <= 0) {
    $_SESSION['user_message'] = 'Invalid user ID.';
    $_SESSION['user_message_class'] = 'alert alert-danger';
    redirect('users.php');
}

// Prevent self-deletion
if ($id == $_SESSION['user_id']) {
    $_SESSION['user_message'] = 'You cannot delete your own account.';
    $_SESSION['user_message_class'] = 'alert alert-danger';
    redirect('users.php');
}

// Check if user exists
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION['user_message'] = 'User not found.';
    $_SESSION['user_message_class'] = 'alert alert-danger';
    redirect('users.php');
}

// Delete user (cascade will handle activity_log, email_logs, tasks because of foreign key constraints with ON DELETE CASCADE)
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$id])) {
    logActivity($_SESSION['user_id'], 'Deleted user', "User ID: $id, Username: " . $user['username']);
    $_SESSION['user_message'] = 'User deleted successfully.';
    $_SESSION['user_message_class'] = 'alert alert-success';
} else {
    $_SESSION['user_message'] = 'Failed to delete user.';
    $_SESSION['user_message_class'] = 'alert alert-danger';
}

redirect('users.php');
?>