from PySide6.QtGui import QPalette, QColor


class ThemeManager:
    def __init__(self, initial_theme: str = "dark") -> None:
        self.is_dark = initial_theme == "dark"

    def toggle_theme(self) -> QPalette:
        self.is_dark = not self.is_dark
        return self.get_palette()

    def get_palette(self) -> QPalette:
        palette = QPalette()
        if self.is_dark:
            palette.setColor(QPalette.Window, QColor(30, 30, 30))
            palette.setColor(QPalette.WindowText, QColor(220, 220, 220))
            palette.setColor(QPalette.Base, QColor(45, 45, 45))
            palette.setColor(QPalette.AlternateBase, QColor(55, 55, 55))
            palette.setColor(QPalette.Text, QColor(220, 220, 220))
            palette.setColor(QPalette.Button, QColor(45, 45, 45))
            palette.setColor(QPalette.ButtonText, QColor(220, 220, 220))
            palette.setColor(QPalette.Highlight, QColor(75, 110, 175))
            palette.setColor(QPalette.HighlightedText, QColor(255, 255, 255))
        else:
            palette.setColor(QPalette.Window, QColor(245, 245, 245))
            palette.setColor(QPalette.WindowText, QColor(30, 30, 30))
            palette.setColor(QPalette.Base, QColor(255, 255, 255))
            palette.setColor(QPalette.AlternateBase, QColor(240, 240, 240))
            palette.setColor(QPalette.Text, QColor(30, 30, 30))
            palette.setColor(QPalette.Button, QColor(225, 225, 225))
            palette.setColor(QPalette.ButtonText, QColor(30, 30, 30))
            palette.setColor(QPalette.Highlight, QColor(64, 110, 187))
            palette.setColor(QPalette.HighlightedText, QColor(255, 255, 255))
        return palette
