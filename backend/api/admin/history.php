<?php

use classes\Auth;
use includes\Database;
use PDF_App\backend\api\classes\Logger;

session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Logger.php';
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

// Check if user is logged in and is admin
$auth = new Auth();
if (!$auth->checkSession() || !is_admin()) {
    redirect(ADMIN_URL . 'login.php');
}

// Get language from session or default to English
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// Language strings
$strings = [
    'en' => [
        'activity_history' => 'User Activity History',
        'welcome' => 'Welcome',
        'nav_dashboard' => 'Dashboard',
        'nav_history' => 'User Activity History',
        'nav_users' => 'Users Management',
        'nav_settings' => 'Settings',
        'nav_logout' => 'Logout',
        'filter_title' => 'Filter History',
        'filter_user' => 'User',
        'filter_action' => 'Action',
        'filter_access_type' => 'Access Type',
        'filter_date_from' => 'Date From',
        'filter_date_to' => 'Date To',
        'filter_button' => 'Apply Filter',
        'filter_reset' => 'Reset Filter',
        'export_csv' => 'Export to CSV',
        'delete_all' => 'Delete All History',
        'delete_confirm' => 'Are you sure you want to delete all history? This action cannot be undone.',
        'option_all' => 'All',
        'option_frontend' => 'Frontend',
        'option_api' => 'API',
        'table_id' => 'ID',
        'table_user' => 'User',
        'table_action' => 'Action',
        'table_details' => 'Details',
        'table_access_type' => 'Access Type',
        'table_location' => 'Location',
        'table_date' => 'Date',
        'confirm_title' => 'Confirm Action',
        'confirm_yes' => 'Yes, Delete All',
        'confirm_no' => 'Cancel',
        'no_records' => 'No records found',
        'language' => 'Language',
        'showing' => 'Showing',
        'to' => 'to',
        'of' => 'of',
        'entries' => 'entries',
        'previous' => 'Previous',
        'next' => 'Next',
    ],
    'sk' => [
        'activity_history' => 'História Aktivity Používateľov',
        'welcome' => 'Vitajte',
        'nav_dashboard' => 'Panel',
        'nav_history' => 'História Aktivity Používateľov',
        'nav_users' => 'Správa Používateľov',
        'nav_settings' => 'Nastavenia',
        'nav_logout' => 'Odhlásiť sa',
        'filter_title' => 'Filtrovať Históriu',
        'filter_user' => 'Používateľ',
        'filter_action' => 'Akcia',
        'filter_access_type' => 'Typ Prístupu',
        'filter_date_from' => 'Dátum Od',
        'filter_date_to' => 'Dátum Do',
        'filter_button' => 'Použiť Filter',
        'filter_reset' => 'Resetovať Filter',
        'export_csv' => 'Exportovať do CSV',
        'delete_all' => 'Vymazať Celú Históriu',
        'delete_confirm' => 'Ste si istí, že chcete vymazať celú históriu? Túto akciu nemožno vrátiť späť.',
        'option_all' => 'Všetky',
        'option_frontend' => 'Frontend',
        'option_api' => 'API',
        'table_id' => 'ID',
        'table_user' => 'Používateľ',
        'table_action' => 'Akcia',
        'table_details' => 'Detaily',
        'table_access_type' => 'Typ Prístupu',
        'table_location' => 'Lokalita',
        'table_date' => 'Dátum',
        'confirm_title' => 'Potvrdiť Akciu',
        'confirm_yes' => 'Áno, Vymazať Všetko',
        'confirm_no' => 'Zrušiť',
        'no_records' => 'Neboli nájdené žiadne záznamy',
        'language' => 'Jazyk',
        'showing' => 'Zobrazujem',
        'to' => 'do',
        'of' => 'z',
        'entries' => 'záznamov',
        'previous' => 'Predchádzajúce',
        'next' => 'Ďalšie',
    ]
];

// Pagination settings
$items_per_page = 15;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $items_per_page;

// Get filters
$filters = [];
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $filters['user_id'] = intval($_GET['user_id']);
}
if (isset($_GET['action']) && !empty($_GET['action'])) {
    $filters['action'] = clean_input($_GET['action']);
}
if (isset($_GET['access_type']) && !empty($_GET['access_type']) && $_GET['access_type'] !== 'all') {
    $filters['access_type'] = clean_input($_GET['access_type']);
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = clean_input($_GET['date_from']);
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = clean_input($_GET['date_to']);
}

// Get data
$logger = new Logger();
$history = $logger->getHistory($items_per_page, $offset, $filters);
$total_records = $logger->getTotalHistoryCount($filters);
$total_pages = ceil($total_records / $items_per_page);

// Get users for filter dropdown
$db = Database::getInstance();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT id, username FROM users ORDER BY username");
$stmt->execute();
$users = $stmt->fetchAll();

