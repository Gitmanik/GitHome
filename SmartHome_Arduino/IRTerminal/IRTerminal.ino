#include <ESP8266WiFi.h>
#include <ESP8266mDNS.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <WiFiClient.h>
#include <WiFiUdp.h>

#include <IRremoteESP8266.h>
#include <IRrecv.h>
#include <IRutils.h>

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

IRrecv irrecv(2);

void setup() {
  Serial.begin(115200);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    Serial.println("NO WIFI");
    delay(5000);
    ESP.restart();
  }

  irrecv.enableIRIn();
  Serial.println();
  Serial.println("Ready");
}

decode_results results;

#define COMMAND_MEMORY 16

uint64_t commands[COMMAND_MEMORY];
short pos = 0;
long last = 0;


void loop() {
  if (irrecv.decode(&results)) {
    if (pos < COMMAND_MEMORY - 1) {
      commands[pos] = results.value;
      pos++;
      last = millis();
      serialPrintUint64(results.value);
      Serial.println();
    }
    irrecv.resume();  // Receive the next value
  }

  if (pos > 0 && millis() > last + 1000) {
      worker();
      pos = 0;
      memset(commands, 0, sizeof(commands));
  }
  delay(100);
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
  for (int i = 0; i < pos; i++)
  {
      api_call += uint64ToString(commands[i]);
      api_call += ";";
  }

  if (http.begin(client, api_call)) {
    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
      String payload = http.getString();
      if (payload == "UPDATE")
      {
        Serial.println("Updating..");
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
