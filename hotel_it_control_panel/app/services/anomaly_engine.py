from dataclasses import dataclass
from typing import List


@dataclass
class AnomalyEvent:
    timestamp: str
    ip: str
    mac: str
    event_type: str
    severity: str
    recommendation: str


def detect_new_devices(known_ips: List[str], current_ips: List[str], timestamp: str) -> List[AnomalyEvent]:
    events = []
    for ip in current_ips:
        if ip not in known_ips:
            events.append(
                AnomalyEvent(
                    timestamp=timestamp,
                    ip=ip,
                    mac="",
                    event_type="NEW_DEVICE",
                    severity="MEDIUM",
                    recommendation="Envantere ekle ve cihazı doğrula.",
                )
            )
    return events
