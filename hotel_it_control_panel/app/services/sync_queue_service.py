import sqlite3
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import List


@dataclass
class QueueItem:
    item_id: int
    action: str
    payload: str
    status: str
    created_at: str


class SyncQueueService:
    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "CREATE TABLE IF NOT EXISTS sync_queue (id INTEGER PRIMARY KEY AUTOINCREMENT, action TEXT, payload TEXT, status TEXT, created_at TEXT)"
            )

    def enqueue(self, action: str, payload: str) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "INSERT INTO sync_queue (action, payload, status, created_at) VALUES (?, ?, ?, ?)",
                (action, payload, "PENDING", datetime.utcnow().isoformat()),
            )

    def list_pending(self) -> List[QueueItem]:
        with sqlite3.connect(self.db_path) as conn:
            rows = conn.execute(
                "SELECT id, action, payload, status, created_at FROM sync_queue WHERE status='PENDING'"
            ).fetchall()
        return [QueueItem(*row) for row in rows]
