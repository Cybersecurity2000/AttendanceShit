<?php
/**
 * QRCodex - Event Scheduler Page
 * Admin can set AM and PM time-in and time-out windows.
 * The scanner will only be active during these scheduled windows.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}

$pageTitle = 'Event Scheduler';
$success = '';
$error = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $eventName = trim($_POST['event_name'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $amTimeInStart = $_POST['am_time_in_start'] ?? '';
        $amTimeInEnd = $_POST['am_time_in_end'] ?? '';
        $amTimeOutStart = $_POST['am_time_out_start'] ?? '';
        $amTimeOutEnd = $_POST['am_time_out_end'] ?? '';
        $pmTimeInStart = $_POST['pm_time_in_start'] ?? '';
        $pmTimeInEnd = $_POST['pm_time_in_end'] ?? '';
        $pmTimeOutStart = $_POST['pm_time_out_start'] ?? '';
        $pmTimeOutEnd = $_POST['pm_time_out_end'] ?? '';

        if (empty($eventName) || empty($eventDate) || empty($amTimeInStart) || empty($amTimeInEnd) || empty($amTimeOutStart) || empty($amTimeOutEnd) || empty($pmTimeInStart) || empty($pmTimeInEnd) || empty($pmTimeOutStart) || empty($pmTimeOutEnd)) {
            $error = 'Please fill in all fields.';
        } elseif ($amTimeInStart >= $amTimeInEnd) {
            $error = 'Morning Time In Start must be before Morning Time In End.';
        } elseif ($amTimeOutStart >= $amTimeOutEnd) {
            $error = 'Morning Time Out Start must be before Morning Time Out End.';
        } elseif ($pmTimeInStart >= $pmTimeInEnd) {
            $error = 'Afternoon Time In Start must be before Afternoon Time In End.';
        } elseif ($pmTimeOutStart >= $pmTimeOutEnd) {
            $error = 'Afternoon Time Out Start must be before Afternoon Time Out End.';
        } else {
            try {
                createEventSchedule($eventName, $eventDate, $amTimeInStart, $amTimeInEnd, $amTimeOutStart, $amTimeOutEnd, $pmTimeInStart, $pmTimeInEnd, $pmTimeOutStart, $pmTimeOutEnd, $_SESSION['admin_id']);
                $success = 'Event schedule created successfully!';
            } catch (Exception $e) {
                $error = 'Error creating event: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update') {
        $id = $_POST['event_id'] ?? '';
        $eventName = trim($_POST['event_name'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $amTimeInStart = $_POST['am_time_in_start'] ?? '';
        $amTimeInEnd = $_POST['am_time_in_end'] ?? '';
        $amTimeOutStart = $_POST['am_time_out_start'] ?? '';
        $amTimeOutEnd = $_POST['am_time_out_end'] ?? '';
        $pmTimeInStart = $_POST['pm_time_in_start'] ?? '';
        $pmTimeInEnd = $_POST['pm_time_in_end'] ?? '';
        $pmTimeOutStart = $_POST['pm_time_out_start'] ?? '';
        $pmTimeOutEnd = $_POST['pm_time_out_end'] ?? '';

        if (empty($id) || empty($eventName) || empty($eventDate) || empty($amTimeInStart) || empty($amTimeInEnd) || empty($amTimeOutStart) || empty($amTimeOutEnd) || empty($pmTimeInStart) || empty($pmTimeInEnd) || empty($pmTimeOutStart) || empty($pmTimeOutEnd)) {
            $error = 'Please fill in all fields.';
        } elseif ($amTimeInStart >= $amTimeInEnd) {
            $error = 'Morning Time In Start must be before Morning Time In End.';
        } elseif ($amTimeOutStart >= $amTimeOutEnd) {
            $error = 'Morning Time Out Start must be before Morning Time Out End.';
        } elseif ($pmTimeInStart >= $pmTimeInEnd) {
            $error = 'Afternoon Time In Start must be before Afternoon Time In End.';
        } elseif ($pmTimeOutStart >= $pmTimeOutEnd) {
            $error = 'Afternoon Time Out Start must be before Afternoon Time Out End.';
        } else {
            try {
                updateEventSchedule($id, $eventName, $eventDate, $amTimeInStart, $amTimeInEnd, $amTimeOutStart, $amTimeOutEnd, $pmTimeInStart, $pmTimeInEnd, $pmTimeOutStart, $pmTimeOutEnd);
                $success = 'Event schedule updated successfully!';
            } catch (Exception $e) {
                $error = 'Error updating event: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['event_id'] ?? '';
        if (!empty($id)) {
            try {
                deleteEventSchedule($id);
                $success = 'Event schedule deleted successfully!';
            } catch (Exception $e) {
                $error = 'Error deleting event: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['event_id'] ?? '';
        if (!empty($id)) {
            try {
                toggleEventScheduleStatus($id);
                $success = 'Event status toggled successfully!';
            } catch (Exception $e) {
                $error = 'Error toggling event: ' . $e->getMessage();
            }
        }
    }
}

// Get all event schedules
$events = getAllEventSchedules();

// Get current scanner status
$scannerStatus = isScannerOpen();

// Check if editing
$editEvent = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editEvent = getEventScheduleById($_GET['edit']);
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
                <a href="<?php echo BASE_URL; ?>admin/scan.php" class="nav-link-custom">
                    <i class="fas fa-camera me-2"></i>Scan QR
                </a>
                <a href="<?php echo BASE_URL; ?>admin/qr-generator.php" class="nav-link-custom">
                    <i class="fas fa-qrcode me-2"></i>QR Generator
                </a>
                <a href="<?php echo BASE_URL; ?>admin/event-scheduler.php" class="nav-link-custom active">
                    <i class="fas fa-calendar-alt me-2"></i>Event Scheduler
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
            <h2><i class="fas fa-calendar-alt me-2"></i>Event Scheduler</h2>
            <span class="text-muted"><?php echo date('F d, Y'); ?></span>
        </div>

        <!-- Current Scanner Status -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <?php if ($scannerStatus['is_open']): ?>
            <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 25px 30px;">
                <div class="d-flex align-items-center">
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 20px;">
                        <i class="fas fa-broadcast-tower fa-2x text-white"></i>
                    </div>
                    <div>
                        <h4 class="text-white fw-bold mb-1">
                            <i class="fas fa-circle text-white me-2" style="font-size: 12px; animation: pulse 1s infinite;"></i>
                            Scanner is OPEN
                        </h4>
                        <p class="text-white mb-0 opacity-75"><?php echo $scannerStatus['message']; ?></p>
                        <?php if ($scannerStatus['mode'] === 'am_time_in'): ?>
                            <span class="badge mt-2" style="background: rgba(255,255,255,0.3); font-size: 0.9rem;">🌅 MORNING TIME IN Window Active</span>
                        <?php elseif ($scannerStatus['mode'] === 'am_time_out'): ?>
                            <span class="badge mt-2" style="background: rgba(255,255,255,0.3); font-size: 0.9rem;">🌅 MORNING TIME OUT Window Active</span>
                        <?php elseif ($scannerStatus['mode'] === 'pm_time_in'): ?>
                            <span class="badge mt-2" style="background: rgba(255,255,255,0.3); font-size: 0.9rem;">🌇 AFTERNOON TIME IN Window Active</span>
                        <?php elseif ($scannerStatus['mode'] === 'pm_time_out'): ?>
                            <span class="badge mt-2" style="background: rgba(255,255,255,0.3); font-size: 0.9rem;">🌇 AFTERNOON TIME OUT Window Active</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div style="background: linear-gradient(135deg, #636e72 0%, #b2bec3 100%); padding: 25px 30px;">
                <div class="d-flex align-items-center">
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 20px;">
                        <i class="fas fa-ban fa-2x text-white"></i>
                    </div>
                    <div>
                        <h4 class="text-white fw-bold mb-1">Scanner is CLOSED</h4>
                        <p class="text-white mb-0 opacity-75"><?php echo $scannerStatus['message']; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
        <div class="alert alert-gradient" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger-gradient" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Create / Edit Event Form -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-<?php echo $editEvent ? 'edit' : 'plus-circle'; ?> me-2" style="color: #c9a227;"></i>
                    <?php echo $editEvent ? 'Edit Event Schedule' : 'Create New Event Schedule'; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editEvent ? 'update' : 'create'; ?>">
                    <?php if ($editEvent): ?>
                    <input type="hidden" name="event_id" value="<?php echo $editEvent['id']; ?>">
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="event_name" class="form-label fw-bold">
                                <i class="fas fa-tag me-1"></i>Event Name
                            </label>
                            <input type="text" class="form-control" id="event_name" name="event_name" 
                                   placeholder="e.g., Morning Assembly, Seminar, Workshop..."
                                   value="<?php echo $editEvent ? htmlspecialchars($editEvent['event_name']) : ''; ?>"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <label for="event_date" class="form-label fw-bold">
                                <i class="fas fa-calendar me-1"></i>Event Date
                            </label>
                            <input type="date" class="form-control" id="event_date" name="event_date" 
                                   value="<?php echo $editEvent ? $editEvent['event_date'] : date('Y-m-d'); ?>"
                                   required>
                        </div>
                    </div>

                    <!-- MORNING (AM) Section -->
                    <h6 class="fw-bold mb-3 mt-4" style="color: #f39c12; font-size: 1.1rem;">
                        <i class="fas fa-sun me-2"></i>MORNING (AM)
                    </h6>

                    <div class="row mb-3">
                        <!-- AM Time In -->
                        <div class="col-md-6">
                            <div class="card" style="border: 2px solid rgba(17, 153, 142, 0.3); border-radius: 12px;">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color: #11998e;">
                                        <i class="fas fa-sign-in-alt me-2"></i>Time In
                                        <small class="text-muted fw-normal">(Morning check-in)</small>
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="am_time_in_start" class="form-label small">Opens At</label>
                                            <input type="time" class="form-control" id="am_time_in_start" name="am_time_in_start" 
                                                   value="<?php echo $editEvent ? substr($editEvent['am_time_in_start'], 0, 5) : '07:00'; ?>"
                                                   required>
                                        </div>
                                        <div class="col-6">
                                            <label for="am_time_in_end" class="form-label small">Closes At</label>
                                            <input type="time" class="form-control" id="am_time_in_end" name="am_time_in_end" 
                                                   value="<?php echo $editEvent ? substr($editEvent['am_time_in_end'], 0, 5) : '09:00'; ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- AM Time Out -->
                        <div class="col-md-6">
                            <div class="card" style="border: 2px solid rgba(229, 57, 53, 0.3); border-radius: 12px;">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color: #e53935;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Time Out
                                        <small class="text-muted fw-normal">(Morning check-out)</small>
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="am_time_out_start" class="form-label small">Opens At</label>
                                            <input type="time" class="form-control" id="am_time_out_start" name="am_time_out_start" 
                                                   value="<?php echo $editEvent ? substr($editEvent['am_time_out_start'], 0, 5) : '11:00'; ?>"
                                                   required>
                                        </div>
                                        <div class="col-6">
                                            <label for="am_time_out_end" class="form-label small">Closes At</label>
                                            <input type="time" class="form-control" id="am_time_out_end" name="am_time_out_end" 
                                                   value="<?php echo $editEvent ? substr($editEvent['am_time_out_end'], 0, 5) : '12:00'; ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AFTERNOON (PM) Section -->
                    <h6 class="fw-bold mb-3 mt-4" style="color: #e67e22; font-size: 1.1rem;">
                        <i class="fas fa-cloud-sun me-2"></i>AFTERNOON (PM)
                    </h6>

                    <div class="row mb-3">
                        <!-- PM Time In -->
                        <div class="col-md-6">
                            <div class="card" style="border: 2px solid rgba(41, 128, 185, 0.3); border-radius: 12px;">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color: #2980b9;">
                                        <i class="fas fa-sign-in-alt me-2"></i>Time In
                                        <small class="text-muted fw-normal">(Afternoon check-in)</small>
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="pm_time_in_start" class="form-label small">Opens At</label>
                                            <input type="time" class="form-control" id="pm_time_in_start" name="pm_time_in_start" 
                                                   value="<?php echo $editEvent ? substr($editEvent['pm_time_in_start'], 0, 5) : '13:00'; ?>"
                                                   required>
                                        </div>
                                        <div class="col-6">
                                            <label for="pm_time_in_end" class="form-label small">Closes At</label>
                                            <input type="time" class="form-control" id="pm_time_in_end" name="pm_time_in_end" 
                                                   value="<?php echo $editEvent ? substr($editEvent['pm_time_in_end'], 0, 5) : '14:00'; ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- PM Time Out -->
                        <div class="col-md-6">
                            <div class="card" style="border: 2px solid rgba(142, 68, 173, 0.3); border-radius: 12px;">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color: #8e44ad;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Time Out
                                        <small class="text-muted fw-normal">(Afternoon check-out)</small>
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="pm_time_out_start" class="form-label small">Opens At</label>
                                            <input type="time" class="form-control" id="pm_time_out_start" name="pm_time_out_start" 
                                                   value="<?php echo $editEvent ? substr($editEvent['pm_time_out_start'], 0, 5) : '16:00'; ?>"
                                                   required>
                                        </div>
                                        <div class="col-6">
                                            <label for="pm_time_out_end" class="form-label small">Closes At</label>
                                            <input type="time" class="form-control" id="pm_time_out_end" name="pm_time_out_end" 
                                                   value="<?php echo $editEvent ? substr($editEvent['pm_time_out_end'], 0, 5) : '18:00'; ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-<?php echo $editEvent ? 'save' : 'plus'; ?> me-2"></i>
                            <?php echo $editEvent ? 'Update Event' : 'Create Event'; ?>
                        </button>
                        <?php if ($editEvent): ?>
                        <a href="<?php echo BASE_URL; ?>admin/event-scheduler.php" class="btn btn-outline-secondary" style="border-radius: 25px; padding: 10px 30px;">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Today's Events -->
        <?php $todayEvents = getTodayEvents(); ?>
        <?php if (!empty($todayEvents)): ?>
        <div class="card mb-4" style="border-left: 4px solid #c9a227;">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2" style="color: #c9a227;"></i>Today's Events (<?php echo date('M d, Y'); ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php foreach ($todayEvents as $te): ?>
                <?php
                    $now = date('H:i:s');
                    $amInActive = ($now >= $te['am_time_in_start'] && $now <= $te['am_time_in_end']);
                    $amOutActive = ($now >= $te['am_time_out_start'] && $now <= $te['am_time_out_end']);
                    $pmInActive = ($now >= $te['pm_time_in_start'] && $now <= $te['pm_time_in_end']);
                    $pmOutActive = ($now >= $te['pm_time_out_start'] && $now <= $te['pm_time_out_end']);
                    $anyActive = $amInActive || $amOutActive || $pmInActive || $pmOutActive;
                ?>
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($te['event_name']); ?></h6>
                            
                            <!-- Morning Row -->
                            <div class="mb-2">
                                <small class="fw-bold text-muted d-block mb-1"><i class="fas fa-sun me-1" style="color: #f39c12;"></i>Morning</small>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge px-3 py-2" 
                                          style="background: <?php echo $amInActive ? 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)' : '#e0e0e0'; ?>; color: <?php echo $amInActive ? 'white' : '#666'; ?>; border-radius: 20px;">
                                        <i class="fas fa-sign-in-alt me-1"></i>
                                        IN: <?php echo date('h:i A', strtotime($te['am_time_in_start'])); ?> – <?php echo date('h:i A', strtotime($te['am_time_in_end'])); ?>
                                        <?php if ($amInActive): ?> <i class="fas fa-circle ms-1" style="font-size: 8px;"></i><?php endif; ?>
                                    </span>
                                    <span class="badge px-3 py-2" 
                                          style="background: <?php echo $amOutActive ? 'linear-gradient(135deg, #e53935 0%, #f5576c 100%)' : '#e0e0e0'; ?>; color: <?php echo $amOutActive ? 'white' : '#666'; ?>; border-radius: 20px;">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        OUT: <?php echo date('h:i A', strtotime($te['am_time_out_start'])); ?> – <?php echo date('h:i A', strtotime($te['am_time_out_end'])); ?>
                                        <?php if ($amOutActive): ?> <i class="fas fa-circle ms-1" style="font-size: 8px;"></i><?php endif; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Afternoon Row -->
                            <div>
                                <small class="fw-bold text-muted d-block mb-1"><i class="fas fa-cloud-sun me-1" style="color: #e67e22;"></i>Afternoon</small>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge px-3 py-2" 
                                          style="background: <?php echo $pmInActive ? 'linear-gradient(135deg, #2980b9 0%, #6dd5fa 100%)' : '#e0e0e0'; ?>; color: <?php echo $pmInActive ? 'white' : '#666'; ?>; border-radius: 20px;">
                                        <i class="fas fa-sign-in-alt me-1"></i>
                                        IN: <?php echo date('h:i A', strtotime($te['pm_time_in_start'])); ?> – <?php echo date('h:i A', strtotime($te['pm_time_in_end'])); ?>
                                        <?php if ($pmInActive): ?> <i class="fas fa-circle ms-1" style="font-size: 8px;"></i><?php endif; ?>
                                    </span>
                                    <span class="badge px-3 py-2" 
                                          style="background: <?php echo $pmOutActive ? 'linear-gradient(135deg, #8e44ad 0%, #c39bd3 100%)' : '#e0e0e0'; ?>; color: <?php echo $pmOutActive ? 'white' : '#666'; ?>; border-radius: 20px;">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        OUT: <?php echo date('h:i A', strtotime($te['pm_time_out_start'])); ?> – <?php echo date('h:i A', strtotime($te['pm_time_out_end'])); ?>
                                        <?php if ($pmOutActive): ?> <i class="fas fa-circle ms-1" style="font-size: 8px;"></i><?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if ($anyActive): ?>
                        <span class="badge bg-success px-3 py-2" style="border-radius: 20px;">
                            <i class="fas fa-broadcast-tower me-1"></i>LIVE
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- All Event Schedules Table -->
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-list me-2"></i>All Event Schedules</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Morning (AM)</th>
                            <th>Afternoon (PM)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($event['event_name']); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-success" style="border-radius: 15px; font-size: 0.75rem;">
                                            <i class="fas fa-sign-in-alt me-1"></i>IN: <?php echo date('h:i A', strtotime($event['am_time_in_start'])); ?> – <?php echo date('h:i A', strtotime($event['am_time_in_end'])); ?>
                                        </span>
                                        <span class="badge bg-danger" style="border-radius: 15px; font-size: 0.75rem;">
                                            <i class="fas fa-sign-out-alt me-1"></i>OUT: <?php echo date('h:i A', strtotime($event['am_time_out_start'])); ?> – <?php echo date('h:i A', strtotime($event['am_time_out_end'])); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge" style="border-radius: 15px; font-size: 0.75rem; background: #2980b9;">
                                            <i class="fas fa-sign-in-alt me-1"></i>IN: <?php echo date('h:i A', strtotime($event['pm_time_in_start'])); ?> – <?php echo date('h:i A', strtotime($event['pm_time_in_end'])); ?>
                                        </span>
                                        <span class="badge" style="border-radius: 15px; font-size: 0.75rem; background: #8e44ad;">
                                            <i class="fas fa-sign-out-alt me-1"></i>OUT: <?php echo date('h:i A', strtotime($event['pm_time_out_start'])); ?> – <?php echo date('h:i A', strtotime($event['pm_time_out_end'])); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($event['is_active']): ?>
                                        <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px;">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-3 py-2" style="border-radius: 15px;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="<?php echo BASE_URL; ?>admin/event-scheduler.php?edit=<?php echo $event['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Edit" style="border-radius: 20px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $event['is_active'] ? 'warning' : 'success'; ?>" 
                                                    title="<?php echo $event['is_active'] ? 'Deactivate' : 'Activate'; ?>"
                                                    style="border-radius: 20px;">
                                                <i class="fas fa-<?php echo $event['is_active'] ? 'pause' : 'play'; ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" style="border-radius: 20px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                    No event schedules created yet. Create one above to control scanner availability.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- How It Works -->
        <div class="card mt-4">
            <div class="card-body">
                <h5><i class="fas fa-info-circle me-2" style="color: #c9a227;"></i>How Event Scheduler Works</h5>
                <ol class="mb-0">
                    <li><strong>Create an event</strong> with a date, morning (AM) windows, and afternoon (PM) windows.</li>
                    <li>The <strong>Morning Time In</strong> defines when students can scan to check in for the morning (e.g., 7:00 AM – 9:00 AM).</li>
                    <li>The <strong>Morning Time Out</strong> defines when students can scan to check out for the morning (e.g., 11:00 AM – 12:00 PM).</li>
                    <li>The <strong>Afternoon Time In</strong> defines when students can scan to check in for the afternoon (e.g., 1:00 PM – 2:00 PM).</li>
                    <li>The <strong>Afternoon Time Out</strong> defines when students can scan to check out for the afternoon (e.g., 4:00 PM – 6:00 PM).</li>
                    <li>The QR scanner (both admin and public) will <strong>only accept scans</strong> during active event windows.</li>
                    <li>Outside of scheduled windows, the scanner will show a <strong>"Scanner Closed"</strong> message.</li>
                    <li>You can <strong>deactivate</strong> an event to temporarily disable it without deleting.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>