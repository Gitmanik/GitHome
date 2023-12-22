# SmartHome - VirtualDevices

SmartHome Virtual Client providing access to various actions

## Supported virtual devices

* HikVision DVR resetting toggle
* Generic TUYA bulb on-off switch
* Web GET/POST toggle (e.g. remote projector switching)
* WakeOnLan toggle

## Recommended deployment

Fill **config.py** and with devices (and server ip) according to your needs. Provide this file via Docker as ```/data/config.py```

Set **network_mode** to **host**!

Set **restart** policy to **unless_stopped**

## Used libraries

* tinytuya
* requests
* Docker
