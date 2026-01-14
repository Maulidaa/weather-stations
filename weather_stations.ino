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
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.println("Connecting");
  while(WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
  
  HTTPClient http;

  Serial.begin(9600);
  //anemometer
  pinMode(2, INPUT);

  //dht
  dht11.begin();

  //rain gauge
  pinMode(9, INPUT);
  //pinMode(3, OUTPUT);
}


void loop() {
  attachInterrupt(digitalPinToInterrupt(2), revolution, RISING);
  delay(60000);
  detachInterrupt(2);

  windSpeed = revolutions * 0.18;

  Serial.println("");
  Serial.print("Velocità: ");
  Serial.println(windSpeed);

  revolutions = 0;

  // read humidity
  float humi = dht11.readHumidity();
  // read temperature as Celsius
  float tempC = dht11.readTemperature();
  // read temperature as Fahrenheit
  float tempF = dht11.readTemperature(true);

  // check if any reads failed
  if (isnan(humi) || isnan(tempC) || isnan(tempF)) {
    Serial.println("Failed to read from DHT11 sensor!");
  } else {
    Serial.print("DHT11# Humidity: ");
    Serial.print(humi);
    Serial.print("%");

    Serial.print("  |  ");

    Serial.print("Temperature: ");
    Serial.print(tempC);
    Serial.print("°C ~ ");
    Serial.print(tempF);
    Serial.println("°F");

    //rain gauge
    sensore = digitalRead(9);

    if (sensore != statoPrecedente) {
      mmTotal = mmTotal + mmPerPulse;
    }

    delay(500);

    statoPrecedente = sensore;

    //bmp180
    float tempC = bmp.readTemperature();     // °C
    long  pressPa = bmp.readPressure();      // Pa
    // Standard sea-level pressure (you can calibrate this for better altitude accuracy)
    float seaLevelPa = 101325.0;
    float altitudeM = bmp.readAltitude(seaLevelPa);
    Serial.print("Temp: "); Serial.print(tempC); Serial.println(" C");
    Serial.print("Pressure: "); Serial.print(pressPa); Serial.println(" Pa");
    Serial.print("Altitude: "); Serial.print(altitudeM); Serial.println(" m");
    Serial.println("---");
    delay(1000);
  }
}