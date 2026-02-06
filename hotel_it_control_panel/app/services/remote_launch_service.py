import subprocess
from pathlib import Path
from typing import List


class RemoteLaunchService:
    def launch_rdp(self, ip: str) -> None:
        subprocess.Popen(["mstsc.exe", f"/v:{ip}"])

    def launch_tool(self, exe_path: str, args: List[str] | None = None) -> None:
        path = Path(exe_path)
        if not path.exists():
            raise FileNotFoundError(f"{exe_path} bulunamadı")
        subprocess.Popen([str(path), *(args or [])])

    def launch_admin_tool(self, tool: str) -> None:
        subprocess.Popen([tool])
