<?php

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

// Check if the user is logged in.  If not, redirect them to the login page.
if (!$auth->checkSession()) {
    echo "checkSession() returned false. Redirecting...<br>";
    redirect('login.php', 'Please log in to access the dashboard.', 'error');
    exit;
}

// Get user data
$user = $auth->getUserById($_SESSION['user_id']);
if (!$user) {
    echo "getUserById() returned false.  User data not found.<br>";
    redirect('login.php', 'User data not found. Please log in again.', 'error');
    exit;
}

// Set the user's name
$username = $user['username'];
$email = $user['email'];
$api_key = $user['api_key'];

// Language strings
$strings = [
    'en' => [
        'pdf_tools' => 'PDF Tools',
        'home_nav' => 'Home',
        'api_key' => 'API Key',
        'admin_panel_nav' => 'Admin Panel',
        'logout_nav_button' => 'Logout',
        'welcome' => 'Welcome, ',
        'this_is_your_persona_dashboard' => 'This is your personal dashboard where you can access your PDF tools.',
        'email' => 'Your email: ',
        'reg_api_key' => 'Regenerate API Key',
        'merge_pdf' => 'Merge PDF',
        'split_pdf' => 'Split PDF',
        'compress_pdf' => 'Compress PDF',
        'rotate_pdf' => 'Rotate PDF',
        'extract_pdf' => 'Extract PDF pages',
        'conv_img_to_pdf' => 'Convert images to PDF',
        'add_wm_to_pdf' => 'Add watermark to PDF',
        'reorder_pdf' => 'Reorder PDF pages',
        'conv_pdf_to_txt' => 'Convert PDF to text',
        'delete_pdf' => 'Delete PDF pages',
        'usr_man' => 'User Manual',
        'download_usr_man_text' => 'Download the user manual in your preferred language',
        'download_usr_man' => 'Download User Manual',
    ],
    'sk' => [
        'pdf_tools' => 'Nástroje PDF',
        'home_nav' => 'Domov',
        'api_key' => 'API kľúč',
        'admin_panel_nav' => 'Administrátorský panel',
        'logout_nav_button' => 'Odhlásiť sa',
        'welcome' => 'Vitajte, ',
        'this_is_your_persona_dashboard' => 'Toto je váš osobný panel, kde máte prístup k nástrojom PDF.',
        'email' => 'Váš email: ',
        'reg_api_key' => 'Obnoviť kľúč API',
        'merge_pdf' => 'Zlúčiť PDF',
        'split_pdf' => 'Rozdeliť PDF',
        'compress_pdf' => 'Komprimovať pdf',
        'rotate_pdf' => 'Otočiť PDF',
        'extract_pdf' => 'Extrahovať strany PDF',
        'conv_img_to_pdf' => 'Previesť obrázky do PDF',
        'add_wm_to_pdf' => 'Pridať vodoznak do PDF',
        'reorder_pdf' => 'Zmeniť poradie stránok PDF',
        'conv_pdf_to_txt' => 'Previesť PDF na text',
        'delete_pdf' => 'Odstrániť strany PDF',
        'usr_man' => 'Používateľská príručka',
        'download_usr_man_text' => 'Stiahnite si používateľskú príručku vo vami preferovanom jazyku',
        'download_usr_man' => 'Stiahnuť používateľskú príručku',
    ],
];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PDF Tools</title>
    <link rel="icon" href="assets/favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background: #ffffff;
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
        .btn-outline-dark:hover {
            background-color: #343a40;
            color: white;
        }
    </style>
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
                    <form action="backend/user_logout.php" method="post">
                        <button type="submit" class="btn btn-outline-danger"><?php echo $strings[$lang]['logout_nav_button']; ?></button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="card shadow-lg rounded-4 p-5 text-center bg-light">
        <h1 class="mb-3 display-5 fw-bold text-primary">
            <?= $strings[$lang]['welcome']; ?> <?= htmlspecialchars($username) ?>!
        </h1>
        <p class="text-muted mb-1"><?= $strings[$lang]['this_is_your_persona_dashboard']; ?></p>
        <p class="text-muted mb-1"><strong><?= $strings[$lang]['email']; ?></strong> <?= htmlspecialchars($email) ?></p>
        <p class="text-muted"><strong><?= $strings[$lang]['api_key']; ?>:</strong> <code><?= htmlspecialchars($api_key) ?></code></p>

        <form id="apiKeyForm" method="post" class="mt-3">
            <input type="hidden" id="user_id" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
            <button type="submit" class="btn btn-success px-4">
                <?= $strings[$lang]['reg_api_key']; ?>
            </button>
            <div id="message" class="mt-2"></div>
        </form>

        <hr class="my-5">

        <h2 class="mb-3 h4"><?= $strings[$lang]['usr_man']; ?></h2>
        <p class="text-muted mb-4"><?= $strings[$lang]['download_usr_man_text']; ?></p>
        <div class="d-flex justify-content-center">
            <a href="user_manual.php" target="_blank" class="btn btn-outline-primary px-4 py-3 fw-semibold">
                📘 <?= $strings[$lang]['download_usr_man']; ?>
            </a>
        </div>
        <br>

        <div class="row g-3">
            <?php
            $tools = [
                'merge_pdf' => 'merge_pdf.php',
                'split_pdf' => 'split_pdf.php',
                'compress_pdf' => 'compress_pdf.php',
                'rotate_pdf' => 'rotate_pdf.php',
                'extract_pdf' => 'extract_pages.php',
                'conv_img_to_pdf' => 'images_to_pdf.php',
                'add_wm_to_pdf' => 'add_watermark.php',
                'reorder_pdf' => 'reorder_pages.php',
                'conv_pdf_to_txt' => 'pdf_to_text.php',
                'delete_pdf' => 'delete_pages.php'
            ];
            foreach ($tools as $key => $link) {
                echo '<div class="col-md-4">';
                echo '<a href="pdf/' . $link . '" class="btn btn-outline-dark w-100 py-3 fw-semibold">';
                echo $strings[$lang][$key];
                echo '</a>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>