#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <RCSwitch.h>
#include <Arduino.h>

#include <Wire.h>
#include <LiquidCrystal_I2C.h>

#define WIFI_SSID "***REMOVED***"
#define WIFI_PASS "***REMOVED***"
#define VERSION "2"

RCSwitch mySwitch = RCSwitch();

LiquidCrystal_I2C lcd(PCF8574_ADDR_A21_A11_A01, 4, 5, 6, 16, 11, 12, 13, 14, POSITIVE);

void worker();
String API_REPORT = "http://***REMOVED***/api/report.php?version=" VERSION "&id=";
String API_UPDATE = "http://***REMOVED***/api/update.php?id=";
String payload;

int print_timer;

void print(String text, String text2)
{
  lcd.backlight();
  lcd.clear();
  lcd.print(text);
  lcd.setCursor(0,1);
  lcd.print(text2);
  print_timer = millis();
}

void setup() {

  Serial.begin(9600);
  while (lcd.begin(16, 2, LCD_5x8DOTS, D5, D6) != 1)
  {
    Serial.println(F("PCF8574 is not connected or lcd pins declaration is wrong. Only pins numbers: 4,5,6,16,11,12,13,14 are legal."));
    delay(5000);
    ESP.restart();
  }

  print("RfBridge", "Gitmanik, 2021");

  API_REPORT += wifi_station_get_hostname();
  API_UPDATE += wifi_station_get_hostname();
  payload.reserve(128);

  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, HIGH);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    delay(5000);
    ESP.restart();
  }
  
  static const RCSwitch::Protocol came = { 320, { 74  , 1 }, { 1, 2 }, { 2, 1 }, true };
  mySwitch.enableTransmit(5);
  mySwitch.setProtocol(came);
  mySwitch.setRepeatTransmit(7);
}
long previousMillis = 0;
void loop() {
  long currentMillis = millis();

  if (print_timer != -1 && currentMillis - print_timer >= 10000)
  {
    print_timer = -1;
    lcd.noBacklight();
    lcd.clear();
    lcd.print(F("RfBridge"));
    lcd.setCursor(0,1);
    lcd.print(F("Gotowy"));
  }

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
        ESPhttpUpdate.update(client, API_REPORT);
        ESP.restart();
      }
      else
      {
        if (payload.length() > 0)
        {
          if (payload[0] == 'T')
          {
            payload.remove(0,1);
            print("Wysylanie RF", payload);
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
