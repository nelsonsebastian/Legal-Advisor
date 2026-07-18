<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireClient();

// Get client appointments
$client_id = getUserId();
$appointments = [];
try {
    $stmt = $pdo->prepare("SELECT a.*, l.full_name AS lawyer_name 
                          FROM appointments a 
                          LEFT JOIN lawyers l ON a.lawyer_id = l.id 
                          WHERE a.client_id = ?
                          ORDER BY a.created_at DESC");
    $stmt->execute([$client_id]);
    $appointments = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Unable to fetch appointments. Please try again later.";
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
        .patience-message {
            background-color: #e8f4fd;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            font-style: italic;
            border-left: 4px solid #2196F3;
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
            <a href="client_dashboard.php" class="logo">⚖️ Legal Advisor</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="client_dashboard.php">Dashboard</a></li>
                    <li><a href="client_book_appointment.php">Book Appointment</a></li>
                    <li><a href="client_appointments.php" class="active">My Appointments</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>My Appointments</h1>
            <p>View and manage your legal appointments</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($appointments)): ?>
            <div class="no-appointments">
                <h3>No Appointments Yet</h3>
                <p>You haven't booked any appointments yet. Schedule your first consultation with a lawyer.</p>
                <a href="client_book_appointment.php" class="btn btn-primary">Book an Appointment</a>
            </div>
        <?php else: ?>
            <div class="appointments-list">
                <?php foreach ($appointments as $appointment): 
                    $status = strtolower($appointment['status']);
                    $statusText = ucfirst($status);
                    
                    // Adjust status text based on requirements
                    if ($status == 'approved') {
                        $statusText = 'Accepted';
                    } elseif ($status == 'pending') {
                        $statusText = 'Pending';
                    }
                ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <h3><?php echo htmlspecialchars($appointment['legal_issue_type']); ?></h3>
                            <span class="status-badge badge-<?php echo $status; ?>"><?php echo $statusText; ?></span>
                        </div>
                        
                        <div class="appointment-details">
                            <div class="detail-row">
                                <span class="detail-label">Case Type:</span>
                                <span><?php echo htmlspecialchars($appointment['legal_issue_type']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Lawyer:</span>
                                <span>
                                    <?php 
                                    if (!empty($appointment['lawyer_name'])) {
                                        echo htmlspecialchars($appointment['lawyer_name']);
                                    } else {
                                        echo 'Not Assigned';
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="status-badge badge-<?php echo $status; ?>"><?php echo $statusText; ?></span>
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
                                <div class="patience-message">
                                    Appreciate Your Patience. Your lawyer will call you at the scheduled time.
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($appointment['notes'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Notes:</span>
                                    <span><?php echo htmlspecialchars($appointment['notes']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Legal Advisor. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>