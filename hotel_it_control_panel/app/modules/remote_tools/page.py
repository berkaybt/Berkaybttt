from PySide6.QtWidgets import (
    QFileDialog,
    QHBoxLayout,
    QLabel,
    QLineEdit,
    QListWidget,
    QPushButton,
    QVBoxLayout,
    QWidget,
)

from app.security.audit_logger import AuditLogger
from app.security.permissions import Permissions
from app.services.inventory_service import InventoryService
from app.services.remote_launch_service import RemoteLaunchService
from app.services.settings_service import SettingsService
from app.ui.notifications import NotificationManager


class RemoteToolsPage(QWidget):
    def __init__(
        self,
        settings: SettingsService,
        audit_logger: AuditLogger,
        notifier: NotificationManager,
        inventory: InventoryService,
        permissions: Permissions,
    ) -> None:
        super().__init__()
        self.settings = settings
        self.audit_logger = audit_logger
        self.notifier = notifier
        self.launcher = RemoteLaunchService()
        self.inventory = inventory

        self.ip_input = QLineEdit()
        self.ip_input.setPlaceholderText("Bağlanılacak IP")
        self.quick_copy_label = QLabel("")

        self.targets_list = QListWidget()
        self.load_inventory()
        self.targets_list.itemClicked.connect(self.fill_target)

        rdp_button = QPushButton("RDP Aç")
        rdp_button.clicked.connect(self.launch_rdp)

        anydesk_button = QPushButton("AnyDesk")
        anydesk_button.clicked.connect(lambda: self.launch_tool("anydesk_path"))

        rustdesk_button = QPushButton("RustDesk")
        rustdesk_button.clicked.connect(lambda: self.launch_tool("rustdesk_path"))

        teamviewer_button = QPushButton("TeamViewer")
        teamviewer_button.clicked.connect(lambda: self.launch_tool("teamviewer_path"))

        admin_tools = QHBoxLayout()
        admin_buttons = [
            self._admin_button("Event Viewer", "eventvwr.msc"),
            self._admin_button("Services", "services.msc"),
            self._admin_button("Computer Mgmt", "compmgmt.msc"),
        ]
        for button in admin_buttons:
            if not permissions.is_admin():
                button.setEnabled(False)
                button.setToolTip(permissions.admin_required_message())
            admin_tools.addWidget(button)

        path_buttons = QHBoxLayout()
        path_buttons.addWidget(self._path_button("AnyDesk Yolu", "anydesk_path"))
        path_buttons.addWidget(self._path_button("RustDesk Yolu", "rustdesk_path"))
        path_buttons.addWidget(self._path_button("TeamViewer Yolu", "teamviewer_path"))

        layout = QVBoxLayout()
        layout.addWidget(QLabel("Uzak Araçlar"))
        layout.addWidget(self.ip_input)

        row = QHBoxLayout()
        row.addWidget(rdp_button)
        row.addWidget(anydesk_button)
        row.addWidget(rustdesk_button)
        row.addWidget(teamviewer_button)
        layout.addLayout(row)
        layout.addLayout(path_buttons)
        layout.addLayout(admin_tools)
        layout.addWidget(QLabel("Kayıtlı Hedefler"))
        layout.addWidget(self.targets_list)
        layout.addWidget(QLabel("Quick Copy"))
        layout.addWidget(self.quick_copy_label)
        layout.addStretch(1)
        self.setLayout(layout)

    def _admin_button(self, label: str, tool: str) -> QPushButton:
        button = QPushButton(label)
        button.clicked.connect(lambda: self.launch_admin_tool(tool))
        return button

    def _path_button(self, label: str, key: str) -> QPushButton:
        button = QPushButton(label)
        button.clicked.connect(lambda: self.set_tool_path(key))
        return button

    def load_inventory(self) -> None:
        for item in self.inventory.list_items():
            self.targets_list.addItem(f"{item.hostname} ({item.ip})")

    def fill_target(self) -> None:
        text = self.targets_list.currentItem().text()
        if "(" in text:
            ip = text.split("(")[-1].strip(")")
            self.ip_input.setText(ip)
            self.quick_copy_label.setText(f"{ip} | {text.split('(')[0].strip()}")

    def launch_rdp(self) -> None:
        ip = self.ip_input.text().strip()
        if not ip:
            self.notifier.show_toast("IP adresi girin.")
            return
        self.launcher.launch_rdp(ip)
        self.audit_logger.log_action("rdp", "remote_tools", "ok", ip)

    def launch_tool(self, tool_key: str) -> None:
        path = self.settings.get_tool_path(tool_key)
        if not path:
            self.notifier.show_toast("Araç yolu ayarlanmamış.")
            return
        try:
            self.launcher.launch_tool(path)
            self.audit_logger.log_action("launch_tool", "remote_tools", "ok", path)
        except FileNotFoundError as exc:
            self.notifier.show_toast(str(exc))

    def launch_admin_tool(self, tool: str) -> None:
        self.launcher.launch_admin_tool(tool)
        self.audit_logger.log_action("admin_tool", "remote_tools", "ok", tool)

    def set_tool_path(self, tool_key: str) -> None:
        file_path, _ = QFileDialog.getOpenFileName(self, "Uygulama Seç", "", "Executable (*.exe)")
        if not file_path:
            return
        self.settings.set_tool_path(tool_key, file_path)
        self.notifier.show_toast("Yol kaydedildi.")
