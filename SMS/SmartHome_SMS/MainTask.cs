using NLog;
using SmartHome_SMS.Modem;
using System;
using System.Net.Http;
using System.Text;
using System.Threading;
using System.Threading.Tasks;

namespace SmartHome_SMS
{
    internal class MainTask
    {
        private static readonly Logger Logger = LogManager.GetLogger("Main Task");

        private readonly HttpClient HTTP_CLIENT = new HttpClient();

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

                        if (sms.number == "+48536509255")
                        {
                            await SendSignal(sms.content);
                            continue;
                        }

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

        public async Task SendSignal(string content) => await HTTP_CLIENT.GetStringAsync(new Uri("http://smartdom.local:4445?text=" + Base64Url.ToBase64(Base64Url.Encode(Encoding.UTF8.GetBytes(content)))));
    }
}