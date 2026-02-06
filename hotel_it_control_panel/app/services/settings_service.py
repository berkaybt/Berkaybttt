import json
import logging
from pathlib import Path
from typing import Any, Dict


DEFAULT_SETTINGS: Dict[str, Any] = {}
DEFAULT_ADMIN_POLICY: Dict[str, Any] = {}


class SettingsService:
    def __init__(self, settings_path: Path, policy_path: Path) -> None:
        self.settings_path = settings_path
        self.policy_path = policy_path
        self.logger = logging.getLogger(self.__class__.__name__)
        self.settings = self._load(settings_path, self._load_defaults("default_settings.json"))
        self.admin_policy = self._load(policy_path, self._load_defaults("default_admin_policy.json"))
        self.policy_source_path = policy_path

    def _load_defaults(self, filename: str) -> Dict[str, Any]:
        defaults_path = self.settings_path.parent / filename
        if defaults_path.exists():
            return json.loads(defaults_path.read_text(encoding="utf-8"))
        self.logger.warning("Defaults file missing: %s", defaults_path)
        return {}

    def get_central_policy_path(self) -> str:
        return self.settings.get("central_policy_path", "")

    def _load(self, path: Path, default: Dict[str, Any]) -> Dict[str, Any]:
        if not path.exists():
            path.parent.mkdir(parents=True, exist_ok=True)
            self._save(path, default)
            return default.copy()
        try:
            return json.loads(path.read_text(encoding="utf-8"))
        except json.JSONDecodeError:
            self.logger.exception("Failed to parse %s, using defaults", path.name)
            return default.copy()

    def _save(self, path: Path, data: Dict[str, Any]) -> None:
        path.write_text(json.dumps(data, indent=2), encoding="utf-8")

    def save_settings(self) -> None:
        self._save(self.settings_path, self.settings)

    def save_policy(self) -> None:
        self._save(self.policy_path, self.admin_policy)

    def add_recent_ip(self, ip_range: str) -> None:
        recent = self.settings.setdefault("recent_ips", [])
        if ip_range in recent:
            recent.remove(ip_range)
        recent.insert(0, ip_range)
        self.settings["recent_ips"] = recent[:10]
        self.save_settings()

    def get_tool_path(self, tool_key: str) -> str:
        return self.settings.get("tools", {}).get(tool_key, "")

    def set_tool_path(self, tool_key: str, path: str) -> None:
        self.settings.setdefault("tools", {})[tool_key] = path
        self.save_settings()
