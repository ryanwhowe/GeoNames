import os
from datetime import datetime, timedelta

import wget
from tqdm import tqdm

from GeoNames import GeoNames, dump


def check_create_directory(directory):
    if not os.path.isdir(directory):
        # todo add some logic to check for write permissions before attempting to create dir
        os.mkdir(directory)


def pull_file(sourcefile, desitinationfile):
    if not os.path.isfile(desitinationfile):
        # wget was the fastest http download implementation I was able to find, tried urllib3 & responses
        # Suppress the default download progress bar which overwrites the overall progress bar from tqdm
        wget.download(sourcefile, desitinationfile, bar=None)


def main():
    currentdir = os.path.dirname(os.path.realpath(__file__))
    downloads = GeoNames.get_dir(currentdir, 'downloads')

    check_create_directory(downloads)

    total = len(dump.FILES) + len(dump.INCREMENTAL)

    with tqdm(total=total) as pbar:
        # first process the static files, if present we skip them and move onto incremental files
        for file in dump.FILES:
            sourcefilename = dump.URL + file
            destinationfilename = downloads + file
            pull_file(sourcefilename, destinationfilename)
            pbar.update(1)

        for file in dump.INCREMENTAL:
            # construct the filename from todays date
            sourcefilename = dump.URL + file + (datetime.today() - timedelta(1)).strftime('%Y-%m-%d') + '.txt'
            destinationfilename = downloads + file + (datetime.today() - timedelta(1)).strftime('%Y-%m-%d') + '.txt'
            pull_file(sourcefilename, destinationfilename)
            pbar.update(1)


main()
