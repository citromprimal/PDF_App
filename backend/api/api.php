<?php
// api.php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/Auth.php';
require_once 'classes/Logger.php';
require_once 'classes/GeoLocation.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get database connection
$db = Database::getInstance()->getConnection();

$auth = new Auth();
$logger = new Logger();
$geoLocation = new GeoLocation();

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$route = isset($_GET['route']) ? explode('/', $_GET['route']) : [];

function isApiRequest(): bool {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return strpos($accept, 'application/json') !== false ||
        strpos($accept, 'application/xml') !== false;
}

switch ($method) {
    case 'GET':
        if ($route[0] === 'user') {
            if (count($route) == 2 && is_numeric($route[1])) {
                http_response_code(200);
                echo json_encode($auth->getUserById($route[1]));
            }
            break;
        } elseif ($route[0] === 'history') {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            $filters = [];
            if (isset($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
            if (isset($_GET['action'])) $filters['action'] = $_GET['action'];
            if (isset($_GET['access_type'])) $filters['access_type'] = $_GET['access_type'];
            if (isset($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
            if (isset($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

            $history = $logger->getHistory($limit, $offset, $filters);
            http_response_code(200);
            echo json_encode($history);
            break;
        }
    case 'POST':
        if ($route[0] === 'login') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['username']) || !isset($data['password'] )) {
                http_response_code(400);
                echo json_encode(['message' => 'Missing username or password']);
                break;
            }

            if ($auth->login($data['username'], $data['password'])) {
                http_response_code(200);
                echo json_encode(['message' => 'Login successful']);
                break;
            } else {
                http_response_code(401);
                echo json_encode(['message' => 'Invalid username or password']);
                break;
            }
        } elseif ($route[0] === 'register') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['username']) || !isset($data['password']) || !isset($data['email'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Missing username or email or password']);
                break;
            }
            $username = $data['username'];
            $email = $data['email'];
            $password = $data['password'];

            $new_user = $auth->register($username, $password, $email);

            $access_type = isApiRequest() ? 'api' : 'frontend';
            $logger->logAction($new_user, 'register', 'User registered', null, $access_type);

            http_response_code(201);
            $response_data = [
                'message' => "Created successfully",
                'user' => $new_user
            ];
            echo json_encode($response_data);
            break;
        } elseif ($route[0] === 'logout') {
            session_start();
            $user_id = $_SESSION['user_id'] ?? null;

            if (!$user_id) {
                http_response_code(400);
                echo json_encode(['message' => 'User not logged in']);
                break;
            }
            $auth->logout();

            $access_type = isApiRequest() ? 'API' : 'Frontend';
            $logger->logAction($user_id, 'logout', 'User logged out', $access_type);

            http_response_code(200);
            echo json_encode(['message' => 'Logout successful']);
            break;
        } elseif ($route[0] === 'regenerate-api-key') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['user_id'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Missing user ID']);
                break;
            }
            $user_id = $data['user_id'];
            $new_api_key = $auth->regenerateApiKey($user_id);

            $access_type = isApiRequest() ? 'API' : 'Frontend';
            $logger->logAction($user_id, 'regenerate-api-key', 'API key regenerated', $access_type);

            if ($new_api_key) {
                http_response_code(200);
                echo json_encode(['message' => 'API key regenerated successfully', 'api_key' => $new_api_key]);
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Failed to regenerate API key']);
            }
            break;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
        break;
    case 'PUT':
        // Handle PUT requests
        break;
    case 'DELETE':
        // Handle DELETE requests
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
