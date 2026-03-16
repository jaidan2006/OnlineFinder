<?php
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
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

        /* Footer Styles */
        .footer {
            background-color: #343a40;
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
    </style>
</head>
<body>

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

<!-- Contact Section -->
<section class="contact-section" style="padding: 4rem 0; background-color: #f8f9fa;">
    <div class="container">
        <h2 style="text-align: center; margin: 0 auto 3rem auto; color: #007bff; font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; position: relative; padding-bottom: 1rem; max-width: 800px; line-height: 1.2;">Contact Us</h2>
        
        <?php echo display_message(); ?>
        
        <div class="row" style="display: flex; justify-content: center; align-items: stretch; gap: 2rem;">
            <!-- Contact Information Box -->
            <div class="col-md-6" style="flex: 1; max-width: 500px;">
                <div class="card" style="height: 100%; padding: 2.5rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
                    <h3 style="color: #007bff; margin-bottom: 2rem; font-size: 1.8rem; font-weight: bold; text-align: center;">
                        <i class="fas fa-info-circle"></i> Get in Touch
                    </h3>
                    
                    <!-- Contact Information -->
                    <div class="contact-info">
                        <div class="hover-lift">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Phone</h4>
                                <p>+91 98765 43210</p>
                            </div>
                        </div>
                        
                        <div class="hover-lift">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>info@tutorfinder.com</p>
                            </div>
                        </div>
                        
                        <div class="hover-lift">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Address</h4>
                                <p>123, Education Street,<br>Koramangala, Bangalore<br>Karnataka - 560034</p>
                            </div>
                        </div>
                        
                        <div class="hover-lift">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Business Hours</h4>
                                <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media Links -->
                    <div class="social-media">
                        <h4>Follow Us</h4>
                        <div class="social-links">
                            <a href="https://www.facebook.com/" style="background-color: #1877f2;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com" style="background-color: #1da1f2;">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.instagram.com/" style="background-color: #e4405f;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://www.linkedin.com/" style="background-color: #0077b5;">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Send Message Box -->
            <div class="col-md-6" style="flex: 1; max-width: 500px;">
                <div class="card" style="height: 100%; padding: 2.5rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
                    <h3 style="color: #007bff; margin-bottom: 2rem; font-size: 1.8rem; font-weight: bold; text-align: center;">
                        <i class="fas fa-paper-plane"></i> Send Us a Message
                    </h3>
                    
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $name = sanitize_input($_POST['name']);
                        $email = sanitize_input($_POST['email']);
                        $subject = sanitize_input($_POST['subject']);
                        $message = sanitize_input($_POST['message']);
                        
                        if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
                            // Validate email
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                set_message('Please enter a valid email address.', 'error');
                            } else {
                                // Create contact_submissions table if it doesn't exist
                                $create_table_sql = "CREATE TABLE IF NOT EXISTS contact_submissions (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    name VARCHAR(255) NOT NULL,
                                    email VARCHAR(255) NOT NULL,
                                    subject VARCHAR(255) NOT NULL,
                                    message TEXT NOT NULL,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    status ENUM('new', 'read', 'replied') DEFAULT 'new'
                                )";
                                
                                if ($conn->query($create_table_sql)) {
                                    // Insert contact submission
                                    $insert_sql = "INSERT INTO contact_submissions (name, email, subject, message) 
                                                   VALUES (?, ?, ?, ?)";
                                    $insert_stmt = $conn->prepare($insert_sql);
                                    
                                    if ($insert_stmt === false) {
                                        set_message('Database error preparing query: ' . $conn->error, 'error');
                                    } else {
                                        $insert_stmt->bind_param("ssss", $name, $email, $subject, $message);
                                        
                                        if ($insert_stmt->execute()) {
                                            set_message('Thank you for contacting us! We will get back to you soon.', 'success');
                                        } else {
                                            set_message('Failed to submit your message. Please try again.', 'error');
                                        }
                                    }
                                } else {
                                    set_message('Database setup error. Please try again.', 'error');
                                }
                            }
                        } else {
                            set_message('Please fill in all required fields.', 'error');
                        }
                    }
                    ?>
                    
                    <form method="POST" action="contact.php" class="contact-form">
                        <div class="form-group">
                            <label for="name">Full Name <span style="color: red;">*</span></label>
                            <input type="text" name="name" id="name" required placeholder="Enter your full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address <span style="color: red;">*</span></label>
                            <input type="email" name="email" id="email" required placeholder="Enter your email address">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject <span style="color: red;">*</span></label>
                            <input type="text" name="subject" id="subject" required placeholder="Enter message subject">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span style="color: red;">*</span></label>
                            <textarea name="message" id="message" required rows="5" placeholder="Enter your message"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Contact Form Styles */
