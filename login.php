<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    if (empty($username) || empty($password) || empty($user_type)) {
        $error = 'All fields are required.';
    } elseif (!in_array($user_type, ['client', 'lawyer', 'admin'])) {
        $error = 'Invalid user type selected.';
    } else {
        try {
            // Determine table based on user type
            $table = $user_type === 'admin' ? 'admin' : ($user_type === 'lawyer' ? 'lawyers' : 'clients');
            
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
          // ... existing code ...

if ($user && password_verify($password, $user['password'])) {
    // Check if lawyer is approved
    if ($user_type == 'lawyer' && $user['status'] != 'approved') {
        $error = 'Your lawyer account is pending approval or has been rejected.';
    } else {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user_type;
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        
        // Redirect based on user type
        switch ($user_type) {
            case 'admin':
                header('Location: admin_dashboard.php');
                break;
            case 'lawyer':
                header('Location: lawyer_dashboard.php');
                break;
            case 'client':
            default:
                header('Location: client_dashboard.php');
        }
        exit();
    }
} else {
    $error = 'Invalid username/email or password.';
}

        } catch(PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Legal Advisor</title>
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
                   
                    <li><a href="register.php">Register</a></li>
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
                        <h2 class="card-title text-center">Login to Your Account</h2>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" data-validate>
                        <div class="form-group">
                            <label for="user_type" class="form-label">Login As</label>
                            <select name="user_type" id="user_type" class="form-select" required>
                                <option value="">Select User Type</option>
                                <option value="client" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'client') ? 'selected' : ''; ?>>Client</option>
                                <option value="lawyer" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'lawyer') ? 'selected' : ''; ?>>Lawyer</option>
                                <option value="admin" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <div class="validation-error" id="user_type-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username" class="form-label">Username or Email</label>
                            <input type="text" name="username" id="username" class="form-control" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            <div class="validation-error" id="username-error"></div>
                        </div>
                        
                        <div class="form-group password-field">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <button type="button" class="password-toggle">👁️</button>
                            <div class="validation-error" id="password-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                        </div>
                    </form>
                    
                    <div class="text-center" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e9ecef;">
                        <p>Don't have an account? <a href="register.php" style="color: #1e3c72; font-weight: 600;">Register here</a></p>
                        <p style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
                        </p>
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
    <script>
         document.addEventListener('DOMContentLoaded', function() {
    // Initialize password toggle functionality
    initPasswordToggle();
    
    // Initialize real-time validation
    initRealTimeValidation();
});

function initPasswordToggle() {
    const passwordFields = document.querySelectorAll('.password-field');
    
    passwordFields.forEach(field => {
        const input = field.querySelector('input');
        const toggle = field.querySelector('.password-toggle');
        
        if (toggle) {
            toggle.addEventListener('click', function() {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                toggle.innerHTML = isPassword ? '👁️‍🗨️' : '👁️';
                
                // Reset to password type on form submit
                const form = input.closest('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        input.type = 'password';
                        toggle.innerHTML = '👁️';
                    });
                }
            });
        }
    });
}

function initRealTimeValidation() {
    // Username validation (only letters, numbers, underscore)
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            validateUsername(this);
        });
    }

    // Password validation (8+ chars, upper, lower, number, special)
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword(this);
            
            // Also validate confirm password when password changes
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword && confirmPassword.value) {
                validateConfirmPassword(confirmPassword);
            }
        });
    }
}
        </script>
    <!-- <script src="assets/js/validation.js"></script> -->
</body>
</html>