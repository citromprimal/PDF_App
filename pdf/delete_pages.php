<?php

session_start();

require_once '../backend/api/includes/functions.php';
require_once '../backend/api/classes/Auth.php';

$auth = new Auth();

// Check if the user is logged in. If not, redirect them to the login page.
if (!$auth->checkSession()) {
    redirect('../login.php', 'Please log in to access this tool.', 'error');
    exit;
}

// Get user data (optional, but good practice)
$user = $auth->getUserById($_SESSION['user_id']);
if (!$user) {
    redirect('../login.php', 'User data not found. Please log in again.', 'error');
    exit;
}
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
$strings = [
    'en' => [
        'pdf_tools' => 'PDF Tools',
        'home_nav' => 'Home',
        'admin_panel_nav' => 'Admin Panel',
        'logout_nav_button' => 'Logout',
        'header' => 'Delete PDF Pages',
        'select' => 'Select a PDF file:',
        'pgs_to_delete' => 'Pages to delete (e.g., 1, 3, 5-7):',
        'eg'=>'e.g., 1, 3, 5-7',
        'button' => 'Delete Pages',

    ],
    'sk' => [
        'pdf_tools' => 'Nástroje PDF',
        'home_nav' => 'Domov',
        'admin_panel_nav' => 'Administrátorský panel',
        'logout_nav_button' => 'Odhlásiť sa',
        'header' => 'Odstrániť Strany PDF',
        'select' => 'Vyberte súbor PDF:',
        'pgs_to_delete' => 'Strany na odstránenie (napr. 1, 3, 5-7):',
        'eg'=>'napr. 1, 3, 5-7',
        'button' => 'Odstrániť Strany',
    ],
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete PDF Pages</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            /*font-family: 'Inter', sans-serif;*/
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: bold;
        }

        /* Center only the form section */
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 4rem;
            padding-bottom: 4rem;
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
        h1 { text-align: center; margin-bottom: 1.5rem; color: #1f2937; }
        form { display: flex; flex-direction: column; gap: 1rem; }
        label { font-weight: 600; color: #374151; }
        input[type="file"], input[type="text"] { padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; }
        button { padding: 0.75rem 1rem; background-color: #dc3545; color: #fff; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 600; transition: background-color 0.3s ease; }
        button:hover { background-color: #c82333; }
        #message { margin-top: 1rem; padding: 0.75rem; border-radius: 0.375rem; text-align: center; font-weight: 600; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="../dashboard.php"><?php echo $strings[$lang]['pdf_tools']; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3">
                    <a class="nav-link active" href="../dashboard.php"><?php echo $strings[$lang]['home_nav']; ?></a>
                </li>
                <li class="nav-item me-3">
                    <form action="../admin/login.php" method="post">
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
                    <form action="../backend/user_logout.php" method="post">
                        <button type="submit" class="btn btn-outline-danger"><?php echo $strings[$lang]['logout_nav_button']; ?></button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="main-content">
    <div class="card shadow rounded-4 p-4 w-100" style="max-width: 600px;">
        <h1><?php echo $strings[$lang]['header']; ?></h1>
        <form id="delete-pages-form" enctype="multipart/form-data">
            <label for="file"><?php echo $strings[$lang]['select']; ?></label>
            <input type="file" id="file" name="file" accept=".pdf" required>

            <label for="pages"><?php echo $strings[$lang]['pgs_to_delete']; ?></label>
            <input type="text" id="pages" name="pages" placeholder="<?php echo $strings[$lang]['eg']; ?>" required>

            <button type="submit"><?php echo $strings[$lang]['button']; ?></button>
        </form>
        <div id="message"></div>
    </div>
</div>

<script src="../script.js"></script>
</body>
</html>