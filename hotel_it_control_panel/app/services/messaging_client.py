import asyncio
import json
import threading
from typing import Any, Dict, Optional

from PySide6.QtCore import QObject, Signal
import websockets


class MessagingClient(QObject):
    connected = Signal()
    disconnected = Signal()
    message_received = Signal(dict)
    users_updated = Signal(list)
    error = Signal(str)

    def __init__(self, server_url: str, token: str, identity: Dict[str, str]) -> None:
        super().__init__()
        self.server_url = server_url
        self.token = token
        self.identity = identity
        self._loop: Optional[asyncio.AbstractEventLoop] = None
        self._thread: Optional[threading.Thread] = None
        self._ws: Optional[websockets.WebSocketClientProtocol] = None
        self._stop_event = threading.Event()

    def start(self) -> None:
        if self._thread and self._thread.is_alive():
            return
        self._stop_event.clear()
        self._thread = threading.Thread(target=self._run_loop, daemon=True)
        self._thread.start()

    def stop(self) -> None:
        self._stop_event.set()
        if self._loop:
            self._loop.call_soon_threadsafe(self._loop.stop)

    def send_message(self, target: str, message: str) -> None:
        if not self._loop:
            return
        payload = {"type": "message", "target": target, "message": message}
        asyncio.run_coroutine_threadsafe(self._send(payload), self._loop)

    def _run_loop(self) -> None:
        self._loop = asyncio.new_event_loop()
        asyncio.set_event_loop(self._loop)
        self._loop.run_until_complete(self._connect_loop())

    async def _connect_loop(self) -> None:
        while not self._stop_event.is_set():
            try:
                async with websockets.connect(self.server_url, extra_headers={"Authorization": self.token}) as ws:
                    self._ws = ws
                    await self._register()
                    self.connected.emit()
                    await self._listen()
            except Exception as exc:  # noqa: BLE001
                self.error.emit(str(exc))
                self.disconnected.emit()
                await asyncio.sleep(2)

    async def _register(self) -> None:
        payload = {"type": "register", **self.identity}
        await self._send(payload)

    async def _listen(self) -> None:
        if not self._ws:
            return
        async for message in self._ws:
            data = json.loads(message)
            if data.get("type") == "users":
                self.users_updated.emit(data.get("users", []))
            elif data.get("type") == "message":
                self.message_received.emit(data)

    async def _send(self, payload: Dict[str, Any]) -> None:
        if self._ws:
            await self._ws.send(json.dumps(payload))
