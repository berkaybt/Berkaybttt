from app.security.audit_logger import AuditLogger


class InventoryController:
    def __init__(self, audit_logger: AuditLogger) -> None:
        self.audit_logger = audit_logger

    def log_open(self) -> None:
        self.audit_logger.log_action("open", "inventory", "ok")
