<?php
    require_once('connection.php');
    // date range
    $startDate = '2023-08-11';
    $endDate = '2023-09-02';
    // generate data based on date range
    for ($i = $startDate; $i <= $endDate; $i = date('Y-m-d', strtotime($i . ' +1 day'))) {
        $rand_minute = str_pad(rand(0, 5), 2, '0', STR_PAD_LEFT);
        $rand_seconds = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $startDateTime = $i . ' 21:' . $rand_minute . ':' . $rand_seconds;
        $endDateTime = $i . ' 22:00:00';
        for ($j = $startDateTime; $j <= $endDateTime; $j = date('Y-m-d H:i:s', strtotime($j . ' +3 seconds'))) {
            $soil_moisture = rand(990, 1000);
            $humidity = rand(71, 74);
            $temperature = rand(30, 32);
            $sql = "INSERT INTO onlinemonitoring VALUES (null, $soil_moisture, $humidity, $temperature, '$j')";
            $res = mysqli_query($conn, $sql);
            if (!$res) {
                echo 'ERROR: ' . $conn->error . "\n";
            } else {
                echo 'NEW RECORD: ' . $soil_moisture . ', ' . $humidity . ', ' . $temperature . ', ' . $j . "\n";
            }
        }
    }
?>