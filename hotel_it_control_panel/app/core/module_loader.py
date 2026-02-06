import json
from dataclasses import dataclass
from pathlib import Path
from typing import List


@dataclass
class ModuleDefinition:
    key: str
    title: str
    order: int
    requires_admin: bool


def load_manifests(modules_dir: Path) -> List[ModuleDefinition]:
    modules = []
    for manifest_path in modules_dir.glob("*/manifest.json"):
        data = json.loads(manifest_path.read_text(encoding="utf-8"))
        modules.append(
            ModuleDefinition(
                key=data.get("key", manifest_path.parent.name),
                title=data.get("title", manifest_path.parent.name),
                order=int(data.get("order", 0)),
                requires_admin=bool(data.get("requires_admin", False)),
            )
        )
    return sorted(modules, key=lambda item: item.order)
