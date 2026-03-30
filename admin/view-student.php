<?php
/**
 * QRCodex - View Student Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'View Student';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "admin/students.php");
    exit;
}

$student = getStudentById($_GET['id']);

if (!$student) {
    header("Location: " . BASE_URL . "admin/students.php");
    exit;
}

$qrCodeUrl = createQRCode(BASE_URL . 'scan.php?token=' . $student['qr_token']);

// Get student's attendance history
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? ORDER BY scan_time DESC LIMIT 20");
$stmt->execute([$student['student_id']]);
$attendanceHistory = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="sidebar">
            <div class="text-center mb-4">
                <i class="fas fa-user-circle fa-4x" style="color: #c9a227;"></i>
                <h5 class="mt-2 mb-0"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h5>
                <small class="text-muted">Administrator</small>
            </div>
            
            <nav class="nav flex-column">
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="nav-link-custom">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>admin/students.php" class="nav-link-custom active">
                    <i class="fas fa-users me-2"></i>Students
                </a>
                <a href="<?php echo BASE_URL; ?>admin/add-student.php" class="nav-link-custom">
                    <i class="fas fa-user-plus me-2"></i>Add Student
                </a>
                <a href="<?php echo BASE_URL; ?>admin/attendance.php" class="nav-link-custom">
                    <i class="fas fa-clipboard-list me-2"></i>Attendance
                </a>
                <a href="<?php echo BASE_URL; ?>admin/scan.php" class="nav-link-custom">
                    <i class="fas fa-camera me-2"></i>Scan QR
                </a>
                <a href="<?php echo BASE_URL; ?>admin/qr-generator.php" class="nav-link-custom">
                    <i class="fas fa-qrcode me-2"></i>QR Generator
                </a>
                <a href="<?php echo BASE_URL; ?>admin/settings.php" class="nav-link-custom">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
                <a href="<?php echo BASE_URL; ?>admin/logout.php" class="nav-link-custom">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user me-2"></i>Student Details</h2>
            <a href="<?php echo BASE_URL; ?>admin/students.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>

        <div class="row">
            <!-- Student Info -->
            <div class="col-md-5">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Student Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Student ID:</td>
                                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Full Name:</td>
                                <td><strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Course:</td>
                                <td><?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Year Level:</td>
                                <td><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Registered:</td>
                                <td><?php echo date('M d, Y h:i A', strtotime($student['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="qr-display">
                    <h5 class="mb-3">Student QR Code</h5>
                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                    
                    <div class="d-grid gap-2">
                        <button onclick="printQRCode()" class="btn btn-primary-custom">
                            <i class="fas fa-print me-2"></i>Print QR Code
                        </button>
                        <a href="<?php echo $qrCodeUrl; ?>" download="qr_<?php echo $student['student_id']; ?>.png" class="btn btn-success-custom">
                            <i class="fas fa-download me-2"></i>Download QR
                        </a>
                    </div>

                    <div class="mt-3 p-2 bg-light rounded">
                        <small class="text-muted">
                            <strong>QR Token:</strong><br>
                            <code><?php echo htmlspecialchars($student['qr_token']); ?></code>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Attendance History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($attendanceHistory)): ?>
                                        <?php foreach ($attendanceHistory as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo date('M d, Y', strtotime($record['scan_time'])); ?></strong>
                                                <br><small class="text-muted"><?php echo date('h:i:s A', strtotime($record['scan_time'])); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($record['status'] === 'in'): ?>
                                                    <span class="badge bg-success"><i class="fas fa-sign-in-alt me-1"></i>IN</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="fas fa-sign-out-alt me-1"></i>OUT</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><small class="text-muted"><?php echo htmlspecialchars($record['ip_address'] ?? 'N/A'); ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                No attendance records found for this student
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printQRCode() {
    const printContent = `
        <html>
        <head>
            <title>QR Code - <?php echo htmlspecialchars($student['student_id']); ?></title>
            <style>
                body { text-align: center; padding: 50px; font-family: Arial, sans-serif; }
                h2 { margin-bottom: 10px; }
                p { color: #666; margin-bottom: 30px; }
                img { max-width: 300px; border: 5px solid #c9a227; border-radius: 10px; padding: 20px; }
            </style>
        </head>
        <body>
            <h2><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
            <p>Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '', 'width=600,height=800');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>