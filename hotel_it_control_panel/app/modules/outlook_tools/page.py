from PySide6.QtWidgets import QLabel, QVBoxLayout, QWidget


class OutlookToolsPage(QWidget):
    def __init__(self) -> None:
        super().__init__()
        layout = QVBoxLayout()
        layout.addWidget(QLabel("Outlook Araçları - Yakında"))
        self.setLayout(layout)
