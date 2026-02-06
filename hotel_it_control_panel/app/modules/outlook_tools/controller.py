from app.security.audit_logger import AuditLogger


class OutlookToolsController:
    def __init__(self, audit_logger: AuditLogger) -> None:
        self.audit_logger = audit_logger

    def log_open(self) -> None:
        self.audit_logger.log_action("open", "outlook_tools", "ok")
