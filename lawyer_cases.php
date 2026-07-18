<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLawyer();

$lawyer_id = getUserId();

// First, ensure all approved appointments have corresponding cases
try {
    // Find approved appointments without cases
    $stmt = $pdo->prepare("SELECT a.* FROM appointments a 
                          LEFT JOIN cases c ON a.id = c.appointment_id 
                          WHERE a.lawyer_id = ? AND a.status = 'approved' AND c.id IS NULL");
    $stmt->execute([$lawyer_id]);
    $appointmentsWithoutCases = $stmt->fetchAll();
    
    // Create cases for these appointments
    foreach ($appointmentsWithoutCases as $appointment) {
        $case_reference = 'CA' . strtoupper(uniqid());
        $case_title = $appointment['legal_issue_type'] . ' Case - ' . $appointment['id'];
        
        $stmt = $pdo->prepare("INSERT INTO cases (case_reference, appointment_id, client_id, lawyer_id, case_title, case_description) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $case_reference,
            $appointment['id'],
            $appointment['client_id'],
            $appointment['lawyer_id'],
            $case_title,
            $appointment['issue_description']
        ]);
    }
} catch(PDOException $e) {
    // Log error but don't break the page
    error_log("Error creating cases: " . $e->getMessage());
}

// Now get lawyer's cases
$cases = [];
try {
    $stmt = $pdo->prepare("SELECT c.*, cl.full_name AS client_name, cl.phone AS client_phone, 
                          cl.email AS client_email, a.appointment_date, a.appointment_time,
                          a.legal_issue_type, a.issue_description
                          FROM cases c 
                          JOIN appointments a ON c.appointment_id = a.id
                          JOIN clients cl ON a.client_id = cl.id
                          WHERE c.lawyer_id = ?
                          ORDER BY a.appointment_date, a.appointment_time");
    $stmt->execute([$lawyer_id]);
    $cases = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Unable to fetch cases. Please try again later.";
}

// Get current date and time for comparison
$current_datetime = new DateTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cases - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .case-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            background: white;
        }
        .case-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .case-reference {
            font-weight: bold;
            color: #2c3e50;
            font-size: 0.9em;
        }
        .case-details {
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: 600;
            min-width: 150px;
            color: #555;
        }
        .call-reminder {
            background-color: #e8f4fd;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            border-left: 4px solid #2196F3;
            display: flex;
            align-items: center;
        }
        .reminder-icon {
            font-size: 24px;
            margin-right: 10px;
            color: #2196F3;
        }
        .upcoming-call {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
        }
        .upcoming-call .reminder-icon {
            color: #ffc107;
        }
        .past-call {
            background-color: #f5f5f5;
            border-left: 4px solid #9e9e9e;
        }
        .past-call .reminder-icon {
            color: #9e9e9e;
        }
        .no-cases {
            text-align: center;
            padding: 40px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-label {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
        }
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .dashboard-header {
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .form-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
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
                    <li><a href="lawyer_appointments.php">Appointments</a></li>
                    <li><a href="lawyer_cases.php" class="active">My Cases</a></li>
                    <li><a href="lawyer_profile.php">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>My Cases</h1>
            <p>Manage your accepted cases and appointment schedule</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($cases)): ?>
            <div class="no-cases">
                <h3>No Cases Yet</h3>
                <p>You don't have any accepted cases yet. Approved appointments will appear here.</p>
                <a href="lawyer_appointments.php" class="btn btn-primary">View Appointments</a>
            </div>
        <?php else: ?>
            <!-- Filter Section -->
            <div class="filter-section">
                <h3>Filter Cases</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <select id="date-filter" class="form-control">
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="past">Past Appointments</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="cases-list">
                <?php foreach ($cases as $case): 
                    $appointment_datetime = new DateTime($case['appointment_date'] . ' ' . $case['appointment_time']);
                    $time_diff = $current_datetime->diff($appointment_datetime);
                    $is_upcoming = $appointment_datetime > $current_datetime;
                    $is_past = $appointment_datetime < $current_datetime;
                    
                    $reminder_class = '';
                    if ($is_upcoming) {
                        $reminder_class = 'upcoming-call';
                    } elseif ($is_past) {
                        $reminder_class = 'past-call';
                    }
                ?>
                    <div class="case-card" data-date="<?php echo htmlspecialchars($case['appointment_date']); ?>">
                        <div class="case-header">
                            <h3>
                                <?php echo htmlspecialchars($case['case_title']); ?>
                                <span class="case-reference">(Ref: <?php echo htmlspecialchars($case['case_reference']); ?>)</span>
                            </h3>
                        </div>
                        
                        <div class="case-details">
                            <div class="detail-row">
                                <span class="detail-label">Client Name:</span>
                                <span><?php echo htmlspecialchars($case['client_name']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Client Email:</span>
                                <span><?php echo htmlspecialchars($case['client_email']); ?></span>
                            </div>
                            
                            <?php if (!empty($case['client_phone'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Client Phone:</span>
                                <span><?php echo htmlspecialchars($case['client_phone']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-row">
                                <span class="detail-label">Case Type:</span>
                                <span><?php echo htmlspecialchars($case['legal_issue_type']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Appointment Date:</span>
                                <span><?php echo date('M d, Y', strtotime($case['appointment_date'])); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Appointment Time:</span>
                                <span><?php echo date('h:i A', strtotime($case['appointment_time'])); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Case Description:</span>
                                <span><?php echo htmlspecialchars($case['case_description']); ?></span>
                            </div>
                        </div>
                        
                        <div class="call-reminder <?php echo $reminder_class; ?>">
                            <div class="reminder-icon">📞</div>
                            <div>
                                <?php if ($is_upcoming): ?>
                                    <strong>Call Reminder:</strong> You have a scheduled call with this client on 
                                    <?php echo date('M d, Y', strtotime($case['appointment_date'])); ?> at 
                                    <?php echo date('h:i A', strtotime($case['appointment_time'])); ?>.
                                    <?php if ($time_diff->days > 0): ?>
                                        (in <?php echo $time_diff->days; ?> day<?php echo $time_diff->days > 1 ? 's' : ''; ?>)
                                    <?php else: ?>
                                        (today)
                                    <?php endif; ?>
                                <?php elseif ($is_past): ?>
                                    <strong>Past Call:</strong> You had a scheduled call with this client on 
                                    <?php echo date('M d, Y', strtotime($case['appointment_date'])); ?> at 
                                    <?php echo date('h:i A', strtotime($case['appointment_time'])); ?>.
                                <?php else: ?>
                                    <strong>Call Now:</strong> You have a scheduled call with this client right now.
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-secondary" onclick="contactClient('<?php echo htmlspecialchars($case['client_email']); ?>', '<?php echo htmlspecialchars($case['client_phone']); ?>')">Contact Client</button>
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

    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dateFilter = document.getElementById('date-filter');
            const caseCards = document.querySelectorAll('.case-card');
            
            if (dateFilter) {
                dateFilter.addEventListener('change', applyFilters);
            }
            
            function applyFilters() {
                const dateValue = dateFilter.value;
                const today = new Date().toISOString().split('T')[0];
                
                caseCards.forEach(card => {
                    const cardDate = card.getAttribute('data-date');
                    
                    let dateMatch = true;
                    
                    if (dateValue !== 'all') {
                        if (dateValue === 'today') {
                            dateMatch = cardDate === today;
                        } else if (dateValue === 'upcoming') {
                            dateMatch = cardDate >= today;
                        } else if (dateValue === 'past') {
                            dateMatch = cardDate < today;
                        }
                    }
                    
                    if (dateMatch) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });
        
        function contactClient(email, phone) {
            let message = 'Contact options:\n';
            if (email) message += 'Email: ' + email + '\n';
            if (phone) message += 'Phone: ' + phone + '\n';
            
            alert(message);
        }
    </script>
</body>
</html>