<?php

session_start();

require_once 'backend/api/includes/config.php';
require_once 'backend/api/includes/functions.php';
require_once 'backend/api/classes/Auth.php';

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

// Get language from session or default to English
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// Language strings
$strings = [
    'en' => [
        'user_login' => 'User Login',
        'username' => 'Username',
        'password' => 'Password',
        'login_button' => 'Login',
        'back_to_site' => 'Back to Site',
        'pdf_tools' => 'PDF Tools',

        'register_button' => 'Register',
        'login_link' => 'Login',
        'dont_have_an_account' => 'Do not have an account?',
        'main_page' => 'Main page',

    ],
    'sk' => [
        'user_login' => 'Prihlásenie Používateľa',
        'username' => 'Používateľské Meno',
        'password' => 'Heslo',
        'login_button' => 'Prihlásiť sa',
        'back_to_site' => 'Späť na Stránku',
        'pdf_tools' => 'Nástroje PDF',

        'register_button' => 'Registrovať sa',
        'already_have_account' => 'Už máte účet?',
        'login_link' => 'Prihlásiť sa',
        'dont_have_an_account' => 'Nemáte vytvorené konto?',
        'main_page' => 'Hlavná stránka',

    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/favicon.png" type="image/x-icon">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #f5f7fa, #c3cfe2);
            min-height: 100vh;
        }

        .main-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 72px);
            padding-top: 20px;
            padding-bottom: 20px;
        }


        .card {
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            width: 100%;
        }

        .form-label {
            font-weight: 600;
        }
        .language-switcher {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        .login-register-link {
            margin-top: 1rem;
            text-align: center;
        }


        .form-group {
            margin-bottom: 1.5rem;
            min-height: 5.5rem;
            position: relative;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .is-valid {
            border-color: #198754 !important;
        }

        .invalid-feedback {
            position: absolute;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
            display: none;
        }

        .form-control.is-invalid, .form-control.is-valid {
            background-image: none !important;
            padding-right: 12px !important;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 5;
        }

        .password-input-container {
            position: relative;
        }

        @media (min-width: 576px) {
            .card {
                max-width: 500px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><?php echo $strings[$lang]['pdf_tools']; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3">
                    <a class="nav-link" href="index.php"><?php echo $strings[$lang]['main_page']; ?></a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link active" href="login.php"><?php echo $strings[$lang]['login_link']; ?></a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link" href="register.php"><?php echo $strings[$lang]['register_button']; ?></a>
                </li>
                <div class="btn-group btn-group-sm">
                    <a href="?lang=en<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>"
                       class="btn <?php echo $lang === 'en' ? 'btn-primary' : 'btn-outline-primary'; ?>">EN</a>
                    <a href="?lang=sk<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>"
                       class="btn <?php echo $lang === 'sk' ? 'btn-primary' : 'btn-outline-primary'; ?>">SK</a>
                </div>
            </ul>
        </div>
    </div>
</nav>

<div class="main-wrapper container">
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <h3 class="text-center mb-4"><?php echo $strings[$lang]['user_login']; ?></h3>
            <form id="loginForm" method="post" action="" novalidate>
                <div class="form-group">
                    <label for="username" class="form-label"><?php echo $strings[$lang]['username']; ?></label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div class="invalid-feedback" id="usernameFeedback">
                        <?php echo $lang === 'en' ? 'Username is required' : 'Používateľské meno je povinné'; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label"><?php echo $strings[$lang]['password']; ?></label>
                    <div class="password-input-container">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                    <div class="invalid-feedback" id="passwordFeedback">
                        <?php echo $lang === 'en' ? 'Password is required' : 'Heslo je povinné'; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100"><?php echo $strings[$lang]['login_button']; ?></button>

                <?php if ($error): ?>
                    <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>

            <p class="login-register-link mt-3">
                <?php echo $strings[$lang]['dont_have_an_account']; ?>
                <a href="register.php"><?php echo $lang === 'en' ? 'Register' : 'Registrovať sa'; ?></a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const form = document.getElementById('loginForm');
    const username = document.getElementById('username');
    const password = document.getElementById('password');

    form.addEventListener('submit', function(event) {
        let valid = true;

        if (username.value.trim() === '') {
            username.classList.add('is-invalid');
            document.getElementById('usernameFeedback').style.display = 'block';
            valid = false;
        } else {
            username.classList.remove('is-invalid');
            username.classList.add('is-valid');
            document.getElementById('usernameFeedback').style.display = 'none';
        }

        if (password.value.trim() === '') {
            password.classList.add('is-invalid');
            document.getElementById('passwordFeedback').style.display = 'block';
            valid = false;
        } else {
            password.classList.remove('is-invalid');
            password.classList.add('is-valid');
            document.getElementById('passwordFeedback').style.display = 'none';
        }

        if (!valid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });

    function validateField(field) {
        const feedbackElement = document.getElementById(`${field.id}Feedback`);

        if (field.checkValidity()) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            feedbackElement.style.display = 'none';
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            feedbackElement.style.display = 'block';
        }
    }

    const fieldsToValidate = ['username', 'password'];
    fieldsToValidate.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        field.addEventListener('input', function() {
            validateField(this);
        });

        field.addEventListener('blur', function() {
            validateField(this);
        });
    });
</script>
<script src="script.js"></script>
</body>
</html>
