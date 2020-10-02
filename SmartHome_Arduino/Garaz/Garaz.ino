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
#define VERSION "2"

#define API_REPORT "http://***REMOVED***/api/report.php?id="
#define API_UPDATE "http://***REMOVED***/api/update.php?id="
#define INTERVAL 400

int relay_pins[] = {5,4,0,2,14,12};

void setup()
{
  for (int i = 0; i < sizeof(relay_pins) / sizeof(int); i++)
  {
    pinMode(relay_pins[i], OUTPUT);
    digitalWrite(relay_pins[i], HIGH);
  }

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

  for (int i = 0; i < 6; i++)
  {
    String api_call = String(API_REPORT);
    api_call += wifi_station_get_hostname();
    api_call += i;
    api_call += "&version=";
    api_call += VERSION;
  
    if (http.begin(client, api_call)) {
      int httpCode = http.GET();
      if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
        String payload = http.getString();
        if (payload == "true")
        {
          digitalWrite(relay_pins[i], LOW);
        }
        if (payload == "false")
        {
          digitalWrite(relay_pins[i], HIGH);
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
  
}
