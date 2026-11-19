"""Loop test L-09 แบบอัตโนมัติ — backoffice เพิ่ม / แก้ / ปิดขายสินค้า"""

import pytest

from app.auth.service import create_user

PASSWORD = "password123"

TSHIRT = {
    "name": "เสื้อยืด Basic",
    "price": "390.00",
    "details": {"material": "cotton 100%"},
    "variants": [
        {"sku": "tee-wht-s", "size": "S", "color": "ขาว", "stock": 5},
        {"sku": "TEE-BLK-L", "size": "L", "color": "ดำ", "stock": 2},
    ],
}


@pytest.fixture
def staff(client, db_session):
    create_user(db_session, "staff@example.com", PASSWORD, role="staff")
    token = client.post("/auth/login", json={"email": "staff@example.com", "password": PASSWORD}).json()
    return {"Authorization": f"Bearer {token['access_token']}"}


@pytest.fixture
def customer(client, db_session):
    create_user(db_session, "c@example.com", PASSWORD)
    token = client.post("/auth/login", json={"email": "c@example.com", "password": PASSWORD}).json()
    return {"Authorization": f"Bearer {token['access_token']}"}


def create(client, headers, body=TSHIRT):
    return client.post("/admin/products", json=body, headers=headers)


# ---------- สิทธิ์ ----------

def test_customer_and_anonymous_cannot_manage_products(client, customer):
    assert client.get("/admin/products").status_code == 401
    assert client.get("/admin/products", headers=customer).status_code == 403
    assert create(client, customer).status_code == 403


# ---------- เพิ่มสินค้า ----------

def test_staff_creates_product_with_variants(client, staff):
    res = create(client, staff)
    assert res.status_code == 201
    body = res.json()
    assert body["price"] == "390.00"  # เงินเป็นข้อความ ไม่ใช่ float
    assert [v["sku"] for v in body["variants"]] == ["TEE-WHT-S", "TEE-BLK-L"]  # SKU เป็นตัวใหญ่เสมอ
    assert body["is_active"] is True


@pytest.mark.parametrize(
    "bad",
    [
        {**TSHIRT, "price": "-1"},  # ราคาติดลบ
        {**TSHIRT, "price": "10.999"},  # ทศนิยมเกิน 2 ตำแหน่ง
        {**TSHIRT, "name": "   "},  # ชื่อว่าง
        {**TSHIRT, "variants": []},  # ไม่มีตัวเลือกให้ขาย
        {**TSHIRT, "variants": [{"sku": "X-1", "stock": -3}]},  # สต็อกติดลบ
        {**TSHIRT, "id": 999},  # ส่งช่องที่ไม่รู้จักมา
    ],
)
def test_invalid_product_rejected(client, staff, bad):
    assert create(client, staff, bad).status_code == 422


def test_duplicate_sku_conflicts(client, staff):
    create(client, staff)
    again = {**TSHIRT, "name": "เสื้ออีกตัว", "variants": [{"sku": "TEE-WHT-S"}]}
    assert create(client, staff, again).status_code == 409


# ---------- ค้นหา ----------

def test_search_by_name_or_sku(client, staff):
    create(client, staff)
    create(client, staff, {"name": "ตุ๊กตาหมี", "price": "590", "variants": [{"sku": "BEAR-M", "size": "M"}]})

    by_name = client.get("/admin/products", params={"q": "ตุ๊กตา"}, headers=staff).json()
    by_sku = client.get("/admin/products", params={"q": "tee-blk"}, headers=staff).json()

    assert [p["name"] for p in by_name] == ["ตุ๊กตาหมี"]
    assert [p["name"] for p in by_sku] == ["เสื้อยืด Basic"]


# ---------- แก้ / ปิดขาย ----------

def test_edit_price_and_suspend(client, staff):
    pid = create(client, staff).json()["id"]

    res = client.patch(f"/admin/products/{pid}", json={"price": "350.50", "is_active": False}, headers=staff)
    assert res.status_code == 200
    assert res.json()["price"] == "350.50"
    assert res.json()["is_active"] is False

    # ปิดขายแล้วยังอยู่ในระบบ (soft delete) แอดมินยังเห็นและเปิดกลับได้
    assert client.get(f"/admin/products/{pid}", headers=staff).status_code == 200


def test_no_hard_delete(client, staff):
    pid = create(client, staff).json()["id"]
    assert client.delete(f"/admin/products/{pid}", headers=staff).status_code == 405


def test_add_variant_and_suspend_variant(client, staff):
    pid = create(client, staff).json()["id"]

    res = client.post(f"/admin/products/{pid}/variants", json={"sku": "TEE-WHT-M", "size": "M", "color": "ขาว", "stock": 4}, headers=staff)
    assert res.status_code == 201
    new_variant = next(v for v in res.json()["variants"] if v["sku"] == "TEE-WHT-M")

    res = client.patch(f"/admin/variants/{new_variant['id']}", json={"is_active": False}, headers=staff)
    assert next(v for v in res.json()["variants"] if v["sku"] == "TEE-WHT-M")["is_active"] is False


def test_stock_cannot_be_edited_here(client, staff):
    vid = create(client, staff).json()["variants"][0]["id"]
    # สต็อกต้องเปลี่ยนผ่าน L-10 ที่มีประวัติเท่านั้น
    assert client.patch(f"/admin/variants/{vid}", json={"stock": 999}, headers=staff).status_code == 422


def test_unknown_product_404(client, staff):
    assert client.get("/admin/products/999999", headers=staff).status_code == 404
    assert client.patch("/admin/products/999999", json={"name": "x"}, headers=staff).status_code == 404
