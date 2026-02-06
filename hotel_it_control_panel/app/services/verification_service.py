import psutil

from app.services.baseline_store import BaselineStore
from app.services.maintenance_service import calculate_temp_size


class VerificationService:
    def __init__(self, baseline_store: BaselineStore) -> None:
        self.baseline_store = baseline_store

    def capture_baseline(self) -> None:
        temp_size, _ = calculate_temp_size()
        disk = psutil.disk_usage("/")
        data = {
            "temp_size": float(temp_size),
            "disk_free": float(disk.free),
            "cpu_idle": float(100 - psutil.cpu_percent(interval=0.1)),
            "ram_free": float(psutil.virtual_memory().available),
        }
        self.baseline_store.create(data)

    def capture_after(self) -> list[tuple]:
        before = self.baseline_store.latest()
        temp_size, _ = calculate_temp_size()
        disk = psutil.disk_usage("/")
        after = {
            "temp_size": float(temp_size),
            "disk_free": float(disk.free),
            "cpu_idle": float(100 - psutil.cpu_percent(interval=0.1)),
            "ram_free": float(psutil.virtual_memory().available),
        }
        result = []
        for key in after:
            before_val = before.get(key, 0)
            after_val = after[key]
            result.append((key, before_val, after_val, after_val - before_val))
        return result
