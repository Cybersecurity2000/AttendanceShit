<?php
/**
 * QRCodex - Student Registration Page
 * Students can self-register, choose year level & major, and get QR code instantly.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Register Student';
$error = '';
$success = '';
$qrCodeUrl = '';
$registeredStudent = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['student_id'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $yearLevel = trim($_POST['year_level'] ?? '');
    $major = trim($_POST['major'] ?? '');

    // Validation
    if (empty($studentId) || empty($firstName) || empty($lastName)) {
        $error = 'Please fill in all required fields (Student ID, First Name, Last Name).';
    } elseif (empty($yearLevel)) {
        $error = 'Please select your Year Level.';
    } elseif (empty($major)) {
        $error = 'Please select your Major.';
    } else {
        try {
            // Use major as the course field
            $qrToken = addStudent($studentId, $firstName, $lastName, $email, $major, $yearLevel);
            $success = 'Registration successful! Your QR code has been generated below.';
            
            // Generate QR code URL for display
            $qrCodeUrl = createQRCode(BASE_URL . 'scan.php?token=' . $qrToken);
            $registeredStudent = [
                'student_id' => $studentId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'year_level' => $yearLevel,
                'major' => $major,
                'qr_token' => $qrToken,
            ];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = 'Student ID already exists. Please use a different Student ID.';
            } else {
                $error = 'Error during registration: ' . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-4">
            <h2 class="text-white"><i class="fas fa-user-plus me-2"></i>Student Registration</h2>
            <p class="text-white opacity-75">Register to get your unique QR code for attendance</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger-gradient" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success && $registeredStudent): ?>
        <!-- Success: Show QR Code -->
        <div class="card mb-4">
            <div class="card-body text-center p-5">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x" style="color: #11998e;"></i>
                </div>
                <h3 class="mb-2">Registration Successful!</h3>
                <p class="text-muted mb-4">Welcome, <strong><?php echo htmlspecialchars($registeredStudent['first_name'] . ' ' . $registeredStudent['last_name']); ?></strong></p>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="qr-display mb-4">
                            <h5 class="mb-3">Your QR Code</h5>
                            <img src="<?php echo $qrCodeUrl; ?>" alt="Your QR Code" class="img-fluid mb-3" style="max-width: 250px;">
                            
                            <div class="d-grid gap-2">
                                <button onclick="printQRCode()" class="btn btn-primary-custom">
                                    <i class="fas fa-print me-2"></i>Print QR Code
                                </button>
                                <a href="<?php echo $qrCodeUrl; ?>" download="qr_<?php echo htmlspecialchars($registeredStudent['student_id']); ?>.png" class="btn btn-success-custom">
                                    <i class="fas fa-download me-2"></i>Download QR Code
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Info Summary -->
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="p-3 bg-light rounded">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted text-end" style="width: 40%;">Student ID:</td>
                                    <td class="text-start"><strong><?php echo htmlspecialchars($registeredStudent['student_id']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-end">Name:</td>
                                    <td class="text-start"><strong><?php echo htmlspecialchars($registeredStudent['first_name'] . ' ' . $registeredStudent['last_name']); ?></strong></td>
                                </tr>
                                <?php if (!empty($registeredStudent['email'])): ?>
                                <tr>
                                    <td class="text-muted text-end">Email:</td>
                                    <td class="text-start"><?php echo htmlspecialchars($registeredStudent['email']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted text-end">Year Level:</td>
                                    <td class="text-start"><?php echo htmlspecialchars($registeredStudent['year_level']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-end">Major:</td>
                                    <td class="text-start"><?php echo htmlspecialchars($registeredStudent['major']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-user-plus me-2"></i>Register Another Student
                    </a>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary-custom">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>

        <script>
        function printQRCode() {
            const printContent = `
                <html>
                <head>
                    <title>QR Code - <?php echo htmlspecialchars($registeredStudent['student_id']); ?></title>
                    <style>
                        body { text-align: center; padding: 50px; font-family: Arial, sans-serif; }
                        h2 { margin-bottom: 5px; }
                        .info { color: #666; margin-bottom: 5px; }
                        img { max-width: 300px; border: 5px solid #667eea; border-radius: 10px; padding: 20px; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <h2><?php echo htmlspecialchars($registeredStudent['first_name'] . ' ' . $registeredStudent['last_name']); ?></h2>
                    <p class="info">Student ID: <?php echo htmlspecialchars($registeredStudent['student_id']); ?></p>
                    <p class="info"><?php echo htmlspecialchars($registeredStudent['major']); ?> - <?php echo htmlspecialchars($registeredStudent['year_level']); ?></p>
                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
                </body>
                </html>
            `;
            
            const printWindow = window.open('', '', 'width=600,height=800');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
        </script>

        <?php else: ?>
        <!-- Registration Form -->
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Registration Form</h4>
            </div>
            <div class="card-body p-4">
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
                            <label for="year_level" class="form-label">Year Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="year_level" name="year_level" required>
                                <option value="">Select Year Level</option>
                                <option value="1st Year" <?php echo ($_POST['year_level'] ?? '') === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo ($_POST['year_level'] ?? '') === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo ($_POST['year_level'] ?? '') === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo ($_POST['year_level'] ?? '') === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="major" class="form-label">Major <span class="text-danger">*</span></label>
                            <select class="form-select" id="major" name="major" required>
                                <option value="">Select Major</option>
                                <option value="Operations Management" <?php echo ($_POST['major'] ?? '') === 'Operations Management' ? 'selected' : ''; ?>>Operations Management</option>
                                <option value="Financial Management" <?php echo ($_POST['major'] ?? '') === 'Financial Management' ? 'selected' : ''; ?>>Financial Management</option>
                                <option value="Marketing Management" <?php echo ($_POST['major'] ?? '') === 'Marketing Management' ? 'selected' : ''; ?>>Marketing Management</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                        <button type="submit" class="btn btn-success-custom">
                            <i class="fas fa-user-plus me-2"></i>Register & Generate QR Code
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions -->
        <div class="card mt-4">
            <div class="card-body">
                <h5><i class="fas fa-info-circle me-2"></i>How Registration Works:</h5>
                <ol class="mb-0">
                    <li>Fill in your Student ID, Name, Year Level, and Major</li>
                    <li>Click "Register & Generate QR Code"</li>
                    <li>Your unique QR code will be generated instantly</li>
                    <li>Download or print your QR code for attendance scanning</li>
                    <li>Present your QR code to the admin scanner for attendance</li>
                </ol>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>