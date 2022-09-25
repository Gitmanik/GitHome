#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <Arduino.h>
#include "../../credentials.h"

#define VERSION "2"

#define RELAY_PIN 0
#define BUILTIN_LED 2

#define INTERVAL 400

String pingString;

WiFiClient client;
HTTPClient http;
long previousMillis = 0;

void syncRelay();

void setup() {

  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, HIGH);

  pinMode(BUILTIN_LED, OUTPUT); // Builtin.
  digitalWrite(BUILTIN_LED, HIGH);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }

  client.setNoDelay(1);
  pingString = String(API_REPORT) + wifi_station_get_hostname() + "&version=" + VERSION;
}

void loop() {
  long currentMillis = millis();
  if (currentMillis - previousMillis >= INTERVAL) {
    previousMillis = currentMillis;
    syncRelay();
  }
}

void syncRelay()
{
  if (http.begin(client, pingString)) {
    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
      String payload = http.getString();
      if (payload == "true")
      {
        digitalWrite(RELAY_PIN, LOW);
      } else if (payload == "false")
      {
        digitalWrite(RELAY_PIN, HIGH);
      } else if (payload == "UPDATE")
      {
        ESPhttpUpdate.update(client, String(API_UPDATE) + wifi_station_get_hostname());
        ESP.restart();
      }
    }
    http.end();
  }
}
