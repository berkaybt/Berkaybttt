import sqlite3
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import List


@dataclass
class Incident:
    incident_id: int
    title: str
    device: str
    priority: str
    status: str
    resolution: str
    created_at: str


class IncidentService:
    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "CREATE TABLE IF NOT EXISTS incidents (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, device TEXT, priority TEXT, status TEXT, resolution TEXT, created_at TEXT)"
            )

    def create_incident(self, title: str, device: str, priority: str) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "INSERT INTO incidents (title, device, priority, status, resolution, created_at) VALUES (?, ?, ?, ?, ?, ?)",
                (title, device, priority, "Open", "", datetime.utcnow().isoformat()),
            )

    def list_incidents(self) -> List[Incident]:
        with sqlite3.connect(self.db_path) as conn:
            rows = conn.execute(
                "SELECT id, title, device, priority, status, resolution, created_at FROM incidents ORDER BY created_at DESC"
            ).fetchall()
        return [Incident(*row) for row in rows]
