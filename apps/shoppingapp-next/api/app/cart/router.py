from dataclasses import asdict
from decimal import Decimal
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel, ConfigDict, Field
from sqlalchemy.orm import Session

from app.auth.deps import Claims, get_claims
from app.cart import service
from app.cart.service import LineStatus
from app.config import settings
from app.db import get_session

# ตะกร้าของใครของมัน: user_id มาจาก JWT เท่านั้น ไม่รับจาก request (กันแก้ตะกร้าคนอื่น)
router = APIRouter(prefix="/cart", tags=["cart"])
SessionDep = Annotated[Session, Depends(get_session)]
ClaimsDep = Annotated[Claims, Depends(get_claims)]
Quantity = Annotated[int, Field(ge=1, le=settings.cart_max_quantity)]


class AddItem(BaseModel):
    model_config = ConfigDict(extra="forbid")

    variant_id: int
    quantity: Quantity = 1


class SetQuantity(BaseModel):
    model_config = ConfigDict(extra="forbid")

    quantity: Quantity


class CartLineOut(BaseModel):
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


class CartOut(BaseModel):
    lines: list[CartLineOut]
    subtotal: Decimal
    item_count: int
    can_checkout: bool


def _view(session: Session, user_id: int) -> CartOut:
    cart = service.get_cart(session, user_id)
    return CartOut(
        lines=[CartLineOut(**asdict(line)) for line in cart.lines],
        subtotal=cart.subtotal,
        item_count=cart.item_count,
        can_checkout=cart.can_checkout,
    )


def _conflict(exc: service.CartRuleError) -> HTTPException:
    return HTTPException(status.HTTP_409_CONFLICT, str(exc))


@router.get("")
def get_cart(claims: ClaimsDep, session: SessionDep) -> CartOut:
    return _view(session, claims.user_id)


@router.post("/items", status_code=status.HTTP_201_CREATED)
def add_item(body: AddItem, claims: ClaimsDep, session: SessionDep) -> CartOut:
    try:
        service.add_item(session, claims.user_id, body.variant_id, body.quantity)
    except service.CartRuleError as exc:
        raise _conflict(exc) from None
    return _view(session, claims.user_id)


@router.patch("/items/{variant_id}")
def set_quantity(variant_id: int, body: SetQuantity, claims: ClaimsDep, session: SessionDep) -> CartOut:
    try:
        service.set_quantity(session, claims.user_id, variant_id, body.quantity)
    except service.NotInCart:
        raise HTTPException(status.HTTP_404_NOT_FOUND, "ไม่มีรายการนี้ในตะกร้า") from None
    except service.CartRuleError as exc:
        raise _conflict(exc) from None
    return _view(session, claims.user_id)


@router.delete("/items/{variant_id}")
def remove_item(variant_id: int, claims: ClaimsDep, session: SessionDep) -> CartOut:
    try:
        service.remove_item(session, claims.user_id, variant_id)
    except service.NotInCart:
        raise HTTPException(status.HTTP_404_NOT_FOUND, "ไม่มีรายการนี้ในตะกร้า") from None
    return _view(session, claims.user_id)
