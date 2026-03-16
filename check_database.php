<?php
require_once 'includes/config.php';

echo "<h1>Check Subjects in Database</h1>";

// Check all subjects
$sql = "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<h2>All Subjects:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Subject Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['subject_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No subjects found<br>";
}

// Check tutors with Chemistry
echo "<h2>Check Tutors with Chemistry</h2>";
$chemistry_sql = "SELECT tutor_id, first_name, last_name, subjects_taught, location, teaching_mode, approved, availability_status FROM tutors WHERE subjects_taught LIKE '%Chemistry%'";
$chemistry_result = $conn->query($chemistry_sql);

if ($chemistry_result && $chemistry_result->num_rows > 0) {
    echo "Found " . $chemistry_result->num_rows . " tutors with Chemistry:<br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Subjects</th><th>Location</th><th>Mode</th><th>Approved</th><th>Available</th></tr>";
    while ($row = $chemistry_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['tutor_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subjects_taught']) . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['teaching_mode']) . "</td>";
        echo "<td>" . $row['approved'] . "</td>";
        echo "<td>" . $row['availability_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No tutors found with Chemistry in subjects_taught<br>";
}

// Check tutors in Delhi
echo "<h2>Check Tutors in Delhi</h2>";
$delhi_sql = "SELECT tutor_id, first_name, last_name, subjects_taught, location, teaching_mode, approved, availability_status FROM tutors WHERE location LIKE '%Delhi%'";
$delhi_result = $conn->query($delhi_sql);

if ($delhi_result && $delhi_result->num_rows > 0) {
    echo "Found " . $delhi_result->num_rows . " tutors in Delhi:<br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Subjects</th><th>Location</th><th>Mode</th><th>Approved</th><th>Available</th></tr>";
    while ($row = $delhi_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['tutor_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subjects_taught']) . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['teaching_mode']) . "</td>";
        echo "<td>" . $row['approved'] . "</td>";
        echo "<td>" . $row['availability_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No tutors found in Delhi<br>";
}

// Check tutors with offline mode
echo "<h2>Check Tutors with Offline Mode</h2>";
$offline_sql = "SELECT tutor_id, first_name, last_name, subjects_taught, location, teaching_mode, approved, availability_status FROM tutors WHERE teaching_mode = 'offline'";
$offline_result = $conn->query($offline_sql);

if ($offline_result && $offline_result->num_rows > 0) {
    echo "Found " . $offline_result->num_rows . " tutors with offline mode:<br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Subjects</th><th>Location</th><th>Mode</th><th>Approved</th><th>Available</th></tr>";
    while ($row = $offline_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['tutor_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subjects_taught']) . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['teaching_mode']) . "</td>";
        echo "<td>" . $row['approved'] . "</td>";
        echo "<td>" . $row['availability_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No tutors found with offline mode<br>";
}

// Check the exact combination
echo "<h2>Check Exact Combination: Chemistry + Delhi + Offline</h2>";
$exact_sql = "SELECT tutor_id, first_name, last_name, subjects_taught, location, teaching_mode, approved, availability_status FROM tutors WHERE subjects_taught LIKE '%Chemistry%' AND location LIKE '%Delhi%' AND teaching_mode = 'offline'";
$exact_result = $conn->query($exact_sql);

if ($exact_result && $exact_result->num_rows > 0) {
    echo "Found " . $exact_result->num_rows . " tutors with Chemistry + Delhi + Offline:<br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Subjects</th><th>Location</th><th>Mode</th><th>Approved</th><th>Available</th></tr>";
    while ($row = $exact_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['tutor_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subjects_taught']) . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['teaching_mode']) . "</td>";
        echo "<td>" . $row['approved'] . "</td>";
        echo "<td>" . $row['availability_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No tutors found with exact combination<br>";
}

$conn->close();
?>
