import os
from PIL import ImageFont
import config

def make_font(name, size):
    font_path = os.path.abspath(os.path.join(
        os.path.dirname(__file__), 'fonts', name))
    return ImageFont.truetype(font_path, size)

def draw_middle(draw, f, text, y, wrap=False):
    s = draw.textsize(text, font=f)
    if wrap:
        if draw.textsize(text,font=f)[0] > 128:
            x = 0
            for c in text:
                draw.text((x % 128, y + int(x/128) * s[1]), text=c, fill="white", font=f)
                x += draw.textsize(c, font=f)[0]
            return
    
    draw.text((64-s[0]/2,y - s[1]/2), text=text, fill="white", font=f)

def change_mode(target, draw):
    config.MODE = target
    if draw is not None:
        config.MODES[target].draw(draw)