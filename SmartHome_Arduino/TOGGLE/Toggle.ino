#include <ESP8266WiFi.h>
#include <ESP8266mDNS.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <WiFiClient.h>
#include <WiFiUdp.h>

#ifdef ESP8266
extern "C" {
#include "user_interface.h"
}
#endif

#define WIFI_SSID "***REMOVED***"
#define WIFI_PASS "***REMOVED***"
#define VERSION "1"
#define RELAY_PIN 0
#define API_REPORT "http://***REMOVED***/api/report.php?id="
#define API_UPDATE "http://***REMOVED***/api/update.php?id="
#define API_LOG    "http://***REMOVED***/api/log.php&id="
#define INTERVAL 400

void setup() {

  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, HIGH);
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, HIGH);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
}

long previousMillis = 0;
void loop() {
  long currentMillis = millis();
  if (currentMillis - previousMillis >= INTERVAL) {
    previousMillis = currentMillis;
    worker();
  }

}
WiFiClient client;
HTTPClient http;

void worker()
{
  String api_call = String(API_REPORT);
  api_call += wifi_station_get_hostname();
  api_call += "&version=";
  api_call += VERSION;

  if (http.begin(client, api_call)) {
    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
      String payload = http.getString();
      if (payload == "true")
      {
        digitalWrite(RELAY_PIN, LOW);
      }
      if (payload == "false")
      {
        digitalWrite(RELAY_PIN, HIGH);
      }
      if (payload == "UPDATE")
      {
        api_call = String(API_UPDATE);
        api_call += wifi_station_get_hostname();
        api_call += "&version=";
        api_call += VERSION;
        ESPhttpUpdate.update(client, api_call);
        ESP.restart();
      }
    }
    http.end();
  }
}

void log(String str) {
  String api_call = String(API_LOG);
  api_call += wifi_station_get_hostname();
  api_call += "&version=";
  api_call += str;
  HTTPClient http2; http2.begin(client, api_call); http2.GET(); http2.end();
}
