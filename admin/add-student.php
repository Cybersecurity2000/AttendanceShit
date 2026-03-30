<?php
/**
 * QRCodex - Add Student Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'Add Student';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['student_id'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $yearLevel = trim($_POST['year_level'] ?? '');
    
    // Validation
    if (empty($studentId) || empty($firstName) || empty($lastName)) {
        $error = 'Please fill in all required fields (Student ID, First Name, Last Name).';
    } else {
        try {
            $qrToken = addStudent($studentId, $firstName, $lastName, $email, $course, $yearLevel);
            $success = 'Student added successfully! QR Token: ' . $qrToken;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = 'Student ID already exists.';
            } else {
                $error = 'Error adding student: ' . $e->getMessage();
            }
        }
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
                <a href="<?php echo BASE_URL; ?>admin/add-student.php" class="nav-link-custom active">
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
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Student</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger-gradient" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-gradient" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="student_id" name="student_id" 
                                   placeholder="e.g., 2024-001" required
                                   value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="student@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   placeholder="Enter first name" required
                                   value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   placeholder="Enter last name" required
                                   value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="course" class="form-label">Course</label>
                            <input type="text" class="form-control" id="course" name="course" 
                                   placeholder="e.g., Bachelor of Science in Computer Science"
                                   value="<?php echo htmlspecialchars($_POST['course'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="year_level" class="form-label">Year Level</label>
                            <select class="form-select" id="year_level" name="year_level">
                                <option value="">Select Year Level</option>
                                <option value="1st Year" <?php echo ($_POST['year_level'] ?? '') === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo ($_POST['year_level'] ?? '') === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo ($_POST['year_level'] ?? '') === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo ($_POST['year_level'] ?? '') === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                                <option value="5th Year" <?php echo ($_POST['year_level'] ?? '') === '5th Year' ? 'selected' : ''; ?>>5th Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="<?php echo BASE_URL; ?>admin/students.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save me-2"></i>Add Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>