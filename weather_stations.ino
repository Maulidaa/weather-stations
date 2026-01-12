#include "DHT.h"
#define DHT11_PIN 2
#include <Wire.h>

float revolutions = 0;
float windSpeed = 0;

DHT dht11(DHT11_PIN, DHT11);

const float mmPerPulse = 0.173;
float mmTotali = 0;
int sensore = 0;
int statoPrecedente = 0;

void setup() {
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


  attachInterrupt(digitalPinToInterrupt(2), function, RISING);
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
      mmTotali = mmTotali + mmPerPulse;
    }

    delay(500);

    statoPrecedente = sensore;
  }

  void function() {
    revolutions++;
    Serial.print(".");
  }