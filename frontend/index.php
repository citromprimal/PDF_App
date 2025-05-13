<?php
session_start();

// Language handling
if (isset($_GET['lang']) && ($_GET['lang'] === 'en' || $_GET['lang'] === 'sk')) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + (86400 * 30), '/'); // Cookie for 30 days
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect_url);
    exit();
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = isset($_COOKIE['lang']) && ($_COOKIE['lang'] === 'en' || $_COOKIE['lang'] === 'sk') ? $_COOKIE['lang'] : 'sk'; // Default to Slovak
}

$lang = $_SESSION['lang'];

$strings = [
    'en' => [
        'welcome' => 'Welcome',
        'intro' => 'If you want to use the features, please log in or register.',
        'login' => 'Login',
        'register' => 'Register',
    ],
    'sk' => [
        'welcome' => 'Vitajte',
        'intro' => 'Ak chcete používať funkcie, prosím, prihláste sa alebo sa zaregistrujte.',
        'login' => 'Prihlásiť sa',
        'register' => 'Zaregistrovať sa',
    ],
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $strings[$lang]['welcome']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1><?php echo $strings[$lang]['welcome']; ?></h1>
    <p><?php echo $strings[$lang]['intro']; ?></p>
    <div class="buttons">
        <a href="login.php" class="button primary-button"><?php echo $strings[$lang]['login']; ?></a>
        <a href="register.php" class="button secondary-button"><?php echo $strings[$lang]['register']; ?></a>
    </div>
    <div class="language-select">
        <a href="?lang=sk" class="<?php echo $lang === 'sk' ? 'active' : ''; ?>">SK</a>
        <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
</div>
</body>
</html>
