# SmartHome - VirtualDevices

Ad-hoc written smart home client </br>

## Supported virtual devices

* HikVision DVR resetting toggle
* Generic TUYA bulb on-off switch
* Web GET/POST toggle (e.g. remote projector switching)
* WakeOnLan toggle

## Preparing

Fill **config.py** and with devices (and server ip) according to your needs.

## Used

* OLED with sh1106 chip
* Generic 4x4 matrix keypad from China
* RaspberryPi Zero as a brain

### Libraries

* tinytuya
* requests
* Docker for containerizing application