<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireAdmin();

// Get pending lawyers
$pending_lawyers = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE status = 'pending'");
    $stmt->execute();
    $pending_lawyers = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Get recent appointments
$appointments = [];
try {
    $stmt = $pdo->prepare("SELECT a.*, c.full_name AS client_name, l.full_name AS lawyer_name 
                          FROM appointments a 
                          JOIN clients c ON a.client_id = c.id 
                          LEFT JOIN lawyers l ON a.lawyer_id = l.id 
                          ORDER BY a.created_at DESC LIMIT 5");
    $stmt->execute();
    $appointments = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Get user counts
$user_counts = [
    'clients' => 0,
    'lawyers' => 0,
    'admins' => 0
];
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM clients");
    $stmt->execute();
    $user_counts['clients'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM lawyers");
    $stmt->execute();
    $user_counts['lawyers'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM admin");
    $stmt->execute();
    $user_counts['admins'] = $stmt->fetch()['count'];
} catch(PDOException $e) {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <a href="admin_dashboard.php" class="logo">⚖️ Legal Advisor</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="admin_lawyers.php">Lawyers</a></li>
                    <li><a href="admin_clients.php">Clients</a></li>
                    <li><a href="admin_cases.php">Cases</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>System Management & Monitoring</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_counts['clients']; ?></div>
                <div class="stat-label">Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_counts['lawyers']; ?></div>
                <div class="stat-label">Lawyers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($appointments); ?></div>
                <div class="stat-label">Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Active Cases</div>
            </div>
        </div>

        <!-- Pending Lawyer Approvals -->
        <?php if (!empty($pending_lawyers)): ?>
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Pending Lawyer Approvals</h2>
                <a href="admin_lawyers.php" class="btn">Manage Lawyers</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Specializations</th>
                            <th>Experience</th>
                            <th>Bar Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_lawyers as $lawyer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($lawyer['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($lawyer['email']); ?></td>
                            <td>
                                <?php 
                                $specializations = json_decode($lawyer['specializations'], true);
                                echo htmlspecialchars(implode(', ', $specializations ?: []));
                                ?>
                            </td>
                            <td><?php echo $lawyer['experience_years']; ?> years</td>
                            <td><?php echo htmlspecialchars($lawyer['bar_number']); ?></td>
                            <td>
                                <button class="btn btn-success" onclick="updateLawyerStatus(<?php echo $lawyer['id']; ?>, 'approved')">Approve</button>
                                <button class="btn btn-danger" onclick="updateLawyerStatus(<?php echo $lawyer['id']; ?>, 'rejected')">Reject</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <!-- Recent Appointments -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Recent Appointments</h2>
                <a href="admin_appointments.php" class="btn">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Lawyer</th>
                            <th>Date</th>
                            <th>Issue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                            <td><?php echo $appointment['lawyer_name'] ? htmlspecialchars($appointment['lawyer_name']) : 'Not Assigned'; ?></td>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- System Overview -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">System Overview</h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <h3 class="card-title">User Distribution</h3>
                        <div id="user-chart" style="height: 300px;">
                            <!-- Chart would be implemented with Chart.js -->
                            <canvas id="userDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <h3 class="card-title">Appointment Status</h3>
                        <div id="appointment-chart" style="height: 300px;">
                            <!-- Chart would be implemented with Chart.js -->
                            <canvas id="appointmentStatusChart"></canvas>
                        </div>
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
        function updateLawyerStatus(lawyerId, status) {
            if (!confirm(`Are you sure you want to ${status} this lawyer?`)) return;
            
            const formData = new FormData();
            formData.append('lawyer_id', lawyerId);
            formData.append('status', status);
            
            fetch('ajax/update_lawyer_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Lawyer ${status} successfully!`);
                    location.reload();
                } else {
                    alert('Error updating lawyer: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating lawyer:', error);
                alert('Error updating lawyer. Please try again.');
            });
        }
    </script>
</body>
</html>