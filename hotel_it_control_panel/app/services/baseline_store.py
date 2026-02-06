import json
import sqlite3
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Dict, List


@dataclass
class Baseline:
    baseline_id: int
    data: Dict[str, float]
    created_at: str


class BaselineStore:
    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "CREATE TABLE IF NOT EXISTS baselines (id INTEGER PRIMARY KEY AUTOINCREMENT, data TEXT, created_at TEXT)"
            )

    def create(self, data: Dict[str, float]) -> int:
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.execute(
                "INSERT INTO baselines (data, created_at) VALUES (?, ?)",
                (json.dumps(data), datetime.utcnow().isoformat()),
            )
            return int(cursor.lastrowid)

    def latest(self) -> Dict[str, float]:
        with sqlite3.connect(self.db_path) as conn:
            row = conn.execute("SELECT data FROM baselines ORDER BY id DESC LIMIT 1").fetchone()
        return json.loads(row[0]) if row else {}
