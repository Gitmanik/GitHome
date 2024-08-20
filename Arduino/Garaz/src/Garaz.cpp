#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <Wire.h>
#include "Adafruit_MCP23017.h"
#include <OneWire.h>
#include <DallasTemperature.h>
#include "../../credentials.h"

const int INTERVAL_RELAY = 500;
const int INTERVAL_DOOR_TEMP = 5000;
const int RELAY_USED = 5;
const int RELAY_COUNT = 6;
const int STATUS_LED = 16;
const int ONEWIRE = 5;
const int VERSION = 44;
const int ACTION_KEYS[3] = {12,13,15};
const int ACTION_KEYS_DEBOUNCE = 50;

const int ACTION_KEYS_COUNT = sizeof(ACTION_KEYS) / sizeof(int);

String dataString;
String pingString;
String actionString;
String nfcString;

WiFiClient client;
HTTPClient http;

OneWire oneWire(ONEWIRE);
DallasTemperature sensors(&oneWire);
Adafruit_MCP23017 mcp;

void sendRelays();
void sendDoorAndTemp();
void sendNFC(String code);

IRAM_ATTR void onActionInterrupt();

volatile int state[ACTION_KEYS_COUNT];
volatile long lastDebounceTime[ACTION_KEYS_COUNT];
volatile bool triggerAction[ACTION_KEYS_COUNT];

void setup()
{
  Serial.begin(9600); // NFC

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  Wire.begin(14,4); // Relays
  mcp.begin();
  mcp.pinMode(8,INPUT);
  mcp.pullUp(8, HIGH);
  for (int a = 0; a < RELAY_COUNT; a++)
  {
    mcp.pinMode(a, OUTPUT);
    mcp.digitalWrite(a,1);
  }
  
  for (int i = 0; i < ACTION_KEYS_COUNT; i++)
  {
    pinMode(ACTION_KEYS[i], INPUT);
    attachInterrupt(ACTION_KEYS[i], onActionInterrupt, CHANGE);
  }

  pinMode(STATUS_LED,OUTPUT);
  sensors.begin();
  sensors.requestTemperatures();

  pingString      = String(API_REPORT) + "?version=" + VERSION +  "&id=" + wifi_station_get_hostname();
  dataString      = pingString                                            + "_DATA" + "&data=";
  actionString    = pingString                                            + "_ACTION" + "&data=";
  nfcString       = pingString                                            + "_NFC" + "&data=";

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
  client.setNoDelay(1);
}

long previousMillis = 0;
long termMillis = 0;

void loop()
{
  long currentMillis = millis();

  if (Serial.available())
  {
    String code = Serial.readStringUntil('\n');
    code.remove(code.length() - 1);
    sendNFC(code); //NFC sends \r\n (meh)
  }

  if (currentMillis - previousMillis >= INTERVAL_RELAY) {
    previousMillis = currentMillis;
    digitalWrite(STATUS_LED,LOW);
    sendRelays();
    digitalWrite(STATUS_LED,HIGH);
  }

  if (currentMillis - termMillis >= INTERVAL_DOOR_TEMP)
  {
    termMillis = currentMillis;
    sendDoorAndTemp();
  }

  for (int i = 0; i < ACTION_KEYS_COUNT; i++)
  {
    if (triggerAction[i])
    {
      if (http.begin(client, actionString + i))
      {
        http.GET();
        http.end();
      }
      triggerAction[i] = false;
    }
  }
}
void sendRelays()
{
  for (int i = 0; i < RELAY_USED; i++)
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
        ESPhttpUpdate.update(client, String(API_UPDATE) + "?id=" + wifi_station_get_hostname());
        ESP.restart();
      }
    }
    http.end();
    sensors.requestTemperatures();
  }
}

IRAM_ATTR void onActionInterrupt() {

  for (int i = 0; i < ACTION_KEYS_COUNT; i++)
  {
    int reading = digitalRead(ACTION_KEYS[i]);

    if(reading == state[i]) continue;

    boolean debounce = false;
    
    if((millis() - lastDebounceTime[i]) <= ACTION_KEYS_DEBOUNCE) {
      debounce = true;
    }

    lastDebounceTime[i] = millis();

    if(debounce) continue;

    if (state[i] == 0 && reading == 1)
    {
      triggerAction[i] = true;
    }

    state[i] = reading;
  }
}