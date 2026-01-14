<?php
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Station Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙 Dark Mode</button>
            <h1>🌤️ Weather Station Dashboard</h1>
            <p>Real-time monitoring sistem cuaca</p>
        </div>

        <?php if ($latest_data): ?>
        <!-- Tab Navigation -->
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('overview')">📊 Overview</button>
            <button class="tab-btn" onclick="switchTab('stats')">📈 Statistik</button>
            <button class="tab-btn" onclick="switchTab('charts')">📉 Grafik</button>
            <button class="tab-btn" onclick="switchTab('data')">📋 Data</button>
        </div>

        <!-- Tab 1: Overview -->
        <div id="overview" class="tab-content active">
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
        </div><!-- end overview tab -->

        <!-- Tab 2: Statistics (24 hours) -->
        <div id="stats" class="tab-content">
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
        </div><!-- end stats tab -->

        <!-- Tab 3: Charts -->
        <div id="charts" class="tab-content">
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
        </div><!-- end charts tab -->

        <!-- Tab 4: Data Table -->
        <div id="data" class="tab-content">
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
        </div><!-- end data tab -->

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
        window.hourlyData = <?php echo json_encode($hourly_data); ?>;
    </script>
    <script src="js/dashboard.js"></script>
    <script>
        // Initialize charts when page loads
        window.addEventListener('load', function() {
            initializeCharts();
        });
    </script>
</body>
</html>
