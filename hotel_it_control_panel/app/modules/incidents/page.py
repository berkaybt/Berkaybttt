from PySide6.QtWidgets import QHBoxLayout, QLabel, QLineEdit, QPushButton, QTableWidget, QTableWidgetItem, QVBoxLayout, QWidget

from app.security.audit_logger import AuditLogger
from app.services.incident_service import IncidentService
from app.ui.notifications import NotificationManager


class IncidentsPage(QWidget):
    def __init__(self, incidents: IncidentService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.incidents = incidents
        self.audit_logger = audit_logger
        self.notifier = notifier

        self.title_input = QLineEdit()
        self.device_input = QLineEdit()
        self.priority_input = QLineEdit("Medium")
        add_button = QPushButton("Olay Ekle")
        add_button.clicked.connect(self.add_incident)

        form_layout = QHBoxLayout()
        form_layout.addWidget(self.title_input)
        form_layout.addWidget(self.device_input)
        form_layout.addWidget(self.priority_input)
        form_layout.addWidget(add_button)

        self.table = QTableWidget(0, 5)
        self.table.setHorizontalHeaderLabels(["ID", "Başlık", "Cihaz", "Öncelik", "Durum"])

        layout = QVBoxLayout()
        layout.addWidget(QLabel("Incident Yönetimi"))
        layout.addLayout(form_layout)
        layout.addWidget(self.table)
        self.setLayout(layout)
        self.refresh()

    def refresh(self) -> None:
        rows = self.incidents.list_incidents()
        self.table.setRowCount(len(rows))
        for row, item in enumerate(rows):
            self.table.setItem(row, 0, QTableWidgetItem(str(item.incident_id)))
            self.table.setItem(row, 1, QTableWidgetItem(item.title))
            self.table.setItem(row, 2, QTableWidgetItem(item.device))
            self.table.setItem(row, 3, QTableWidgetItem(item.priority))
            self.table.setItem(row, 4, QTableWidgetItem(item.status))

    def add_incident(self) -> None:
        title = self.title_input.text().strip()
        device = self.device_input.text().strip()
        priority = self.priority_input.text().strip() or "Medium"
        if not title:
            self.notifier.show_toast("Başlık gerekli.")
            return
        self.incidents.create_incident(title, device, priority)
        self.audit_logger.log_action("incident_create", "incidents", "ok")
        self.refresh()
        self.notifier.show_toast("Olay oluşturuldu.")
