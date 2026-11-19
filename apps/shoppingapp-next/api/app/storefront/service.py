"""หน้าร้าน (L-01) — ลูกค้าเห็นเฉพาะของที่เปิดขาย · ไม่ต้องล็อกอิน"""

from typing import Literal

from sqlalchemy import exists, select
from sqlalchemy.orm import Session, selectinload, with_loader_criteria

from app.config import settings
from app.models import Product, ProductVariant

Sort = Literal["new", "price_asc", "price_desc"]
Availability = Literal["in_stock", "low_stock", "sold_out"]

_ORDER = {
    "new": Product.id.desc(),
    "price_asc": Product.price.asc(),
    "price_desc": Product.price.desc(),
}

# โหลดมาเฉพาะ variant ที่เปิดขาย — ตัวที่ปิดขายไม่หลุดออกไปหน้าร้านเลย
_ACTIVE_CHILDREN = (
    selectinload(Product.variants),
    selectinload(Product.images),
    with_loader_criteria(ProductVariant, ProductVariant.is_active),
)


class NotFound(Exception):
    pass


def availability(stock: int) -> Availability:
    """กติกา Business: ไม่บอกจำนวนจริง บอกแค่ มีของ / เหลือน้อย / หมด"""
    if stock <= 0:
        return "sold_out"
    if stock <= settings.low_stock_threshold:
        return "low_stock"
    return "in_stock"


def list_products(session: Session, q: str | None, sort: Sort, limit: int, offset: int) -> list[Product]:
    has_active_variant = exists().where(ProductVariant.product_id == Product.id, ProductVariant.is_active)
    stmt = (
        select(Product)
        .where(Product.is_active, has_active_variant)
        .options(*_ACTIVE_CHILDREN)
        .order_by(_ORDER[sort], Product.id.desc())
        .limit(limit)
        .offset(offset)
    )
    if q:
        stmt = stmt.where(Product.name.ilike(f"%{q.strip()}%"))
    return list(session.scalars(stmt))


def get_product(session: Session, product_id: int) -> Product:
    product = session.scalar(
        select(Product).where(Product.id == product_id, Product.is_active).options(*_ACTIVE_CHILDREN)
    )
    if product is None or not product.variants:
        raise NotFound  # ปิดขาย = ลูกค้ามองไม่เห็น เหมือนไม่มีสินค้านี้
    return product
