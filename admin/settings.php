<?php
/**
 * QRCodex - Admin Settings Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'Settings';
$success = '';
$error = '';

// Handle password update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all password fields.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirm password do not match.';
    } else {
        // Verify current password and update
        $adminId = $_SESSION['admin_id'];
        $result = updateAdminPassword($adminId, $currentPassword, $newPassword);

        if ($result === true) {
            $success = 'Password updated successfully! Your new password is now active.';
        } else {
            $error = $result; // Error message from function
        }
    }
}

// Get current admin info
$adminInfo = getAdminById($_SESSION['admin_id']);
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
                <a href="<?php echo BASE_URL; ?>admin/qr-generator.php" class="nav-link-custom">
                    <i class="fas fa-qrcode me-2"></i>QR Generator
                </a>
                <a href="<?php echo BASE_URL; ?>admin/event-scheduler.php" class="nav-link-custom">
                    <i class="fas fa-calendar-alt me-2"></i>Event Scheduler
                </a>
                <a href="<?php echo BASE_URL; ?>admin/settings.php" class="nav-link-custom active">
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
            <h2><i class="fas fa-cog me-2"></i>Settings</h2>
            <span class="text-muted"><?php echo date('F d, Y'); ?></span>
        </div>

        <!-- Success Message -->
        <?php if ($success): ?>
        <div class="alert alert-gradient" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="alert alert-danger-gradient" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Admin Profile Info -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="fas fa-user-shield me-2" style="color: #c9a227;"></i>Admin Profile</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Full Name:</strong> <?php echo htmlspecialchars($adminInfo['full_name'] ?? 'N/A'); ?></p>
                        <p class="mb-2"><strong>Username:</strong> <?php echo htmlspecialchars($adminInfo['username'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($adminInfo['email'] ?? 'N/A'); ?></p>
                        <p class="mb-2"><strong>Account Created:</strong> <?php echo isset($adminInfo['created_at']) ? date('M d, Y', strtotime($adminInfo['created_at'])) : 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="card">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="fas fa-key me-2" style="color: #c9a227;"></i>Change Password</h5>
                <p class="text-muted mb-4">Update your admin password. The new password will be used for your next login.</p>

                <form method="POST" action="" id="passwordForm">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Current Password -->
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="current_password" name="current_password" 
                                           placeholder="Enter your current password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           placeholder="Enter new password (min 6 characters)" required minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Password must be at least 6 characters long.</div>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm your new password" required minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="password-match-feedback" class="form-text"></div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary-custom" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary ms-2" style="border-radius: 25px; padding: 10px 30px;">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '
<script>
// Toggle password visibility
function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon = btn.querySelector("i");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Real-time password match validation
var newPass = document.getElementById("new_password");
var confirmPass = document.getElementById("confirm_password");
var feedback = document.getElementById("password-match-feedback");
var submitBtn = document.getElementById("submitBtn");

function checkPasswordMatch() {
    if (confirmPass.value === "") {
        feedback.textContent = "";
        feedback.className = "form-text";
        return;
    }
    if (newPass.value === confirmPass.value) {
        feedback.textContent = "✓ Passwords match";
        feedback.className = "form-text text-success";
        confirmPass.classList.remove("is-invalid");
        confirmPass.classList.add("is-valid");
    } else {
        feedback.textContent = "✗ Passwords do not match";
        feedback.className = "form-text text-danger";
        confirmPass.classList.remove("is-valid");
        confirmPass.classList.add("is-invalid");
    }
}

newPass.addEventListener("input", checkPasswordMatch);
confirmPass.addEventListener("input", checkPasswordMatch);

// Form submission validation
document.getElementById("passwordForm").addEventListener("submit", function(e) {
    if (newPass.value !== confirmPass.value) {
        e.preventDefault();
        alert("New password and confirm password do not match!");
        confirmPass.focus();
    }
});
</script>
';
?>

<?php include __DIR__ . '/../includes/footer.php'; ?>