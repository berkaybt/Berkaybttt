import getpass

from PySide6.QtWidgets import QPushButton, QTableWidget, QTableWidgetItem, QVBoxLayout, QWidget

from app.security.audit_logger import AuditLogger
from app.services.approval_service import ApprovalService
from app.ui.notifications import NotificationManager


class ApprovalsPage(QWidget):
    def __init__(self, approvals: ApprovalService, audit_logger: AuditLogger, notifier: NotificationManager) -> None:
        super().__init__()
        self.approvals = approvals
        self.audit_logger = audit_logger
        self.notifier = notifier
        self.table = QTableWidget(0, 5)
        self.table.setHorizontalHeaderLabels(["ID", "Action", "Requested By", "Status", "Created At"])
        approve_button = QPushButton("Seçiliyi Onayla")
        approve_button.clicked.connect(self.approve_selected)

        layout = QVBoxLayout()
        layout.addWidget(self.table)
        layout.addWidget(approve_button)
        self.setLayout(layout)
        self.refresh()

    def refresh(self) -> None:
        requests = self.approvals.list_pending()
        self.table.setRowCount(len(requests))
        for row, item in enumerate(requests):
            self.table.setItem(row, 0, QTableWidgetItem(str(item.request_id)))
            self.table.setItem(row, 1, QTableWidgetItem(item.action))
            self.table.setItem(row, 2, QTableWidgetItem(item.requested_by))
            self.table.setItem(row, 3, QTableWidgetItem(item.status))
            self.table.setItem(row, 4, QTableWidgetItem(item.created_at))

    def approve_selected(self) -> None:
        row = self.table.currentRow()
        if row < 0:
            self.notifier.show_toast("Onaylamak için kayıt seçin.")
            return
        request_id = int(self.table.item(row, 0).text())
        approved_by = getpass.getuser()
        self.approvals.approve(request_id, approved_by)
        self.audit_logger.log_action(
            "approval",
            "approvals",
            "approved",
            requested_by=self.table.item(row, 2).text(),
            approved_by=approved_by,
        )
        self.refresh()
        self.notifier.show_toast("Onaylandı.")
