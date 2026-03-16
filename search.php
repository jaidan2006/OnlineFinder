<?php
require_once 'includes/config.php';

// Get search parameters
$subject_id = isset($_GET['subject']) ? sanitize_input($_GET['subject']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
$mode = isset($_GET['mode']) ? sanitize_input($_GET['mode']) : '';

// Use subject directly as text input (no ID conversion needed)
$subject_name = $subject_id;

// Only execute search if there are search parameters
$has_search_params = !empty($subject_name) || !empty($location) || !empty($mode);

if ($has_search_params) {
    // Build WHERE conditions for tutors
    $tutor_where_conditions = [];
    $tutor_params = [];
    $tutor_types = '';
    
    // Build WHERE conditions for coaching centers
    $center_where_conditions = [];
    $center_params = [];
    $center_types = '';
    
    // Subject conditions
    if (!empty($subject_name)) {
        $tutor_where_conditions[] = "t.subjects_taught LIKE ?";
        $tutor_params[] = "%$subject_name%";
        $tutor_types .= 's';
        
        $center_where_conditions[] = "cc.courses_offered LIKE ?";
        $center_params[] = "%$subject_name%";
        $center_types .= 's';
    }
    
    // Location conditions
    if (!empty($location)) {
        $tutor_where_conditions[] = "t.location LIKE ?";
        $tutor_params[] = "%$location%";
        $tutor_types .= 's';
        
        $center_where_conditions[] = "cc.location LIKE ?";
        $center_params[] = "%$location%";
        $center_types .= 's';
    }
    
    // Mode conditions
    if (!empty($mode)) {
        $tutor_where_conditions[] = "(LOWER(t.teaching_mode) = LOWER(?) OR LOWER(t.teaching_mode) = 'both')";
        $tutor_params[] = $mode;
        $tutor_types .= 's';
        
        $center_where_conditions[] = "(LOWER(cc.teaching_mode) = LOWER(?) OR LOWER(cc.teaching_mode) = 'both')";
        $center_params[] = $mode;
        $center_types .= 's';
    }
    
    // Build WHERE clauses
    $tutor_where_clause = !empty($tutor_where_conditions) ? 'WHERE ' . implode(' AND ', $tutor_where_conditions) . ' AND t.approved = "approved" AND t.availability_status = "available"' : 'WHERE t.approved = "approved" AND t.availability_status = "available"';
    $center_where_clause = !empty($center_where_conditions) ? 'WHERE ' . implode(' AND ', $center_where_conditions) . ' AND cc.approved = "approved" AND cc.availability_status = "available"' : 'WHERE cc.approved = "approved" AND cc.availability_status = "available"';
    
} else {
    $tutor_where_clause = '';
    $center_where_clause = '';
    $tutor_params = [];
    $center_params = [];
    $tutor_types = '';
    $center_types = '';
}

// Search tutors
$tutor_sql = "SELECT t.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count 
               FROM tutors t 
               LEFT JOIN reviews r ON t.tutor_id = r.tutor_id 
               $tutor_where_clause
               GROUP BY t.tutor_id 
               ORDER BY avg_rating DESC, t.experience_years DESC";

// Search coaching centers
$center_sql = "SELECT cc.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count 
               FROM coaching_centers cc 
               LEFT JOIN reviews r ON cc.center_id = r.center_id 
               $center_where_clause
               GROUP BY cc.center_id 
               ORDER BY avg_rating DESC, cc.center_name";

$tutors = [];
$centers = [];

// Debug: Check parameters
echo "<!-- DEBUG: Parameters -->";
echo "<!-- Subject ID: " . var_export($subject_id, true) . " -->";
echo "<!-- Subject Name: " . var_export($subject_name, true) . " -->";
echo "<!-- Location: " . var_export($location, true) . " -->";
echo "<!-- Mode: " . var_export($mode, true) . " -->";
echo "<!-- Has Search Params: " . var_export($has_search_params, true) . " -->";
echo "<!-- Tutor WHERE clause: " . var_export($tutor_where_clause, true) . " -->";
echo "<!-- Center WHERE clause: " . var_export($center_where_clause, true) . " -->";
echo "<!-- Tutor Params: " . var_export($tutor_params, true) . " -->";
echo "<!-- Center Params: " . var_export($center_params, true) . " -->";
echo "<!-- Tutor Types: " . var_export($tutor_types, true) . " -->";
echo "<!-- Center Types: " . var_export($center_types, true) . " -->";

// Only execute search queries if there are search parameters
if ($has_search_params) {
    // Prepare and execute tutor query
    $tutor_stmt = $conn->prepare($tutor_sql);
    if ($tutor_stmt) {
        if (!empty($tutor_params)) {
            $tutor_stmt->bind_param($tutor_types, ...$tutor_params);
        }
        echo "<!-- DEBUG: Full Tutor SQL: " . htmlspecialchars($tutor_sql) . " -->";
        $tutor_stmt->execute();
        $tutor_result = $tutor_stmt->get_result();
        echo "<!-- DEBUG: Tutor query executed, results: " . $tutor_result->num_rows . " -->";
        while ($row = $tutor_result->fetch_assoc()) {
            $tutors[] = $row;
        }
    } else {
        echo "<!-- DEBUG: Tutor prepare failed: " . $conn->error . " -->";
    }

    // Prepare and execute center query
    $center_stmt = $conn->prepare($center_sql);
    if ($center_stmt) {
        if (!empty($center_params)) {
            $center_stmt->bind_param($center_types, ...$center_params);
        }
        echo "<!-- DEBUG: Full Center SQL: " . htmlspecialchars($center_sql) . " -->";
        $center_stmt->execute();
        $center_result = $center_stmt->get_result();
        echo "<!-- DEBUG: Center query executed, results: " . $center_result->num_rows . " -->";
        while ($row = $center_result->fetch_assoc()) {
            $centers[] = $row;
        }
    } else {
        echo "<!-- DEBUG: Center prepare failed: " . $conn->error . " -->";
    }
} else {
    echo "<!-- DEBUG: No search parameters provided, skipping queries -->";
}

echo "<!-- DEBUG: Total tutors: " . count($tutors) . " -->";
echo "<!-- DEBUG: Total centers: " . count($centers) . " -->";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Tutors - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .btn-login {
            background-color: transparent;
            color: white;
            border: 2px solid white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: white;
            color: #343a40;
        }

        .btn-register {
            background-color: #28a745;
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

        .btn-register:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        /* Footer Styles - Override CSS to match index.php */
        .footer {
            background-color: #343a40 !important;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
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
                        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Search Section -->
    <section style="padding: 2rem 0; background-color: #f8f9fa;">
        <div class="container">
            <div class="search-section">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #007bff;">Search Tutors & Coaching Centers</h2>
                <form action="search.php" method="GET" class="search-form">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" value="<?php echo htmlspecialchars($subject_id); ?>" placeholder="Enter subject (e.g., Mathematics, Chemistry, Physics)">
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" name="location" id="location" class="form-control" value="<?php echo htmlspecialchars($location); ?>" placeholder="Enter location">
                    </div>
                    <div class="form-group">
                        <label for="mode">Teaching Mode</label>
                        <select name="mode" id="mode" class="form-control">
                            <option value="">All Modes</option>
                            <option value="online" <?php echo ($mode == 'online') ? 'selected' : ''; ?>>Online</option>
                            <option value="offline" <?php echo ($mode == 'offline') ? 'selected' : ''; ?>>Offline</option>
                            <option value="both" <?php echo ($mode == 'both') ? 'selected' : ''; ?>>Both</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="search.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <?php if ($has_search_params): ?>
                <h3 style="margin-bottom: 2rem; color: #007bff;">
                    Search Results 
                    <?php if (!empty($subject_name)): ?>
                        for "<?php echo htmlspecialchars($subject_name); ?>"
                    <?php endif; ?>
                    <?php if (!empty($location)): ?> in "<?php echo htmlspecialchars($location); ?>"<?php endif; ?>
                    <?php if (!empty($mode)): ?> (<?php echo ucfirst($mode); ?>)<?php endif; ?>
                </h3>

                <!-- Tutors Results -->
                <?php 
                echo "<!-- DEBUG: Checking tutors array -->";
                echo "<!-- DEBUG: Tutors count: " . count($tutors) . " -->";
                ?>
                <?php if (!empty($tutors)): ?>
                    <h4 style="color: #007bff; margin-bottom: 1.5rem;">Tutors (<?php echo count($tutors); ?> found)</h4>
                    <div class="row">
                        <?php foreach ($tutors as $tutor): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></h5>
                                        <p><?php echo htmlspecialchars($tutor['qualification']); ?></p>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Subjects:</strong> <?php echo htmlspecialchars($tutor['subjects_taught']); ?></p>
                                        <p><strong>Experience:</strong> <?php echo $tutor['experience_years']; ?> years</p>
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($tutor['location']); ?></p>
                                        <p><strong>Mode:</strong> <?php echo ucfirst($tutor['teaching_mode']); ?></p>
                                        <p><strong>Rate:</strong> ₹<?php echo number_format($tutor['hourly_rate'], 2); ?>/hour</p>
                                        
                                        <?php if ($tutor['avg_rating']): ?>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= round($tutor['avg_rating'])): ?>
                                                        <span style="color: #ffc107;">★</span>
                                                    <?php else: ?>
                                                        <span style="color: #ddd;">★</span>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                <span style="color: #666; font-size: 0.9rem;">(<?php echo number_format($tutor['avg_rating'], 1); ?>, <?php echo $tutor['review_count']; ?> reviews)</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <p style="margin-top: 1rem;"><?php echo substr(htmlspecialchars($tutor['description']), 0, 100) . '...'; ?></p>
                                        
                                        <div style="margin-top: 1rem;">
                                            <a href="tutor_profile.php?id=<?php echo $tutor['tutor_id']; ?>" class="btn btn-primary">View Profile</a>
                                            <?php if (is_student()): ?>
                                                <a href="booking.php?type=tutor&id=<?php echo $tutor['tutor_id']; ?>" class="btn btn-success">Book Now</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php 
                    echo "<!-- DEBUG: No tutors found -->";
                    ?>
                    <h4 style="color: #007bff; margin-bottom: 1.5rem;">No tutors found</h4>
                <?php endif; ?>

                <!-- Coaching Centers Results -->
                <?php if (!empty($centers)): ?>
                    <h4 style="color: #007bff; margin: 2rem 0 1.5rem; margin-top: 3rem;">Coaching Centers (<?php echo count($centers); ?> found)</h4>
                    <div class="row">
                        <?php foreach ($centers as $center): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><?php echo htmlspecialchars($center['center_name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Courses:</strong> <?php echo htmlspecialchars($center['courses_offered']); ?></p>
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($center['location']); ?></p>
                                        <p><strong>Mode:</strong> <?php echo ucfirst($center['teaching_mode']); ?></p>
                                        <p><strong>Address:</strong> <?php echo htmlspecialchars($center['address']); ?></p>
                                        
                                        <?php if ($center['website']): ?>
                                            <p><strong>Website:</strong> <a href="<?php echo htmlspecialchars($center['website']); ?>" target="_blank" style="color: #007bff;"><?php echo htmlspecialchars($center['website']); ?></a></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($center['avg_rating']): ?>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= round($center['avg_rating'])): ?>
                                                        <span style="color: #ffc107;">★</span>
                                                    <?php else: ?>
                                                        <span style="color: #ddd;">★</span>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                <span style="color: #666; font-size: 0.9rem;">(<?php echo number_format($center['avg_rating'], 1); ?>, <?php echo $center['review_count']; ?> reviews)</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <p style="margin-top: 1rem;"><?php echo substr(htmlspecialchars($center['description']), 0, 100) . '...'; ?></p>
                                        
                                        <div style="margin-top: 1rem;">
                                            <a href="center_profile.php?id=<?php echo $center['center_id']; ?>" class="btn btn-primary">View Profile</a>
                                            <?php if (is_student()): ?>
                                                <a href="booking.php?type=center&id=<?php echo $center['center_id']; ?>" class="btn btn-success">Book Now</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- No Results Message -->
                <?php if (empty($tutors) && empty($centers)): ?>
                    <div class="alert alert-info text-center">
                        <h4>No tutors or coaching centers found matching your criteria.</h4>
                        <p>Try adjusting your search filters or browse all available tutors.</p>
                        <a href="search.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Show search form message when no search parameters -->
                <div class="alert alert-info text-center">
                    <h4>Search for tutors and coaching centers</h4>
                    <p>Use the search form above to find tutors and coaching centers based on subject, location, and teaching mode.</p>
                    <p>Select at least one search criteria to see results.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p>
                <a href="index.php" style="color: white; text-decoration: none;">Home</a> |
                <a href="search.php" style="color: white; text-decoration: none;">Search</a> |
                <a href="student_register.php" style="color: white; text-decoration: none;">Register</a> |
                <a href="contact.php" style="color: white; text-decoration: none;">Contact</a>
            </p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
