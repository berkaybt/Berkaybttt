import os
import shutil
from pathlib import Path
from typing import List, Tuple


def get_temp_paths() -> List[Path]:
    paths = [Path(os.getenv("TEMP", "")), Path("C:/Windows/Temp")]
    return [path for path in paths if path.exists()]


def calculate_temp_size() -> Tuple[int, List[Path]]:
    total = 0
    paths = []
    for temp_path in get_temp_paths():
        for root, _, files in os.walk(temp_path):
            for name in files:
                file_path = Path(root) / name
                try:
                    total += file_path.stat().st_size
                except OSError:
                    continue
        paths.append(temp_path)
    return total, paths


def clean_temp_files() -> None:
    for temp_path in get_temp_paths():
        for item in temp_path.iterdir():
            try:
                if item.is_dir():
                    shutil.rmtree(item, ignore_errors=True)
                else:
                    item.unlink(missing_ok=True)
            except OSError:
                continue
