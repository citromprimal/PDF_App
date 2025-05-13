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
    setcookie('lang', $_GET['lang'], time() + (86400 * 30), '/');
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    $query_params = $_GET;
    unset($query_params['lang']);
    if (!empty($query_params)) {
        $redirect_url .= '?' . http_build_query($query_params);
    }
    header('Location: ' . $redirect_url);
    exit;
} elseif (!isset($_SESSION['lang'])) {
    if (isset($_COOKIE['lang']) && ($_COOKIE['lang'] === 'en' || $_COOKIE['lang'] === 'sk')) {
        $_SESSION['lang'] = $_COOKIE['lang'];
    } else {
        $_SESSION['lang'] = 'en';
    }
}

$lang = $_SESSION['lang'];

// Redirect if logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? clean_input($_POST['username']) : '';
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (empty($username) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        $auth = new Auth();
        $registration_success = $auth->register($username, $password, $email);

        if ($registration_success) {
            header('Location: login.php');
            exit;
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}

// Language strings
$strings = [
    'en' => [
        'register_title' => 'Register',
        'username' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'register_button' => 'Register',
        'already_have_account' => 'Already have an account?',
        'login_link' => 'Login',
    ],
    'sk' => [
        'register_title' => 'Registrácia',
        'username' => 'Meno',
        'email' => 'Email',
        'password' => 'Heslo',
        'confirm_password' => 'Potvrďte heslo',
        'register_button' => 'Registrovať sa',
        'already_have_account' => 'Už máte účet?',
        'login_link' => 'Prihlásiť sa',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $strings[$lang]['register_title']; ?></title>
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

    <main class="register-main">
        <section class="register-form-container">
            <h2><?php echo $strings[$lang]['register_title']; ?></h2>
            <form method="post" action="" class="register-form">
                <div class="input-group">
                    <label for="username"><?php echo $strings[$lang]['username']; ?></label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="email"><?php echo $strings[$lang]['email']; ?></label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password"><?php echo $strings[$lang]['password']; ?></label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password"><?php echo $strings[$lang]['confirm_password']; ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="button primary-button"><?php echo $strings[$lang]['register_button']; ?></button>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
            <p class="login-register-link">
                <?php echo $strings[$lang]['already_have_account']; ?> <a href="login.html"><?php echo $strings[$lang]['login_link']; ?></a>
            </p>
        </section>
    </main>
</div>
<script src="../script.js"></script>
</body>
</html>
