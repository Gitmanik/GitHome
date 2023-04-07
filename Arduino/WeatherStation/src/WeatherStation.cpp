//https://forum.arduino.cc/index.php?topic=519102.0
#include <DS18B20.h>
#include <OneWire.h>
#include <DS3231.h>
#include <LiquidCrystal.h>
#include <Arduino.h>

LiquidCrystal lcd(9, 4, 8, 7, 6, 5);

DS3231 rtc;

OneWire onewire(3);
DS18B20 sensors(&onewire);
const byte insideSensorAddress[8] PROGMEM = {0x28, 0xFF, 0x89, 0x5E, 0x62, 0x14, 0x3, 0x62};

#define BACKLIGHT_PIN 2

void clearLine(uint8_t y) {
  lcd.setCursor(0, y);
  lcd.print("                ");
  lcd.setCursor(0, y);
}

void setup() {

  Serial.begin(9600);

  digitalWrite(BACKLIGHT_PIN, HIGH);

  lcd.begin(16, 2);
  lcd.clear();
  lcd.print(" WeatherStation ");
  lcd.setCursor(0, 1);
  lcd.print("Pawel Reich");
  delay(1000);
  lcd.clear();

  rtc.begin();
  rtc.setAlarm1(0, 0, 0, 0, DS3231_EVERY_SECOND, true);

  sensors.begin(12);
  sensors.request();

  pinMode(BACKLIGHT_PIN, OUTPUT);
  pinMode(BACKLIGHT_PIN, LOW);

}
void updateLCD() {
  static int ctr = 0;

  lcd.setCursor(0,0);
  lcd.print(rtc.dateFormat("d-m   H:i:s", rtc.getDateTime()));
  if (ctr % 5 == 0)
  {
    if (sensors.available()) {
      float temp1 = sensors.readTemperature(FA(insideSensorAddress));

      Serial.print(temp1);
      Serial.print('\n');

      sensors.request();

      clearLine(1);
      lcd.print(temp1);
    }
  }
  ctr++;
}

void loop() {

  if (rtc.isAlarm1()) {
    updateLCD();
  }

  if (Serial.available())
  {
    String inp = Serial.readStringUntil('\n');
    if (inp == "BACKLIGHT_ON")
    {
      digitalWrite(BACKLIGHT_PIN, HIGH);
    }

    if (inp == "BACKLIGHT_OFF")
    {
      digitalWrite(BACKLIGHT_PIN, LOW);
    }

    if (inp.startsWith("H:"))
    {
      rtc.setDateTime(inp.substring(2).toInt());
    }

    if (inp.startsWith("T:"))
    {
      lcd.setCursor(6,1);
      lcd.print(inp.substring(2));
    }
  }
}
