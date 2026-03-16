<?php
// Footer component for Tutor Finder application
// This file can be included in any page to add a consistent footer
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Tutor Finder</span>
                </div>
                <p class="footer-description">
                    Your trusted platform for finding qualified tutors and coaching centers. Connect with the best educators to achieve your learning goals.
                </p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="services.php"><i class="fas fa-cogs"></i> Services</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>For Students</h4>
                <ul class="footer-links">
                    <li><a href="search.php"><i class="fas fa-search"></i> Find Tutors</a></li>
                    <li><a href="student_register.php"><i class="fas fa-user-plus"></i> Student Register</a></li>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Student Login</a></li>
                    <li><a href="how-it-works.php"><i class="fas fa-question-circle"></i> How It Works</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>For Providers</h4>
                <ul class="footer-links">
                    <li><a href="tutor_register.php"><i class="fas fa-chalkboard-teacher"></i> Tutor Register</a></li>
                    <li><a href="center_register.php"><i class="fas fa-school"></i> Center Register</a></li>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Provider Login</a></li>
                    <li><a href="become-tutor.php"><i class="fas fa-star"></i> Become a Tutor</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Connect With Us</h4>
                <div class="social-links">
                    <a href="#" class="social-link facebook" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link twitter" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link instagram" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link linkedin" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
                <div class="contact-info">
                    <p><i class="fas fa-phone"></i> +91 98765 43210</p>
                    <p><i class="fas fa-envelope"></i> info@tutorfinder.com</p>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p class="copyright">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                </p>
                <div class="footer-bottom-links">
                    <a href="privacy-policy.php">Privacy Policy</a>
                    <a href="terms-of-service.php">Terms of Service</a>
                    <a href="sitemap.php">Sitemap</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.site-footer {
    background-color: #1a1a2e;
    color: #ffffff;
    padding: 60px 0 20px;
    font-family: 'Arial', sans-serif;
}

.footer-content {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1.5fr;
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section h4 {
    color: #007bff;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.footer-logo i {
    font-size: 2rem;
    color: #007bff;
}

.footer-logo span {
    font-size: 1.5rem;
    font-weight: bold;
    color: #ffffff;
}

.footer-description {
    color: #b8b8b8;
    line-height: 1.6;
    font-size: 0.95rem;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #b8b8b8;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color 0.3s ease, transform 0.2s ease;
    font-size: 0.95rem;
}

.footer-links a:hover {
    color: #007bff;
    transform: translateX(5px);
}

.footer-links a i {
    font-size: 0.9rem;
    width: 16px;
    text-align: center;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.social-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.social-link.facebook:hover {
    background-color: #1877f2;
}

.social-link.twitter:hover {
    background-color: #1da1f2;
}

.social-link.instagram:hover {
    background-color: #e4405f;
}

.social-link.linkedin:hover {
    background-color: #0077b5;
}

.contact-info p {
    color: #b8b8b8;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
}

.contact-info i {
    color: #007bff;
    width: 16px;
    text-align: center;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 20px;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.copyright {
    color: #b8b8b8;
    font-size: 0.9rem;
    margin: 0;
}

.footer-bottom-links {
    display: flex;
    gap: 20px;
}

.footer-bottom-links a {
    color: #b8b8b8;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #007bff;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .footer-content {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 30px;
    }
    
    .footer-section:first-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }
    
    .footer-section:first-child {
        grid-column: 1 / -1;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .footer-bottom-links {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .site-footer {
        padding: 40px 0 15px;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .footer-section {
        text-align: center;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .contact-info {
        text-align: center;
    }
    
    .contact-info p {
        justify-content: center;
    }
    
    .footer-links a {
        justify-content: center;
    }
}
</style>
