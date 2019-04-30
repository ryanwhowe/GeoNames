import json
import os

import mysql.connector
import pika
from dotenv import load_dotenv

load_dotenv()

database_config = {
    'host': os.getenv('DATABASE_HOST'),
    'password': os.getenv('DATABASE_PASS'),
    'user': os.getenv('DATABASE_USER'),
    'database': os.getenv('DATABASE_NAME')
}
conn = mysql.connector.connect(**database_config)
conn.sql_mode = ''
cursor = conn.cursor()

rabbit_user = os.getenv('RABBITMQ_USER')
rabbit_pass = os.getenv('RABBITMQ_PASS')
credentials = pika.PlainCredentials(rabbit_user, rabbit_pass)
connection = pika.BlockingConnection(
    pika.ConnectionParameters(
        host=os.getenv('RABBITMQ_HOST'),
        credentials=credentials
    )
)
channel = connection.channel()


def modification_process(ch, method, props, body):
    data = json.loads(body)
    if conn.is_connected():
        query = """
        INSERT INTO `geoname`(`geonameid`, `name`, `asciiname`, `alternatenames`, `latitude`, `longitude`, `fclass`, `fcode`, `country`, `cc2`, `admin1`, `admin2`, `admin3`, `admin4`, `population`, `elevation`, `gtopo30`, `timezone`, `moddate`) VALUES 
            (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
        ON DUPLICATE KEY UPDATE
            `name` = VALUES(`name`), 
            `asciiname` = VALUES(`asciiname`), 
            `alternatenames` = VALUES(`alternatenames`), 
            `latitude` = VALUES(`latitude`), 
            `longitude` = VALUES(`longitude`), 
            `fclass` = VALUES(`fclass`), 
            `fcode` = VALUES(`fcode`), 
            `country` = VALUES(`country`), 
            `cc2` = VALUES(`cc2`), 
            `admin1` = VALUES(`admin1`), 
            `admin2` = VALUES(`admin2`), 
            `admin3` = VALUES(`admin3`), 
            `admin4` = VALUES(`admin4`), 
            `population` = VALUES(`population`), 
            `elevation` = VALUES(`elevation`), 
            `gtopo30` = VALUES(`gtopo30`), 
            `timezone` = VALUES(`timezone`), 
            `moddate` = VALUES(`moddate`)
        """
        insert_tuple = (data['geonameid'], data['name'], data['asciiname'],
                        data['alternatenames'], data['latitude'], data['longitude'],
                        data['fclass'], data['fcode'], data['country'], data['cc2'],
                        data['admin1'], data['admin2'], data['admin3'], data['admin4'],
                        data['population'], data['elevation'], data['gtopo30'],
                        data['timezone'], data['moddate'])
        cursor.execute(query, insert_tuple)
        conn.commit()
    ch.basic_ack(delivery_tag=method.delivery_tag)


def modifications():
    channel.basic_qos(prefetch_count=1)
    channel.basic_consume(queue='modifications', on_message_callback=modification_process)
    channel.start_consuming()


def main():
    modifications()


main()
