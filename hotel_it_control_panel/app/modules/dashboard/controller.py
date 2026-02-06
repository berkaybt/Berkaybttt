from app.security.audit_logger import AuditLogger


class DashboardController:
    def __init__(self, audit_logger: AuditLogger) -> None:
        self.audit_logger = audit_logger

    def log_quick_action(self, action: str) -> None:
        self.audit_logger.log_action(action, module="dashboard", result="requested")
