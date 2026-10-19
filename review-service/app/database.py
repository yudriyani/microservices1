import os

from dotenv import load_dotenv
from pymongo import ASCENDING, MongoClient

load_dotenv()

_db = None

def init_db():
    global _db

    client = MongoClient(
        os.getenv("MONGO_URI", "mongodb://localhost:27017"),
        tz_aware=True,
        serverSelectionTimeoutMS=3000,
    )

    _db = client[os.getenv("MONGO_DB_NAME", "reviewdb")]

    reviews = _db["reviews"]

    reviews.create_index(
        [("user_id", ASCENDING), ("product_id", ASCENDING)],
        unique=True,
    )

    reviews.create_index("product_id")


def get_collection():
    if _db is None:
        raise RuntimeError(
            "Database belum diinisialisasi, panggil init_db() lebih dulu"
        )

    return _db["reviews"]
