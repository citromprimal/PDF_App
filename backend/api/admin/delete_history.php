<?php

use classes\Auth;
use PDF_App\backend\api\classes\Logger;

session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Logger.php';

// Language handling - no redirect needed here
if (!isset($_SESSION['lang'])) {
    // Try to get language from cookie
    if (isset($_COOKIE['lang']) && ($_COOKIE['lang'] === 'en' || $_COOKIE['lang'] === 'sk')) {
        $_SESSION['lang'] = $_COOKIE['lang'];
    } else {
        // Default language
        $_SESSION['lang'] = 'en';
    }
}

$lang = $_SESSION['lang'];

// Check if user is logged in and is admin
$auth = new Auth();
if (!$auth->checkSession() || !is_admin()) {
    redirect(ADMIN_URL . 'login.php');
}

// Language strings
$strings = [
    'en' => [
        'history_deleted' => 'All history has been successfully deleted.',
        'history_delete_failed' => 'Failed to delete history. Please try again.'
    ],
    'sk' => [
        'history_deleted' => 'Celá história bola úspešne vymazaná.',
        'history_delete_failed' => 'Nepodarilo sa vymazať históriu. Prosím, skúste znova.'
    ]
];

// Delete all history
$logger = new Logger();
$success = $logger->deleteAllHistory();

if ($success) {
    // Log the action (this will be the first record in the new activity log)
    $logger->logAction($_SESSION['user_id'], 'delete_history', 'Deleted all activity history');

    $_SESSION['flash_message'] = [
        'message' => $strings[$lang]['history_deleted'],
        'type' => 'success'
    ];
} else {
    $_SESSION['flash_message'] = [
        'message' => $strings[$lang]['history_delete_failed'],
        'type' => 'danger'
    ];
}

redirect(ADMIN_URL . 'history.php');
?>