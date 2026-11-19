"""มาตรฐาน data masking ของทีม — ตัวอย่างในเทสนี้คือสเปค"""

import pytest

from app.auth.service import create_user
from app.privacy import mask_email, mask_part


@pytest.mark.parametrize(
    ("raw", "masked"),
    [
        ("admin@example.com", "a***n@e*****e.com"),
        ("ann@shop.co.th", "a*n@s*****o.th"),
        ("ab@x.io", "a*@x*.io"),
    ],
)
def test_mask_email(raw, masked):
    assert mask_email(raw) == masked


def test_mask_short_parts():
    assert mask_part("a") == "a*"
    assert mask_part("ab") == "a*"
    assert mask_part("abc") == "a*c"


def test_admin_sees_others_masked_but_self_in_full(client, db_session):
    create_user(db_session, "admin@example.com", "password123", role="admin")
    create_user(db_session, "customer@example.com", "password123")
    token = client.post(
        "/auth/login", json={"email": "admin@example.com", "password": "password123"}
    ).json()["access_token"]

    emails = [m["email"] for m in client.get("/admin/users", headers={"Authorization": f"Bearer {token}"}).json()]

    assert "admin@example.com" in emails  # ของตัวเอง เห็นเต็ม
    assert "c******r@e*****e.com" in emails  # ของคนอื่น ถูก mask
    assert "customer@example.com" not in emails
