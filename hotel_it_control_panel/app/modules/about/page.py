from PySide6.QtWidgets import QLabel, QVBoxLayout, QWidget

from app.utils.build_info import get_build_info


class AboutPage(QWidget):
    def __init__(self, project_root) -> None:  # type: ignore[no-untyped-def]
        super().__init__()
        info = get_build_info(project_root)
        layout = QVBoxLayout()
        layout.addWidget(QLabel("Hotel IT Suite"))
        layout.addWidget(QLabel(f"Version: {info.version}"))
        layout.addWidget(QLabel(f"Commit: {info.commit}"))
        layout.addWidget(QLabel(f"Build Date (UTC): {info.build_date}"))
        self.setLayout(layout)
