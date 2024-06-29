import tinytuya
import logging

class TUYA_BulbDevice(object):

    def __init__(self, id, device_id, ip, local_key):
        self.device_id = device_id
        self.id = id
        self.ip = ip
        self.local_key = local_key
        self.d = tinytuya.BulbDevice(device_id, ip, local_key)
        self.d.set_version(3.3)
        self.saved_state = False

    def execute(self, state):

        if self.saved_state == state:
            return True

        self.saved_state = state

        logging.info(f"Setting {self.id} to {state}")

        if state:
            self.d.turn_on()
        else:
            self.d.turn_off()

        return True

class TUYA_OutletDevice(object):
    
    def __init__(self, id, device_id, ip, local_key):
        self.device_id = device_id
        self.id = id
        self.ip = ip
        self.local_key = local_key
        self.d = tinytuya.OutletDevice(device_id, ip, local_key)
        self.d.set_version(3.3)
        self.saved_state = False

    def execute(self, state):

        if self.saved_state == state:
            return True

        self.saved_state = state

        logging.info(f"Setting {self.id} to {state}")

        if state:
            self.d.turn_on()
        else:
            self.d.turn_off()

        return True
