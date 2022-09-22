from welcome import WelcomeMode, EnterMode, ListMode, InfoMode, RestartMode, InitMode
from smarthome import SmartHome
from rfid import Rfid
ACTION = ["A","B","C","D","*","#"]

SERVER_IP = "192.168.8.51"
DEVICE_ID = "TER-001"
RFID = Rfid()
SMARTHOME = SmartHome()
VERSION = 1

MODE = 5
MODES = [
    WelcomeMode(),
    EnterMode(),
    ListMode(),
    InfoMode(),
    RestartMode(),
    InitMode()
]