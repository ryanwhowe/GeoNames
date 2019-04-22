import os
from dotenv import load_dotenv
import GeoNames.GeoNames as GN
from lxml import html
import requests
from tqdm import tqdm

from pathlib import Path

load_dotenv()

DATABASE_HOST = os.getenv('DATABASE_HOST')
DATABASE_PASS = os.getenv('DATABASE_PASS')
DATABASE_USER = os.getenv('DATABASE_USER')
DATABASE_DATABASE = os.getenv('DATABASE_DATABASE')

geonames = GN.GeoNames()
currentdir = os.path.dirname(os.path.realpath(__file__))
loaderdir = geonames.getDir(currentdir, 'loaders')

page = requests.get(geonames.url)
tree = html.fromstring(page.content)
anchors = tree.xpath('//a/@href')
with tqdm(total=len(anchors)) as pbar:
    for anchor in anchors:
        if ".zip" in anchor or ".txt" in anchor:
            filename = loaderdir + os.path.splitext(anchor)[0] + ".sql"
            Path(filename).touch()
    pbar.update(1)
