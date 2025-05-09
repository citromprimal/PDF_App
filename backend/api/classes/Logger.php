<?php
//logger.php
namespace PDF_App\backend\api\classes;

use includes\Database;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/GeoLocation.php';

class Logger
{
    private $db;
    private $geo;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->geo = new GeoLocation();
    }

    /**
     * Log user action with geolocation data
     * Example usage for frontend:
     * $logger = new Logger();
     * $details = 'User viewed contract documents';
     * $logger->logAction($user_id, 'view_contracts', $details, 'frontend');
     *
     * Example usage for API:
     * $logger = new Logger();
     * $details = 'API request: Get user profile data';
     * $logger->logAction($user_id, 'get_profile', $details, 'api');
     */
    public function logAction($user_id, $action, $details = null, $access_type = 'frontend')
    {
        $user_id = intval($user_id);

        // Get geolocation
        $location = $this->geo->getLocationByIp();
        $city = isset($location['city']) ? $location['city'] : 'Unknown';
        $country = isset($location['country']) ? $location['country'] : 'Unknown';

        $ip_address = $this->geo->getClientIp();

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, details, access_type, ip_address, city, country, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        return $stmt->execute([$user_id, $action, $details, $access_type, $ip_address, $city, $country]);
    }

    public function logLogin($user_id, $username)
    {
        return $this->logAction($user_id, 'login', "User {$username} logged in");
    }

    public function logLogout($user_id, $username)
    {
        return $this->logAction($user_id, 'logout', "User {$username} logged out");
    }

    /**
     * Log PDF-related actions
     * Example for frontend PDF logging:
     * * $logger = new Logger();
     * * $details = 'User downloaded contract PDF #12345';
     * * $logger->logPdfAction($user_id, 'download', $details, 'frontend');
     * *
     * * Example for API PDF logging:
     * * $logger = new Logger();
     * * $details = 'API request: Merged PDF files';
     * * $logger->logPdfAction($user_id, 'merge', $details, 'api');
     */
    public function logPdfAction($user_id, $pdf_action, $file_details, $access_type = 'frontend')
    {
        return $this->logAction($user_id, 'pdf_' . $pdf_action, $file_details, $access_type);
    }

    public function getHistory($limit = 100, $offset = 0, $filters = [])
    {
        $where_clauses = [];
        $params = [];

        // Build where clauses based on filters
        if (!empty($filters['user_id'])) {
            $where_clauses[] = "al.user_id = ?";
            $params[] = intval($filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $where_clauses[] = "al.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['access_type'])) {
            $where_clauses[] = "al.access_type = ?";
            $params[] = $filters['access_type'];
        }

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "al.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where_clauses[] = "al.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

        $limit = intval($limit);
        $offset = intval($offset);

        $sql = "SELECT al.*, u.username 
                FROM activity_log al
                JOIN users u ON al.user_id = u.id
                {$where_sql}
                ORDER BY al.created_at DESC
                LIMIT {$offset}, {$limit}";

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getTotalHistoryCount($filters = [])
    {
        $where_clauses = [];
        $params = [];

        // Build where clauses based on filters
        if (!empty($filters['user_id'])) {
            $where_clauses[] = "user_id = ?";
            $params[] = intval($filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $where_clauses[] = "action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['access_type'])) {
            $where_clauses[] = "access_type = ?";
            $params[] = $filters['access_type'];
        }

        if (!empty($filters['date_from'])) {
            $where_clauses[] = "created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where_clauses[] = "created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

        $sql = "SELECT COUNT(*) as total FROM activity_log {$where_sql}";

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row['total'];
    }

    public function deleteAllHistory()
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("TRUNCATE TABLE activity_log");

        return $stmt->execute();
    }

    public function exportHistoryToCsv($filters = [])
    {
        $logs = $this->getHistory(10000, 0, $filters); // Get up to 10,000 records

        if (empty($logs)) {
            return false;
        }

        $filename = 'activity_log_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV content
        $csv_content = "ID,Username,User ID,Action,Details,Access Type,IP Address,City,Country,Date\n";

        foreach ($logs as $log) {
            $csv_content .= '"' . $log['id'] . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['username']) . '",';
            $csv_content .= '"' . $log['user_id'] . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['action']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['details']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['access_type']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['ip_address']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['city']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['country']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $log['created_at']) . '"' . "\n";
        }

        return [
            'filename' => $filename,
            'content' => $csv_content
        ];
    }
}

?>