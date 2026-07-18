<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireAdmin();

// Handle client deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $client_id = $_POST['delete_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $_SESSION['success'] = "Client deleted successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting client: " . $e->getMessage();
    }
    
    header("Location: admin_clients.php");
    exit();
}

// Get all clients
$clients = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM clients ORDER BY created_at DESC");
    $stmt->execute();
    $clients = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching clients: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Add the CSS styles from the HTML example above */
    </style>
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
                    <li><a href="admin_clients.php" class="active">Clients</a></li>
                    <li><a href="admin_cases.php">Cases</a></li>
                    <li><a href="admin_appointments.php">Appointments</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>Manage Clients</h1>
            <p>View and manage all registered clients</p>
        </div>

        <!-- Display messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>


        <!-- Clients Table -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Registered Clients</h2>
                <span id="client-count"><?php echo count($clients); ?> clients found</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="clients-table-body">
                        <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <i>👥</i>
                                    <h3>No Clients Found</h3>
                                    <p>There are no registered clients in the system yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td>CL-<?php echo $client['id']; ?></td>
                                <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($client['address'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $client['status'] === 'active' ? 'badge-approved' : 'badge-rejected'; ?>">
                                        <?php echo ucfirst($client['status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?php echo $client['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this client? This action cannot be undone.');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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

    <script>
        // JavaScript from the HTML example above
    </script>
</body>
</html>