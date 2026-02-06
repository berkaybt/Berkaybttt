from PySide6.QtCore import QTimer, Qt
from PySide6.QtWidgets import QFrame, QLabel, QVBoxLayout, QWidget


class Toast(QFrame):
    def __init__(self, message: str, parent: QWidget) -> None:
        super().__init__(parent)
        self.setFrameShape(QFrame.StyledPanel)
        self.setStyleSheet("background-color: #2d2d2d; color: white; padding: 10px; border-radius: 6px;")
        layout = QVBoxLayout()
        layout.addWidget(QLabel(message))
        self.setLayout(layout)
        self.setWindowFlags(Qt.ToolTip)


class NotificationManager:
    def __init__(self, parent: QWidget) -> None:
        self.parent = parent

    def show_toast(self, message: str, duration_ms: int = 2500) -> None:
        toast = Toast(message, self.parent)
        toast.adjustSize()
        parent_rect = self.parent.geometry()
        toast.move(parent_rect.right() - toast.width() - 20, parent_rect.bottom() - toast.height() - 20)
        toast.show()
        QTimer.singleShot(duration_ms, toast.close)
