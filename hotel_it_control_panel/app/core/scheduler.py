from dataclasses import dataclass
from datetime import datetime, timedelta
from typing import Callable, Dict


@dataclass
class ScheduledJob:
    name: str
    interval_minutes: int
    action: Callable[[], None]
    next_run: datetime


class Scheduler:
    def __init__(self) -> None:
        self.jobs: Dict[str, ScheduledJob] = {}

    def add_job(self, name: str, interval_minutes: int, action: Callable[[], None]) -> None:
        self.jobs[name] = ScheduledJob(
            name=name,
            interval_minutes=interval_minutes,
            action=action,
            next_run=datetime.now() + timedelta(minutes=interval_minutes),
        )

    def tick(self) -> None:
        now = datetime.now()
        for job in self.jobs.values():
            if now >= job.next_run:
                job.action()
                job.next_run = now + timedelta(minutes=job.interval_minutes)
