"""Loop test L-03 checkout — 1 ฟังก์ชัน = 1 เส้นทางใน L-03-checkout.md"""

import threading
from datetime import UTC, datetime, timedelta
from decimal import Decimal

import pytest
from sqlalchemy import text
from sqlalchemy.orm import Session

from app.auth.service import create_user
from app.models import CartItem, Product, ProductVariant
from app.orders import service

PASSWORD = "password123"
SHIPPING = {"shipping_name": "แอน ใจดี", "shipping_phone": "0812345678", "shipping_address": "99/1 ถ.สุขุมวิท กรุงเทพฯ 10110"}


def login(client, db_session, email, role="customer"):
    create_user(db_session, email, PASSWORD, role=role)
    token = client.post("/auth/login", json={"email": email, "password": PASSWORD}).json()
    return {"Authorization": f"Bearer {token['access_token']}"}


@pytest.fixture
def staff(client, db_session):
    return login(client, db_session, "staff@example.com", "staff")


@pytest.fixture
def ann(client, db_session):
    return login(client, db_session, "ann@example.com")


@pytest.fixture
def tee(client, staff):
    body = {
        "name": "เสื้อยืด",
        "price": "390.50",
        "variants": [
            {"sku": "TEE-WHT-M", "size": "M", "color": "ขาว", "stock": 5},
            {"sku": "TEE-BLK-L", "size": "L", "color": "ดำ", "stock": 2},
        ],
    }
    product = client.post("/admin/products", json=body, headers=staff).json()
    return {v["sku"]: v["id"] for v in product["variants"]} | {"product_id": product["id"]}


def add(client, headers, variant_id, quantity=1):
    return client.post("/cart/items", json={"variant_id": variant_id, "quantity": quantity}, headers=headers)


def checkout(client, headers, body=SHIPPING):
    return client.post("/orders", json=body, headers=headers)


def stock(db_session, variant_id):
    db_session.expire_all()
    return db_session.get(ProductVariant, variant_id).stock


# ---------- ทางหลัก ----------

def test_checkout_cuts_stock_creates_order_and_clears_cart(client, db_session, ann, tee):
    add(client, ann, tee["TEE-WHT-M"], 3)
    add(client, ann, tee["TEE-BLK-L"], 1)

    res = checkout(client, ann)
    assert res.status_code == 201
    order = res.json()
    assert order["status"] == "pending_payment"
    assert order["subtotal"] == "1562.00"  # 390.50 × 4
    assert len(order["items"]) == 2

    assert stock(db_session, tee["TEE-WHT-M"]) == 2  # ตัดทันทีตอนกดสั่งซื้อ
    assert stock(db_session, tee["TEE-BLK-L"]) == 1
    assert client.get("/cart", headers=ann).json()["lines"] == []  # ตะกร้าล้างแล้ว


def test_order_reserved_for_one_hour(client, ann, tee):
    add(client, ann, tee["TEE-WHT-M"])
    before = datetime.now(UTC)
    expires = datetime.fromisoformat(checkout(client, ann).json()["expires_at"])
    assert timedelta(minutes=59) < expires - before < timedelta(minutes=61)


def test_price_is_locked_at_order_time(client, staff, ann, tee):
    add(client, ann, tee["TEE-WHT-M"], 2)
    order_id = checkout(client, ann).json()["id"]

    client.patch(f"/admin/products/{tee['product_id']}", json={"price": "999.00", "name": "ชื่อใหม่"}, headers=staff)

    item = client.get(f"/orders/{order_id}", headers=ann).json()["items"][0]
    assert item["unit_price"] == "390.50"  # ราคา ณ ตอนสั่ง ไม่ตามราคาใหม่
    assert item["product_name"] == "เสื้อยืด"
    assert item["line_total"] == "781.00"


# ---------- ทางปฏิเสธ ----------

def test_checkout_needs_login(client):
    assert client.post("/orders", json=SHIPPING).status_code == 401


def test_empty_cart_and_double_click(client, ann, tee):
    assert checkout(client, ann).status_code == 409  # ตะกร้าว่าง

    add(client, ann, tee["TEE-WHT-M"])
    assert checkout(client, ann).status_code == 201
    second = checkout(client, ann)  # กดซ้ำ
    assert second.status_code == 409
    assert second.json()["detail"] == "ตะกร้าว่าง"
    assert len(client.get("/orders", headers=ann).json()) == 1  # ไม่เกิดออเดอร์ซ้ำ


