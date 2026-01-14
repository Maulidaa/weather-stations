<?php

// Accept both GET and POST requests
$data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    // Handle form-encoded POST
    $data = $_POST;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(file_get_contents('php://input'))) {
    // Handle JSON POST
    $json = json_decode(file_get_contents('php://input'), true);
    $data = $json;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET (backward compatibility)
    $data = $_GET;
}

// Validate all required parameters are present
$required_params = ['temp', 'humi', 'pressure', 'altitude', 'wind', 'rain'];
$missing_params = [];

foreach ($required_params as $param) {
    if (!isset($data[$param])) {
        $missing_params[] = $param;
    }
}

if (!empty($missing_params)) {
    http_response_code(400);
    echo "Missing required parameters: " . implode(', ', $missing_params);
    exit;
}

// Sanitize and validate input
$temperature_dht = floatval($data["temp"]);
$humidity = floatval($data["humi"]);
$pressure = floatval($data["pressure"]);
$altitude = floatval($data["altitude"]);
$windSpeed = floatval($data["wind"]);
$rainState = floatval($data["rain"]);

// Validate sensor ranges (optional - adjust based on your sensors)
if ($humidity < 0 || $humidity > 100) {
    http_response_code(400);
    echo "Invalid humidity value: " . $humidity;
    exit;
}

if ($temperature_dht < -40 || $temperature_dht > 80) {
    http_response_code(400);
    echo "Invalid temperature value: " . $temperature_dht;
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "weather_stations";

// Create MySQL connection from PHP to MySQL server
$connection = new mysqli($servername, $username, $password, $database_name);

// Check connection
if ($connection->connect_error) {
    http_response_code(500);
    die("MySQL connection failed: " . $connection->connect_error);
}

// Use prepared statements to prevent SQL injection
$stmt = $connection->prepare("INSERT INTO weather_data (temperature_dht, humidity, pressure, altitude, windSpeed, rain_state) VALUES (?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    die("Prepare failed: " . $connection->error);
}

$stmt->bind_param("dddddd", $temperature_dht, $humidity, $pressure, $altitude, $windSpeed, $rainState);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Data inserted successfully",
        "timestamp" => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error inserting data: " . $stmt->error
    ]);
}

$stmt->close();
$connection->close();
?>