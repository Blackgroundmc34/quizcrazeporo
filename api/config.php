<?php
// -- Database Configuration --
define('DB_SERVERNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'exam_system');

// -- Other Configurations --
define('BASE_APP_PATH', 'http://localhost/quizcrazepro/');
define('IMAGE_BASE_URL_CONFIG', '/'); // Renamed to avoid conflict with your define()
define('ACTIVITY_TIMEOUT_SECONDS', 300); // 5 minutes

// --- Helper function to get current page URL ---
// Placed here as it's a general utility
function getCurrentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

// --- Function to establish database connection ---
function getDbConnection() {
    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database Connection failed: " . $conn->connect_error);
        // In a real app, you might throw an exception or handle this more gracefully
        die("Connection failed. Please contact support or try again later.");
    }
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}
?>