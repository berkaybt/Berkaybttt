import ctypes


class Permissions:
    def is_admin(self) -> bool:
        try:
            return bool(ctypes.windll.shell32.IsUserAnAdmin())
        except (AttributeError, OSError):
            return False

    def admin_required_message(self) -> str:
        return "Bu işlem yönetici yetkisi gerektirir. Lütfen uygulamayı yönetici olarak çalıştırın."
