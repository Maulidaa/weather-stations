USE weather_stations;

-- Drop old tables
DROP TABLE IF EXISTS dht;
DROP TABLE IF EXISTS anemometer;
DROP TABLE IF EXISTS rain_gauge;
DROP TABLE IF EXISTS bmp;

-- Create unified weather data table
CREATE TABLE weather_data (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	temperature_dht FLOAT DEFAULT 0.00,
	humidity FLOAT DEFAULT 0.00,
	pressure FLOAT DEFAULT 0.00,
	altitude FLOAT DEFAULT 0.00,
	windSpeed FLOAT DEFAULT 0.00,
	rain_state FLOAT DEFAULT 0.00,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	INDEX idx_created_at (created_at)
);