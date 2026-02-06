import getpass
import os
import platform
import re
import socket
import subprocess
from dataclasses import dataclass
from datetime import datetime
from typing import Dict, Tuple

import psutil


@dataclass
class SystemInfo:
    hostname: str
    username: str
    domain: str
    os_version: str
    uptime: str
    ip_address: str
    mac_address: str
    gateway: str
    dns: str
    cpu_percent: float
    ram_percent: float
    disk_usage: Dict[str, float]
    health_score: int


def _format_uptime() -> str:
    boot_time = datetime.fromtimestamp(psutil.boot_time())
    delta = datetime.now() - boot_time
    hours, remainder = divmod(int(delta.total_seconds()), 3600)
    minutes = remainder // 60
    return f"{hours} saat {minutes} dk"


def _get_ipconfig_details() -> Tuple[str, str]:
    try:
        result = subprocess.run(
            ["ipconfig", "/all"],
            capture_output=True,
            text=True,
            check=False,
        )
        output = result.stdout
    except FileNotFoundError:
        return "", ""

    gateway_match = re.search(r"Default Gateway\s*:\s*([\d\.]+)", output)
    dns_match = re.search(r"DNS Servers\s*:\s*([\d\.]+)", output)
    gateway = gateway_match.group(1) if gateway_match else ""
    dns = dns_match.group(1) if dns_match else ""
    return gateway, dns


def _get_primary_ip_mac() -> Tuple[str, str]:
    ip_address = ""
    mac_address = ""
    for interface, addrs in psutil.net_if_addrs().items():
        for addr in addrs:
            if addr.family == socket.AF_INET and not addr.address.startswith("127."):
                ip_address = addr.address
            if addr.family == psutil.AF_LINK:
                mac_address = addr.address
        if ip_address:
            break
    return ip_address, mac_address


def get_system_info() -> SystemInfo:
    ip_address, mac_address = _get_primary_ip_mac()
    gateway, dns = _get_ipconfig_details()
    cpu_percent = psutil.cpu_percent(interval=0.2)
    disk_usage = {
        partition.device: psutil.disk_usage(partition.mountpoint).percent
        for partition in psutil.disk_partitions()
        if partition.fstype
    }
    health_score = calculate_health_score(cpu_percent=cpu_percent, ram_percent=ram_percent, disk_usage=disk_usage)

    return SystemInfo(
        hostname=platform.node(),
        username=getpass.getuser(),
        domain=os.environ.get("USERDOMAIN", ""),
        os_version=platform.platform(),
        uptime=_format_uptime(),
        ip_address=ip_address or socket.gethostbyname(socket.gethostname()),
        mac_address=mac_address,
        gateway=gateway,
        dns=dns,
        cpu_percent=cpu_percent,
        ram_percent=psutil.virtual_memory().percent,
        disk_usage=disk_usage,
        health_score=health_score,
    )


def system_info_as_dict() -> Dict[str, str]:
    info = get_system_info()
    disk_text = ", ".join([f"{drive}: {usage:.1f}%" for drive, usage in info.disk_usage.items()])
    return {
        "PC Adı": info.hostname,
        "Kullanıcı": info.username,
        "Domain/Workgroup": info.domain,
        "OS": info.os_version,
        "Uptime": info.uptime,
        "IP": info.ip_address,
        "MAC": info.mac_address,
        "Gateway": info.gateway,
        "DNS": info.dns,
        "CPU %": f"{info.cpu_percent:.1f}",
        "RAM %": f"{info.ram_percent:.1f}",
        "Disk": disk_text,
        "Health Score": str(info.health_score),
    }


def calculate_health_score(cpu_percent: float, ram_percent: float, disk_usage: Dict[str, float]) -> int:
    score = 100
    score -= int(cpu_percent // 2)
    score -= int(ram_percent // 2)
    if disk_usage:
        score -= int(max(disk_usage.values()) // 2)
    return max(score, 0)
