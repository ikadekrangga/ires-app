import logging 
import json


logger = logging.getLogger("worker")
logger.setLevel(logging.INFO)

handler = logging.StreamHandler()
formatter = logging.Formatter("%(message)s")
handler.setFormatter(formatter)

logger.addHandler(handler)

def log(data: dict):
    logger.info(json.dumps(data))