from datetime import UTC, datetime
from decimal import Decimal
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel, ConfigDict, StringConstraints
from sqlalchemy.orm import Session

from app.auth.deps import Claims, get_claims
from app.db import get_session
from app.orders import service

# ออเดอร์ของลูกค้า: เห็น/สร้างได้เฉพาะของตัวเอง (user_id จาก JWT)
router = APIRouter(prefix="/orders", tags=["orders"])
SessionDep = Annotated[Session, Depends(get_session)]
ClaimsDep = Annotated[Claims, Depends(get_claims)]

Name = Annotated[str, StringConstraints(strip_whitespace=True, min_length=2, max_length=120)]
# เบอร์มือถือ/บ้านไทย: ขึ้นต้น 0 ตามด้วยตัวเลข 8–9 หลัก (ไม่มีขีด)
Phone = Annotated[str, StringConstraints(strip_whitespace=True, pattern=r"^0\d{8,9}$")]
Address = Annotated[str, StringConstraints(strip_whitespace=True, min_length=10, max_length=500)]


class CheckoutIn(BaseModel):
    model_config = ConfigDict(extra="forbid")

    shipping_name: Name
    shipping_phone: Phone
    shipping_address: Address
    note: Annotated[str, StringConstraints(strip_whitespace=True, max_length=300)] | None = None


class OrderItemOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    variant_id: int
    product_name: str
    sku: str
    size: str | None
    color: str | None
    unit_price: Decimal
    quantity: int
    line_total: Decimal


class OrderOut(BaseModel):
    """เจ้าของออเดอร์เห็นข้อมูลจัดส่งของตัวเองเต็ม (มาตรฐาน §8: เจ้าของข้อมูลเห็นเต็ม)"""

    model_config = ConfigDict(from_attributes=True)

    id: int
    status: str
    subtotal: Decimal
    shipping_name: str
    shipping_phone: str
    shipping_address: str
    note: str | None
    expires_at: datetime
    created_at: datetime
    items: list[OrderItemOut]


@router.post("", status_code=status.HTTP_201_CREATED)
def checkout(body: CheckoutIn, claims: ClaimsDep, session: SessionDep) -> OrderOut:
    shipping = service.Shipping(
        name=body.shipping_name, phone=body.shipping_phone, address=body.shipping_address, note=body.note
    )
    try:
        order = service.checkout(session, claims.user_id, shipping, now=datetime.now(UTC))
    except service.EmptyCart:
        raise HTTPException(status.HTTP_409_CONFLICT, "ตะกร้าว่าง") from None
    except service.CannotBuy as exc:
        raise HTTPException(
            status.HTTP_409_CONFLICT,
            {"message": "มีสินค้าบางรายการซื้อไม่ได้ กรุณาแก้ตะกร้า", "problems": exc.problems},
        ) from None
    return OrderOut.model_validate(order)


@router.get("")
def list_orders(claims: ClaimsDep, session: SessionDep) -> list[OrderOut]:
    return [OrderOut.model_validate(o) for o in service.list_orders(session, claims.user_id)]


@router.get("/{order_id}")
def get_order(order_id: int, claims: ClaimsDep, session: SessionDep) -> OrderOut:
    try:
        return OrderOut.model_validate(service.get_order(session, claims.user_id, order_id))
    except service.OrderNotFound:
        raise HTTPException(status.HTTP_404_NOT_FOUND, "ไม่พบคำสั่งซื้อ") from None
