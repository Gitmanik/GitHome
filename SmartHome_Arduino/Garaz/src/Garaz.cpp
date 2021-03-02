#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>

#include <Wire.h>
#include "Adafruit_MCP23017.h"

#include <Bounce2.h>
#include <OneWire.h>
#include <DallasTemperature.h>


#ifdef ESP8266
extern "C" {
#include "user_interface.h"
}
#endif

#define WIFI_SSID "***REMOVED***"
#define WIFI_PASS "***REMOVED***"
#define VERSION "20"

#define INTERVAL 600

#define ACTION 12
#define ONE_WIRE 5

#define RELAYS_USED 3

String garazDataString;
String pingString;
String updateString;
String actionString;

WiFiClient client;
HTTPClient http;

Bounce actionButton = Bounce();

OneWire oneWire(ONE_WIRE);
DallasTemperature sensors(&oneWire);

Adafruit_MCP23017 mcp; 

void worker();
void action();
void syncTemperature();
void updateButton();

void setup()
{
  //EKSPANDER I/O
  Wire.begin(14,4);
  mcp.begin();
  mcp.pinMode(8,INPUT);
  mcp.pullUp(8, HIGH);
  for (int a = 0; a < 6; a++)
  {
    mcp.pinMode(a, OUTPUT);
    mcp.digitalWrite(a,1);
  }

  actionButton.attach(ACTION, INPUT);

  pinMode(16,OUTPUT);

  sensors.begin();
  sensors.requestTemperatures();

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  pingString = String("http://***REMOVED***/api/report.php?version=");
  pingString += VERSION;
  pingString += "&id=";
  pingString += wifi_station_get_hostname();

  updateString = String("http://***REMOVED***/api/update.php?version=");
  updateString += VERSION;
  updateString += "&id=";
  updateString += wifi_station_get_hostname();

  garazDataString = String(pingString);
  garazDataString += "_DATA";
  garazDataString += "&data=";

  actionString = String(pingString);
  actionString += "_ACTION";
  actionString += "&data=1";
  
  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
}

long previousMillis = 0;
long termMillis = 0;

void loop() {
  long currentMillis = millis();
  if (currentMillis - previousMillis >= INTERVAL) {
    previousMillis = currentMillis;
    digitalWrite(16,LOW);
    worker();
    digitalWrite(16,HIGH);
  }

  if (currentMillis - termMillis >= 2500)
  {
    termMillis = currentMillis;
    syncTemperature();
  }

  updateButton();
}

void updateButton()
{
  actionButton.update();
  if (actionButton.rose())
    action();

}

void worker()
{
  for (int i = 0; i < RELAYS_USED; i++)
  {
    updateButton();
    if (http.begin(client, pingString + i)) {
      int httpCode = http.GET();
      if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
        String payload = http.getString();
        if (payload == "true")
        {
          mcp.digitalWrite(5-i, LOW);
        }
        if (payload == "false")
        {
          mcp.digitalWrite(5-i, HIGH);
        }
      }
    http.end();
    }
  }
}

void action()
{
    if (http.begin(client, actionString)) {
      http.GET();
      http.end();
  	}
}

void syncTemperature()
{
    if (http.begin(client, garazDataString + mcp.digitalRead(8) + ";" + sensors.getTempCByIndex(0))) {
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
    sensors.requestTemperatures();
}
}