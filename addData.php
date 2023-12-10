<?php
    require_once('connection.php');
    // get data
    $soil_moisture = $_REQUEST['soil_moisture'] ?? null;
    $humidity = $_REQUEST['humidity'] ?? null;
    $temperature = $_REQUEST['temperature'] ?? null;
    try {
        $datetime = date('Y-m-d H:i:s');
        $sql = "INSERT INTO onlinemonitoring VALUES (null, $soil_moisture, $humidity, $temperature, '$datetime')";
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            throw new Exception($conn->error . '; SQL: ' . $sql);
        }
        mysqli_close($conn);
        echo 'Success';
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    die;
?>