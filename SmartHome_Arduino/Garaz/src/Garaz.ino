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
#define VERSION "10"

#define API_REPORT "http://***REMOVED***/api/report.php?id="
#define API_UPDATE "http://***REMOVED***/api/update.php?id="
#define INTERVAL 400

int relay_pins[] = {5,4,0,2,14,12};

#define INP 15
#define ACTION 13

#define DEBOUNCE 100

void setup()
{
  for (int i = 0; i < sizeof(relay_pins) / sizeof(int); i++)
  {
    pinMode(relay_pins[i], OUTPUT);
    digitalWrite(relay_pins[i], HIGH);
  }

  pinMode(INP, INPUT);
  
  pinMode(ACTION, INPUT);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
}

long previousMillis = 0;

// Variables will change:
int actionState = LOW;         // the current state of the output pin
int buttonState;             // the current reading from the input pin
int lastButtonState = LOW;   // the previous reading from the input pin

// the following variables are unsigned longs because the time, measured in
// milliseconds, will quickly become a bigger number than can be stored in an int.
unsigned long lastDebounceTime = 0;  // the last time the output pin was toggled
unsigned long debounceDelay = 100;    // the debounce time; increase if the output flickers


void loop() {
  long currentMillis = millis();
  if (currentMillis - previousMillis >= INTERVAL) {
    previousMillis = currentMillis;
    worker();
  }

  int reading = digitalRead(ACTION);

  // check to see if you just pressed the button
  // (i.e. the input went from LOW to HIGH), and you've waited long enough
  // since the last press to ignore any noise:

  // If the switch changed, due to noise or pressing:
  if (reading != lastButtonState) {
    // reset the debouncing timer
    lastDebounceTime = currentMillis;
  }

  if ((currentMillis - lastDebounceTime) > debounceDelay) {
    // whatever the reading is at, it's been there for longer than the debounce
    // delay, so take it as the actual current state:

    // if the button state has changed:
    if (reading != buttonState) {
      buttonState = reading;

      // only toggle the LED if the new button state is HIGH
      if (buttonState == HIGH) {
        actionState = HIGH;
      }
    }
  }

  // save the reading. Next time through the loop, it'll be the lastButtonState:
  lastButtonState = reading;
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
  String api2 = String(API_REPORT);
    api2 += wifi_station_get_hostname();
    api2 += "_GARAZ";
    api2 += "&version=";
    api2 += VERSION;
    api2 += "&data=";
    api2 += digitalRead(INP);
  
    if (http.begin(client, api2)) {
      int httpCode = http.GET();
      http.end();
  }

    String api3 = String(API_REPORT);
    api3 += wifi_station_get_hostname();
    api3 += "_ACTION";
    api3 += "&version=";
    api3 += VERSION;
    api3 += "&data=";
    api3 += actionState;

    actionState = LOW;
  
    if (http.begin(client, api3)) {
      int httpCode = http.GET();
      http.end();
  }
}
