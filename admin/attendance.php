<?php
/**
 * QRCodex - Attendance Monitoring Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'Attendance';
$records = getConsolidatedAttendanceRecords(100);
$filterDate = $_GET['date'] ?? date('Y-m-d');

if (!empty($filterDate)) {
    $records = getConsolidatedAttendanceByDate($filterDate);
}

$stats = getAttendanceStats();
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
                <a href="<?php echo BASE_URL; ?>admin/attendance.php" class="nav-link-custom active">
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
            <h2><i class="fas fa-clipboard-list me-2"></i>Attendance Monitor</h2>
            <a href="<?php echo BASE_URL; ?>admin/scan.php" class="btn btn-primary-custom">
                <i class="fas fa-camera me-2"></i>Open Scanner
            </a>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['today_count']; ?></h3>
                    <p class="text-muted mb-0 small">Today's Attendance</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #c9a227 0%, #d4af37 100%); color: white;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['total_students']; ?></h3>
                    <p class="text-muted mb-0 small">Total Students</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #a68523 0%, #e6c744 100%); color: white;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['recent_count']; ?></h3>
                    <p class="text-muted mb-0 small">Last Hour</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #c9a227 0%, #d4af37 100%); color: white;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['week_count']; ?></h3>
                    <p class="text-muted mb-0 small">This Week</p>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Filter by Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo $filterDate; ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                        <a href="<?php echo BASE_URL; ?>admin/attendance.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="text-muted">Showing <?php echo count($records); ?> records</span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Attendance Records</h5>
                <button onclick="exportToExcel()" class="btn btn-sm btn-success-custom">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></strong>
                                </td>
                                <td><strong><?php echo htmlspecialchars($record['student_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars(($record['first_name'] ?? 'Unknown') . ' ' . ($record['last_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($record['course'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['year_level'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (!empty($record['time_in'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-sign-in-alt me-1"></i><?php echo date('h:i:s A', strtotime($record['time_in'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record['time_out'])): ?>
                                        <span class="badge bg-danger"><i class="fas fa-sign-out-alt me-1"></i><?php echo date('h:i:s A', strtotime($record['time_out'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No attendance records found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    let html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    html += '<head>';
    html += '<meta charset="UTF-8">';
    html += '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
    html += '<x:Name>Attendance</x:Name>';
    html += '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
    html += '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    html += '<style>';
    html += 'td, th { mso-number-format:"\\@"; white-space: nowrap; padding: 5px 10px; }';
    html += 'th { background-color: #c9a227; color: white; font-weight: bold; }';
    html += '</style>';
    html += '</head><body>';
    html += '<table border="1" cellpadding="5" cellspacing="0">';
    
    html += '<tr>';
    html += '<th>Date</th>';
    html += '<th>Student ID</th>';
    html += '<th>Name</th>';
    html += '<th>Course</th>';
    html += '<th>Year</th>';
    html += '<th>Time In</th>';
    html += '<th>Time Out</th>';
    html += '</tr>';
    
    <?php if (!empty($records)): ?>
    <?php foreach ($records as $record): ?>
    html += '<tr>';
    html += '<td style="mso-number-format:\'\\@\';"><?php echo date("M d, Y", strtotime($record["attendance_date"])); ?></td>';
    html += '<td style="mso-number-format:\'\\@\';"><?php echo htmlspecialchars($record["student_id"]); ?></td>';
    html += '<td style="mso-number-format:\'\\@\';"><?php echo htmlspecialchars(($record["first_name"] ?? "Unknown") . " " . ($record["last_name"] ?? "")); ?></td>';
    html += '<td style="mso-number-format:\'\\@\';"><?php echo htmlspecialchars($record["course"] ?? "N/A"); ?></td>';
    html += '<td style="mso-number-format:\'\\@\';"><?php echo htmlspecialchars($record["year_level"] ?? "N/A"); ?></td>';
    html += '<td style="mso-number-format:\'\\@\';"><?php echo !empty($record["time_in"]) ? date("h:i:s A", strtotime($record["time_in"])) : "—"; ?></td>';
    html += '<td style="mso-number-format:\'\\@\';"><?php echo !empty($record["time_out"]) ? date("h:i:s A", strtotime($record["time_out"])) : "—"; ?></td>';
    html += '</tr>';
    <?php endforeach; ?>
    <?php endif; ?>
    
    html += '</table></body></html>';
    
    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'attendance_<?php echo $filterDate; ?>.xls';
    link.click();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>