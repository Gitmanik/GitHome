using NLog;
using NLog.Config;
using NLog.Targets;
using SmartHome_VirtualDevices.VirtualDevices;
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Text;
using System.Threading;

namespace SmartHome_VirtualDevices
{
    class Program
    {
        private static Logger Logger = LogManager.GetCurrentClassLogger();

        public static List<MomentaryDevice> momentaryDevices = new List<MomentaryDevice>();

        public static string APIUrl = "http://smartdom.local/api/report.php";

        public static Worker worker;

        static void Main(string[] args)
        {
            ConfigureNLog();
            Logger.Info("SmartHome_VirtualDevices by Gitmanik, 2020");

            momentaryDevices.Add(new WOLDevice("WOL-PC1", "00-D8-61-36-BA-8E"));
            momentaryDevices.Add(new WebDevice("WEB-PRJR1", "http://192.168.8.116/cgi-bin/pjctrl.cgi.elf?D=%05%02%01%00%00%00", WebDevice.Type.Get));

            worker = new Worker();

            Logger.Info("Ready");
            new ManualResetEvent(false).WaitOne();

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

            ColoredConsoleTarget logconsole = new ColoredConsoleTarget("logconsole")
            {
                Layout = @"${date:format=HH\:mm\:ss} ${logger:long=True} ${level}: ${message} ${exception}",
            };

            logconsole.UseDefaultRowHighlightingRules = false;
            logconsole.RowHighlightingRules.Clear();
            logconsole.RowHighlightingRules.Add(new ConsoleRowHighlightingRule(
                "level == LogLevel.Trace and starts-with('${message}','[THREAD:')", ConsoleOutputColor.Cyan, ConsoleOutputColor.Black));
            logconsole.RowHighlightingRules.Add(new ConsoleRowHighlightingRule(
                "level == LogLevel.Trace", ConsoleOutputColor.DarkCyan, ConsoleOutputColor.Black));
            logconsole.RowHighlightingRules.Add(new ConsoleRowHighlightingRule(
                "level == LogLevel.Debug", ConsoleOutputColor.Green, ConsoleOutputColor.Black));

            logconsole.RowHighlightingRules.Add(new ConsoleRowHighlightingRule(
                "level == LogLevel.Info", ConsoleOutputColor.Cyan, ConsoleOutputColor.Black));

            logconsole.RowHighlightingRules.Add(new ConsoleRowHighlightingRule(
                "level == LogLevel.Warn", ConsoleOutputColor.Yellow, ConsoleOutputColor.Black));
            logconsole.RowHighlightingRules.Add(new ConsoleRowHighlightingRule(
                "level >= LogLevel.Error", ConsoleOutputColor.Red, ConsoleOutputColor.Black));

            logConfig.AddRule(NLog.LogLevel.Debug, NLog.LogLevel.Fatal, logconsole);
            logConfig.AddRule(NLog.LogLevel.Trace, NLog.LogLevel.Fatal, logfile);

            LogManager.Configuration = logConfig;
        }
    }
}
