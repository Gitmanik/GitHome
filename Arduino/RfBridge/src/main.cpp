#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <RCSwitch.h>
#include <Arduino.h>
#include <Wire.h>
#include "../../credentials.h"

#define VERSION "9"

#define MAX_RAW_DATA_SIZE 512
#define REPEAT_COUNT 10
#define OUTPUT_PIN D5

RCSwitch mySwitch = RCSwitch();
void worker();
void send_raw(const char* data);
void send_signal(int size, const unsigned long deltas[]);

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

  pingString += String("?id=") + wifi_station_get_hostname() + String("&version=") + VERSION;
  updateString += String("?id=") + wifi_station_get_hostname() + String("&version=") + VERSION;

  payload.reserve(2048);
  
  static const RCSwitch::Protocol came = { 320, { 74, 1 }, { 1, 2 }, { 2, 1 }, true };
  
  mySwitch.enableTransmit(OUTPUT_PIN);
  mySwitch.setProtocol(came);
  mySwitch.setRepeatTransmit(REPEAT_COUNT);
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
          if (payload.charAt(0) == 'R')
          {
            digitalWrite(LED_BUILTIN, LOW);
            send_raw(payload.c_str()+sizeof(char));
            digitalWrite(LED_BUILTIN, HIGH);
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

void send_raw(const char* data)
{
  char* dataPtr = (char*) data;

  unsigned long recvData[512] = {0};

  int ctr = 0;
  
  char* delay = strtok(dataPtr, ",");

  while (delay != 0)
  {
    if (ctr > MAX_RAW_DATA_SIZE)
    {
      // TODO: LOG FAILURE
      return;
    }

    uint8_t is_number = 1;
    for (unsigned int idx = 0; idx < strlen(delay); idx++)
    {
      if (!isdigit(delay[idx]))
        is_number = 0;
    }
    if (!is_number)
      break;
    recvData[ctr] = atol(delay);
    ctr++;
    delay = strtok(0, ",");
  }

  if (ctr > 0)
  {
    send_signal(ctr, (const unsigned long*) recvData);
  }
}

void send_signal(int size, const unsigned long deltas[])
{
  for (int rep_ctr = 0; rep_ctr < REPEAT_COUNT; rep_ctr++)
  {
    for (int ctr = 0; ctr < size; ctr +=2)
    {
        digitalWrite(OUTPUT_PIN, LOW);
        delayMicroseconds(deltas[ctr]);

        digitalWrite(OUTPUT_PIN, HIGH);
        delayMicroseconds(deltas[ctr+1]);

    }
  }
  digitalWrite(OUTPUT_PIN, LOW);
  digitalWrite(LED_BUILTIN, HIGH);
}