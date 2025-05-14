<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'backend/api/includes/functions.php';
require_once 'backend/api/classes/Auth.php';

$auth = new Auth();



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

if (!$auth->checkSession()) {
    redirect('login.php', 'Please log in to access the dashboard.', 'error');
    exit;
}

$user = $auth->getUserById($_SESSION['user_id']);
if (!$user) {
    redirect('login.php', 'User data not found. Please log in again.', 'error');
    exit;
}

$username = $user['username'];








// Language strings
$strings = [
    'en' => [
        'pdf_tools' => 'PDF Tools',
        'home_nav' => 'Home',
        'admin_panel_nav' => 'Admin Panel',
        'logout_nav_button' => 'Logout',
        'welcome' => 'Welcome, ',
        'this_is_your_persona_dashboard' => 'This is your personal dashboard where you can access your PDF tools.',
    ],
    'sk' => [
        'pdf_tools' => 'Nástroje PDF',
        'home_nav' => 'Domov',
        'admin_panel_nav' => 'Administrátorský panel',
        'logout_nav_button' => 'Odhlásiť sa',
        'welcome' => 'Vitajte, ',
        'this_is_your_persona_dashboard' => 'Toto je váš osobný panel, kde máte prístup k nástrojom PDF.',
    ],
];



?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PDF Tools</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .dashboard-header {
            padding: 2rem 0;
        }
        .btn-admin {
            background-color: #0d6efd;
            color: white;
        }
        .btn-admin:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>




<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><?php echo $strings[$lang]['pdf_tools']; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3">
                    <a class="nav-link active" href="dashboard.php"><?php echo $strings[$lang]['home_nav']; ?></a>
                </li>
                <li class="nav-item me-3">
                    <form action="admin/login.php" method="post">
                        <button type="submit" class="btn btn-admin"><?php echo $strings[$lang]['admin_panel_nav']; ?></button>
                    </form>
                </li>

                <div class="btn-group btn-group-sm">
                    <a href="?lang=en<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>"
                       class="btn <?php echo $lang === 'en' ? 'btn-primary' : 'btn-outline-primary'; ?>">EN</a>
                    <a href="?lang=sk<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>"
                       class="btn <?php echo $lang === 'sk' ? 'btn-primary' : 'btn-outline-primary'; ?>">SK</a>
                </div>


                <li class="nav-item" style="margin-left: 1.5em">
                    <form action="backend/api/logout.php" method="post">
                        <button type="submit" class="btn btn-outline-danger"><?php echo $strings[$lang]['logout_nav_button']; ?></button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container dashboard-header text-center">
    <h1 class="mb-3"><?php echo $strings[$lang]['welcome']; ?> <?= htmlspecialchars($username) ?>!</h1>
    <p class="text-muted"><?php echo $strings[$lang]['this_is_your_persona_dashboard']; ?></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
