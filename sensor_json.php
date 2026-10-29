<?php
// EE470 Database Assignment - Part 2
// Display sensor readings in JSON format

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

// Connect to MySQL database
require_once dirname(__DIR__, 2) . '/ee470_config.php';

// Select one registered sensor node
$node = 'node_1';

// Retrieve all readings for the selected node
$sql = "SELECT node_name, time_received,
               temperature, humidity
        FROM sensor_data
        WHERE node_name = ?
        ORDER BY time_received ASC";

$stmt = $db->prepare($sql);
$stmt->execute([$node]);

// Fetch database records
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert numeric values to JSON numbers
foreach ($data as &$row) {
    $row['temperature'] = (float)$row['temperature'];
    $row['humidity'] = (float)$row['humidity'];
}
unset($row);

// Convert records to JSON and display
echo json_encode(
    $data,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);
?>
