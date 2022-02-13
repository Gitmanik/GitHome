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
#define VERSION "35"

//Action button
#define INTERVAL 500
#define ACTION 12

#define STATUS_LED 16

#define RELAYS_USED 5

String dataString;
String pingString;
String actionString;
String nfcString;

WiFiClient client;
HTTPClient http;

OneWire oneWire(5);
DallasTemperature sensors(&oneWire);

Adafruit_MCP23017 mcp;

int relayStates[RELAYS_USED + 1];
long relayUsed;
void sendRelays();
void sendDoorAndTemp();
void sendNFC(String code);
IRAM_ATTR void onActionInterrupt();

volatile int state;
volatile long lastDebounceTime;
volatile bool triggerAction;

void setup()
{
  Serial.begin(9600); // NFC

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  Wire.begin(14,4); // Relays
  mcp.begin();
  mcp.pinMode(8,INPUT);
  mcp.pullUp(8, HIGH);
  for (int a = 0; a < 6; a++)
  {
    mcp.pinMode(a, OUTPUT);
    mcp.digitalWrite(a,1);
  }

  pinMode(ACTION, INPUT); // Action button (Garage door)
  
  pinMode(STATUS_LED,OUTPUT);
  attachInterrupt(ACTION, onActionInterrupt, CHANGE);

  sensors.begin();
  sensors.requestTemperatures();

  pingString      = String("http://***REMOVED***/api/report.php?version=") + VERSION   + "&id="    + wifi_station_get_hostname();
  dataString      = pingString                                            + "_DATA"   + "&data=";
  actionString    = pingString                                            + "_ACTION" + "&data=1";
  nfcString       = pingString                                            + "_NFC"    + "&data=";

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

  if (Serial.available())
  {
    String code = Serial.readStringUntil('\n');
    code.remove(code.length() - 1);
    sendNFC(code); //NFC sends \r\n (meh)
  }

  if (currentMillis - previousMillis >= INTERVAL) {
    previousMillis = currentMillis;
    digitalWrite(STATUS_LED,LOW);
    sendRelays();
    digitalWrite(STATUS_LED,HIGH);
  }

  if (currentMillis - termMillis >= 2500)
  {
    termMillis = currentMillis;
    sendDoorAndTemp();
  }
  if (triggerAction)
  {
    triggerAction = false;
    if ((currentMillis - relayUsed >= 500) && http.begin(client, actionString)) {
      http.GET();
      http.end();
    }
  }
}
void sendRelays()
{
  for (int i = 0; i < RELAYS_USED; i++)
  {
    if (http.begin(client, pingString + i)) {
      int httpCode = http.GET();
      if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
        String payload = http.getString();
        if (payload == "false")
        {
          if (relayStates[i] == LOW)
          {
            relayUsed = millis();
          }
          relayStates[i] = HIGH;
          mcp.digitalWrite(5-i, HIGH);
        } else if (payload == "true")
        {
          if (relayStates[i] == HIGH)
          {
            relayUsed = millis();
          }
          relayStates[i] = LOW;
          mcp.digitalWrite(5-i, LOW);
        }
      }
    http.end();
    }
  }
}

void sendNFC(String code)
{
  if (http.begin(client, nfcString + code)) {
    http.GET();
    http.end();
  }
}
void sendDoorAndTemp()
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

IRAM_ATTR void onActionInterrupt() {
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