import logging
import sys
from pathlib import Path

from PySide6.QtWidgets import QApplication

from app.core.exception_handler import install_exception_hook
from app.security.audit_logger import AuditLogger
from app.security.permissions import Permissions
from app.services.log_retention_service import LogRetentionService
from app.services.policy_service import PolicyService
from app.services.settings_service import SettingsService
from app.ui.main_window import MainWindow
from app.utils.logging_config import setup_logging


def run() -> int:
    base_dir = Path(__file__).resolve().parents[1]
    logs_dir = base_dir / "app" / "logs"
    config_dir = base_dir / "app" / "config"

    setup_logging(logs_dir)
    install_exception_hook(logs_dir / "crash_report.json")

    settings = SettingsService(config_dir / "settings.json", config_dir / "admin_policy.json")
    central_path = settings.get_central_policy_path()
    policy_service = PolicyService(
        local_policy_path=config_dir / "admin_policy.json",
        central_policy_path=Path(central_path) if central_path else None,
    )
    selected_policy = policy_service.select_policy_path()
    if selected_policy != settings.policy_path:
        settings.admin_policy = settings._load(selected_policy, settings.admin_policy)  # type: ignore[attr-defined]
        settings.policy_source_path = selected_policy

    retention_days = int(settings.settings.get("log_retention_days", 30))
    LogRetentionService(logs_dir, base_dir / "app" / "data" / "archive", retention_days).archive_old_logs()
    audit_logger = AuditLogger(logs_dir / "actions.csv", logs_dir / "security.log")

    app = QApplication(sys.argv)
    app.setApplicationName("Hotel IT Suite")
    app.setOrganizationName("Hotel IT")

    permissions = Permissions()
    window = MainWindow(settings=settings, permissions=permissions, audit_logger=audit_logger)
    window.show()

    logging.getLogger(__name__).info("Application started")
    return app.exec()
