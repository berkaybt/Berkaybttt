import getpass

from PySide6.QtWidgets import QLabel, QPushButton, QVBoxLayout, QWidget

from app.security.audit_logger import AuditLogger
from app.services.approval_service import ApprovalService
from app.ui.notifications import NotificationManager


class MaintenancePage(QWidget):
    def __init__(self, approvals: ApprovalService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.approvals = approvals
        self.audit_logger = audit_logger
        self.notifier = notifier

        temp_button = QPushButton("Disk Temizleme (Onay)")
        temp_button.clicked.connect(lambda: self.request_approval("disk_cleanup"))
        startup_button = QPushButton("Startup Disable (Onay)")
        startup_button.clicked.connect(lambda: self.request_approval("startup_disable"))
        reset_button = QPushButton("Network Reset (Onay)")
        reset_button.clicked.connect(lambda: self.request_approval("network_reset"))

        layout = QVBoxLayout()
        layout.addWidget(QLabel("PC Bakım - Onaylı İşlemler"))
        layout.addWidget(temp_button)
        layout.addWidget(startup_button)
        layout.addWidget(reset_button)
        layout.addStretch(1)
        self.setLayout(layout)

    def request_approval(self, action: str) -> None:
        user = getpass.getuser()
        request_id = self.approvals.create_request(action, requested_by=user)
        self.audit_logger.log_action("approval_request", "pc_maintenance", "pending", requested_by=user, details=str(request_id))
        self.notifier.show_toast("Onay talebi oluşturuldu.")
