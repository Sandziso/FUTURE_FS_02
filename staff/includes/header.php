<?php
// staff/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

// Ensure only staff (or non-admin) can access staff area
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'admin') {
    // If admin tries to access staff area, redirect to admin dashboard
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
        exit;
    }
    // If not logged in, redirect to login
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo APP_NAME; ?> - Staff</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Staff CSS (can reuse admin CSS or create a staff-specific one) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin/css/admin.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <!-- Page Content -->
    <div id="page-content-wrapper" class="w-100">
        <!-- Topbar -->
        <?php include 'topbar.php'; ?>
        <!-- Main Container -->
        <div class="container-fluid px-4 py-3">