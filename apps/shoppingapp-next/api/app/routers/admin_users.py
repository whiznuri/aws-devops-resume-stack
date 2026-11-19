from typing import Annotated

from fastapi import APIRouter, Depends
from pydantic import BaseModel
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.auth.deps import Claims, require_roles
from app.db import get_session
from app.models import User
from app.privacy import mask_email

# ทั้ง router นี้เป็นของ admin (ข้อมูลสมาชิก / user management)
router = APIRouter(
    prefix="/admin/users",
    tags=["admin"],
    dependencies=[Depends(require_roles("admin"))],
)


class MemberOut(BaseModel):
    """ใบที่ admin เห็นเกี่ยวกับคนอื่น — email ถูก mask
    (admin เห็นได้แค่ไหน PM ยังไม่ชี้ขาด → ตามมาตรฐานทีม mask ไว้ก่อน)"""

    id: int
    email: str
    role: str
    is_active: bool


@router.get("")
def list_users(
    session: Annotated[Session, Depends(get_session)],
    claims: Annotated[Claims, Depends(require_roles("admin"))],
) -> list[MemberOut]:
    users = session.scalars(select(User).order_by(User.id))
    return [
        MemberOut(
            id=u.id,
            # เจ้าของข้อมูลเห็นของตัวเองเต็ม · คนอื่นเห็นแบบ mask
            email=u.email if u.id == claims.user_id else mask_email(u.email),
            role=u.role,
            is_active=u.is_active,
        )
        for u in users
    ]