// Get unique actions for filter dropdown
$stmt = $conn->prepare("SELECT DISTINCT action FROM activity_log ORDER BY action");
$stmt->execute();
$actions = [];
while ($row = $stmt->fetch()) {
    $actions[] = $row['action'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $strings[$lang]['activity_history']; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        .table-responsive {
            overflow-x: auto;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .pagination-info {
            margin-top: 20px;
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
                    <a href="index.php" class="nav-link py-3 px-3">
                        <i class="fas fa-tachometer-alt mr-2"></i> <?php echo $strings[$lang]['nav_dashboard']; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link active py-3 px-3">
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

            <h2 class="mb-4"><?php echo $strings[$lang]['activity_history']; ?></h2>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show">
                    <?php echo $_SESSION['flash_message']['message']; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <?php echo $strings[$lang]['filter_title']; ?>
                </div>
                <div class="card-body">
                    <form method="get" action="" class="row">
                        <div class="form-group col-md-2">
                            <label for="user_id"><?php echo $strings[$lang]['filter_user']; ?></label>
                            <select name="user_id" id="user_id" class="form-control">
                                <option value=""><?php echo $strings[$lang]['option_all']; ?></option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo (isset($filters['user_id']) && $filters['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="action"><?php echo $strings[$lang]['filter_action']; ?></label>
                            <select name="action" id="action" class="form-control">
                                <option value=""><?php echo $strings[$lang]['option_all']; ?></option>
                                <?php foreach ($actions as $action): ?>
                                    <option value="<?php echo htmlspecialchars($action); ?>" <?php echo (isset($filters['action']) && $filters['action'] == $action) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($action); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="access_type"><?php echo $strings[$lang]['filter_access_type']; ?></label>
                            <select name="access_type" id="access_type" class="form-control">
                                <option value="all"><?php echo $strings[$lang]['option_all']; ?></option>
                                <option value="frontend" <?php echo (isset($filters['access_type']) && $filters['access_type'] == 'frontend') ? 'selected' : ''; ?>>
                                    <?php echo $strings[$lang]['option_frontend']; ?>
                                </option>
                                <option value="api" <?php echo (isset($filters['access_type']) && $filters['access_type'] == 'api') ? 'selected' : ''; ?>>
                                    <?php echo $strings[$lang]['option_api']; ?>
                                </option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="date_from"><?php echo $strings[$lang]['filter_date_from']; ?></label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo isset($filters['date_from']) ? $filters['date_from'] : ''; ?>">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="date_to"><?php echo $strings[$lang]['filter_date_to']; ?></label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo isset($filters['date_to']) ? $filters['date_to'] : ''; ?>">
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2"><?php echo $strings[$lang]['filter_button']; ?></button>
                            <a href="history.php" class="btn btn-secondary"><?php echo $strings[$lang]['filter_reset']; ?></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mb-3 d-flex justify-content-between">
                <div>
                    <a href="export_csv.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-success">
                        <i class="fas fa-file-csv mr-1"></i> <?php echo $strings[$lang]['export_csv']; ?>
                    </a>
                </div>
                <div>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                        <i class="fas fa-trash-alt mr-1"></i> <?php echo $strings[$lang]['delete_all']; ?>
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                            <tr>
                                <th><?php echo $strings[$lang]['table_id']; ?></th>
                                <th><?php echo $strings[$lang]['table_user']; ?></th>
                                <th><?php echo $strings[$lang]['table_action']; ?></th>
                                <th><?php echo $strings[$lang]['table_details']; ?></th>
                                <th><?php echo $strings[$lang]['table_access_type']; ?></th>
                                <th><?php echo $strings[$lang]['table_location']; ?></th>
                                <th><?php echo $strings[$lang]['table_date']; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($history)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4"><?php echo $strings[$lang]['no_records']; ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($history as $row): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['action']); ?></td>
                                        <td><?php echo htmlspecialchars($row['details']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row['access_type'] === 'api' ? 'info' : 'success'; ?>">
                                                <?php echo $row['access_type']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['city'] . ', ' . $row['country']); ?></td>
                                        <td><?php echo format_date($row['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center pagination-info">
                    <div>
                        <?php echo $strings[$lang]['showing']; ?> <?php echo min($offset + 1, $total_records); ?> <?php echo $strings[$lang]['to']; ?> <?php echo min($offset + $items_per_page, $total_records); ?> <?php echo $strings[$lang]['of']; ?> <?php echo $total_records; ?> <?php echo $strings[$lang]['entries']; ?>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo $query_string; ?>">
                                        <?php echo $strings[$lang]['previous']; ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $query_string; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo $query_string; ?>">
                                        <?php echo $strings[$lang]['next']; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel"><?php echo $strings[$lang]['confirm_title']; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0"><?php echo $strings[$lang]['delete_confirm']; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $strings[$lang]['confirm_no']; ?></button>
                <a href="delete_history.php" class="btn btn-danger"><?php echo $strings[$lang]['confirm_yes']; ?></a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize date pickers
    flatpickr("#date_from", {
        dateFormat: "Y-m-d"
    });
    flatpickr("#date_to", {
        dateFormat: "Y-m-d"
    });
</script>
</body>
</html>
