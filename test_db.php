<?php
require_once 'includes/config.php';

// Test a simple query first
$sql = "SELECT COUNT(*) as count FROM tutors";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "Tutors table has " . $row['count'] . " records\n";
} else {
    echo "Error querying tutors table: " . $conn->error . "\n";
}

// Test the UPDATE query with a simple case
$sql = "UPDATE tutors SET first_name = ? WHERE tutor_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Error preparing simple query: " . $conn->error . "\n";
} else {
    echo "Simple query preparation successful\n";
    $stmt->close();
}

// Test the full query
$sql = "UPDATE tutors SET 
        first_name = ?, 
        last_name = ?, 
        email = ?, 
        phone = ?, 
        qualification = ?,
        subjects_taught = ?,
        teaching_mode = ?,
        location = ?,
        experience = ?,
        description = ?,
        availability_status = ?
        WHERE tutor_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Error preparing full query: " . $conn->error . "\n";
} else {
    echo "Full query preparation successful\n";
    $stmt->close();
}
?>
