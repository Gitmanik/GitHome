import threading
class SmartHome:
    def update(self):
        import requests
        import config
        import time
        while True:
            r = requests.get(f'http://{config.SERVER_IP}/api/report.php?id={config.DEVICE_ID}&version={config.VERSION}')
            self.Data = r.json()
            time.sleep(0.4)

    def devlen(self):
        x = 0
        for a in list(self.Data.keys()):
            if (self.Data[a]["type"] == "CONTROLPANEL"):
                continue
            x +=1
        return x

    def command(self, command):
        import config
        import requests
        r = requests.post(f'http://{config.SERVER_IP}/api/sms.php', data = {'content':command})
        return r.content.decode('unicode-escape').encode('latin1').decode('utf-8')

    def __init__(self):
        self.Data = dict()
        self.UpdateThread = threading.Thread(target=self.update, daemon=True)
        self.UpdateThread.start()
        