import csv
from pathlib import Path
from typing import List

from PySide6.QtWidgets import (
    QFileDialog,
    QHBoxLayout,
    QLabel,
    QLineEdit,
    QPushButton,
    QTableWidget,
    QTableWidgetItem,
    QVBoxLayout,
    QWidget,
)

from app.security.audit_logger import AuditLogger
from app.services.inventory_service import InventoryItem, InventoryService
from app.ui.notifications import NotificationManager


class InventoryPage(QWidget):
    def __init__(self, inventory: InventoryService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.inventory = inventory
        self.audit_logger = audit_logger
        self.notifier = notifier

        self.hostname_input = QLineEdit()
        self.ip_input = QLineEdit()
        self.location_input = QLineEdit()
        self.tags_input = QLineEdit()

        add_button = QPushButton("Ekle")
        add_button.clicked.connect(self.add_item)
        import_button = QPushButton("CSV Import")
        import_button.clicked.connect(self.import_csv)
        export_button = QPushButton("CSV Export")
        export_button.clicked.connect(self.export_csv)

        form_layout = QHBoxLayout()
        form_layout.addWidget(self.hostname_input)
        form_layout.addWidget(self.ip_input)
        form_layout.addWidget(self.location_input)
        form_layout.addWidget(self.tags_input)
        form_layout.addWidget(add_button)

        actions_layout = QHBoxLayout()
        actions_layout.addWidget(import_button)
        actions_layout.addWidget(export_button)

        self.table = QTableWidget(0, 4)
        self.table.setHorizontalHeaderLabels(["Hostname", "IP", "Konum", "Etiketler"])

        layout = QVBoxLayout()
        layout.addWidget(QLabel("Cihaz Envanteri"))
        layout.addLayout(form_layout)
        layout.addLayout(actions_layout)
        layout.addWidget(self.table)
        self.setLayout(layout)

        self.refresh()

    def refresh(self) -> None:
        items = self.inventory.list_items()
        self.table.setRowCount(len(items))
        for row, item in enumerate(items):
            self.table.setItem(row, 0, QTableWidgetItem(item.hostname))
            self.table.setItem(row, 1, QTableWidgetItem(item.ip))
            self.table.setItem(row, 2, QTableWidgetItem(item.location))
            self.table.setItem(row, 3, QTableWidgetItem(item.tags))

    def add_item(self) -> None:
        item = InventoryItem(
            hostname=self.hostname_input.text().strip(),
            ip=self.ip_input.text().strip(),
            location=self.location_input.text().strip(),
            tags=self.tags_input.text().strip(),
        )
        if not item.hostname or not item.ip:
            self.notifier.show_toast("Hostname ve IP gerekli.")
            return
        self.inventory.add_item(item)
        self.audit_logger.log_action("inventory_add", "inventory", "ok", params={"ip": item.ip})
        self.refresh()

    def import_csv(self) -> None:
        path, _ = QFileDialog.getOpenFileName(self, "CSV Import", str(Path.home()), "CSV Files (*.csv)")
        if not path:
            return
        items: List[InventoryItem] = []
        with open(path, newline="", encoding="utf-8") as handle:
            reader = csv.DictReader(handle)
            for row in reader:
                items.append(
                    InventoryItem(
                        hostname=row.get("hostname", ""),
                        ip=row.get("ip", ""),
                        location=row.get("location", ""),
                        tags=row.get("tags", ""),
                    )
                )
        self.inventory.import_items(items)
        self.audit_logger.log_action("inventory_import", "inventory", "ok", params={"count": str(len(items))})
        self.refresh()
        self.notifier.show_toast("Envanter içe aktarıldı.")

    def export_csv(self) -> None:
        path, _ = QFileDialog.getSaveFileName(self, "CSV Export", str(Path.home()), "CSV Files (*.csv)")
        if not path:
            return
        items = self.inventory.list_items()
        with open(path, "w", newline="", encoding="utf-8") as handle:
            writer = csv.writer(handle)
            writer.writerow(["hostname", "ip", "location", "tags"])
            for item in items:
                writer.writerow([item.hostname, item.ip, item.location, item.tags])
        self.notifier.show_toast("Envanter dışa aktarıldı.")
