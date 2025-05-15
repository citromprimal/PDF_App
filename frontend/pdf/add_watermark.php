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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Text Watermark</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 2rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: 90%; max-width: 600px; margin-top: 2rem; }
        h1 { text-align: center; margin-bottom: 1.5rem; color: #1f2937; }
        form { display: flex; flex-direction: column; gap: 1rem; }
        label { font-weight: 600; color: #374151; }
        input[type="file"], input[type="text"] { padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; }
        button { padding: 0.75rem 1rem; background-color: #ff9800; color: #fff; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 600; transition: background-color 0.3s ease; }
        button:hover { background-color: #f57c00; }
        #message { margin-top: 1rem; padding: 0.75rem; border-radius: 0.375rem; text-align: center; font-weight: 600; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
<div class="container">
    <h1>Add Text Watermark</h1>
    <form id="add-watermark-form" enctype="multipart/form-data">
        <label for="file">Select a PDF file:</label>
        <input type="file" id="file" name="file" accept=".pdf" required>

        <label for="text">Watermark Text:</label>
        <input type="text" id="text" name="text" placeholder="Enter watermark text" required>

        <button type="submit">Add Watermark</button>
    </form>
    <div id="message"></div>
</div>

<script src="../script.js"></script>
</body>
</html>