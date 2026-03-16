<?php
require_once 'includes/config.php';

// Debug: Check current session
error_log("Session data in view_student_booking.php: " . print_r($_SESSION, true));
error_log("User type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'not set'));
error_log("is_center(): " . (is_center() ? 'true' : 'false'));
error_log("is_tutor(): " . (is_tutor() ? 'true' : 'false'));

// Check if user is logged in as coaching center or tutor
if (!is_center() && !is_tutor()) {
    $error_msg = 'Access denied. You must be logged in as a coaching center or tutor to access this page.';
    error_log($error_msg);
    set_message($error_msg, 'error');
    
    // Check if user is logged in as student
    if (is_student()) {
        error_log("User is logged in as student, redirecting to student_dashboard.php");
        redirect('student_dashboard.php');
    } else {
        error_log("User not logged in, redirecting to login.php");
        redirect('login.php');
    }
}

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? sanitize_input($_GET['booking_id']) : '';

if (empty($booking_id) || !is_numeric($booking_id)) {
    set_message('Invalid booking ID.', 'error');
    if (is_center()) {
        redirect('center_dashboard.php');
    } else {
        redirect('tutor_dashboard.php');
    }
}

// Get user ID from session and determine user type
$user_id = $_SESSION['user_id'];
$is_center_user = is_center();

// Fetch booking details with student information
if ($is_center_user) {
    $sql = "SELECT b.*, s.first_name, s.last_name, s.email as student_email, s.phone as student_phone, 
            s.address as student_address
            FROM bookings b 
            JOIN students s ON b.student_id = s.student_id 
            WHERE b.booking_id = ? AND b.center_id = ?";
    $stmt = $conn->prepare($sql);
    $bind_result = $stmt->bind_param("ii", $booking_id, $user_id);
    $redirect_page = 'center_dashboard.php';
} else {
    // Tutor query
    $sql = "SELECT b.*, s.first_name, s.last_name, s.email as student_email, s.phone as student_phone, 
            s.address as student_address
            FROM bookings b 
            JOIN students s ON b.student_id = s.student_id 
            WHERE b.booking_id = ? AND b.tutor_id = ?";
    $stmt = $conn->prepare($sql);
    $bind_result = $stmt->bind_param("ii", $booking_id, $user_id);
    $redirect_page = 'tutor_dashboard.php';
}

if ($stmt === false) {
    $error_msg = "Database error preparing booking query: " . $conn->error;
    error_log($error_msg);
    set_message($error_msg, 'error');
    redirect($redirect_page);
}

if ($bind_result === false) {
    $error_msg = "Database bind error: " . $stmt->error;
    error_log($error_msg);
    set_message($error_msg, 'error');
    redirect($redirect_page);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_message('Booking not found or you do not have permission to view it.', 'error');
    redirect($redirect_page);
}

$booking = $result->fetch_assoc();

// Handle form submission for approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = sanitize_input($_POST['action']);
    $payment_details = sanitize_input($_POST['payment_details']);
    $message = sanitize_input($_POST['message']);
    
    if ($action == 'approve') {
        // Update booking status to confirmed with payment details and message
        $update_sql = "UPDATE bookings SET status = 'confirmed', payment_details = ?, message = ? WHERE booking_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if ($update_stmt === false) {
            // Try updating without message column first
            $update_sql_simple = "UPDATE bookings SET status = 'confirmed', payment_details = ? WHERE booking_id = ?";
            $update_stmt_simple = $conn->prepare($update_sql_simple);
            
            if ($update_stmt_simple === false) {
                // Try updating just status
                $update_sql_status = "UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?";
                $update_stmt_status = $conn->prepare($update_sql_status);
                
                if ($update_stmt_status === false) {
                    set_message('Database error preparing approval query: ' . $conn->error, 'error');
                } else {
                    $update_stmt_status->bind_param("i", $booking_id);
                    
                    if ($update_stmt_status->execute()) {
                        set_message('Booking approved successfully! (Basic approval only)', 'success');
                    } else {
                        set_message('Failed to approve booking: ' . $update_stmt_status->error, 'error');
                    }
                }
            } else {
                $update_stmt_simple->bind_param("si", $payment_details, $booking_id);
                
                if ($update_stmt_simple->execute()) {
                    // Try to add message column if it doesn't exist
                    $alter_sql = "ALTER TABLE bookings ADD COLUMN message TEXT NULL DEFAULT NULL AFTER payment_details";
                    $conn->query($alter_sql); // Don't check success, just try
                    
                    // Now try to update with message
                    $update_sql_full = "UPDATE bookings SET message = ? WHERE booking_id = ?";
                    $update_stmt_full = $conn->prepare($update_sql_full);
                    
                    if ($update_stmt_full) {
                        $update_stmt_full->bind_param("si", $message, $booking_id);
                        $update_stmt_full->execute();
                    }
                    
                    set_message('Booking approved successfully! Payment details saved for student.', 'success');
                } else {
                    set_message('Failed to approve booking: ' . $update_stmt_simple->error, 'error');
                }
            }
        } else {
            $update_stmt->bind_param("ssi", $payment_details, $message, $booking_id);
            
            if ($update_stmt->execute()) {
                set_message('Booking approved successfully! Payment details and message saved for student.', 'success');
            } else {
                set_message('Failed to approve booking: ' . $update_stmt->error, 'error');
            }
        }
    } elseif ($action == 'reject') {
        // Update booking status to rejected
        $update_sql = "UPDATE bookings SET status = 'rejected' WHERE booking_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if ($update_stmt === false) {
            set_message('Database error preparing rejection query: ' . $conn->error, 'error');
        } else {
            $update_stmt->bind_param("i", $booking_id);
            
            if ($update_stmt->execute()) {
                set_message('Booking rejected successfully.', 'success');
            } else {
                set_message('Failed to reject booking: ' . $update_stmt->error, 'error');
            }
        }
    }
    
    redirect($redirect_page);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Booking Details - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .nav-menu a:hover {
            opacity: 0.8;
        }

        /* Content Styles */
        .content-section {
            padding: 2rem 0;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d1f2eb;
            color: #0f5132;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Two Column Layout */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-section h3 {
            color: #667eea;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .info-item {
            display: flex;
            margin-bottom: 1rem;
        }

        .info-label {
            font-weight: bold;
            color: #333;
            min-width: 150px;
        }

        .info-value {
            color: #666;
        }

        /* Form Styles */
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
            border-color: #667eea;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Button Styles */
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .card-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="center_dashboard.php" class="logo"><?php echo SITE_NAME; ?></a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="center_dashboard.php">Dashboard</a></li>
                        <li><a href="edit_center_profile.php">Edit Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <?php echo display_message(); ?>

            <!-- Booking Details Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Student Booking Details</h2>
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>

                <div class="info-grid">
                    <!-- Student Information -->
                    <div class="info-section">
                        <h3><i class="fas fa-user"></i> Student Information</h3>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['student_email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['student_phone']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['student_address']); ?></span>
                        </div>
                    </div>

                    <!-- Booking Information -->
                    <div class="info-section">
                        <h3><i class="fas fa-calendar"></i> Booking Information</h3>
                        <div class="info-item">
                            <span class="info-label">Subject:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['subject_id']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date:</span>
                            <span class="info-value"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Time:</span>
                            <span class="info-value"><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Duration:</span>
                            <span class="info-value"><?php echo $booking['duration_hours']; ?> hour(s)</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Mode:</span>
                            <span class="info-value"><?php echo ucfirst($booking['mode']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Booked On:</span>
                            <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($booking['created_at'])); ?></span>
                        </div>
                        <?php if (!empty($booking['payment_details'])): ?>
                        <div class="info-item">
                            <span class="info-label">Payment Details:</span>
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($booking['payment_details'])); ?></span>
                        </div>
                        <?php else: ?>
                        <div class="info-item">
                            <span class="info-label">Payment Details:</span>
                            <span class="info-value" style="color: #666; font-style: italic;">Not provided yet</span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($booking['center_message'])): ?>
                        <div class="info-item">
                            <span class="info-label">Message:</span>
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($booking['center_message'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Approval Form (only show if status is pending) -->
                <?php if ($booking['status'] == 'pending'): ?>
                <form method="POST">
                    <div class="info-section">
                        <h3><i class="fas fa-check-circle"></i> Take Action</h3>
                        
                        <div class="form-group">
                            <label for="payment_details">Payment Details *</label>
                            <textarea name="payment_details" id="payment_details" class="form-control" 
                                      placeholder="Enter payment details such as:
- Bank account details
- UPI payment information
- Payment gateway links
- Fee structure
- Payment deadline
- Any other payment instructions" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="message">Message to Student</label>
                            <textarea name="message" id="message" class="form-control" 
                                      placeholder="Enter any message for the student (optional)"></textarea>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                <i class="fas fa-check"></i> Approve Booking
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                <i class="fas fa-times"></i> Reject Booking
                            </button>
                            <a href="center_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div class="action-buttons">
                    <a href="center_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>
