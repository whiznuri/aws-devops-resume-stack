"""Loop test L-08 แบบอัตโนมัติ — 1 ฟังก์ชัน = 1 เส้นทางใน L-08-login-role.md"""

from datetime import UTC, datetime, timedelta

from sqlalchemy import select

from app.auth.security import hash_refresh_token
from app.auth.service import create_user
from app.models import RefreshToken, User

PASSWORD = "password123"


def make_user(db_session, email: str, role: str = "customer") -> User:
    return create_user(db_session, email, PASSWORD, role=role)


def login(client, email: str, password: str = PASSWORD):
    return client.post("/auth/login", json={"email": email, "password": password})


def bearer(token: str) -> dict:
    return {"Authorization": f"Bearer {token}"}


def stored_token(db_session, raw: str) -> RefreshToken:
    return db_session.scalar(
        select(RefreshToken).where(RefreshToken.token_hash == hash_refresh_token(raw))
    )


# ---------- สมัคร ----------

def test_register_creates_customer(client):
    res = client.post("/auth/register", json={"email": "Ann@Example.com", "password": PASSWORD})
    assert res.status_code == 201
    assert res.json()["role"] == "customer"
    assert res.json()["email"] == "ann@example.com"  # เก็บเป็นตัวเล็กเสมอ
    assert "password_hash" not in res.json()  # schema ไม่ส่ง hash ออกไป


def test_register_same_email_twice_conflicts(client):
    body = {"email": "ann@example.com", "password": PASSWORD}
    client.post("/auth/register", json=body)
    assert client.post("/auth/register", json=body).status_code == 409


def test_register_rejects_short_password(client):
    res = client.post("/auth/register", json={"email": "ann@example.com", "password": "short"})
    assert res.status_code == 422


# ---------- ล็อกอิน ----------

def test_login_returns_tokens_and_me_works(client, db_session):
    make_user(db_session, "ann@example.com")
    res = login(client, "ann@example.com")
    assert res.status_code == 200
    tokens = res.json()

    me = client.get("/auth/me", headers=bearer(tokens["access_token"]))
    assert me.status_code == 200
    assert me.json()["email"] == "ann@example.com"


def test_login_wrong_password(client, db_session):
    make_user(db_session, "ann@example.com")
    assert login(client, "ann@example.com", "wrong-password").status_code == 401


def test_login_unknown_email_same_answer(client):
    res = login(client, "nobody@example.com")
    assert res.status_code == 401
    assert res.json()["detail"] == "email หรือรหัสผ่านไม่ถูกต้อง"  # ไม่บอกว่าไม่มี email นี้


def test_login_disabled_account(client, db_session):
    user = make_user(db_session, "ann@example.com")
    user.is_active = False
    db_session.commit()
    assert login(client, "ann@example.com").status_code == 401


def test_me_without_or_with_fake_token(client):
    assert client.get("/auth/me").status_code == 401
    assert client.get("/auth/me", headers=bearer("not-a-real-jwt")).status_code == 401


# ---------- ต่ออายุ (refresh) ----------

def test_refresh_rotates_and_old_token_dies(client, db_session):
    make_user(db_session, "ann@example.com")
    first = login(client, "ann@example.com").json()

    second = client.post("/auth/refresh", json={"refresh_token": first["refresh_token"]})
    assert second.status_code == 200
    assert second.json()["refresh_token"] != first["refresh_token"]
    # เพดานรวมไม่ขยายตามการต่ออายุ
    assert second.json()["session_expires_at"] == first["session_expires_at"]

    reuse = client.post("/auth/refresh", json={"refresh_token": first["refresh_token"]})
    assert reuse.status_code == 401


def test_staff_idle_over_30_minutes_must_login_again(client, db_session):
    make_user(db_session, "staff@example.com", role="staff")
    tokens = login(client, "staff@example.com").json()
    stored_token(db_session, tokens["refresh_token"]).last_used_at -= timedelta(minutes=31)
    db_session.commit()

    res = client.post("/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert res.status_code == 401


def test_staff_idle_under_30_minutes_still_ok(client, db_session):
    make_user(db_session, "staff@example.com", role="staff")
    tokens = login(client, "staff@example.com").json()
    stored_token(db_session, tokens["refresh_token"]).last_used_at -= timedelta(minutes=29)
    db_session.commit()

    res = client.post("/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert res.status_code == 200


def test_customer_has_no_idle_timeout(client, db_session):
    make_user(db_session, "ann@example.com")
    tokens = login(client, "ann@example.com").json()
    stored_token(db_session, tokens["refresh_token"]).last_used_at -= timedelta(days=3)
    db_session.commit()

    res = client.post("/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert res.status_code == 200


def test_absolute_timeout_ends_session(client, db_session):
    make_user(db_session, "staff@example.com", role="staff")
    tokens = login(client, "staff@example.com").json()
    stored_token(db_session, tokens["refresh_token"]).session_expires_at = datetime.now(UTC) - timedelta(seconds=1)
    db_session.commit()

    res = client.post("/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert res.status_code == 401


def test_logout_kills_refresh_token(client, db_session):
    make_user(db_session, "ann@example.com")
    tokens = login(client, "ann@example.com").json()
    assert client.post("/auth/logout", json={"refresh_token": tokens["refresh_token"]}).status_code == 204

    res = client.post("/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert res.status_code == 401


def test_disabled_staff_cannot_refresh(client, db_session):
    user = make_user(db_session, "staff@example.com", role="staff")
    tokens = login(client, "staff@example.com").json()
    user.is_active = False  # admin ปิดบัญชีพนักงาน
    db_session.commit()

    res = client.post("/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert res.status_code == 401


# ---------- สิทธิ์ (RBAC) ----------

def test_admin_users_page_by_role(client, db_session):
    for email, role in [("c@example.com", "customer"), ("s@example.com", "staff"), ("a@example.com", "admin")]:
        make_user(db_session, email, role=role)

    assert client.get("/admin/users").status_code == 401  # ไม่มีตัวตน

    expected = {"c@example.com": 403, "s@example.com": 403, "a@example.com": 200}
    for email, status in expected.items():
        token = login(client, email).json()["access_token"]
        assert client.get("/admin/users", headers=bearer(token)).status_code == status, email
