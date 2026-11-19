"""Loop test L-02 ตะกร้า — 1 ฟังก์ชัน = 1 เส้นทางใน L-02-cart.md"""

import pytest

from app.auth.service import create_user
from app.models import ProductVariant

PASSWORD = "password123"


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
    """เสื้อ: ขาว M มี 5 · ดำ L มี 2 · ขาว S หมด"""
    body = {
        "name": "เสื้อยืด",
        "price": "390.50",
        "variants": [
            {"sku": "TEE-WHT-M", "size": "M", "color": "ขาว", "stock": 5},
            {"sku": "TEE-BLK-L", "size": "L", "color": "ดำ", "stock": 2},
            {"sku": "TEE-WHT-S", "size": "S", "color": "ขาว", "stock": 0},
        ],
        "images": [{"url": "https://img.example/cover.png"}, {"url": "https://img.example/black.png", "color": "ดำ"}],
    }
    product = client.post("/admin/products", json=body, headers=staff).json()
    return {v["sku"]: v["id"] for v in product["variants"]} | {"product_id": product["id"]}


def add(client, headers, variant_id, quantity=1):
    return client.post("/cart/items", json={"variant_id": variant_id, "quantity": quantity}, headers=headers)


# ---------- ต้องมี ID ----------

def test_cart_needs_login(client, tee):
    assert client.get("/cart").status_code == 401
    assert add(client, {}, tee["TEE-WHT-M"]).status_code == 401


# ---------- ใส่ตะกร้า ----------

def test_add_and_add_again_merges_quantity(client, ann, tee):
    add(client, ann, tee["TEE-WHT-M"], 2)
    cart = add(client, ann, tee["TEE-WHT-M"], 1).json()

    assert len(cart["lines"]) == 1  # ใส่ซ้ำ = เพิ่มจำนวน ไม่ใช่เพิ่มแถว
    line = cart["lines"][0]
    assert line["quantity"] == 3
    assert line["line_total"] == "1171.50"  # 390.50 × 3 คิดแบบ Decimal ไม่เพี้ยน
    assert cart["subtotal"] == "1171.50"
    assert cart["item_count"] == 3
    assert cart["can_checkout"] is True


def test_image_follows_chosen_color(client, ann, tee):
    line = add(client, ann, tee["TEE-BLK-L"]).json()["lines"][0]
    assert line["image_url"] == "https://img.example/black.png"


def test_cannot_add_sold_out_or_more_than_stock(client, ann, tee):
    sold_out = add(client, ann, tee["TEE-WHT-S"])
    assert sold_out.status_code == 409
    assert sold_out.json()["detail"] == "สินค้าหมด"

    too_many = add(client, ann, tee["TEE-BLK-L"], 3)
    assert too_many.status_code == 409
    assert too_many.json()["detail"] == "สินค้ามีไม่พอ"  # ไม่บอกตัวเลขจริง


def test_quantity_limits(client, ann, tee):
    assert add(client, ann, tee["TEE-WHT-M"], 0).status_code == 422
    assert add(client, ann, tee["TEE-WHT-M"], 11).status_code == 422


def test_cannot_add_suspended(client, staff, ann, tee):
    client.patch(f"/admin/products/{tee['product_id']}", json={"is_active": False}, headers=staff)
    res = add(client, ann, tee["TEE-WHT-M"])
    assert res.status_code == 409
    assert res.json()["detail"] == "สินค้านี้ไม่มีจำหน่ายแล้ว"


def test_cart_does_not_reserve_stock(client, ann, db_session, tee):
    add(client, ann, tee["TEE-WHT-M"], 5)
    assert db_session.get(ProductVariant, tee["TEE-WHT-M"]).stock == 5  # ตะกร้าไม่แตะสต็อก


# ---------- แก้ / ลบ ----------

def test_change_quantity_and_remove(client, ann, tee):
    add(client, ann, tee["TEE-WHT-M"], 1)
    cart = client.patch(f"/cart/items/{tee['TEE-WHT-M']}", json={"quantity": 4}, headers=ann).json()
    assert cart["lines"][0]["quantity"] == 4

    over = client.patch(f"/cart/items/{tee['TEE-WHT-M']}", json={"quantity": 6}, headers=ann)
    assert over.status_code == 409

    cart = client.delete(f"/cart/items/{tee['TEE-WHT-M']}", headers=ann).json()
    assert cart["lines"] == []
    assert cart["can_checkout"] is False  # ตะกร้าว่าง checkout ไม่ได้


def test_change_item_not_in_cart(client, ann, tee):
    assert client.patch(f"/cart/items/{tee['TEE-WHT-M']}", json={"quantity": 2}, headers=ann).status_code == 404
    assert client.delete(f"/cart/items/{tee['TEE-WHT-M']}", headers=ann).status_code == 404


# ---------- สถานะเปลี่ยนหลังใส่ตะกร้า ----------

def test_line_becomes_unbuyable_after_changes(client, staff, ann, db_session, tee):
    add(client, ann, tee["TEE-WHT-M"], 4)
    add(client, ann, tee["TEE-BLK-L"], 1)

    # มีคนอื่นซื้อไปจนเหลือ 2 (จำลองด้วยการแก้ DB) + staff ปิดขายสีดำ
    db_session.get(ProductVariant, tee["TEE-WHT-M"]).stock = 2
    db_session.commit()
    client.patch(f"/admin/variants/{tee['TEE-BLK-L']}", json={"is_active": False}, headers=staff)

    cart = client.get("/cart", headers=ann).json()
    status = {line["sku"]: line["status"] for line in cart["lines"]}
    assert status == {"TEE-WHT-M": "insufficient", "TEE-BLK-L": "unavailable"}
    assert cart["subtotal"] == "0.00"  # แถวที่ซื้อไม่ได้ไม่นับเงิน
    assert cart["can_checkout"] is False


# ---------- ของใครของมัน ----------

def test_each_user_sees_only_own_cart(client, db_session, ann, tee):
    add(client, ann, tee["TEE-WHT-M"], 2)
    bob = login(client, db_session, "bob@example.com")

    assert client.get("/cart", headers=bob).json()["lines"] == []
    # bob แก้ / ลบแถวของ ann ไม่ได้ (ระบบหาแค่ในตะกร้าของ bob เอง)
    assert client.delete(f"/cart/items/{tee['TEE-WHT-M']}", headers=bob).status_code == 404
    assert client.get("/cart", headers=ann).json()["lines"][0]["quantity"] == 2
