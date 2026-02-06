from PySide6.QtWidgets import QPushButton, QVBoxLayout, QWidget


class Sidebar(QWidget):
    def __init__(self) -> None:
        super().__init__()
        self.buttons: dict[str, QPushButton] = {}
        self.layout = QVBoxLayout()
        self.layout.addStretch(1)
        self.setLayout(self.layout)

    def add_button(self, key: str, title: str) -> QPushButton:
        button = QPushButton(title)
        self.buttons[key] = button
        self.layout.insertWidget(self.layout.count() - 1, button)
        return button
