<?php
// EE470 Database Assignment - Part 2
// Insert sensor readings using the GET method

header("Content-Type: text/plain; charset=utf-8");
header("Cache-Control: no-store");

require_once dirname(__DIR__, 2) . '/ee470_config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit("Error: Only GET requests are allowed.");
}

// Read parameters from the URL

 // Read regular GET parameters

 // Read regular GET parameters
$params = $_GET;

// Support both Base64 URL formats
$encodedMessage = null;

// Format 1: ?encoded=BASE64_MESSAGE
if (isset($_GET['encoded'])) {
    $encodedMessage = $_GET['encoded'];
}

// Format 2: ?BASE64_MESSAGE
elseif (!empty($_SERVER['QUERY_STRING'])
    && count($_GET) === 1
    && !isset($_GET['nodeId'])) {

    $encodedMessage = $_SERVER['QUERY_STRING'];
}

// Decode the Base64 message
if ($encodedMessage !== null) {
    $decoded = base64_decode($encodedMessage, true);

    if ($decoded === false) {
        http_response_code(400);
        exit("Error: Invalid Base64 message.");
    }

    // Extract parameters from decoded message
    parse_str($decoded, $params);
}


// Extract sensor values
$node = $params['nodeId'] ?? '';
$temp = $params['nodeTemp'] ?? null;
$humidity = $params['nodeHumidity'] ?? null;
$time = $params['timeReceived'] ?? null;


// Check required parameters
if ($node === '' || $temp === null || $humidity === null) {
    http_response_code(400);
    exit("Error: Missing required sensor data.");
}

// Validate numeric readings
if (!is_numeric($temp) || !is_numeric($humidity)) {
    http_response_code(400);
    exit("Error: Temperature and humidity must be numbers.");
}

 // Check temperature range: -10 to 100 Celsius
if ($temp < -10 || $temp > 100) {
    http_response_code(400);
    exit("Error: Temperature must be between -10 and 100 Celsius.");
}

// Check humidity range: 0 to 100 percent
if ($humidity < 0 || $humidity > 100) {
    http_response_code(400);
    exit("Error: Humidity must be between 0 and 100 percent.");
}

// Check maximum node name length
if (strlen($node) > 10) {
    http_response_code(400);
    exit("Error: Node name cannot exceed 10 characters.");
}

// Check whether the node is registered
$stmt = $db->prepare(
    "SELECT node_name FROM sensor_register
     WHERE node_name = ?"
);
$stmt->execute([$node]);

if (!$stmt->fetch()) {
    http_response_code(400);
    exit("Error: Node is not registered.");
}

// Validate the supplied timestamp
if ($time !== null && $time !== '') {
    $time = str_replace('T', ' ', $time);

    $date = DateTimeImmutable::createFromFormat(
        '!Y-m-d H:i:s',
        $time
    );

    $errors = DateTimeImmutable::getLastErrors();

    if (!$date ||
        ($errors !== false &&
        ($errors['warning_count'] > 0 ||
         $errors['error_count'] > 0)) ||
        $date->format('Y-m-d H:i:s') !== $time) {
        http_response_code(400);
        exit("Error: Invalid timestamp.");
    }
} else {
    $time = null;
}

try {
    // Insert the sensor reading
    // Database timestamp is used when time is omitted
    $sql = "INSERT INTO sensor_data
            (node_name, time_received,
             temperature, humidity)
            VALUES (?, COALESCE(?, NOW()), ?, ?)";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        $node,
        $time,
        $temp,
        $humidity
    ]);

    echo "Node: " . $node . "\n";
    echo "Temperature: " . $temp . "\n";
    echo "Humidity: " . $humidity . "\n";
    echo "New record created successfully!";

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        http_response_code(409);
        exit("Error: Duplicate timestamp or constraint violation.");
    }

    error_log($e->getMessage());
    http_response_code(500);
    exit("Error: Unable to insert data.");
}
?>
