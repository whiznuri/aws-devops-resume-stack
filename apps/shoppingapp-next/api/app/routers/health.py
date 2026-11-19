from typing import Annotated

from fastapi import APIRouter, Depends
from sqlalchemy import text
from sqlalchemy.exc import SQLAlchemyError
from sqlalchemy.orm import Session

from app.db import get_session

router = APIRouter(tags=["health"])


@router.get("/health")
def health(session: Annotated[Session, Depends(get_session)]) -> dict[str, str]:
    # ตอบ 200 เสมอ แล้วบอกสถานะ DB แยก — หน้าเว็บจะได้โชว์ได้ว่าสายไหนขาด
    try:
        session.execute(text("SELECT 1"))
        db = "ok"
    except SQLAlchemyError:
        db = "down"
    return {"api": "ok", "db": db}
