from dataclasses import dataclass
from typing import List, Tuple


@dataclass
class DiffResult:
    added: List[str]
    removed: List[str]


def diff_lists(previous: List[str], current: List[str]) -> DiffResult:
    previous_set = set(previous)
    current_set = set(current)
    return DiffResult(
        added=sorted(list(current_set - previous_set)),
        removed=sorted(list(previous_set - current_set)),
    )
