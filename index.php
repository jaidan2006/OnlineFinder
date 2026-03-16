<?php
require_once 'includes/config.php';

// Fetch subjects for the search form
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_result = $conn->query($subjects_query);

// Fetch latest tutors
$latest_tutors_query = "SELECT * FROM tutors WHERE approved = 'approved' ORDER BY created_at DESC LIMIT 4";
$latest_tutors_result = $conn->query($latest_tutors_query);

// Fetch latest coaching centers
$latest_centers_query = "SELECT * FROM coaching_centers WHERE approved = 'approved' ORDER BY created_at DESC LIMIT 4";
$latest_centers_result = $conn->query($latest_centers_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Find Your Perfect Tutor or Coaching Center</title>
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

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            display: flex;
            align-items: center;
            gap: 3rem;
        }

        .hero-text {
            flex: 1;
        }

        .hero-text h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .hero-text p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

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
            background-color: white;
            color: #007bff;
        }

        .btn-primary:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,255,255,0.3);
        }

        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background-color: white;
            color: #007bff;
            transform: translateY(-2px);
        }

        .hero-image {
            flex: 1;
            text-align: center;
        }

        .hero-image i {
            font-size: 8rem;
            opacity: 0.8;
        }

        /* Search Section */
        .search-section {
            padding: 3rem 0;
            background-color: white;
            margin-top: -2rem;
            position: relative;
            z-index: 10;
            border-radius: 15px 15px 0 0;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            background-color: white;
        }

        /* Features Section */
        .features-section {
            padding: 4rem 0;
            background-color: #f8f9fa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            color: #333;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Tutors Section */
        .tutors-section {
            padding: 4rem 0;
            background-color: white;
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            color: #333;
            margin-bottom: 3rem;
        }

        .tutors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .tutor-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .tutor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .tutor-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
        }

        .tutor-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .tutor-subject {
            color: #007bff;
            margin-bottom: 1rem;
        }

        .tutor-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .tutor-rating {
            color: #ffc107;
            margin-bottom: 1rem;
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
            
            .hero-content {
                flex-direction: column;
                text-align: center;
            }
            
            .hero-text h1 {
                font-size: 2rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .hero-image i {
                font-size: 6rem;
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
            
            .hero-text h1 {
                font-size: 1.5rem;
            }
            
            .hero-text p {
                font-size: 1rem;
            }
            
            .search-form {
                padding: 1.5rem;
            }
            
            .hero-image i {
                font-size: 4rem;
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
                
                <div class="auth-buttons">
                    <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Find the Perfect Tutor or Coaching Centre</h1>
                    <p>Connect with qualified tutors and reputable coaching centres to enhance your learning experience. Quality education at your fingertips.</p>
                    <div class="hero-buttons">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Get Started
                        </a>
                        <a href="search.php" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Search Now
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section style="padding: 4rem 0; background-color: white;">
        <div class="container">
            <h2 style="text-align: center; margin: 0 auto 3rem auto; color: #007bff; font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; position: relative; padding-bottom: 1rem; max-width: 800px; line-height: 1.2;">Why Choose Our Platform?</h2>
            <div class="row" style="display: flex; justify-content: center; align-items: stretch; gap: 2rem;">
                <div class="col-md-4" style="flex: 1; max-width: 350px;">
                    <div class="card text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                        <div style="font-size: 4rem; color: #007bff; margin-bottom: 1.5rem; text-align: center;">👨‍🏫</div>
                        <h3 style="color: #007bff; margin-bottom: 1rem; font-size: 1.5rem; font-weight: bold; text-align: center;">Expert Tutors</h3>
                        <p style="text-align: center; line-height: 1.6; color: #666; margin: 0;">Connect with qualified and experienced tutors in various subjects</p>
                    </div>
                </div>
                <div class="col-md-4" style="flex: 1; max-width: 350px;">
                    <div class="card text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                        <div style="font-size: 4rem; color: #007bff; margin-bottom: 1.5rem; text-align: center;">🏫</div>
                        <h3 style="color: #007bff; margin-bottom: 1rem; font-size: 1.5rem; font-weight: bold; text-align: center;">Coaching Centres</h3>
                        <p style="text-align: center; line-height: 1.6; color: #666; margin: 0;">Find reputed coaching centres offering comprehensive courses</p>
                    </div>
                </div>
                <div class="col-md-4" style="flex: 1; max-width: 350px;">
                    <div class="card text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                        <div style="font-size: 4rem; color: #007bff; margin-bottom: 1.5rem; text-align: center;">💻</div>
                        <h3 style="color: #007bff; margin-bottom: 1rem; font-size: 1.5rem; font-weight: bold; text-align: center;">Flexible Learning</h3>
                        <p style="text-align: center; line-height: 1.6; color: #666; margin: 0;">Choose between online and offline learning modes</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section style="padding: 4rem 0; background-color: #f8f9fa;">
        <div class="container">
            <h2 style="text-align: center; margin: 0 auto 3rem auto; color: #007bff; font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; position: relative; padding-bottom: 1rem; max-width: 800px; line-height: 1.2;">How It Works</h2>
            <div class="row" style="display: flex; justify-content: center; align-items: stretch; gap: 2rem;">
                <div class="col-md-3" style="flex: 1; max-width: 250px;">
                    <div class="card text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                        <div style="background-color: #007bff; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 1.8rem; font-weight: bold;">1</div>
                        <h4 style="color: #007bff; margin-bottom: 1rem; font-size: 1.3rem; font-weight: bold;">Register</h4>
                        <p style="text-align: center; line-height: 1.6; color: #666; margin: 0;">Create your account as student, tutor, or coaching centre</p>
                    </div>
                </div>
                <div class="col-md-3" style="flex: 1; max-width: 250px;">
                    <div class="card text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                        <div style="background-color: #007bff; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 1.8rem; font-weight: bold;">2</div>
                        <h4 style="color: #007bff; margin-bottom: 1rem; font-size: 1.3rem; font-weight: bold;">Search</h4>
                        <p style="text-align: center; line-height: 1.6; color: #666; margin: 0;">Find tutors or centres based on subject, location, and mode</p>
                    </div>
                </div>
                <div class="col-md-3" style="flex: 1; max-width: 250px;">
                    <div class="card text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                        <div style="background-color: #007bff; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 1.8rem; font-weight: bold;">3</div>
                        <h4 style="color: #007bff; margin-bottom: 1rem; font-size: 1.3rem; font-weight: bold;">Book</h4>
                        <p style="text-align: center; line-height: 1.6; color: #666; margin: 0;">Book sessions with your preferred tutors or centres</p>
                    </div>
                </div>
                <div class="col-md-3" style="flex: 1; max-width: 250px;">
                    <div class="card text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                        <div style="background-color: #007bff; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 1.8rem; font-weight: bold;">4</div>
                        <h4 style="color: #007bff; margin-bottom: 1rem; font-size: 1.3rem; font-weight: bold;">Learn</h4>
                        <p style="text-align: center; line-height: 1.6; color: #666; margin: 0;">Start your learning journey and provide feedback</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Subjects Section -->
    <section style="padding: 4rem 0; background-color: white;">
        <div class="container">
            <h2 style="text-align: center; margin: 0 auto 3rem auto; color: #007bff; font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; position: relative; padding-bottom: 1rem; max-width: 800px; line-height: 1.2;">Popular Subjects</h2>
            <div class="row" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 1.5rem;">
                <?php
                $sql = "SELECT s.*, COUNT(b.booking_id) as booking_count 
                        FROM subjects s 
                        LEFT JOIN bookings b ON s.subject_id = b.subject_id 
                        GROUP BY s.subject_id 
                        ORDER BY booking_count DESC 
                        LIMIT 8";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="col-md-3" style="flex: 0 0 calc(25% - 1.125rem); max-width: 250px;">';
                        echo '<div class="card text-center" style="cursor: pointer; padding: 1.5rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center;" onclick="window.location.href=\'search.php?subject=' . $row['subject_id'] . '\'">';
                        echo '<h4 style="color: #007bff; margin-bottom: 0.5rem; font-size: 1.2rem; font-weight: bold;">' . htmlspecialchars($row['subject_name']) . '</h4>';
                        echo '<p style="color: #666; font-size: 0.9rem; margin: 0;">' . $row['booking_count'] . ' bookings</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section style="padding: 4rem 0; background-color: #007bff; color: white;">
        <div class="container">
            <h2 style="text-align: center; margin: 0 auto 3rem auto; font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; position: relative; padding-bottom: 1rem; max-width: 800px; line-height: 1.2;">Platform Statistics</h2>
            <div class="row" style="display: flex; justify-content: center; align-items: stretch; gap: 2rem;">
                <div class="col-md-3" style="flex: 1; max-width: 200px;">
                    <div class="text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem;">
                        <div style="font-size: 3rem; font-weight: bold; margin-bottom: 1rem;">
                            <?php
                            $sql = "SELECT COUNT(*) as count FROM students";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                            ?>
                        </div>
                        <p style="font-size: 1.2rem; margin: 0;">Students</p>
                    </div>
                </div>
                <div class="col-md-3" style="flex: 1; max-width: 200px;">
                    <div class="text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem;">
                        <div style="font-size: 3rem; font-weight: bold; margin-bottom: 1rem;">
                            <?php
                            $sql = "SELECT COUNT(*) as count FROM tutors WHERE approved = 'approved' AND availability_status = 'available'";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                            ?>
                        </div>
                        <p style="font-size: 1.2rem; margin: 0;">Tutors</p>
                    </div>
                </div>
                <div class="col-md-3" style="flex: 1; max-width: 200px;">
                    <div class="text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem;">
                        <div style="font-size: 3rem; font-weight: bold; margin-bottom: 1rem;">
                            <?php
                            $sql = "SELECT COUNT(*) as count FROM coaching_centers WHERE approved = 'approved' AND availability_status = 'available'";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                            ?>
                        </div>
                        <p style="font-size: 1.2rem; margin: 0;">Coaching Centers</p>
                    </div>
                </div>
                <div class="col-md-3" style="flex: 1; max-width: 200px;">
                    <div class="text-center" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem;">
                        <div style="font-size: 3rem; font-weight: bold; margin-bottom: 1rem;">
                            <?php
                            $sql = "SELECT COUNT(*) as count FROM bookings WHERE status IN ('confirmed', 'completed')";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                            ?>
                        </div>
                        <p style="font-size: 1.2rem; margin: 0;">Bookings</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section style="padding: 4rem 0; background-color: #f8f9fa;">
        <div class="container">
            <h2 style="text-align: center; margin: 0 auto 3rem auto; color: #007bff; font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; position: relative; padding-bottom: 1rem; max-width: 800px; line-height: 1.2;">What Our Users Say</h2>
            <div class="row" style="display: flex; justify-content: center; align-items: stretch; gap: 2rem;">
                <?php
                $sql = "SELECT r.*, s.first_name, s.last_name, 
                               CASE 
                                   WHEN r.tutor_id IS NOT NULL THEN t.first_name
                                   ELSE cc.center_name
                               END as provider_name,
                               CASE 
                                   WHEN r.tutor_id IS NOT NULL THEN 'tutor'
                                   ELSE 'center'
                               END as provider_type
                        FROM reviews r 
                        JOIN students s ON r.student_id = s.student_id 
                        LEFT JOIN tutors t ON r.tutor_id = t.tutor_id 
                        LEFT JOIN coaching_centers cc ON r.center_id = cc.center_id 
                        WHERE r.rating >= 4 
                        ORDER BY r.created_at DESC 
                        LIMIT 3";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="col-md-4" style="flex: 1; max-width: 350px;">';
                        echo '<div class="card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">';
                        echo '<div class="rating mb-2" style="text-align: center; margin-bottom: 1rem;">';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $row['rating']) {
                                echo '<span style="color: #ffc107; font-size: 1.2rem;">★</span>';
                            } else {
                                echo '<span style="color: #ddd; font-size: 1.2rem;">★</span>';
                            }
                        }
                        echo '</div>';
                        echo '<p style="font-style: italic; margin-bottom: 1rem; text-align: center; line-height: 1.6; color: #666;">"' . htmlspecialchars($row['review_text']) . '"</p>';
                        echo '<p style="color: #666; font-size: 0.9rem; text-align: center; margin: 0;">- ' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                        if ($row['provider_type'] == 'tutor') {
                            echo ' (Student of ' . htmlspecialchars($row['provider_name']) . ')';
                        } else {
                            echo ' (Student of ' . htmlspecialchars($row['provider_name']) . ')';
                        }
                        echo '</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="col-12" style="text-align: center;">';
                    echo '<p style="color: #666;">No reviews yet. Be the first to share your experience!</p>';
                    echo '</div>';
                }
                ?>
            </div>
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
