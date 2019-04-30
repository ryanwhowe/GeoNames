import csv
import glob
import json
import os

import pika
from dotenv import load_dotenv
from tqdm import tqdm

from GeoNames import GeoNames, dump

load_dotenv()


def process_file(filename, type):
    rabbit_user = os.getenv('RABBIT_USER')
    rabbit_pass = os.getenv('RABBIT_PASS')
    credentials = pika.PlainCredentials(rabbit_user, rabbit_pass)
    connection = pika.BlockingConnection(
        pika.ConnectionParameters(
            host='192.168.2.101',
            credentials=credentials
        )
    )
    channel = connection.channel()
    channel.queue_declare(queue=type, durable=True)
    with open(filename, 'r') as file:
        csv_reader = csv.reader(file, delimiter="\t")
        for row in csv_reader:
            channel.basic_publish(
                exchange='',
                routing_key=type,
                body=process_row(row, type),
                properties=pika.BasicProperties(delivery_mode=2)
            )
    os.unlink(filename)
    connection.close()


def process_row(row, type):
    """
    There are four different file types with different data needed for processing
    :param row: list csvreader row data
    :param type: string
    :return: string json object to be written to queue
    """
    if type == "deletes":
        return json.dumps({'geonameid': row[0]})
    elif type == "modifications":
        return json.dumps({
            'geonameid': row[0],
            'name': row[1],
            'asciiname': row[2],
            'alternatenames': row[3],
            'latitude': row[4],
            'longitude': row[5],
            'fclass': row[6],
            'fcode': row[7],
            'country': row[8],
            'cc2': row[9],
            'admin1': row[10],
            'admin2': row[11],
            'admin3': row[12],
            'admin4': row[13],
            'population': row[14],
            'elevation': row[15],
            'gtopo30': row[16],
            'timezone': row[17],
            'moddate': row[18]
        })
    elif type == "alternateNamesDeletes":
        return json.dumps({'alternatenameId': row[0]})
    elif type == "alternateNamesModifications":
        return json.dumps({
            'alternatenameId': row[0],
            'geonameid': row[1],
            'isoLanguage': row[2],
            'alternateName': row[3],
            'isPreferredName': row[4],
            'isShortName': row[5],
            'isColloquial': row[6],
            'isHistoric': row[7]
        })
    raise IndexError('Invalid process type')


def main():
    currentdir = os.path.dirname(os.path.realpath(__file__))
    downloads = GeoNames.get_dir(currentdir, 'downloads')
    total = len(dump.INCREMENTAL)

    with tqdm(total=total) as pbar:

        for file in dump.INCREMENTAL:
            files = glob.glob(downloads + file + '*')
            for to_process_file in files:
                process_file(to_process_file, file[:-1])
            pbar.update(1)


main()
