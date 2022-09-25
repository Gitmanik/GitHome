import socket, struct, logging
# https://gist.github.com/rschuetzler/8854764
class Waker():
    def makeMagicPacket(self, macAddress):
        # Take the entered MAC address and format it to be sent via socket
        splitMac = str.split(macAddress,':')
    
        # Pack together the sections of the MAC address as binary hex
        hexMac = struct.pack('BBBBBB', int(splitMac[0], 16),
                             int(splitMac[1], 16),
                             int(splitMac[2], 16),
                             int(splitMac[3], 16),
                             int(splitMac[4], 16),
                             int(splitMac[5], 16))
    
        self.packet = b'\xff' * 6 + hexMac * 16 #create the magic packet from MAC address
    
    def sendPacket(self, packet):
        # Create the socket connection and send the packet
        s=socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST, 1)
        s.sendto(packet,("255.255.255.255",40000))
        s.close()
        
    def wake(self, macAddress):
        self.makeMagicPacket(macAddress)
        self.sendPacket(self.packet)
        logging.info(f'Packet successfully sent to {macAddress}')

class WOL_Device(object):

    def __init__(self, device_id, mac):
        self.waker = Waker()
        self.id = device_id
        self.mac = mac

    def execute(self, state):
        if state == False:
            return
            
        self.waker.wake(self.mac)
        return True
