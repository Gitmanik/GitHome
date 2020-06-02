using NLog;
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Threading.Tasks;

namespace SmartHome_VirtualDevices.VirtualDevices
{
    class WebDevice : MomentaryDevice
    {
        private static Logger Logger = LogManager.GetCurrentClassLogger();
        public enum Type
        {
            Post,
            Get
        }

        private HttpClient client;

        public override string ID { get; }

        private string url;
        private Type type;
        private HttpContent content;

        public WebDevice(string id, string url, Type type, Dictionary<string, string> postData = null)
        {
            this.ID = id;
            this.url = url;
            this.type = type;

            if (type == Type.Post)
            {
                if (postData == null)
                {
                    Logger.Error($"Requested WebDevice {id} with type Post, but didn't specify postData!");
                    Environment.Exit(1);
                }
                content = new FormUrlEncodedContent(postData);
            }

            client = new HttpClient();
            Logger.Info($"WebDevice {id}, {type} ready.");
        }

        public async override Task<bool> Toggle()
        {
            try
            {
                switch (type)
                {
                    case Type.Get:
                        await client.GetStringAsync(url);
                        break;

                    case Type.Post:
                        await client.PostAsync(url, content);
                        break;
                }
                return true;
            }
            catch (Exception e)
            {
                Logger.Fatal(e);
                return false;
            }
        }
    }
}
