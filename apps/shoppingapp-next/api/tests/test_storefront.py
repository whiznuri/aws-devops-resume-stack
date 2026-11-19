"""Loop test L-01 หน้าร้าน + รูปต่อสี (ต่อจาก L-09)"""

import pytest

from app.auth.service import create_user

PASSWORD = "password123"


@pytest.fixture
def staff(client, db_session):
    create_user(db_session, "staff@example.com", PASSWORD, role="staff")
    token = client.post("/auth/login", json={"email": "staff@example.com", "password": PASSWORD}).json()
    return {"Authorization": f"Bearer {token['access_token']}"}


def make(client, staff, name, price, variants, images=()):
    body = {"name": name, "price": price, "variants": variants, "images": list(images)}
    res = client.post("/admin/products", json=body, headers=staff)
    assert res.status_code == 201, res.text
    return res.json()


@pytest.fixture
def shop(client, staff):
    tee = make(
        client, staff, "เสื้อยืด", "390",
        [
            {"sku": "TEE-WHT-S", "size": "S", "color": "ขาว", "stock": 10},
            {"sku": "TEE-WHT-M", "size": "M", "color": "ขาว", "stock": 2},  # เหลือน้อย
            {"sku": "TEE-BLK-L", "size": "L", "color": "ดำ", "stock": 0},  # หมด
        ],
        [
            {"url": "https://img.example/tee.png"},
            {"url": "https://img.example/tee-white.png", "color": "ขาว"},
            {"url": "https://img.example/tee-black.png", "color": "ดำ"},
        ],
    )
    bear = make(client, staff, "ตุ๊กตาหมี", "590", [{"sku": "BEAR-M", "size": "M", "stock": 0}])
    lip = make(client, staff, "ลิปสติก", "299", [{"sku": "LIP-01", "color": "01 Nude", "stock": 5}])
    hidden = make(client, staff, "ของปิดขาย", "100", [{"sku": "HID-1", "stock": 5}])
    client.patch(f"/admin/products/{hidden['id']}", json={"is_active": False}, headers=staff)
    return {"tee": tee, "bear": bear, "lip": lip, "hidden": hidden}


def test_storefront_needs_no_login(client, shop):
    assert client.get("/products").status_code == 200


def test_hidden_product_not_listed_and_404(client, shop):
    names = [p["name"] for p in client.get("/products").json()]
    assert "ของปิดขาย" not in names
    assert client.get(f"/products/{shop['hidden']['id']}").status_code == 404


def test_card_shows_cover_colors_sizes_sold_out(client, shop):
    cards = {p["name"]: p for p in client.get("/products").json()}
    tee = cards["เสื้อยืด"]
    assert tee["cover_url"] == "https://img.example/tee.png"  # รูปรวม (ไม่ระบุสี) เป็นปก
    assert tee["colors"] == ["ขาว", "ดำ"]
    assert tee["sizes"] == ["S", "M", "L"]
    assert tee["sold_out"] is False
    assert cards["ตุ๊กตาหมี"]["sold_out"] is True


def test_detail_hides_real_stock_numbers(client, shop):
    detail = client.get(f"/products/{shop['tee']['id']}").json()
    by_sku = {v["sku"]: v["availability"] for v in detail["variants"]}
    assert by_sku == {"TEE-WHT-S": "in_stock", "TEE-WHT-M": "low_stock", "TEE-BLK-L": "sold_out"}
    assert all("stock" not in v for v in detail["variants"])  # ตัวเลขจริงไม่หลุดออกหน้าร้าน


def test_suspended_variant_hidden_from_storefront(client, staff, shop):
    black = next(v for v in shop["tee"]["variants"] if v["sku"] == "TEE-BLK-L")
    client.patch(f"/admin/variants/{black['id']}", json={"is_active": False}, headers=staff)

    detail = client.get(f"/products/{shop['tee']['id']}").json()
    assert "TEE-BLK-L" not in [v["sku"] for v in detail["variants"]]
    card = next(p for p in client.get("/products").json() if p["name"] == "เสื้อยืด")
    assert card["colors"] == ["ขาว"]


def test_search_and_sort(client, shop):
    assert [p["name"] for p in client.get("/products", params={"q": "ตุ๊กตา"}).json()] == ["ตุ๊กตาหมี"]
    cheap_first = [p["price"] for p in client.get("/products", params={"sort": "price_asc"}).json()]
    assert cheap_first == sorted(cheap_first, key=float)
    assert client.get("/products", params={"sort": "random"}).status_code == 422


def test_images_grouped_by_color(client, shop):
    images = client.get(f"/products/{shop['tee']['id']}").json()["images"]
    assert [i["color"] for i in images] == [None, "ขาว", "ดำ"]  # รูปรวมก่อน แล้วตามสี


def test_staff_adds_and_deletes_image(client, staff, shop):
    pid = shop["lip"]["id"]
    res = client.post(f"/admin/products/{pid}/images", json={"url": "https://img.example/lip.png", "color": "01 Nude"}, headers=staff)
    assert res.status_code == 201
    image_id = res.json()["images"][0]["id"]

    assert client.delete(f"/admin/images/{image_id}", headers=staff).json()["images"] == []


@pytest.mark.parametrize("url", ["javascript:alert(1)", "http://insecure.example/a.png", "data:image/png;base64,AAA"])
def test_image_must_be_https_link(client, staff, shop, url):
    res = client.post(f"/admin/products/{shop['lip']['id']}/images", json={"url": url}, headers=staff)
    assert res.status_code == 422
