<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'backend/api/includes/functions.php';
require_once 'backend/api/classes/Auth.php';

$auth = new Auth();

echo "Session ID: " . session_id() . "<br>"; // Check if session is started
echo "Current User ID from Session: " . ($_SESSION['user_id'] ?? 'No user ID') . "<br>";

// Check if the user is logged in.  If not, redirect them to the login page.
if (!$auth->checkSession()) {
    echo "checkSession() returned false. Redirecting...<br>";
    redirect('login.php', 'Please log in to access the dashboard.', 'error');
    exit; // Add exit after redirect
}

// Get user data
$user = $auth->getUserById($_SESSION['user_id']);
if (!$user) {
    echo "getUserById() returned false.  User data not found.<br>";
    redirect('login.php', 'User data not found. Please log in again.', 'error');
    exit;  // Add exit
}

// Set the user's name
$username = $user['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css"> </head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">PDF Tools</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="dashboard.php">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <form action="../backend/logout.php" method="post">
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
    <p>This is your dashboard.</p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="../script.js"></script> </body>
</html>