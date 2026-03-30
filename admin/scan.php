<?php
/**
 * QRCodex - Admin QR Scanner Page for Attendance
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'Scan QR Code';
$message = '';
$messageType = '';
$student = null;
$newStatus = '';
$scanTime = '';

// Handle manual token submission
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    $student = getStudentByQRToken($token);
    
    if ($student) {
        // Determine status (in/out based on last attendance)
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT status FROM attendance WHERE student_id = ? ORDER BY scan_time DESC LIMIT 1");
        $stmt->execute([$student['student_id']]);
        $lastStatus = $stmt->fetch();
        $newStatus = (!$lastStatus || $lastStatus['status'] === 'out') ? 'in' : 'out';
        
        // Record attendance
        recordAttendance($student['student_id'], $student['qr_token'], $newStatus);
        
        // Get the exact scan time
        $scanTime = date('h:i:s A');
        
        $message = 'Attendance recorded successfully!';
        $messageType = 'success';
    } else {
        $message = 'Invalid QR code or token not found.';
        $messageType = 'error';
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
                <a href="<?php echo BASE_URL; ?>admin/scan.php" class="nav-link-custom active">
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
        <div class="text-center mb-4">
            <h2><i class="fas fa-qrcode me-2"></i>QR Code Scanner</h2>
            <p class="text-muted">Scan student QR codes to register attendance</p>
        </div>

        <?php if ($message && $messageType === 'error'): ?>
        <div class="alert alert-danger-gradient" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <!-- TIME IN / TIME OUT Visual Indicator Card -->
        <?php if ($student && $messageType === 'success'): ?>
        <div class="card mb-4 border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <?php if ($newStatus === 'in'): ?>
            <!-- GREEN CARD — TIME IN -->
            <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 40px 30px; text-align: center;">
                <div style="background: rgba(255,255,255,0.2); width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fas fa-sign-in-alt fa-3x text-white"></i>
                </div>
                <h2 class="text-white fw-bold mb-1" style="font-size: 2rem;">🟢 TIME IN</h2>
                <p class="text-white mb-0" style="font-size: 1.5rem; font-weight: 600;">
                    <i class="fas fa-clock me-2"></i><?php echo $scanTime; ?>
                </p>
            </div>
            <?php else: ?>
            <!-- RED CARD — TIME OUT -->
            <div style="background: linear-gradient(135deg, #e53935 0%, #e35d5b 50%, #f5576c 100%); padding: 40px 30px; text-align: center;">
                <div style="background: rgba(255,255,255,0.2); width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fas fa-sign-out-alt fa-3x text-white"></i>
                </div>
                <h2 class="text-white fw-bold mb-1" style="font-size: 2rem;">🔴 TIME OUT</h2>
                <p class="text-white mb-0" style="font-size: 1.5rem; font-weight: 600;">
                    <i class="fas fa-clock me-2"></i><?php echo $scanTime; ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Student Info Section -->
            <div class="card-body text-center py-4" style="background: #fff;">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-4x" style="color: <?php echo $newStatus === 'in' ? '#11998e' : '#e53935'; ?>;"></i>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                <p class="text-muted mb-1">
                    <i class="fas fa-id-badge me-1"></i>Student ID: <strong><?php echo htmlspecialchars($student['student_id']); ?></strong>
                </p>
                <?php if (!empty($student['course']) || !empty($student['year_level'])): ?>
                <p class="text-muted mb-2">
                    <i class="fas fa-graduation-cap me-1"></i>
                    <?php echo htmlspecialchars($student['course'] ?? ''); ?>
                    <?php if (!empty($student['year_level'])): ?>
                        - <?php echo htmlspecialchars($student['year_level']); ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
                <div class="mt-3">
                    <?php if ($newStatus === 'in'): ?>
                    <span class="badge fs-6 px-4 py-2" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 25px;">
                        <i class="fas fa-check-circle me-2"></i>Checked In Successfully
                    </span>
                    <?php else: ?>
                    <span class="badge fs-6 px-4 py-2" style="background: linear-gradient(135deg, #e53935 0%, #f5576c 100%); border-radius: 25px;">
                        <i class="fas fa-check-circle me-2"></i>Checked Out Successfully
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Camera Scanner -->
        <div class="scanner-container mb-4">
            <div id="qr-reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
            <button id="start-scan-btn" class="scanner-btn mt-3" onclick="startScanner()">
                <i class="fas fa-camera me-2"></i>Start Camera Scanner
            </button>
            <button id="stop-scan-btn" class="scanner-btn mt-3 d-none" onclick="stopScanner()">
                <i class="fas fa-stop-circle me-2"></i>Stop Scanner
            </button>
        </div>

        <!-- Manual Entry -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-keyboard me-2"></i>Manual Token Entry</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="token" 
                               placeholder="Enter QR code token or student ID..."
                               required>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-search me-2"></i>Verify & Record
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions -->
        <div class="card mt-4">
            <div class="card-body">
                <h5><i class="fas fa-info-circle me-2"></i>How to use:</h5>
                <ol class="mb-0">
                    <li>Click "Start Camera Scanner" to open your device camera</li>
                    <li>Point your camera at the QR code displayed on the student's device or printed card</li>
                    <li>The system will automatically record attendance when a valid QR is detected</li>
                    <li>Alternatively, you can manually enter the token or student ID</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrCode;
let scanning = false;

function startScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");
    
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        scanning = true;
        document.getElementById('start-scan-btn').classList.add('d-none');
        document.getElementById('stop-scan-btn').classList.remove('d-none');
    }).catch((err) => {
        alert('Error starting camera: ' + err);
    });
}

function stopScanner() {
    if (html5QrCode && scanning) {
        html5QrCode.stop().then(() => {
            scanning = false;
            document.getElementById('start-scan-btn').classList.remove('d-none');
            document.getElementById('stop-scan-btn').classList.add('d-none');
        });
    }
}

function onScanSuccess(decodedText) {
    // Stop scanner to prevent multiple scans
    if (html5QrCode && scanning) {
        html5QrCode.stop().then(() => {
            scanning = false;
            window.location.href = '<?php echo BASE_URL; ?>admin/scan.php?token=' + encodeURIComponent(decodedText);
        });
    } else {
        window.location.href = '<?php echo BASE_URL; ?>admin/scan.php?token=' + encodeURIComponent(decodedText);
    }
}

function onScanFailure(error) {
    // Silently ignore scan failures (camera is still scanning)
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>