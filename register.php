<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Get legal issue types for lawyer specializations
try {
    $stmt = $pdo->prepare("SELECT * FROM legal_issue_types WHERE is_active = 1 ORDER BY type_name");
    $stmt->execute();
    $legal_issues = $stmt->fetchAll();
} catch(PDOException $e) {
    $legal_issues = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'];
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    
    // Server-side validation
    if (empty($user_type) || empty($username) || empty($email) || empty($password) || 
        empty($confirm_password) || empty($full_name)) {
        $error = 'All required fields must be filled.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers and underscore.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $full_name)) {
        $error = 'Full name can only contain letters and spaces.';
    } elseif (!empty($phone) && !preg_match('/^\d{10}$/', $phone)) {
        $error = 'Phone number must be 10 digits.';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || 
             !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || 
             !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $error = 'Password must be at least 8 characters with uppercase, lowercase, number and special character.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
   } elseif ($user_type == 'lawyer' && (trim($_POST['bar_number']) === '' || !preg_match('/^[A-Za-z]{3}\/\d{4}\/\d{1,4}$/', trim($_POST['bar_number'])))) {
        $error = 'Bar number must be in format: ABC/YYYY/1234 (3 letters, /, 4 digit year, /, 1-4 digit number)';
    } else {
        try {
            // Check if username or email already exists
            $tables = ['clients', 'lawyers', 'admin'];
            $exists = false;
            
            foreach ($tables as $table) {
                $stmt = $pdo->prepare("SELECT id FROM $table WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $exists = true;
                    break;
                }
            }
            
            if ($exists) {
                $error = 'Username or email already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                if ($user_type == 'client') {
                    $stmt = $pdo->prepare("INSERT INTO clients (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address]);
                    $success = 'Client account created successfully! You can now login.';
                } elseif ($user_type == 'lawyer') {
                    $specializations = isset($_POST['specializations']) ? $_POST['specializations'] : [];
                    $experience_years = intval($_POST['experience_years']);
                    $bar_number = sanitizeInput($_POST['bar_number']);
                    $bio = sanitizeInput($_POST['bio']);
                    
                    if (empty($specializations)) {
                        $error = 'Specializations are required for lawyers.';
                    } else {
                        $specializations_json = json_encode($specializations);
                        
                        $stmt = $pdo->prepare("INSERT INTO lawyers (username, email, password, full_name, phone, address, specializations, experience_years, bar_number, bio, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                        $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address, $specializations_json, $experience_years, $bar_number, $bio]);
                        $success = 'Lawyer account created successfully! Your account is pending admin approval.';
                    }
                } else {
                    $error = 'Invalid user type.';
                }
            }
        } catch(PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/validation.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <a href="index.php" class="logo">⚖️ Legal Advisor</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                 
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="row justify-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title text-center">Create Your Account</h2>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" data-validate>
                        <div class="form-group">
                            <label for="user_type" class="form-label">Register As *</label>
                            <select name="user_type" id="user_type" class="form-select" required onchange="toggleLawyerFields()">
                                <option value="">Select User Type</option>
                                <option value="client" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'client') ? 'selected' : ''; ?>>Client</option>
                                <option value="lawyer" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'lawyer') ? 'selected' : ''; ?>>Lawyer</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" name="username" id="username" class="form-control" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           pattern="[a-zA-Z0-9_]+" required>
                                    <div class="validation-error" id="username-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" name="email" id="email" class="form-control" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                    <div class="validation-error" id="email-error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group password-field">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" name="password" id="password" class="form-control" 
                                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()]).{8,}" required>
                                    <button type="button" class="password-toggle">👁️</button>
                                    <div class="validation-error" id="password-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group password-field">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                    <button type="button" class="password-toggle">👁️</button>
                                    <div class="validation-error" id="confirm_password-error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" name="full_name" id="full_name" class="form-control" 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                                   pattern="[a-zA-Z\s]+" required>
                            <div class="validation-error" id="full_name-error"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                           pattern="\d{10}" maxlength="10">
                                    <div class="validation-error" id="phone-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" name="address" id="address" class="form-control" 
                                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lawyer-specific fields -->
                        <div id="lawyer-fields" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Specializations *</label>
                                <div class="row">
                                    <?php foreach ($legal_issues as $issue): ?>
                                    <div class="col-md-6">
                                        <label style="font-weight: normal; display: flex; align-items: center; margin-bottom: 0.5rem;">
                                            <input type="checkbox" name="specializations[]" value="<?php echo htmlspecialchars($issue['type_name']); ?>" 
                                                   style="margin-right: 0.5rem;"
                                                   <?php echo (isset($_POST['specializations']) && in_array($issue['type_name'], $_POST['specializations'])) ? 'checked' : ''; ?>>
                                            <?php echo htmlspecialchars($issue['type_name']); ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="experience_years" class="form-label">Years of Experience</label>
                                        <input type="number" name="experience_years" id="experience_years" class="form-control" min="0" max="50"
                                               value="<?php echo isset($_POST['experience_years']) ? intval($_POST['experience_years']) : '0'; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bar_number" class="form-label">Bar Number *</label>
                                        <input type="text" name="bar_number" id="bar_number" class="form-control" 
                                               value="<?php echo isset($_POST['bar_number']) ? htmlspecialchars($_POST['bar_number']) : ''; ?>"
                                               pattern="^[A-Z]{3}/\d{4}/\d{1,4}$"
                                               title="Format: ABC/YYYY/1234 (e.g. MAH/2023/1234)"
                                               >
                                        <small class="form-text text-muted">Format: 3-letter state code / 4-digit year / 1-4 digit number (e.g. MAH/2023/1234)</small>
                                        <div class="validation-error" id="bar_number-error"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio" class="form-label">Professional Bio</label>
                                <textarea name="bio" id="bio" class="form-control" rows="4" 
                                          placeholder="Tell clients about your experience, achievements, and approach to legal practice..."><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                        </div>
                    </form>
                    
                    <div class="text-center" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e9ecef;">
                        <p>Already have an account? <a href="login.php" style="color: #1e3c72; font-weight: 600;">Login here</a></p>
                    </div>
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

    <script src="assets/js/main.js"></script>
    <script src="assets/js/validation.js"></script>
    <script>
        function toggleLawyerFields() {
            const userType = document.getElementById('user_type').value;
            const lawyerFields = document.getElementById('lawyer-fields');
            const barNumber = document.getElementById('bar_number');
            
            if (userType === 'lawyer') {
                lawyerFields.style.display = 'block';
                barNumber.setAttribute('required', 'required');
                lawyerFields.style.display = 'block';
            } else {
                lawyerFields.style.display = 'none';
                barNumber.removeAttribute('required');
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleLawyerFields();
        });
    </script>
</body>
</html>