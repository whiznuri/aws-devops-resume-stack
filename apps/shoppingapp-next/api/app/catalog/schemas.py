from decimal import Decimal
from typing import Annotated

from pydantic import BaseModel, ConfigDict, Field, StringConstraints

Text200 = Annotated[str, StringConstraints(strip_whitespace=True, min_length=1, max_length=200)]
Sku = Annotated[str, StringConstraints(strip_whitespace=True, to_upper=True, min_length=1, max_length=64)]
Short = Annotated[str, StringConstraints(strip_whitespace=True, min_length=1, max_length=40)]
# เงิน: Decimal 2 ตำแหน่ง ห้ามติดลบ · ส่งออกเป็นข้อความ "390.00" กันทศนิยมเพี้ยนแบบ float
Money = Annotated[Decimal, Field(ge=0, max_digits=10, decimal_places=2)]
# รูปเก็บเป็นลิงก์ https เท่านั้น (ไม่รับไฟล์ ไม่รับ javascript: หรือ data:)
ImageUrl = Annotated[str, StringConstraints(strip_whitespace=True, max_length=500, pattern=r"^https://")]


class VariantIn(BaseModel):
    model_config = ConfigDict(extra="forbid")

    sku: Sku
    size: Short | None = None
    color: Short | None = None
    stock: int = Field(default=0, ge=0)  # ใส่ได้ตอนสร้างครั้งแรกเท่านั้น หลังจากนี้ไป L-10


class VariantUpdate(BaseModel):
    """แก้ variant ได้ทุกอย่างยกเว้นสต็อก (สต็อกต้องผ่าน L-10 ที่มีประวัติ)"""

    model_config = ConfigDict(extra="forbid")

    sku: Sku | None = None
    size: Short | None = None
    color: Short | None = None
    is_active: bool | None = None


class ImageIn(BaseModel):
    model_config = ConfigDict(extra="forbid")

    url: ImageUrl
    color: Short | None = None  # ว่าง = รูปรวมของรุ่น
    sort_order: int = Field(default=0, ge=0)


class ProductIn(BaseModel):
    model_config = ConfigDict(extra="forbid")

    name: Text200
    description: str | None = None
    price: Money
    details: dict | None = None
    variants: list[VariantIn] = Field(min_length=1)  # สินค้าต้องมีอย่างน้อย 1 ตัวเลือกถึงจะขายได้
    images: list[ImageIn] = []


class ProductUpdate(BaseModel):
    model_config = ConfigDict(extra="forbid")

    name: Text200 | None = None
    description: str | None = None
    price: Money | None = None
    details: dict | None = None
    is_active: bool | None = None  # False = suspend (ปิดขาย)


class VariantOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    sku: str
    size: str | None
    color: str | None
    stock: int
    is_active: bool


class ImageOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    url: str
    color: str | None
    sort_order: int


class ProductOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str
    description: str | None
    price: Decimal
    details: dict | None
    is_active: bool
    variants: list[VariantOut]
    images: list[ImageOut]
