<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLawyer();

$lawyer_id = getUserId();

// Get lawyer appointments
$appointments = [];
try {
    $stmt = $pdo->prepare("SELECT a.*, c.full_name AS client_name, c.phone AS client_phone, c.email AS client_email 
                          FROM appointments a 
                          JOIN clients c ON a.client_id = c.id 
                          WHERE a.lawyer_id = ?
                          ORDER BY a.created_at DESC");
    $stmt->execute([$lawyer_id]);
    $appointments = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Unable to fetch appointments. Please try again later.";
}

// Handle appointment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['appointment_id']) && isset($_POST['status'])) {
        $appointment_id = $_POST['appointment_id'];
        $status = $_POST['status'];
        $appointment_date = $_POST['appointment_date'] ?? null;
        $appointment_time = $_POST['appointment_time'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        try {
            if ($status == 'approved') {
                // For approved appointments, update with date and time
                $stmt = $pdo->prepare("UPDATE appointments 
                                      SET status = ?, appointment_date = ?, appointment_time = ?, notes = ?, approved_by = ?
                                      WHERE id = ? AND lawyer_id = ?");
                $stmt->execute([$status, $appointment_date, $appointment_time, $notes, $lawyer_id, $appointment_id, $lawyer_id]);
            } else {
                // For rejected appointments
                $stmt = $pdo->prepare("UPDATE appointments 
                                      SET status = ?, notes = ?, approved_by = ?
                                      WHERE id = ? AND lawyer_id = ?");
                $stmt->execute([$status, $notes, $lawyer_id, $appointment_id, $lawyer_id]);
            }
            
            $_SESSION['success'] = "Appointment status updated successfully!";
            header("Location: lawyer_appointments.php");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating appointment: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .appointment-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-cancelled {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .appointment-details {
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: 600;
            min-width: 150px;
        }
        .action-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .no-appointments {
            text-align: center;
            padding: 40px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <a href="lawyer_dashboard.php" class="logo">⚖️ Legal Advisor</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="lawyer_dashboard.php">Dashboard</a></li>
                    <li><a href="lawyer_appointments.php" class="active">Appointments</a></li>
                    <li><a href="lawyer_cases.php">My Cases</a></li>
                    <li><a href="lawyer_profile.php">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>My Appointments</h1>
            <p>Manage your appointment requests</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($appointments)): ?>
            <div class="no-appointments">
                <h3>No Appointments Yet</h3>
                <p>You don't have any appointment requests yet.</p>
            </div>
        <?php else: ?>
            <div class="appointments-list">
                <?php foreach ($appointments as $appointment): 
                    $status = strtolower($appointment['status']);
                    $statusText = ucfirst($status);
                ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <h3><?php echo htmlspecialchars($appointment['legal_issue_type']); ?></h3>
                            <span class="status-badge badge-<?php echo $status; ?>"><?php echo $statusText; ?></span>
                        </div>
                        
                        <div class="appointment-details">
                            <div class="detail-row">
                                <span class="detail-label">Client Name:</span>
                                <span><?php echo htmlspecialchars($appointment['client_name']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Client Email:</span>
                                <span><?php echo htmlspecialchars($appointment['client_email']); ?></span>
                            </div>
                            
                            <?php if (!empty($appointment['client_phone'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Client Phone:</span>
                                <span><?php echo htmlspecialchars($appointment['client_phone']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-row">
                                <span class="detail-label">Case Type:</span>
                                <span><?php echo htmlspecialchars($appointment['legal_issue_type']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Issue Description:</span>
                                <span><?php echo htmlspecialchars($appointment['issue_description']); ?></span>
                            </div>
                            
                            <?php if ($status == 'approved'): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Scheduled Date:</span>
                                    <span><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Scheduled Time:</span>
                                    <span><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($appointment['notes'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Notes:</span>
                                    <span><?php echo htmlspecialchars($appointment['notes']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($status == 'pending'): ?>
                            <div class="action-buttons">
                                <button class="btn btn-success" onclick="openApproveModal(<?php echo $appointment['id']; ?>)">Accept Appointment</button>
                                <button class="btn btn-danger" onclick="openRejectModal(<?php echo $appointment['id']; ?>)">Reject Appointment</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Approve Appointment Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('approveModal')">&times;</span>
            <h2>Accept Appointment</h2>
            <form method="POST" action="">
                <input type="hidden" name="appointment_id" id="approve_appointment_id">
                <input type="hidden" name="status" value="approved">
                
                <div class="form-group">
                    <label for="appointment_date">Appointment Date:</label>
                    <input type="date" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="appointment_time">Appointment Time:</label>
                    <input type="time" id="appointment_time" name="appointment_time" required>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes (Optional):</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">Confirm Acceptance</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('approveModal')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Reject Appointment Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            <h2>Reject Appointment</h2>
            <form method="POST" action="">
                <input type="hidden" name="appointment_id" id="reject_appointment_id">
                <input type="hidden" name="status" value="rejected">
                
                <div class="form-group">
                    <label for="reject_notes">Reason for Rejection (Optional):</label>
                    <textarea id="reject_notes" name="notes" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Legal Advisor. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function openApproveModal(appointmentId) {
            document.getElementById('approve_appointment_id').value = appointmentId;
            document.getElementById('approveModal').style.display = 'block';
        }
        
        function openRejectModal(appointmentId) {
            document.getElementById('reject_appointment_id').value = appointmentId;
            document.getElementById('rejectModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        // Set minimum time to current time for today's dates
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('appointment_date').addEventListener('change', function() {
            const timeInput = document.getElementById('appointment_time');
            if (this.value === today) {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                timeInput.min = `${hours}:${minutes}`;
            } else {
                timeInput.removeAttribute('min');
            }
        });
    </script>
</body>
</html>