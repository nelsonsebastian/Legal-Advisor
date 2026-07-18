<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireAdmin();

// Handle lawyer deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $lawyer_id = $_POST['delete_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM lawyers WHERE id = ?");
        $stmt->execute([$lawyer_id]);
        $_SESSION['success'] = "Lawyer deleted successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting lawyer: " . $e->getMessage();
    }
    
    header("Location: admin_lawyers.php");
    exit();
}

// Get approved lawyers
$approved_lawyers = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE status = 'approved' ORDER BY full_name");
    $stmt->execute();
    $approved_lawyers = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching lawyers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lawyers - Legal Advisor</title>
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
                    <li><a href="admin_lawyers.php" class="active">Lawyers</a></li>
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
            <h1>Manage Approved Lawyers</h1>
            <p>View and manage approved lawyers in the system</p>
        </div>

        <!-- Display messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Lawyers Table -->
        <section class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Approved Lawyers</h2>
                <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
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
                        <?php if (empty($approved_lawyers)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No approved lawyers found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($approved_lawyers as $lawyer): ?>
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
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="delete_id" value="<?php echo $lawyer['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this lawyer? This action cannot be undone.');">Delete</button>
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

    <script src="assets/js/main.js"></script>
</body>
</html>
[file content end]