"""ขั้นตอนล็อกอิน / ต่ออายุ / ออกจากระบบ — router เรียกใช้ ส่วนนี้ไม่รู้จัก HTTP"""

from datetime import datetime

from sqlalchemy import select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.auth.policy import SESSION_POLICIES
from app.auth.schemas import TokenPair
from app.auth.security import (
    create_access_token,
    hash_password,
    hash_refresh_token,
    new_refresh_token,
    verify_password,
)
from app.models import RefreshToken, User


class AuthError(Exception):
    """ล็อกอิน / ต่ออายุไม่ผ่าน — router แปลงเป็น 401"""


class EmailTaken(Exception):
    """email นี้มีคนใช้แล้ว — router แปลงเป็น 409"""


def create_user(session: Session, email: str, password: str, role: str = "customer") -> User:
    user = User(email=email.lower(), password_hash=hash_password(password), role=role)
    session.add(user)
    try:
        session.commit()
    except IntegrityError as exc:  # unique ของ email ใน DB เป็นด่านสุดท้าย กันสมัครซ้อนพร้อมกัน
        session.rollback()
        raise EmailTaken from exc
    return user


def login(session: Session, email: str, password: str, now: datetime) -> TokenPair:
    user = session.scalar(select(User).where(User.email == email.lower()))
    if not verify_password(password, user.password_hash if user else None) or not user.is_active:
        raise AuthError("email หรือรหัสผ่านไม่ถูกต้อง")  # ไม่บอกว่าผิดช่องไหน กันคนเดา email
    absolute = SESSION_POLICIES[user.role].absolute
    return _issue_tokens(session, user, now, session_expires_at=now + absolute)


def refresh(session: Session, raw_token: str, now: datetime) -> TokenPair:
    # FOR UPDATE: กันกดต่ออายุซ้อนกัน 2 ครั้งด้วยบัตรใบเดียว
    token = session.scalar(
        select(RefreshToken)
        .where(RefreshToken.token_hash == hash_refresh_token(raw_token))
        .with_for_update()
    )
    if token is None or token.revoked_at is not None:
        raise AuthError("บัตรต่ออายุใช้ไม่ได้")

    user = session.get(User, token.user_id)
    policy = SESSION_POLICIES[user.role]
    idle_too_long = policy.idle is not None and now - token.last_used_at > policy.idle

    if not user.is_active or now >= token.session_expires_at or idle_too_long:
        token.revoked_at = now
        session.commit()
        raise AuthError("หมดเวลา กรุณาเข้าสู่ระบบใหม่")

    # Rotation: บัตรเก่าใช้ได้ครั้งเดียว แลกบัตรใหม่ที่เพดานรวมเท่าเดิม
    token.revoked_at = now
    return _issue_tokens(session, user, now, session_expires_at=token.session_expires_at)


def logout(session: Session, raw_token: str, now: datetime) -> None:
    token = session.scalar(
        select(RefreshToken).where(RefreshToken.token_hash == hash_refresh_token(raw_token))
    )
    if token is not None and token.revoked_at is None:
        token.revoked_at = now
        session.commit()


def _issue_tokens(
    session: Session, user: User, now: datetime, session_expires_at: datetime
) -> TokenPair:
    access, access_expires_at = create_access_token(user.id, user.role, now, session_expires_at)
    raw, token_hash = new_refresh_token()
    session.add(
        RefreshToken(
            user_id=user.id,
            token_hash=token_hash,
            last_used_at=now,
            session_expires_at=session_expires_at,
        )
    )
    session.commit()
    return TokenPair(
        access_token=access,
        access_expires_at=access_expires_at,
        refresh_token=raw,
        session_expires_at=session_expires_at,
    )
