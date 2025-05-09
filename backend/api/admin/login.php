<?php

use classes\Auth;

session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';

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
// Check if the user is logged in and is an administrator
$is_logged = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;


// Redirect only if both logged in and admin
if ($is_logged && $is_admin) {
   header('Location: index.php');
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


        if (isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN) {
            header('Location: index.php');
            exit;
        } else {
            // Logged in but not admin
            $auth->logout();
            $error = 'You do not have admin privileges.';
        }
    } else {
        $error = 'Invalid username or password.';
    }
}

// Get language from session or default to English
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// Language strings
$strings = [
    'en' => [
        'admin_login' => 'Admin Login',
        'username' => 'Username',
        'password' => 'Password',
        'login_button' => 'Login',
        'back_to_site' => 'Back to Site'
    ],
    'sk' => [
        'admin_login' => 'Prihlásenie Administrátora',
        'username' => 'Používateľské Meno',
        'password' => 'Heslo',
        'login_button' => 'Prihlásiť sa',
        'back_to_site' => 'Späť na Stránku'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $strings[$lang]['admin_login']; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .language-switcher {
            position: absolute;
            top: 10px;
            right: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="language-switcher">
        <div class="btn-group">
            <a href="?lang=en<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm <?php echo $lang === 'en' ? 'btn-primary' : 'btn-outline-primary'; ?>">EN</a>
            <a href="?lang=sk<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm <?php echo $lang === 'sk' ? 'btn-primary' : 'btn-outline-primary'; ?>">SK</a>
        </div>
    </div>

    <div class="login-container">
        <h2 class="text-center mb-4"><?php echo $strings[$lang]['admin_login']; ?></h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="username"><?php echo $strings[$lang]['username']; ?></label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password"><?php echo $strings[$lang]['password']; ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><?php echo $strings[$lang]['login_button']; ?></button>
        </form>

        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>"><?php echo $strings[$lang]['back_to_site']; ?></a>
        </div>
    </div>
</div>
</body>
</html>