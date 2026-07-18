<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireClient();

$client_id = getUserId();

// Get legal issue types
$legal_issues = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM legal_issue_types WHERE is_active = 1 ORDER BY type_name");
    $stmt->execute();
    $legal_issues = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error loading legal issue types.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legal_issue_type = $_POST['legal_issue_type'] ?? '';
    $lawyer_id = $_POST['lawyer_id'] ?? '';
    $issue_description = $_POST['issue_description'] ?? '';
    
    // Validate inputs
    if (empty($legal_issue_type) || empty($lawyer_id) || empty($issue_description)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            // Insert appointment - date and time will be set by lawyer later
            $stmt = $pdo->prepare("INSERT INTO appointments (client_id, lawyer_id, legal_issue_type, issue_description, status) 
                                  VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$client_id, $lawyer_id, $legal_issue_type, $issue_description]);
            
            // Set success message in session
            $_SESSION['appointment_success'] = true;
            
            // Redirect to avoid form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error booking appointment: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Legal Advisor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-menu a.active {
            color: #ffd700;
            border-bottom: 2px solid #ffd700;
            padding-bottom: 5px;
        }
        
        .nav-menu a:hover {
            color: #ffd700;
        }
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .dashboard-header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .card-title {
            color: #1e3c72;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #ffd700;
            color: #1e3c72;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            background: #ffed4e;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .btn-primary {
            background: #1e3c72;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2a5298;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        /* Footer */
        .footer {
            background: #1e3c72;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }
        
        /* Alert messages */
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Steps navigation */
        .step {
            display: none;
        }
        
        .step.active {
            display: block;
        }
        
        .lawyer-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .lawyer-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .lawyer-card.selected {
            border: 2px solid #1e3c72;
            transform: scale(1.02);
        }
        
        /* Text alignment */
        .text-right {
            text-align: right;
        }
        
        /* Popup modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }
        
        .modal-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #28a745;
        }
        
        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #1e3c72;
        }
        
        .modal-message {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .modal-button {
            padding: 10px 25px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .modal-button:hover {
            background: #2a5298;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-menu {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <a href="client_dashboard.php" class="logo">⚖️ Legal Advisor</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="client_dashboard.php">Dashboard</a></li>
                    <li><a href="book-appointment.php" class="active">Book Appointment</a></li>
                    <li><a href="client_appointments.php">My Appointments</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="dashboard-header">
            <h1>Book an Appointment</h1>
            <p>Schedule a consultation with a qualified lawyer</p>
        </div>

        <!-- Display messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Display success message if appointment was booked -->
        <?php if (isset($_SESSION['appointment_success'])): ?>
            <div class="alert alert-success">Appointment booked successfully! Wait for the Lawyer to Accept.</div>
            <?php unset($_SESSION['appointment_success']); ?>
        <?php endif; ?>

        <form id="appointment-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Step 1: Select Legal Issue -->
            <div class="card step active" id="step1">
                <div class="card-header">
                    <h2 class="card-title">Step 1: Select Your Legal Issue</h2>
                </div>
                <div class="form-group">
                    <label for="legal_issue_type" class="form-label">What legal issue do you need help with?</label>
                    <select name="legal_issue_type" id="legal_issue_type" class="form-select" required>
                        <option value="">Select a legal issue type</option>
                        <?php foreach ($legal_issues as $issue): ?>
                            <option value="<?php echo htmlspecialchars($issue['type_name']); ?>">
                                <?php echo htmlspecialchars($issue['type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-primary" onclick="showStep(2)">Next</button>
                </div>
            </div>

            <!-- Step 2: Select Lawyer -->
            <div class="card step" id="step2">
                <div class="card-header">
                    <h2 class="card-title">Step 2: Select a Lawyer</h2>
                    <p>Based on your selection: <span id="selected-issue"></span></p>
                </div>
                <div id="lawyers-container">
                    <p>Please select a legal issue first to see available lawyers.</p>
                </div>
                <div class="text-right">
                    <button type="button" class="btn" onclick="showStep(1)">Back</button>
                    <button type="button" class="btn btn-primary" onclick="showStep(3)">Next</button>
                </div>
            </div>

            <!-- Step 3: Describe Issue & Confirm -->
            <div class="card step" id="step3">
                <div class="card-header">
                    <h2 class="card-title">Step 3: Describe Your Issue</h2>
                    <p>Selected lawyer: <span id="selected-lawyer"></span></p>
                </div>
                
                <div class="form-group">
                    <label for="issue_description" class="form-label">Please describe your legal issue</label>
                    <textarea name="issue_description" id="issue_description" class="form-control" rows="5" required placeholder="Provide details about your legal situation..."></textarea>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn" onclick="showStep(2)">Back</button>
                    <button type="submit" class="btn btn-primary" id="book-appointment-btn">Book Appointment</button>
                </div>
            </div>
        </form>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Legal Advisor. All rights reserved.</p>
        </div>
    </footer>

    <!-- Success Popup Modal -->
    <div class="modal-overlay" id="success-modal">
        <div class="modal-content">
            <div class="modal-icon">✅</div>
            <h2 class="modal-title">Appointment Booked Successfully!</h2>
            <p class="modal-message">Your appointment request has been sent. The lawyer will contact you to schedule the date and time.</p>
            <button class="modal-button" onclick="closeModal()">OK</button>
        </div>
    </div>

    <script>
        let selectedLawyerId = null;
        let selectedLawyerName = null;
        
        // Show/hide steps
        function showStep(stepNumber) {
            // Validate current step before proceeding
            if (stepNumber === 2) {
                const issueType = document.getElementById('legal_issue_type').value;
                if (!issueType) {
                    showError('Please select a legal issue type.');
                    return;
                }
                document.getElementById('selected-issue').textContent = issueType;
                loadLawyers(issueType);
            } else if (stepNumber === 3) {
                if (!selectedLawyerId) {
                    showError('Please select a lawyer.');
                    return;
                }
                document.getElementById('selected-lawyer').textContent = selectedLawyerName;
            }
            
            // Hide all steps
            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active');
            });
            
            // Show the selected step
            document.getElementById(`step${stepNumber}`).classList.add('active');
            
            // Scroll to top of the step
            document.getElementById(`step${stepNumber}`).scrollIntoView({ behavior: 'smooth' });
        }
        
        // Show error message
        function showError(message) {
            // Create a temporary error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'alert alert-danger';
            errorElement.textContent = message;
            
            // Insert at the top of the main content
            const main = document.querySelector('main');
            main.insertBefore(errorElement, main.firstChild);
            
            // Remove error after 5 seconds
            setTimeout(() => {
                errorElement.remove();
            }, 5000);
        }
        
        // Load lawyers based on selected legal issue
        function loadLawyers(issueType) {
            const container = document.getElementById('lawyers-container');
            container.innerHTML = '<p>Loading lawyers...</p>';
            
            // Create AJAX request to fetch lawyers from database
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `ajax/get_lawyers_by_specialization.php?specialization=${encodeURIComponent(issueType)}`, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success && response.lawyers.length > 0) {
                            let html = '<div class="row">';
                            response.lawyers.forEach(lawyer => {
                                html += `
                                    <div class="col-md-6">
                                        <div class="lawyer-card" data-id="${lawyer.id}" data-name="${lawyer.full_name}">
                                            <div class="card-body">
                                                <div class="lawyer-avatar" style="width: 50px; height: 50px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 1.2rem;">
                                                    ${lawyer.full_name.split(' ').map(n => n[0]).join('').toUpperCase()}
                                                </div>
                                                <h3>${lawyer.full_name}</h3>
                                                <p>Specializations: ${Array.isArray(lawyer.specializations) ? lawyer.specializations.join(', ') : lawyer.specializations}</p>
                                                <p>Experience: ${lawyer.experience_years} years</p>
                                                <div class="rating">
                                                    ${getRatingStars(lawyer.average_rating)}
                                                    <span>(${lawyer.total_ratings} reviews)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            container.innerHTML = html;
                            
                            // Add click event to lawyer cards
                            document.querySelectorAll('.lawyer-card').forEach(card => {
                                card.addEventListener('click', function() {
                                    document.querySelectorAll('.lawyer-card').forEach(c => {
                                        c.classList.remove('selected');
                                    });
                                    this.classList.add('selected');
                                    selectedLawyerId = this.dataset.id;
                                    selectedLawyerName = this.dataset.name;
                                    
                                    // Remove any existing hidden input
                                    document.querySelector('input[name="lawyer_id"]')?.remove();
                                    
                                    // Add hidden input for lawyer ID
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = 'lawyer_id';
                                    hiddenInput.value = selectedLawyerId;
                                    document.getElementById('appointment-form').appendChild(hiddenInput);
                                });
                            });
                        } else {
                            container.innerHTML = '<p>No lawyers found for this specialization. Please try another legal issue.</p>';
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        container.innerHTML = '<p>Error loading lawyers. Please try again.</p>';
                    }
                } else {
                    container.innerHTML = '<p>Error loading lawyers. Please try again.</p>';
                }
            };
            xhr.onerror = function() {
                container.innerHTML = '<p>Error loading lawyers. Please try again.</p>';
            };
            xhr.send();
        }
        
        // Generate rating stars
        function getRatingStars(rating) {
            const fullStars = Math.floor(rating);
            const halfStar = rating % 1 >= 0.5;
            let stars = '';
            
            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    stars += '<span class="star">★</span>';
                } else if (i === fullStars + 1 && halfStar) {
                    stars += '<span class="star">★</span>';
                } else {
                    stars += '<span class="star empty">★</span>';
                }
            }
            
            return stars;
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show success modal if appointment was just booked
            <?php if (isset($_SESSION['appointment_success']) && $_SESSION['appointment_success']): ?>
                showSuccessModal();
                <?php unset($_SESSION['appointment_success']); ?>
            <?php endif; ?>
        });
        
        // Show success modal
        function showSuccessModal() {
            document.getElementById('success-modal').style.display = 'flex';
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('success-modal').style.display = 'none';
            
            // Reset form and go back to step 1
            document.getElementById('appointment-form').reset();
            selectedLawyerId = null;
            selectedLawyerName = null;
            
            // Remove any hidden inputs
            document.querySelectorAll('input[type="hidden"][name="lawyer_id"]').forEach(input => {
                input.remove();
            });
            
            // Go back to step 1
            showStep(1);
        }
    </script>
</body>
</html>