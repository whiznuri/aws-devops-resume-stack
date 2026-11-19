"""Loop test L-07 ยกเลิกออเดอร์ที่เกินเวลา — 1 ฟังก์ชัน = 1 เส้นทางใน L-07-expire-orders.md"""

from datetime import UTC, datetime, timedelta

import pytest

from app.auth.service import create_user
from app.main import app
from app.models import Order, Payment, ProductVariant
from app.orders.expiry import expire_overdue
from app.payments.omise import get_omise
from fakes import FakeOmise

PASSWORD = "password123"
SHIPPING = {"shipping_name": "แอน ใจดี", "shipping_phone": "0812345678", "shipping_address": "99/1 ถ.สุขุมวิท กรุงเทพฯ 10110"}
LATER = datetime.now(UTC) + timedelta(hours=2)  # "อีก 2 ชั่วโมงข้างหน้า" = เลยเวลาจอง 1 ชม. แน่นอน


def login(client, db_session, email, role="customer"):
    create_user(db_session, email, PASSWORD, role=role)
    token = client.post("/auth/login", json={"email": email, "password": PASSWORD}).json()
    return {"Authorization": f"Bearer {token['access_token']}"}


@pytest.fixture
def omise(client):
    fake = FakeOmise()
    app.dependency_overrides[get_omise] = lambda: fake
    return fake


@pytest.fixture
def setup(client, db_session):
    """เสื้อมี 5 ตัว · ann สั่ง 2 ตัว → เหลือ 3"""
    staff = login(client, db_session, "staff@example.com", "staff")
    ann = login(client, db_session, "ann@example.com")
    product = client.post(
        "/admin/products", json={"name": "เสื้อ", "price": "100", "variants": [{"sku": "TEE-1", "stock": 5}]}, headers=staff
    ).json()
    variant_id = product["variants"][0]["id"]
    client.post("/cart/items", json={"variant_id": variant_id, "quantity": 2}, headers=ann)
    order_id = client.post("/orders", json=SHIPPING, headers=ann).json()["id"]
    return {"order_id": order_id, "variant_id": variant_id, "ann": ann}


def state(db_session, setup):
    db_session.expire_all()
    order = db_session.get(Order, setup["order_id"])
    return order.status, db_session.get(ProductVariant, setup["variant_id"]).stock


def test_overdue_unpaid_order_expires_and_stock_returns(db_session, setup):
    assert state(db_session, setup) == ("pending_payment", 3)
    result = expire_overdue(db_session, LATER, omise=None)
    assert result == {"expired": 1, "paid": 0, "skipped": 0}
    assert state(db_session, setup) == ("expired", 5)  # คืน 2 ตัวกลับเข้าสต็อก


def test_not_yet_due_is_untouched(db_session, setup):
    expire_overdue(db_session, datetime.now(UTC), omise=None)
    assert state(db_session, setup) == ("pending_payment", 3)


def test_paid_at_last_minute_becomes_paid_not_expired(client, db_session, omise, setup):
    client.post(f"/orders/{setup['order_id']}/payment", headers=setup["ann"])
    omise.pay("chrg_test_1")  # ลูกค้าสแกนจ่ายนาทีสุดท้าย แต่ยังไม่ได้กดตรวจสถานะ

    result = expire_overdue(db_session, LATER, omise=omise)
    assert result["paid"] == 1
    assert state(db_session, setup) == ("paid", 3)  # ไม่คืนสต็อก เพราะขายไปแล้วจริง


def test_unpaid_qr_expires_too(client, db_session, omise, setup):
    client.post(f"/orders/{setup['order_id']}/payment", headers=setup["ann"])
    expire_overdue(db_session, LATER, omise=omise)
    assert state(db_session, setup) == ("expired", 5)
    assert db_session.query(Payment).one().status == "expired"


def test_omise_down_means_wait_not_cancel(client, db_session, omise, setup):
    client.post(f"/orders/{setup['order_id']}/payment", headers=setup["ann"])
    omise.down = True
    result = expire_overdue(db_session, LATER, omise=omise)
    assert result["skipped"] == 1
    assert state(db_session, setup) == ("pending_payment", 3)  # ยังไม่แน่ใจ = ยังไม่ยกเลิก


def test_already_paid_order_is_never_touched(client, db_session, omise, setup):
    client.post(f"/orders/{setup['order_id']}/payment", headers=setup["ann"])
    omise.pay("chrg_test_1")
    client.post(f"/orders/{setup['order_id']}/payment/check", headers=setup["ann"])

    assert expire_overdue(db_session, LATER, omise=omise) == {"expired": 0, "paid": 0, "skipped": 0}
    assert state(db_session, setup) == ("paid", 3)


def test_customer_sees_expired_status(client, db_session, setup):
    expire_overdue(db_session, LATER, omise=None)
    assert client.get(f"/orders/{setup['order_id']}", headers=setup["ann"]).json()["status"] == "expired"
