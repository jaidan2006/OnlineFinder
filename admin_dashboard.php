<?php
require_once 'includes/config.php';

// Check if user is admin
if (!is_admin()) {
    set_message('Access denied. Admin login required.', 'error');
    redirect('admin_login.php');
}

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = sanitize_input($_POST['action']);
    $user_type = sanitize_input($_POST['user_type']);
    $user_id = sanitize_input($_POST['user_id']);
    
    if ($action == 'approve' || $action == 'reject') {
        if ($user_type == 'tutor') {
            $sql = "UPDATE tutors SET approved = ? WHERE tutor_id = ?";
            $stmt = $conn->prepare($sql);
            $status = ($action == 'approve') ? 'approved' : 'rejected';
            $stmt->bind_param("si", $status, $user_id);
        } elseif ($user_type == 'center') {
            $sql = "UPDATE coaching_centers SET approved = ? WHERE center_id = ?";
            $stmt = $conn->prepare($sql);
            $status = ($action == 'approve') ? 'approved' : 'rejected';
            $stmt->bind_param("si", $status, $user_id);
        }
        
        if ($stmt->execute()) {
            set_message(ucfirst($user_type) . ' ' . $action . 'd successfully.', 'success');
        } else {
            set_message('Action failed. Please try again.', 'error');
        }
    } elseif ($action == 'delete') {
        if ($user_type == 'student') {
            $sql = "DELETE FROM students WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
        } elseif ($user_type == 'tutor') {
            $sql = "DELETE FROM tutors WHERE tutor_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
        } elseif ($user_type == 'center') {
            $sql = "DELETE FROM coaching_centers WHERE center_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
        }
        
        if ($stmt->execute()) {
            set_message(ucfirst($user_type) . ' deleted successfully.', 'success');
        } else {
            set_message('Deletion failed. Please try again.', 'error');
        }
    }
    
    redirect('admin_dashboard.php');
}

// Get statistics
$stats = [];
$stats['students'] = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$stats['tutors'] = $conn->query("SELECT COUNT(*) as count FROM tutors")->fetch_assoc()['count'];
$stats['centers'] = $conn->query("SELECT COUNT(*) as count FROM coaching_centers")->fetch_assoc()['count'];
$stats['bookings'] = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$stats['pending_tutors'] = $conn->query("SELECT COUNT(*) as count FROM tutors WHERE approved = 'pending'")->fetch_assoc()['count'];
$stats['pending_centers'] = $conn->query("SELECT COUNT(*) as count FROM coaching_centers WHERE approved = 'pending'")->fetch_assoc()['count'];

// Get pending tutors
$pending_tutors = [];
$result = $conn->query("SELECT * FROM tutors WHERE approved = 'pending' ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $pending_tutors[] = $row;
}

// Get pending centers
$pending_centers = [];
$result = $conn->query("SELECT * FROM coaching_centers WHERE approved = 'pending' ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $pending_centers[] = $row;
}

// Get recent bookings
$recent_bookings = [];
$result = $conn->query("SELECT b.*, s.first_name as student_name, s.last_name as student_last_name,
                        CASE 
                            WHEN b.tutor_id IS NOT NULL THEN (SELECT CONCAT(t.first_name, ' ', t.last_name) FROM tutors t WHERE t.tutor_id = b.tutor_id)
                            WHEN b.center_id IS NOT NULL THEN (SELECT cc.center_name FROM coaching_centers cc WHERE cc.center_id = b.center_id)
                        END as provider_name
                        FROM bookings b 
                        JOIN students s ON b.student_id = s.student_id 
                        ORDER BY b.created_at DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $recent_bookings[] = $row;
}

