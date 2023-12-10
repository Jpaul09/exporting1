<?php
require_once('connection.php');

// Get request data
$start_date = date('Y-m-d H:i:s', strtotime('00:00:00'));
if (isset($_REQUEST['start_date'])) {
    $start_date = date('Y-m-d H:i:s', strtotime($_REQUEST['start_date']));
}

$end_date = date('Y-m-d H:i:s', strtotime('23:59:59'));
if (isset($_REQUEST['end_date'])) {
    $end_date = date('Y-m-d H:i:s', strtotime($_REQUEST['end_date']));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Indoor Farming</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2 style="text-align: center; text-shadow: 2px 2px white; font-size: 50px;">INDOOR FARMING</h2>
    <form name="frmDASHBOARD" method="POST">
        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="datetime-local" id="start_date" name="start_date" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($start_date)); ?>" />
        </div>
        <div class="form-group">
            <label for="end_date">End Date:</label>
            <input type="datetime-local" id="end_date" name="end_date" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($end_date)); ?>" />
        </div>
        <button type="submit" class="btn btn-primary" id="btnGO" style="width:70px">GO</button>
    </form>
</div>

<?php
// Get and show latest data
$sql = "SELECT * FROM onlinemonitoring ORDER BY created_at DESC LIMIT 1";
$res = mysqli_query($conn, $sql);
$latestDataDateTime = null;
$latestDataSoilMoisture = null;
$latestDataHumidity = null;
$latestDataTemperature = null;
if ($res) {
    $data = $res->fetch_assoc();
    $latestDataDateTime = $data['created_at'];
    $latestDataSoilMoisture = $data['soil_moisture'];
    $latestDataHumidity = $data['humidity'];
    $latestDataTemperature = $data['temperature'];
} else {
    echo $conn->error;
}
?>

<?php if ($res): ?>
<div class="container">
    <h2>Latest Data</h2>
    <p>Date-Time: <?php echo $latestDataDateTime; ?></p>
    <p>Soil Moisture: <?php echo $latestDataSoilMoisture . ' wfv'; ?></p>
    <p>Humidity: <?php echo $latestDataHumidity . '%'; ?></p>
    <p>Temperature: <?php echo $latestDataTemperature . ' °C'; ?></p>
</div>
<?php endif; ?>

<?php
// Get and show average data based on datetime range
$sql = "SELECT AVG(soil_moisture) as `soil_moisture`,
                AVG(humidity) as `humidity`,
                AVG(temperature) as `temperature`
        FROM onlinemonitoring
        WHERE created_at BETWEEN '$start_date' AND '$end_date'";
$res = mysqli_query($conn, $sql);
$averageDataSoilMoisture = null;
$averageDataHumidity = null;
$averageDataTemperature = null;
if ($res) {
    $data = $res->fetch_assoc();
    $averageDataSoilMoisture = round($data['soil_moisture'], 2);
    $averageDataHumidity = round($data['humidity'], 2);
    $averageDataTemperature = round($data['temperature'], 2);
} else {
    echo $conn->error;
}
?>
<?php if (!is_null($averageDataSoilMoisture)): ?>
<div class="container">
    <h2>Average Data (from <?php echo date('Y-m-d H:i', strtotime($start_date)) . ' to ' . date('Y-m-d H:i', strtotime($end_date)); ?>)</h2>
    <p>Soil Moisture: <?php echo $averageDataSoilMoisture . ' wfv'; ?></p>
    <p>Humidity: <?php echo $averageDataHumidity . '%'; ?></p>
    <p>Temperature: <?php echo $averageDataTemperature . ' °C'; ?></p>
</div>
<?php endif; ?>

