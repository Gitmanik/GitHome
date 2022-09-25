#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <RCSwitch.h>
#include <Arduino.h>
#include <Wire.h>
#include "../../credentials.h"

#define VERSION "4"

RCSwitch mySwitch = RCSwitch();
void worker();
String pingString = API_REPORT;
String updateString = API_UPDATE;
String payload;

void setup() {
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, HIGH);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }

  pingString += wifi_station_get_hostname() + "&version=" + VERSION;
  updateString += wifi_station_get_hostname() + "&version=" + VERSION;

  payload.reserve(128);

  Serial.println(WiFi.localIP().toString());
  Serial.println(wifi_station_get_hostname());
  Serial.println(pingString);
  
  static const RCSwitch::Protocol came = { 320, { 74, 1 }, { 1, 2 }, { 2, 1 }, true };
  // static const RCSwitch::Protocol came = { 304, { 73, 3 }, { 1, 2 }, { 2, 1 }, true };
  mySwitch.enableTransmit(D5);
  mySwitch.setProtocol(came);
  mySwitch.setRepeatTransmit(7);
}
long previousMillis = 0;
void loop() {
  long currentMillis = millis();

  if (currentMillis - previousMillis >= 300) {
    previousMillis = currentMillis;
    worker();
  }
}
WiFiClient client;
HTTPClient http;

void worker()
{
  if (http.begin(client, pingString)) {
    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
      
      payload = http.getString();
      if (payload == "UPDATE")
      {
        ESPhttpUpdate.update(client, updateString);
        ESP.restart();
      }
      else
      {
        if (payload.length() > 0)
        {
          if (payload[0] == 'T')
          {
          }
          else
          {
            digitalWrite(LED_BUILTIN, LOW);
            mySwitch.send(payload.toInt(), 24);
            digitalWrite(LED_BUILTIN, HIGH);
          }
        }
      }
    }
    http.end();
  }
}