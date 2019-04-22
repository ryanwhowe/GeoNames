
import os
from datetime import datetime, timedelta

import wget
from tqdm import tqdm

import GeoNames.GeoNames as GN


def check_create_directory(directory):
    if not os.path.isdir(directory):
        os.mkdir(directory)


def pull_file(sourcefile, desitinationfile):
    if not os.path.isfile(desitinationfile):
        # Suppress the default download progress bar which overwrites the overall progress bar from tqdm
        wget.download(sourcefile, desitinationfile, bar=None)


def main():
    geonames = GN.GeoNames()
    geonames_dump = GN.GeoNamesDump()
    currentdir = os.path.dirname(os.path.realpath(__file__))
    downloads = geonames.getDir(currentdir, 'downloads')

    check_create_directory(downloads)

    total = len(geonames_dump.FILES) + len(geonames_dump.INCREMENTAL)

    with tqdm(total=total) as pbar:
        # first process the static files, if present we skip them and move onto incremental files
        for file in geonames_dump.FILES:
            sourcefilename = geonames_dump.URL + file
            destinationfilename = downloads + file
            pull_file(sourcefilename, destinationfilename)
            pbar.update(1)

        for file in geonames_dump.INCREMENTAL:
            # construct the filename from todays date
            sourcefilename = geonames_dump.URL + file + (datetime.today() - timedelta(1)).strftime('%Y-%m-%d') + '.txt'
            destinationfilename = downloads + file + (datetime.today() - timedelta(1)).strftime('%Y-%m-%d') + '.txt'
            pull_file(sourcefilename, destinationfilename)
            pbar.update(1)


main()
