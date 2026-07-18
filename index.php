<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Get approved lawyers for display
try {
    $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE status = 'approved' ORDER BY average_rating DESC LIMIT 6");
    $stmt->execute();
    $featured_lawyers = $stmt->fetchAll();
} catch(PDOException $e) {
    $featured_lawyers = [];
}

// Get legal issue types
try {
    $stmt = $pdo->prepare("SELECT * FROM legal_issue_types WHERE is_active = 1 ORDER BY type_name");
    $stmt->execute();
    $legal_issues = $stmt->fetchAll();
} catch(PDOException $e) {
    $legal_issues = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Advisor - Professional Legal Services</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <a href="index.php" class="logo">⚖️ Legal Advisor</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                
                    <?php if (isLoggedIn()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Professional Legal Services</h1>
            <p>Connect with experienced lawyers and get the legal help you need. Book consultations, track your cases, and access professional legal advice.</p>
            <div style="margin-top: 2rem;">
                <a href="client_book_appointment.php" class="btn">Book Consultation</a>
            
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container">
        <!-- Services Section -->
        <section class="services-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Our Legal Services</h2>
                </div>
                <div class="row">
                    <?php foreach ($legal_issues as $issue): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <h3 style="color: #1e3c72; margin-bottom: 1rem;"><?php echo htmlspecialchars($issue['type_name']); ?></h3>
                            <p style="color: #666; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($issue['description']); ?></p>
                            <a href="client_book_appointment.php?specialization=<?php echo urlencode($issue['type_name']); ?>" class="btn btn-primary">Find Lawyers</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

       
        <!-- How It Works -->
        <section class="how-it-works">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">How It Works</h2>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div style="font-size: 3rem; color: #1e3c72; margin-bottom: 1rem;">1</div>
                            <h3>Register</h3>
                            <p>Create your account as a client or lawyer</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div style="font-size: 3rem; color: #1e3c72; margin-bottom: 1rem;">2</div>
                            <h3>Find Lawyer</h3>
                            <p>Browse our directory of qualified lawyers</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div style="font-size: 3rem; color: #1e3c72; margin-bottom: 1rem;">3</div>
                            <h3>Book Appointment</h3>
                            <p>Schedule a consultation at your convenience</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div style="font-size: 3rem; color: #1e3c72; margin-bottom: 1rem;">4</div>
                            <h3>Get Help</h3>
                            <p>Receive professional legal assistance</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta-section">
            <div class="card" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; text-align: center;">
                <h2 style="color: white; margin-bottom: 1rem;">Need Legal Help Today?</h2>
                <p style="font-size: 1.1rem; margin-bottom: 2rem;">Don't wait - connect with qualified lawyers and get the legal assistance you deserve.</p>
                <a href="client_book_appointment.php" class="btn" style="margin-right: 1rem;">Book Consultation Now</a>
                <a href="register.php" class="btn btn-primary">Join Our Platform</a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Legal Advisor. All rights reserved. | Professional Legal Services Platform</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>