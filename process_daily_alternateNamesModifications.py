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
        INSERT INTO `alternatename`(`alternatenameId`, `geonameid`, `isoLanguage`, `alternateName`, `isPreferredName`, `isShortName`, `isColloquial`, `isHistoric`) VALUES 
        (%s,%s,%s,%s,%s,%s,%s,%s)
        ON DUPLICATE KEY UPDATE
            `geonameid` = VALUES(`geonameid`), 
            `isoLanguage` = VALUES(`isoLanguage`), 
            `alternateName` = VALUES(`alternateName`), 
            `isPreferredName` = VALUES(`isPreferredName`), 
            `isShortName` = VALUES(`isShortName`), 
            `isColloquial` = VALUES(`isColloquial`), 
            `isHistoric` = VALUES(`isHistoric`)
        """
        insert_tuple = (data['alternatenameId'], data['geonameid'], data['isoLanguage'],
                        data['alternateName'], data['isPreferredName'], data['isShortName'],
                        data['isColloquial'], data['isHistoric']
                        )
        cursor.execute(query, insert_tuple)
        conn.commit()
    ch.basic_ack(delivery_tag=method.delivery_tag)


def modifications():
    channel.basic_qos(prefetch_count=1)
    channel.basic_consume(queue='alternateNamesModifications', on_message_callback=modification_process)
    channel.start_consuming()


def main():
    modifications()


main()
