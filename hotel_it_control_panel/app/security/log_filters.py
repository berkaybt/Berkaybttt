import re
from typing import Pattern


EMAIL_PATTERN: Pattern[str] = re.compile(r"([\w\.-]+)@([\w\.-]+)")


def mask_emails(text: str) -> str:
    def replacer(match: re.Match[str]) -> str:
        user, domain = match.groups()
        masked_user = user[0] + "***" if user else "***"
        return f"{masked_user}@{domain}"

    return EMAIL_PATTERN.sub(replacer, text)
