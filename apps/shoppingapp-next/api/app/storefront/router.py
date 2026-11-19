from decimal import Decimal
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, Query, status
from pydantic import BaseModel
from sqlalchemy.orm import Session

from app.db import get_session
from app.models import Product
from app.storefront import service
from app.storefront.service import Availability, Sort

# หน้าร้าน: เปิดให้ทุกคน ไม่ต้องล็อกอิน · ส่งออกเฉพาะข้อมูลที่ลูกค้าควรเห็น (ไม่มีจำนวนสต็อกจริง)
router = APIRouter(prefix="/products", tags=["storefront"])
SessionDep = Annotated[Session, Depends(get_session)]


class PublicImage(BaseModel):
    url: str
    color: str | None


class PublicVariant(BaseModel):
    id: int
    sku: str
    size: str | None
    color: str | None
    availability: Availability


class ProductCard(BaseModel):
    """การ์ดในหน้ารวมสินค้า"""

    id: int
    name: str
    price: Decimal
    cover_url: str | None
    colors: list[str]
    sizes: list[str]
    sold_out: bool


class ProductDetail(BaseModel):
    id: int
    name: str
    description: str | None
    price: Decimal
    details: dict | None
    images: list[PublicImage]
    variants: list[PublicVariant]


def _unique(values) -> list[str]:
    return list(dict.fromkeys(v for v in values if v))  # ตัดซ้ำ แต่คงลำดับเดิม


def _cover(p: Product) -> str | None:
    general = [i for i in p.images if i.color is None]
    first = (general or p.images or [None])[0]
    return first.url if first else None


def _card(p: Product) -> ProductCard:
    return ProductCard(
        id=p.id,
        name=p.name,
        price=p.price,
        cover_url=_cover(p),
        colors=_unique(v.color for v in p.variants),
        sizes=_unique(v.size for v in p.variants),
        sold_out=all(v.stock <= 0 for v in p.variants),
    )


@router.get("")
def list_products(
    session: SessionDep,
    q: Annotated[str | None, Query(max_length=100)] = None,
    sort: Sort = "new",
    limit: Annotated[int, Query(ge=1, le=60)] = 24,
    offset: Annotated[int, Query(ge=0)] = 0,
) -> list[ProductCard]:
    return [_card(p) for p in service.list_products(session, q, sort, limit, offset)]


@router.get("/{product_id}")
def get_product(product_id: int, session: SessionDep) -> ProductDetail:
    try:
        p = service.get_product(session, product_id)
    except service.NotFound:
        raise HTTPException(status.HTTP_404_NOT_FOUND, "ไม่พบสินค้า") from None
    return ProductDetail(
        id=p.id,
        name=p.name,
        description=p.description,
        price=p.price,
        details=p.details,
        images=[PublicImage(url=i.url, color=i.color) for i in p.images],
        variants=[
            PublicVariant(id=v.id, sku=v.sku, size=v.size, color=v.color, availability=service.availability(v.stock))
            for v in p.variants
        ],
    )
