from datetime import UTC, datetime
from decimal import Decimal
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel, ConfigDict
from sqlalchemy.orm import Session

from app.auth.deps import Claims, get_claims
from app.db import get_session
from app.payments import service
from app.payments.omise import OmiseClient, OmiseError, get_omise

router = APIRouter(prefix="/orders/{order_id}/payment", tags=["payments"])
SessionDep = Annotated[Session, Depends(get_session)]
ClaimsDep = Annotated[Claims, Depends(get_claims)]
OmiseDep = Annotated[OmiseClient, Depends(get_omise)]


class PaymentOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    method: str
    amount: Decimal
    status: str
    qr_image_url: str | None
    created_at: datetime
    paid_at: datetime | None


class PaymentStatusOut(BaseModel):
    order_status: str
    payment: PaymentOut | None


def _not_found() -> HTTPException:
    return HTTPException(status.HTTP_404_NOT_FOUND, "ไม่พบคำสั่งซื้อ")


@router.get("")
def get_payment(order_id: int, claims: ClaimsDep, session: SessionDep) -> PaymentOut | None:
    try:
        payment = service.latest_payment(session, claims.user_id, order_id)
    except service.OrderNotFound:
        raise _not_found() from None
    return PaymentOut.model_validate(payment) if payment else None


@router.post("", status_code=status.HTTP_201_CREATED)
def start_payment(order_id: int, claims: ClaimsDep, session: SessionDep, omise: OmiseDep) -> PaymentOut:
    try:
        payment = service.start_payment(session, omise, claims.user_id, order_id, now=datetime.now(UTC))
    except service.OrderNotFound:
        raise _not_found() from None
    except service.PaymentNotAllowed as exc:
        raise HTTPException(status.HTTP_409_CONFLICT, str(exc)) from None
    except OmiseError as exc:
        raise HTTPException(status.HTTP_502_BAD_GATEWAY, f"ระบบชำระเงินขัดข้อง: {exc}") from None
    return PaymentOut.model_validate(payment)


@router.post("/check")
def check_payment(order_id: int, claims: ClaimsDep, session: SessionDep, omise: OmiseDep) -> PaymentStatusOut:
    try:
        order = service.check_payment(session, omise, claims.user_id, order_id, now=datetime.now(UTC))
        payment = service.latest_payment(session, claims.user_id, order_id)
    except service.OrderNotFound:
        raise _not_found() from None
    except OmiseError as exc:
        raise HTTPException(status.HTTP_502_BAD_GATEWAY, f"ระบบชำระเงินขัดข้อง: {exc}") from None
    return PaymentStatusOut(order_status=order.status, payment=PaymentOut.model_validate(payment) if payment else None)
