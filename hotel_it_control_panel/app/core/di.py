from typing import Any, Dict


class ServiceContainer:
    def __init__(self) -> None:
        self._services: Dict[str, Any] = {}

    def register(self, name: str, service: Any) -> None:
        self._services[name] = service

    def resolve(self, name: str) -> Any:
        return self._services[name]
