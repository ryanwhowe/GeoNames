import os

import mysql.connector
from dotenv import load_dotenv
from mysql.connector import Error

load_dotenv()
database_config = {
    'host': os.getenv('DATABASE_HOST'),
    'password': os.getenv('DATABASE_PASS'),
    'user': os.getenv('DATABASE_USER'),
    'database': os.getenv('DATABASE_NAME')
}


def main(database_config):
    try:
        conn = mysql.connector.connect(**database_config)
        if conn.is_connected():
            db_info = conn.get_server_info()
            print("connected ", db_info)
            cursor = conn.cursor()
            cursor.execute("SELECT database();")
            record = cursor.fetchone()
            print("Database: ", record)

    except Error as e:
        print("Error while trying to connect to MySQL :: ", e)


main(database_config)
