from PySide6.QtGui import QIcon
from PySide6.QtWidgets import QMenu, QSystemTrayIcon


class TrayIcon(QSystemTrayIcon):
    def __init__(self, parent=None) -> None:  # type: ignore[no-untyped-def]
        super().__init__(parent)
        self.setToolTip("Hotel IT Suite")
        menu = QMenu()
        menu.addAction("Ağ Taraması")
        menu.addAction("RDP")
        menu.addAction("Temizlik")
        menu.addAction("Mesajlar")
        self.setContextMenu(menu)
        self.setIcon(QIcon())
