using Newtonsoft.Json;
using NLog;
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Threading.Tasks;

namespace SmartHome_SMS.SmartDom
{
    internal class SmartHome_Bridge
    {
        private static readonly Logger Logger = LogManager.GetLogger("SmartHome Bridge");

        private static readonly Uri SMS_API = new Uri("http://192.168.8.51/api/sms.php");

        private readonly HttpClient HTTP_CLIENT = new HttpClient();

        public async Task<string> HandleAsync(string command)
        {
            Dictionary<string, string> data = new Dictionary<string, string>
            {
                { "content", command },
            };

            Logger.Info("Handling: " + command);
            using (var request = new HttpRequestMessage()
            {
                RequestUri = SMS_API,
                Method = HttpMethod.Post,
                Content = new FormUrlEncodedContent(data)
            })
            {
                var r = await HTTP_CLIENT.SendAsync(request);
                return await r.Content.ReadAsStringAsync();
            }
        }
    }
}