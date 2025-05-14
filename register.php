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
        'pdf_tools' => 'PDF Tools',
        'main_page' => 'Main page',


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
        'pdf_tools' => 'Nástroje PDF',
        'main_page' => 'Hlavná stránka',


    ],
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $strings[$lang]['register_title']; ?> | PDF Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            min-height: 6rem;
        }

        .form-control.is-invalid, .form-control.is-valid {
            background-image: none !important;
            padding-right: 12px !important;
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
                    <a class="nav-link active" href="index.php"><?php echo $strings[$lang]['main_page']; ?></a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link active" href="login.php"><?php echo $strings[$lang]['login_link']; ?></a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link active" href="register.php"><?php echo $strings[$lang]['register_button']; ?></a>
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
            <h3 class="text-center mb-4"><?php echo $strings[$lang]['register_title']; ?></h3>
            <form id="registrationForm" method="post" action="" novalidate>
                <div class="form-group mb-3">
                    <label for="username" class="form-label"><?php echo $strings[$lang]['username']; ?></label>
                    <input type="text" class="form-control" id="username" name="username" required
                           minlength="3" maxlength="50"
                           pattern="^[a-zA-Z0-9_\- ]+$">
                    <div class="invalid-feedback" id="usernameFeedback">
                        <?php echo $lang === 'en' ?
                            'Username must be 3-50 characters (letters, numbers, spaces, -_)' :
                            'Meno musí mať 3-50 znakov (písmená, čísla, medzery, -_)'; ?>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label"><?php echo $strings[$lang]['email']; ?></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback" id="emailFeedback">
                        <?php echo $lang === 'en' ?
                            'Please enter a valid email address' :
                            'Zadajte platnú emailovú adresu'; ?>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="password" class="form-label"><?php echo $strings[$lang]['password']; ?></label>
                    <div class="password-input-container">
                        <input type="password" class="form-control" id="password" name="password" required
                               minlength="8" maxlength="50"
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$">
                    </div>
                    <div class="invalid-feedback" id="passwordFeedback">
                        <?php echo $lang === 'en' ?
                            'Password must be 8-50 characters with at least one uppercase, one lowercase, and one number' :
                            'Heslo musí mať 8-50 znakov, aspoň jedno veľké písmeno, jedno malé písmeno a jednu číslicu'; ?>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label"><?php echo $strings[$lang]['confirm_password']; ?></label>
                    <div class="password-input-container">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="invalid-feedback" id="confirmPasswordFeedback">
                        <?php echo $lang === 'en' ?
                            'Passwords must match' :
                            'Heslá sa musia zhodovať'; ?>
                    </div>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary w-100"><?php echo $strings[$lang]['register_button']; ?></button>
            </form>
            <p class="login-register-link">
                <?php echo $strings[$lang]['already_have_account']; ?>
                <a href="login.php"><?php echo $strings[$lang]['login_link']; ?></a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const form = document.getElementById('registrationForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity(
                '<?php echo $lang === "en" ? "Passwords must match" : "Heslá sa musia zhodovať"; ?>'
            );
            confirmPassword.classList.add('is-invalid');
            document.getElementById('confirmPasswordFeedback').style.display = 'block';
        } else {
            confirmPassword.setCustomValidity('');
            confirmPassword.classList.remove('is-invalid');
            document.getElementById('confirmPasswordFeedback').style.display = 'none';
        }

        form.classList.add('was-validated');
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

        if (field.id === 'password' || field.id === 'confirm_password') {
            if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                document.getElementById('confirmPasswordFeedback').style.display = 'block';
            } else if (confirmPassword.value) {
                confirmPassword.classList.remove('is-invalid');
                document.getElementById('confirmPasswordFeedback').style.display = 'none';
            }
        }
    }

    const fieldsToValidate = ['username', 'email', 'password', 'confirm_password'];
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
</body>
</html>