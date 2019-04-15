from lxml import html
import requests
from tqdm import tqdm
import os

url = 'http://download.geonames.org/export/dump/'

page = requests.get(url)
tree = html.fromstring(page.content)

anchors = tree.xpath('//a/@href')
with tqdm(total=len(anchors)) as pbar:
    for anchor in anchors:
        if ".zip" in anchor or ".txt" in anchor:
            filename = anchor
            if not os.path.isfile(filename):
                r = requests.get(url + anchor, stream=True)
                with open(filename, 'wb') as f:
                    for chunk in r.iter_content():
                        f.write(chunk)
        pbar.update(1)