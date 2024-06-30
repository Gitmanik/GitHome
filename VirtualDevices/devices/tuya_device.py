import tinytuya
import logging

class TUYA_BulbDevice(object):

    def try_setup(self):
        try:
            self.d = tinytuya.BulbDevice(self.device_id, self.ip, self.local_key)
            self.d.set_version(3.3)
        except Exception as ex:
            logging.error(ex)

    def __init__(self, id, device_id, ip, local_key):
        self.device_id = device_id
        self.id = id
        self.ip = ip
        self.local_key = local_key
        self.saved_state = False
        self.d = None

    def execute(self, state):
        if self.saved_state == state:
            return True

        if self.d == None:
            logging.info(f"Setting up Bulb {self.id}")
            self.try_setup()

        if self.d != None:
            logging.info(f"Setting {self.id} to {state}")
            if state:
                self.d.turn_on()
            else:
                self.d.turn_off()
            self.saved_state = state

        return True

class TUYA_OutletDevice(object):
    
    def try_setup(self):
        try:
            self.d = tinytuya.OutletDevice(self.device_id, self.ip, self.local_key)
            self.d.set_version(3.3)
        except Exception as ex:
            logging.error(ex)

    def __init__(self, id, device_id, ip, local_key):
        self.device_id = device_id
        self.id = id
        self.ip = ip
        self.local_key = local_key
        self.saved_state = False
        self.d = None

    def execute(self, state):
        if self.saved_state == state:
            return True

        if self.d == None:
            logging.info(f"Setting up Outlet {self.id}")
            self.try_setup()

        if self.d != None:
            logging.info(f"Setting {self.id} to {state}")
            if state:
                self.d.turn_on()
            else:
                self.d.turn_off()
            self.saved_state = state

        return True
