from dataclasses import dataclass
from typing import Callable, List


@dataclass
class Command:
    label: str
    action: Callable[[], None]


class CommandRegistry:
    def __init__(self) -> None:
        self.commands: List[Command] = []

    def register(self, label: str, action: Callable[[], None]) -> None:
        self.commands.append(Command(label=label, action=action))