// Get recent reviews
$recent_reviews = [];
$result = $conn->query("SELECT r.*, s.first_name as student_name, s.last_name as student_last_name,
                        CASE 
                            WHEN r.tutor_id IS NOT NULL THEN (SELECT CONCAT(t.first_name, ' ', t.last_name) FROM tutors t WHERE t.tutor_id = r.tutor_id)
                            WHEN r.center_id IS NOT NULL THEN (SELECT cc.center_name FROM coaching_centers cc WHERE cc.center_id = r.center_id)
                        END as provider_name
                        FROM reviews r 
                        JOIN students s ON r.student_id = s.student_id 
                        ORDER BY r.created_at DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $recent_reviews[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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

        .btn-admin {
            background-color: #6f42c1;
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

        .btn-admin:hover {
            background-color: #5a32a3;
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

        .col-md-3 {
            flex: 0 0 25%;
            max-width: 25%;
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

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,193,7,0.3);
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

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        /* Text Utilities */
        .text-center {
            text-align: center !important;
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
            
            .col-md-3, .col-md-4, .col-md-6 {
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
                        <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="admin_dashboard.php"><i class="fas fa-cog"></i> Admin Panel</a></li>
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
            <h2 style="color: #007bff; margin-bottom: 2rem;">Admin Dashboard</h2>
            
            <?php echo display_message(); ?>
            
            <!-- Statistics -->
            <div class="row" style="margin-bottom: 3rem;">
                <div class="col-md-3">
                    <div class="card text-center">
                        <h3 style="color: #007bff;"><?php echo $stats['students']; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <h3 style="color: #007bff;"><?php echo $stats['tutors']; ?></h3>
                        <p>Total Tutors</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <h3 style="color: #007bff;"><?php echo $stats['centers']; ?></h3>
                        <p>Coaching Centers</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <h3 style="color: #007bff;"><?php echo $stats['bookings']; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="row" style="margin-bottom: 3rem;">
                <div class="col-md-6">
                    <div class="card">
                        <h4 style="color: #007bff; margin-bottom: 1rem;">Pending Tutor Approvals (<?php echo $stats['pending_tutors']; ?>)</h4>
                        <?php if (!empty($pending_tutors)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Subjects</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_tutors as $tutor): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($tutor['email']); ?></td>
                                                <td><?php echo htmlspecialchars($tutor['subjects_taught']); ?></td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="user_type" value="tutor">
                                                        <input type="hidden" name="user_id" value="<?php echo $tutor['tutor_id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="user_type" value="tutor">
                                                        <input type="hidden" name="user_id" value="<?php echo $tutor['tutor_id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this tutor?')">Reject</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No pending tutor approvals.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <h4 style="color: #007bff; margin-bottom: 1rem;">Pending Center Approvals (<?php echo $stats['pending_centers']; ?>)</h4>
                        <?php if (!empty($pending_centers)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Center Name</th>
                                            <th>Email</th>
                                            <th>Location</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_centers as $center): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($center['center_name']); ?></td>
                                                <td><?php echo htmlspecialchars($center['email']); ?></td>
                                                <td><?php echo htmlspecialchars($center['location']); ?></td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="user_type" value="center">
                                                        <input type="hidden" name="user_id" value="<?php echo $center['center_id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="user_type" value="center">
                                                        <input type="hidden" name="user_id" value="<?php echo $center['center_id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this center?')">Reject</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No pending center approvals.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <h4 style="color: #007bff; margin-bottom: 1rem;">Recent Bookings</h4>
                        <?php if (!empty($recent_bookings)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Provider</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['student_name'] . ' ' . $booking['student_last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['provider_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo ($booking['status'] == 'completed') ? 'success' : (($booking['status'] == 'pending') ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No recent bookings.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <h4 style="color: #007bff; margin-bottom: 1rem;">Recent Reviews</h4>
                        <?php if (!empty($recent_reviews)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Provider</th>
                                            <th>Rating</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_reviews as $review): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($review['student_name'] . ' ' . $review['student_last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($review['provider_name']); ?></td>
                                                <td>
                                                    <div class="rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= $review['rating']): ?>
                                                                <span style="color: #ffc107;">★</span>
                                                            <?php else: ?>
                                                                <span style="color: #ddd;">★</span>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No recent reviews.</p>
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
