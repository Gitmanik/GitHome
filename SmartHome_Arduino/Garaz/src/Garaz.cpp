#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <Arduino.h>
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
#define VERSION "18"

#define INTERVAL 200

#define GARAZ_CZUJNIK 3
#define ACTION 13
#define ONE_WIRE 1

String pingString;
String updateString;

WiFiClient client;
HTTPClient http;

const int relay_pins[] = {5,4,0,2,14,12};
Bounce actionButton = Bounce();

OneWire oneWire(ONE_WIRE);
DallasTemperature sensors(&oneWire);


void worker();
void action();
void syncTemperature();

void setup()
{
  for (int i = 0; i < 6; i++)
  {
    pinMode(relay_pins[i], OUTPUT);
    digitalWrite(relay_pins[i], HIGH);
  }

  pinMode(GARAZ_CZUJNIK, INPUT);
  actionButton.attach(ACTION, INPUT);
  actionButton.interval(50);

  sensors.begin();

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
    worker();
  }

  if (currentMillis - termMillis >= 5000)
  {
    termMillis = currentMillis;
    syncTemperature();
  }

		actionButton.update();
		if (actionButton.rose())
			action();

}

void worker()
{

  for (int i = 0; i < 6; i++)
  {
    String api_call = String(pingString);
    api_call += i;
  
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
          ESPhttpUpdate.update(client, updateString);
          ESP.restart();
        }
    }
    http.end();
  }
  }
  		String api2 = String(pingString);
    api2 += "_GARAZ";
    api2 += "&data=";
    api2 += digitalRead(GARAZ_CZUJNIK);
  
    if (http.begin(client, api2)) {
      http.GET();
      http.end();
    }
}

void action()
{
    String api3 = String(pingString);
    api3 += "_ACTION";
    api3 += "&data=1";

    if (http.begin(client, api3)) {
      http.GET();
      http.end();
  	}
}

void syncTemperature()
{
    sensors.requestTemperatures(); 
    String api3 = String(pingString);
    api3 += "_TEMP";
    api3 += "&data=";
    api3 += sensors.getTempCByIndex(0);

    if (http.begin(client, api3)) {
      http.GET();
      http.end();
  	}
}