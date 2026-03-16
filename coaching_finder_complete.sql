-- =============================================
-- Coaching Center and Tutor Finder Database
-- Complete SQL Schema for XAMPP MySQL Import
-- =============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `coaching_finder` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `coaching_finder`;

-- =============================================
-- 1. Admin Table
-- =============================================
CREATE TABLE `admin` (
    `admin_id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`admin_id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 2. Students Table
-- =============================================
CREATE TABLE `students` (
    `student_id` INT(11) NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`student_id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 3. Tutors Table
-- =============================================
CREATE TABLE `tutors` (
    `tutor_id` INT(11) NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `qualification` VARCHAR(255) DEFAULT NULL,
    `experience_years` INT(11) DEFAULT NULL,
    `subjects_taught` TEXT DEFAULT NULL,
    `teaching_mode` ENUM('online', 'offline', 'both') NOT NULL DEFAULT 'both',
    `location` VARCHAR(100) DEFAULT NULL,
    `hourly_rate` DECIMAL(10,2) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `availability_status` ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `approved` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`tutor_id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 4. Coaching Centers Table
-- =============================================
CREATE TABLE `coaching_centers` (
    `center_id` INT(11) NOT NULL AUTO_INCREMENT,
    `center_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `location` VARCHAR(100) DEFAULT NULL,
    `courses_offered` TEXT DEFAULT NULL,
    `teaching_mode` ENUM('online', 'offline', 'both') NOT NULL DEFAULT 'both',
    `description` TEXT DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `availability_status` ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `approved` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`center_id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 5. Subjects Table
-- =============================================
CREATE TABLE `subjects` (
    `subject_id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (`subject_id`),
    UNIQUE KEY `subject_name` (`subject_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 6. Bookings Table
-- =============================================
CREATE TABLE `bookings` (
    `booking_id` INT(11) NOT NULL AUTO_INCREMENT,
    `student_id` INT(11) NOT NULL,
    `tutor_id` INT(11) DEFAULT NULL,
    `center_id` INT(11) DEFAULT NULL,
    `subject_id` INT(11) NOT NULL,
    `booking_date` DATE NOT NULL,
    `booking_time` TIME NOT NULL,
    `duration_hours` INT(11) NOT NULL DEFAULT 1,
    `mode` ENUM('online', 'offline') NOT NULL,
    `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `message` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`booking_id`),
    KEY `student_id` (`student_id`),
    KEY `tutor_id` (`tutor_id`),
    KEY `center_id` (`center_id`),
    KEY `subject_id` (`subject_id`),
    CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
    CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`tutor_id`) ON DELETE SET NULL,
    CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`center_id`) REFERENCES `coaching_centers` (`center_id`) ON DELETE SET NULL,
    CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 7. Reviews Table
-- =============================================
CREATE TABLE `reviews` (
    `review_id` INT(11) NOT NULL AUTO_INCREMENT,
    `student_id` INT(11) NOT NULL,
    `tutor_id` INT(11) DEFAULT NULL,
    `center_id` INT(11) DEFAULT NULL,
    `booking_id` INT(11) DEFAULT NULL,
    `rating` INT(11) NOT NULL,
    `review_text` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`review_id`),
    KEY `student_id` (`student_id`),
    KEY `tutor_id` (`tutor_id`),
    KEY `center_id` (`center_id`),
    KEY `booking_id` (`booking_id`),
    CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
    CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`tutor_id`) ON DELETE SET NULL,
    CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`center_id`) REFERENCES `coaching_centers` (`center_id`) ON DELETE SET NULL,
    CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
    CONSTRAINT `chk_rating` CHECK (`rating` >= 1 AND `rating` <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 8. Login Credentials Table
-- =============================================
CREATE TABLE `login_credentials` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `user_type` ENUM('student', 'tutor', 'center', 'admin') NOT NULL,
    `login_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `logout_time` TIMESTAMP NULL DEFAULT NULL,
    `session_id` VARCHAR(255) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `session_id` (`session_id`),
    KEY `user_id` (`user_id`),
    KEY `user_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Insert Sample Data
-- =============================================

-- Insert Admin User
INSERT INTO `admin` (`username`, `password`, `email`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@coachingfinder.com');

-- Insert Subjects
INSERT INTO `subjects` (`subject_name`, `category`) VALUES 
('Mathematics', 'Science'),
('Physics', 'Science'),
('Chemistry', 'Science'),
('Biology', 'Science'),
('English', 'Language'),
('Hindi', 'Language'),
('Computer Science', 'Technology'),
('History', 'Social Studies'),
('Geography', 'Social Studies'),
('Economics', 'Commerce'),
('Accountancy', 'Commerce'),
('Business Studies', 'Commerce');

-- Insert Sample Students
INSERT INTO `students` (`first_name`, `last_name`, `email`, `phone`, `password`, `address`) VALUES 
('Rahul', 'Kumar', 'rahul@email.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Delhi, India'),
('Priya', 'Sharma', 'priya@email.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mumbai, India'),
('Amit', 'Patel', 'amit@email.com', '9876543212', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bangalore, India');

-- Insert Sample Tutors
INSERT INTO `tutors` (`first_name`, `last_name`, `email`, `phone`, `password`, `qualification`, `experience_years`, `subjects_taught`, `teaching_mode`, `location`, `hourly_rate`, `description`, `approved`) VALUES 
('Dr. Rajesh', 'Singh', 'rajesh@email.com', '9876543213', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PhD in Mathematics', 10, 'Mathematics, Physics', 'both', 'Delhi', 500.00, 'Expert in Mathematics and Physics with 10 years of teaching experience.', 'approved'),
('Sunita', 'Reddy', 'sunita@email.com', '9876543214', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'M.Sc Chemistry', 8, 'Chemistry, Biology', 'online', 'Mumbai', 400.00, 'Specialized in Chemistry and Biology for competitive exams.', 'approved'),
('Vikram', 'Mehta', 'vikram@email.com', '9876543215', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'M.Tech Computer Science', 5, 'Computer Science, Mathematics', 'offline', 'Bangalore', 600.00, 'Software engineer with passion for teaching programming.', 'approved');

-- Insert Sample Coaching Centers
INSERT INTO `coaching_centers` (`center_name`, `email`, `phone`, `password`, `address`, `location`, `courses_offered`, `teaching_mode`, `description`, `approved`) VALUES 
('Excel Learning Center', 'info@excellearning.com', '9876543216', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Main Street, Delhi', 'Delhi', 'Mathematics, Science, English', 'both', 'Premier coaching center for competitive exams.', 'approved'),
('Bright Future Academy', 'contact@brightfuture.com', '9876543217', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '456 Park Road, Mumbai', 'Mumbai', 'All Subjects', 'offline', 'Complete education solution from KG to PG.', 'approved'),
('Tech Institute', 'info@techinstitute.com', '9876543218', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '789 Tech Park, Bangalore', 'Bangalore', 'Computer Science, IT', 'online', 'Specialized in technology and programming courses.', 'approved');

-- Insert Sample Bookings
INSERT INTO `bookings` (`student_id`, `tutor_id`, `subject_id`, `booking_date`, `booking_time`, `duration_hours`, `mode`, `status`) VALUES 
(1, 1, 1, '2026-03-15', '10:00:00', 2, 'online', 'confirmed'),
(2, 2, 3, '2026-03-16', '14:00:00', 1, 'offline', 'pending'),
(3, 3, 7, '2026-03-17', '16:00:00', 2, 'online', 'confirmed');

-- Insert Sample Reviews
INSERT INTO `reviews` (`student_id`, `tutor_id`, `booking_id`, `rating`, `review_text`) VALUES 
(1, 1, 1, 5, 'Excellent teaching method! Very clear explanations.'),
(2, 2, 2, 4, 'Good tutor, explains concepts well.'),
(3, 3, 3, 5, 'Best programming tutor I have ever had!');

-- =============================================
-- Database Import Complete
-- =============================================

-- Default Login Credentials:
-- Admin: admin@coachingfinder.com / admin123
-- Students: rahul@email.com / student123
-- Tutors: rajesh@email.com / tutor123
-- Centers: info@excellearning.com / center123
