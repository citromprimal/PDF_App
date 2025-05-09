<?php
require_once 'config.php';
require_once 'db.php';

// Function to clean input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate a random token
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

// Function to redirect with a message
function redirect($url, $message = '', $type = 'info') {
    error_log('Redirecting to: ' . $url);
    error_log('Called from: ' . debug_backtrace()[0]['file'] . ' on line ' . debug_backtrace()[0]['line']);

    if (!empty($message)) {
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    header('Location: ' . $url);
    exit;
}

// Function to display flash message
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];

        echo '<div class="alert alert-' . $type . '">' . $message . '</div>';

        unset($_SESSION['flash_message']);
    }
}

// Function to get current page URL
function get_current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $current_url;
}

// Function to format date
function format_date($date, $format = 'Y-m-d H:i:s') {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}
?>