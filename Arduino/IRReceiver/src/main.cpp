#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <IRremoteESP8266.h>
#include <IRrecv.h>
#include <IRutils.h>
#include <Arduino.h>
#include "../../credentials.h"

#ifdef ESP8266
extern "C" {
#include "user_interface.h"
}
#endif

#define VERSION "2"

IRrecv irrecv(2);

String pingString;
String updateString;

void worker();

void setup() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }

  pingString = String(API_REPORT);
  pingString += wifi_station_get_hostname();
  pingString += "&version=";
  pingString += VERSION;
  pingString += "&data=";

  updateString = String(API_UPDATE);
  updateString += wifi_station_get_hostname();
  updateString += "&version=";
  updateString += VERSION;

  irrecv.enableIRIn();
}

decode_results results;

#define COMMAND_MEMORY 16

uint64_t commands[COMMAND_MEMORY];
unsigned short pos = 0;
unsigned long last = 0;


void loop() {
  if (irrecv.decode(&results)) {
    if (pos < COMMAND_MEMORY - 1) {
      commands[pos] = results.value;
      pos++;
      last = millis();
    }
    irrecv.resume();
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
  String api_call = String(pingString);
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
        ESPhttpUpdate.update(client, updateString);
        ESP.restart();
      }
    }
    http.end();
  }
}
