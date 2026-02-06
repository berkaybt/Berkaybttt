from dataclasses import dataclass
from datetime import datetime
from typing import List

from app.services.anomaly_engine import AnomalyEvent


@dataclass
class SecuritySnapshot:
    timestamp: str
    events: List[AnomalyEvent]


def build_snapshot(events: List[AnomalyEvent]) -> SecuritySnapshot:
    return SecuritySnapshot(timestamp=datetime.utcnow().isoformat(), events=events)
