<?php
require_once 'includes/config.php';

// Check if user is logged in as student
if (!is_student()) {
    set_message('Please login as a student.', 'error');
    redirect('login.php'); // Updated to use login.php instead of student_login.php
}

// Get student's bookings
$bookings_sql = "SELECT b.*, s.subject_name,
                CASE 
                    WHEN b.tutor_id IS NOT NULL THEN (SELECT CONCAT(t.first_name, ' ', t.last_name) FROM tutors t WHERE t.tutor_id = b.tutor_id)
                    WHEN b.center_id IS NOT NULL THEN (SELECT cc.center_name FROM coaching_centers cc WHERE cc.center_id = b.center_id)
                END as provider_name,
                CASE 
                    WHEN b.tutor_id IS NOT NULL THEN 'tutor'
                    WHEN b.center_id IS NOT NULL THEN 'center'
                END as provider_type
                FROM bookings b 
                JOIN subjects s ON b.subject_id = s.subject_id 
                WHERE b.student_id = ? 
                ORDER BY b.created_at DESC";
$stmt = $conn->prepare($bookings_sql);

if ($stmt === false) {
    die("Error preparing bookings query: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bookings_result = $stmt->get_result();
$bookings = [];

while ($row = $bookings_result->fetch_assoc()) {
    $bookings[] = $row;
}

$stmt->close();

// Get student's reviews
$reviews_sql = "SELECT r.*, b.subject_id, s.subject_name,
                CASE 
                    WHEN r.tutor_id IS NOT NULL THEN (SELECT CONCAT(t.first_name, ' ', t.last_name) FROM tutors t WHERE t.tutor_id = r.tutor_id)
                    WHEN r.center_id IS NOT NULL THEN (SELECT cc.center_name FROM coaching_centers cc WHERE cc.center_id = r.center_id)
                END as provider_name,
                CASE 
                    WHEN r.tutor_id IS NOT NULL THEN 'tutor'
                    WHEN r.center_id IS NOT NULL THEN 'center'
                END as provider_type
                FROM reviews r 
                LEFT JOIN bookings b ON r.booking_id = b.booking_id
                LEFT JOIN subjects s ON b.subject_id = s.subject_id 
                WHERE r.student_id = ? 
                ORDER BY r.created_at DESC";
$stmt = $conn->prepare($reviews_sql);

if ($stmt === false) {
    die("Error preparing reviews query: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];

while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo SITE_NAME; ?></title>
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

        .btn-dashboard {
            background-color: #17a2b8;
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

        .btn-dashboard:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        /* Dashboard Content Styles */
        .dashboard-section {
            padding: 2rem 0;
            background-color: #f8f9fa;
        }

        .welcome-title {
            color: #007bff;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: bold;
        }

        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .card h3 {
            color: #007bff;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .card h4 {
            color: #007bff;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .card h5 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        /* Grid System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-md-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding-right: 15px;
            padding-left: 15px;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            min-width: 120px;
            justify-content: center;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            min-width: auto;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-bottom: 0;
        }

        .table th {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #0056b3;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        /* Rating Stars */
        .rating {
            font-size: 1.2rem;
            letter-spacing: 2px;
        }

        .rating span {
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        /* Text Utilities */
        .text-muted {
            color: #6c757d !important;
            font-size: 0.875rem;
        }

        .text-center {
            text-align: center !important;
        }

        /* Quick Actions Cards */
        .quick-actions-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .quick-action-card {
            flex: 1;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            border: 1px solid #e9ecef;
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 250px;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .quick-action-card .card-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .quick-action-card h4 {
            color: #007bff;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .quick-action-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .quick-action-card .btn {
            margin-top: auto;
        }

        /* Review Cards */
        .review-card {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }

        .review-card h5 {
            color: #007bff;
            margin-bottom: 0.5rem;
        }

        .review-card p {
            margin-bottom: 1rem;
            font-style: italic;
        }

        .review-card small {
            color: #666;
            font-size: 0.875rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #666;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .empty-state a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-info {
                justify-content: center;
                gap: 1rem;
                font-size: 0.8rem;
            }
            
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
            
            .welcome-title {
                font-size: 1.5rem;
                text-align: center;
            }
            
            .quick-actions-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .quick-action-card {
                min-height: auto;
                padding: 1.5rem;
            }
            
            .col-md-4, .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .table th, .table td {
                padding: 0.75rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .auth-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .dashboard-section {
                padding: 1rem 0;
            }
            
            .card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
            
            .table th, .table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.75rem;
            }
            
            .rating {
                font-size: 1rem;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-info {
                justify-content: center;
                gap: 1rem;
                font-size: 0.8rem;
            }
            
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

        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .auth-buttons {
                flex-direction: column;
                align-items: center;
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

    <!-- Dashboard Section -->
    <section style="padding: 2rem 0; background-color: #f8f9fa;">
        <div class="container">
            <h2 style="color: #007bff; margin-bottom: 2rem;">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h2>
            
            <?php echo display_message(); ?>
            
            <!-- Quick Actions -->
            <div class="quick-actions-row">
                <div class="quick-action-card">
                    <div class="card-content">
                        <h4>Find Tutors</h4>
                        <p>Search for qualified tutors and coaching centers</p>
                    </div>
                    <a href="search.php" class="btn btn-primary">Search Now</a>
                </div>
                <div class="quick-action-card">
                    <div class="card-content">
                        <h4>My Bookings</h4>
                        <p>View and manage your booking history</p>
                    </div>
                    <a href="#bookings" class="btn btn-primary">View Bookings</a>
                </div>
                <div class="quick-action-card">
                    <div class="card-content">
                        <h4>My Reviews</h4>
                        <p>See your submitted reviews and ratings</p>
                    </div>
                    <a href="#reviews" class="btn btn-primary">View Reviews</a>
                </div>
            </div>

            <!-- Bookings Section -->
            <div id="bookings" class="row" style="margin-bottom: 3rem;">
                <div class="col-md-12">
                    <div class="card">
                        <h3 style="color: #007bff; margin-bottom: 1.5rem;">My Bookings</h3>
                        <?php if (!empty($bookings)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Provider</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                            <th>Mode</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['provider_name']); ?>
                                                    <br><small class="text-muted"><?php echo ucfirst($booking['provider_type']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($booking['subject_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></td>
                                                <td><?php echo $booking['duration_hours']; ?> hour(s)</td>
                                                <td><?php echo ucfirst($booking['mode']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo ($booking['status'] == 'completed') ? 'success' : (($booking['status'] == 'confirmed') ? 'info' : 'warning'); ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($booking['status'] == 'completed'): ?>
                                                        <a href="review.php?type=<?php echo $booking['provider_type']; ?>&id=<?php echo ($booking['provider_type'] == 'tutor') ? $booking['tutor_id'] : $booking['center_id']; ?>&booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-success btn-sm">Review</a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo ($booking['provider_type'] == 'tutor') ? 'tutor_profile.php?id=' . $booking['tutor_id'] : 'center_profile.php?id=' . $booking['center_id']; ?>" class="btn btn-primary btn-sm">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>You haven't made any bookings yet. <a href="search.php">Search for tutors</a> to get started!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div id="reviews" class="row">
                <div class="col-md-12">
                    <div class="card">
                        <h3 style="color: #007bff; margin-bottom: 1.5rem;">My Reviews</h3>
                        <?php if (!empty($reviews)): ?>
                            <div class="row">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="col-md-6">
                                        <div class="card" style="margin-bottom: 1rem;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                                <h5><?php echo htmlspecialchars($review['provider_name']); ?></h5>
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
                                            <small style="color: #666;">
                                                Subject: <?php echo htmlspecialchars($review['subject_name']); ?> | 
                                                <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>You haven't submitted any reviews yet. Reviews will appear here after you complete bookings and submit feedback.</p>
                        <?php endif; ?>
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
