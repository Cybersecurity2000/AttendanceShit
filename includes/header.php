<?php
/**
 * QRCodex Header Template
 */

if (!defined('QRCODEX_ROOT')) {
    define('QRCODEX_ROOT', dirname(__DIR__));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Background IBM Logo Watermark -->
    <div class="bg-watermark" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 0; pointer-events: none; opacity: 0.06;">
        <img src="<?php echo BASE_URL; ?>logos/IBMLogo.png" alt="" style="width: 700px; max-width: 90vw; height: auto;">
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-bottom: 3px solid #c9a227;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?>logos/nbsclogo.png" alt="NBSC" style="height: 40px; margin-right: 10px;">
                <i class="fas fa-qrcode me-2" style="color: #d4af37;"></i>
                <span style="color: #d4af37; font-weight: 700;"><?php echo SITE_NAME; ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/students.php"><i class="fas fa-users me-1"></i> Students</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/attendance.php"><i class="fas fa-clipboard-list me-1"></i> Attendance</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/scan.php"><i class="fas fa-camera me-1"></i> Scan</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>"><i class="fas fa-home me-1"></i> Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>register.php"><i class="fas fa-user-plus me-1"></i> Register</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/login.php"><i class="fas fa-user-shield me-1"></i> Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-4" style="position: relative; z-index: 1;">