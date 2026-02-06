from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
import subprocess


@dataclass
class BuildInfo:
    version: str
    commit: str
    build_date: str


def get_build_info(project_root: Path) -> BuildInfo:
    commit = "unknown"
    try:
        commit = subprocess.check_output(["git", "rev-parse", "--short", "HEAD"], cwd=project_root).decode().strip()
    except (subprocess.SubprocessError, FileNotFoundError):
        commit = "unknown"
    build_date = datetime.utcnow().isoformat()
    return BuildInfo(version="0.1.0", commit=commit, build_date=build_date)