def test_not_enough_stock_rolls_back_everything(client, db_session, ann, tee):
    add(client, ann, tee["TEE-WHT-M"], 2)
    add(client, ann, tee["TEE-BLK-L"], 2)
    # มีคนอื่นซื้อสีดำไป 1 ตัว ระหว่างที่ ann ยังไม่กดสั่งซื้อ
    db_session.get(ProductVariant, tee["TEE-BLK-L"]).stock = 1
    db_session.commit()

    res = checkout(client, ann)
    assert res.status_code == 409
    assert res.json()["detail"]["problems"] == {"TEE-BLK-L": "มีไม่พอ"}
    assert stock(db_session, tee["TEE-WHT-M"]) == 5  # แถวที่พอก็ไม่ถูกตัด (ทั้งก้อนหรือไม่เลย)
    assert len(client.get("/cart", headers=ann).json()["lines"]) == 2  # ตะกร้ายังอยู่ครบ
    assert client.get("/orders", headers=ann).json() == []


def test_suspended_item_blocks_checkout(client, staff, ann, tee):
    add(client, ann, tee["TEE-WHT-M"])
    client.patch(f"/admin/products/{tee['product_id']}", json={"is_active": False}, headers=staff)

    res = checkout(client, ann)
    assert res.json()["detail"]["problems"] == {"TEE-WHT-M": "ไม่มีจำหน่ายแล้ว"}


@pytest.mark.parametrize(
    "field,value",
    [("shipping_phone", "12345"), ("shipping_phone", "08-1234-5678"), ("shipping_address", "สั้น"), ("shipping_name", "")],
)
def test_shipping_validation(client, ann, tee, field, value):
    add(client, ann, tee["TEE-WHT-M"])
    assert checkout(client, ann, {**SHIPPING, field: value}).status_code == 422


# ---------- ของใครของมัน ----------

def test_cannot_see_other_users_order(client, db_session, ann, tee):
    add(client, ann, tee["TEE-WHT-M"])
    order_id = checkout(client, ann).json()["id"]
    bob = login(client, db_session, "bob@example.com")

    assert client.get(f"/orders/{order_id}", headers=bob).status_code == 404
    assert client.get("/orders", headers=bob).json() == []
    assert client.get(f"/orders/{order_id}", headers=ann).json()["shipping_phone"] == "0812345678"  # เจ้าของเห็นเต็ม


# ---------- แย่งชิ้นสุดท้าย (พร้อมกันจริง 2 thread) ----------

def test_two_buyers_race_for_last_item(engine):
    """สองคนกดซื้อชิ้นสุดท้ายพร้อมกัน → ได้แค่คนเดียว สต็อกไม่ติดลบ
    ใช้ DB จริงแบบ commit จริง (ไม่ผ่าน rollback ของ fixture) เพราะต้องมี 2 connection แย่งล็อกกัน"""
    with Session(engine) as s:
        a = create_user(s, "race-a@example.com", PASSWORD)
        b = create_user(s, "race-b@example.com", PASSWORD)
        product = Product(name="ชิ้นสุดท้าย", price=Decimal("100"))
        product.variants = [ProductVariant(sku="LAST-1", stock=1)]
        s.add(product)
        s.commit()
        variant_id = product.variants[0].id
        s.add_all([CartItem(user_id=a.id, variant_id=variant_id, quantity=1), CartItem(user_id=b.id, variant_id=variant_id, quantity=1)])
        s.commit()
        buyers = [a.id, b.id]

    results: list[str] = []
    start = threading.Barrier(2)  # ให้ทั้งสองคนออกตัวพร้อมกัน

    def buy(user_id: int):
        with Session(engine) as s:
            start.wait()
            try:
                service.checkout(s, user_id, service.Shipping("คนซื้อ", "0812345678", "ที่อยู่ทดสอบ 12345"), datetime.now(UTC))
                results.append("ได้")
            except service.CannotBuy:
                results.append("หมด")

    try:
        threads = [threading.Thread(target=buy, args=(u,)) for u in buyers]
        for t in threads:
            t.start()
        for t in threads:
            t.join(timeout=30)

        assert sorted(results) == sorted(["ได้", "หมด"])  # คนหนึ่งได้ อีกคนเจอของหมด (ไม่สนว่าใครได้)
        with Session(engine) as s:
            assert s.get(ProductVariant, variant_id).stock == 0  # ไม่ติดลบ ไม่ขายเกิน
    finally:
        with engine.begin() as conn:  # ล้างข้อมูลที่ commit จริงในเทสนี้
            conn.execute(text("TRUNCATE order_items, orders, cart_items, refresh_tokens, product_images, product_variants, products, users RESTART IDENTITY CASCADE"))