.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    background-color: white;
}

/* Contact Info Cards Hover Effects */
.contact-info > div:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Social Media Hover Effects */
.social-links a:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

/* Button Hover Effects */
button[type="submit"]:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,123,255,0.3);
}

/* Card Hover Effects */
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* Perfect Alignment Styles */
.contact-section .row {
    justify-content: center;
    align-items: stretch;
    gap: 2rem;
    margin: 0 auto;
    max-width: 1200px;
}

.contact-section .col-md-6 {
    flex: 1;
    min-width: 300px;
    max-width: 500px;
}

.contact-section .card {
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 2.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    background: white;
}

.contact-section .card h3 {
    color: #007bff;
    margin-bottom: 2rem;
    font-size: 1.8rem;
    font-weight: bold;
    text-align: center;
    line-height: 1.3;
}

/* Contact Info Alignment */
.contact-info {
    margin-bottom: 2rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.contact-info > div {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background-color: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.contact-info > div i {
    font-size: 1.8rem;
    color: #007bff;
    margin-right: 1.5rem;
    width: 40px;
    text-align: center;
    flex-shrink: 0;
}

.contact-info > div div {
    flex: 1;
    text-align: left;
}

.contact-info > div h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.2rem;
    font-weight: 600;
    line-height: 1.2;
}

.contact-info > div p {
    margin: 0;
    color: #666;
    font-size: 1rem;
    line-height: 1.4;
}

/* Social Media Alignment */
.contact-section .social-media {
    text-align: center;
    margin-top: auto;
    padding-top: 1rem;
}

.contact-section .social-media h4 {
    color: #007bff;
    margin-bottom: 1rem;
    font-size: 1.3rem;
    font-weight: 600;
}

.contact-section .social-media .social-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.contact-section .social-media .social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.2rem;
    color: white;
}

/* Form Alignment */
.contact-form {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.contact-form .form-group {
    margin-bottom: 1.5rem;
}

.contact-form .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    font-size: 1rem;
    text-align: left;
}

.contact-form .form-group input,
.contact-form .form-group textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
    text-align: left;
}

.contact-form .form-group textarea {
    resize: vertical;
    min-height: 140px;
}

.contact-form .form-group:last-child {
    margin-top: auto;
    text-align: center;
    margin-bottom: 0;
}

.contact-form button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 15px 2.5rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 180px;
}

/* Responsive Design */
@media (max-width: 992px) {
    .contact-section .row {
        flex-direction: column;
        gap: 2rem;
    }
    
    .contact-section .col-md-6 {
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    .contact-section .card {
        padding: 2rem;
    }
    
    .contact-section .card h3 {
        font-size: 1.6rem;
    }
    
    .contact-info > div {
        padding: 1.2rem;
    }
    
    .contact-info > div i {
        font-size: 1.6rem;
        margin-right: 1.2rem;
        width: 35px;
    }
    
    .contact-info > div h4 {
        font-size: 1.1rem;
    }
    
    .contact-info > div p {
        font-size: 0.95rem;
    }
}

@media (max-width: 576px) {
    .contact-section .card {
        padding: 1.5rem;
    }
    
    .contact-section .card h3 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .contact-info > div {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
        gap: 0.5rem;
    }
    
    .contact-info > div i {
        margin-right: 0;
        margin-bottom: 0.5rem;
        width: auto;
    }
    
    .contact-info > div div {
        text-align: center;
    }
    
    .contact-form .form-group input,
    .contact-form .form-group textarea {
        padding: 12px;
    }
    
    .contact-form button {
        padding: 12px 2rem;
        font-size: 1rem;
        min-width: 150px;
    }
    
    .contact-section .social-media .social-links a {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

/* Perfect Centering */
.contact-section h2 {
    text-align: center;
    margin: 0 auto 3rem auto;
    color: #007bff;
    font-size: 2.5rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    padding-bottom: 1rem;
    max-width: 800px;
    line-height: 1.2;
}

/* Animation Classes */
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Fix for textarea box-sizing */
.contact-form .form-group textarea {
    box-sizing: border-box;
}
</style>

<?php require_once 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
