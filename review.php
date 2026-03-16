<?php
require_once 'includes/config.php';

// Check if user is logged in as student
if (!is_student()) {
    set_message('Please login as a student to submit reviews.', 'error');
    redirect('student_login.php');
}

// Get review parameters
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$id = isset($_GET['id']) ? sanitize_input($_GET['id']) : '';
$booking_id = isset($_GET['booking_id']) ? sanitize_input($_GET['booking_id']) : '';

if (empty($type) || empty($id) || !in_array($type, ['tutor', 'center']) || !is_numeric($id)) {
    set_message('Invalid review parameters.', 'error');
    redirect('student_dashboard.php');
}

// Validate booking exists and belongs to the student
if ($booking_id) {
    if ($type == 'tutor') {
        $booking_sql = "SELECT b.*, t.first_name, t.last_name 
                       FROM bookings b 
                       JOIN tutors t ON b.tutor_id = t.tutor_id 
                       WHERE b.booking_id = ? AND b.student_id = ? AND b.tutor_id = ? AND b.status = 'completed'";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("iii", $booking_id, $_SESSION['user_id'], $id);
    } else {
        $booking_sql = "SELECT b.*, cc.center_name 
                       FROM bookings b 
                       JOIN coaching_centers cc ON b.center_id = cc.center_id 
                       WHERE b.booking_id = ? AND b.student_id = ? AND b.center_id = ? AND b.status = 'completed'";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("iii", $booking_id, $_SESSION['user_id'], $id);
    }
    
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    
    if ($booking_result->num_rows === 0) {
        set_message('Invalid booking or booking not completed.', 'error');
        redirect('student_dashboard.php');
    }
    
    $booking = $booking_result->fetch_assoc();
}

// Check if review already exists for this booking
if ($booking_id) {
    $review_check_sql = "SELECT review_id FROM reviews WHERE booking_id = ? AND student_id = ?";
    $review_check_stmt = $conn->prepare($review_check_sql);
    $review_check_stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $review_check_stmt->execute();
    $review_check_result = $review_check_stmt->get_result();
    
    if ($review_check_result->num_rows > 0) {
        set_message('You have already submitted a review for this booking.', 'error');
        redirect('student_dashboard.php');
    }
}

// Get provider details
if ($type == 'tutor') {
    $sql = "SELECT * FROM tutors WHERE tutor_id = ? AND approved = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        set_message('Tutor not found.', 'error');
        redirect('student_dashboard.php');
    }
    
    $provider = $result->fetch_assoc();
    $provider_name = $provider['first_name'] . ' ' . $provider['last_name'];
    $provider_type = 'Tutor';
} else {
    $sql = "SELECT * FROM coaching_centers WHERE center_id = ? AND approved = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        set_message('Coaching center not found.', 'error');
        redirect('student_dashboard.php');
    }
    
    $provider = $result->fetch_assoc();
    $provider_name = $provider['center_name'];
    $provider_type = 'Coaching Center';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = sanitize_input($_POST['rating']);
    $review_text = sanitize_input($_POST['review_text']);
    
    if ($rating < 1 || $rating > 5) {
        set_message('Invalid rating. Please select a rating between 1 and 5.', 'error');
    } else {
        // Insert review
        $sql = "INSERT INTO reviews (student_id, " . ($type == 'tutor' ? 'tutor_id' : 'center_id') . ", booking_id, rating, review_text) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($type == 'tutor') {
            $stmt->bind_param("iiiii", $_SESSION['user_id'], $id, $booking_id, $rating, $review_text);
        } else {
            $stmt->bind_param("iiiii", $_SESSION['user_id'], $id, $booking_id, $rating, $review_text);
        }
        
        if ($stmt->execute()) {
            set_message('Review submitted successfully! Thank you for your feedback.', 'success');
            redirect('student_dashboard.php');
        } else {
            set_message('Review submission failed. Please try again.', 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Review - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
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
                    <li><a href="student_dashboard.php">My Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Review Section -->
    <section style="padding: 2rem 0; background-color: #f8f9fa;">
        <div class="container">
            <div class="row">
                <div class="col-md-8" style="margin: 0 auto;">
                    <div class="form-container">
                        <h2 class="text-center" style="color: #007bff; margin-bottom: 2rem;">Submit Review for <?php echo htmlspecialchars($provider_name); ?></h2>
                        
                        <?php if ($booking_id): ?>
                            <div class="alert alert-info">
                                <strong>Booking Details:</strong><br>
                                Booking ID: #<?php echo $booking_id; ?><br>
                                Date: <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?><br>
                                Time: <?php echo date('g:i A', strtotime($booking['booking_time'])); ?><br>
                                Subject: <?php 
                                    $subject_sql = "SELECT subject_name FROM subjects WHERE subject_id = ?";
                                    $subject_stmt = $conn->prepare($subject_sql);
                                    $subject_stmt->bind_param("i", $booking['subject_id']);
                                    $subject_stmt->execute();
                                    $subject_result = $subject_stmt->get_result();
                                    if ($subject_result->num_rows > 0) {
                                        $subject_row = $subject_result->fetch_assoc();
                                        echo htmlspecialchars($subject_row['subject_name']);
                                    }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php echo display_message(); ?>
                        
                        <form method="POST" onsubmit="return validateReview()">
                            <div class="form-group">
                                <label>Rating *</label>
                                <div class="rating-container" style="font-size: 2rem; margin-bottom: 1rem;">
                                    <input type="hidden" name="rating" id="rating_value" value="0">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="rating-star" data-rating="<?php echo $i; ?>" style="cursor: pointer; color: #ddd;">☆</span>
                                    <?php endfor; ?>
                                </div>
                                <small>Click on the stars to rate (1 = Poor, 5 = Excellent)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="review_text">Your Review *</label>
                                <textarea name="review_text" id="review_text" class="form-control" rows="6" placeholder="Share your experience with this <?php echo strtolower($provider_type); ?>. Tell us about the teaching quality, communication, punctuality, and overall experience..." required></textarea>
                                <small>Minimum 10 characters required</small>
                            </div>
                            
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                                <a href="student_dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                            
                            <div class="alert alert-warning">
                                <strong>Review Guidelines:</strong><br>
                                • Be honest and specific in your review<br>
                                • Focus on the teaching quality and experience<br>
                                • Avoid using offensive language<br>
                                • Your review will help other students make informed decisions<br>
                                • False or misleading reviews may be removed
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
