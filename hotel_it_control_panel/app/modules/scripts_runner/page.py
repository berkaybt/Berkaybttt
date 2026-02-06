from PySide6.QtWidgets import QLabel, QVBoxLayout, QWidget


class ScriptsRunnerPage(QWidget):
    def __init__(self) -> None:
        super().__init__()
        layout = QVBoxLayout()
        layout.addWidget(QLabel("Script Runner - Policy allowlist gerekli"))
        self.setLayout(layout)
