import zipfile
from datetime import datetime, timedelta
from pathlib import Path


class LogRetentionService:
    def __init__(self, logs_dir: Path, archive_dir: Path, retention_days: int) -> None:
        self.logs_dir = logs_dir
        self.archive_dir = archive_dir
        self.retention_days = retention_days

    def archive_old_logs(self) -> None:
        cutoff = datetime.now() - timedelta(days=self.retention_days)
        self.archive_dir.mkdir(parents=True, exist_ok=True)
        for log_file in self.logs_dir.glob("*.log"):
            if datetime.fromtimestamp(log_file.stat().st_mtime) < cutoff:
                archive_name = self.archive_dir / f"{log_file.stem}-{cutoff.strftime('%Y-%m')}.zip"
                with zipfile.ZipFile(archive_name, "a", zipfile.ZIP_DEFLATED) as archive:
                    archive.write(log_file, arcname=log_file.name)
                log_file.unlink(missing_ok=True)
