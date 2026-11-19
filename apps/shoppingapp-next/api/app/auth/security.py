"""กลไก Technical: hash รหัสผ่าน, เซ็น/ตรวจ JWT, สร้างบัตรต่ออายุ"""

import hashlib
import secrets
from datetime import datetime, timedelta

import jwt
from pwdlib import PasswordHash

from app.config import settings

_hasher = PasswordHash.recommended()  # Argon2 — มาตรฐานปัจจุบันสำหรับเก็บรหัสผ่าน
# ใช้ตอนไม่เจอ email: ยังเสียเวลาตรวจเท่าเดิม คนร้ายจะเดาไม่ได้ว่า email ไหนมีในระบบจากเวลาตอบ
_DUMMY_HASH = _hasher.hash("dummy-password-for-timing")


def hash_password(password: str) -> str:
    return _hasher.hash(password)


def verify_password(password: str, password_hash: str | None) -> bool:
    return _hasher.verify(password, password_hash or _DUMMY_HASH) and password_hash is not None


def create_access_token(user_id: int, role: str, now: datetime, session_expires_at: datetime) -> tuple[str, datetime]:
    # อายุสายรัด = 15 นาที แต่ห้ามเกินเพดานรวมของ session
    expires_at = min(now + timedelta(minutes=settings.access_token_minutes), session_expires_at)
    payload = {"sub": str(user_id), "role": role, "iat": now, "exp": expires_at}
    token = jwt.encode(payload, settings.jwt_secret, algorithm=settings.jwt_algorithm)
    return token, expires_at


def decode_access_token(token: str) -> dict:
    """ลายเซ็นผิด / หมดอายุ → โยน jwt.InvalidTokenError"""
    return jwt.decode(token, settings.jwt_secret, algorithms=[settings.jwt_algorithm])


def new_refresh_token() -> tuple[str, str]:
    raw = secrets.token_urlsafe(32)
    return raw, hash_refresh_token(raw)


def hash_refresh_token(raw: str) -> str:
    return hashlib.sha256(raw.encode()).hexdigest()
