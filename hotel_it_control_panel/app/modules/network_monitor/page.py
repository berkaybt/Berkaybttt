import csv
import json
from pathlib import Path
from typing import List

from PySide6.QtWidgets import (
    QAbstractItemView,
    QFileDialog,
    QHBoxLayout,
    QLabel,
    QLineEdit,
    QPushButton,
    QTableWidget,
    QTableWidgetItem,
    QVBoxLayout,
    QWidget,
)

from app.core.action_framework import ActionCheckResult, ActionExecResult, ActionRunner, ActionSpec, VerificationResult, BaselineStore
from app.core.task_runner import Task, TaskRunnerPool
from app.security.audit_logger import AuditLogger
from app.services.diff_service import diff_lists
from app.services.inventory_service import InventoryItem, InventoryService
from app.services.network_scan_service import ScanResult, scan_targets
from app.services.settings_service import SettingsService
from app.ui.notifications import NotificationManager


class NetworkMonitorPage(QWidget):
    def __init__(
        self,
        settings: SettingsService,
        audit_logger: AuditLogger,
        notifier: NotificationManager,
        inventory: InventoryService,
    ) -> None:
        super().__init__()
        self.settings = settings
        self.audit_logger = audit_logger
        self.notifier = notifier
        self.inventory = inventory
        self.runner = TaskRunnerPool()
        self.results: List[ScanResult] = []
        self.action_runner = ActionRunner(
            baseline_store=BaselineStore(Path(__file__).resolve().parents[2] / "data" / "app.db"),
            proof_log_path=Path(__file__).resolve().parents[2] / "logs" / "proof.log",
        )

        self.range_input = QLineEdit(settings.settings.get("default_ip_range", ""))
        self.range_input.setPlaceholderText("192.168.1.1-254 veya 192.168.1.0/24")
        self.group_input = QLineEdit()
        self.group_input.setPlaceholderText("Grup/Konum (opsiyonel)")

        self.port_input = QLineEdit("3389,445,80,443")

        scan_button = QPushButton("Ping Tara")
        scan_button.clicked.connect(self.start_scan)
        export_csv = QPushButton("CSV Dışa Aktar")
        export_csv.clicked.connect(self.export_csv)
        export_json = QPushButton("JSON Dışa Aktar")
        export_json.clicked.connect(self.export_json)
        save_inventory = QPushButton("Cihazları Kaydet")
        save_inventory.clicked.connect(self.save_inventory)

        self.status_label = QLabel("")
        self.table = QTableWidget(0, 5)
        self.table.setHorizontalHeaderLabels(["IP", "Host", "Reachable", "Ping ms", "Açık Portlar"])
        self.table.setEditTriggers(QAbstractItemView.NoEditTriggers)

        input_layout = QHBoxLayout()
        input_layout.addWidget(self.range_input)
        input_layout.addWidget(self.port_input)
        input_layout.addWidget(scan_button)

        actions_layout = QHBoxLayout()
        actions_layout.addWidget(export_csv)
        actions_layout.addWidget(export_json)
        actions_layout.addWidget(save_inventory)

        layout = QVBoxLayout()
        layout.addLayout(input_layout)
        layout.addWidget(self.group_input)
        layout.addWidget(self.status_label)
        layout.addWidget(self.table)
        layout.addLayout(actions_layout)
        self.setLayout(layout)

    def start_scan(self) -> None:
        target = self.range_input.text().strip()
        if not target:
            self.notifier.show_toast("IP aralığı boş olamaz.")
            return
        ports = [int(p.strip()) for p in self.port_input.text().split(",") if p.strip().isdigit()]
        self.status_label.setText("Tarama başladı...")
        self.table.setRowCount(0)
        self.results = []
        self.settings.add_recent_ip(target)
        self.audit_logger.log_action("network_scan", "network_monitor", "started", target)

        task = Task(name="network_scan", fn=self._run_scan_action, args=(target, ports), kwargs={})
        self.runner.start(task, on_result=self.on_scan_result, on_error=self.on_scan_error, on_finished=self.on_scan_finished)

    def on_scan_result(self, payload: dict) -> None:
        results = payload.get("results", [])
        self.results = results
        self.table.setRowCount(len(results))
        for row, result in enumerate(results):
            self.table.setItem(row, 0, QTableWidgetItem(result.ip))
            self.table.setItem(row, 1, QTableWidgetItem(result.hostname))
            self.table.setItem(row, 2, QTableWidgetItem("Yes" if result.reachable else "No"))
            self.table.setItem(row, 3, QTableWidgetItem(f"{result.latency_ms:.0f}"))
            ports = ",".join([str(port) for port in result.open_ports])
            self.table.setItem(row, 4, QTableWidgetItem(ports))
        verification = payload.get("verification")
        summary = verification.summary if verification else f"{len(results)} cihaz"
        self.audit_logger.log_action("network_scan", "network_monitor", "completed", summary)
        self._report_diff(results)

    def _run_scan_action(self, target: str, ports: List[int]) -> dict:
        spec = ActionSpec(
            action_id="network_scan",
            name="Network Scan",
            required_role="USER",
            risk_level="LOW",
            precheck=lambda: ActionCheckResult(ok=True, snapshot=None, message=""),
            execute=lambda: ActionExecResult(ok=True, payload=scan_targets(target, ports), message=""),
            postcheck=lambda: ActionCheckResult(ok=True, snapshot=None, message=""),
            verify=lambda pre, exec_result, post: VerificationResult(
                status="SUCCESS" if exec_result.ok else "FAIL",
                proof={"count": len(exec_result.payload)},
                summary=f"{len(exec_result.payload)} cihaz tarandı",
            ),
        )
        verification, exec_result = self.action_runner.run(spec)
        return {"results": exec_result.payload or [], "verification": verification}

    def _report_diff(self, results: List[ScanResult]) -> None:
        last_scan_path = Path(__file__).resolve().parents[2] / "data" / "last_scan.json"
        previous = []
        if last_scan_path.exists():
            previous = json.loads(last_scan_path.read_text(encoding="utf-8"))
        current = [result.ip for result in results]
        diff = diff_lists(previous, current)
        if diff.added or diff.removed:
            self.notifier.show_toast(
                f"Yeni: {len(diff.added)} | Kaybolan: {len(diff.removed)}"
            )
        last_scan_path.parent.mkdir(parents=True, exist_ok=True)
        last_scan_path.write_text(json.dumps(current, indent=2), encoding="utf-8")

    def on_scan_error(self, message: str) -> None:
        self.audit_logger.log_action("network_scan", "network_monitor", "failed", message)
        self.notifier.show_toast(f"Tarama başarısız: {message}")

    def on_scan_finished(self) -> None:
        self.status_label.setText("Tarama tamamlandı.")

    def export_csv(self) -> None:
        if not self.results:
            self.notifier.show_toast("Aktarılacak veri yok.")
            return
        file_path, _ = QFileDialog.getSaveFileName(self, "CSV Kaydet", str(Path.home()), "CSV Files (*.csv)")
        if not file_path:
            return
        with open(file_path, "w", newline="", encoding="utf-8") as handle:
            writer = csv.writer(handle)
            writer.writerow(["IP", "Host", "Reachable", "Ping ms", "Açık Portlar"])
            for result in self.results:
                writer.writerow([
                    result.ip,
                    result.hostname,
                    result.reachable,
                    f"{result.latency_ms:.0f}",
                    ",".join([str(port) for port in result.open_ports]),
                ])
        self.notifier.show_toast("CSV kaydedildi.")

    def export_json(self) -> None:
        if not self.results:
            self.notifier.show_toast("Aktarılacak veri yok.")
            return
        file_path, _ = QFileDialog.getSaveFileName(self, "JSON Kaydet", str(Path.home()), "JSON Files (*.json)")
        if not file_path:
            return
        payload = [
            {
                "ip": result.ip,
                "hostname": result.hostname,
                "reachable": result.reachable,
                "latency_ms": result.latency_ms,
                "open_ports": result.open_ports,
            }
            for result in self.results
        ]
        Path(file_path).write_text(json.dumps(payload, indent=2), encoding="utf-8")
        self.notifier.show_toast("JSON kaydedildi.")

    def save_inventory(self) -> None:
        if not self.results:
            self.notifier.show_toast("Kaydedilecek veri yok.")
            return
        group = self.group_input.text().strip() or "Genel"
        for result in self.results:
            self.inventory.add_item(
                InventoryItem(
                    hostname=result.hostname or result.ip,
                    ip=result.ip,
                    location=group,
                    tags="reachable" if result.reachable else "offline",
                )
            )
        self.notifier.show_toast("Envanter kaydedildi.")
