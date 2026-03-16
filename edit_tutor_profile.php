<?php
require_once 'includes/config.php';

// Check if user is logged in as tutor
if (!is_tutor()) {
    set_message('Please login as a tutor to access this page.', 'error');
    redirect('login.php');
}

// Get tutor ID from session
$tutor_id = $_SESSION['user_id'];

// Fetch current tutor details
$sql = "SELECT * FROM tutors WHERE tutor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_message('Tutor profile not found.', 'error');
    redirect('tutor_dashboard.php');
}

$tutor = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $qualification = sanitize_input($_POST['qualification']);
    $subjects_taught = sanitize_input($_POST['subjects_taught']);
    $teaching_mode = sanitize_input($_POST['teaching_mode']);
    $location = sanitize_input($_POST['location']);
    $experience = sanitize_input($_POST['experience']);
    $description = sanitize_input($_POST['description']);
    $availability_status = sanitize_input($_POST['availability_status']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message('Please enter a valid email address.', 'error');
    } else {
        // Update tutor profile
        $sql = "UPDATE tutors SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                qualification = ?,
                subjects_taught = ?,
                teaching_mode = ?,
                location = ?,
                experience_years = ?,
                description = ?,
                availability_status = ?
                WHERE tutor_id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $error_msg = "Database prepare error: " . $conn->error;
            set_message($error_msg, 'error');
        } else {
            $stmt->bind_param("ssssssssissi", 
    $first_name, 
    $last_name, 
    $email, 
    $phone, 
    $qualification, 
    $subjects_taught, 
    $teaching_mode, 
    $location, 
    $experience, 
    $description, 
    $availability_status, 
    $tutor_id
);
            
            if ($stmt->execute()) {
                set_message('Profile updated successfully!', 'success');
                redirect('tutor_dashboard.php');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            margin: 2rem auto;
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #007bff;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-secondary {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .text-center {
            text-align: center;
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
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
                flex-wrap: wrap;
            }
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
                        <li><a href="tutor_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="tutor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
    <section class="edit-section">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
                    <p>Update your tutor profile information</p>
                </div>

                <?php echo display_message(); ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($tutor['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($tutor['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($tutor['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" name="phone" id="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($tutor['phone']); ?>">
                        </div>
                    </div>

                    <h4 style="color: #007bff; margin: 2rem 0 1rem 0;">Professional Information</h4>
                    <div class="form-group">
                        <label for="qualification">Qualification</label>
                        <input type="text" name="qualification" id="qualification" class="form-control" 
                               value="<?php echo htmlspecialchars($tutor['qualification']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="subjects_taught">Subjects Taught</label>
                        <input type="text" name="subjects_taught" id="subjects_taught" class="form-control" 
                               value="<?php echo htmlspecialchars($tutor['subjects_taught']); ?>" 
                               placeholder="e.g., Mathematics, Physics, Chemistry" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="teaching_mode">Teaching Mode</label>
                            <select name="teaching_mode" id="teaching_mode" class="form-control" required>
                                <option value="online" <?php echo ($tutor['teaching_mode'] == 'online') ? 'selected' : ''; ?>>Online</option>
                                <option value="offline" <?php echo ($tutor['teaching_mode'] == 'offline') ? 'selected' : ''; ?>>Offline</option>
                                <option value="both" <?php echo ($tutor['teaching_mode'] == 'both') ? 'selected' : ''; ?>>Both</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" name="location" id="location" class="form-control" 
                                   value="<?php echo htmlspecialchars($tutor['location']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="experience">Experience (years)</label>
                        <input type="number" name="experience" id="experience" class="form-control" 
                               value="<?php echo htmlspecialchars($tutor['experience_years'] ?? ''); ?>" 
                               min="0" step="1" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" 
                                  placeholder="Tell students about yourself, your teaching style, and expertise..." required><?php echo htmlspecialchars($tutor['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="availability_status">Availability Status</label>
                        <select name="availability_status" id="availability_status" class="form-control" required>
                            <option value="available" <?php echo ($tutor['availability_status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="unavailable" <?php echo ($tutor['availability_status'] == 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                        </select>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="tutor_dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        function validateProfile() {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const qualification = document.getElementById('qualification').value.trim();
            const subjectsTaught = document.getElementById('subjects_taught').value.trim();
            const location = document.getElementById('location').value.trim();
            const description = document.getElementById('description').value.trim();
            
            if (!firstName || !lastName || !email || !qualification || !subjectsTaught || !location || !description) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (!validateEmail(email)) {
                alert('Please enter a valid email address.');
                return false;
            }
            
            return true;
        }
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Add form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!validateProfile()) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
