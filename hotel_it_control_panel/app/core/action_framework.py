import json
import logging
import sqlite3
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Any, Callable, Dict, Optional


def _now() -> str:
    return datetime.utcnow().isoformat()


@dataclass
class Snapshot:
    name: str
    data: Dict[str, Any]


@dataclass
class ActionCheckResult:
    ok: bool
    snapshot: Optional[Snapshot]
    message: str = ""


@dataclass
class ActionExecResult:
    ok: bool
    payload: Any
    message: str = ""


@dataclass
class VerificationResult:
    status: str
    proof: Dict[str, Any]
    summary: str


class ActionSpec:
    def __init__(
        self,
        action_id: str,
        name: str,
        required_role: str,
        risk_level: str,
        precheck: Callable[[], ActionCheckResult],
        execute: Callable[[], ActionExecResult],
        postcheck: Callable[[], ActionCheckResult],
        verify: Callable[[ActionCheckResult, ActionExecResult, ActionCheckResult], VerificationResult],
    ) -> None:
        self.action_id = action_id
        self.name = name
        self.required_role = required_role
        self.risk_level = risk_level
        self.precheck = precheck
        self.execute = execute
        self.postcheck = postcheck
        self.verify = verify


class BaselineStore:
    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "CREATE TABLE IF NOT EXISTS verification_runs (id INTEGER PRIMARY KEY AUTOINCREMENT, action_id TEXT, status TEXT, summary TEXT, proof TEXT, created_at TEXT)"
            )

    def record(self, action_id: str, status: str, summary: str, proof: Dict[str, Any]) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "INSERT INTO verification_runs (action_id, status, summary, proof, created_at) VALUES (?, ?, ?, ?, ?)",
                (action_id, status, summary, json.dumps(proof), _now()),
            )

    def list_runs(self, limit: int = 200) -> list[tuple]:
        with sqlite3.connect(self.db_path) as conn:
            return conn.execute(
                "SELECT action_id, status, summary, created_at FROM verification_runs ORDER BY created_at DESC LIMIT ?",
                (limit,),
            ).fetchall()


class ActionRunner:
    def __init__(self, baseline_store: BaselineStore, proof_log_path: Path) -> None:
        self.baseline_store = baseline_store
        self.proof_logger = logging.getLogger("proof")
        handler = logging.FileHandler(proof_log_path, encoding="utf-8")
        handler.setFormatter(logging.Formatter("%(asctime)s | %(levelname)s | %(message)s"))
        if not self.proof_logger.handlers:
            self.proof_logger.addHandler(handler)
        self.proof_logger.setLevel(logging.INFO)

    def run(self, spec: ActionSpec) -> tuple[VerificationResult, ActionExecResult]:
        pre = spec.precheck()
        if not pre.ok:
            result = VerificationResult(status="FAIL", proof={"precheck": pre.message}, summary="Precheck failed")
            exec_result = ActionExecResult(ok=False, payload=None, message="precheck failed")
            self._record(spec.action_id, result)
            return result, exec_result
        exec_result = spec.execute()
        post = spec.postcheck()
        verification = spec.verify(pre, exec_result, post)
        self._record(spec.action_id, verification)
        return verification, exec_result

    def _record(self, action_id: str, verification: VerificationResult) -> None:
        self.baseline_store.record(action_id, verification.status, verification.summary, verification.proof)
        self.proof_logger.info("%s | %s | %s", action_id, verification.status, verification.summary)
