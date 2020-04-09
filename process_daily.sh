#!/usr/bin/env bash
###############################################################################
# Ryan W. Howe <ryanwhowe@gmail.com>
# 2019-04-30
#
# This script will run the download script and processing script to download
# the daily delta files from GeoNames and then process the files and add
# them to the processing queue to be committed to the database.
#
# This script should be called from a crontab and should be expected to run
# daily (the GeoNames server only keeps the current days delta file available)
#
# CHANGE LOG
# ------ ---
# 2019-04-30 -RWH- 1.) Script created.
#
#
###############################################################################

# change to the script's directory (this directory)
cd "${0%/*}" || exit

# chain the download and process only if the download was successful
venv/bin/python download.py > /dev/null && venv/bin/python process.py > /dev/null