"""กติกาฝั่ง Business ของการล็อกอิน — แก้ตรงนี้เมื่อธุรกิจเปลี่ยน ไม่ต้องไปแตะกลไก"""

from dataclasses import dataclass
from datetime import timedelta

from app.config import settings

ROLES = ("customer", "staff", "admin")


@dataclass(frozen=True)
class SessionPolicy:
    idle: timedelta | None  # None = ไม่มี idle timeout
    absolute: timedelta


_staff = SessionPolicy(
    idle=timedelta(minutes=settings.staff_idle_minutes),
    absolute=timedelta(hours=settings.staff_absolute_hours),
)

SESSION_POLICIES: dict[str, SessionPolicy] = {
    "customer": SessionPolicy(idle=None, absolute=timedelta(days=settings.customer_absolute_days)),
    "staff": _staff,
    "admin": _staff,
}


def can_access(role: str, allowed: tuple[str, ...]) -> bool:
    """admin = god เข้าได้ทุกที่ (ไว้เช็คปัญหาที่ staff แจ้งก่อนส่งต่อ dev)"""
    return role == "admin" or role in allowed
