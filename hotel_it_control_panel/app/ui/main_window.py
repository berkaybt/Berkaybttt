import logging

from pathlib import Path

from PySide6.QtCore import Qt
from PySide6.QtGui import QAction, QKeySequence
from PySide6.QtWidgets import QHBoxLayout, QMainWindow, QStackedWidget, QWidget

from app.core.action_framework import BaselineStore as VerificationRunStore
from app.core.module_loader import load_manifests
from app.modules.about.page import AboutPage
from app.modules.approvals.page import ApprovalsPage
from app.modules.dashboard.page import DashboardPage
from app.modules.incidents.page import IncidentsPage
from app.modules.inventory.page import InventoryPage
from app.modules.messaging.page import MessagingPage
from app.modules.maintenance_verification.page import MaintenanceVerificationPage
from app.modules.network_monitor.page import NetworkMonitorPage
from app.modules.network_security.page import NetworkSecurityPage
from app.modules.verification_center.page import VerificationCenterPage
from app.modules.outlook_tools.page import OutlookToolsPage
from app.modules.pc_maintenance.page import MaintenancePage
from app.modules.remote_tools.page import RemoteToolsPage
from app.modules.reports.page import ReportsPage
from app.modules.scripts_runner.page import ScriptsRunnerPage
from app.modules.server_connections.page import ServerConnectionsPage
from app.modules.settings_page.page import SettingsPage
from app.modules.ticket_notes.page import TicketNotesPage
from app.security.audit_logger import AuditLogger
from app.security.permissions import Permissions
from app.services.approval_service import ApprovalService
from app.services.baseline_store import BaselineStore as VerificationBaselineStore
from app.services.incident_service import IncidentService
from app.services.inventory_service import InventoryService
from app.services.verification_service import VerificationService
from app.services.settings_service import SettingsService
from app.ui.command_palette import CommandPalette
from app.ui.notifications import NotificationManager
from app.ui.sidebar import Sidebar
from app.ui.theme import ThemeManager
from app.ui.topbar import TopBar
from app.ui.tray import TrayIcon


