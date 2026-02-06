import csv
import getpass
import logging
import socket
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Mapping, Optional


class AuditLogger:
    def __init__(self, csv_path: Path, security_log_path: Path) -> None:
        self.csv_path = csv_path
        self.security_logger = logging.getLogger("security")
        self.security_handler = logging.FileHandler(security_log_path, encoding="utf-8")
        formatter = logging.Formatter(
            "%(asctime)s | %(levelname)s | %(name)s | %(message)s",
            datefmt="%Y-%m-%d %H:%M:%S",
        )
        self.security_handler.setFormatter(formatter)
        self.security_logger.addHandler(self.security_handler)
        self.security_logger.setLevel(logging.INFO)

        if not csv_path.exists():
            csv_path.parent.mkdir(parents=True, exist_ok=True)
            with csv_path.open("w", newline="", encoding="utf-8") as handle:
                writer = csv.writer(handle)
                writer.writerow(
                    [
                        "timestamp",
                        "user",
                        "hostname",
                        "ip",
                        "action_id",
                        "module",
                        "result",
                        "duration_ms",
                        "requested_by",
                        "approved_by",
                        "params",
                        "details",
                    ]
                )

    def log_action(
        self,
        action: str,
        module: str,
        result: str,
        details: str = "",
        params: Optional[Mapping[str, str]] = None,
        duration_ms: Optional[float] = None,
        requested_by: str = "",
        approved_by: str = "",
    ) -> None:
        row: Dict[str, str] = {
            "timestamp": datetime.utcnow().isoformat(),
            "user": getpass.getuser(),
            "hostname": socket.gethostname(),
            "ip": self._get_ip_address(),
            "action_id": action,
            "module": module,
            "result": result,
            "duration_ms": f"{duration_ms:.2f}" if duration_ms is not None else "",
            "requested_by": requested_by,
            "approved_by": approved_by,
            "params": self._format_params(params),
            "details": details,
        }
        with self.csv_path.open("a", newline="", encoding="utf-8") as handle:
            writer = csv.writer(handle)
            writer.writerow(row.values())

    def _format_params(self, params: Optional[Mapping[str, str]]) -> str:
        if not params:
            return ""
        return ";".join([f"{key}={value}" for key, value in params.items()])

    def _get_ip_address(self) -> str:
        try:
            return socket.gethostbyname(socket.gethostname())
        except socket.gaierror:
            return ""

    def get_recent_actions(self, limit: int = 10) -> List[Dict[str, str]]:
        if not self.csv_path.exists():
            return []
        with self.csv_path.open("r", encoding="utf-8") as handle:
            reader = csv.DictReader(handle)
            rows = list(reader)
        return rows[-limit:]

    def log_security_event(self, message: str) -> None:
        self.security_logger.info(message)
