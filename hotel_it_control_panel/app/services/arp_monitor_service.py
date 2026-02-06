from dataclasses import dataclass
from typing import Dict, List


@dataclass
class ArpEntry:
    ip: str
    mac: str


class ArpMonitorService:
    def __init__(self) -> None:
        self.known: Dict[str, str] = {}

    def compare(self, current: List[ArpEntry]) -> List[ArpEntry]:
        changes = []
        for entry in current:
            if entry.ip in self.known and self.known[entry.ip] != entry.mac:
                changes.append(entry)
            self.known[entry.ip] = entry.mac
        return changes
