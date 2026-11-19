"""ใส่สินค้าตัวอย่าง (mock) ร้านไลฟ์สไตล์ — รันซ้ำได้ ตัวที่มี SKU อยู่แล้วจะข้าม

ใช้:  docker compose exec api python -m app.scripts.seed_products
"""

import random
from itertools import product as combos

from sqlalchemy import select

from app.catalog.placeholder import placeholder_url
from app.catalog.schemas import ProductIn
from app.catalog.service import create_product
from app.db import SessionLocal
from app.models import Product, ProductImage, ProductVariant

random.seed(42)  # สุ่มสต็อกแบบเดิมทุกครั้ง ผลลัพธ์จะได้ซ้ำได้

COLORS_EN = {"ขาว": "WHT", "ดำ": "BLK", "ครีม": "CRM", "กรม": "NVY", "ชมพู": "PNK", "น้ำตาล": "BRN", "เทา": "GRY"}


def images_for(label: str, colors: list[str]) -> list[dict]:
    """รูปปก 1 รูป + ทุกสีมี 2 มุม (หน้า / หลัง) — สีหนึ่งมีได้หลายรูป"""
    out = [{"url": placeholder_url(label), "color": None, "sort_order": 0}]
    for color in colors:
        for order, view in enumerate(["Front", "Back"]):
            out.append({"url": placeholder_url(label, color, view), "color": color, "sort_order": order})
    return out


def variants(prefix: str, sizes: list[str | None], colors: list[str | None]) -> list[dict]:
    out = []
    for size, color in combos(sizes, colors):
        parts = [prefix, COLORS_EN.get(color, color) if color else None, size]
        out.append(
            {
                "sku": "-".join(p for p in parts if p),
                "size": size,
                "color": color,
                # บางตัวให้หมดสต็อก (0) ไว้เทสหน้าร้านตอน L-01 / L-03
                "stock": random.choice([0, 2, 5, 8, 12, 20, 35]),
            }
        )
    return out


SHADES = ["01 Nude", "02 Rose", "03 Coral", "04 Red"]

