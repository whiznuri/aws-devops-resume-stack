from collections.abc import Iterator
from datetime import datetime

from sqlalchemy import DateTime, create_engine
from sqlalchemy.orm import DeclarativeBase, Session, sessionmaker

from app.config import settings

# pool_pre_ping: เช็คสายก่อนใช้ กัน connection ที่ตายไปแล้วตอน DB รีสตาร์ท
engine = create_engine(settings.database_url, pool_pre_ping=True)
SessionLocal = sessionmaker(bind=engine, autoflush=False, expire_on_commit=False)


class Base(DeclarativeBase):
    """แม่แบบของทุก model — Alembic อ่าน Base.metadata เพื่อรู้ว่ามีตารางอะไรบ้าง"""

    # เวลาทุกช่องเก็บแบบมี timezone กันเวลาเพี้ยนเมื่อ server อยู่คนละโซน
    type_annotation_map = {datetime: DateTime(timezone=True)}


def get_session() -> Iterator[Session]:
    """Session per request: 1 request = 1 session ปิดเองเมื่อตอบกลับเสร็จ"""
    with SessionLocal() as session:
        yield session
