import json
import sys
import traceback
from datetime import datetime
from pathlib import Path
from typing import Any, Dict


def install_exception_hook(report_path: Path) -> None:
    def handle_exception(exc_type, exc_value, exc_traceback) -> None:  # type: ignore[no-untyped-def]
        report: Dict[str, Any] = {
            "timestamp": datetime.utcnow().isoformat(),
            "type": str(exc_type.__name__),
            "message": str(exc_value),
            "traceback": "".join(traceback.format_tb(exc_traceback)),
        }
        report_path.parent.mkdir(parents=True, exist_ok=True)
        report_path.write_text(json.dumps(report, indent=2), encoding="utf-8")
        sys.__excepthook__(exc_type, exc_value, exc_traceback)

    sys.excepthook = handle_exception
