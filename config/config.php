<?php
/**
 * QRCodex Database Configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'qrcodex_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create database connection
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-detect Base URL so it works on any server/port/path
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Detect the project root folder from the script path
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// Walk up to find the QRCODEX root (the folder containing config/)
$projectRoot = $scriptDir;
// If we're inside a subfolder like /admin, go up one level
if (basename($scriptDir) === 'admin') {
    $projectRoot = dirname($scriptDir);
}
// Ensure trailing slash
$projectRoot = rtrim($projectRoot, '/') . '/';

define('BASE_URL', $protocol . '://' . $host . $projectRoot);
define('SITE_NAME', 'QRCodex - Attendance System');

// Timezone
date_default_timezone_set('Asia/Manila');
?>