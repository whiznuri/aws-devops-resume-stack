"""L-07 ยกเลิกออเดอร์ที่เกินเวลาชำระ + คืนสต็อก (ไม่มีคนกด — ตัวจับเวลาเรียกเอง)"""

import logging
import threading
from datetime import UTC, datetime

from sqlalchemy import select
from sqlalchemy.orm import Session, selectinload

from app.config import settings
from app.db import SessionLocal
from app.models import Order, Payment, ProductVariant
from app.payments.omise import OmiseClient, OmiseError
from app.payments.service import apply_charge

log = logging.getLogger("expiry")


def expire_overdue(session: Session, now: datetime, omise: OmiseClient | None, batch: int = 100) -> dict[str, int]:
    """คืน {"expired": n, "paid": n, "skipped": n} ของรอบนี้"""
    result = {"expired": 0, "paid": 0, "skipped": 0}
    orders = session.scalars(
        select(Order)
        .where(Order.status == "pending_payment", Order.expires_at <= now)
        .order_by(Order.id)
        .limit(batch)
        .options(selectinload(Order.items))
        # SKIP LOCKED: แถวที่คนอื่นกำลังล็อกอยู่ (ลูกค้ากำลังกดตรวจสถานะ / API อีกตัว) ข้ามไปก่อน ไม่รอ ไม่ทำซ้ำ
        .with_for_update(skip_locked=True, of=Order)
    ).all()

    for order in orders:
        payment = session.scalar(
            select(Payment).where(Payment.order_id == order.id, Payment.status == "pending").order_by(Payment.id.desc())
        )
        if payment is not None:
            # ก่อนยกเลิก ถาม Omise ก่อน: ลูกค้าอาจสแกนจ่ายนาทีสุดท้ายแต่ยังไม่ได้กดตรวจ
            if omise is None:
                result["skipped"] += 1
                continue
            try:
                charge = omise.get_charge(payment.charge_id)
            except OmiseError:
                result["skipped"] += 1  # ยังไม่แน่ใจ = ยังไม่ยกเลิก รอรอบหน้า
                continue
            if apply_charge(order, payment, charge, now):
                result["paid"] += 1
                continue
            payment.status = "expired"

        _restore_stock(session, order)
        order.status = "expired"
        result["expired"] += 1

    session.commit()
    return result


def _restore_stock(session: Session, order: Order) -> None:
    # ล็อก variant เรียงตาม id เหมือนตอน checkout (L-03) — ทุกคนล็อกลำดับเดียวกัน ไม่เกิด deadlock
    ids = sorted({item.variant_id for item in order.items})
    variants = {
        v.id: v
        for v in session.scalars(
            select(ProductVariant).where(ProductVariant.id.in_(ids)).order_by(ProductVariant.id).with_for_update()
        )
    }
    for item in order.items:
        variants[item.variant_id].stock += item.quantity


def _omise_or_none() -> OmiseClient | None:
    if not settings.omise_secret_key:
        return None
    return OmiseClient(settings.omise_secret_key, settings.omise_api_url)


def run_once() -> dict[str, int]:
    with SessionLocal() as session:
        return expire_overdue(session, datetime.now(UTC), _omise_or_none())


def start_background_job(stop: threading.Event) -> threading.Thread:
    """ตัวจับเวลา: ทำงานทุก N วินาทีจนกว่า API จะปิด (stop ถูก set)"""

    def loop() -> None:
        while not stop.wait(settings.expire_job_interval_seconds):
            try:
                result = run_once()
                if result["expired"] or result["paid"]:
                    log.info("expiry: %s", result)
            except Exception:  # รอบนี้พัง (เช่น DB รีสตาร์ท) ไม่ให้ตัวจับเวลาตาย ลองใหม่รอบหน้า
                log.exception("expiry job failed")

    thread = threading.Thread(target=loop, name="order-expiry", daemon=True)
    thread.start()
    return thread
