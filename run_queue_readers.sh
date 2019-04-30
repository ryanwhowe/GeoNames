#!/usr/bin/env bash
###############################################################################
# Ryan W. Howe <ryanwhowe@gmail.com>
# 2019-04-30
#
# This script will run all of the daily queue reader and process scripts in the
# background.
#
# CHANGE LOG
# ------ ---
# 2019-04-30 -RWH- 1.) Script created.
#
#
###############################################################################
# change to the script's directory (this directory)
cd "${0%/*}"

venv/bin/python process_daily_deletes.py &
venv/bin/python process_daily_modifications.py &
venv/bin/python process_daily_alternateNamesDeletes.py &
venv/bin/python process_daily_alternateNamesModifications.py &