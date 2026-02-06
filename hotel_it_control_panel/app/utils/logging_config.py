import getpass
import logging
from logging.handlers import RotatingFileHandler
from pathlib import Path


class UserFilter(logging.Filter):
    def filter(self, record: logging.LogRecord) -> bool:  # noqa: D401
        record.user = getpass.getuser()
        return True


def setup_logging(logs_dir: Path) -> None:
    logs_dir.mkdir(parents=True, exist_ok=True)

    app_handler = RotatingFileHandler(logs_dir / "app.log", maxBytes=5_000_000, backupCount=3, encoding="utf-8")
    formatter = logging.Formatter(
        "%(asctime)s | %(levelname)s | %(name)s | %(user)s | %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S",
    )
    app_handler.setFormatter(formatter)
    app_handler.addFilter(UserFilter())

    logging.basicConfig(level=logging.INFO, handlers=[app_handler])
