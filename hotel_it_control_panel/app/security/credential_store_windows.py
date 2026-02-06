import sys
from dataclasses import dataclass

import win32cred


@dataclass
class Credential:
    username: str
    secret: str


class CredentialStoreWindows:
    def __init__(self, target: str) -> None:
        self.target = target

    def save(self, credential: Credential) -> None:
        if sys.platform != "win32":
            raise RuntimeError("Credential Manager yalnızca Windows'ta desteklenir.")
        credential_blob = credential.secret.encode("utf-16le")
        win32cred.CredWrite(
            {
                "Type": win32cred.CRED_TYPE_GENERIC,
                "TargetName": self.target,
                "UserName": credential.username,
                "CredentialBlob": credential_blob,
                "Persist": win32cred.CRED_PERSIST_LOCAL_MACHINE,
            },
            0,
        )

    def load(self) -> Credential | None:
        if sys.platform != "win32":
            return None
        data = win32cred.CredRead(self.target, win32cred.CRED_TYPE_GENERIC, 0)
        secret = data["CredentialBlob"].decode("utf-16le")
        return Credential(username=data["UserName"], secret=secret)

    def delete(self) -> None:
        if sys.platform != "win32":
            return
        win32cred.CredDelete(self.target, win32cred.CRED_TYPE_GENERIC, 0)
