<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLawyer();

$lawyer_id = getUserId();

// Get lawyer appointments
$appointments = [];
try {
    $stmt = $pdo->prepare("SELECT a.*, c.full_name AS client_name 
                          FROM appointments a 
                          JOIN clients c ON a.client_id = c.id 
                          WHERE a.lawyer_id = ?
                          ORDER BY a.appointment_date DESC");
    $stmt->execute([$lawyer_id]);
    $appointments = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Get lawyer details
$lawyer = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE id = ?");
    $stmt->execute([$lawyer_id]);
    $lawyer = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lawyer Dashboard - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
            <h1>Welcome, <?php echo htmlspecialchars($lawyer['full_name']); ?></h1>
            <p>Your Lawyer Dashboard</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($appointments); ?></div>
                <div class="stat-label">Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $lawyer['average_rating'] ?: '4.8'; ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $lawyer['total_ratings'] ?: '24'; ?></div>
                <div class="stat-label">Total Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $lawyer['experience_years'] ?: '5'; ?>+</div>
                <div class="stat-label">Years Experience</div>
            </div>
        </div>

        <!-- Appointment Requests -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Appointment Requests</h2>
                <a href="lawyer_appointments.php" class="btn">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($appointments, 0, 3) as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($appointment['legal_issue_type']); ?></td>
                            <td>
                                <?php 
                                $statusClass = [
                                    'pending' => 'badge-pending',
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    'completed' => 'badge-resolved'
                                ];
                                ?>
                                <span class="badge <?php echo $statusClass[strtolower($appointment['status'])]; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($appointment['status'] === 'pending'): ?>
                                    <button class="btn btn-success" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'approved')">Approve</button>
                                    <button class="btn btn-danger" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'rejected')">Reject</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Recent Ratings -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Recent Ratings</h2>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="rating text-center">
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star empty">★</span>
                        </div>
                        <p class="text-center mt-2">"Very professional and helpful. Solved my property dispute quickly."</p>
                        <p class="text-center text-muted">- John Doe</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="rating text-center">
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                        </div>
                        <p class="text-center mt-2">"Excellent advice for my business contract. Will definitely recommend!"</p>
                        <p class="text-center text-muted">- Sarah Johnson</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="rating text-center">
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star empty">★</span>
                        </div>
                        <p class="text-center mt-2">"Helped me navigate a difficult family law situation with compassion."</p>
                        <p class="text-center text-muted">- Michael Brown</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Legal Advisor. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        function updateAppointmentStatus(appointmentId, status) {
            if (!confirm(`Are you sure you want to ${status} this appointment?`)) return;
            
            const formData = new FormData();
            formData.append('appointment_id', appointmentId);
            formData.append('status', status);
            
            fetch('ajax/update_appointment_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Appointment ${status} successfully!`);
                    location.reload();
                } else {
                    alert('Error updating appointment: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating appointment:', error);
                alert('Error updating appointment. Please try again.');
            });
        }
    </script>
</body>
</html>