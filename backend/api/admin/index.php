<?php

use classes\Auth;
use includes\Database;

session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';

// Check if user is logged in and is admin
$auth = new Auth();
if (!$auth->checkSession() || !is_admin()) {
    redirect(ADMIN_URL . 'login.php');
}

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

// Language strings
$strings = [
    'en' => [
        'admin_dashboard' => 'Admin Dashboard',
        'welcome' => 'Welcome',
        'nav_dashboard' => 'Dashboard',
        'nav_history' => 'User Activity History',
        'nav_users' => 'Users Management',
        'nav_settings' => 'Settings',
        'nav_logout' => 'Logout',
        'dashboard_title' => 'Dashboard Overview',
        'total_users' => 'Total Users',
        'total_actions' => 'Total Actions',
        'actions_today' => 'Actions Today',
        'api_usage' => 'API Usage',
        'frontend_usage' => 'Frontend Usage',
        'recent_activity' => 'Recent Activity',
        'view_all' => 'View All Activity',
        'language' => 'Language',
    ],
    'sk' => [
        'admin_dashboard' => 'Administrátorský Panel',
        'welcome' => 'Vitajte',
        'nav_dashboard' => 'Panel',
        'nav_history' => 'História Aktivity Používateľov',
        'nav_users' => 'Správa Používateľov',
        'nav_settings' => 'Nastavenia',
        'nav_logout' => 'Odhlásiť sa',
        'dashboard_title' => 'Prehľad Panela',
        'total_users' => 'Celkový Počet Používateľov',
        'total_actions' => 'Celkový Počet Akcií',
        'actions_today' => 'Akcie Dnes',
        'api_usage' => 'Použitie API',
        'frontend_usage' => 'Použitie Frontendu',
        'recent_activity' => 'Nedávna Aktivita',
        'view_all' => 'Zobraziť Všetky Aktivity',
        'language' => 'Jazyk',
    ]
];

// Get statistics for the dashboard
$db = Database::getInstance();
$conn = $db->getConnection();

// Total users
$stmt = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

// Total actions
$stmt = $conn->query("SELECT COUNT(*) as count FROM activity_log");
$total_actions = $stmt->fetch()['count'];

// Actions today
$stmt = $conn->query("SELECT COUNT(*) as count FROM activity_log WHERE DATE(created_at) = CURDATE()");
$actions_today = $stmt->fetch()['count'];

// API usage
$stmt = $conn->query("SELECT COUNT(*) as count FROM activity_log WHERE access_type = 'api'");
$api_usage = $stmt->fetch()['count'];

// Frontend usage
$stmt = $conn->query("SELECT COUNT(*) as count FROM activity_log WHERE access_type = 'frontend'");
$frontend_usage = $stmt->fetch()['count'];

// Recent activity
$stmt = $conn->query("SELECT al.*, u.username FROM activity_log al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");
$recent_activity = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $strings[$lang]['admin_dashboard']; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar a:hover {
            color: white;
            text-decoration: none;
        }
        .sidebar .active {
            background-color: #007bff;
            color: white;
        }
        .main-content {
            padding: 20px;
        }
        .card-stats {
            border-left: 5px solid #007bff;
        }
        .stats-icon {
            font-size: 3rem;
            opacity: 0.3;
        }
        .language-switcher {
            position: absolute;
            top: 10px;
            right: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <div class="py-4 px-3 mb-4 bg-dark">
                <h5><?php echo APP_NAME; ?></h5>
                <div class="text-muted"><?php echo $strings[$lang]['welcome']; ?>, <?php echo $_SESSION['username']; ?></div>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active py-3 px-3">
                        <i class="fas fa-tachometer-alt mr-2"></i> <?php echo $strings[$lang]['nav_dashboard']; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link py-3 px-3">
                        <i class="fas fa-history mr-2"></i> <?php echo $strings[$lang]['nav_history']; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link py-3 px-3">
                        <i class="fas fa-sign-out-alt mr-2"></i> <?php echo $strings[$lang]['nav_logout']; ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-md-10 main-content">
            <div class="language-switcher">
                <div class="btn-group">
                    <a href="?lang=en<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm <?php echo $lang === 'en' ? 'btn-primary' : 'btn-outline-primary'; ?>">EN</a>
                    <a href="?lang=sk<?php echo !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'lang=') === false ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm <?php echo $lang === 'sk' ? 'btn-primary' : 'btn-outline-primary'; ?>">SK</a>
                </div>
            </div>

            <h2 class="mb-4"><?php echo $strings[$lang]['dashboard_title']; ?></h2>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5">
                                    <div class="stats-icon text-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <h5 class="card-title"><?php echo $strings[$lang]['total_users']; ?></h5>
                                    <h3><?php echo $total_users; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5">
                                    <div class="stats-icon text-success">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <h5 class="card-title"><?php echo $strings[$lang]['total_actions']; ?></h5>
                                    <h3><?php echo $total_actions; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5">
                                    <div class="stats-icon text-warning">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <h5 class="card-title"><?php echo $strings[$lang]['actions_today']; ?></h5>
                                    <h3><?php echo $actions_today; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <?php echo $strings[$lang]['api_usage']; ?> vs <?php echo $strings[$lang]['frontend_usage']; ?>
                        </div>
                        <div class="card-body">
                            <canvas id="usageChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <?php echo $strings[$lang]['recent_activity']; ?>
                            <a href="history.php" class="btn btn-sm btn-outline-primary"><?php echo $strings[$lang]['view_all']; ?></a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_activity as $activity): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="badge badge-<?php echo $activity['access_type'] === 'api' ? 'info' : 'success'; ?>"><?php echo $activity['access_type']; ?></span>
                                                <strong><?php echo htmlspecialchars($activity['username']); ?></strong>: <?php echo htmlspecialchars($activity['action']); ?>
                                                <?php if ($activity['details']): ?>
                                                    <small class="text-muted d-block"><?php echo htmlspecialchars($activity['details']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <small class="text-muted"><?php echo format_date($activity['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($recent_activity)): ?>
                                    <li class="list-group-item text-center text-muted">No recent activity</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Usage chart
    const ctx = document.getElementById('usageChart').getContext('2d');
    const usageChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['API', 'Frontend'],
            datasets: [{
                data: [<?php echo $api_usage; ?>, <?php echo $frontend_usage; ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });
</script>
</body>
</html>