from lxml import html
import requests
from tqdm import tqdm
import os
import GeoNames.GeoNames as GN

def main():
    geoname = GN.GeoNames()
    currentdir = os.path.dirname(os.path.realpath(__file__))
    basename = geonames.getDir(currentdir, 'downloads')
    page = requests.get(geonames.url)
    tree = html.fromstring(page.content)

    anchors = tree.xpath('//a/@href')
    with tqdm(total=len(anchors)) as pbar:
        for anchor in anchors:
            # we are only interested in downloading zip and txt files
            if ".zip" in anchor or ".txt" in anchor:
                filename = basename + anchor
                if not os.path.isfile(filename):
                    r = requests.get(url + anchor, stream=True)
                    with open(filename, 'wb') as f:
                        for chunk in r.iter_content():
                            f.write(chunk)
            pbar.update(1)
