<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "weather_stations";

$connection = new mysqli($servername, $username, $password, $database_name);

if ($connection->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

// Get the requested format (latest, hourly, daily)
$format = isset($_GET['format']) ? $_GET['format'] : 'latest';
$hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;

try {
    switch($format) {
        case 'latest':
            // Get latest data
            $sql = "SELECT * FROM weather_data ORDER BY id DESC LIMIT 1";
            $result = $connection->query($sql);
            $data = $result->fetch_assoc();
            
            echo json_encode([
                "status" => "success",
                "data" => $data,
                "timestamp" => date('Y-m-d H:i:s')
            ]);
            break;

        case 'hourly':
            // Get hourly average data
            $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour,
                AVG(temperature_dht) as temperature_dht,
                AVG(humidity) as humidity,
                AVG(pressure) as pressure,
                AVG(altitude) as altitude,
                AVG(windSpeed) as windSpeed,
                SUM(rain_state) as rain_state,
                COUNT(*) as measurements
            FROM weather_data 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')
            ORDER BY hour DESC";
            
            $result = $connection->query($sql);
            $data = array();
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            echo json_encode([
                "status" => "success",
                "format" => "hourly",
                "hours" => $hours,
                "data" => $data,
                "count" => count($data),
                "timestamp" => date('Y-m-d H:i:s')
            ]);
            break;

        case 'daily':
            // Get daily average data
            $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m-%d') as date,
                AVG(temperature_dht) as temperature_dht,
                MAX(temperature_dht) as max_temperature,
                MIN(temperature_dht) as min_temperature,
                AVG(humidity) as humidity,
                AVG(pressure) as pressure,
                MAX(windSpeed) as max_windSpeed,
                SUM(rain_state) as rain_state,
                COUNT(*) as measurements
            FROM weather_data 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY date DESC";
            
            $result = $connection->query($sql);
            $data = array();
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            echo json_encode([
                "status" => "success",
                "format" => "daily",
                "data" => $data,
                "count" => count($data),
                "timestamp" => date('Y-m-d H:i:s')
            ]);
            break;

        case 'stats':
            // Get statistics
            $sql = "SELECT 
                AVG(temperature_dht) as avg_temperature,
                MAX(temperature_dht) as max_temperature,
                MIN(temperature_dht) as min_temperature,
                AVG(humidity) as avg_humidity,
                MAX(humidity) as max_humidity,
                MIN(humidity) as min_humidity,
                AVG(pressure) as avg_pressure,
                AVG(altitude) as avg_altitude,
                MAX(windSpeed) as max_windSpeed,
                AVG(windSpeed) as avg_windSpeed,
                SUM(rain_state) as total_rain,
                COUNT(*) as total_records,
                MIN(created_at) as first_record,
                MAX(created_at) as last_record
            FROM weather_data 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL $hours HOUR)";
            
            $result = $connection->query($sql);
            $data = $result->fetch_assoc();
            
            echo json_encode([
                "status" => "success",
                "format" => "statistics",
                "period_hours" => $hours,
                "data" => $data,
                "timestamp" => date('Y-m-d H:i:s')
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid format. Use: latest, hourly, daily, or stats",
                "available_formats" => [
                    "latest" => "Get latest data",
                    "hourly" => "Get hourly average (default 24 hours, adjustable with ?hours=N)",
                    "daily" => "Get daily average (last 30 days)",
                    "stats" => "Get statistics (default 24 hours, adjustable with ?hours=N)"
                ]
            ]);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$connection->close();
?>