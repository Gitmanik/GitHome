import requests, struct, logging

class WEB_Device(object):

    def __init__(self, device_id, url,t, postdata = None):
        self.id = device_id
        self.t = t
        self.url = url
        self.postdata = postdata

    def execute(self, state):
        if state == False:
            return

        if self.t == 'POST':
            requests.post(self.url, data = self.postdata)
        elif self.t == 'GET':
            requests.get(self.url)
        else:
            logging.error("Wrong t")
            return False
        return True
