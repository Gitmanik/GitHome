using NLog;
using SmartHome_SMS.Modem;
using System;
using System.Threading;

namespace SmartHome_SMS
{
    public class ConsoleHandler
    {
        private static readonly Logger Logger = LogManager.GetLogger("Console");

        public ConsoleHandler()
        {
            Thread consoleThread = new Thread(HandleConsole);
            consoleThread.Start();
        }

        private async void HandleConsole()
        {
            while (true)
            {
                string command = Console.ReadLine().ToLower();
                if (command.StartsWith("!"))
                {
                    command = command.Substring(1);
                    if (command == "exit")
                        Environment.Exit(0);

                    if (command == "sms")
                        Logger.Info("Wszystkie SMS: \n" + string.Join<SMS>(",\n", (await Program.modem.GetAllSMS()).ToArray()));

                    if (command == "unread")
                        Logger.Info("Nieodczytane SMS: \n" + string.Join<SMS>(",\n", (await Program.modem.GetUnreadSMS()).ToArray()));
                    continue;
                }

                Logger.Info($"Response: {await Program.smarthome.HandleAsync(command)}");
            }
        }
    }
}