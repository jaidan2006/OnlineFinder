<?php
require_once 'includes/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        set_message('Please fill in all fields.', 'error');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message('Please enter a valid email address.', 'error');
    } else {
        $login_success = false;
        $redirect_page = '';
        
        // Auto-detect user type by checking each table in order
        
        // Check admin first
        $admin_sql = "SELECT admin_id, username, password, email FROM admin WHERE email = ?";
        $admin_stmt = $conn->prepare($admin_sql);
        $admin_stmt->bind_param("s", $email);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        
        if ($admin_result->num_rows > 0) {
            $admin = $admin_result->fetch_assoc();
            
            // Check password - support both plain text and hashed passwords
            $password_valid = false;
            if (password_verify($password, $admin['password'])) {
                $password_valid = true;
            } elseif ($password === $admin['password']) {
                // Plain text password match (for sample data)
                $password_valid = true;
            }
            
            if ($password_valid) {
                // Set admin session variables
                $_SESSION['user_id'] = $admin['admin_id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['username'] = $admin['username'];
                $_SESSION['email'] = $admin['email'];
                
                // Log login
                $login_sql = "INSERT INTO login_credentials (user_id, user_type, ip_address) VALUES (?, ?, ?)";
                $login_stmt = $conn->prepare($login_sql);
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $login_stmt->bind_param("iss", $admin['admin_id'], $_SESSION['user_type'], $ip_address);
                $login_stmt->execute();
                
                $login_success = true;
                $redirect_page = 'admin_dashboard.php';
                set_message('Login successful! Welcome to Admin Panel.', 'success');
            } else {
                set_message('Invalid email or password.', 'error');
            }
        } else {
            // Check student
            $student_sql = "SELECT student_id, first_name, last_name, email, password FROM students WHERE email = ? AND status = 'active'";
            $student_stmt = $conn->prepare($student_sql);
            $student_stmt->bind_param("s", $email);
            $student_stmt->execute();
            $student_result = $student_stmt->get_result();
            
            if ($student_result->num_rows > 0) {
                $student = $student_result->fetch_assoc();
                
                if (password_verify($password, $student['password'])) {
                    // Set student session variables
                    $_SESSION['user_id'] = $student['student_id'];
                    $_SESSION['user_type'] = 'student';
                    $_SESSION['first_name'] = $student['first_name'];
                    $_SESSION['last_name'] = $student['last_name'];
                    $_SESSION['email'] = $student['email'];
                    
                    // Log login
                    $login_sql = "INSERT INTO login_credentials (user_id, user_type, ip_address) VALUES (?, ?, ?)";
                    $login_stmt = $conn->prepare($login_sql);
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $login_stmt->bind_param("iss", $student['student_id'], $_SESSION['user_type'], $ip_address);
                    $login_stmt->execute();
                    
                    $login_success = true;
                    $redirect_page = 'student_dashboard.php';
                    set_message('Login successful! Welcome back, ' . $student['first_name'] . '.', 'success');
                } else {
                    set_message('Invalid email or password.', 'error');
                }
            } else {
                // Check tutor
                $tutor_sql = "SELECT tutor_id, first_name, last_name, email, password, approved FROM tutors WHERE email = ?";
                $tutor_stmt = $conn->prepare($tutor_sql);
                $tutor_stmt->bind_param("s", $email);
                $tutor_stmt->execute();
                $tutor_result = $tutor_stmt->get_result();
                
                if ($tutor_result->num_rows > 0) {
                    $tutor = $tutor_result->fetch_assoc();
                    
                    if (password_verify($password, $tutor['password'])) {
                        if ($tutor['approved'] == 'approved') {
                            // Set session variables
                            $_SESSION['user_id'] = $tutor['tutor_id'];
                            $_SESSION['user_type'] = 'tutor';
                            $_SESSION['first_name'] = $tutor['first_name'];
                            $_SESSION['last_name'] = $tutor['last_name'];
                            $_SESSION['email'] = $tutor['email'];
                            
                            // Log login
                            $login_sql = "INSERT INTO login_credentials (user_id, user_type, ip_address) VALUES (?, ?, ?)";
                            $login_stmt = $conn->prepare($login_sql);
                            $ip_address = $_SERVER['REMOTE_ADDR'];
                            $login_stmt->bind_param("iss", $tutor['tutor_id'], $_SESSION['user_type'], $ip_address);
                            $login_stmt->execute();
                            
                            $login_success = true;
                            $redirect_page = 'tutor_dashboard.php';
                            set_message('Login successful! Welcome back, ' . $tutor['first_name'] . '.', 'success');
                        } elseif ($tutor['approved'] == 'pending') {
                            set_message('Your account is pending approval. Please wait for admin approval.', 'error');
                        } elseif ($tutor['approved'] == 'rejected') {
                            set_message('Your account has been rejected. Please contact admin for details.', 'error');
                        }
                    } else {
                        set_message('Invalid email or password.', 'error');
                    }
                } else {
                    // Check coaching center
                    $center_sql = "SELECT center_id, center_name, email, password, approved FROM coaching_centers WHERE email = ?";
                    $center_stmt = $conn->prepare($center_sql);
                    $center_stmt->bind_param("s", $email);
                    $center_stmt->execute();
                    $center_result = $center_stmt->get_result();
                    
                    if ($center_result->num_rows > 0) {
                        $center = $center_result->fetch_assoc();
                        
                        if (password_verify($password, $center['password'])) {
                            if ($center['approved'] == 'approved') {
                                // Set session variables
                                $_SESSION['user_id'] = $center['center_id'];
                                $_SESSION['user_type'] = 'center';
                                $_SESSION['center_name'] = $center['center_name'];
                                $_SESSION['email'] = $center['email'];
                                
                                // Log login
                                $login_sql = "INSERT INTO login_credentials (user_id, user_type, ip_address) VALUES (?, ?, ?)";
                                $login_stmt = $conn->prepare($login_sql);
                                $ip_address = $_SERVER['REMOTE_ADDR'];
                                $login_stmt->bind_param("iss", $center['center_id'], $_SESSION['user_type'], $ip_address);
                                $login_stmt->execute();
                                
                                $login_success = true;
                                $redirect_page = 'center_dashboard.php';
                                set_message('Login successful! Welcome back, ' . $center['center_name'] . '.', 'success');
                            } elseif ($center['approved'] == 'pending') {
                                set_message('Your center is pending approval. Please wait for admin approval.', 'error');
                            } elseif ($center['approved'] == 'rejected') {
                                set_message('Your center has been rejected. Please contact admin for details.', 'error');
                            }
                        } else {
                            set_message('Invalid email or password.', 'error');
                        }
                    } else {
                        set_message('Invalid email or password.', 'error');
                    }
                }
            }
        }
        
        if ($login_success) {
            redirect($redirect_page);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            padding: 3rem;
        }

        .login-form h2 {
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,123,255,0.3);
        }

        .register-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .register-links p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .register-links a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .register-links a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
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

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
            
            <?php echo display_message(); ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="register-links">
                <p>New to our platform?</p>
                <a href="student_register.php">Register as Student</a> |
                <a href="tutor_register.php">Register as Tutor</a> |
                <a href="center_register.php">Register as Center</a>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email) {
                e.preventDefault();
                alert('Please enter your email address.');
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                alert('Please enter your password.');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
        });
    </script>
</body>
</html>
