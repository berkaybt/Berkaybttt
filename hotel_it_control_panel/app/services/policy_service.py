import hashlib
from pathlib import Path
from typing import Optional


class PolicyService:
    def __init__(self, local_policy_path: Path, central_policy_path: Optional[Path]) -> None:
        self.local_policy_path = local_policy_path
        self.central_policy_path = central_policy_path

    def select_policy_path(self) -> Path:
        if self.central_policy_path and self.central_policy_path.exists():
            return self.central_policy_path
        return self.local_policy_path

    def checksum(self, path: Path) -> str:
        data = path.read_bytes()
        return hashlib.sha256(data).hexdigest()
