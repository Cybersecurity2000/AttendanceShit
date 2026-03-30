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
 * Check if a student has already been scanned for a specific mode (period + status) today.
 * Returns true if already scanned, false otherwise.
 */
function hasAlreadyScannedToday($studentId, $status, $period) {
    $pdo = getDbConnection();
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = ? AND status = ? AND period = ? AND DATE(scan_time) = ?");
    $stmt->execute([$studentId, $status, $period, $today]);
    $result = $stmt->fetch();
    
    return ($result['cnt'] > 0);
}

/**
 * Record attendance with period (am/pm)
 * Returns 'duplicate' if already scanned for this mode today, true on success.
 */
function recordAttendance($studentId, $qrToken, $status = 'in', $period = null) {
    // Check for duplicate scan: same student, same status (in/out), same period (am/pm), same day
    if ($period !== null && hasAlreadyScannedToday($studentId, $status, $period)) {
        return 'duplicate';
    }
    
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, qr_token, scan_time, status, period, ip_address) VALUES (?, ?, NOW(), ?, ?, ?)");
    $stmt->execute([$studentId, $qrToken, $status, $period, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    
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
 * Get consolidated attendance records (one row per student per day with AM/PM Time In and Time Out)
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
            MIN(CASE WHEN a.status = 'in' AND (a.period = 'am' OR a.period IS NULL) THEN a.scan_time END) as am_time_in,
            MAX(CASE WHEN a.status = 'out' AND (a.period = 'am' OR a.period IS NULL) THEN a.scan_time END) as am_time_out,
            MIN(CASE WHEN a.status = 'in' AND a.period = 'pm' THEN a.scan_time END) as pm_time_in,
            MAX(CASE WHEN a.status = 'out' AND a.period = 'pm' THEN a.scan_time END) as pm_time_out
        FROM attendance a
        LEFT JOIN students s ON a.student_id = s.student_id
        GROUP BY a.student_id, DATE(a.scan_time), s.first_name, s.last_name, s.course, s.year_level
        ORDER BY attendance_date DESC, am_time_in DESC
        LIMIT $limit
    ");
    return $stmt->fetchAll();
}

/**
 * Get consolidated attendance records by date (one row per student with AM/PM Time In and Time Out)
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
            MIN(CASE WHEN a.status = 'in' AND (a.period = 'am' OR a.period IS NULL) THEN a.scan_time END) as am_time_in,
            MAX(CASE WHEN a.status = 'out' AND (a.period = 'am' OR a.period IS NULL) THEN a.scan_time END) as am_time_out,
            MIN(CASE WHEN a.status = 'in' AND a.period = 'pm' THEN a.scan_time END) as pm_time_in,
            MAX(CASE WHEN a.status = 'out' AND a.period = 'pm' THEN a.scan_time END) as pm_time_out
        FROM attendance a
        LEFT JOIN students s ON a.student_id = s.student_id
        WHERE DATE(a.scan_time) = ?
        GROUP BY a.student_id, DATE(a.scan_time), s.first_name, s.last_name, s.course, s.year_level
        ORDER BY am_time_in DESC
    ");
    $stmt->execute([$date]);
    return $stmt->fetchAll();
}

/**
 * =============================================
 * Event Scheduler Functions (AM/PM)
 * =============================================
 */

/**
 * Create a new event schedule with AM and PM windows
 */
function createEventSchedule($eventName, $eventDate, $amTimeInStart, $amTimeInEnd, $amTimeOutStart, $amTimeOutEnd, $pmTimeInStart, $pmTimeInEnd, $pmTimeOutStart, $pmTimeOutEnd, $createdBy = null) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO event_schedules (event_name, event_date, am_time_in_start, am_time_in_end, am_time_out_start, am_time_out_end, pm_time_in_start, pm_time_in_end, pm_time_out_start, pm_time_out_end, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
    $stmt->execute([$eventName, $eventDate, $amTimeInStart, $amTimeInEnd, $amTimeOutStart, $amTimeOutEnd, $pmTimeInStart, $pmTimeInEnd, $pmTimeOutStart, $pmTimeOutEnd, $createdBy]);
    return $pdo->lastInsertId();
}

/**
 * Get all event schedules
 */
function getAllEventSchedules($limit = 50) {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT * FROM event_schedules ORDER BY event_date DESC, am_time_in_start DESC LIMIT $limit");
    return $stmt->fetchAll();
}

/**
 * Get event schedule by ID
 */
function getEventScheduleById($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM event_schedules WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Update event schedule with AM and PM windows
 */
function updateEventSchedule($id, $eventName, $eventDate, $amTimeInStart, $amTimeInEnd, $amTimeOutStart, $amTimeOutEnd, $pmTimeInStart, $pmTimeInEnd, $pmTimeOutStart, $pmTimeOutEnd) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE event_schedules SET event_name = ?, event_date = ?, am_time_in_start = ?, am_time_in_end = ?, am_time_out_start = ?, am_time_out_end = ?, pm_time_in_start = ?, pm_time_in_end = ?, pm_time_out_start = ?, pm_time_out_end = ? WHERE id = ?");
    return $stmt->execute([$eventName, $eventDate, $amTimeInStart, $amTimeInEnd, $amTimeOutStart, $amTimeOutEnd, $pmTimeInStart, $pmTimeInEnd, $pmTimeOutStart, $pmTimeOutEnd, $id]);
}

/**
 * Delete event schedule
 */
function deleteEventSchedule($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM event_schedules WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Toggle event schedule active status
 */
function toggleEventScheduleStatus($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE event_schedules SET is_active = NOT is_active WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Check if scanner should be open right now based on active event schedules.
 * Now supports 4 windows: AM Time In, AM Time Out, PM Time In, PM Time Out.
 * Returns an array with:
 *   'is_open' => bool (whether scanner is currently open)
 *   'mode' => 'am_time_in' | 'am_time_out' | 'pm_time_in' | 'pm_time_out' | null
 *   'period' => 'am' | 'pm' | null
 *   'status' => 'in' | 'out' | null
 *   'event' => array|null (the active event details)
 *   'message' => string (human-readable status message)
 */
function isScannerOpen() {
    $pdo = getDbConnection();
    $now = date('H:i:s');
    $today = date('Y-m-d');

    // Check for active events today
    $stmt = $pdo->prepare("SELECT * FROM event_schedules WHERE event_date = ? AND is_active = 1 ORDER BY am_time_in_start ASC");
    $stmt->execute([$today]);
    $events = $stmt->fetchAll();

    if (empty($events)) {
        return [
            'is_open' => false,
            'mode' => null,
            'period' => null,
            'status' => null,
            'event' => null,
            'message' => 'No active events scheduled for today.'
        ];
    }

    foreach ($events as $event) {
        // Check AM Time In window
        if ($now >= $event['am_time_in_start'] && $now <= $event['am_time_in_end']) {
            return [
                'is_open' => true,
                'mode' => 'am_time_in',
                'period' => 'am',
                'status' => 'in',
                'event' => $event,
                'message' => 'Scanner is OPEN for MORNING TIME IN — ' . htmlspecialchars($event['event_name'])
            ];
        }
        // Check AM Time Out window
        if ($now >= $event['am_time_out_start'] && $now <= $event['am_time_out_end']) {
            return [
                'is_open' => true,
                'mode' => 'am_time_out',
                'period' => 'am',
                'status' => 'out',
                'event' => $event,
                'message' => 'Scanner is OPEN for MORNING TIME OUT — ' . htmlspecialchars($event['event_name'])
            ];
        }
        // Check PM Time In window
        if ($now >= $event['pm_time_in_start'] && $now <= $event['pm_time_in_end']) {
            return [
                'is_open' => true,
                'mode' => 'pm_time_in',
                'period' => 'pm',
                'status' => 'in',
                'event' => $event,
                'message' => 'Scanner is OPEN for AFTERNOON TIME IN — ' . htmlspecialchars($event['event_name'])
            ];
        }
        // Check PM Time Out window
        if ($now >= $event['pm_time_out_start'] && $now <= $event['pm_time_out_end']) {
            return [
                'is_open' => true,
                'mode' => 'pm_time_out',
                'period' => 'pm',
                'status' => 'out',
                'event' => $event,
                'message' => 'Scanner is OPEN for AFTERNOON TIME OUT — ' . htmlspecialchars($event['event_name'])
            ];
        }
    }

    // Find the next upcoming window today
    $nextWindow = null;
    $nextTime = null;
    $windows = [
        ['field' => 'am_time_in_start', 'label' => 'Morning Time In'],
        ['field' => 'am_time_out_start', 'label' => 'Morning Time Out'],
        ['field' => 'pm_time_in_start', 'label' => 'Afternoon Time In'],
        ['field' => 'pm_time_out_start', 'label' => 'Afternoon Time Out'],
    ];

    foreach ($events as $event) {
        foreach ($windows as $w) {
            if ($now < $event[$w['field']]) {
                if (!$nextTime || $event[$w['field']] < $nextTime) {
                    $nextTime = $event[$w['field']];
                    $nextWindow = $w['label'] . ' for "' . $event['event_name'] . '" opens at ' . date('h:i A', strtotime($event[$w['field']]));
                }
            }
        }
    }

    $msg = 'Scanner is CLOSED. No active scanning window right now.';
    if ($nextWindow) {
        $msg .= ' Next: ' . $nextWindow;
    }

    return [
        'is_open' => false,
        'mode' => null,
        'period' => null,
        'status' => null,
        'event' => null,
        'message' => $msg
    ];
}

/**
 * Get today's active events
 */
function getTodayEvents() {
    $pdo = getDbConnection();
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM event_schedules WHERE event_date = ? AND is_active = 1 ORDER BY am_time_in_start ASC");
    $stmt->execute([$today]);
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