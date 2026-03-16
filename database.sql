-- Online Tutor and Coaching Center Finder Database Schema
-- Create Database
CREATE DATABASE IF NOT EXISTS coaching_finder;
USE coaching_finder;

-- 1. Admin Table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Students Table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- 3. Tutors Table
CREATE TABLE tutors (
    tutor_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    qualification VARCHAR(255),
    experience_years INT,
    subjects_taught TEXT,
    teaching_mode ENUM('online', 'offline', 'both') DEFAULT 'both',
    location VARCHAR(100),
    hourly_rate DECIMAL(10,2),
    description TEXT,
    profile_image VARCHAR(255),
    availability_status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
);

-- 4. Coaching Centers Table
CREATE TABLE coaching_centers (
    center_id INT AUTO_INCREMENT PRIMARY KEY,
    center_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    location VARCHAR(100),
    courses_offered TEXT,
    teaching_mode ENUM('online', 'offline', 'both') DEFAULT 'both',
    description TEXT,
    website VARCHAR(255),
    logo VARCHAR(255),
    availability_status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
);

-- 5. Subjects Table
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(50)
);

-- 6. Bookings Table
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    tutor_id INT NULL,
    center_id INT NULL,
    subject_id INT,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    duration_hours INT DEFAULT 1,
    mode ENUM('online', 'offline') NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id),
    FOREIGN KEY (center_id) REFERENCES coaching_centers(center_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id)
);

-- 7. Reviews Table
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    tutor_id INT NULL,
    center_id INT NULL,
    booking_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id),
    FOREIGN KEY (center_id) REFERENCES coaching_centers(center_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- 8. Login Credentials Table (for session management)
CREATE TABLE login_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('student', 'tutor', 'center', 'admin') NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    session_id VARCHAR(255) UNIQUE,
    ip_address VARCHAR(45)
);

-- Insert Sample Data

-- Insert Admin
INSERT INTO admin (username, password, email) VALUES 
('admin', 'admin123', 'admin@coachingfinder.com');

-- Insert Sample Subjects
INSERT INTO subjects (subject_name, category) VALUES 
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
INSERT INTO students (first_name, last_name, email, phone, password, address) VALUES 
('Rahul', 'Kumar', 'rahul@email.com', '9876543210', 'student123', 'Delhi, India'),
('Priya', 'Sharma', 'priya@email.com', '9876543211', 'student123', 'Mumbai, India'),
('Amit', 'Patel', 'amit@email.com', '9876543212', 'student123', 'Bangalore, India');

-- Insert Sample Tutors
INSERT INTO tutors (first_name, last_name, email, phone, password, qualification, experience_years, subjects_taught, teaching_mode, location, hourly_rate, description, approved) VALUES 
('Dr. Rajesh', 'Singh', 'rajesh@email.com', '9876543213', 'tutor123', 'PhD in Mathematics', 10, 'Mathematics, Physics', 'both', 'Delhi', 500.00, 'Expert in Mathematics and Physics with 10 years of teaching experience.', 'approved'),
('Sunita', 'Reddy', 'sunita@email.com', '9876543214', 'tutor123', 'M.Sc Chemistry', 8, 'Chemistry, Biology', 'online', 'Mumbai', 400.00, 'Specialized in Chemistry and Biology for competitive exams.', 'approved'),
('Vikram', 'Mehta', 'vikram@email.com', '9876543215', 'tutor123', 'M.Tech Computer Science', 5, 'Computer Science, Mathematics', 'offline', 'Bangalore', 600.00, 'Software engineer with passion for teaching programming.', 'approved');

-- Insert Sample Coaching Centers
INSERT INTO coaching_centers (center_name, email, phone, password, address, location, courses_offered, teaching_mode, description, approved) VALUES 
('Excel Learning Center', 'info@excellearning.com', '9876543216', 'center123', '123 Main Street, Delhi', 'Delhi', 'Mathematics, Science, English', 'both', 'Premier coaching center for competitive exams.', 'approved'),
('Bright Future Academy', 'contact@brightfuture.com', '9876543217', 'center123', '456 Park Road, Mumbai', 'Mumbai', 'All Subjects', 'offline', 'Complete education solution from KG to PG.', 'approved'),
('Tech Institute', 'info@techinstitute.com', '9876543218', 'center123', '789 Tech Park, Bangalore', 'Bangalore', 'Computer Science, IT', 'online', 'Specialized in technology and programming courses.', 'approved');

-- Insert Sample Bookings
INSERT INTO bookings (student_id, tutor_id, subject_id, booking_date, booking_time, duration_hours, mode, status) VALUES 
(1, 1, 1, '2026-02-15', '10:00:00', 2, 'online', 'confirmed'),
(2, 2, 3, '2026-02-16', '14:00:00', 1, 'offline', 'pending'),
(3, 3, 7, '2026-02-17', '16:00:00', 2, 'online', 'confirmed');

-- Insert Sample Reviews
INSERT INTO reviews (student_id, tutor_id, booking_id, rating, review_text) VALUES 
(1, 1, 1, 5, 'Excellent teaching method! Very clear explanations.'),
(2, 2, 2, 4, 'Good tutor, explains concepts well.'),
(3, 3, 3, 5, 'Best programming tutor I have ever had!');
