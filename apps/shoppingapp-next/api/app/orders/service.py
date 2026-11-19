"""Checkout (L-03) — ตัดสต็อก + สร้างออเดอร์ ใน transaction เดียว"""

from dataclasses import dataclass
from datetime import datetime, timedelta
from decimal import Decimal

from sqlalchemy import select
from sqlalchemy.orm import Session, selectinload

from app.config import settings
from app.models import CartItem, Order, OrderItem, ProductVariant


@dataclass
class Shipping:
    name: str
    phone: str
    address: str
    note: str | None = None


class EmptyCart(Exception):
    """ตะกร้าว่าง (รวมถึงกรณีกดสั่งซื้อซ้ำ ครั้งแรกล้างตะกร้าไปแล้ว)"""


class CannotBuy(Exception):
    """มีบางแถวซื้อไม่ได้ — เก็บ SKU ที่มีปัญหาไว้บอกลูกค้า"""

    def __init__(self, problems: dict[str, str]):
        super().__init__("มีสินค้าบางรายการซื้อไม่ได้")
        self.problems = problems  # {"TEE-WHT-M": "มีไม่พอ", ...}


class OrderNotFound(Exception):
    pass


def checkout(session: Session, user_id: int, shipping: Shipping, now: datetime) -> Order:
    # 1) ล็อกแถวตะกร้าของคนนี้ก่อน: ถ้ากดซ้ำ คำขอที่สองต้องรอคิว แล้วจะเจอตะกร้าว่าง
    cart = session.scalars(
        select(CartItem).where(CartItem.user_id == user_id).order_by(CartItem.id).with_for_update()
    ).all()
    if not cart:
        raise EmptyCart

    # 2) ล็อกแถวสินค้าที่จะตัด เรียงตาม id เสมอ — ทุกคนล็อกลำดับเดียวกัน จึงไม่เกิด deadlock
    #    (A ล็อกเสื้อแล้วรอตุ๊กตา ขณะที่ B ล็อกตุ๊กตาแล้วรอเสื้อ = ค้างกันทั้งคู่)
    variant_ids = sorted(item.variant_id for item in cart)
    variants = {
        v.id: v
        for v in session.scalars(
            select(ProductVariant)
            .where(ProductVariant.id.in_(variant_ids))
            .order_by(ProductVariant.id)
            .options(selectinload(ProductVariant.product))
            .with_for_update(of=ProductVariant)
        )
    }

    # 3) เช็คทุกแถวก่อนแตะอะไร — ผิดแถวเดียว ไม่สร้างออเดอร์เลยทั้งก้อน
    problems: dict[str, str] = {}
    for item in cart:
        v = variants[item.variant_id]
        if not v.is_active or not v.product.is_active:
            problems[v.sku] = "ไม่มีจำหน่ายแล้ว"
        elif v.stock <= 0:
            problems[v.sku] = "สินค้าหมด"
        elif v.stock < item.quantity:
            problems[v.sku] = "มีไม่พอ"
    if problems:
        session.rollback()  # ปลดล็อกทันที ไม่มีอะไรเปลี่ยน
        raise CannotBuy(problems)

    # 4) ตัดสต็อก + สร้างออเดอร์ (ล็อกราคา/ชื่อ ณ ตอนนี้) + ล้างตะกร้า
    order = Order(
        user_id=user_id,
        status="pending_payment",
        subtotal=Decimal("0.00"),
        shipping_name=shipping.name,
        shipping_phone=shipping.phone,
        shipping_address=shipping.address,
        note=shipping.note,
        expires_at=now + timedelta(minutes=settings.order_payment_window_minutes),
    )
    for item in cart:
        v = variants[item.variant_id]
        v.stock -= item.quantity
        line_total = v.product.price * item.quantity
        order.items.append(
            OrderItem(
                variant_id=v.id,
                product_name=v.product.name,
                sku=v.sku,
                size=v.size,
                color=v.color,
                unit_price=v.product.price,
                quantity=item.quantity,
                line_total=line_total,
            )
        )
        order.subtotal += line_total
        session.delete(item)

    session.add(order)
    session.commit()  # ทั้งหมดข้างบนสำเร็จพร้อมกัน — พังตรงไหน DB ย้อนกลับทั้งก้อนเอง
    return order


def list_orders(session: Session, user_id: int) -> list[Order]:
    return list(
        session.scalars(
            select(Order)
            .where(Order.user_id == user_id)
            .order_by(Order.id.desc())
            .options(selectinload(Order.items))
        )
    )


def get_order(session: Session, user_id: int, order_id: int) -> Order:
    order = session.get(Order, order_id, options=[selectinload(Order.items)])
    # ออเดอร์คนอื่น = ตอบเหมือนไม่มี (404) ไม่บอกด้วยซ้ำว่ามีเลขนี้อยู่
    if order is None or order.user_id != user_id:
        raise OrderNotFound
    return order
