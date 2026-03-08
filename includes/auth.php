<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    // Calculate the correct path to root login.php
    $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // Go up one level if we are in a subfolder (admin/ or staff/)
    $up = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'admin' || basename(dirname($_SERVER['SCRIPT_NAME'])) === 'staff') ? '../' : '';
    header("Location: " . $root . $dir . "/" . $up . "login.php");
    exit;
}
?>