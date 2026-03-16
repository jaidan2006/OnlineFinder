<?php
require_once 'includes/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $center_name = sanitize_input($_POST['center_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = sanitize_input($_POST['password']);
    $address = sanitize_input($_POST['address']);
    $location = sanitize_input($_POST['location']);
    $courses_offered = sanitize_input($_POST['courses_offered']);
    $teaching_mode = sanitize_input($_POST['teaching_mode']);
    $description = sanitize_input($_POST['description']);
    $website = sanitize_input($_POST['website']);
    
    // Check if email already exists
    $check_sql = "SELECT center_id FROM coaching_centers WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        set_message('Email already registered. Please use a different email.', 'error');
    } else {
        // Insert new coaching center
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO coaching_centers (center_name, email, phone, password, address, location, courses_offered, teaching_mode, description, website) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $center_name, $email, $phone, $hashed_password, $address, $location, $courses_offered, $teaching_mode, $description, $website);
        
        if ($stmt->execute()) {
            set_message('Registration successful! Your account is pending approval. Please wait for admin approval.', 'success');
            redirect('login.php');
        } else {
            set_message('Registration failed. Please try again.', 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coaching Center Registration - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Top Contact Bar */
        .top-bar {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }

        .contact-info {
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
        }

        .contact-info span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-info i {
            font-size: 0.8rem;
        }

        /* Main Header */
        .main-header {
            background-color: #343a40;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-header .logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .main-header .logo i {
            font-size: 2rem;
            color: #007bff;
        }

        .main-nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        .main-nav a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .main-nav a:hover {
            background-color: #007bff;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-login {
            background-color: transparent;
            color: white;
            border: 2px solid white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: white;
            color: #343a40;
        }

        .btn-register {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-register.active {
            background-color: #007bff;
            color: white;
        }

        .btn-register.active:hover {
            background-color: #0056b3;
        }

        /* Registration Section */
        .registration-section {
            padding: 4rem 0;
            background-color: #f8f9fa;
            min-height: calc(100vh - 200px);
        }

        .form-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 2rem auto;
            border: 1px solid #e9ecef;
            max-width: 800px;
            width: 100%;
        }

        .form-container h2 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 2rem;
            color: #007bff;
            text-align: center;
        }

        .form-container h2 i {
            margin-right: 0.5rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            background-color: white;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-right: 15px;
            padding-left: 15px;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            min-width: 120px;
            justify-content: center;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108,117,125,0.3);
        }

        .form-links {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }

        .form-links p {
            margin-bottom: 0.8rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .form-links a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .form-links a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-info {
                justify-content: center;
                gap: 1rem;
                font-size: 0.8rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
                padding: 0.5rem 20px;
            }
            
            .main-header .logo {
                font-size: 1.2rem;
                text-align: center;
            }
            
            .main-header .logo i {
                font-size: 1.5rem;
            }
            
            .main-nav ul {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
                width: 100%;
            }
            
            .main-nav a {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
            
            .auth-buttons {
                justify-content: center;
                width: 100%;
            }
            
            .form-container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .form-container {
                margin: 0.5rem;
                padding: 1.5rem;
            }
            
            .auth-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Top Contact Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span><i class="fas fa-phone"></i> +91 98765 43210</span>
                <span><i class="fas fa-envelope"></i> info@tutorfinder.com</span>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Tutor Finder</span>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    </ul>
                </nav>
                
                <div class="auth-buttons">
                    <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="center_register.php" class="btn-register active"><i class="fas fa-building"></i> Register</a>
                </div>
            </div>
        </div>
    </header>
</head>
<body>
    
    <!-- Registration Section -->
    <section style="padding: 4rem 0; background-color: #f8f9fa;">
        <div class="container">
            <div class="row">
                <div class="col-md-10" style="margin: 0 auto;">
                    <div class="form-container">
                        <h2 class="text-center" style="color: #007bff; margin-bottom: 2rem;">Coaching Center Registration</h2>
                        
                        <?php echo display_message(); ?>
                        
                        <form method="POST" onsubmit="return validateCenterRegistration()">
                            <div class="form-group">
                                <label for="center_name">Center Name <span style="color: red;">*</span></label>
                                <input type="text" name="center_name" id="center_name" class="form-control" placeholder="e.g., Excel Learning Center" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address <span style="color: red;">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                        <small id="email_error" style="color: green;"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span style="color: red;">*</span></label>
                                        <input type="tel" name="phone" id="phone" class="form-control" placeholder="10-digit mobile number" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Full Address <span style="color: red;">*</span></label>
                                <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter complete address with landmark" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="location">City/Area <span style="color: red;">*</span></label>
                                        <input type="text" name="location" id="location" class="form-control" placeholder="e.g., Delhi, Mumbai, Bangalore" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="teaching_mode">Teaching Mode <span style="color: red;">*</span></label>
                                        <select name="teaching_mode" id="teaching_mode" class="form-control" required>
                                            <option value="">Select Teaching Mode</option>
                                            <option value="online">Online Only</option>
                                            <option value="offline">Offline Only</option>
                                            <option value="both">Both Online & Offline</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="courses_offered">Courses Offered <span style="color: red;">*</span></label>
                                <input type="text" name="courses_offered" id="courses_offered" class="form-control" placeholder="e.g., Mathematics, Science, English, Competitive Exams" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="website">Website (Optional)</label>
                                <input type="url" name="website" id="website" class="form-control" placeholder="https://www.example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">About Your Center</label>
                                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Tell students about your coaching center, facilities, achievements..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password <span style="color: red;">*</span></label>
                                        <input type="password" name="password" id="password" class="form-control" required>
                                        <small id="password_strength"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password <span style="color: red;">*</span></label>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Register</button>
                                <a href="index.php" class="btn btn-secondary">Cancel</a>
                            </div>
                            
                            <p class="text-center">
                                Already have an account? <a href="login.php" style="color: #007bff;">Login here</a>
                            </p>
                            
                            <div class="alert alert-info">
                                <strong>Note:</strong> Your registration will be reviewed by the admin before approval. 
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
