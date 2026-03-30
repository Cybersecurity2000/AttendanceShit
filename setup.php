<?php
/**
 * QRCodex Setup Script
 * Run this to set up the database and admin user
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>QRCodex Setup</h1>";

// Connect without database first to create it
try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<p>✓ Connected to MySQL server</p>";
} catch (PDOException $e) {
    die("<p>✗ Connection failed: " . $e->getMessage() . "</p>");
}

// Create database
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "<p>✓ Database '" . DB_NAME . "' created or already exists</p>";
} catch (PDOException $e) {
    die("<p>✗ Failed to create database: " . $e->getMessage() . "</p>");
}

// Select database
$pdo->exec("USE " . DB_NAME);

// Create tables
$tables = [
    "students" => "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(150),
        course VARCHAR(100),
        year_level VARCHAR(20),
        qr_token VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "attendance" => "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        qr_token VARCHAR(255) NOT NULL,
        scan_time DATETIME NOT NULL,
        status ENUM('in', 'out') DEFAULT 'in',
        location VARCHAR(100),
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "admins" => "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(150),
        email VARCHAR(150),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "<p>✓ Table '$name' created or already exists</p>";
    } catch (PDOException $e) {
        echo "<p>! Table '$name': " . $e->getMessage() . "</p>";
    }
}

// Check if admin exists
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute(['admin']);
$admin = $stmt->fetch();

if ($admin) {
    echo "<p>✓ Admin user 'admin' already exists</p>";
} else {
    // Create default admin (password: admin123, MD5 hash)
    $passwordHash = '0192023a7bbd73250516f069df18b500'; // MD5 of 'admin123'
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name, email) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', $passwordHash, 'System Administrator', 'admin@qrcodex.com']);
    echo "<p>✓ Admin user 'admin' created with default password 'admin123'</p>";
}

echo "<h2>Setup Complete!</h2>";
echo "<p>You can now login with:</p>";
echo "<ul>";
echo "<li>Username: <strong>admin</strong></li>";
echo "<li>Password: <strong>admin123</strong></li>";
echo "</ul>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";