<?php
// Show bar graph based on the provided datetime range
$data = array();
$startDate = date('Y-m-d', strtotime($start_date));
$endDate = date('Y-m-d', strtotime($end_date));
if ($startDate == $endDate) {
    // Hourly
    // Get max data for the day
    $startDateTime = $startDate . ' 00:00:00';
    $endDateTime = $endDate . ' 23:59:59';
    $sql = "SELECT MAX(soil_moisture) as `max_soil_moisture`,
                MAX(temperature) as `max_temperature`
            FROM onlinemonitoring
            WHERE created_at BETWEEN '$startDateTime' AND '$endDateTime'";
    $res = mysqli_query($conn, $sql);
    $row = $res->fetch_assoc();
    $maxSoilMoisture = $row['max_soil_moisture'] ?? 0;
    $maxTemperature = $row['max_temperature'] ?? 0;
    if (!is_null($row['max_soil_moisture'])) {
        for ($i = 0; $i <= 23; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $startDateTime =  $startDate . ' ' . $hour . ':00:00';
            $endDateTime = $startDate . ' ' . $hour . ":59:59";
            // Average data within an hour
            $sql = "SELECT AVG(soil_moisture) as `soil_moisture`,
                            AVG(humidity) as `humidity`,
                            AVG(temperature) as `temperature`
                    FROM onlinemonitoring
                    WHERE created_at BETWEEN '$startDateTime' AND '$endDateTime'";
            $res = mysqli_query($conn, $sql);
            if ($res) {
                $row = $res->fetch_assoc();
                $data[] = array(
                    'label' => $startDateTime,
                    'soil_moisture' => round($row['soil_moisture'], 2) . ' wfv',
                    'humidity' => round($row['humidity'], 2) . '%',
                    'temperature' => round($row['temperature'], 2) . '°C',
                    'soil_moisture_width' => $row['soil_moisture'] > 0 ? round($row['soil_moisture'] / $maxSoilMoisture * 100, 2) : 100,
                    'humidity_width' => ($row['soil_moisture'] > 0 ? $row['humidity'] : 100),
                    'temperature_width' => $row['temperature'] > 0 ? round($row['temperature'] / $maxTemperature * 100, 2) : 100,
                    'soil_moisture_color' => $row['soil_moisture'] > 0 ? 'green' : 'white',
                    'humidity_color' => $row['humidity'] > 0 ? 'orange' : 'white',
                    'temperature_color' => $row['temperature'] > 0 ? 'yellow' : 'white',
                );
            } else {
                $data[] = array(
                    'label' => $startDateTime,
                    'soil_moisture' => '0 wfv',
                    'humidity' => '0%',
                    'temperature' => '0 °C',
                    'soil_moisture_width' => 100,
                    'humidity_width' => 100,
                    'temperature_width' => 100,
                    'soil_moisture_color' => 'white',
                    'humidity_color' => 'white',
                    'temperature_color' => 'white',
                );
            }
        }
    }
} else {
    // Daily
    // Get max data within the data range
    $startDateTime = $startDate . ' 00:00:00';
    $endDateTime = $endDate . ' 23:59:59';
    $sql = "SELECT MAX(soil_moisture) as `max_soil_moisture`,
            MAX(temperature) as `max_temperature`
    FROM onlinemonitoring
    WHERE created_at BETWEEN '$startDateTime' AND '$endDateTime'";
    $res = mysqli_query($conn, $sql);
    $row = $res->fetch_assoc();
    $maxSoilMoisture = $row['max_soil_moisture'] ?? 0;
    $maxTemperature = $row['max_temperature'] ?? 0;
    if (!is_null($row['max_soil_moisture'])) {
        for ($i = $startDate; $i <= $endDate; $i = date('Y-m-d', strtotime($i . ' + 1 day'))) {
            $startDateTime = $i . ' 00:00:00';
            $endDateTime = $i . ' 23:59:59';
            // Average data within a day
            $sql = "SELECT AVG(soil_moisture) as `soil_moisture`,
                            AVG(humidity) as `humidity`,
                            AVG(temperature) as `temperature`
                    FROM onlinemonitoring
                    WHERE created_at BETWEEN '$startDateTime' AND '$endDateTime'";
            $res = mysqli_query($conn, $sql);
            if ($res) {
                $row = $res->fetch_assoc();
                $data[] = array(
                    'label' => $i,
                    'soil_moisture' => round($row['soil_moisture'], 2) . ' wfv',
                    'humidity' => round($row['humidity'], 2) . '%',
                    'temperature' => round($row['temperature'], 2) . ' °C',
                    'soil_moisture_width' => $row['soil_moisture'] > 0 ? round($row['soil_moisture'] / $maxSoilMoisture * 100, 2) : 100,
                    'humidity_width' => ($row['soil_moisture'] > 0 ? $row['humidity'] : 100),
                    'temperature_width' => $row['temperature'] > 0 ? round($row['temperature'] / $maxTemperature * 100, 2) : 100,
                    'soil_moisture_color' => $row['soil_moisture'] > 0 ? 'green' : 'white',
                    'humidity_color' => $row['humidity'] > 0 ? 'orange' : 'white',
                    'temperature_color' => $row['temperature'] > 0 ? 'yellow' : 'white',
                );
            } else {
                $data[] = array(
                    'label' => $i,
                    'soil_moisture' => '0 wfv',
                    'humidity' => '0%',
                    'temperature' => '0 °C',
                    'soil_moisture_width' => 100,
                    'humidity_width' => 100,
                    'temperature_width' => 100,
                    'soil_moisture_color' => 'white',
                    'humidity_color' => 'white',
                    'temperature_color' => 'white',
                );
            }
        }
    }
}
?>

<div class="container">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 20%; text-align: center">Date</th>
                <th style="text-align: center">Graph</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($data as $d): ?>
        <tr>
    <td style="width: 30%;"><?php echo $d['label']; ?></td>
    <td>
        <div class="graph" style="background-color:   <?php echo $d['soil_moisture_color']; ?>; width: <?php echo $d['soil_moisture_width'] ?>%">
            
        <div class="centered-text">Soil Moisture<?php echo $d['soil_moisture']; ?></div>
        </div>
        <div class="graph" style="background-color: <?php echo $d['humidity_color']; ?>; width: <?php echo $d['humidity_width'] ?>%">
            <div class="centered-text">Humidity<?php echo $d['humidity']; ?></div>
        </div>
        <div class="graph" style="background-color: <?php echo $d['temperature_color']; ?>; width: <?php echo $d['temperature_width'] ?>%">
            <div class="centered-text">Temperature<?php echo $d['temperature']; ?></div>
        </div>
    </td>
</tr>
    <?php endforeach; ?>
</tbody>
    </table>
</div>
</body>
</html>
