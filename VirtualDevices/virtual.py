#!/usr/bin/env python3
import requests
import config
import time
import sys
import logging

if __name__ == "__main__":
    print("SmartHome VirtualDevices Client by Gitmanik, 2020-2024")
    logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    try:
        while True:
            for dev in config.TOGGLE_DEVICES:
                r = requests.get(f'http://{config.SERVER_IP}/api/report.php?id={dev.id}&version=100')
                dev.execute(r.text == "true")
            time.sleep(0.4)

    except KeyboardInterrupt:
        logging.info("\nShutting down..")
        sys.exit(0)