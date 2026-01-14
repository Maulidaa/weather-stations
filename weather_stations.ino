#include "DHT.h"
#define DHT11_PIN 2
#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <Adafruit_BMP085.h>

const char WIFI_SSID[] = "WIFI_SSID";
const char WIFI_PASSWORD[] = "WIFI_PASSWORD";

String HOST_NAME = "http://192.168.0.19";
String PATH_NAME   = "/insert_data.php";
String queryString = "";

float revolutions = 0;
float windSpeed = 0;

DHT dht11(DHT11_PIN, DHT11);

const float mmPerPulse = 0.173;
float mmTotal = 0;
int sensore = 0;
int statoPrecedente = 0;

Adafruit_BMP085 bmp;

void revolution() {
  revolutions++;
  Serial.print(".");
}

void setup() {
  Serial.begin(9600);
  delay(1000);
  
  // Initialize sensors
  dht11.begin();
  if (!bmp.begin()) {
    Serial.println("BMP180 initialization failed!");
  }

  // Configure pins
  pinMode(2, INPUT);  // anemometer
  pinMode(9, INPUT);  // rain gauge

  // Connect to WiFi
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.println("Connecting to WiFi...");
  int attempts = 0;
  while(WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  Serial.println("");
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("Connected to WiFi. IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("Failed to connect to WiFi");
  }
}


void loop() {
  attachInterrupt(digitalPinToInterrupt(2), revolution, RISING);
  delay(60000);
  detachInterrupt(2);

  windSpeed = revolutions * 0.18;

  Serial.println("");
  Serial.print("Wind Speed: ");
  Serial.print(windSpeed);
  Serial.println(" km/h");

  revolutions = 0;

  // Read DHT11 sensors
  float humidity = dht11.readHumidity();
  float tempDHT = dht11.readTemperature();
  float tempF = dht11.readTemperature(true);

  // Read BMP180 sensors
  float tempBMP = bmp.readTemperature();
  long pressPa = bmp.readPressure();
  float seaLevelPa = 101325.0;
  float altitudeM = bmp.readAltitude(seaLevelPa);

  // Read rain gauge
  sensore = digitalRead(9);
  if (sensore != statoPrecedente) {
    mmTotal += mmPerPulse;
  }
  statoPrecedente = sensore;

  // Check sensor readings
  if (isnan(humidity) || isnan(tempDHT) || isnan(tempF)) {
    Serial.println("ERROR: Failed to read DHT11 sensor!");
    return;
  }

  // Display sensor data
  Serial.print("DHT11 - Humidity: ");
  Serial.print(humidity);
  Serial.print("% | Temperature: ");
  Serial.print(tempDHT);
  Serial.print("°C (");
  Serial.print(tempF);
  Serial.println("°F)");

  Serial.print("BMP180 - Temp: ");
  Serial.print(tempBMP);
  Serial.print("°C | Pressure: ");
  Serial.print(pressPa);
  Serial.print(" Pa | Altitude: ");
  Serial.print(altitudeM);
  Serial.println(" m");

  Serial.print("Rain Gauge: ");
  Serial.print(mmTotal);
  Serial.println(" mm");
  Serial.println("---");

  delay(1000);

  // Send data to server
  if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    
    // Build POST request with JSON data (more secure than GET)
    String jsonData = "{\"temp\":";
    jsonData += String(tempDHT);
    jsonData += ",\"humi\":";
    jsonData += String(humidity);
    jsonData += ",\"pressure\":";
    jsonData += String(pressPa);
    jsonData += ",\"altitude\":";
    jsonData += String(altitudeM);
    jsonData += ",\"wind\":";
    jsonData += String(windSpeed);
    jsonData += ",\"rain\":";
    jsonData += String(mmTotal);
    jsonData += "}";

    String url = HOST_NAME + PATH_NAME;
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    
    int httpResponseCode = http.POST(jsonData);
    
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.print("HTTP Response Code: ");
      Serial.println(httpResponseCode);
      Serial.println(response);
      
      // Reset rain gauge after successful data send
      mmTotal = 0;
    } else {
      Serial.print("ERROR: HTTP POST failed with code: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  } else {
    Serial.println("WiFi Disconnected");
  }
}