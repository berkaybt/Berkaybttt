from PySide6.QtWidgets import (
    QFormLayout,
    QHBoxLayout,
    QLabel,
    QLineEdit,
    QPushButton,
    QWidget,
)

from app.security.audit_logger import AuditLogger
from app.services.settings_service import SettingsService
from app.ui.notifications import NotificationManager


class SettingsPage(QWidget):
    def __init__(self, settings: SettingsService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.settings = settings
        self.audit_logger = audit_logger
        self.notifier = notifier

        self.default_ip = QLineEdit(settings.settings.get("default_ip_range", ""))
        self.anydesk_path = QLineEdit(settings.get_tool_path("anydesk_path"))
        self.rustdesk_path = QLineEdit(settings.get_tool_path("rustdesk_path"))
        self.teamviewer_path = QLineEdit(settings.get_tool_path("teamviewer_path"))
        self.messaging_url = QLineEdit(settings.settings.get("messaging", {}).get("server_url", ""))
        self.messaging_token = QLineEdit(settings.settings.get("messaging", {}).get("token", ""))
        self.role = QLineEdit(settings.settings.get("role", "USER"))
        self.log_retention = QLineEdit(str(settings.settings.get("log_retention_days", 30)))
        self.central_policy_path = QLineEdit(settings.settings.get("central_policy_path", ""))

        save_button = QPushButton("Kaydet")
        save_button.clicked.connect(self.save)

        form = QFormLayout()
        form.addRow(QLabel("Default IP Range"), self.default_ip)
        form.addRow(QLabel("AnyDesk Path"), self.anydesk_path)
        form.addRow(QLabel("RustDesk Path"), self.rustdesk_path)
        form.addRow(QLabel("TeamViewer Path"), self.teamviewer_path)
        form.addRow(QLabel("Messaging Server"), self.messaging_url)
        form.addRow(QLabel("Messaging Token"), self.messaging_token)
        form.addRow(QLabel("Role"), self.role)
        form.addRow(QLabel("Log Retention (days)"), self.log_retention)
        form.addRow(QLabel("Central Policy Path"), self.central_policy_path)

        layout = QHBoxLayout()
        layout.addLayout(form)
        layout.addWidget(save_button)
        self.setLayout(layout)

    def save(self) -> None:
        self.settings.settings["default_ip_range"] = self.default_ip.text().strip()
        self.settings.set_tool_path("anydesk_path", self.anydesk_path.text().strip())
        self.settings.set_tool_path("rustdesk_path", self.rustdesk_path.text().strip())
        self.settings.set_tool_path("teamviewer_path", self.teamviewer_path.text().strip())
        self.settings.settings.setdefault("messaging", {})["server_url"] = self.messaging_url.text().strip()
        self.settings.settings.setdefault("messaging", {})["token"] = self.messaging_token.text().strip()
        self.settings.settings["role"] = self.role.text().strip()
        retention = self.log_retention.text().strip()
        if retention.isdigit():
            self.settings.settings["log_retention_days"] = int(retention)
        self.settings.settings["central_policy_path"] = self.central_policy_path.text().strip()
        self.settings.save_settings()
        self.audit_logger.log_action("settings_save", "settings", "ok")
        self.notifier.show_toast("Ayarlar kaydedildi.")
