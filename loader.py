import os

from dotenv import load_dotenv

load_dotenv()

DATABASE_HOST = os.getenv('DATABASE_HOST')
DATABASE_PASS = os.getenv('DATABASE_PASS')
DATABASE_USER = os.getenv('DATABASE_USER')
DATABASE_NAME = os.getenv('DATABASE_NAME')
