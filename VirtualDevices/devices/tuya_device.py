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

        data = self.d.status()
        logging.info(data)
        if data['dps']['20'] == state:
            logging.info(f"{self.id} Already in desired state")
            return True

        logging.info(f"Toggling {self.id} {state}")

        if data['dps']['20'] == True:
            self.d.turn_off()
        else:
            self.d.turn_on()


        return True



# data = d.status()
# print(data)
# if data['dps']['20'] == False:
# 	d.turn_on()
# 	d.set_mode('scene')
# 	d.set_value(25, '07464602000003e803e800000000464602007803e803e80000000046460200f003e803e800000000464602003d03e803e80000000046460200ae03e803e800000000464602011303e803e800000000')
# else:
# 	d.turn_off()
