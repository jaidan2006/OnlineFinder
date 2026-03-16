<?php
require_once 'includes/config.php';

$sql = 'DESCRIBE tutors';
$result = $conn->query($sql);

echo "Tutors Table Structure:\n";
echo "========================\n";

while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . PHP_EOL;
}
?>
