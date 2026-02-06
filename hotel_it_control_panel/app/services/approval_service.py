import sqlite3
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import List


@dataclass
class ApprovalRequest:
    request_id: int
    action: str
    requested_by: str
    status: str
    approved_by: str
    created_at: str


class ApprovalService:
    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "CREATE TABLE IF NOT EXISTS approvals (id INTEGER PRIMARY KEY AUTOINCREMENT, action TEXT, requested_by TEXT, status TEXT, approved_by TEXT, created_at TEXT)"
            )

    def create_request(self, action: str, requested_by: str) -> int:
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.execute(
                "INSERT INTO approvals (action, requested_by, status, approved_by, created_at) VALUES (?, ?, ?, ?, ?)",
                (action, requested_by, "PENDING", "", datetime.utcnow().isoformat()),
            )
            return int(cursor.lastrowid)

    def list_pending(self) -> List[ApprovalRequest]:
        with sqlite3.connect(self.db_path) as conn:
            rows = conn.execute(
                "SELECT id, action, requested_by, status, approved_by, created_at FROM approvals WHERE status='PENDING'"
            ).fetchall()
        return [ApprovalRequest(*row) for row in rows]

    def approve(self, request_id: int, approved_by: str) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "UPDATE approvals SET status='APPROVED', approved_by=? WHERE id=?",
                (approved_by, request_id),
            )
