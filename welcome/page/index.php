<?php
session_start();

// Language handling
if (isset($_GET['lang']) && ($_GET['lang'] === 'en' || $_GET['lang'] === 'sk')) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + (86400 * 30), '/');
    header("Location: index.php");
    exit();
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $_COOKIE['lang'] ?? 'sk';
}

$lang = $_SESSION['lang'];

$strings = [
    'en' => [
        'welcome' => 'Welcome to PDF Tools',
        'intro' => 'Manage your PDFs easily. Convert, split, merge and more — all in one place.',
        'login' => 'Login',
        'register' => 'Register',
        'features' => 'Available Tools',
        'feature_list' => [
            'Merge PDF', 'Split PDF', 'Compress PDF', 'Rotate PDF',
            'Extract Pages', 'Add Watermark', 'Reorder Pages',
            'Convert to Text', 'Convert Images to PDF', 'Remove Pages'
        ],
    ],
    'sk' => [
        'welcome' => 'Vitajte v PDF nástrojoch',
        'intro' => 'Spravujte svoje PDF jednoducho. Konvertujte, rozdeľujte, zlúčujte a ďalšie — všetko na jednom mieste.',
        'login' => 'Prihlásiť sa',
        'register' => 'Zaregistrovať sa',
        'features' => 'Dostupné nástroje',
        'feature_list' => [
            'Zlúčiť PDF', 'Rozdeliť PDF', 'Komprimovať PDF', 'Otočiť PDF',
            'Extrahovať strany', 'Pridať vodoznak', 'Zmeniť poradie strán',
            'Previesť PDF na text', 'Obrázky do PDF', 'Odstrániť strany'
        ],
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hero,
        .features {
            max-width: 800px;
            width: 100%;
            padding: 2rem;
            background-color: #ffffffc7;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            text-align: center;
        }
        .btn-custom {
            margin: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .feature-card {
            background-color: #f1f3f5;
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            font-weight: 500;
            color: #495057;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
        }

        .language-switcher a {
            text-decoration: none;
            font-weight: 600;
            margin: 0 0.5rem;
        }

        .language-switcher a.active {
            text-decoration: underline;
        }
        .language-switcher {
            margin-top: 1rem;
            text-align: center;
        }

    </style>
</head>
<body>
<div class="main-container">

    <div class="hero">
        <h1 class="mb-3"><?php echo $strings[$lang]['welcome']; ?></h1>
        <p class="lead mb-4"><?php echo $strings[$lang]['intro']; ?></p>
        <a href="login.php" class="btn btn-primary btn-custom"><?php echo $strings[$lang]['login']; ?></a>
        <a href="register.php" class="btn btn-outline-primary btn-custom"><?php echo $strings[$lang]['register']; ?></a>
    </div>

    <div class="features">
        <h3 class="mb-4"><?php echo $strings[$lang]['features']; ?></h3>
        <div class="feature-grid">
            <?php foreach ($strings[$lang]['feature_list'] as $feature): ?>
                <div class="feature-card">
                    <?php echo $feature; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="language-switcher mt-4">
            <a href="?lang=sk" class="<?= $lang === 'sk' ? 'active' : '' ?>">SK</a> |
            <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>
    </div>

</div>

</body>
</html>