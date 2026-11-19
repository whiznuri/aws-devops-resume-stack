"""ด่านตรวจของ API — ใช้กับทุก endpoint ที่ต้องล็อกอิน / ต้องมี role"""

from dataclasses import dataclass
from typing import Annotated

import jwt
from fastapi import Depends, HTTPException, status
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer

from app.auth.policy import can_access
from app.auth.security import decode_access_token

_bearer = HTTPBearer(auto_error=False)


@dataclass(frozen=True)
class Claims:
    user_id: int
    role: str


def get_claims(
    credentials: Annotated[HTTPAuthorizationCredentials | None, Depends(_bearer)],
) -> Claims:
    """อ่านสายรัดข้อมือ (JWT) อย่างเดียว ไม่ถาม DB — นี่คือจุดเด่นของ JWT"""
    unauthorized = HTTPException(
        status.HTTP_401_UNAUTHORIZED,
        "ยังไม่ได้เข้าสู่ระบบ",
        headers={"WWW-Authenticate": "Bearer"},
    )
    if credentials is None:
        raise unauthorized
    try:
        payload = decode_access_token(credentials.credentials)
    except jwt.InvalidTokenError:
        raise unauthorized from None
    return Claims(user_id=int(payload["sub"]), role=payload["role"])


def require_roles(*allowed: str):
    """ใช้แบบ Depends(require_roles("staff")) — admin ผ่านทุกด่านอัตโนมัติ"""

    def checker(claims: Annotated[Claims, Depends(get_claims)]) -> Claims:
        if not can_access(claims.role, allowed):
            raise HTTPException(status.HTTP_403_FORBIDDEN, "ไม่มีสิทธิ์เข้าส่วนนี้")
        return claims

    return checker
