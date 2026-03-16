<?php
require_once 'includes/config.php';

// Get tutor ID from URL
$tutor_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : '';

if (empty($tutor_id) || !is_numeric($tutor_id)) {
    set_message('Invalid tutor ID.', 'error');
    redirect('search.php');
}

// Get tutor details
$sql = "SELECT t.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count 
        FROM tutors t 
        LEFT JOIN reviews r ON t.tutor_id = r.tutor_id 
        WHERE t.tutor_id = ? AND t.approved = 'approved' 
        GROUP BY t.tutor_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_message('Tutor not found.', 'error');
    redirect('search.php');
}

$tutor = $result->fetch_assoc();

// Get tutor reviews
$reviews_sql = "SELECT r.*, s.first_name, s.last_name 
                FROM reviews r 
                JOIN students s ON r.student_id = s.student_id 
                WHERE r.tutor_id = ? 
                ORDER BY r.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $tutor_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews = [];

while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?> - Tutor Profile</title>
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

    <!-- Profile Section -->
    <section style="padding: 2rem 0; background-color: #f8f9fa;">
        <div class="container">
            <div class="profile-card">
                <div class="profile-header">
                    <?php if ($tutor['profile_image']): ?>
                        <img src="images/<?php echo htmlspecialchars($tutor['profile_image']); ?>" alt="Profile" class="profile-image">
                    <?php else: ?>
                        <div class="profile-image" style="background-color: white; color: #007bff; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                            <?php echo strtoupper(substr($tutor['first_name'], 0, 1) . substr($tutor['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <h2><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></h2>
                    <p><?php echo htmlspecialchars($tutor['qualification']); ?></p>
                    
                    <?php if ($tutor['avg_rating']): ?>
                        <div class="rating" style="margin-top: 1rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= round($tutor['avg_rating'])): ?>
                                    <span style="color: #ffc107; font-size: 1.5rem;">★</span>
                                <?php else: ?>
                                    <span style="color: #ddd; font-size: 1.5rem;">★</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span style="color: white; font-size: 1rem;">(<?php echo number_format($tutor['avg_rating'], 1); ?> out of 5, <?php echo $tutor['review_count']; ?> reviews)</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 style="color: #007bff; margin-bottom: 1.5rem;">About</h3>
                            <p style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($tutor['description'])); ?></p>
                            
                            <h3 style="color: #007bff; margin: 2rem 0 1.5rem;">Subjects Taught</h3>
                            <p><?php echo htmlspecialchars($tutor['subjects_taught']); ?></p>
                            
                            <h3 style="color: #007bff; margin: 2rem 0 1.5rem;">Teaching Mode</h3>
                            <p>
                                <span class="badge badge-<?php echo ($tutor['teaching_mode'] == 'online') ? 'info' : (($tutor['teaching_mode'] == 'offline') ? 'success' : 'primary'); ?>">
                                    <?php echo ucfirst($tutor['teaching_mode']); ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <h4 style="color: #007bff; margin-bottom: 1rem;">Contact Information</h4>
                                <div class="profile-info">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($tutor['email']); ?>
                                </div>
                                <div class="profile-info">
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($tutor['phone']); ?>
                                </div>
                                <div class="profile-info">
                                    <strong>Location:</strong> <?php echo htmlspecialchars($tutor['location']); ?>
                                </div>
                                <div class="profile-info">
                                    <strong>Experience:</strong> <?php echo $tutor['experience_years']; ?> years
                                </div>
                                <div class="profile-info">
                                    <strong>Hourly Rate:</strong> ₹<?php echo number_format($tutor['hourly_rate'], 2); ?>
                                </div>
                                <div class="profile-info">
                                    <strong>Status:</strong> 
                                    <span class="badge badge-<?php echo ($tutor['availability_status'] == 'available') ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($tutor['availability_status']); ?>
                                    </span>
                                </div>
                                
                                <?php if (is_student()): ?>
                                    <div style="margin-top: 1rem;">
                                        <a href="submit_review.php?type=tutor&id=<?php echo $tutor['tutor_id']; ?>" 
                                           class="btn btn-success btn-block" 
                                           style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); 
                                                  border: none; 
                                                  color: white; 
                                                  font-weight: 600; 
                                                  padding: 12px 20px; 
                                                  border-radius: 8px; 
                                                  text-decoration: none; 
                                                  display: block; 
                                                  text-align: center; 
                                                  transition: all 0.3s ease; 
                                                  box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);"
                                           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 193, 7, 0.4)'"
                                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.3)'">
                                            <i class="fas fa-star"></i> Write Review
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (is_tutor() && $_SESSION['user_id'] == $tutor['tutor_id']): ?>
                                    <div style="margin-top: 1rem;">
                                        <a href="edit_tutor_profile.php" class="btn btn-warning btn-block">
                                            <i class="fas fa-edit"></i> Edit Profile
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Replies Section -->
                    <div style="margin-top: 3rem;">
                        <div class="card">
                            <h3 style="color: #007bff; margin-bottom: 2rem;">Recent Booking Replies</h3>
                            
                            <?php
                            // Fetch recent bookings with payment details for this tutor
                            $bookings_sql = "SELECT b.*, s.first_name, s.last_name, s.email as student_email
                                            FROM bookings b 
                                            JOIN students s ON b.student_id = s.student_id 
                                            WHERE b.tutor_id = ? AND b.status = 'confirmed' AND (b.payment_details IS NOT NULL OR b.message IS NOT NULL)
                                            ORDER BY b.created_at DESC 
                                            LIMIT 5";
                            $bookings_stmt = $conn->prepare($bookings_sql);
                            $bookings_stmt->bind_param("i", $tutor['tutor_id']);
                            $bookings_stmt->execute();
                            $bookings_result = $bookings_stmt->get_result();
                            ?>
                            
                            <?php if ($bookings_result->num_rows > 0): ?>
                                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                                    <div class="card" style="margin-bottom: 1rem; border-left: 4px solid #28a745;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 1rem; background-color: #f8f9fa;">
                                            <div>
                                                <h5><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></h5>
                                                <small style="color: #666;"><?php echo htmlspecialchars($booking['student_email']); ?></small>
                                            </div>
                                            <div style="text-align: right;">
                                            </div>
                                        </div>
                                        <div style="padding: 0 1rem 1rem 1rem;">
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
                                        </div>
                                        
                                        <?php if (!empty($booking['payment_details'])): ?>
                                        <div style="background-color: #e8f5e8; padding: 1rem; border-radius: 5px; margin: 0 1rem 1rem 1rem;">
                                            <strong style="color: #155724;">
                                                <i class="fas fa-money-bill-wave"></i> Payment Details:
                                            </strong>
                                            <div style="margin-top: 0.5rem; white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($booking['payment_details'])); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($booking['message'])): ?>
                                        <div style="background-color: #e3f2fd; padding: 1rem; border-radius: 5px; margin: 0 1rem 1rem 1rem;">
                                            <strong style="color: #1565c0;">
                                                <i class="fas fa-comment-dots"></i> Message from Tutor:
                                            </strong>
                                            <div style="margin-top: 0.5rem; white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($booking['message'])); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No booking replies yet. Payment details and messages from tutors will appear here once students complete bookings and tutors provide their responses.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Reviews Section -->
                    <div style="margin-top: 3rem;">
                        <h3 style="color: #007bff; margin-bottom: 2rem;">Student Reviews</h3>
                        
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="card" style="margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <h5><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h5>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <span style="color: #ffc107;">★</span>
                                                <?php else: ?>
                                                    <span style="color: #ddd;">★</span>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                    <small style="color: #666;"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No reviews yet. Be the first to review this tutor!
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Back Button -->
                    <div style="margin-top: 2rem; text-align: center;">
                        <a href="search.php" class="btn btn-secondary">← Back to Search</a>
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
