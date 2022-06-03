import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522, MFRC522
from time import sleep
import threading

class Rfid:

    def read(self):
        from time import sleep
        from mfrc522 import SimpleMFRC522, MFRC522
        import config
        while True:
            self.reader.READER = MFRC522(pin_mode=11)
            self.reader.READER.logger.setLevel('CRITICAL')
            id, text = self.reader.read()
            # for i in range(64):
            #     self.reader.READER.MFRC522_Read(i)
            id = str(id)
            print(f"RFID: {id}: {text}")
            # if id == "38204597282":
            #     config.MODES[3].show("GALAXY S8", 2, 0)
            # elif id == "580482762960":
            #     config.MODES[3].show(config.SMARTHOME.command(f"przelacz 1"), 2, 0)
            # elif id == "1079536262105":
            #     config.MODES[3].show(config.SMARTHOME.command(f"przelacz 0"), 2, 0)
            # else:  
            #     config.MODES[3].show(f"{id}: {text}", 2, 0)
            sleep(5)

    def __init__(self):
        self.Running = True
        self.ReadThread = threading.Thread(target=self.read, daemon=True)
        self.ReadThread.start()
        self.reader = SimpleMFRC522()
        print(f"version: {hex(self.reader.READER.Read_MFRC522(0x37))}")