namespace PDF_App\backend\api\classes;
namespace includes;
namespace PDF_App\backend\api\classes;
namespace PDF_App\backend\api\classes;
namespace PDF_App\backend\api\classes;
namespace classes;
namespace classes;

<?php
//auth.php
use includes\Database;
use PDF_App\backend\api\classes\Logger;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Logger.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function login($username, $password)
    {

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");

        if (!$stmt) {
            error_log("Prepare failed");
            return false;
        }

        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            error_log("User found: " . print_r($user, true));

            error_log("Input password (raw): " . $password);
            error_log("Password hash from DB: " . $user['password']);

            // Standard check for all users
            if (password_verify($password, $user['password'])) {
                error_log("Password verified successfully");

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                error_log("Session set: " . print_r($_SESSION, true));

                // Log the login

                $logger = new Logger();
                $logger->logLogin($user['id'], $user['username']);

                return true;
            } else {
                error_log("Password verification failed");
            }
        } else {
            error_log("User not found");
        }

        return false;
    }

    public function logout()
    {
        // Log the logout if user is logged in
        if (isset($_SESSION['user_id'])) {
            $logger = new Logger();
            $logger->logLogout($_SESSION['user_id'], $_SESSION['username']);
        }

        // Destroy the session
        session_unset();
        session_destroy();
        error_log("Session destroyed. Current session: " . print_r($_SESSION, true));

        return true;
    }

    public function register($username, $password, $email, $role = ROLE_USER)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $api_key = generate_token(API_KEY_LENGTH);

        $conn = $this->db->getConnection();

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            return false; // User already exists
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, api_key, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $success = $stmt->execute([$username, $hashed_password, $email, $role, $api_key]);

        if ($success) {
            return $conn->lastInsertId();
        }

        return false;
    }

    public function getUserById($user_id)
    {
        $user_id = intval($user_id);

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id, username, email, role, api_key, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        return $stmt->fetch();
    }

    public function getUserByApiKey($api_key)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE api_key = ?");
        $stmt->execute([$api_key]);

        return $stmt->fetch();
    }

    public function regenerateApiKey($user_id)
    {
        $user_id = intval($user_id);
        $new_api_key = generate_token(API_KEY_LENGTH);

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE users SET api_key = ? WHERE id = ?");
        $success = $stmt->execute([$new_api_key, $user_id]);

        if ($success) {
            return $new_api_key;
        }

        return false;
    }

    public function checkSession()
    {
        error_log('Checking session...');
        error_log('Session user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
        error_log('Session last_activity: ' . (isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : 'not set'));

        // Check if session exists and is not expired
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            // Check for session timeout
            if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }

            // Update last activity time
            $_SESSION['last_activity'] = time();
            return true;
        }

        return false;
    }
}

?>