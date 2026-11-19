import os

# ปิดตัวจับเวลา L-07 ระหว่างเทส (ต้องตั้งก่อน import app — settings อ่านค่าตอน import)
os.environ["EXPIRE_JOB_ENABLED"] = "false"

import pytest
from fastapi.testclient import TestClient
from sqlalchemy import create_engine, text
from sqlalchemy.engine import make_url
from sqlalchemy.orm import Session

from app.db import Base, get_session
from app.main import app

# เทสใช้ DB แยก (shop_test) ไม่ไปปนข้อมูลใน DB ที่ใช้ dev
TEST_DATABASE_URL = os.environ.get(
    "TEST_DATABASE_URL", "postgresql+psycopg://shop:shop@localhost:5433/shop_test"
)


def _ensure_database(url: str) -> None:
    """สร้าง DB เทสถ้ายังไม่มี (ต่อเข้า DB ระบบชื่อ postgres ก่อน)"""
    target = make_url(url)
    admin = create_engine(target.set(database="postgres"), isolation_level="AUTOCOMMIT")
    with admin.connect() as conn:
        exists = conn.scalar(
            text("SELECT 1 FROM pg_database WHERE datname = :name"), {"name": target.database}
        )
        if not exists:
            conn.execute(text(f'CREATE DATABASE "{target.database}"'))
    admin.dispose()


@pytest.fixture(scope="session")
def engine():
    """pre ครั้งเดียวต่อการรันเทส: เตรียม DB เทสให้มีตารางล่าสุด"""
    _ensure_database(TEST_DATABASE_URL)
    eng = create_engine(TEST_DATABASE_URL)
    Base.metadata.drop_all(eng)
    Base.metadata.create_all(eng)
    yield eng
    eng.dispose()


@pytest.fixture
def db_session(engine):
    """pre: เปิด transaction ใหญ่ครอบเทส 1 ข้อ · post: rollback ทิ้งทั้งก้อน
    เทสแต่ละข้อจึงเริ่มจาก DB ว่างเสมอ (เหมือนระบายน้ำออกจากไลน์หลังเทส)"""
    connection = engine.connect()
    outer = connection.begin()
    # commit ในโค้ดแอปจะกลายเป็น savepoint ไม่ได้ commit จริง
    session = Session(bind=connection, join_transaction_mode="create_savepoint", expire_on_commit=False)
    yield session
    session.close()
    outer.rollback()
    connection.close()


@pytest.fixture
def client(db_session):
    """TestClient ที่ใช้ session ของ DB เทส"""
    app.dependency_overrides[get_session] = lambda: db_session
    with TestClient(app) as c:
        yield c
    app.dependency_overrides.clear()
