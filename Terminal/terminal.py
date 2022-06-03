#!/usr/bin/env python3
from luma.core.interface.serial import i2c
from luma.core.render import canvas
from luma.oled.device import sh1106 
from time import sleep
from pad4pi import rpi_gpio
import RPi.GPIO
import sys

RPi.GPIO.setmode(11)
import config
if __name__ == "__main__":
    device = sh1106(i2c(port=1, address=0x3c))

    def redraw():
        with canvas(device) as draw:
            config.MODES[config.MODE].draw(draw)            

    def processKey(key):
        config.MODES[config.MODE].entered(key)

    # Setup Keypad
    KEYPAD = [
            ["1","2","3","A"],
            ["4","5","6","B"],
            ["7","8","9","C"],
            ["*","0","#","D"]
    ]

    COL_PINS = [13,6,5,0]
    ROW_PINS = [21,20,26,19]

    factory = rpi_gpio.KeypadFactory()

    keypad = factory.create_keypad(keypad=KEYPAD, row_pins=ROW_PINS, col_pins=COL_PINS)

    keypad.registerKeyPressHandler(processKey)
    while True:
        try:
            redraw()
            sleep(0.1)
        except KeyboardInterrupt:
            print("\nShutting down..")
            device.cleanup()
            keypad.cleanup()
            sys.exit(0)