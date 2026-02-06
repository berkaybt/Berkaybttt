from PySide6.QtWidgets import QLabel, QVBoxLayout, QWidget


class ServerConnectionsPage(QWidget):
    def __init__(self) -> None:
        super().__init__()
        layout = QVBoxLayout()
        layout.addWidget(QLabel("Sunucu Bağlantıları - Yakında"))
        self.setLayout(layout)
