from dataclasses import dataclass


@dataclass
class AppState:
    active_module: str = "dashboard"
    is_admin: bool = False
    role: str = "USER"
