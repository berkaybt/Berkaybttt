from PySide6.QtWidgets import QDialog, QLineEdit, QListWidget, QVBoxLayout


class CommandPalette(QDialog):
    def __init__(self, commands: list[str]) -> None:
        super().__init__()
        self.setWindowTitle("Komut Paleti")
        self.search = QLineEdit()
        self.list_widget = QListWidget()
        self.list_widget.addItems(commands)

        layout = QVBoxLayout()
        layout.addWidget(self.search)
        layout.addWidget(self.list_widget)
        self.setLayout(layout)

        self.search.textChanged.connect(self.filter_list)

    def filter_list(self, text: str) -> None:
        for row in range(self.list_widget.count()):
            item = self.list_widget.item(row)
            item.setHidden(text.lower() not in item.text().lower())
