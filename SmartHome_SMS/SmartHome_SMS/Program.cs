using NLog;
using NLog.Config;
using NLog.Targets;
using SmartHome_SMS.Modem;
using SmartHome_SMS.SmartDom;
using System;
using System.IO;
using System.Net;
using System.Text;
using System.Threading;

namespace SmartHome_SMS
{
    internal class Program
    {
        private static readonly Logger Logger = LogManager.GetLogger("SmartHome_SMS");

        public static ZTE_MF823_Bridge modem;
        public static SmartHome_Bridge smarthome;

        public static MainTask automat;
        public static ConsoleHandler console;

        private static HttpListener server;

        private static void Main()
        {
            server = new HttpListener();
            server.Prefixes.Add("http://127.0.0.1:4444/");
            server.Prefixes.Add("http://localhost:4444/");
            server.Prefixes.Add("http://192.168.8.121:4444/");
            server.Start();

            Thread t = new Thread(serve);
            t.Start();

            ConfigureNLog();

            Logger.Info("SmartHome_SMS by Gitmanik, 2020");

            Logger.Info("Building Bridge over Cellular Modem");
            modem = new ZTE_MF823_Bridge();

            Logger.Info("Building Bridge over SmartHome");
            smarthome = new SmartHome_Bridge();

            Logger.Info("Starting main Task");
            automat = new MainTask();

            Logger.Info("Starting Console Handler");
            console = new ConsoleHandler();

            Logger.Info("Ready");

            new ManualResetEvent(false).WaitOne();
        }

        private static async void serve()
        {
            while (true)
            {
                HttpListenerContext context = server.GetContext();
                HttpListenerResponse response = context.Response;

                string msg = "No response";

                switch (context.Request.QueryString.Get("mode"))
                {
                    case null:
                        msg = "Mode is null";
                        break;

                    case "send":
                        msg = $"Sending to {Encoding.UTF8.GetString(Base64Url.Decode(context.Request.QueryString.Get("number")))}";
                        await modem.SendSMS(new SMS()
                        {
                            number = Encoding.UTF8.GetString(Base64Url.Decode(context.Request.QueryString.Get("number"))),
                            content = Encoding.UTF8.GetString(Base64Url.Decode(context.Request.QueryString.Get("text")))
                        });
                        break;
                }

                Console.WriteLine(msg);
                byte[] buffer = Encoding.UTF8.GetBytes(msg);

                response.ContentLength64 = buffer.Length;
                Stream st = response.OutputStream;
                st.Write(buffer, 0, buffer.Length);

                context.Response.Close();
            }
        }

        private static void ConfigureNLog()
        {
            LoggingConfiguration logConfig = new LoggingConfiguration();

            FileTarget logfile = new FileTarget("logfile")
            {
                FileName = "app.log",
                Layout = @"${date:format=HH\:mm\:ss} ${logger:long=True} ${level}: ${message} ${exception}",
                Encoding = Encoding.UTF8
            };

            ConsoleTarget logconsole = new ConsoleTarget("logconsole")
            {
                Layout = @"${date:format=HH\:mm\:ss} ${logger:long=True} ${level}: ${message} ${exception}",
            };

            logConfig.AddRule(LogLevel.Info, LogLevel.Fatal, logconsole);
            logConfig.AddRule(LogLevel.Debug, LogLevel.Fatal, logfile);

            LogManager.Configuration = logConfig;
        }

        public static string RemovePolishDiacritics(string input)
        {
            string t = "";
            foreach (char c in input.ToCharArray())
            {
                switch (c)
                {
                    case 'ą':
                        t += 'a';
                        break;

                    case 'ć':
                        t += 'c';
                        break;

                    case 'ę':
                        t += 'e';
                        break;

                    case 'ł':
                        t += 'l';
                        break;

                    case 'ń':
                        t += 'n';
                        break;

                    case 'ó':
                        t += 'o';
                        break;

                    case 'ś':
                        t += 's';
                        break;

                    case 'ż':
                    case 'ź':
                        t += 'z';
                        break;

                    case 'Ą':
                        t += 'A';
                        break;

                    case 'Ć':
                        t += 'C';
                        break;

                    case 'Ę':
                        t += 'E';
                        break;

                    case 'Ł':
                        t += 'L';
                        break;

                    case 'Ń':
                        t += 'N';
                        break;

                    case 'Ó':
                        t += 'O';
                        break;

                    case 'Ś':
                        t += 'S';
                        break;

                    case 'Ż':
                    case 'Ź':
                        t += 'Z';
                        break;

                    default:
                        t += c;
                        break;
                }
            }
            return t;
        }
    }
}