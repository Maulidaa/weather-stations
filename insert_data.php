<?php
if(isset($_GET["temperature"]) && isset($_GET["humidity"]) && isset($_GET["pressure"])) {
   $temperature = $_GET["temperature"]; 

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

   $sql = "INSERT INTO temperature (value) VALUES ($temperature)";
   $sql = "INSERT INTO humidity (value) VALUES ($humidity)";

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