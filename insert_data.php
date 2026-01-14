<?php
if(isset($_GET["temperature_dht"]) && isset($_GET["humidity"]) && isset($_GET["pressure"]) && isset($_GET["temperature_bmp"]) && isset($_GET["altitude"]) && isset($_GET["windSpeed"]) && isset($_GET["statoPrecedente"])) {
   $temperature_dht = $_GET["temperature_dht"]; 
   $humidity = $_GET["humidity"];
   $pressure = $_GET["pressure"];
   $temperature_bmp = $_GET["temperature_bmp"];
   $altitude = $_GET["altitude"];
   $windSpeed = $_GET["windSpeed"];
   $statoPrecedente = $_GET["statoPrecedente"];

   $servername = "localhost";
   $username = "root";
   $password = "";
   $database_name = "weather_stations";

   // Create MySQL connection fom PHP to MySQL server
   $connection = new mysqli($servername, $username, $password, $database_name);
   // Check connection
   if ($connection->connect_error) {
      die("MySQL connection failed: " . $connection->connect_error);
   }

   $sql = "INSERT INTO dht (temperature) VALUES ($temperature_dht)";
   $sql = "INSERT INTO dht (humidity) VALUES ($humidity)";
   $sql = "INSERT INTO bmp (pressure) VALUES ($pressure)";
   $sql = "INSERT INTO bmp (temperature) VALUES ($temperature_bmp)";
   $sql = "INSERT INTO bmp (altitude) VALUES ($altitude)";
   $sql = "INSERT INTO anemometer (value) VALUES ($windSpeed)";
   $sql = "INSERT INTO rain_gauge (state) VALUES ($statoPrecedente)";

   if ($connection->query($sql) === TRUE) {
      echo "New record created successfully";
   } else {
      echo "Error: " . $sql . " => " . $connection->error;
   }

   $connection->close();
} else {
   echo "temperature is not set in the HTTP request";
}
?>