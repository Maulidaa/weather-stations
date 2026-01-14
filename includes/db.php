<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "weather_stations";

$connection = new mysqli($servername, $username, $password, $database_name);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get latest data
$sql_latest = "SELECT * FROM weather_data ORDER BY id DESC LIMIT 1";
$result_latest = $connection->query($sql_latest);
$latest_data = $result_latest->fetch_assoc();

// Get last 24 hours data
$sql_24h = "SELECT * FROM weather_data WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY created_at DESC";
$result_24h = $connection->query($sql_24h);

// Get statistics
$sql_stats = "SELECT 
    AVG(temperature_dht) as avg_temp,
    MAX(temperature_dht) as max_temp,
    MIN(temperature_dht) as min_temp,
    AVG(humidity) as avg_humidity,
    AVG(pressure) as avg_pressure,
    MAX(windSpeed) as max_wind,
    SUM(rain_state) as total_rain,
    COUNT(*) as total_records
FROM weather_data WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$result_stats = $connection->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Get hourly data for chart
$sql_hourly = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour,
    AVG(temperature_dht) as avg_temp,
    AVG(humidity) as avg_humidity,
    AVG(pressure) as avg_pressure
FROM weather_data WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')
ORDER BY hour DESC";
$result_hourly = $connection->query($sql_hourly);

$hourly_data = array();
while($row = $result_hourly->fetch_assoc()) {
    $hourly_data[] = $row;
}
?>
