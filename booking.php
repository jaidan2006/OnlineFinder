<?php
require_once 'includes/config.php';

// Check if user is logged in as student
if (!is_student()) {
    set_message('Please login as a student to book sessions.', 'error');
    redirect('student_login.php');
}

// Get booking parameters
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$id = isset($_GET['id']) ? sanitize_input($_GET['id']) : '';

if (empty($type) || empty($id) || !in_array($type, ['tutor', 'center']) || !is_numeric($id)) {
    set_message('Invalid booking parameters.', 'error');
    redirect('search.php');
}

// Get tutor or center details
if ($type == 'tutor') {
    $sql = "SELECT * FROM tutors WHERE tutor_id = ? AND approved = 'approved' AND availability_status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        set_message('Tutor not found or unavailable.', 'error');
        redirect('search.php');
    }
    
    $provider = $result->fetch_assoc();
    $provider_name = $provider['first_name'] . ' ' . $provider['last_name'];
    $provider_type = 'Tutor';
} else {
    $sql = "SELECT * FROM coaching_centers WHERE center_id = ? AND approved = 'approved' AND availability_status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        set_message('Coaching center not found or unavailable.', 'error');
        redirect('search.php');
    }
    
    $provider = $result->fetch_assoc();
    $provider_name = $provider['center_name'];
    $provider_type = 'Coaching Center';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = sanitize_input($_POST['subject_id']);
    $booking_date = sanitize_input($_POST['booking_date']);
    $booking_time = sanitize_input($_POST['booking_time']);
    $duration_hours = sanitize_input($_POST['duration_hours']);
    $mode = sanitize_input($_POST['mode']);
    $message = sanitize_input($_POST['message']);
    
    // Find subject ID from subject name
    $subject_id = null;
    if (!empty($subject_name)) {
        $sql = "SELECT subject_id FROM subjects WHERE subject_name = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $subject_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $subject_id = $row['subject_id'];
        } else {
            // If subject not found, insert it as a new subject
            $insert_sql = "INSERT INTO subjects (subject_name) VALUES (?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("s", $subject_name);
            if ($insert_stmt->execute()) {
                $subject_id = $conn->insert_id;
            }
        }
    }
    
    if ($subject_id === null) {
        set_message('Invalid subject provided.', 'error');
    } else {
        // Validate booking date (should be future date)
        $booking_datetime = new DateTime($booking_date . ' ' . $booking_time);
        $current_datetime = new DateTime();
        
        if ($booking_datetime <= $current_datetime) {
            set_message('Booking date and time must be in the future.', 'error');
        } else {
            // Insert booking
            $sql = "INSERT INTO bookings (student_id, " . ($type == 'tutor' ? 'tutor_id' : 'center_id') . ", subject_id, booking_date, booking_time, duration_hours, mode, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($type == 'tutor') {
                $stmt->bind_param("iississs", $_SESSION['user_id'], $id, $subject_id, $booking_date, $booking_time, $duration_hours, $mode, $message);
            } else {
                $stmt->bind_param("iississs", $_SESSION['user_id'], $id, $subject_id, $booking_date, $booking_time, $duration_hours, $mode, $message);
            }
            
            if ($stmt->execute()) {
                set_message('Booking request submitted successfully! The ' . strtolower($provider_type) . ' will contact you soon.', 'success');
                redirect('student_dashboard.php');
            } else {
                set_message('Booking failed. Please try again.', 'error');
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
    <title>Book Session - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
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

        @media (max-width: 768px) {
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
                        <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
                        <li><a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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

    <!-- Booking Section -->
    <section style="padding: 2rem 0; background-color: #f8f9fa;">
        <div class="container">
            <div class="row">
                <div class="col-md-8" style="margin: 0 auto;">
                    <div class="form-container">
                        <h2 class="text-center" style="color: #007bff; margin-bottom: 2rem;">Book Session with <?php echo htmlspecialchars($provider_name); ?></h2>
                        
                        <div class="alert alert-info">
                            <strong><?php echo $provider_type; ?> Details:</strong><br>
                            <?php if ($type == 'tutor'): ?>
                                Qualification: <?php echo htmlspecialchars($provider['qualification']); ?><br>
                                Experience: <?php echo $provider['experience_years']; ?> years<br>
                                Subjects: <?php echo htmlspecialchars($provider['subjects_taught']); ?><br>
                                Hourly Rate: ₹<?php echo number_format($provider['hourly_rate'], 2); ?>
                            <?php else: ?>
                                Courses: <?php echo htmlspecialchars($provider['courses_offered']); ?><br>
                                Location: <?php echo htmlspecialchars($provider['location']); ?><br>
                                Address: <?php echo htmlspecialchars($provider['address']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php echo display_message(); ?>
                        
                        <form method="POST" onsubmit="return validateBooking()">
                            <div class="form-group">
                                <label for="subject_id">Subject *</label>
                                <input type="text" name="subject_id" id="subject_id" class="form-control" placeholder="Enter subject (e.g., Mathematics, Chemistry, Physics)" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="booking_date">Booking Date *</label>
                                        <input type="date" name="booking_date" id="booking_date" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="booking_time">Booking Time *</label>
                                        <input type="time" name="booking_time" id="booking_time" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration_hours">Duration (hours) *</label>
                                        <select name="duration_hours" id="duration_hours" class="form-control" required>
                                            <option value="">Select Duration</option>
                                            <option value="1">1 hour</option>
                                            <option value="2">2 hours</option>
                                            <option value="3">3 hours</option>
                                            <option value="4">4 hours</option>
                                            <option value="5">5 hours</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mode">Learning Mode *</label>
                                        <select name="mode" id="mode" class="form-control" required>
                                            <option value="">Select Mode</option>
                                            <?php
                                            $available_modes = explode(',', $provider['teaching_mode']);
                                            foreach ($available_modes as $mode_option) {
                                                $mode_option = trim($mode_option);
                                                if ($mode_option == 'both') {
                                                    echo '<option value="online">Online</option>';
                                                    echo '<option value="offline">Offline</option>';
                                                } else {
                                                    echo '<option value="' . $mode_option . '">' . ucfirst($mode_option) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message to <?php echo strtolower($provider_type); ?> *</label>
                                <textarea name="message" id="message" class="form-control" rows="4" placeholder="Please mention your requirements, preferred schedule, and any specific topics you want to cover..." required></textarea>
                            </div>
                            
                            <?php if ($type == 'tutor'): ?>
                                <div class="alert alert-warning">
                                    <strong>Estimated Cost:</strong> 
                                    <span id="estimated_cost">Select duration to calculate</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Submit Booking Request</button>
                                <a href="<?php echo ($type == 'tutor') ? 'tutor_profile.php?id=' . $id : 'center_profile.php?id=' . $id; ?>" class="btn btn-secondary">Back to Profile</a>
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>Important Notes:</strong><br>
                                • Your booking request will be sent to the <?php echo strtolower($provider_type); ?> for confirmation<br>
                                • You will receive a notification once the booking is confirmed<br>
                                • Payment details will be shared after confirmation<br>
                                • Please provide accurate contact information
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

    <script src="js/script.js">
        // Calculate estimated cost for tutor bookings
        document.getElementById('duration_hours').addEventListener('change', function() {
            <?php if ($type == 'tutor'): ?>
                const duration = parseInt(this.value);
                const hourlyRate = <?php echo $provider['hourly_rate']; ?>;
                const totalCost = duration * hourlyRate;
                document.getElementById('estimated_cost').textContent = '₹' + totalCost.toFixed(2);
            <?php endif; ?>
        });
    </script>
</body>
</html>
