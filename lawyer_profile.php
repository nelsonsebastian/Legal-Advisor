<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLawyer();

$lawyer_id = getUserId();
$error = '';
$success = '';

// Get lawyer details
$lawyer = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE id = ?");
    $stmt->execute([$lawyer_id]);
    $lawyer = $stmt->fetch();
} catch(PDOException $e) {
    $error = "Unable to fetch lawyer details. Please try again later.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $experience_years = $_POST['experience_years'] ?? 0;
    
    // Process specializations
    $specializations = [];
    if (!empty($_POST['specializations'])) {
        $specializations = $_POST['specializations'];
        // Remove any empty values
        $specializations = array_filter($specializations);
    }
    
    // Validate input
    if (!is_numeric($experience_years) || $experience_years < 0) {
        $error = "Years of experience must be a positive number.";
    } else {
        try {
            // Update lawyer details
            $stmt = $pdo->prepare("UPDATE lawyers SET phone = ?, address = ?, experience_years = ?, specializations = ? WHERE id = ?");
            $stmt->execute([
                $phone,
                $address,
                $experience_years,
                json_encode($specializations),
                $lawyer_id
            ]);
            
            $success = "Profile updated successfully!";
            
            // Refresh lawyer data
            $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE id = ?");
            $stmt->execute([$lawyer_id]);
            $lawyer = $stmt->fetch();
            
        } catch(PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Decode specializations from JSON
$specializations_list = [];
if (!empty($lawyer['specializations'])) {
    $specializations_list = json_decode($lawyer['specializations'], true);
    if (!is_array($specializations_list)) {
        $specializations_list = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        .profile-sidebar {
            width: 300px;
            flex-shrink: 0;
        }
        .profile-content {
            flex-grow: 1;
        }
        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 4px solid #f0f0f0;
        }
        .profile-name {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .profile-title {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .specialization-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .specialization-tag {
            background: #e8f4fd;
            color: #3498db;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        .remove-specialization {
            margin-left: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .add-specialization {
            display: flex;
            gap: 10px;
        }
        .add-btn {
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
        }
        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
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
                    <li><a href="lawyer_cases.php">My Cases</a></li>
                    <li><a href="lawyer_profile.php" class="active">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>My Profile</h1>
            <p>Manage your professional information and preferences</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="profile-container">
            <!-- Sidebar with profile summary -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-header">
                        <img src="<?php echo !empty($lawyer['profile_image']) ? 'uploads/' . htmlspecialchars($lawyer['profile_image']) : 'assets/images/default-lawyer.jpg'; ?>" 
                             alt="Profile Image" class="profile-image">
                        <h2 class="profile-name"><?php echo htmlspecialchars($lawyer['full_name']); ?></h2>
                        <p class="profile-title">Legal Advisor</p>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo htmlspecialchars($lawyer['experience_years'] ?? 0); ?></div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo htmlspecialchars($lawyer['total_ratings'] ?? 0); ?></div>
                            <div class="stat-label">Reviews</div>
                        </div>
                    </div>
                </div>
                
                <div class="profile-card">
                    <h3 class="section-title">Contact Information</h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($lawyer['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo !empty($lawyer['phone']) ? htmlspecialchars($lawyer['phone']) : 'Not provided'; ?></p>
                    <p><strong>Bar Number:</strong> <?php echo !empty($lawyer['bar_number']) ? htmlspecialchars($lawyer['bar_number']) : 'Not provided'; ?></p>
                </div>
            </div>
            
            <!-- Main profile content -->
            <div class="profile-content">
                <div class="profile-card">
                    <h3 class="section-title">Edit Profile Information</h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($lawyer['full_name']); ?>" disabled>
                            <small>Name cannot be changed. Contact admin for name changes.</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($lawyer['email']); ?>" disabled>
                            <small>Email cannot be changed. Contact admin for email changes.</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($lawyer['phone'] ?? ''); ?>" placeholder="Enter your phone number">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($lawyer['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" name="experience_years" value="<?php echo htmlspecialchars($lawyer['experience_years'] ?? 0); ?>" min="0" max="50">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Specializations</label>
                            <div class="specialization-list" id="specializations-container">
                                <?php foreach ($specializations_list as $index => $spec): ?>
                                    <div class="specialization-tag">
                                        <input type="hidden" name="specializations[]" value="<?php echo htmlspecialchars($spec); ?>">
                                        <?php echo htmlspecialchars($spec); ?>
                                        <span class="remove-specialization" onclick="removeSpecialization(this)">×</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="add-specialization">
                                <input type="text" class="form-control" id="new-specialization" placeholder="Add a specialization">
                                <button type="button" class="add-btn" onclick="addSpecialization()">Add</button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Legal Advisor. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function addSpecialization() {
            const input = document.getElementById('new-specialization');
            const specialization = input.value.trim();
            
            if (specialization) {
                const container = document.getElementById('specializations-container');
                
                // Create new specialization tag
                const tag = document.createElement('div');
                tag.className = 'specialization-tag';
                tag.innerHTML = `
                    <input type="hidden" name="specializations[]" value="${specialization}">
                    ${specialization}
                    <span class="remove-specialization" onclick="removeSpecialization(this)">×</span>
                `;
                
                container.appendChild(tag);
                input.value = '';
            }
        }
        
        function removeSpecialization(element) {
            element.parentElement.remove();
        }
        
        // Allow adding specializations with Enter key
        document.getElementById('new-specialization').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSpecialization();
            }
        });
    </script>
</body>
</html>