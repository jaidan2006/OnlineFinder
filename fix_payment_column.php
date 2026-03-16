<?php
require_once 'includes/config.php';

echo "<h1>Add Payment Details Column to Bookings Table</h1>";

// Check if column already exists
$check_sql = "SHOW COLUMNS FROM bookings LIKE 'payment_details'";
$result = $conn->query($check_sql);

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ payment_details column already exists in bookings table!</p>";
    echo "<p><a href='center_dashboard.php'>Go to Dashboard</a></p>";
} else {
    echo "<p style='color: orange;'>⚠️ payment_details column does not exist. Adding it now...</p>";
    
    // Add the column
    $alter_sql = "ALTER TABLE bookings ADD COLUMN payment_details TEXT NULL DEFAULT NULL AFTER status";
    
    if ($conn->query($alter_sql)) {
        echo "<p style='color: green;'>✅ Successfully added payment_details column to bookings table!</p>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ul>";
        echo "<li>Refresh your browser</li>";
        echo "<li>Try approving a booking again</li>";
        echo "<li>Payment details will now be saved and visible to students</li>";
        echo "</ul>";
        echo "<p><a href='center_dashboard.php'>Go to Dashboard</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding column: " . $conn->error . "</p>";
        echo "<p><strong>Manual SQL command:</strong></p>";
        echo "<code>ALTER TABLE bookings ADD COLUMN payment_details TEXT NULL DEFAULT NULL AFTER status;</code>";
    }
}

// Show current table structure
echo "<h2>Current Bookings Table Structure:</h2>";
$describe_sql = "DESCRIBE bookings";
$result = $conn->query($describe_sql);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $highlight = ($row['Field'] == 'payment_details') ? "style='background-color: #d4edda;'" : "";
        echo "<tr " . $highlight . ">";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error describing table: " . $conn->error . "</p>";
}

$conn->close();
?>
