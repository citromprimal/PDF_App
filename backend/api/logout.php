<?php

use classes\Auth;

session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/Auth.php';
error_log("Logout script loaded");

// Logout the user
$auth = new Auth();
$auth->logout();

// Redirect to login page
redirect(ADMIN_URL . 'login.php');
?>