#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <RCSwitch.h>
#include <Arduino.h>

#include <Wire.h>

#define WIFI_SSID "***REMOVED***"
#define WIFI_PASS "***REMOVED***"
#define VERSION "4"

RCSwitch mySwitch = RCSwitch();
void worker();
String API_REPORT = "http://***REMOVED***/api/report.php?version=" VERSION "&id=";
String API_UPDATE = "http://***REMOVED***/api/update.php?id=";
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

  API_REPORT += wifi_station_get_hostname();
  API_UPDATE += wifi_station_get_hostname();
  payload.reserve(128);

  Serial.println(WiFi.localIP().toString());
  Serial.println(wifi_station_get_hostname());
  Serial.println(API_REPORT);
  
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
  if (http.begin(client, API_REPORT)) {
    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
      
      payload = http.getString();
      if (payload == "UPDATE")
      {
        ESPhttpUpdate.update(client, API_UPDATE);
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