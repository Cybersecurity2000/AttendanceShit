<?php
/**
 * QRCodex - Admin Dashboard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'Dashboard';
$stats = getAttendanceStats();
$recentAttendance = getConsolidatedAttendanceRecords(20);
$students = getAllStudents();
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
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="nav-link-custom active">
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
            <h2><i class="fas fa-chart-line me-2"></i>Dashboard</h2>
            <span class="text-muted"><?php echo date('F d, Y'); ?></span>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['today_count']; ?></h3>
                    <p class="text-muted mb-0">Today's Attendance</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #a68523 0%, #e6c744 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['week_count']; ?></h3>
                    <p class="text-muted mb-0">This Week</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="<?php echo BASE_URL; ?>admin/add-student.php" class="btn btn-primary-custom w-100">
                    <i class="fas fa-user-plus me-2"></i>Add Student
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?php echo BASE_URL; ?>admin/qr-generator.php" class="btn btn-success-custom w-100">
                    <i class="fas fa-qrcode me-2"></i>Generate QR
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?php echo BASE_URL; ?>admin/scan.php" class="btn btn-primary-custom w-100" style="background: linear-gradient(135deg, #a68523 0%, #c9a227 50%, #d4af37 100%);">
                    <i class="fas fa-camera me-2"></i>Start Scanner
                </a>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-history me-2"></i>Recent Attendance</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentAttendance)): ?>
                            <?php foreach ($recentAttendance as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars(($record['first_name'] ?? 'Unknown') . ' ' . ($record['last_name'] ?? '')); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($record['course'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                                <td>
                                    <?php if (!empty($record['time_in'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-sign-in-alt me-1"></i><?php echo date('h:i A', strtotime($record['time_in'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record['time_out'])): ?>
                                        <span class="badge bg-danger"><i class="fas fa-sign-out-alt me-1"></i><?php echo date('h:i A', strtotime($record['time_out'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No attendance records yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>