<?php
require_once 'includes/config.php';

// Check if user is logged in as student
if (!is_student()) {
    set_message('Please login as a student to access this page.', 'error');
    redirect('student_login.php');
}

// Get student ID from session
$student_id = $_SESSION['user_id'];

// Fetch student details
$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_message('Student profile not found.', 'error');
    redirect('index.php');
}

$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $location = sanitize_input($_POST['location']);
    $education_level = sanitize_input($_POST['education_level']);
    $school_college = sanitize_input($_POST['school_college']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message('Please enter a valid email address.', 'error');
    } else {
        // Update student profile
        $sql = "UPDATE students SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                location = ?, 
                education_level = ?, 
                school_college = ?
                WHERE student_id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            set_message('Database error: ' . $conn->error, 'error');
        } else {
            $stmt->bind_param("ssssssssi", 
                $first_name, 
                $last_name, 
                $email, 
                $phone, 
                $address, 
                $location, 
                $education_level, 
                $school_college,
                $student_id
            );
            
            if ($stmt->execute()) {
                set_message('Profile updated successfully!', 'success');
                redirect('student_dashboard.php');
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
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
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
        
        .row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .col-md-6 {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search.php">Search Tutors</a></li>
                    <li><a href="student_dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Edit Profile Section -->
    <section style="padding: 2rem 0; background-color: #f8f9fa;">
        <div class="container">
            <div class="form-container">
                <h2 class="header-title">Edit Student Profile</h2>
                
                <?php echo display_message(); ?>
                
                <form method="POST" onsubmit="return validateProfile()">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" name="first_name" id="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" name="last_name" id="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" name="phone" id="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address *</label>
                        <input type="text" name="address" id="address" class="form-control" 
                               value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Location *</label>
                                <input type="text" name="location" id="location" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['location'] ?? ''); ?>" 
                                       placeholder="e.g., Delhi, Mumbai, Bangalore" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="education_level">Education Level *</label>
                                <select name="education_level" id="education_level" class="form-control" required>
                                    <option value="">Select Education Level</option>
                                    <option value="high-school" <?php echo (($student['education_level'] ?? '') == 'high-school') ? 'selected' : ''; ?>>High School</option>
                                    <option value="intermediate" <?php echo (($student['education_level'] ?? '') == 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="undergraduate" <?php echo (($student['education_level'] ?? '') == 'undergraduate') ? 'selected' : ''; ?>>Undergraduate</option>
                                    <option value="graduate" <?php echo (($student['education_level'] ?? '') == 'graduate') ? 'selected' : ''; ?>>Graduate</option>
                                    <option value="postgraduate" <?php echo (($student['education_level'] ?? '') == 'postgraduate') ? 'selected' : ''; ?>>Postgraduate</option>
                                    <option value="other" <?php echo (($student['education_level'] ?? '') == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="school_college">School/College *</label>
                        <input type="text" name="school_college" id="school_college" class="form-control" 
                               value="<?php echo htmlspecialchars($student['school_college'] ?? ''); ?>" 
                               placeholder="Enter your school or college name" required>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn">Update Profile</button>
                        <a href="student_dashboard.php" class="btn btn-secondary">Cancel</a>
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
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const location = document.getElementById('location').value.trim();
            const educationLevel = document.getElementById('education_level').value;
            const schoolCollege = document.getElementById('school_college').value.trim();
            
            if (!firstName) {
                alert('Please enter your first name');
                return false;
            }
            
            if (!lastName) {
                alert('Please enter your last name');
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
            
            if (!educationLevel) {
                alert('Please select education level');
                return false;
            }
            
            if (!schoolCollege) {
                alert('Please enter your school/college name');
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
