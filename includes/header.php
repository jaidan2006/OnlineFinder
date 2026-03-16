<?php
// Check if user is logged in and get user type
$is_logged_in = is_logged_in();
$user_type = get_user_type();
?>
<style>
    /* Global Header Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
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
            flex-wrap: wrap;
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
                    <li><a href="<?php echo ($is_logged_in && $user_type == 'student') ? 'student_dashboard.php' : 'index.php'; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    <?php if ($is_logged_in): ?>
                        <?php if ($user_type == 'student'): ?>
                            <li><a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <?php elseif ($user_type == 'tutor'): ?>
                            <li><a href="tutor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <?php elseif ($user_type == 'center'): ?>
                            <li><a href="center_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <?php elseif ($user_type == 'admin'): ?>
                            <li><a href="admin_dashboard.php"><i class="fas fa-cog"></i> Admin Panel</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                <?php if ($is_logged_in): ?>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">
                        <i class="fas fa-user-graduate"></i> Student Login
                    </a>
                    <a href="login.php" class="btn-register">
                        <i class="fas fa-chalkboard-teacher"></i> Provider Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
