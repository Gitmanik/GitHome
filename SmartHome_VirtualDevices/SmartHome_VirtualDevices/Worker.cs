using NLog;
using SmartHome_VirtualDevices.VirtualDevices;
using System;
using System.Net.Http;
using System.Threading;
using System.Threading.Tasks;

namespace SmartHome_VirtualDevices
{
    public class Worker
    {
        private static Logger Logger = LogManager.GetCurrentClassLogger();

        private HttpClient httpClient;

        public Worker()
        {
            httpClient = new HttpClient();
            Thread workerThread = new Thread(ThreadHandler);
            workerThread.Start();
        }

        private async void ThreadHandler()
        {
            while(true)
            {
                try
                {

                    foreach (MomentaryDevice dev in Program.momentaryDevices)
                    {
                        if (!await dev.Work(await Report(dev.ID)))
                        {
                            Logger.Error($"Device {dev.ID} failed.");
                        }
                    }
                    await Task.Delay(400);
                }
                catch (Exception e)
                {
                    Logger.Fatal(e);
                    await Task.Delay(10000);
                }
            }
        }

        private async Task<string> Report(string id)
        {
            return await httpClient.GetStringAsync(Program.APIUrl + $"?version=100&id={id}");
        }
    }
}