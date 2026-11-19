"""ตะกร้า (L-02) — อ่านสต็อกเพื่อเช็คอย่างเดียว ไม่จอง ไม่ตัด (ตัดตอน checkout L-03)"""

from dataclasses import dataclass
from decimal import Decimal
from typing import Literal

from sqlalchemy import select
from sqlalchemy.orm import Session, selectinload

from app.config import settings
from app.models import CartItem, Product, ProductVariant

LineStatus = Literal["ok", "insufficient", "sold_out", "unavailable"]


class CartRuleError(Exception):
    """ผิดกติกาตะกร้า — router แปลงเป็น 409 พร้อมข้อความนี้"""


class NotInCart(Exception):
    """ไม่มีรายการนี้ในตะกร้า — router แปลงเป็น 404"""


@dataclass
class CartLine:
    variant_id: int
    product_id: int
    product_name: str
    sku: str
    size: str | None
    color: str | None
    image_url: str | None
    unit_price: Decimal
    quantity: int
    line_total: Decimal
    status: LineStatus


@dataclass
class CartView:
    lines: list[CartLine]
    subtotal: Decimal  # คิดเฉพาะแถวที่ซื้อได้
    item_count: int
    can_checkout: bool


def _line_status(item: CartItem) -> LineStatus:
    v = item.variant
    if not v.is_active or not v.product.is_active:
        return "unavailable"
    if v.stock <= 0:
        return "sold_out"
    if v.stock < item.quantity:
        return "insufficient"
    return "ok"


def _image_for(v: ProductVariant) -> str | None:
    same_color = [i for i in v.product.images if i.color == v.color]
    pool = same_color or v.product.images
    return pool[0].url if pool else None


def _sellable_variant(session: Session, variant_id: int) -> ProductVariant:
    v = session.get(ProductVariant, variant_id, options=[selectinload(ProductVariant.product)])
    if v is None or not v.is_active or not v.product.is_active:
        raise CartRuleError("สินค้านี้ไม่มีจำหน่ายแล้ว")
    return v


def _check_quantity(v: ProductVariant, quantity: int) -> None:
    if quantity > settings.cart_max_quantity:
        raise CartRuleError(f"ใส่ได้สูงสุด {settings.cart_max_quantity} ชิ้นต่อรายการ")
    if v.stock <= 0:
        raise CartRuleError("สินค้าหมด")
    if v.stock < quantity:
        raise CartRuleError("สินค้ามีไม่พอ")  # ไม่บอกตัวเลขจริง (กติกาเดียวกับหน้าร้าน)


def _find(session: Session, user_id: int, variant_id: int) -> CartItem | None:
    return session.scalar(
        select(CartItem).where(CartItem.user_id == user_id, CartItem.variant_id == variant_id)
    )


def add_item(session: Session, user_id: int, variant_id: int, quantity: int) -> None:
    v = _sellable_variant(session, variant_id)
    item = _find(session, user_id, variant_id)
    new_quantity = (item.quantity if item else 0) + quantity  # ใส่ซ้ำ = เพิ่มจำนวนในแถวเดิม
    _check_quantity(v, new_quantity)
    if item:
        item.quantity = new_quantity
    else:
        session.add(CartItem(user_id=user_id, variant_id=variant_id, quantity=new_quantity))
    session.commit()


def set_quantity(session: Session, user_id: int, variant_id: int, quantity: int) -> None:
    item = _find(session, user_id, variant_id)
    if item is None:
        raise NotInCart
    if quantity > item.quantity:  # ลดจำนวนได้เสมอ · เพิ่มต้องเช็คของ
        _check_quantity(_sellable_variant(session, variant_id), quantity)
    item.quantity = quantity
    session.commit()


def remove_item(session: Session, user_id: int, variant_id: int) -> None:
    item = _find(session, user_id, variant_id)
    if item is None:
        raise NotInCart
    session.delete(item)
    session.commit()


def get_cart(session: Session, user_id: int) -> CartView:
    items = session.scalars(
        select(CartItem)
        .where(CartItem.user_id == user_id)
        .order_by(CartItem.added_at, CartItem.id)
        # ดึง variant → สินค้า → รูป มาครบในรอบเดียว (กัน N+1)
        .options(
            selectinload(CartItem.variant)
            .selectinload(ProductVariant.product)
            .selectinload(Product.images)
        )
    ).all()

    lines = []
    for item in items:
        v, p = item.variant, item.variant.product
        lines.append(
            CartLine(
                variant_id=v.id,
                product_id=p.id,
                product_name=p.name,
                sku=v.sku,
                size=v.size,
                color=v.color,
                image_url=_image_for(v),
                unit_price=p.price,  # ราคาปัจจุบันเสมอ · ล็อกราคาตอนสร้างออเดอร์
                quantity=item.quantity,
                line_total=p.price * item.quantity,
                status=_line_status(item),
            )
        )

    ok_lines = [line for line in lines if line.status == "ok"]
    return CartView(
        lines=lines,
        subtotal=sum((line.line_total for line in ok_lines), Decimal("0.00")),
        item_count=sum(line.quantity for line in lines),
        can_checkout=bool(lines) and len(ok_lines) == len(lines),
    )
