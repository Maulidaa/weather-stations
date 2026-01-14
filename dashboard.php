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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Station Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        /* Latest Data Cards */
        .latest-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .card-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        .card-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-value {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .card-unit {
            color: #999;
            font-size: 0.9em;
        }

        .card.temp {
            border-top: 4px solid #FF6B6B;
        }

        .card.humidity {
            border-top: 4px solid #4ECDC4;
        }

        .card.pressure {
            border-top: 4px solid #45B7D1;
        }

        .card.wind {
            border-top: 4px solid #FFA502;
        }

        .card.rain {
            border-top: 4px solid #5D7DBE;
        }

        .card.altitude {
            border-top: 4px solid #95E1D3;
        }

        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .chart-box h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        /* Stats Section */
        .stats-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }

        .stats-container h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-label {
            color: #666;
            font-size: 0.85em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #667eea;
        }

        /* Data Table */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow-x: auto;
        }

        .table-container h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .last-update {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 0.9em;
            opacity: 0.8;
        }

        .no-data {
            text-align: center;
            color: #999;
            padding: 40px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }

            .latest-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .charts-container {
                grid-template-columns: 1fr;
            }

            .card-value {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌤️ Weather Station Dashboard</h1>
            <p>Real-time monitoring sistem cuaca</p>
        </div>

        <?php if ($latest_data): ?>
        <!-- Latest Data Cards -->
        <div class="latest-container">
            <div class="card temp">
                <div class="card-icon">🌡️</div>
                <div class="card-label">Suhu</div>
                <div class="card-value"><?php echo round($latest_data['temperature_dht'], 1); ?></div>
                <div class="card-unit">°C</div>
            </div>

            <div class="card humidity">
                <div class="card-icon">💧</div>
                <div class="card-label">Kelembaban</div>
                <div class="card-value"><?php echo round($latest_data['humidity'], 1); ?></div>
                <div class="card-unit">%</div>
            </div>

            <div class="card pressure">
                <div class="card-icon">🔽</div>
                <div class="card-label">Tekanan</div>
                <div class="card-value"><?php echo round($latest_data['pressure'] / 1000, 2); ?></div>
                <div class="card-unit">kPa</div>
            </div>

            <div class="card altitude">
                <div class="card-icon">⛰️</div>
                <div class="card-label">Ketinggian</div>
                <div class="card-value"><?php echo round($latest_data['altitude'], 1); ?></div>
                <div class="card-unit">m</div>
            </div>

            <div class="card wind">
                <div class="card-icon">💨</div>
                <div class="card-label">Kecepatan Angin</div>
                <div class="card-value"><?php echo round($latest_data['windSpeed'], 1); ?></div>
                <div class="card-unit">km/h</div>
            </div>

            <div class="card rain">
                <div class="card-icon">🌧️</div>
                <div class="card-label">Curah Hujan</div>
                <div class="card-value"><?php echo round($latest_data['rain_state'], 2); ?></div>
                <div class="card-unit">mm</div>
            </div>
        </div>

        <!-- Statistics (24 hours) -->
        <?php if ($stats): ?>
        <div class="stats-container">
            <h3>📊 Statistik 24 Jam Terakhir</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-label">Suhu Rata-rata</div>
                    <div class="stat-value"><?php echo round($stats['avg_temp'], 1); ?>°C</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Suhu Maksimal</div>
                    <div class="stat-value"><?php echo round($stats['max_temp'], 1); ?>°C</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Suhu Minimal</div>
                    <div class="stat-value"><?php echo round($stats['min_temp'], 1); ?>°C</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Kelembaban Rata-rata</div>
                    <div class="stat-value"><?php echo round($stats['avg_humidity'], 1); ?>%</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Tekanan Rata-rata</div>
                    <div class="stat-value"><?php echo round($stats['avg_pressure'] / 1000, 2); ?> kPa</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Angin Maksimal</div>
                    <div class="stat-value"><?php echo round($stats['max_wind'], 1); ?> km/h</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Curah Hujan</div>
                    <div class="stat-value"><?php echo round($stats['total_rain'], 2); ?> mm</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Pengukuran</div>
                    <div class="stat-value"><?php echo $stats['total_records']; ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Charts -->
        <div class="charts-container">
            <div class="chart-box">
                <h3>📈 Suhu (24 Jam)</h3>
                <canvas id="temperatureChart"></canvas>
            </div>
            <div class="chart-box">
                <h3>💧 Kelembaban (24 Jam)</h3>
                <canvas id="humidityChart"></canvas>
            </div>
            <div class="chart-box">
                <h3>🔽 Tekanan (24 Jam)</h3>
                <canvas id="pressureChart"></canvas>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <h3>📋 Data Terakhir</h3>
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu (°C)</th>
                        <th>Kelembaban (%)</th>
                        <th>Tekanan (kPa)</th>
                        <th>Ketinggian (m)</th>
                        <th>Angin (km/h)</th>
                        <th>Hujan (mm)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result_24h->data_seek(0);
                    $count = 0;
                    while($row = $result_24h->fetch_assoc()):
                        if($count >= 20) break;
                        $count++;
                    ?>
                    <tr>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><?php echo round($row['temperature_dht'], 1); ?></td>
                        <td><?php echo round($row['humidity'], 1); ?></td>
                        <td><?php echo round($row['pressure'] / 1000, 2); ?></td>
                        <td><?php echo round($row['altitude'], 1); ?></td>
                        <td><?php echo round($row['windSpeed'], 1); ?></td>
                        <td><?php echo round($row['rain_state'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="last-update">
            Data terakhir diperbarui: <strong><?php echo $latest_data['created_at']; ?></strong>
            <br>
            <small>Halaman ini akan refresh setiap 60 detik</small>
        </div>

        <?php else: ?>
        <div class="no-data">
            <p>Belum ada data. Tunggu Arduino mengirimkan data pertama...</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Parse hourly data for charts
        const hourlyData = <?php echo json_encode($hourly_data); ?>;
        
        const labels = hourlyData.map(d => d.hour.substr(5, 11)).reverse();
        const tempData = hourlyData.map(d => parseFloat(d.avg_temp)).reverse();
        const humidityData = hourlyData.map(d => parseFloat(d.avg_humidity)).reverse();
        const pressureData = hourlyData.map(d => (parseFloat(d.avg_pressure) / 1000).toFixed(2)).reverse();

        // Temperature Chart
        new Chart(document.getElementById('temperatureChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Suhu (°C)',
                    data: tempData,
                    borderColor: '#FF6B6B',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#FF6B6B'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });

        // Humidity Chart
        new Chart(document.getElementById('humidityChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kelembaban (%)',
                    data: humidityData,
                    borderColor: '#4ECDC4',
                    backgroundColor: 'rgba(78, 205, 196, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#4ECDC4'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100
                    }
                }
            }
        });

        // Pressure Chart
        new Chart(document.getElementById('pressureChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tekanan (kPa)',
                    data: pressureData,
                    borderColor: '#45B7D1',
                    backgroundColor: 'rgba(69, 183, 209, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#45B7D1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });

        // Auto refresh setiap 60 detik
        setInterval(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>