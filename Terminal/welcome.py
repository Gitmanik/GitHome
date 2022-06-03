from common_mode import make_font, draw_middle, change_mode
from datetime import datetime, timedelta
import config
import sys

class WelcomeMode:
    def draw(self, draw):
        pass
        #draw_middle(draw, self.Font, "Terminal", 32)

    def entered(self, key):
        if key not in config.ACTION:
            config.MODES[1].entered(key)
            config.MODE = 1
            return

        if key == "A":
            config.MODE = 2
    
    def __init__(self):
        self.Font = make_font("UbuntuMono-R.ttf", 20)

class EnterMode:
    def draw(self, draw):
        if ((datetime.now() - self.LastEnter).seconds > 5):
            self.reset()
            change_mode(0, draw)
            return
        
        draw_middle(draw, self.Font, self.Entered, 32)

    def entered(self, key):
        if key not in config.ACTION:
            self.Entered += key
            self.LastEnter = datetime.now()
            return

        if self.Entered == "5823":
            print("PIN entered, restarting")
            config.MODES[3].show("Resetting", 2, 4)


        if key == "#": # wlacz
            response = config.SMARTHOME.command(f"przelacz {self.Entered}")
            self.reset()
            config.MODES[3].show(response, 2, 0)
            return

    def reset(self):
        self.Entered = ""
        self.LastEnter = None

    def __init__(self):
        self.reset()
        self.Font = make_font("UbuntuMono-R.ttf", 20)

class ListMode:
    def draw(self, draw):
        x = 0
        for a in list(config.SMARTHOME.Data.keys())[self.ListPos:]:

            if (config.SMARTHOME.Data[a]["type"] == "CONTROLPANEL"):
                continue

            t = f'{config.SMARTHOME.Data[a]["id"]} {config.SMARTHOME.Data[a]["name"]} '

            if config.SMARTHOME.Data[a]["type"] == "DATA":
                t += config.SMARTHOME.Data[a]["content"].replace("\n", "")
            elif config.SMARTHOME.Data[a]["type"] == "TOGGLE":
                t += "⚡" if config.SMARTHOME.Data[a]["data"]["state"] else ""
                pass
            elif config.SMARTHOME.Data[a]["type"] == "MOMENTARY":
                t += ""

            draw.text((0,x*12), text=t, fill="white", font=self.Font)
            x +=1

    def entered(self, key):
        print(self.ListPos)
        if key == "A":
            config.SMARTHOME.command(f"przelacz {self.ListPos}")
        if key == "B":
            if self.ListPos > 0:
                self.ListPos -= 1
            else:
                self.ListPos = config.SMARTHOME.devlen() - 1
            return
        if key == "C":
            if config.SMARTHOME.devlen() - 1> self.ListPos:
                self.ListPos += 1
            else:
                self.ListPos = 0
            return
        if key == "*":
            config.MODE = 0

    def reset(self):
        self.ListPos = 0

    def __init__(self):
        self.reset()
        self.Font = make_font("code2000.ttf", 10)

class InfoMode:
    def draw(self, draw):
        if (datetime.now() - self.Start).seconds > self.Duration:
            change_mode(self.Target, draw)
            self.reset()
            return
        else:
            draw_middle(draw, self.SmallFont, self.Message, 32, wrap=True)
            return

    def entered(self, key):
        print(key)

    def show(self, message, duration, target):
        self.Message = message
        self.Start = datetime.now()
        self.Duration = duration
        self.Target = target
        config.MODE = 3

    def reset(self):
        self.Message = ""
        self.Duration = 0
        self.Start = 0
        self.Target = 0

    def __init__(self):
        self.reset()
        self.SmallFont = make_font("UbuntuMono-R.ttf", 10)

class RestartMode:
    def draw(self, draw):
        print("Restarting by RestartMode")
        raise KeyboardInterrupt

class InitMode:
    def draw(self, draw):
        if ((datetime.now() - self.Start).seconds > 5):
            change_mode(0, draw)
            return
        
        draw_middle(draw, self.Font, "TERMINAL", 25)
        draw_middle(draw, self.SmallFont, "Gitmanik 2020", 40)

    def entered(self, key):
        return

    def __init__(self):
        self.Start = datetime.now()
        self.Font = make_font("UbuntuMono-R.ttf", 20)
        self.SmallFont = make_font("UbuntuMono-R.ttf", 15)