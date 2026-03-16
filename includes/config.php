<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'coaching_finder');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Session configuration
session_start();

// Global variables
define('BASE_URL', 'http://localhost/CoachingCenter_And_Tutor_Finder/');
define('SITE_NAME', 'Online Tutor and Coaching Center Finder');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_student() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student';
}

function is_tutor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'tutor';
}

function is_center() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'center';
}

function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function get_user_type() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function display_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        $class = ($type == 'error') ? 'error-message' : 'success-message';
        return "<div class='$class'>$message</div>";
    }
    return '';
}

function set_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function get_user_full_name($user_id, $user_type) {
    global $conn;
    
    if ($user_type == 'student') {
        $sql = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM students WHERE student_id = ?";
    } elseif ($user_type == 'tutor') {
        $sql = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM tutors WHERE tutor_id = ?";
    } elseif ($user_type == 'center') {
        $sql = "SELECT center_name as full_name FROM coaching_centers WHERE center_id = ?";
    } elseif ($user_type == 'admin') {
        $sql = "SELECT username as full_name FROM admin WHERE admin_id = ?";
    } else {
        return 'Unknown User';
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['full_name'];
    }
    
    return 'Unknown User';
}

function logout() {
    // Log the logout if user was logged in
    if (is_logged_in()) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        $user_type = $_SESSION['user_type'];
        
        $sql = "UPDATE login_credentials SET logout_time = CURRENT_TIMESTAMP WHERE user_id = ? AND user_type = ? AND logout_time IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $user_type);
        $stmt->execute();
    }
    
    // Destroy all session data
    session_destroy();
    
    // Redirect to home
    redirect('index.php');
}
?>
