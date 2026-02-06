from PySide6.QtWidgets import QLabel, QPushButton, QTableWidget, QTableWidgetItem, QVBoxLayout, QWidget

from app.security.audit_logger import AuditLogger
from app.services.verification_service import VerificationService
from app.ui.notifications import NotificationManager


class MaintenanceVerificationPage(QWidget):
    def __init__(self, verification: VerificationService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.verification = verification
        self.audit_logger = audit_logger
        self.notifier = notifier

        baseline_button = QPushButton("Baseline Al")
        baseline_button.clicked.connect(self.capture_baseline)
        after_button = QPushButton("After Ölç")
        after_button.clicked.connect(self.capture_after)

        self.table = QTableWidget(0, 4)
        self.table.setHorizontalHeaderLabels(["Metric", "Before", "After", "Delta"])

        layout = QVBoxLayout()
        layout.addWidget(QLabel("Maintenance Verification"))
        layout.addWidget(baseline_button)
        layout.addWidget(after_button)
        layout.addWidget(self.table)
        self.setLayout(layout)

    def capture_baseline(self) -> None:
        self.verification.capture_baseline()
        self.notifier.show_toast("Baseline alındı.")

    def capture_after(self) -> None:
        result = self.verification.capture_after()
        self.table.setRowCount(len(result))
        for row, (metric, before, after, delta) in enumerate(result):
            self.table.setItem(row, 0, QTableWidgetItem(metric))
            self.table.setItem(row, 1, QTableWidgetItem(str(before)))
            self.table.setItem(row, 2, QTableWidgetItem(str(after)))
            self.table.setItem(row, 3, QTableWidgetItem(str(delta)))
        self.audit_logger.log_action("verification", "maintenance_verification", "ok")
        self.notifier.show_toast("After ölçümü tamamlandı.")
