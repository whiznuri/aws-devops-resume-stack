"""ชำระเงิน (L-04) — สร้าง QR PromptPay / ตรวจสถานะกับ Omise · ไม่เชื่อ browser"""

from datetime import datetime
from decimal import Decimal

from sqlalchemy import select
from sqlalchemy.orm import Session

from app.models import Order, Payment
from app.payments.omise import Charge, OmiseClient


class PaymentNotAllowed(Exception):
    """ออเดอร์นี้จ่ายไม่ได้ (จ่ายแล้ว / ยกเลิก / หมดเวลา) — router แปลงเป็น 409"""


class OrderNotFound(Exception):
    pass


def to_satang(amount: Decimal) -> int:
    return int(amount * 100)  # Omise นับเป็นสตางค์ (390.50 บาท = 39050)


def _own_order(session: Session, user_id: int, order_id: int, lock: bool = False) -> Order:
    stmt = select(Order).where(Order.id == order_id)
    if lock:
        stmt = stmt.with_for_update()  # กันตรวจสถานะซ้อนกัน 2 ครั้งแล้วบันทึกซ้ำ
    order = session.scalar(stmt)
    if order is None or order.user_id != user_id:
        raise OrderNotFound
    return order


def _latest(session: Session, order_id: int) -> Payment | None:
    return session.scalar(select(Payment).where(Payment.order_id == order_id).order_by(Payment.id.desc()))


def start_payment(session: Session, omise: OmiseClient, user_id: int, order_id: int, now: datetime) -> Payment:
    order = _own_order(session, user_id, order_id)
    if order.status != "pending_payment":
        raise PaymentNotAllowed("ออเดอร์นี้ไม่ได้อยู่ในสถานะรอชำระ")
    if now >= order.expires_at:
        raise PaymentNotAllowed("หมดเวลาชำระแล้ว")

    current = _latest(session, order_id)
    if current and current.status == "pending":
        return current  # มี QR ที่ยังใช้ได้อยู่แล้ว → ให้ใบเดิม ไม่สร้างซ้ำ (กันเก็บเงินซ้ำ)

    charge = omise.create_promptpay_charge(to_satang(order.subtotal), order.id)
    payment = Payment(
        order_id=order.id,
        charge_id=charge.id,
        amount=order.subtotal,
        currency=charge.currency,
        status=charge.status,
        qr_image_url=charge.qr_image_url,
    )
    session.add(payment)
    session.commit()
    return payment


def check_payment(session: Session, omise: OmiseClient, user_id: int, order_id: int, now: datetime) -> Order:
    order = _own_order(session, user_id, order_id, lock=True)
    payment = _latest(session, order_id)
    if payment is None or order.status != "pending_payment":
        session.rollback()
        return order  # ไม่มีอะไรต้องตรวจ (ยังไม่กดจ่าย / จ่ายแล้ว)

    charge = omise.get_charge(payment.charge_id)  # ถาม Omise ตรง ไม่เชื่อสิ่งที่ browser บอก
    apply_charge(order, payment, charge, now)
    session.commit()
    return order


def apply_charge(order: Order, payment: Payment, charge: Charge, now: datetime) -> bool:
    """เอาผลจาก Omise มาบันทึก · ใช้ร่วมกันทั้งปุ่มตรวจสถานะ (L-04) และตัวยกเลิกอัตโนมัติ (L-07)
    คืน True ถ้าจ่ายครบถูกต้อง"""
    paid_correctly = (
        charge.status == "successful"
        and charge.amount_satang == to_satang(order.subtotal)  # ยอดต้องตรงเป๊ะ
        and charge.currency == "THB"
        and charge.order_id == str(order.id)  # charge ต้องเป็นของออเดอร์นี้จริง
    )
    if paid_correctly:
        payment.status = "successful"
        payment.paid_at = now
        order.status = "paid"
        order.paid_at = now
    elif charge.status in ("failed", "expired"):
        payment.status = charge.status  # QR ใบนี้ใช้ไม่ได้แล้ว กดจ่ายใหม่จะได้ใบใหม่
    return paid_correctly


def latest_payment(session: Session, user_id: int, order_id: int) -> Payment | None:
    _own_order(session, user_id, order_id)
    return _latest(session, order_id)
