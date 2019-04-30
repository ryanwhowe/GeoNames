#!/usr/bin/env bash

venv/bin/python process_daily_deletes.py &
venv/bin/python process_daily_modifications.py &
venv/bin/python process_daily_alternateNamesDeletes.py &
venv/bin/python process_daily_alternateNamesModifications.py &