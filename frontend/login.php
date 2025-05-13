<?php

session_start();
require_once 'backend/api/includes/config.php';
require_once 'backend/api/includes/functions.php';
require_once 'backend/api/classes/Auth.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Language handling
if (isset($_GET['lang']) && ($_GET['lang'] === 'en' || $_GET['lang'] === 'sk')) {
    $_SESSION['lang'] = $_GET['lang'];

    // Save lang in cookie for 30 days
    setcookie('lang', $_GET['lang'], time() + (86400 * 30), '/');

    // Redirect to the same page without the lang parameter to avoid issues with repeated form submissions
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    $query_params = $_GET;
    unset($query_params['lang']);

    if (!empty($query_params)) {
        $redirect_url .= '?' . http_build_query($query_params);
    }

    header('Location: ' . $redirect_url);
    exit;
} elseif (!isset($_SESSION['lang'])) {
    // Try to get language from cookie
    if (isset($_COOKIE['lang']) && ($_COOKIE['lang'] === 'en' || $_COOKIE['lang'] === 'sk')) {
        $_SESSION['lang'] = $_COOKIE['lang'];
    } else {
        // Default language
        $_SESSION['lang'] = 'en';
    }
}

$lang = $_SESSION['lang'];

// Redirect only if both logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? clean_input($_POST['username']) : '';
    $password = $_POST['password'] ?? '';

    $auth = new Auth();
    $login_success = $auth->login($username, $password);

    if ($login_success) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// Get language from session or default to English
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// Language strings
$strings = [
    'en' => [
        'user_login' => 'User Login',
        'username' => 'Username',
        'password' => 'Password',
        'login_button' => 'Login',
        'back_to_site' => 'Back to Site'
    ],
    'sk' => [
        'user_login' => 'Prihlásenie Používateľa',
        'username' => 'Používateľské Meno',
        'password' => 'Heslo',
        'login_button' => 'Prihlásiť sa',
        'back_to_site' => 'Späť na Stránku'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
    <div class="language-switcher">
        <div class="btn-group">
            <a href="?lang=en<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm <?php echo $lang === 'en' ? 'btn-primary' : 'btn-outline-primary'; ?>">EN</a>
            <a href="?lang=sk<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm <?php echo $lang === 'sk' ? 'btn-primary' : 'btn-outline-primary'; ?>">SK</a>
        </div>
    </div>

    <main class="login-main">
        <section class="login-form-container">
            <h2><?php echo $strings[$lang]['user_login']; ?></h2>
            <form method="post" action="" class="login-form">
                <div class="input-group">
                    <label for="username"><?php echo $strings[$lang]['username']; ?></label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password"><?php echo $strings[$lang]['password']; ?></label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="button primary-button"><?php echo $strings[$lang]['login_button']; ?></button>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
            <p class="login-register-link">
                Don't have an account? <a href="register.php">Register</a>
            </p>
        </section>
    </main>
</div>
<script src="../script.js"></script>
</body>
</html>
