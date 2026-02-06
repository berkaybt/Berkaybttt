import json
import logging
from pathlib import Path
from typing import Dict, List

from fastapi import FastAPI, WebSocket, WebSocketDisconnect
import uvicorn


base_dir = Path(__file__).resolve().parent
config_path = base_dir / "config.json"
config = json.loads(config_path.read_text(encoding="utf-8"))
TOKEN = config.get("token", "")

app = FastAPI()
clients: Dict[str, WebSocket] = {}
user_info: Dict[str, Dict[str, str]] = {}
ws_to_key: Dict[int, str] = {}

logs_dir = base_dir / "logs"
logs_dir.mkdir(parents=True, exist_ok=True)
logging.basicConfig(
    filename=logs_dir / "server.log",
    level=logging.INFO,
    format="%(asctime)s | %(levelname)s | %(message)s",
)


def serialize_users() -> List[Dict[str, str]]:
    return list(user_info.values())


async def broadcast_users() -> None:
    payload = {"type": "users", "users": serialize_users()}
    for ws in clients.values():
        await ws.send_json(payload)


@app.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket) -> None:
    token = websocket.headers.get("authorization", "")
    if token != TOKEN:
        await websocket.close(code=1008)
        return

    await websocket.accept()
    try:
        while True:
            data = await websocket.receive_json()
            if data.get("type") == "register":
                identity = {
                    "hostname": data.get("hostname", ""),
                    "username": data.get("username", ""),
                }
                key = f"{identity['hostname']}|{identity['username']}"
                clients[key] = websocket
                user_info[key] = identity
                ws_to_key[id(websocket)] = key
                await broadcast_users()
                logging.info("Registered %s", key)
            if data.get("type") == "message":
                target = data.get("target")
                sender_key = ws_to_key.get(id(websocket), "")
                sender = user_info.get(sender_key, {}).get("hostname", "")
                payload = {
                    "type": "message",
                    "sender": sender,
                    "message": data.get("message", ""),
                }
                if target in clients:
                    await clients[target].send_json(payload)
                    logging.info("Message from %s to %s", sender_key, target)
    except WebSocketDisconnect:
        key = ws_to_key.pop(id(websocket), "")
        if key:
            clients.pop(key, None)
            user_info.pop(key, None)
        await broadcast_users()


if __name__ == "__main__":
    uvicorn.run(
        "server:app",
        host=config.get("host", "0.0.0.0"),
        port=int(config.get("port", 8000)),
        reload=False,
    )
