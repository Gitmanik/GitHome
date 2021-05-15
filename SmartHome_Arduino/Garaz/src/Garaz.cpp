#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <Wire.h>
#include "Adafruit_MCP23017.h"
#include <OneWire.h>
#include <DallasTemperature.h>

#define WIFI_SSID "***REMOVED***"
#define WIFI_PASS "***REMOVED***"
#define VERSION "25"

#define INTERVAL 500
#define ACTION 12
#define STATUS_LED 16
#define RELAYS_USED 3

String dataString;
String pingString;
String actionString;

WiFiClient client;
HTTPClient http;

OneWire oneWire(5);
DallasTemperature sensors(&oneWire);

Adafruit_MCP23017 mcp;

void syncRelays();
void syncData();
ICACHE_RAM_ATTR void onActionInterrupt();

volatile int state;
volatile long lastDebounceTime;
volatile bool triggerAction;

void setup()
{
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  Wire.begin(14,4);
  mcp.begin();
  mcp.pinMode(8,INPUT);
  mcp.pullUp(8, HIGH);
  for (int a = 0; a < 6; a++)
  {
    mcp.pinMode(a, OUTPUT);
    mcp.digitalWrite(a,1);
  }

  pinMode(ACTION, INPUT);
  pinMode(STATUS_LED,OUTPUT);
  attachInterrupt(ACTION, onActionInterrupt, CHANGE);

  sensors.begin();
  sensors.requestTemperatures();

  pingString      = String("http://***REMOVED***/api/report.php?version=") + VERSION   + "&id="    + wifi_station_get_hostname();
  dataString      = pingString                                            + "_DATA"   + "&data=";
  actionString    = pingString                                            + "_ACTION" + "&data=1";

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
  client.setNoDelay(1);
}

long previousMillis = 0;
long termMillis = 0;

void loop() {
  long currentMillis = millis();
  if (currentMillis - previousMillis >= INTERVAL) {
    previousMillis = currentMillis;
    digitalWrite(STATUS_LED,LOW);
    syncRelays();
    digitalWrite(STATUS_LED,HIGH);
  }

  if (currentMillis - termMillis >= 2500)
  {
    termMillis = currentMillis;
    syncData();
  }
  if (triggerAction)
  {
    triggerAction = false;
    if (http.begin(client, actionString)) {
      http.GET();
      http.end();
    }
  }
}
void syncRelays()
{
  for (int i = 0; i < RELAYS_USED; i++)
  {
    if (http.begin(client, pingString + i)) {
      int httpCode = http.GET();
      if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
        String payload = http.getString();
        if (payload == "false")
        {
          mcp.digitalWrite(5-i, HIGH);
        } else if (payload == "true")
        {
          mcp.digitalWrite(5-i, LOW);
        }
      }
    http.end();
    }
  }
}

void syncData()
{
  if (http.begin(client, dataString + mcp.digitalRead(8) + ";" + sensors.getTempCByIndex(0))) {
    int httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
      String payload = http.getString();
      if (payload == "UPDATE")
      {
        ESPhttpUpdate.update(client, String("http://***REMOVED***/api/update.php?id=") + wifi_station_get_hostname());
        ESP.restart();
      }
    }
    http.end();
    sensors.requestTemperatures();
  }
}

ICACHE_RAM_ATTR void onActionInterrupt() {
  int reading = digitalRead(ACTION);

  if(reading == state) return;

  boolean debounce = false;
  
  if((millis() - lastDebounceTime) <= 100) {
    debounce = true;
  }

  lastDebounceTime = millis();

  if(debounce) return;

  if (state == 0 && reading == 1)
  {
    triggerAction = true;
  }

  state = reading;
}