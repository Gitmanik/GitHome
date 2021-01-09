#include <RCSwitch.h>
#include <Arduino.h>

RCSwitch mySwitch = RCSwitch();

void setup() {
  Serial.begin(9600);
  Serial.println("Start");
  mySwitch.enableReceive(0);  // Receiver on interrupt 0 => that is pin #2
  mySwitch.enableTransmit(10);
  mySwitch.setProtocol(8);
  // mySwitch.setRepeatTransmit(1);
}

void loop() {
  if (mySwitch.available()) {
    
    Serial.print("Received ");
    Serial.print( mySwitch.getReceivedValue() );
    Serial.print(" / ");
    Serial.print( mySwitch.getReceivedBitlength() );
    Serial.print("bit ");
    Serial.print("Protocol: ");
    Serial.println( mySwitch.getReceivedProtocol() );

    mySwitch.resetAvailable();
  }

  if (Serial.available())
  {
    String cmd = Serial.readStringUntil('\n');
    if (cmd == "dol")
    {
      Serial.println("ok");
      mySwitch.send(5974125, 24);
    } else
    if (cmd == "stop")
    {
      Serial.println("ok");
      mySwitch.send(5974126, 24);
    } else
    if (cmd == "gora")
    {
      Serial.println("ok");
      mySwitch.send(5974127, 24);
    }
    else
    {
      long l = cmd.toInt();
      Serial.print("Sending ");
      Serial.println(l);
      mySwitch.send(l, 24);
    }
  }
}