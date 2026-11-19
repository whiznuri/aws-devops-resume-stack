"""Loop test L-04 ชำระเงิน — ใช้ Omise ปลอม (test double #19: เทสด้วยน้ำแทนน้ำมัน)
ไม่ยิง Omise จริงในเทส: เร็ว ไม่ต้องพึ่งเน็ต และจำลองเคสยาก ๆ ได้ (ยอดไม่ตรง, charge ของออเดอร์อื่น)"""

from datetime import timedelta

import pytest

from app.auth.service import create_user
from app.main import app
from app.models import Order
from app.payments.omise import get_omise
from fakes import FakeOmise

PASSWORD = "password123"
SHIPPING = {"shipping_name": "แอน ใจดี", "shipping_phone": "0812345678", "shipping_address": "99/1 ถ.สุขุมวิท กรุงเทพฯ 10110"}


@pytest.fixture
def omise(client):
    fake = FakeOmise()
    app.dependency_overrides[get_omise] = lambda: fake
    return fake


def login(client, db_session, email, role="customer"):
    create_user(db_session, email, PASSWORD, role=role)
    token = client.post("/auth/login", json={"email": email, "password": PASSWORD}).json()
    return {"Authorization": f"Bearer {token['access_token']}"}


@pytest.fixture
def order(client, db_session):
    staff = login(client, db_session, "staff@example.com", "staff")
    ann = login(client, db_session, "ann@example.com")
    product = client.post(
        "/admin/products", json={"name": "เสื้อ", "price": "390.50", "variants": [{"sku": "TEE-1", "stock": 5}]}, headers=staff
    ).json()
    client.post("/cart/items", json={"variant_id": product["variants"][0]["id"], "quantity": 2}, headers=ann)
    created = client.post("/orders", json=SHIPPING, headers=ann).json()
    return {"id": created["id"], "headers": ann}


def start(client, order):
    return client.post(f"/orders/{order['id']}/payment", headers=order["headers"])


def check(client, order):
    return client.post(f"/orders/{order['id']}/payment/check", headers=order["headers"])


def test_start_payment_returns_qr_with_exact_amount(client, omise, order):
    res = start(client, order)
    assert res.status_code == 201
    assert res.json()["qr_image_url"].startswith("https://")
    assert res.json()["amount"] == "781.00"
    assert omise.charges["chrg_test_1"].amount_satang == 78100  # 781.00 บาท = 78100 สตางค์


def test_pressing_pay_twice_reuses_same_qr(client, omise, order):
    first = start(client, order).json()
    second = start(client, order).json()
    assert first["qr_image_url"] == second["qr_image_url"]
    assert omise.created == 1  # ไม่สร้าง charge ซ้ำ = ไม่เก็บเงินซ้ำ


def test_check_before_paying_stays_pending(client, omise, order):
    start(client, order)
    assert check(client, order).json()["order_status"] == "pending_payment"


def test_paid_marks_order_paid(client, omise, order):
    start(client, order)
    omise.pay("chrg_test_1")
    body = check(client, order).json()
    assert body["order_status"] == "paid"
    assert body["payment"]["status"] == "successful"
    assert start(client, order).status_code == 409  # จ่ายแล้วกดจ่ายอีกไม่ได้


def test_wrong_amount_is_not_accepted(client, omise, order):
    start(client, order)
    omise.pay("chrg_test_1", amount_satang=100)  # มีคนแอบจ่ายแค่ 1 บาท
    assert check(client, order).json()["order_status"] == "pending_payment"


def test_charge_of_another_order_is_not_accepted(client, omise, order):
    start(client, order)
    omise.pay("chrg_test_1", order_id="999")
    assert check(client, order).json()["order_status"] == "pending_payment"


def test_failed_qr_lets_customer_get_a_new_one(client, omise, order):
    start(client, order)
    omise.charges["chrg_test_1"].status = "expired"
    check(client, order)
    assert start(client, order).json()["qr_image_url"] == "https://qr.example/2.png"


def test_cannot_pay_expired_order(client, omise, order, db_session):
    db_order = db_session.get(Order, order["id"])
    db_order.expires_at -= timedelta(hours=2)
    db_session.commit()
    res = start(client, order)
    assert res.status_code == 409
    assert res.json()["detail"] == "หมดเวลาชำระแล้ว"


def test_others_cannot_pay_or_peek(client, omise, order, db_session):
    bob = login(client, db_session, "bob@example.com")
    assert client.post(f"/orders/{order['id']}/payment", headers=bob).status_code == 404
    assert client.get(f"/orders/{order['id']}/payment", headers=bob).status_code == 404
    assert client.post(f"/orders/{order['id']}/payment").status_code == 401
