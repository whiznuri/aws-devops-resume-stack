"""จัดการสินค้าฝั่ง backoffice — router เรียกใช้ ส่วนนี้ไม่รู้จัก HTTP"""

from sqlalchemy import or_, select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session, selectinload

from app.catalog.schemas import ImageIn, ProductIn, ProductUpdate, VariantIn, VariantUpdate
from app.models import Product, ProductImage, ProductVariant

# ดึงลูก (variant + รูป) มาพร้อมกันในรอบเดียว กันปัญหา N+1 (ดู lessons/orm.md)
WITH_CHILDREN = (selectinload(Product.variants), selectinload(Product.images))


class NotFound(Exception):
    """ไม่มีสินค้า / variant / รูปนี้ — router แปลงเป็น 404"""


class DuplicateSku(Exception):
    """SKU ซ้ำ — router แปลงเป็น 409"""


def list_products(session: Session, q: str | None, limit: int, offset: int) -> list[Product]:
    stmt = select(Product).options(*WITH_CHILDREN).order_by(Product.id.desc()).limit(limit).offset(offset)
    if q:
        pattern = f"%{q.strip()}%"
        sku_match = select(ProductVariant.product_id).where(ProductVariant.sku.ilike(pattern))
        stmt = stmt.where(or_(Product.name.ilike(pattern), Product.id.in_(sku_match)))
    return list(session.scalars(stmt))


def get_product(session: Session, product_id: int) -> Product:
    product = session.get(Product, product_id, options=WITH_CHILDREN)
    if product is None:
        raise NotFound
    return product


def create_product(session: Session, data: ProductIn) -> Product:
    product = Product(**data.model_dump(exclude={"variants", "images"}))
    product.variants = [ProductVariant(**v.model_dump()) for v in data.variants]
    product.images = [ProductImage(**i.model_dump()) for i in data.images]
    session.add(product)
    _commit(session)  # สินค้า + variant + รูป บันทึกใน transaction เดียว
    return product


def update_product(session: Session, product_id: int, data: ProductUpdate) -> Product:
    product = get_product(session, product_id)
    for field, value in data.model_dump(exclude_unset=True).items():
        setattr(product, field, value)
    _commit(session)
    return product


def add_variant(session: Session, product_id: int, data: VariantIn) -> Product:
    product = get_product(session, product_id)
    product.variants.append(ProductVariant(**data.model_dump()))
    _commit(session)
    return product


def update_variant(session: Session, variant_id: int, data: VariantUpdate) -> Product:
    variant = session.get(ProductVariant, variant_id)
    if variant is None:
        raise NotFound
    for field, value in data.model_dump(exclude_unset=True).items():
        setattr(variant, field, value)
    _commit(session)
    session.expire_all()  # ให้ get_product อ่านค่าล่าสุดจาก DB
    return get_product(session, variant.product_id)


def add_image(session: Session, product_id: int, data: ImageIn) -> Product:
    product = get_product(session, product_id)
    product.images.append(ProductImage(**data.model_dump()))
    _commit(session)
    session.expire_all()
    return get_product(session, product_id)


def delete_image(session: Session, image_id: int) -> Product:
    # รูปเป็นแค่ลิงก์ ไม่ใช่ข้อมูลธุรกรรม → ลบจริงได้ (ต่างจากสินค้าที่ห้ามลบ)
    image = session.get(ProductImage, image_id)
    if image is None:
        raise NotFound
    product_id = image.product_id
    session.delete(image)
    _commit(session)
    session.expire_all()
    return get_product(session, product_id)


def _commit(session: Session) -> None:
    try:
        session.commit()
    except IntegrityError as exc:
        session.rollback()
        # UNIQUE ของ sku ใน DB = ด่านสุดท้าย (กันกรณีสองคนบันทึก SKU เดียวกันพร้อมกัน)
        if "sku" in str(exc.orig):
            raise DuplicateSku from exc
        raise
