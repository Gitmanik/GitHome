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
//const byte outsideSensorAddress[8] PROGMEM = {0x28, 0x09, 0xCA, 0x87, 0x06 , 0x0 , 0x0, 0x99}; //2809CA8706000099

boolean whichTemp = true;

//Pogodynka 2017 Pawel Reich, 2020!!

void clearLine(uint8_t y) {
  lcd.setCursor(0, y);
  lcd.print("          ");
  lcd.setCursor(0, y);
}

void setup() {

  Serial.begin(115200);

  lcd.begin(16, 2);
  lcd.createChar_P(0, celsius);
  lcd.createChar_P(1, house);
  lcd.createChar_P(2, sun);
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
int ctr = 0;
void updateLCD() {

  lcd.setCursor(0,0);
  lcd.print(rtc.dateFormat("d-m   H:i:s", rtc.getDateTime()));
  if (ctr == 0 || ctr == 6)
  {
    ctr = 1;
    if (sensors.available()) {
      float temp1 = sensors.readTemperature(FA(insideSensorAddress));
      float temp2 = sensors.readTemperature(FA(outsideSensorAddress));

      Serial.print(temp1);
      Serial.print("$");
      Serial.print(temp2);
      Serial.print('\n');

      sensors.request();

      clearLine(1);
      lcd.write(1);
      lcd.print(temp1);
      lcd.print("    ");
      if (temp2 != -273.15)
        lcd.print(temp2);
      else
        lcd.print("-----");  
      lcd.write(2);
    }
  }
  ctr++;
}

void loop() {
  if (rtc.isAlarm1()) {
    updateLCD();
  }
}
