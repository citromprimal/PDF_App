<?php

session_start();

require_once '../backend/api/includes/config.php';
require_once '../backend/api/includes/functions.php';
require_once '../backend/api/classes/Auth.php';

error_log("Logout script loaded");

// Logout the user
$auth = new Auth();
$logger = new Logger();

$auth->logout();

$logger->logAction($_SESSION['user_id'], 'logout', 'User ' . $_SESSION['username'] . ' logged out');

// Redirect to login page
redirect(ADMIN_URL . 'login.php');
?>