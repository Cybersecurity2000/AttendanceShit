<?php
/**
 * QRCodex - View Student Details Page
 * Displays individual student information, QR code, and token
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

// Check if student ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "admin/students.php");
    exit;
}

// Fetch the student
$student = getStudentById($_GET['id']);

// If student not found, redirect back
if (!$student) {
    header("Location: " . BASE_URL . "admin/students.php");
    exit;
}

$pageTitle = 'View Student - ' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);

// Generate QR code URL using the student's qr_token
$qrCodeUrl = createQRCode($student['qr_token'], 300);
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
            <h2><i class="fas fa-user me-2"></i>Student Details</h2>
            <a href="<?php echo BASE_URL; ?>admin/students.php" class="btn btn-primary-custom">
                <i class="fas fa-arrow-left me-2"></i>Back to Students
            </a>
        </div>

        <div class="row">
            <!-- Student Information Card -->
            <div class="col-md-7 mb-4">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #d4af37; border-radius: 15px 15px 0 0;">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Student Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td style="width: 40%; font-weight: 600; color: #555;">
                                        <i class="fas fa-hashtag me-2" style="color: #c9a227;"></i>Student ID
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555;">
                                        <i class="fas fa-user me-2" style="color: #c9a227;"></i>First Name
                                    </td>
                                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555;">
                                        <i class="fas fa-user me-2" style="color: #c9a227;"></i>Last Name
                                    </td>
                                    <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555;">
                                        <i class="fas fa-envelope me-2" style="color: #c9a227;"></i>Email
                                    </td>
                                    <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555;">
                                        <i class="fas fa-graduation-cap me-2" style="color: #c9a227;"></i>Course
                                    </td>
                                    <td><?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555;">
                                        <i class="fas fa-layer-group me-2" style="color: #c9a227;"></i>Year Level
                                    </td>
                                    <td><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555;">
                                        <i class="fas fa-calendar me-2" style="color: #c9a227;"></i>Registered
                                    </td>
                                    <td><?php echo date('F j, Y g:i A', strtotime($student['created_at'])); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- QR Token Card -->
                <div class="card mt-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #d4af37; border-radius: 15px 15px 0 0;">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>QR Token</h5>
                    </div>
                    <div class="card-body">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px; border: 1px solid rgba(201, 162, 39, 0.2);">
                            <code style="font-size: 0.85rem; word-break: break-all; color: #1a1a2e;">
                                <?php echo htmlspecialchars($student['qr_token']); ?>
                            </code>
                        </div>
                        <button class="btn btn-sm btn-primary-custom mt-3" onclick="copyToken()">
                            <i class="fas fa-copy me-2"></i>Copy Token
                        </button>
                    </div>
                </div>
            </div>

            <!-- QR Code Display -->
            <div class="col-md-5 mb-4">
                <div class="qr-display">
                    <h5 class="mb-3" style="color: #1a1a2e; font-weight: 600;">
                        <i class="fas fa-qrcode me-2" style="color: #c9a227;"></i>QR Code
                    </h5>
                    <div class="mb-3">
                        <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" 
                             alt="QR Code for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>"
                             id="qr-image"
                             style="width: 100%; max-width: 280px;">
                    </div>
                    <p class="text-muted mb-3">
                        <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($student['student_id']); ?></small>
                    </p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo htmlspecialchars($qrCodeUrl); ?>" 
                           download="QR_<?php echo htmlspecialchars($student['student_id']); ?>.png" 
                           class="btn btn-primary-custom">
                            <i class="fas fa-download me-2"></i>Download QR Code
                        </a>
                        <button class="btn btn-success-custom" onclick="printQR()">
                            <i class="fas fa-print me-2"></i>Print QR Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Copy token to clipboard
function copyToken() {
    const token = <?php echo json_encode($student['qr_token']); ?>;
    navigator.clipboard.writeText(token).then(function() {
        alert('QR Token copied to clipboard!');
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = token;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('QR Token copied to clipboard!');
    });
}

// Print QR code
function printQR() {
    const qrImage = document.getElementById('qr-image').src;
    const studentName = <?php echo json_encode($student['first_name'] . ' ' . $student['last_name']); ?>;
    const studentId = <?php echo json_encode($student['student_id']); ?>;
    const course = <?php echo json_encode($student['course'] ?? 'N/A'); ?>;
    const yearLevel = <?php echo json_encode($student['year_level'] ?? 'N/A'); ?>;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - ${studentName}</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    text-align: center; 
                    padding: 40px; 
                }
                .qr-print-container {
                    border: 3px solid #c9a227;
                    border-radius: 15px;
                    padding: 30px;
                    display: inline-block;
                    max-width: 400px;
                }
                .qr-print-container img { 
                    width: 250px; 
                    height: 250px; 
                    margin: 15px 0; 
                }
                h2 { color: #1a1a2e; margin-bottom: 5px; }
                .student-info { color: #555; font-size: 14px; margin: 5px 0; }
                .brand { color: #c9a227; font-weight: 700; font-size: 18px; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class="qr-print-container">
                <div class="brand">QRCodex - Attendance System</div>
                <h2>${studentName}</h2>
                <p class="student-info"><strong>Student ID:</strong> ${studentId}</p>
                <p class="student-info"><strong>Course:</strong> ${course}</p>
                <p class="student-info"><strong>Year Level:</strong> ${yearLevel}</p>
                <img src="${qrImage}" alt="QR Code">
                <p class="student-info" style="margin-top: 10px;"><em>Scan this QR code for attendance</em></p>
            </div>
            <script>
                window.onload = function() { window.print(); window.close(); };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>