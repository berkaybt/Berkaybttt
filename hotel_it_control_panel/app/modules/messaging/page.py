import getpass
import platform
import sqlite3
from pathlib import Path

from PySide6.QtWidgets import (
    QHBoxLayout,
    QLabel,
    QListWidget,
    QPushButton,
    QTextEdit,
    QVBoxLayout,
    QWidget,
)

from app.security.audit_logger import AuditLogger
from app.services.messaging_client import MessagingClient
from app.services.settings_service import SettingsService
from app.ui.notifications import NotificationManager


class MessagingPage(QWidget):
    def __init__(self, settings: SettingsService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.settings = settings
        self.audit_logger = audit_logger
        self.notifier = notifier
        self.current_target = ""

        self.users_list = QListWidget()
        self.chat_view = QTextEdit()
        self.chat_view.setReadOnly(True)
        self.message_input = QTextEdit()
        self.send_button = QPushButton("Gönder")
        self.status_label = QLabel("Bağlanıyor...")

        self.send_button.clicked.connect(self.send_message)
        self.users_list.itemClicked.connect(self.select_user)

        left_layout = QVBoxLayout()
        left_layout.addWidget(QLabel("Online Kullanıcılar"))
        left_layout.addWidget(self.users_list)

        right_layout = QVBoxLayout()
        right_layout.addWidget(QLabel("Mesajlaşma"))
        right_layout.addWidget(self.status_label)
        right_layout.addWidget(self.chat_view)
        right_layout.addWidget(self.message_input)
        right_layout.addWidget(self.send_button)

        layout = QHBoxLayout()
        layout.addLayout(left_layout, 1)
        layout.addLayout(right_layout, 3)
        self.setLayout(layout)

        self.db_path = Path(__file__).resolve().parents[2] / "data" / "chat.db"
        self._init_db()

        identity = {
            "hostname": platform.node(),
            "username": getpass.getuser(),
        }
        self.client = MessagingClient(
            server_url=settings.settings.get("messaging", {}).get("server_url", ""),
            token=settings.settings.get("messaging", {}).get("token", ""),
            identity=identity,
        )
        self.client.connected.connect(self.handle_connected)
        self.client.disconnected.connect(self.handle_disconnected)
        self.client.users_updated.connect(self.update_users)
        self.client.message_received.connect(self.receive_message)
        self.client.error.connect(lambda msg: self.notifier.show_toast(f"Mesajlaşma hata: {msg}"))
        self.client.start()

    def _init_db(self) -> None:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, sender TEXT, target TEXT, message TEXT, timestamp TEXT)"
            )

    def update_users(self, users: list) -> None:
        self.users_list.clear()
        for user in users:
            label = f"{user['hostname']}|{user['username']}"
            self.users_list.addItem(label)

    def select_user(self) -> None:
        text = self.users_list.currentItem().text()
        self.current_target = text
        self.chat_view.append(f"Seçilen: {text}")

    def send_message(self) -> None:
        message = self.message_input.toPlainText().strip()
        if not message or not self.current_target:
            self.notifier.show_toast("Kullanıcı ve mesaj seçin.")
            return
        target = self.current_target
        self.client.send_message(target, message)
        self._save_message("me", target, message)
        self.chat_view.append(f"Ben -> {target}: {message}")
        self.message_input.clear()
        self.audit_logger.log_action("send_message", "messaging", "ok")

    def receive_message(self, payload: dict) -> None:
        sender = payload.get("sender", "")
        message = payload.get("message", "")
        self._save_message(sender, "me", message)
        self.chat_view.append(f"{sender}: {message}")

    def handle_connected(self) -> None:
        self.status_label.setText("Bağlı")
        self.notifier.show_toast("Mesajlaşma bağlı")

    def handle_disconnected(self) -> None:
        self.status_label.setText("Server offline")
        self.notifier.show_toast("Mesajlaşma kesildi")

    def _save_message(self, sender: str, target: str, message: str) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                "INSERT INTO messages (sender, target, message, timestamp) VALUES (?, ?, ?, datetime('now'))",
                (sender, target, message),
            )
