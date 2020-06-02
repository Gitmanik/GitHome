//https://forum.arduino.cc/index.php?topic=519102.0
#include <DS18B20.h>
#include <OneWire.h>
#include <DS3231.h>
#include <LiquidCrystal.h>

LiquidCrystal lcd(9, 4, 8, 7, 6, 5);

const byte celsius[8] PROGMEM = {
  0b01100,
  0b10010,
  0b10010,
  0b01100,
  0b00000,
  0b00000,
  0b00000,
  0b00000
};

const byte house[8] PROGMEM = {
  0b00000,
  0b00100,
  0b01110,
  0b11111,
  0b01010,
  0b01010,
  0b01110,
  0b00000
};

const byte temperror[8] PROGMEM = {

  0b01110,
  0b10011,
  0b10011,
  0b10101,
  0b10101,
  0b11001,
  0b01010,
  0b00100
};
const byte sun[8] PROGMEM = {

  0b00000,
  0b10101,
  0b01110,
  0b11011,
  0b01010,
  0b01110,
  0b10101,
  0b00000
};

DS3231 rtc;
uint8_t minuteCounter = 0;
uint8_t secondCounter = 0;

OneWire onewire(3);
DS18B20 sensors(&onewire);
const byte insideSensorAddress[8] PROGMEM = {0x28, 0xFF, 0x89, 0x5E, 0x62, 0x14, 0x3, 0x62};
const byte outsideSensorAddress[8] PROGMEM = {0x28, 0x09, 0xCA, 0x87, 0x06 , 0x0 , 0x0, 0x99}; //2809CA8706000099

boolean whichTemp = true;

//Pogodynka 2017 Pawel Reich, 2020!!

void setup() {

  Serial.begin(115200);

  lcd.begin(16, 2);
  lcd.createChar_P(0, celsius);
  lcd.createChar_P(1, house);
  lcd.createChar_P(3, temperror);
  lcd.createChar_P(4, sun);
  lcd.clear();
  lcd.print("Pogodynka 2020");
  lcd.setCursor(0, 1);
  lcd.print("Pawel Reich");
  delay(1000);
  lcd.clear();

  rtc.begin();
  rtc.setAlarm1(0, 0, 0, 0, DS3231_EVERY_SECOND, true);

  sensors.begin(12);
  sensors.request();

}

RTCDateTime printTime() {

  lcd.setCursor(0, 0);
  RTCDateTime rtctime = rtc.getDateTime();
  if (rtctime.month < 10 ) lcd.print("0"); lcd.print(rtctime.month);  lcd.print(F("-"));
  if (rtctime.day < 10 )   lcd.print("0"); lcd.print(rtctime.day);    lcd.print(F(" "));
  if (rtctime.hour < 10)   lcd.print("0"); lcd.print(rtctime.hour);   lcd.print(F(":"));
  if (rtctime.minute < 10) lcd.print("0"); lcd.print(rtctime.minute); lcd.print(F(":"));
  if (rtctime.second < 10) lcd.print("0"); lcd.print(rtctime.second);

  return rtctime;
}

void printTemp() {

  if (sensors.available()) {
    float temp1 = sensors.readTemperature(FA(insideSensorAddress));
    float temp2 = sensors.readTemperature(FA(outsideSensorAddress));

    Serial.print(temp1);
    Serial.print("$");
    Serial.print(temp2);
    Serial.print('\n');

    sensors.request();

    if (whichTemp) {
      secondCounter++;
      if (secondCounter == 4) {
        whichTemp = false;
        secondCounter = 0;
      }
      clearLine(1);
      lcd.write(byte(1));
      lcd.print(temp1);
    } else {
      secondCounter++;
      if (secondCounter == 4) {
        secondCounter = 0;
        whichTemp = true;
      }
      if (temp2 != -273.15) {
        clearLine(1);
        lcd.write(byte(4));
        lcd.print(temp2);
      } else {
        clearLine(1);
        lcd.write(byte(4));
        lcd.print("----");
        lcd.setCursor(15, 1);
        lcd.write(byte(3));
        return;
      }

    }

    lcd.write(byte(0));
    lcd.print(F("C"));

    lcd.setCursor(14, 1);
    lcd.print(F(" "));
    return;
  }
  lcd.setCursor(14, 1);
  lcd.write(byte(3));
}

void loop() {
  if (rtc.isAlarm1()) {
    printTime();
    printTemp();
  }
}
void clearLine(uint8_t y) {
  lcd.setCursor(0, y);
  lcd.print("          ");
  lcd.setCursor(0, y);
}
