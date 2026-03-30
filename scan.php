<?php
/**
 * QRCodex - QR Scanner Page for Attendance (Public)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Scan QR Code';
$message = '';
$messageType = '';
$student = null;
$newStatus = '';
$scanPeriod = '';
$scanTime = '';

// Check scanner status based on event schedule
$scannerStatus = isScannerOpen();

// Handle manual token submission
if (isset($_GET['token']) && !empty($_GET['token'])) {
    // Check scanner status for the scan attempt
    if (!$scannerStatus['is_open']) {
        $message = 'Scanner is currently CLOSED. ' . $scannerStatus['message'];
        $messageType = 'error';
    } else {
        $token = $_GET['token'];
        $student = getStudentByQRToken($token);
        
        if ($student) {
            // Use the event schedule mode to determine status and period
            $newStatus = $scannerStatus['status'] ?? 'in';
            $scanPeriod = $scannerStatus['period'] ?? 'am';
            
            // Record attendance with period (checks for duplicates)
            $result = recordAttendance($student['student_id'], $student['qr_token'], $newStatus, $scanPeriod);
            
            if ($result === 'duplicate') {
                // Student already scanned for this mode today
                $periodLabel = ($scanPeriod === 'am') ? 'Morning' : 'Afternoon';
                $statusLabel = ($newStatus === 'in') ? 'Time In' : 'Time Out';
                $message = 'This QR code has already been scanned for ' . $periodLabel . ' ' . $statusLabel . ' today. Duplicate scan not recorded.';
                $messageType = 'warning';
            } else {
                // Get the exact scan time
                $scanTime = date('h:i:s A');
                
                $message = 'Attendance recorded successfully!';
                $messageType = 'success';
            }
        } else {
            $message = 'Invalid QR code or token not found.';
            $messageType = 'error';
        }
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-4">
            <h2 class="text-white"><i class="fas fa-qrcode me-2"></i>QR Code Scanner</h2>
            <p class="text-white opacity-75">Scan your QR code to register attendance</p>
        </div>

        <!-- Scanner Status Banner -->
        <?php if ($scannerStatus['is_open']): ?>
        <div class="alert mb-4 border-0" role="alert" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border-radius: 15px; padding: 20px;">
            <div class="text-center">
                <h5 class="mb-1 fw-bold"><i class="fas fa-broadcast-tower me-2"></i>Scanner is OPEN</h5>
                <p class="mb-0"><?php echo $scannerStatus['message']; ?></p>
                <?php
                    $modeLabel = '';
                    switch ($scannerStatus['mode'] ?? '') {
                        case 'am_time_in': $modeLabel = '🌅 MORNING TIME IN'; break;
                        case 'am_time_out': $modeLabel = '🌅 MORNING TIME OUT'; break;
                        case 'pm_time_in': $modeLabel = '🌇 AFTERNOON TIME IN'; break;
                        case 'pm_time_out': $modeLabel = '🌇 AFTERNOON TIME OUT'; break;
                    }
                ?>
                <?php if ($modeLabel): ?>
                <span class="badge fs-6 px-3 py-2 mt-2" style="background: rgba(255,255,255,0.25); border-radius: 20px;"><?php echo $modeLabel; ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert mb-4 border-0" role="alert" style="background: linear-gradient(135deg, #636e72 0%, #b2bec3 100%); color: white; border-radius: 15px; padding: 20px;">
            <div class="text-center">
                <h5 class="mb-1 fw-bold"><i class="fas fa-ban me-2"></i>Scanner is CLOSED</h5>
                <p class="mb-0"><?php echo $scannerStatus['message']; ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($message && $messageType === 'error'): ?>
        <div class="alert alert-danger-gradient" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($message && $messageType === 'warning'): ?>
        <div class="alert mb-4 border-0" role="alert" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; border-radius: 15px; padding: 20px;">
            <div class="text-center">
                <div style="background: rgba(255,255,255,0.2); width: 70px; height: 70px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-circle fa-3x text-white"></i>
                </div>
                <h4 class="fw-bold mb-2"><i class="fas fa-ban me-2"></i>Already Scanned!</h4>
                <p class="mb-1" style="font-size: 1.1rem;"><?php echo htmlspecialchars($message); ?></p>
                <?php if ($student): ?>
                <hr style="border-color: rgba(255,255,255,0.3);">
                <p class="mb-0">
                    <i class="fas fa-user me-1"></i>
                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                    — <?php echo htmlspecialchars($student['student_id']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- TIME IN / TIME OUT Visual Indicator Card -->
        <?php if ($student && $messageType === 'success'): ?>
        <div class="card mb-4 border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <?php
                $isTimeIn = ($newStatus === 'in');
                $periodLabel = ($scanPeriod === 'am') ? 'MORNING' : 'AFTERNOON';
                $statusLabel = $isTimeIn ? 'TIME IN' : 'TIME OUT';
                $periodEmoji = ($scanPeriod === 'am') ? '🌅' : '🌇';
                
                if ($isTimeIn) {
                    $gradientBg = ($scanPeriod === 'am') 
                        ? 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)' 
                        : 'linear-gradient(135deg, #2980b9 0%, #6dd5fa 100%)';
                    $iconColor = ($scanPeriod === 'am') ? '#11998e' : '#2980b9';
                } else {
                    $gradientBg = ($scanPeriod === 'am') 
                        ? 'linear-gradient(135deg, #e53935 0%, #f5576c 100%)' 
                        : 'linear-gradient(135deg, #8e44ad 0%, #c39bd3 100%)';
                    $iconColor = ($scanPeriod === 'am') ? '#e53935' : '#8e44ad';
                }
            ?>
            <div style="background: <?php echo $gradientBg; ?>; padding: 40px 30px; text-align: center;">
                <div style="background: rgba(255,255,255,0.2); width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fas fa-<?php echo $isTimeIn ? 'sign-in-alt' : 'sign-out-alt'; ?> fa-3x text-white"></i>
                </div>
                <h2 class="text-white fw-bold mb-1" style="font-size: 2rem;"><?php echo $periodEmoji; ?> <?php echo $periodLabel; ?> <?php echo $statusLabel; ?></h2>
                <p class="text-white mb-0" style="font-size: 1.5rem; font-weight: 600;">
                    <i class="fas fa-clock me-2"></i><?php echo $scanTime; ?>
                </p>
            </div>

            <!-- Student Info Section -->
            <div class="card-body text-center py-4" style="background: #fff;">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-4x" style="color: <?php echo $iconColor; ?>;"></i>
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
                    <span class="badge fs-6 px-4 py-2" style="background: <?php echo $gradientBg; ?>; border-radius: 25px;">
                        <i class="fas fa-check-circle me-2"></i><?php echo $periodLabel; ?> <?php echo $isTimeIn ? 'Checked In' : 'Checked Out'; ?> Successfully
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Camera Scanner -->
        <?php if ($scannerStatus['is_open']): ?>
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
        <?php else: ?>
        <div class="scanner-container mb-4 text-center py-5">
            <i class="fas fa-lock fa-4x mb-3" style="color: #b2bec3;"></i>
            <h4 class="text-muted">Scanner is Currently Closed</h4>
            <p class="text-muted mb-3">The QR scanner is only available during scheduled event windows.</p>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="card mt-4">
            <div class="card-body">
                <h5><i class="fas fa-info-circle me-2"></i>How to use:</h5>
                <ol class="mb-0">
                    <li>Click "Start Camera Scanner" to open your device camera</li>
                    <li>Point your camera at the QR code displayed on the student's device or printed card</li>
                    <li>The system will automatically record attendance when a valid QR is detected</li>
                    <li>Your scan will be recorded as Morning or Afternoon based on the active event window</li>
                    <li>Alternatively, you can manually enter the token or student ID</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php if ($scannerStatus['is_open']): ?>
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
            window.location.href = '<?php echo BASE_URL; ?>scan.php?token=' + encodeURIComponent(decodedText);
        });
    } else {
        window.location.href = '<?php echo BASE_URL; ?>scan.php?token=' + encodeURIComponent(decodedText);
    }
}

function onScanFailure(error) {
    // Silently ignore scan failures (camera is still scanning)
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>