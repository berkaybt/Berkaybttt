import sqlite3
from dataclasses import dataclass
from pathlib import Path
from typing import List


@dataclass
class InventoryItem:
    hostname: str
    ip: str
    location: str
    tags: str


class InventoryService:
    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "CREATE TABLE IF NOT EXISTS inventory (id INTEGER PRIMARY KEY AUTOINCREMENT, hostname TEXT, ip TEXT, location TEXT, tags TEXT)"
            )

    def list_items(self) -> List[InventoryItem]:
        with sqlite3.connect(self.db_path) as conn:
            rows = conn.execute("SELECT hostname, ip, location, tags FROM inventory ORDER BY hostname").fetchall()
        return [InventoryItem(*row) for row in rows]

    def add_item(self, item: InventoryItem) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "INSERT INTO inventory (hostname, ip, location, tags) VALUES (?, ?, ?, ?)",
                (item.hostname, item.ip, item.location, item.tags),
            )

    def import_items(self, items: List[InventoryItem]) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.executemany(
                "INSERT INTO inventory (hostname, ip, location, tags) VALUES (?, ?, ?, ?)",
                [(item.hostname, item.ip, item.location, item.tags) for item in items],
            )
