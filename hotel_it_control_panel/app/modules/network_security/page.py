from datetime import datetime

from PySide6.QtWidgets import QTableWidget, QTableWidgetItem, QVBoxLayout, QWidget

from app.security.audit_logger import AuditLogger
from app.services.anomaly_engine import detect_new_devices
from app.services.inventory_service import InventoryService
from app.services.network_security_service import build_snapshot
from app.ui.notifications import NotificationManager


class NetworkSecurityPage(QWidget):
    def __init__(self, inventory: InventoryService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.inventory = inventory
        self.audit_logger = audit_logger
        self.notifier = notifier

        self.table = QTableWidget(0, 5)
        self.table.setHorizontalHeaderLabels(["Timestamp", "IP", "MAC", "Type", "Severity"])

        layout = QVBoxLayout()
        layout.addWidget(self.table)
        self.setLayout(layout)
        self.refresh()

    def refresh(self) -> None:
        known_ips = [item.ip for item in self.inventory.list_items()]
        current_ips = known_ips
        events = detect_new_devices(known_ips, current_ips, datetime.utcnow().isoformat())
        snapshot = build_snapshot(events)
        self.table.setRowCount(len(snapshot.events))
        for row, event in enumerate(snapshot.events):
            self.table.setItem(row, 0, QTableWidgetItem(event.timestamp))
            self.table.setItem(row, 1, QTableWidgetItem(event.ip))
            self.table.setItem(row, 2, QTableWidgetItem(event.mac))
            self.table.setItem(row, 3, QTableWidgetItem(event.event_type))
            self.table.setItem(row, 4, QTableWidgetItem(event.severity))
        if snapshot.events:
            self.audit_logger.log_action("network_security", "network_security", "ok", details=str(len(snapshot.events)))
            self.notifier.show_toast("Ağ güvenliği olayları güncellendi.")
