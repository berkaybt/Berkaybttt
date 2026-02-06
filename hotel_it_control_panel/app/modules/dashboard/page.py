from PySide6.QtCore import Qt, Signal
from PySide6.QtWidgets import (
    QGridLayout,
    QLabel,
    QListWidget,
    QPushButton,
    QVBoxLayout,
    QWidget,
)

from app.security.audit_logger import AuditLogger
from app.services.system_info_service import system_info_as_dict


class DashboardPage(QWidget):
    quick_action_requested = Signal(str)

    def __init__(self, audit_logger: AuditLogger) -> None:
        super().__init__()
        self.audit_logger = audit_logger
        self.info_labels = {}
        self.actions_list = QListWidget()

        info_layout = QGridLayout()
        info_layout.setAlignment(Qt.AlignTop)
        info = system_info_as_dict()
        for row, (key, value) in enumerate(info.items()):
            title = QLabel(key)
            value_label = QLabel(value)
            value_label.setTextInteractionFlags(Qt.TextSelectableByMouse)
            info_layout.addWidget(title, row, 0)
            info_layout.addWidget(value_label, row, 1)
            self.info_labels[key] = value_label

        quick_actions = QWidget()
        quick_layout = QVBoxLayout()
        temp_button = QPushButton("Temp Temizle")
        scan_button = QPushButton("Ağ Taraması")
        rdp_button = QPushButton("RDP Aç")
        temp_button.clicked.connect(lambda: self.quick_action_requested.emit("temp_cleanup"))
        scan_button.clicked.connect(lambda: self.quick_action_requested.emit("network_scan"))
        rdp_button.clicked.connect(lambda: self.quick_action_requested.emit("rdp"))
        quick_layout.addWidget(temp_button)
        quick_layout.addWidget(scan_button)
        quick_layout.addWidget(rdp_button)
        quick_actions.setLayout(quick_layout)

        layout = QVBoxLayout()
        layout.addLayout(info_layout)
        layout.addWidget(QLabel("Son İşlemler"))
        layout.addWidget(self.actions_list)
        layout.addWidget(QLabel("Hızlı İşlemler"))
        layout.addWidget(quick_actions)
        self.setLayout(layout)
        self.refresh_actions()

    def refresh_info(self) -> None:
        info = system_info_as_dict()
        for key, value in info.items():
            if key in self.info_labels:
                self.info_labels[key].setText(value)

    def refresh_actions(self) -> None:
        self.actions_list.clear()
        for row in self.audit_logger.get_recent_actions():
            text = f"{row['timestamp']} | {row['action']} | {row['result']}"
            self.actions_list.addItem(text)
