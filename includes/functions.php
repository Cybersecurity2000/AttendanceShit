<?php
/**
 * QRCodex Functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Generate unique QR token
 */
function generateQRToken($studentId) {
    return hash('sha256', $studentId . time() . uniqid());
}

/**
 * Create QR code image
 */
function createQRCode($data, $size = 300) {
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
    return $qrUrl;
}

/**
 * Add new student
 */
function addStudent($studentId, $firstName, $lastName, $email, $course, $yearLevel) {
    $pdo = getDbConnection();
    $qrToken = generateQRToken($studentId);
    
    $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, email, course, year_level, qr_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$studentId, $firstName, $lastName, $email, $course, $yearLevel, $qrToken]);
    
    return $qrToken;
}

/**
 * Get all students
 */
function getAllStudents() {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Get student by ID
 */
function getStudentById($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get student by QR token
 */
function getStudentByQRToken($token) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM students WHERE qr_token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

/**
 * Record attendance
 */
function recordAttendance($studentId, $qrToken, $status = 'in') {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, qr_token, scan_time, status, ip_address) VALUES (?, ?, NOW(), ?, ?)");
    $stmt->execute([$studentId, $qrToken, $status, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    
    return true;
}

/**
 * Get attendance records
 */
function getAttendanceRecords($limit = 100) {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT a.*, s.first_name, s.last_name, s.course, s.year_level 
                          FROM attendance a 
                          LEFT JOIN students s ON a.student_id = s.student_id 
                          ORDER BY a.scan_time DESC 
                          LIMIT $limit");
    return $stmt->fetchAll();
}

/**
 * Get attendance by date
 */
function getAttendanceByDate($date) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT a.*, s.first_name, s.last_name, s.course, s.year_level 
                          FROM attendance a 
                          LEFT JOIN students s ON a.student_id = s.student_id 
                          WHERE DATE(a.scan_time) = ?
                          ORDER BY a.scan_time DESC");
    $stmt->execute([$date]);
    return $stmt->fetchAll();
}

/**
 * Get attendance statistics
 */
function getAttendanceStats() {
    $pdo = getDbConnection();
    
    $stats = [];
    
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
    $stats['total_students'] = $stmt->fetch()['total'];
    
    // Today's attendance
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE DATE(scan_time) = CURDATE()");
    $stats['today_count'] = $stmt->fetch()['count'];
    
    // This week's attendance
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE YEARWEEK(scan_time) = YEARWEEK(NOW())");
    $stats['week_count'] = $stmt->fetch()['count'];
    
    // Recent attendance (last hour)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE scan_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stats['recent_count'] = $stmt->fetch()['count'];
    
    return $stats;
}

/**
 * Admin login
 */
function adminLogin($username, $password) {
    try {
        $pdo = getDbConnection();
        
        // First check if admin table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
        if ($stmt->rowCount() === 0) {
            error_log("Admin table does not exist. Run setup.php first.");
            return false;
        }
        
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            error_log("Admin user '$username' not found in database");
            return false;
        }
        
        // Verify password
        $inputHash = md5($password);
        $storedHash = $admin['password'];
        
        if ($inputHash === $storedHash) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_logged_in'] = true;
            return true;
        } else {
            error_log("Password mismatch. Input: $inputHash, Stored: $storedHash");
            return false;
        }
    } catch (Exception $e) {
        error_log("Admin login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Get admin by ID
 */
function getAdminById($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Update admin password
 * Verifies current password, then updates to new password in the database
 */
function updateAdminPassword($adminId, $currentPassword, $newPassword) {
    try {
        $pdo = getDbConnection();
        
        // Get admin record
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            return 'Admin account not found.';
        }
        
        // Verify current password using MD5 (matching existing login logic)
        $currentHash = md5($currentPassword);
        if ($currentHash !== $admin['password']) {
            return 'Current password is incorrect.';
        }
        
        // Update password with MD5 hash (matching existing password scheme)
        $newHash = md5($newPassword);
        $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $updateStmt->execute([$newHash, $adminId]);
        
        return true;
    } catch (Exception $e) {
        error_log("Password update error: " . $e->getMessage());
        return 'An error occurred while updating the password. Please try again.';
    }
}

/**
 * Admin logout
 */
function adminLogout() {
    session_destroy();
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

/**
 * Get consolidated attendance records (one row per student per day with Time In and Time Out)
 */
function getConsolidatedAttendanceRecords($limit = 100) {
    $pdo = getDbConnection();
    $stmt = $pdo->query("
        SELECT 
            a.student_id,
            s.first_name,
            s.last_name,
            s.course,
            s.year_level,
            DATE(a.scan_time) as attendance_date,
            MIN(CASE WHEN a.status = 'in' THEN a.scan_time END) as time_in,
            MAX(CASE WHEN a.status = 'out' THEN a.scan_time END) as time_out
        FROM attendance a
        LEFT JOIN students s ON a.student_id = s.student_id
        GROUP BY a.student_id, DATE(a.scan_time), s.first_name, s.last_name, s.course, s.year_level
        ORDER BY attendance_date DESC, time_in DESC
        LIMIT $limit
    ");
    return $stmt->fetchAll();
}

/**
 * Get consolidated attendance records by date (one row per student with Time In and Time Out)
 */
function getConsolidatedAttendanceByDate($date) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT 
            a.student_id,
            s.first_name,
            s.last_name,
            s.course,
            s.year_level,
            DATE(a.scan_time) as attendance_date,
            MIN(CASE WHEN a.status = 'in' THEN a.scan_time END) as time_in,
            MAX(CASE WHEN a.status = 'out' THEN a.scan_time END) as time_out
        FROM attendance a
        LEFT JOIN students s ON a.student_id = s.student_id
        WHERE DATE(a.scan_time) = ?
        GROUP BY a.student_id, DATE(a.scan_time), s.first_name, s.last_name, s.course, s.year_level
        ORDER BY time_in DESC
    ");
    $stmt->execute([$date]);
    return $stmt->fetchAll();
}

/**
 * Delete student
 */
function deleteStudent($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Update student
 */
function updateStudent($id, $firstName, $lastName, $email, $course, $yearLevel) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, course = ?, year_level = ? WHERE id = ?");
    return $stmt->execute([$firstName, $lastName, $email, $course, $yearLevel, $id]);
}
?>