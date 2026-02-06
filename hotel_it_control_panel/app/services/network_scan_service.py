import ipaddress
import socket
import subprocess
import time
from concurrent.futures import ThreadPoolExecutor, as_completed
from dataclasses import dataclass
from typing import Iterable, List


@dataclass
class ScanResult:
    ip: str
    hostname: str
    reachable: bool
    latency_ms: float
    open_ports: List[int]


def parse_ip_targets(target_text: str) -> Iterable[str]:
    target_text = target_text.strip()
    if "/" in target_text:
        network = ipaddress.ip_network(target_text, strict=False)
        for ip in network.hosts():
            yield str(ip)
        return
    if "-" in target_text:
        base, end = target_text.split("-")
        start_ip = ipaddress.ip_address(base.strip())
        end_suffix = int(end.strip())
        prefix = ".".join(base.strip().split(".")[:-1])
        for suffix in range(int(str(start_ip).split(".")[-1]), end_suffix + 1):
            yield f"{prefix}.{suffix}"
        return
    yield target_text


def _ping_ip(ip: str, timeout_ms: int = 800) -> tuple[bool, float]:
    start = time.time()
    try:
        result = subprocess.run(
            ["ping", "-n", "1", "-w", str(timeout_ms), ip],
            capture_output=True,
            text=True,
            check=False,
        )
        reachable = result.returncode == 0
    except FileNotFoundError:
        reachable = False
    latency = (time.time() - start) * 1000
    return reachable, latency


def _resolve_hostname(ip: str) -> str:
    try:
        return socket.gethostbyaddr(ip)[0]
    except (socket.herror, socket.gaierror):
        return ""


def _check_ports(ip: str, ports: List[int], timeout: float = 0.3) -> List[int]:
    open_ports = []
    for port in ports:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as sock:
            sock.settimeout(timeout)
            result = sock.connect_ex((ip, port))
            if result == 0:
                open_ports.append(port)
    return open_ports


def scan_targets(target_text: str, ports: List[int] | None = None, max_workers: int = 64) -> List[ScanResult]:
    ports = ports or []
    results: List[ScanResult] = []
    targets = list(parse_ip_targets(target_text))
    with ThreadPoolExecutor(max_workers=min(max_workers, len(targets) or 1)) as executor:
        futures = {executor.submit(_scan_single, ip, ports): ip for ip in targets}
        for future in as_completed(futures):
            results.append(future.result())
    return sorted(results, key=lambda item: item.ip)


def _scan_single(ip: str, ports: List[int]) -> ScanResult:
    reachable, latency = _ping_ip(ip)
    hostname = _resolve_hostname(ip)
    open_ports = _check_ports(ip, ports) if ports and reachable else []
    return ScanResult(
        ip=ip,
        hostname=hostname,
        reachable=reachable,
        latency_ms=latency,
        open_ports=open_ports,
    )
