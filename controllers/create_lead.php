<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Lead.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'source' => $_POST['source'] ?? '',
        'status' => $_POST['status'] ?? 'new'
    ];

    $leadModel = new Lead($pdo);
    if ($leadModel->create($data)) {
        flash('message', 'Lead created successfully!');
    } else {
        flash('message', 'Failed to create lead', 'alert-danger');
    }
}
redirect('../views/leads.php');