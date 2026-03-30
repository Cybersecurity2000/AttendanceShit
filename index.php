<?php
/**
 * QRCodex - Main Landing Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$stats = getAttendanceStats();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="text-center mb-5">
            <div class="mb-4">
                <img src="<?php echo BASE_URL; ?>logos/nbsclogo.png" alt="NBSC Logo" style="height: 90px; margin-right: 15px; vertical-align: middle;">
                <img src="<?php echo BASE_URL; ?>logos/IBMLogo.png" alt="IBM Logo" style="height: 90px; margin-left: 15px; vertical-align: middle;">
            </div>
            <h1 class="display-4 fw-bold mb-3" style="color: #2c2c2c;">
                <i class="fas fa-qrcode me-3"></i>QRCodex
            </h1>
            <p class="lead" style="color: #4a4a4a;">
                Modern QR Code Attendance System IBM Students
            </p>
        </div>

        <!-- Statistics -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #c9a227 0%, #d4af37 100%); color: white;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['total_students']; ?></h3>
                    <p class="text-muted mb-0">Total Students</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #b8960c 0%, #e6c744 100%); color: white;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['today_count']; ?></h3>
                    <p class="text-muted mb-0">Today's Attendance</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #a68523 0%, #d4af37 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['recent_count']; ?></h3>
                    <p class="text-muted mb-0">Last Hour</p>
                </div>
            </div>
        </div>

        <!-- Feature Card - Register Only -->
        <div class="row g-4 justify-content-center">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-user-plus fa-4x" style="color: #c9a227;"></i>
                        </div>
                        <h4>Register Student</h4>
                        <p class="text-muted mb-4">
                            New students can register and get their unique QR code instantly.
                        </p>
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-success-custom">
                            <i class="fas fa-user-plus me-2"></i>Register Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>