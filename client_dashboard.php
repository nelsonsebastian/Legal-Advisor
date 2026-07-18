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
                          ORDER BY a.appointment_date DESC");
    $stmt->execute([$client_id]);
    $appointments = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Get featured lawyers
try {
    $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE status = 'approved' ORDER BY average_rating DESC LIMIT 4");
    $stmt->execute();
    $featured_lawyers = $stmt->fetchAll();
} catch(PDOException $e) {
    $featured_lawyers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li><a href="client_appointments.php">My Appointments</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
            <p>Your Client Dashboard</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($appointments); ?></div>
                <div class="stat-label">Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">2</div>
                <div class="stat-label">Active Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">4.5</div>
                <div class="stat-label">Avg. Lawyer Rating</div>
            </div>
        </div>

        <!-- Featured Lawyers -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Featured Lawyers</h2>
            </div>
            <div class="row">
                <?php foreach ($featured_lawyers as $lawyer): ?>
                <div class="col-md-3">
                    <div class="lawyer-card">
                        <div class="lawyer-avatar">
                            <?php echo strtoupper(substr($lawyer['full_name'], 0, 2)); ?>
                        </div>
                        <div class="lawyer-name"><?php echo htmlspecialchars($lawyer['full_name']); ?></div>
                        <div class="lawyer-specialization">
                            <?php 
                            $specializations = json_decode($lawyer['specializations'], true);
                            echo htmlspecialchars(implode(', ', $specializations ?: ['General Practice']));
                            ?>
                        </div>
                        <div class="rating">
                            <?php
                            $rating = round($lawyer['average_rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '<span class="star">★</span>' : '<span class="star empty">★</span>';
                            }
                            ?>
                        </div>
                        <a href="book-appointment.php?lawyer_id=<?php echo $lawyer['id']; ?>" class="btn btn-primary mt-2">Book Consultation</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Recent Appointments -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Recent Appointments</h2>
                <a href="client_appointments.php" class="btn">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Lawyer</th>
                            <th>Issue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($appointments, 0, 3) as $appointment): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                            <td><?php echo $appointment['lawyer_name'] ? htmlspecialchars($appointment['lawyer_name']) : 'Not Assigned'; ?></td>
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