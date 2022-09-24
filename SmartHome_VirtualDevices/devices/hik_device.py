import xml.etree.ElementTree as ET
import hashlib
import requests
import time
import logging

class HikReboot_Device(object):

    def sha(self, x):
        return hashlib.sha256(x.encode('utf-8')).hexdigest()

    def __init__(self, device_id, address, username, password):
        self.id = device_id
        self.username = username
        self.password = password
        self.address = address

        self.namespaces = {'hik': 'http://www.hikvision.com/ver20/XMLSchema'}

    def execute(self, state):
        if state == False:
            return
            
        s = requests.session()

        r = s.get(f'http://:{self.password}@{self.address}/ISAPI/Security/sessionLogin/capabilities?username={self.username}')

        xx = r.text
        logging.info(xx)

        ss = ET.fromstring(xx)

        salt = ss.find('hik:salt', self.namespaces).text
        challenge = ss.find('hik:challenge', self.namespaces).text
        sessionid = ss.find('hik:sessionID', self.namespaces).text

        n = self.sha(self.username + salt + self.password)
        n = self.sha(n + challenge)

        iterations = int(ss.find('hik:iterations', self.namespaces).text)

        for x in range(2, iterations):
            n = self.sha(n)

        hdr = {'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'}

        xml = f"<SessionLogin><userName>{self.username}</userName><password>{n}</password><sessionID>{sessionid}</sessionID></SessionLogin>"

        r = s.post(f'http://{self.address}/ISAPI/Security/sessionLogin?timeStamp=' + str(int(time.time_ns()/1000)), headers = hdr, data=xml)

        sessionid = ET.fromstring(r.text).find('hik:sessionID', self.namespaces).text

        r = s.put(f'http://{self.address}/ISAPI/System/reboot', cookies={'WebSession': sessionid})
        logging.info(r.text)