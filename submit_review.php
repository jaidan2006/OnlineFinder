<?php
require_once 'includes/config.php';

// Check if user is logged in as student
if (!is_student()) {
    set_message('Please login as a student to submit a review.', 'error');
    redirect('student_login.php');
}

// Get review parameters
$target_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$target_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : '';

if (empty($target_type) || empty($target_id) || !is_numeric($target_id)) {
    set_message('Invalid review request.', 'error');
    redirect('student_dashboard.php');
}

// Validate target type
if (!in_array($target_type, ['tutor', 'center'])) {
    set_message('Invalid review type.', 'error');
    redirect('student_dashboard.php');
}

// Get target details
if ($target_type == 'tutor') {
    $sql = "SELECT tutor_id, first_name, last_name, qualification FROM tutors WHERE tutor_id = ? AND approved = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        set_message('Tutor not found.', 'error');
        redirect('student_dashboard.php');
    }
    
    $target = $result->fetch_assoc();
    $target_name = $target['first_name'] . ' ' . $target['last_name'];
    $target_details = $target['qualification'];
    
} else { // center
    $sql = "SELECT center_id, center_name, description FROM coaching_centers WHERE center_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        set_message('Coaching center not found.', 'error');
        redirect('student_dashboard.php');
    }
    
    $target = $result->fetch_assoc();
    $target_name = $target['center_name'];
    $target_details = $target['description'];
}

// Check if student has already reviewed this target
$check_sql = "SELECT review_id FROM reviews WHERE student_id = ? AND {$target_type}_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $_SESSION['user_id'], $target_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    set_message('You have already submitted a review for this ' . $target_type . '.', 'error');
    redirect($target_type . '_profile.php?id=' . $target_id);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = sanitize_input($_POST['rating']);
    $review_text = sanitize_input($_POST['review_text']);
    
    // Validate rating
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        set_message('Please select a valid rating between 1 and 5.', 'error');
    } elseif (empty($review_text)) {
        set_message('Please enter your review text.', 'error');
    } elseif (strlen($review_text) < 10) {
        set_message('Review text must be at least 10 characters long.', 'error');
    } else {
        // Insert review
        $insert_sql = "INSERT INTO reviews (student_id, {$target_type}_id, rating, review_text, created_at) 
                       VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        
        if ($target_type == 'tutor') {
            $insert_stmt->bind_param("iiis", $_SESSION['user_id'], $target_id, $rating, $review_text);
        } else {
            $insert_stmt->bind_param("iiis", $_SESSION['user_id'], $target_id, $rating, $review_text);
        }
        
        if ($insert_stmt->execute()) {
            set_message('Review submitted successfully! Thank you for your feedback.', 'success');
            redirect($target_type . '_profile.php?id=' . $target_id);
        } else {
            set_message('Failed to submit review. Please try again.', 'error');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .review-section {
            padding: 2rem 0;
            background-color: #f8f9fa;
            min-height: calc(100vh - 200px);
        }

        .review-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2.5rem;
        }

        .target-info {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .target-info h3 {
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .target-info p {
            margin: 0;
            opacity: 0.9;
        }

        .rating-section {
            margin: 2rem 0;
            text-align: center;
        }

        .rating-section h4 {
            margin-bottom: 1rem;
            color: #333;
        }

        .star-rating {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .star {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }

        .star:hover,
        .star.active {
            color: #ffc107;
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
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-cancel {
            background: #6c757d;
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
            margin-right: 1rem;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .action-buttons {
            text-align: center;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        @media (max-width: 768px) {
            .review-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .star-rating {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Review Section -->
    <section class="review-section">
        <div class="container">
            <div class="review-container">
                <!-- Target Information -->
                <div class="target-info">
                    <h3>
                        <i class="fas fa-<?php echo $target_type == 'tutor' ? 'user-tie' : 'school'; ?>"></i>
                        <?php echo htmlspecialchars($target_name); ?>
                    </h3>
                    <p><?php echo htmlspecialchars(substr($target_details, 0, 100)) . '...'; ?></p>
                </div>

                <h2 style="text-align: center; color: #007bff; margin-bottom: 2rem;">
                    <i class="fas fa-star"></i> Submit Your Review
                </h2>

                <?php echo display_message(); ?>

                <form method="POST" action="">
                    <!-- Rating Section -->
                    <div class="rating-section">
                        <h4>How would you rate this <?php echo $target_type; ?>?</h4>
                        <div class="star-rating" id="starRating">
                            <span class="star" data-rating="1">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="5">★</span>
                        </div>
                        <small style="color: #666;">Click on the stars to rate</small>
                        <input type="hidden" name="rating" id="rating" value="0" required>
                    </div>

                    <!-- Review Text -->
                    <div class="form-group">
                        <label for="review_text">
                            <i class="fas fa-comment"></i> Your Review
                        </label>
                        <textarea name="review_text" id="review_text" class="form-control" 
                                  placeholder="Share your experience with this <?php echo $target_type; ?>..." required></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="<?php echo $target_type; ?>_profile.php?id=<?php echo $target_id; ?>" class="btn-cancel">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        // Star rating functionality
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('rating');
        let currentRating = 0;

        stars.forEach(star => {
            star.addEventListener('click', function() {
                currentRating = parseInt(this.dataset.rating);
                ratingInput.value = currentRating;
                updateStars();
            });

            star.addEventListener('mouseenter', function() {
                const hoverRating = parseInt(this.dataset.rating);
                highlightStars(hoverRating);
            });
        });

        document.getElementById('starRating').addEventListener('mouseleave', function() {
            updateStars();
        });

        function updateStars() {
            highlightStars(currentRating);
        }

        function highlightStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (ratingInput.value === '0') {
                e.preventDefault();
                alert('Please select a rating before submitting your review.');
                return false;
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
