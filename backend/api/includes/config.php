<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'xbohak');
define('DB_PASS', 'Adrz2003');
define('DB_NAME', 'pdf_tools_db');

// Application settings
define('APP_NAME', 'PDF Tools');
define('BASE_URL', 'https://node35.webte.fei.stuba.sk/pdf_app/');
define('ADMIN_URL', BASE_URL . 'admin/');

// Roles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');

// API Key settings
define('API_KEY_LENGTH', 32);

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// GeoLocation API (using ipinfo.io - get a free API key)
define('GEO_API_KEY', '40c656483e5301');
?>