class MainWindow(QMainWindow):
    def __init__(self, settings: SettingsService, permissions: Permissions, audit_logger: AuditLogger) -> None:
        super().__init__()
        self.logger = logging.getLogger(self.__class__.__name__)
        self.settings = settings
        self.permissions = permissions
        self.audit_logger = audit_logger
        self.notifier = NotificationManager(self)
        self.theme_manager = ThemeManager(settings.settings.get("theme", "dark"))

        self.sidebar = Sidebar()
        self.stack = QStackedWidget()
        self.tray = TrayIcon(self)
        self.tray.show()

        db_path = Path(__file__).resolve().parents[2] / "data" / "app.db"
        inventory_service = InventoryService(db_path)
        approval_service = ApprovalService(db_path)
        incident_service = IncidentService(db_path)
        verification_run_store = VerificationRunStore(db_path)
        verification_baseline = VerificationBaselineStore(db_path)
        verification_service = VerificationService(verification_baseline)

        self.pages = {
            "dashboard": DashboardPage(audit_logger=audit_logger),
            "inventory": InventoryPage(inventory=inventory_service, audit_logger=audit_logger, notifier=self.notifier),
            "network_monitor": NetworkMonitorPage(
                settings=settings,
                audit_logger=audit_logger,
                notifier=self.notifier,
                inventory=inventory_service,
            ),
            "remote_tools": RemoteToolsPage(
                settings=settings,
                audit_logger=audit_logger,
                notifier=self.notifier,
                inventory=inventory_service,
                permissions=permissions,
            ),
            "server_connections": ServerConnectionsPage(),
            "pc_maintenance": MaintenancePage(
                approvals=approval_service,
                audit_logger=audit_logger,
                notifier=self.notifier,
            ),
            "outlook_tools": OutlookToolsPage(),
            "messaging": MessagingPage(settings=settings, audit_logger=audit_logger, notifier=self.notifier),
            "network_security": NetworkSecurityPage(
                inventory=inventory_service, audit_logger=audit_logger, notifier=self.notifier
            ),
            "maintenance_verification": MaintenanceVerificationPage(
                verification=verification_service, audit_logger=audit_logger, notifier=self.notifier
            ),
            "approvals": ApprovalsPage(approvals=approval_service, audit_logger=audit_logger, notifier=self.notifier),
            "incidents": IncidentsPage(incidents=incident_service, audit_logger=audit_logger, notifier=self.notifier),
            "scripts_runner": ScriptsRunnerPage(),
            "verification_center": VerificationCenterPage(baseline_store=verification_run_store),
            "ticket_notes": TicketNotesPage(),
            "reports": ReportsPage(),
            "settings_page": SettingsPage(settings=settings, audit_logger=audit_logger, notifier=self.notifier),
            "about": AboutPage(project_root=Path(__file__).resolve().parents[2]),
        }

        self.page_index: dict[str, int] = {}
        self.modules = load_manifests(Path(__file__).resolve().parents[2] / "modules")
        for module in self.modules:
            page = self.pages.get(module.key)
            if not page:
                continue
            self.page_index[module.key] = self.stack.count()
            self.stack.addWidget(page)
            button = self.sidebar.add_button(module.key, module.title)
            button.clicked.connect(lambda _, key=module.key: self.stack.setCurrentIndex(self.page_index[key]))

        self.pages["dashboard"].quick_action_requested.connect(self.handle_quick_action)

        content_layout = QHBoxLayout()
        content_layout.addWidget(self.sidebar)
        content_layout.addWidget(self.stack, 1)

        container = QWidget()
        container.setLayout(content_layout)
        self.setCentralWidget(container)
        self.setWindowTitle("Hotel IT Suite")
        self.resize(1200, 720)

        self.setup_toolbar()
        self.setup_shortcuts()
        self.apply_feature_flags()
        self.apply_theme()
        self.setStatusBarMessage()
        if settings.policy_source_path != settings.policy_path:
            self.notifier.show_toast("Central policy yüklendi.")

    def setup_toolbar(self) -> None:
        toolbar = TopBar(self.permissions)
        toolbar.theme_button.clicked.connect(self.toggle_theme)
        self.addToolBar(Qt.TopToolBarArea, toolbar)

        action_palette = QAction("Komut Paleti", self)
        action_palette.setShortcut(QKeySequence("Ctrl+K"))
        action_palette.triggered.connect(self.open_command_palette)
        self.addAction(action_palette)

    def setup_shortcuts(self) -> None:
        for idx, key in enumerate(list(self.page_index.keys())[:9], start=1):
            action = QAction(self)
            action.setShortcut(QKeySequence(f"Ctrl+{idx}"))
            action.triggered.connect(lambda _, page_key=key: self.stack.setCurrentIndex(self.page_index[page_key]))
            self.addAction(action)

    def apply_feature_flags(self) -> None:
        features = self.settings.admin_policy.get("features", {})
        is_admin = self.permissions.is_admin()
        if self.settings.admin_policy.get("safe_mode"):
            for key, button in self.sidebar.buttons.items():
                if key != "dashboard":
                    button.setEnabled(False)
            return
        if self.settings.admin_policy.get("read_only") or self.settings.settings.get("role") == "AUDIT":
            for key, button in self.sidebar.buttons.items():
                if key not in {"dashboard", "inventory", "reports"}:
                    button.setEnabled(False)
        for module in self.modules:
            if module.requires_admin and not is_admin:
                button = self.sidebar.buttons.get(module.key)
                if button:
                    button.setEnabled(False)
                    button.setToolTip(self.permissions.admin_required_message())
        for key, enabled in features.items():
            button = self.sidebar.buttons.get(key)
            if button and not enabled:
                button.setEnabled(False)

    def setStatusBarMessage(self) -> None:
        self.statusBar().showMessage("Hazır")

    def open_command_palette(self) -> None:
        commands = [
            "Temp temizle",
            "Ağ tarama başlat",
            "RDP aç",
            "Mesajlaşma aç",
        ]
        palette = CommandPalette(commands)
        palette.exec()

    def toggle_theme(self) -> None:
        palette = self.theme_manager.toggle_theme()
        self.setPalette(palette)
        self.settings.settings["theme"] = "dark" if self.theme_manager.is_dark else "light"
        self.settings.save_settings()

    def apply_theme(self) -> None:
        self.setPalette(self.theme_manager.get_palette())

    def handle_quick_action(self, action: str) -> None:
        if action == "temp_cleanup":
            self.notifier.show_toast("Temp temizleme planlandı.")
        if action == "network_scan":
            self.stack.setCurrentIndex(1)
        if action == "rdp":
            self.stack.setCurrentIndex(2)
