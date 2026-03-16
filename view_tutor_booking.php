<?php
require_once 'includes/config.php';

// Debug: Check current session
error_log("Session data in view_tutor_booking.php: " . print_r($_SESSION, true));
error_log("User type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'not set'));
error_log("is_tutor(): " . (is_tutor() ? 'true' : 'false'));

// Check if user is logged in as tutor
if (!is_tutor()) {
    $error_msg = 'Access denied. You must be logged in as a tutor to access this page.';
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
    redirect('tutor_dashboard.php');
}

// Get tutor ID from session
$tutor_id = $_SESSION['user_id'];

// Fetch booking details with student information
$sql = "SELECT b.*, s.first_name, s.last_name, s.email as student_email, s.phone as student_phone, 
        s.address as student_address
        FROM bookings b 
        JOIN students s ON b.student_id = s.student_id 
        WHERE b.booking_id = ? AND b.tutor_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $error_msg = "Database error preparing booking query: " . $conn->error;
    error_log($error_msg);
    set_message($error_msg, 'error');
    redirect('tutor_dashboard.php');
}

$bind_result = $stmt->bind_param("ii", $booking_id, $tutor_id);
if ($bind_result === false) {
    $error_msg = "Database bind error: " . $stmt->error;
    error_log($error_msg);
    set_message($error_msg, 'error');
    redirect('tutor_dashboard.php');
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_message('Booking not found or you do not have permission to view it.', 'error');
    redirect('tutor_dashboard.php');
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
    
    redirect('tutor_dashboard.php');
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
            background-color: #ffc107;
            color: #856404;
        }

        .status-confirmed {
            background-color: #28a745;
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-section h4 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .info-item {
            margin-bottom: 0.8rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            display: inline-block;
            min-width: 120px;
        }

        .info-value {
            color: #333;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
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

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
                        <li><a href="tutor_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
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

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Student Booking Details</h1>
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>

                <?php if ($booking['status'] == 'pending'): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Please review this booking request and decide whether to approve or reject it.
                    </div>
                <?php endif; ?>

                <div class="info-grid">
                    <div class="info-section">
                        <h4><i class="fas fa-user"></i> Student Information</h4>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['student_email']); ?></span>
                        </div>
                        <?php if ($booking['student_phone']): ?>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['student_phone']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($booking['student_address']): ?>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($booking['student_address'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="info-section">
                        <h4><i class="fas fa-calendar"></i> Booking Details</h4>
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
                            <span class="info-label">Requested:</span>
                            <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($booking['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($booking['message']): ?>
                <div class="info-section">
                    <h4><i class="fas fa-comment"></i> Student Message</h4>
                    <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #667eea;">
                        <?php echo nl2br(htmlspecialchars($booking['message'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($booking['status'] == 'pending'): ?>
                <form method="POST">
                    <div class="info-section">
                        <h4><i class="fas fa-reply"></i> Your Response</h4>
                        
                        <div class="form-group">
                            <label for="payment_details">Payment Details</label>
                            <textarea name="payment_details" id="payment_details" class="form-control" rows="6" 
                                      placeholder="Enter your payment details such as:
- Hourly rate or total fee
- Bank account details
- UPI payment information
- Payment gateway links
- Payment schedule
- Any other payment instructions"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message to Student</label>
                            <textarea name="message" id="message" class="form-control" rows="4" 
                                      placeholder="Enter any additional message for the student such as:
- Confirmation of session details
- Preparation instructions
- Materials needed
- Location details (for offline sessions)
- Meeting link (for online sessions)
- Any other relevant information"></textarea>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" name="action" value="approve" class="btn btn-approve">
                            <i class="fas fa-check"></i> Approve Booking
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-reject">
                            <i class="fas fa-times"></i> Reject Booking
                        </button>
                        <a href="tutor_dashboard.php" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
                <?php else: ?>
                <div class="action-buttons">
                    <a href="tutor_dashboard.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>
