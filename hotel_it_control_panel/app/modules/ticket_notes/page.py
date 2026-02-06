from PySide6.QtWidgets import QLabel, QVBoxLayout, QWidget


class TicketNotesPage(QWidget):
    def __init__(self) -> None:
        super().__init__()
        layout = QVBoxLayout()
        layout.addWidget(QLabel("Ticket/Notlar - Yakında"))
        self.setLayout(layout)
