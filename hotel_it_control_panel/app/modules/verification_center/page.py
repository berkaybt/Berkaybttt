from PySide6.QtWidgets import QTableWidget, QTableWidgetItem, QVBoxLayout, QWidget

from app.core.action_framework import BaselineStore


class VerificationCenterPage(QWidget):
    def __init__(self, baseline_store: BaselineStore) -> None:
        super().__init__()
        self.baseline_store = baseline_store
        self.table = QTableWidget(0, 4)
        self.table.setHorizontalHeaderLabels(["Action", "Status", "Summary", "Created At"])

        layout = QVBoxLayout()
        layout.addWidget(self.table)
        self.setLayout(layout)
        self.refresh()

    def refresh(self) -> None:
        rows = self.baseline_store.list_runs()
        self.table.setRowCount(len(rows))
        for row, (action_id, status, summary, created_at) in enumerate(rows):
            self.table.setItem(row, 0, QTableWidgetItem(action_id))
            self.table.setItem(row, 1, QTableWidgetItem(status))
            self.table.setItem(row, 2, QTableWidgetItem(summary))
            self.table.setItem(row, 3, QTableWidgetItem(created_at))
