<?php
session_start();

// Language handling
if (isset($_GET['lang']) && ($_GET['lang'] === 'en' || $_GET['lang'] === 'sk')) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + (86400 * 30), '/');
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect_url);
    exit();
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = isset($_COOKIE['lang']) && ($_COOKIE['lang'] === 'en' || $_COOKIE['lang'] === 'sk') ? $_COOKIE['lang'] : 'sk';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .welcome-card {
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            background-color: white;
            max-width: 500px;
            width: 100%;
        }
        .btn-custom {
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .language-select {
            text-align: center;
            margin-top: 1rem;
        }
        .language-select a {
            margin: 0 0.5rem;
            text-decoration: none;
            font-weight: 600;
            color: #0d6efd;
        }
        .language-select a.active {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="welcome-card text-center">
    <h1 class="mb-3"><?php echo $strings[$lang]['welcome']; ?></h1>
    <p class="mb-4"><?php echo $strings[$lang]['intro']; ?></p>
    <a href="login.php" class="btn btn-primary btn-custom"><?php echo $strings[$lang]['login']; ?></a>
    <a href="register.php" class="btn btn-outline-primary btn-custom"><?php echo $strings[$lang]['register']; ?></a>
    <div class="language-select">
        <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a> |
        <a href="?lang=sk" class="<?php echo $lang === 'sk' ? 'active' : ''; ?>">SK</a>
    </div>
</div>
</body>
</html>
