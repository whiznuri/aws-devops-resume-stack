from sqlalchemy.exc import OperationalError

from app.db import get_session
from app.main import app


def test_health_all_ok(client):
    # Act: ยิง GET /health (ต่อ DB จริงที่ localhost:5433 — ต้องเปิด docker compose อยู่)
    res = client.get("/health")

    # Assert: ค่าที่วัดได้ต้องตรงกับค่าที่คาดไว้
    assert res.status_code == 200
    assert res.json() == {"api": "ok", "db": "ok"}


class BrokenSession:
    """session ปลอมที่ทำตัวเหมือน DB ล่ม — เหมือนถอดสายหน้างาน แต่ไม่ต้องไปปิด DB จริง"""

    def execute(self, *args, **kwargs):
        raise OperationalError("SELECT 1", {}, Exception("จำลอง: DB ล่ม"))


def test_health_db_down(client):
    # Arrange: สลับ session จริงออก เสียบ session ปลอมแทน
    app.dependency_overrides[get_session] = lambda: BrokenSession()

    # Act
    res = client.get("/health")

    # Assert: API ยังตอบได้ แต่ต้องบอกว่า DB ล่ม
    assert res.status_code == 200
    assert res.json() == {"api": "ok", "db": "down"}
