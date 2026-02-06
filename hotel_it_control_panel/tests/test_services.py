import tempfile
import unittest
from pathlib import Path

from app.services.approval_service import ApprovalService
from app.services.diff_service import diff_lists
from app.services.inventory_service import InventoryItem, InventoryService
from app.services.maintenance_service import calculate_temp_size
from app.services.network_scan_service import parse_ip_targets


class TestNetworkScanService(unittest.TestCase):
    def test_parse_ip_targets_single(self) -> None:
        self.assertEqual(list(parse_ip_targets("192.168.1.10")), ["192.168.1.10"])

    def test_parse_ip_targets_range(self) -> None:
        self.assertEqual(
            list(parse_ip_targets("192.168.1.10-12")),
            ["192.168.1.10", "192.168.1.11", "192.168.1.12"],
        )

    def test_parse_ip_targets_cidr(self) -> None:
        result = list(parse_ip_targets("192.168.1.0/30"))
        self.assertEqual(result, ["192.168.1.1", "192.168.1.2"])


class TestMaintenanceService(unittest.TestCase):
    def test_calculate_temp_size(self) -> None:
        size, paths = calculate_temp_size()
        self.assertGreaterEqual(size, 0)
        self.assertIsInstance(paths, list)


class TestInventoryService(unittest.TestCase):
    def test_add_and_list_inventory(self) -> None:
        with tempfile.TemporaryDirectory() as tmp_dir:
            db_path = Path(tmp_dir) / "app.db"
            service = InventoryService(db_path)
            service.add_item(InventoryItem(hostname="PC1", ip="192.168.1.10", location="Lobby", tags="test"))
            items = service.list_items()
            self.assertEqual(len(items), 1)
            self.assertEqual(items[0].hostname, "PC1")


class TestDiffService(unittest.TestCase):
    def test_diff_lists(self) -> None:
        result = diff_lists(["1", "2"], ["2", "3"])
        self.assertEqual(result.added, ["3"])
        self.assertEqual(result.removed, ["1"])


class TestApprovalService(unittest.TestCase):
    def test_approval_flow(self) -> None:
        with tempfile.TemporaryDirectory() as tmp_dir:
            db_path = Path(tmp_dir) / "app.db"
            service = ApprovalService(db_path)
            request_id = service.create_request("disk_cleanup", "userA")
            pending = service.list_pending()
            self.assertEqual(len(pending), 1)
            self.assertEqual(pending[0].request_id, request_id)


if __name__ == "__main__":
    unittest.main()
