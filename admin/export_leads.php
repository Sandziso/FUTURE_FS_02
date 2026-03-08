<?php
// admin/export_leads.php
// Export all leads as a CSV file

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include database configuration (adjust path if needed)
require_once '../config/database.php'; // assumes $pdo is defined here

// Set headers to force download of CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=leads_export_' . date('Y-m-d') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Column headings
fputcsv($output, [
    'ID',
    'Name',
    'Email',
    'Phone',
    'Source',
    'Estimated Value',
    'Status',
    'Lead Score',
    'Converted to Client',
    'Created At',
    'Updated At'
]);

// Fetch all leads (you can add WHERE clauses if needed)
$stmt = $pdo->query("
    SELECT 
        id,
        name,
        email,
        phone,
        source,
        estimated_value,
        status,
        lead_score,
        converted_to_client,
        created_at,
        updated_at
    FROM leads
    ORDER BY id
");

// Output each row
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Format estimated value as plain number (no currency symbol)
    fputcsv($output, $row);
}

// Close stream
fclose($output);
exit;