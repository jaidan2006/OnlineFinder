<?php
require_once 'includes/config.php';

// Check if user is logged in as coaching center
if (!is_center()) {
    set_message('Please login as a coaching center to access this page.', 'error');
    redirect('login.php');
}

// Get center ID from session
$center_id = $_SESSION['user_id'];

// Fetch current center details
$sql = "SELECT * FROM coaching_centers WHERE center_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_message('Center profile not found.', 'error');
    redirect('provider_dashboard.php');
}

$center = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $center_name = sanitize_input($_POST['center_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $location = sanitize_input($_POST['location']);
    $website = sanitize_input($_POST['website']);
    $courses_offered = sanitize_input($_POST['courses_offered']);
    $teaching_mode = sanitize_input($_POST['teaching_mode']);
    $description = sanitize_input($_POST['description']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message('Please enter a valid email address.', 'error');
    } else {
        // Update center profile with basic columns only
        $sql = "UPDATE coaching_centers SET 
                center_name = ?, 
                email = ?, 
                phone = ?, 
                address = ?,
                location = ?
                WHERE center_id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $error_msg = "Database prepare error: " . $conn->error;
            set_message($error_msg, 'error');
        } else {
            $stmt->bind_param("sssssi", 
                $center_name, 
                $email, 
                $phone, 
                $address, 
                $location,
                $center_id
            );
            
            if ($stmt->execute()) {
                set_message('Profile updated successfully!', 'success');
                redirect('center_dashboard.php');
            } else {
                set_message('Profile update failed: ' . $stmt->error, 'error');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
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

        .btn-logout {
            background-color: #dc3545;
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

        .btn-logout:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .btn-secondary {
            background-color: #dc3545;
            color: white;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        .header-title {
            text-align: center;
            color: #007bff;
            margin-bottom: 2rem;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
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
    </style>
</head>
<body>
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
                        <li><a href="center_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="center_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    </ul>
                </nav>
                
                <div class="auth-buttons">
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Edit Profile Section -->
    <section style="padding: 2rem 0; background-color: #f8f9fa;">
        <div class="container">
            <div class="form-container">
                <h2 class="header-title">Edit Coaching Center Profile</h2>
                
                <?php echo display_message(); ?>
                
                <form method="POST" onsubmit="return validateProfile()">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="center_name">Center Name <span style="color: red;">*</span></label>
                                <input type="text" name="center_name" id="center_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($center['center_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="website">Website</label>
                                <input type="url" name="website" id="website" class="form-control" 
                                       value="<?php echo htmlspecialchars($center['website'] ?? ''); ?>" 
                                       placeholder="https://www.example.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address <span style="color: red;">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($center['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number <span style="color: red;">*</span></label>
                                <input type="tel" name="phone" id="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($center['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address <span style="color: red;">*</span></label>
                        <input type="text" name="address" id="address" class="form-control" 
                               value="<?php echo htmlspecialchars($center['address'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Location <span style="color: red;">*</span></label>
                                <input type="text" name="location" id="location" class="form-control" 
                                       value="<?php echo htmlspecialchars($center['location'] ?? ''); ?>" 
                                       placeholder="e.g., Delhi, Mumbai, Bangalore" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="teaching_mode">Teaching Mode <span style="color: red;">*</span></label>
                                <select name="teaching_mode" id="teaching_mode" class="form-control" required>
                                    <option value="">Select Mode</option>
                                    <option value="online" <?php echo (($center['teaching_mode'] ?? '') == 'online') ? 'selected' : ''; ?>>Online</option>
                                    <option value="offline" <?php echo (($center['teaching_mode'] ?? '') == 'offline') ? 'selected' : ''; ?>>Offline</option>
                                    <option value="both" <?php echo (($center['teaching_mode'] ?? '') == 'both') ? 'selected' : ''; ?>>Both</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="courses_offered">Courses Offered <span style="color: red;">*</span></label>
                        <input type="text" name="courses_offered" id="courses_offered" class="form-control" 
                               value="<?php echo htmlspecialchars($center['courses_offered'] ?? ''); ?>" 
                               placeholder="e.g., Mathematics, Physics, Chemistry, Biology" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" 
                                  placeholder="Describe your coaching center, facilities, teaching methodology, etc."><?php echo htmlspecialchars($center['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="center_dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        function validateProfile() {
            const centerName = document.getElementById('center_name').value.trim();
            const ownerName = document.getElementById('owner_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const location = document.getElementById('location').value.trim();
            const coursesOffered = document.getElementById('courses_offered').value.trim();
            
            if (!centerName) {
                alert('Please enter center name');
                return false;
            }
            
            if (!ownerName) {
                alert('Please enter owner name');
                return false;
            }
            
            if (!email) {
                alert('Please enter email address');
                return false;
            }
            
            if (!validateEmail(email)) {
                alert('Please enter a valid email address');
                return false;
            }
            
            if (!phone) {
                alert('Please enter phone number');
                return false;
            }
            
            if (!address) {
                alert('Please enter address');
                return false;
            }
            
            if (!location) {
                alert('Please enter location');
                return false;
            }
            
            if (!coursesOffered) {
                alert('Please enter courses offered');
                return false;
            }
            
            return true;
        }
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    </script>
</body>
</html>
