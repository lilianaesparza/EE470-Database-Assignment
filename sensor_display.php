<?php
// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Connect to MySQL database
require_once dirname(__DIR__, 2) . '/ee470_config.php';

// NEW CODE FOR SECTION 3.2 STARTS HERE

// Get selected sensor node from dropdown
$selectedNode = $_GET['node'] ?? 'node_1';

// Check that selected node is registered
$stmtCheck = $db->prepare(
    "SELECT node_name FROM sensor_register WHERE node_name = ?"
);
$stmtCheck->execute([$selectedNode]);

if (!$stmtCheck->fetch()) {
    $selectedNode = 'node_1';
}

// Retrieve registered sensor nodes
$sqlNodes = "SELECT node_name, manufacturer,
                    longitude, latitude
             FROM sensor_register
             ORDER BY node_name ASC";

$stmtNodes = $db->query($sqlNodes);
$nodes = $stmtNodes->fetchAll(PDO::FETCH_ASSOC);

// Retrieve sensor readings
// Sort by node name first, then time
$sqlData = "SELECT node_name, time_received,
                   temperature, humidity
            FROM sensor_data
            ORDER BY node_name ASC,
                     time_received ASC";

$stmtData = $db->query($sqlData);
$readings = $stmtData->fetchAll(PDO::FETCH_ASSOC);

 // Calculate statistics for the selected sensor node
$sqlAverage = "SELECT
    AVG(temperature) AS avg_temperature,
    AVG(humidity) AS avg_humidity,
    MAX(temperature) AS max_temperature,
    MIN(humidity) AS min_humidity
    FROM sensor_data
    WHERE node_name = ?";

// Prepare SQL query
$stmtAverage = $db->prepare($sqlAverage);

// Execute query for selected node
$stmtAverage->execute([$selectedNode]);

// Retrieve statistics
$average = $stmtAverage->fetch(PDO::FETCH_ASSOC);



 // Retrieve temperature readings for selected node
$sqlChart = "SELECT time_received, temperature
             FROM sensor_data
             WHERE node_name = ?
             ORDER BY time_received ASC";

// Prepare and execute query
$stmtChart = $db->prepare($sqlChart);
$stmtChart->execute([$selectedNode]);

// Fetch readings
$chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);


// Prepare data for Chart.js
$chartTimes = [];
$chartTemps = [];

foreach ($chartData as $row) {
    $chartTimes[] = $row['time_received'];
    $chartTemps[] = (float)$row['temperature'];
}

// Escape values before displaying in HTML
function safe($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<!-- Chart.js library for plotting sensor data -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>EE470 Sensor Database</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fb;
            margin: 30px;
            color: #243247;
        }

        h1, h2 {
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto 40px;
            background-color: white;
        }

        th, td {
            border: 1px solid #cccccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #78a85a;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #eef5e8;
        }
    </style>
</head>

<body>

<h1>Welcome to SSU IoT Lab</h1>

<h2>Registered Sensor Nodes</h2>

<table>
    <tr>
        <th>Node Name</th>
        <th>Manufacturer</th>
        <th>Longitude</th>
        <th>Latitude</th>
    </tr>

    <?php foreach ($nodes as $node): ?>
    <tr>
        <td><?= safe($node['node_name']) ?></td>
        <td><?= safe($node['manufacturer']) ?></td>
        <td><?= safe($node['longitude']) ?></td>
        <td><?= safe($node['latitude']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Data Received</h2>

<table>
    <tr>
        <th>Node Name</th>
        <th>Time Received</th>
        <th>Temperature</th>
        <th>Humidity</th>
    </tr>

    <?php foreach ($readings as $row): ?>
    <tr>
        <td><?= safe($row['node_name']) ?></td>
        <td><?= safe($row['time_received']) ?></td>
        <td><?= safe($row['temperature']) ?></td>
        <td><?= safe($row['humidity']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Dropdown menu for selecting a sensor node -->
<h2>Select a Sensor Node</h2>

<form method="GET" style="text-align:center; margin:20px;">
    <select name="node" onchange="this.form.submit()">

        <?php foreach ($nodes as $node): ?>
            <option
                value="<?= safe($node['node_name']) ?>"
                <?= $selectedNode === $node['node_name']
                    ? 'selected' : '' ?>>
                <?= safe($node['node_name']) ?>
            </option>
        <?php endforeach; ?>

    </select>
</form>

<h2>Sensor Statistics for <?= safe($selectedNode) ?></h2>


<p style="text-align:center;">
    Average Temperature:
    <?= $average['avg_temperature'] !== null
        ? number_format($average['avg_temperature'], 2) . ' °C'
        : 'No data' ?>
</p>

<p style="text-align:center;">
    Average Humidity:
    <?= $average['avg_humidity'] !== null
        ? number_format($average['avg_humidity'], 2) . ' %'
        : 'No data' ?>
</p>
<p style="text-align:center;">
    Maximum Temperature:
    <?= $average['max_temperature'] !== null
        ? number_format($average['max_temperature'], 2) . ' °C'
        : 'No data' ?>
</p>

<p style="text-align:center;">
    Minimum Humidity:
    <?= $average['min_humidity'] !== null
        ? number_format($average['min_humidity'], 2) . ' %'
        : 'No data' ?>
</p>

<!-- Temperature graph for node_1 -->
<h2>Sensor Node <?= safe($selectedNode) ?></h2>

<div style="width:90%; max-width:900px; margin:30px auto;">
    <canvas id="temperatureChart"></canvas>
</div>

<script>
const chartTimes = <?= json_encode($chartTimes) ?>;
const chartTemps = <?= json_encode($chartTemps) ?>;

const ctx = document.getElementById('temperatureChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartTimes,
        datasets: [{
            label: 'Temperature (°C)',
            data: chartTemps,
            backgroundColor: 'rgba(75, 192, 75, 0.7)',
            borderColor: 'rgba(75, 192, 75, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: <?= json_encode('Sensor Node ' . $selectedNode) ?>
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Time'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Temperature (°C)'
                }
            }
        }
    }
});
</script>

</body>
</html>
