# GeoNames
This is a project to automate the ETL of the GeoNames public data sets

## Usage

* create a virtualenv for the script to run
````bash
virtualenv venv
````
* enter the virtualenv
````bash
source venv/bin/activate
````
* install the required pip packages
````bash
pip install -r requirements.txt
````

the puller.py script is currently the only working component.  It will create a Downloads directory to pull the principal files from the [GeoNames](https://www.geonames.org) dump directory along with the incremental files.
````bash
python puller.py
````

There is a progress bar to show the progress of processing the 11 files, however the first file is the largest at several hundred MB


the process.py script will process through the incremental files and add them to the rabbitMQ server to queue changes to the database.  This only needs to run once a day after the puller script runs to queue the changes to be written to the database.


there is a run_queue_readers.sh script that will run and detach the queue readers that run in the background waiting for information to be written to the rabbitMQ queues to be processes by the readers and committed to the database.