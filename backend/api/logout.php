<?php
session_start();
require_once 'backend/api/includes/functions.php';
require_once 'backend/api/classes/Auth.php';
require_once 'backend/api/classes/Logger.php';

$auth = new Auth();
$logger = new Logger();

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Log the user out
    $auth->logout();

    // Log the logout action
    $logger->logAction($user_id, 'logout', 'User logged out');

    // Redirect to the index page or login page
    redirect('login.php', 'Logged out successfully.', 'success');
} else {
    // If there's no user ID in the session, redirect to the login page
    redirect('login.php', 'No user logged in.', 'error');
}
