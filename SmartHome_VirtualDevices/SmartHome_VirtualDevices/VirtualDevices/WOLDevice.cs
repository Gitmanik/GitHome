using NLog;
using System;
using System.IO;
using System.Net;
using System.Net.Sockets;
using System.Text.RegularExpressions;
using System.Threading.Tasks;

namespace SmartHome_VirtualDevices.VirtualDevices
{
    public class WOLDevice : MomentaryDevice
    {
        private static Logger Logger = LogManager.GetCurrentClassLogger();
        public override string ID { get; }
        private byte[] magicPacket;

        public WOLDevice(string name, string macAddress)
        {
            ID = name;
            magicPacket = BuildMagicPacket(macAddress);
            Logger.Info($"WOLDevice {name}, {macAddress} ready.");
        }

        public override async Task<bool> Toggle()
        {
            await SendWakeOnLan();
            return true;
        }

        static byte[] BuildMagicPacket(string macAddress) // MacAddress in any standard HEX format
        {
            macAddress = Regex.Replace(macAddress, "[: -]", "");
            byte[] macBytes = new byte[6];
            for (int i = 0; i < 6; i++)
            {
                macBytes[i] = Convert.ToByte(macAddress.Substring(i * 2, 2), 16);
            }

            using (MemoryStream ms = new MemoryStream())
            {
                using (BinaryWriter bw = new BinaryWriter(ms))
                {
                    for (int i = 0; i < 6; i++)  //First 6 times 0xff
                    {
                        bw.Write((byte)0xff);
                    }
                    for (int i = 0; i < 16; i++) // then 16 times MacAddress
                    {
                        bw.Write(macBytes);
                    }
                }
                return ms.ToArray(); // 102 bytes magic packet
            }
        }

        private async Task SendWakeOnLan()
        {
            using UdpClient client = new UdpClient();
            await client.SendAsync(magicPacket, magicPacket.Length, new IPEndPoint(IPAddress.Broadcast, 40000));
        }
    }
}
