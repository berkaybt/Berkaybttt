import platform
import socket
import sys
from datetime import datetime

import psutil
from PySide6.QtCore import Qt
from PySide6.QtWidgets import (
    QApplication,
    QComboBox,
    QFormLayout,
    QGroupBox,
    QHBoxLayout,
    QLabel,
    QMainWindow,
    QMessageBox,
    QPushButton,
    QTableWidget,
    QTableWidgetItem,
    QTabWidget,
    QTextEdit,
    QVBoxLayout,
    QWidget,
)


class InfoApp(QMainWindow):
    def __init__(self) -> None:
        super().__init__()
        self.setWindowTitle("Bilgi İşlem Kontrol Paneli")
        self.resize(980, 680)

        self.tabs = QTabWidget()
        self.setCentralWidget(self.tabs)

        self.overview_tab = QWidget()
        self.network_tab = QWidget()
        self.server_tab = QWidget()
        self.tuning_tab = QWidget()

        self.tabs.addTab(self.overview_tab, "Genel Bakış")
        self.tabs.addTab(self.network_tab, "Ağlar ve IP")
        self.tabs.addTab(self.server_tab, "Sunucular")
        self.tabs.addTab(self.tuning_tab, "Hızlandırma")

        self._build_overview()
        self._build_network()
        self._build_server()
        self._build_tuning()
        self.refresh_all()

    def _build_overview(self) -> None:
        layout = QVBoxLayout()

        self.system_info_box = QGroupBox("Sistem Özeti")
        self.system_info_layout = QFormLayout()
        self.system_info_box.setLayout(self.system_info_layout)

        self.hostname_label = QLabel("-")
        self.os_label = QLabel("-")
        self.cpu_label = QLabel("-")
        self.ram_label = QLabel("-")
        self.disk_label = QLabel("-")
        self.boot_label = QLabel("-")

        self.system_info_layout.addRow("Bilgisayar Adı:", self.hostname_label)
        self.system_info_layout.addRow("İşletim Sistemi:", self.os_label)
        self.system_info_layout.addRow("İşlemci:", self.cpu_label)
        self.system_info_layout.addRow("RAM Kullanımı:", self.ram_label)
        self.system_info_layout.addRow("Disk Kullanımı:", self.disk_label)
        self.system_info_layout.addRow("Açılış Zamanı:", self.boot_label)

        refresh_button = QPushButton("Bilgileri Yenile")
        refresh_button.clicked.connect(self.refresh_all)

        layout.addWidget(self.system_info_box)
        layout.addWidget(refresh_button, alignment=Qt.AlignRight)
        layout.addStretch()
        self.overview_tab.setLayout(layout)

    def _build_network(self) -> None:
        layout = QVBoxLayout()

        self.network_summary = QTextEdit()
        self.network_summary.setReadOnly(True)

        self.interface_table = QTableWidget(0, 4)
        self.interface_table.setHorizontalHeaderLabels(
            ["Arayüz", "IP Adresi", "Netmask", "Aile"]
        )
        self.interface_table.horizontalHeader().setStretchLastSection(True)

        refresh_button = QPushButton("Ağ Bilgilerini Yenile")
        refresh_button.clicked.connect(self.refresh_network)

        layout.addWidget(QLabel("Ağ Özeti"))
        layout.addWidget(self.network_summary)
        layout.addWidget(QLabel("Arayüz Detayları"))
        layout.addWidget(self.interface_table)
        layout.addWidget(refresh_button, alignment=Qt.AlignRight)
        self.network_tab.setLayout(layout)

    def _build_server(self) -> None:
        layout = QVBoxLayout()

        self.server_table = QTableWidget(0, 4)
        self.server_table.setHorizontalHeaderLabels(
            ["Sunucu Adı", "IP", "Durum", "Not"]
        )
        self.server_table.horizontalHeader().setStretchLastSection(True)

        controls = QHBoxLayout()
        add_button = QPushButton("Sunucu Ekle")
        add_button.clicked.connect(self.add_server)
        remove_button = QPushButton("Seçiliyi Sil")
        remove_button.clicked.connect(self.remove_server)
        controls.addWidget(add_button)
        controls.addWidget(remove_button)
        controls.addStretch()

        layout.addWidget(QLabel("Sunucu Envanteri"))
        layout.addWidget(self.server_table)
        layout.addLayout(controls)

        hint = QLabel(
            "Örnek sunucu kayıtları ekleyebilir, IP ve durum bilgilerini güncelleyebilirsiniz."
        )
        hint.setWordWrap(True)
        layout.addWidget(hint)
        self.server_tab.setLayout(layout)

    def _build_tuning(self) -> None:
        layout = QVBoxLayout()

        self.tuning_summary = QTextEdit()
        self.tuning_summary.setReadOnly(True)

        quick_box = QGroupBox("Hızlı Ayarlar")
        quick_layout = QFormLayout()
        quick_box.setLayout(quick_layout)

        self.power_profile = QComboBox()
        self.power_profile.addItems(["Dengeli", "Yüksek Performans", "Tasarruf"])
        apply_power = QPushButton("Güncelle")
        apply_power.clicked.connect(self.show_power_hint)

        self.cleanup_button = QPushButton("Geçici Dosyaları Temizle")
        self.cleanup_button.clicked.connect(self.show_cleanup_hint)

        quick_layout.addRow("Güç Profili:", self._inline_controls(self.power_profile, apply_power))
        quick_layout.addRow("Bakım:", self.cleanup_button)

        layout.addWidget(QLabel("Sistem Sağlığı"))
        layout.addWidget(self.tuning_summary)
        layout.addWidget(quick_box)
        layout.addStretch()
        self.tuning_tab.setLayout(layout)

    def _inline_controls(self, *widgets: QWidget) -> QWidget:
        container = QWidget()
        layout = QHBoxLayout()
        layout.setContentsMargins(0, 0, 0, 0)
        for widget in widgets:
            layout.addWidget(widget)
        layout.addStretch()
        container.setLayout(layout)
        return container

    def refresh_all(self) -> None:
        self.refresh_overview()
        self.refresh_network()
        self.refresh_tuning()

    def refresh_overview(self) -> None:
        hostname = socket.gethostname()
        os_info = f"{platform.system()} {platform.release()}"
        cpu_info = f"{platform.processor()} ({psutil.cpu_count(logical=True)} çekirdek)"
        ram = psutil.virtual_memory()
        disk = psutil.disk_usage("/")
        boot = datetime.fromtimestamp(psutil.boot_time()).strftime("%d.%m.%Y %H:%M")

        self.hostname_label.setText(hostname)
        self.os_label.setText(os_info)
        self.cpu_label.setText(cpu_info)
        self.ram_label.setText(f"{ram.percent}% kullanım, {ram.used / 1024**3:.1f} / {ram.total / 1024**3:.1f} GB")
        self.disk_label.setText(f"{disk.percent}% kullanım, {disk.used / 1024**3:.1f} / {disk.total / 1024**3:.1f} GB")
        self.boot_label.setText(boot)

    def refresh_network(self) -> None:
        addresses = psutil.net_if_addrs()
        lines = ["Ağ Arayüzleri ve IP Adresleri:"]
        self.interface_table.setRowCount(0)

        for iface, addrs in addresses.items():
            for addr in addrs:
                if addr.family in (socket.AF_INET, socket.AF_INET6):
                    lines.append(f"- {iface}: {addr.address}")
                row = self.interface_table.rowCount()
                self.interface_table.insertRow(row)
                self.interface_table.setItem(row, 0, QTableWidgetItem(iface))
                self.interface_table.setItem(row, 1, QTableWidgetItem(addr.address))
                self.interface_table.setItem(row, 2, QTableWidgetItem(str(addr.netmask)))
                self.interface_table.setItem(row, 3, QTableWidgetItem(str(addr.family)))

        self.network_summary.setText("\n".join(lines))

    def refresh_tuning(self) -> None:
        cpu_percent = psutil.cpu_percent(interval=0.2)
        mem = psutil.virtual_memory()
        swap = psutil.swap_memory()

        summary = (
            f"CPU kullanım oranı: {cpu_percent}%\n"
            f"RAM kullanım oranı: {mem.percent}%\n"
            f"Swap kullanım oranı: {swap.percent}%\n"
            "\nÖneriler:\n"
            "• Kritik uygulamalar için yüksek performans modunu tercih edin.\n"
            "• Haftalık bakım ile gereksiz dosyaları temizleyin.\n"
            "• Ağ kartı sürücülerinizi güncel tutun."
        )
        self.tuning_summary.setText(summary)

    def add_server(self) -> None:
        row = self.server_table.rowCount()
        self.server_table.insertRow(row)
        defaults = ["Yeni Sunucu", "0.0.0.0", "Beklemede", "Not ekleyin"]
        for column, value in enumerate(defaults):
            self.server_table.setItem(row, column, QTableWidgetItem(value))

    def remove_server(self) -> None:
        row = self.server_table.currentRow()
        if row >= 0:
            self.server_table.removeRow(row)

    def show_power_hint(self) -> None:
        profile = self.power_profile.currentText()
        QMessageBox.information(
            self,
            "Güç Profili",
            f"Seçilen güç profili: {profile}.\nGerçek sistem ayarları için yönetici ayrıcalığı gerekir.",
        )

    def show_cleanup_hint(self) -> None:
        QMessageBox.information(
            self,
            "Bakım",
            "Geçici dosyaları temizlemek için sistem bakım aracı çalıştırabilirsiniz.",
        )


def main() -> None:
    app = QApplication(sys.argv)
    window = InfoApp()
    window.show()
    sys.exit(app.exec())


if __name__ == "__main__":
    main()
