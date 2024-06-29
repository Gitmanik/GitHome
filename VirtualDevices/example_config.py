from devices.wol_device import WOL_Device
from devices.web_device import WEB_Device
from devices.tuya_device import *
from devices.hik_device import HikReboot_Device

SERVER_IP = "127.0.0.1"

TOGGLE_DEVICES = [
        WOL_Device("my-pc", "<mac>")
]

