using NLog;
using SmartHome_SMS.Modem;
using System;
using System.Threading;
using System.Threading.Tasks;

namespace SmartHome_SMS
{
    internal class MainTask
    {
        private static readonly Logger Logger = LogManager.GetLogger("Main Task");

        public MainTask()
        {
            Thread automatThread = new Thread(MainTaskHandler);
            automatThread.Start();
        }

        private async void MainTaskHandler()
        {
            while (true)
            {
                try
                {
                    foreach (SMS sms in (await Program.modem.GetUnreadSMS()))
                    {
                        await Program.modem.SetSMSTag(sms.id, 0);
                        string command = Program.RemovePolishDiacritics(sms.content.ToLower());

                        Logger.Info($"Received: {command}");
                        string resp = await Program.smarthome.HandleAsync(command);
                        Logger.Info("NOT SENDING");
                        //await Program.modem.SendSMS(new SMS()
                        //{
                        //    number = sms.number,
                        //    content = resp
                        //});
                    }
                    await Task.Delay(1000);
                }
                catch (Exception e)
                {
                    Logger.Fatal(e);
                    await Task.Delay(10000);
                }
            }
        }
    }
}