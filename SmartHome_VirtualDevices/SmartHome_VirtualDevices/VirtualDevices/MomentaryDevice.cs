using NLog;
using System.Threading.Tasks;

namespace SmartHome_VirtualDevices.VirtualDevices
{
    public abstract class MomentaryDevice
    {
        private static Logger Logger = LogManager.GetCurrentClassLogger();
        public abstract string ID { get; }

        public abstract Task<bool> Toggle();

        public async Task<bool> Work(string data)
        {
            if (bool.TryParse(data, out bool res))
            {
                if (res)
                {
                    Logger.Info($"Toggling {ID}");
                    await Toggle();
                }

                return true;
            }
            else
                Logger.Error($"{ID} got wrong response: {data}");

            return false;
        }
    }
}