CATALOG = [
    # ---- เสื้อผ้า: เล็ก กลาง ใหญ่ × สี ----
    ("เสื้อยืด Oversize Cotton", "390", "Oversize Tee", {"หมวด": "เสื้อผ้า", "วัสดุ": "cotton 100%"},
     variants("TEE-OVS", ["S", "M", "L", "XL"], ["ขาว", "ดำ", "ครีม"])),
    ("เสื้อเชิ้ตลินิน แขนยาว", "890", "Linen Shirt", {"หมวด": "เสื้อผ้า", "วัสดุ": "linen"},
     variants("SHIRT-LIN", ["S", "M", "L"], ["ขาว", "กรม"])),
    ("ฮู้ดดี้ Fleece", "1290", "Hoodie", {"หมวด": "เสื้อผ้า", "วัสดุ": "fleece"},
     variants("HOOD-FLC", ["M", "L", "XL"], ["ดำ", "เทา", "ชมพู"])),
    ("กางเกงยีนส์ทรงกระบอก", "1190", "Straight Jeans", {"หมวด": "เสื้อผ้า"},
     variants("JEAN-STR", ["28", "30", "32", "34"], ["กรม", "ดำ"])),
    ("กระโปรงพลีท ยาว", "690", "Pleated Skirt", {"หมวด": "เสื้อผ้า"},
     variants("SKIRT-PLT", ["S", "M", "L"], ["ดำ", "ครีม"])),
    # ---- รองเท้า: เบอร์ × สี ----
    ("รองเท้าผ้าใบ Canvas", "1590", "Canvas Sneaker", {"หมวด": "รองเท้า"},
     variants("SNK-CNV", ["37", "38", "39", "40", "41", "42"], ["ขาว", "ดำ"])),
    ("รองเท้าแตะ Slide", "490", "Slide", {"หมวด": "รองเท้า"},
     variants("SLD", ["38", "40", "42"], ["ดำ", "ครีม"])),
    ("รองเท้าคัทชู หนังนิ่ม", "1890", "Flat Shoes", {"หมวด": "รองเท้า"},
     variants("FLAT", ["36", "37", "38", "39"], ["ดำ", "น้ำตาล"])),
    # ---- ตุ๊กตา: เล็ก กลาง ใหญ่ ----
    ("ตุ๊กตาหมีขนนุ่ม", "590", "Teddy Bear", {"หมวด": "ตุ๊กตา", "วัสดุ": "polyester"},
     variants("BEAR", ["S", "M", "L"], ["น้ำตาล", "ครีม"])),
    ("ตุ๊กตาแมวส้ม", "450", "Orange Cat", {"หมวด": "ตุ๊กตา"},
     variants("CAT", ["S", "M", "L"], [None])),
    ("หมอนอิงกระต่าย", "350", "Bunny Pillow", {"หมวด": "ตุ๊กตา"},
     variants("BUNNY", [None], ["ชมพู", "ขาว"])),
    # ---- เครื่องสำอาง: เฉดสี / ขนาดเดียว ----
    ("ลิปแมท Velvet", "299", "Velvet Lip", {"หมวด": "เครื่องสำอาง", "ปริมาณ": "3.5 g"},
     [{"sku": f"LIP-VLV-{s[:2]}", "color": s, "stock": random.choice([0, 10, 25])} for s in SHADES]),
    ("คุชชั่น SPF50", "690", "Cushion", {"หมวด": "เครื่องสำอาง", "ปริมาณ": "15 g"},
     [{"sku": f"CSH-{n}", "color": n, "stock": random.choice([5, 15, 30])} for n in ["21", "23", "25"]]),
    ("น้ำหอม Eau de Parfum", "1490", "Perfume", {"หมวด": "เครื่องสำอาง"},
     variants("EDP", ["30ml", "50ml", "100ml"], [None])),
    ("ครีมกันแดด Daily", "390", "Sunscreen", {"หมวด": "เครื่องสำอาง", "ปริมาณ": "50 ml"},
     [{"sku": "SUN-DLY", "stock": 40}]),
]


def colors_of(vs: list[dict]) -> list[str]:
    return list(dict.fromkeys(v["color"] for v in vs if v.get("color")))


def main() -> None:
    created = skipped = backfilled = 0
    with SessionLocal() as session:
        existing = set(session.scalars(select(ProductVariant.sku)))
        for name, price, label, details, vs in CATALOG:
            # เทียบแบบตัวใหญ่ เพราะระบบเก็บ SKU เป็นตัวใหญ่เสมอ
            if any(v["sku"].upper() in existing for v in vs):
                skipped += 1
                continue
            data = ProductIn(
                name=name, price=price, details=details, variants=vs,
                images=images_for(label, colors_of(vs)),
                description=f"{name} — สินค้าตัวอย่างสำหรับเดโม",
            )
            create_product(session, data)
            created += 1

        # สินค้าที่ใส่ไว้ก่อนมีตารางรูป → เติมรูปตามสีให้ (เฉพาะตัวที่ยังไม่มีรูปเลย)
        labels = {name: (label, colors_of(vs)) for name, _, label, _, vs in CATALOG}
        for product in session.scalars(select(Product).where(~Product.images.any())):
            if product.name in labels:
                label, colors = labels[product.name]
                session.add_all(ProductImage(product_id=product.id, **img) for img in images_for(label, colors))
                backfilled += 1
        session.commit()

    total_variants = sum(len(c[4]) for c in CATALOG)
    print(
        f"สร้างสินค้าใหม่ {created} · ข้าม (มีอยู่แล้ว) {skipped} · เติมรูปให้ของเดิม {backfilled}"
        f" · variant ทั้งหมดในชุดนี้ {total_variants}"
    )


if __name__ == "__main__":
    main()
