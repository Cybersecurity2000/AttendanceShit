<?php
/**
 * QRCodex - QR Generator Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'QR Generator';
$students = getAllStudents();
$selectedStudent = null;
$qrCodeUrl = '';
$studentInfo = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $selectedStudent = getStudentById($_GET['id']);
    if ($selectedStudent) {
        $qrCodeUrl = createQRCode(BASE_URL . 'scan.php?token=' . $selectedStudent['qr_token']);
        $studentInfo = $selectedStudent;
    }
}
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
                <a href="<?php echo BASE_URL; ?>admin/students.php" class="nav-link-custom">
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
                <a href="<?php echo BASE_URL; ?>admin/qr-generator.php" class="nav-link-custom active">
                    <i class="fas fa-qrcode me-2"></i>QR Generator
                </a>
                <a href="<?php echo BASE_URL; ?>admin/event-scheduler.php" class="nav-link-custom">
                    <i class="fas fa-calendar-alt me-2"></i>Event Scheduler
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
            <h2><i class="fas fa-qrcode me-2"></i>QR Code Generator</h2>
        </div>

        <div class="row">
            <!-- Student Selection -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Select Student</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($students as $student): ?>
                            <a href="<?php echo BASE_URL; ?>admin/qr-generator.php?id=<?php echo $student['id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo ($selectedStudent && $selectedStudent['id'] === $student['id']) ? 'active' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                        <br><small><?php echo htmlspecialchars($student['student_id']); ?></small>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                            <?php endforeach; ?>
                            <?php if (empty($students)): ?>
                            <div class="list-group-item text-center text-muted py-4">
                                No students found. <a href="<?php echo BASE_URL; ?>admin/add-student.php">Add a student</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code Display -->
            <div class="col-md-7">
                <div class="qr-display">
                    <?php if ($selectedStudent): ?>
                        <h4 class="mb-4"><?php echo htmlspecialchars($selectedStudent['first_name'] . ' ' . $selectedStudent['last_name']); ?></h4>
                        <p class="text-muted mb-3">Student ID: <?php echo htmlspecialchars($selectedStudent['student_id']); ?></p>
                        
                        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid mb-4" style="max-width: 250px;">
                        
                        <div class="mb-3">
                            <button onclick="printQRCode()" class="btn btn-primary-custom me-2">
                                <i class="fas fa-print me-2"></i>Print QR Code
                            </button>
                            <a href="<?php echo $qrCodeUrl; ?>" download="qr_<?php echo $selectedStudent['student_id']; ?>.png" class="btn btn-success-custom">
                                <i class="fas fa-download me-2"></i>Download
                            </a>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>QR Data:</strong> <?php echo BASE_URL . 'scan.php?token=' . htmlspecialchars($selectedStudent['qr_token']); ?>
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-qrcode fa-4x mb-3" style="color: #c9a227;"></i>
                            <p>Select a student to generate their QR code</p>
                        </div>
                    <?php endif; ?>
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
            <title>QR Code - <?php echo htmlspecialchars($selectedStudent['student_id'] ?? ''); ?></title>
            <style>
                body { text-align: center; padding: 50px; font-family: Arial, sans-serif; }
                h2 { margin-bottom: 10px; }
                p { color: #666; margin-bottom: 30px; }
                img { max-width: 300px; border: 5px solid #c9a227; border-radius: 10px; padding: 20px; }
            </style>
        </head>
        <body>
            <h2><?php echo htmlspecialchars(($selectedStudent['first_name'] ?? '') . ' ' . ($selectedStudent['last_name'] ?? '')); ?></h2>
            <p>Student ID: <?php echo htmlspecialchars($selectedStudent['student_id'] ?? ''); ?></p>
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