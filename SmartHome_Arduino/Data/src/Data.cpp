#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <Arduino.h>

#ifdef ESP8266
extern "C" {
#include "user_interface.h"
}
#endif

#define WIFI_SSID "***REMOVED***"
#define WIFI_PASS "***REMOVED***"
#define VERSION "4"
#define API_REPORT "http://***REMOVED***/api/report.php?id="
#define API_UPDATE "http://***REMOVED***/api/update.php?id="

#define LED_BUILTIN 2

void worker(String d);
void setup() {
  Serial.begin(9600);
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, HIGH);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
}

void loop() {
  if (Serial.available())
  {
      String data = Serial.readStringUntil('\n');
      worker(data);
  }
}
WiFiClient client;
HTTPClient http;

void worker(String d)
{
  String api_call = String(API_REPORT);
  api_call += wifi_station_get_hostname();
  api_call += "&version=";
  api_call += VERSION;
  api_call += "&data=";
  api_call += d;

  if (http.begin(client, api_call)) {
    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
      String payload = http.getString();
      if (payload == "UPDATE")
      {
        api_call = String(API_UPDATE);
        api_call += wifi_station_get_hostname();
        api_call += "&version=";
        api_call += VERSION;
        ESPhttpUpdate.update(client, api_call);
        ESP.restart();
      }
      else
      {
        if (payload.length() > 0)
        {
          digitalWrite(LED_BUILTIN, LOW);
          Serial.print(payload);
          Serial.print('\n');
          digitalWrite(LED_BUILTIN, HIGH);
        }
      }
    }
    http.end();
  }
}
