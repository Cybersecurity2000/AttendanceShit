<?php
/**
 * QRCodex - Student Management Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'Students';
$students = getAllStudents();
$search = $_GET['search'] ?? '';
$filteredStudents = $students;

// Filter by search
if (!empty($search)) {
    $filteredStudents = array_filter($students, function($student) use ($search) {
        return stripos($student['student_id'], $search) !== false ||
               stripos($student['first_name'], $search) !== false ||
               stripos($student['last_name'], $search) !== false ||
               stripos($student['course'] ?? '', $search) !== false;
    });
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (deleteStudent($_GET['delete'])) {
        header("Location: " . BASE_URL . "admin/students.php?deleted=1");
        exit;
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
            <h2><i class="fas fa-users me-2"></i>Student Management</h2>
            <a href="<?php echo BASE_URL; ?>admin/add-student.php" class="btn btn-primary-custom">
                <i class="fas fa-plus me-2"></i>Add Student
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-gradient" role="alert">
            <i class="fas fa-check-circle me-2"></i>Student deleted successfully!
        </div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by Student ID, Name, or Course..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Student List -->
        <div class="table-container">
            <h5 class="mb-3">Total Students: <?php echo count($filteredStudents); ?></h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>QR Token</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($filteredStudents)): ?>
                            <?php foreach ($filteredStudents as $student): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></td>
                                <td>
                                    <small class="text-muted" title="<?php echo htmlspecialchars($student['qr_token']); ?>">
                                        <?php echo substr($student['qr_token'], 0, 20) . '...'; ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>admin/view-student.php?id=<?php echo $student['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" title="View QR">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>admin/students.php?delete=<?php echo $student['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this student?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <?php echo empty($search) ? 'No students found. <a href="' . BASE_URL . 'admin/add-student.php">Add a student</a>' : 'No students match your search.'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>