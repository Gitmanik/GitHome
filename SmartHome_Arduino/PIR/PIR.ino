#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>

#ifdef ESP8266
extern "C" {
#include "user_interface.h"
}
#endif

#define WIFI_SSID "***REMOVED***"
#define WIFI_PASS "***REMOVED***"
#define VERSION "1"
#define API_REPORT "http://***REMOVED***/api/report.php?id="
#define API_UPDATE "http://***REMOVED***/api/update.php?id="

volatile boolean state_changed = false;
ICACHE_RAM_ATTR void changeState()
{
  state_changed = true;
}
void setup() {
  Serial.begin(115200);
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, HIGH);

  pinMode(3, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(3), changeState, CHANGE);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
}

void loop() {
  if (state_changed)
  {
    digitalWrite(LED_BUILTIN, LOW);
    worker();
    state_changed = false;
    digitalWrite(LED_BUILTIN, HIGH);
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
  api_call += "&data=";
  api_call += String(digitalRead(3));

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
    }
    http.end();
  }
}
