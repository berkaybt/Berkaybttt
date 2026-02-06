from PySide6.QtWidgets import QLabel, QLineEdit, QPushButton, QToolBar

from app.security.permissions import Permissions


class TopBar(QToolBar):
    def __init__(self, permissions: Permissions) -> None:
        super().__init__("TopBar")
        self.setMovable(False)
        self.search = QLineEdit()
        self.search.setPlaceholderText("Ara (Ctrl+K)")
        self.search.setFixedWidth(250)
        self.theme_button = QPushButton("Tema")
        role_text = "Rol: Admin" if permissions.is_admin() else "Rol: User"
        self.role_label = QLabel(role_text)

        self.addWidget(self.search)
        self.addWidget(self.theme_button)
        self.addWidget(self.role_label)
