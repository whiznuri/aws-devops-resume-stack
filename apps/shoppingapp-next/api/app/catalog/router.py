from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.orm import Session

from app.auth.deps import require_roles
from app.catalog import service
from app.catalog.schemas import ImageIn, ProductIn, ProductOut, ProductUpdate, VariantIn, VariantUpdate
from app.db import get_session

# Backoffice สินค้า (L-09): staff ขึ้นไป · ลบสินค้า/variant ไม่ได้ (ปิดขายด้วย is_active=false) · ลบได้แค่รูป
router = APIRouter(prefix="/admin", tags=["backoffice: products"], dependencies=[Depends(require_roles("staff"))])
SessionDep = Annotated[Session, Depends(get_session)]


def _not_found() -> HTTPException:
    return HTTPException(status.HTTP_404_NOT_FOUND, "ไม่พบสินค้า")


def _duplicate_sku() -> HTTPException:
    return HTTPException(status.HTTP_409_CONFLICT, "SKU นี้มีอยู่แล้ว")


@router.get("/products")
def list_products(
    session: SessionDep,
    q: Annotated[str | None, Query(max_length=100)] = None,
    limit: Annotated[int, Query(ge=1, le=100)] = 50,
    offset: Annotated[int, Query(ge=0)] = 0,
) -> list[ProductOut]:
    return [ProductOut.model_validate(p) for p in service.list_products(session, q, limit, offset)]


@router.get("/products/{product_id}")
def get_product(product_id: int, session: SessionDep) -> ProductOut:
    try:
        return ProductOut.model_validate(service.get_product(session, product_id))
    except service.NotFound:
        raise _not_found() from None


@router.post("/products", status_code=status.HTTP_201_CREATED)
def create_product(body: ProductIn, session: SessionDep) -> ProductOut:
    try:
        return ProductOut.model_validate(service.create_product(session, body))
    except service.DuplicateSku:
        raise _duplicate_sku() from None


@router.patch("/products/{product_id}")
def update_product(product_id: int, body: ProductUpdate, session: SessionDep) -> ProductOut:
    try:
        return ProductOut.model_validate(service.update_product(session, product_id, body))
    except service.NotFound:
        raise _not_found() from None


@router.post("/products/{product_id}/variants", status_code=status.HTTP_201_CREATED)
def add_variant(product_id: int, body: VariantIn, session: SessionDep) -> ProductOut:
    try:
        return ProductOut.model_validate(service.add_variant(session, product_id, body))
    except service.NotFound:
        raise _not_found() from None
    except service.DuplicateSku:
        raise _duplicate_sku() from None


@router.patch("/variants/{variant_id}")
def update_variant(variant_id: int, body: VariantUpdate, session: SessionDep) -> ProductOut:
    try:
        return ProductOut.model_validate(service.update_variant(session, variant_id, body))
    except service.NotFound:
        raise _not_found() from None
    except service.DuplicateSku:
        raise _duplicate_sku() from None


@router.post("/products/{product_id}/images", status_code=status.HTTP_201_CREATED)
def add_image(product_id: int, body: ImageIn, session: SessionDep) -> ProductOut:
    try:
        return ProductOut.model_validate(service.add_image(session, product_id, body))
    except service.NotFound:
        raise _not_found() from None


@router.delete("/images/{image_id}")
def delete_image(image_id: int, session: SessionDep) -> ProductOut:
    try:
        return ProductOut.model_validate(service.delete_image(session, image_id))
    except service.NotFound:
        raise HTTPException(status.HTTP_404_NOT_FOUND, "ไม่พบรูป") from None
