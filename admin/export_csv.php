<?php

session_start();
require_once '../backend/api/includes/config.php';
require_once '../backend/api/includes/functions.php';
require_once '../backend/api/classes/Auth.php';
require_once '../backend/api/classes/Logger.php';

// Check if user is logged in and is admin
$auth = new Auth();
if (!$auth->checkSession() || !is_admin()) {
    redirect(ADMIN_URL . 'login.php');
}

// Define English headers (always use English for CSV export)
$headers = [
    'id' => 'ID',
    'username' => 'Username',
    'user_id' => 'User ID',
    'action' => 'Action',
    'details' => 'Details',
    'access_type' => 'Access Type',
    'ip_address' => 'IP Address',
    'city' => 'City',
    'country' => 'Country',
    'created_at' => 'Date and Time'
];

// Get filters from query string
$filters = [];
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $filters['user_id'] = intval($_GET['user_id']);
}
if (isset($_GET['action']) && !empty($_GET['action'])) {
    $filters['action'] = clean_input($_GET['action']);
}
if (isset($_GET['access_type']) && !empty($_GET['access_type']) && $_GET['access_type'] !== 'all') {
    $filters['access_type'] = clean_input($_GET['access_type']);
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = clean_input($_GET['date_from']);
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = clean_input($_GET['date_to']);
}

// Get logs for export
$logger = new Logger();
$logs = $logger->getHistory(10000, 0, $filters); // Get up to 10,000 records

if (empty($logs)) {
    // No records to export
    $_SESSION['flash_message'] = [
        'message' => 'No records to export.',
        'type' => 'warning'
    ];

    redirect(ADMIN_URL . 'history.php');
    exit;
}

// Log the export action
$logger->logAction($_SESSION['user_id'], 'export_history', 'Exported history to CSV');

// Prepare filename
$filename = 'activity_log_' . date('Y-m-d_H-i-s') . '.csv';

// Set headers for file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Define the CSV delimiter
$delimiter = ';';

// Write headers
$csv_headers = [
    $headers['id'],
    $headers['username'],
    $headers['user_id'],
    $headers['action'],
    $headers['details'],
    $headers['access_type'],
    $headers['city'],
    $headers['country'],
    $headers['created_at']
];
fputcsv($output, $csv_headers, $delimiter);

// Write data rows
foreach ($logs as $log) {
    $row = [
        $log['id'],
        $log['username'],
        $log['user_id'],
        $log['action'],
        $log['details'],
        $log['access_type'],
        $log['city'],
        $log['country'],
        $log['created_at']
    ];
    fputcsv($output, $row, $delimiter);
}

// Close the output stream
fclose($output);
exit;
?>
