<?php
require_once 'includes/config.php';

echo "<h1>Search Debug Test - Chemistry, Delhi, Offline</h1>";

// Test the exact search parameters
$subject_id = 1; // Assuming Chemistry has ID 1
$location = 'Delhi';
$mode = 'offline';

echo "<h2>Test Parameters</h2>";
echo "Subject ID: " . $subject_id . "<br>";
echo "Location: " . $location . "<br>";
echo "Mode: " . $mode . "<br>";

// Get subject name
$subject_name = '';
$subject_sql = "SELECT subject_name FROM subjects WHERE subject_id = ?";
$subject_stmt = $conn->prepare($subject_sql);
$subject_stmt->bind_param("i", $subject_id);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();
if ($subject_result->num_rows > 0) {
    $subject_row = $subject_result->fetch_assoc();
    $subject_name = $subject_row['subject_name'];
    echo "Subject Name: " . $subject_name . "<br>";
} else {
    echo "❌ Subject not found for ID: " . $subject_id . "<br>";
}

// Build search query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($subject_name)) {
    $where_conditions[] = "(t.subjects_taught LIKE ? OR cc.courses_offered LIKE ?)";
    $params[] = "%$subject_name%";
    $params[] = "%$subject_name%";
    $types .= 'ss';
}

if (!empty($location)) {
    $where_conditions[] = "(t.location LIKE ? OR cc.location LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $types .= 'ss';
}

if (!empty($mode)) {
    $where_conditions[] = "(t.teaching_mode = ? OR cc.teaching_mode = ? OR t.teaching_mode = 'both' OR cc.teaching_mode = 'both')";
    $params[] = $mode;
    $params[] = $mode;
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

echo "<h2>Generated Query</h2>";
echo "Where clause: " . $where_clause . "<br>";
echo "Parameters: " . print_r($params, true) . "<br>";
echo "Types: " . $types . "<br>";

// Build the full tutor query
$tutor_sql = "SELECT t.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count 
               FROM tutors t 
               LEFT JOIN reviews r ON t.tutor_id = r.tutor_id 
               $where_clause AND t.approved = 'approved' AND t.availability_status = 'available'
               GROUP BY t.tutor_id 
               ORDER BY avg_rating DESC, t.experience_years DESC";

echo "<h2>Full SQL Query</h2>";
echo "<pre>" . htmlspecialchars($tutor_sql) . "</pre>";

// Execute the query
$tutor_stmt = $conn->prepare($tutor_sql);
if ($tutor_stmt) {
    if (!empty($params)) {
        $tutor_stmt->bind_param($types, ...$params);
    }
    $tutor_stmt->execute();
    $tutor_result = $tutor_stmt->get_result();
    
    echo "<h2>Query Results</h2>";
    echo "Tutors found: " . $tutor_result->num_rows . "<br>";
    
    if ($tutor_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Subjects</th><th>Location</th><th>Mode</th><th>Approved</th><th>Available</th></tr>";
        while ($row = $tutor_result->fetch_assoc()) {
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
        echo "❌ No tutors found<br>";
    }
} else {
    echo "❌ Query preparation failed<br>";
}

// Test without mode filter to see if subject/location work
echo "<h2>Test Without Mode Filter</h2>";
$where_conditions_no_mode = [];
$params_no_mode = [];
$types_no_mode = '';

if (!empty($subject_name)) {
    $where_conditions_no_mode[] = "(t.subjects_taught LIKE ? OR cc.courses_offered LIKE ?)";
    $params_no_mode[] = "%$subject_name%";
    $params_no_mode[] = "%$subject_name%";
    $types_no_mode .= 'ss';
}

if (!empty($location)) {
    $where_conditions_no_mode[] = "(t.location LIKE ? OR cc.location LIKE ?)";
    $params_no_mode[] = "%$location%";
    $params_no_mode[] = "%$location%";
    $types_no_mode .= 'ss';
}

$where_clause_no_mode = !empty($where_conditions_no_mode) ? 'WHERE ' . implode(' AND ', $where_conditions_no_mode) : '';

$tutor_sql_no_mode = "SELECT t.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count 
                      FROM tutors t 
                      LEFT JOIN reviews r ON t.tutor_id = r.tutor_id 
                      $where_clause_no_mode AND t.approved = 'approved' AND t.availability_status = 'available'
                      GROUP BY t.tutor_id 
                      ORDER BY avg_rating DESC, t.experience_years DESC";

echo "Query without mode: <pre>" . htmlspecialchars($tutor_sql_no_mode) . "</pre>";

$tutor_stmt_no_mode = $conn->prepare($tutor_sql_no_mode);
if ($tutor_stmt_no_mode) {
    if (!empty($params_no_mode)) {
        $tutor_stmt_no_mode->bind_param($types_no_mode, ...$params_no_mode);
    }
    $tutor_stmt_no_mode->execute();
    $tutor_result_no_mode = $tutor_stmt_no_mode->get_result();
    
    echo "Tutors found without mode filter: " . $tutor_result_no_mode->num_rows . "<br>";
}

// Check what teaching modes exist in database
echo "<h2>Check Teaching Modes in Database</h2>";
$mode_check_sql = "SELECT DISTINCT teaching_mode FROM tutors WHERE teaching_mode IS NOT AND teaching_mode != ''";
$mode_result = $conn->query($mode_check_sql);
if ($mode_result) {
    echo "Available teaching modes:<br>";
    while ($row = $mode_result->fetch_assoc()) {
        echo "- " . htmlspecialchars($row['teaching_mode']) . "<br>";
    }
}

$conn->close();
?>
