<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Lead.php';

$id = $_GET['id'] ?? 0;
if ($id) {
    $leadModel = new Lead($pdo);
    if ($leadModel->delete($id)) {
        flash('message', 'Lead deleted.');
    } else {
        flash('message', 'Deletion failed.', 'alert-danger');
    }
}
redirect('../views/leads.php');