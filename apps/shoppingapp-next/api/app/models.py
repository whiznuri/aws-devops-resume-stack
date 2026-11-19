from datetime import datetime
from decimal import Decimal

from sqlalchemy import CheckConstraint, ForeignKey, Numeric, String, Text, UniqueConstraint, func
from sqlalchemy.dialects.postgresql import JSONB
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db import Base


class User(Base):
    __tablename__ = "users"
    __table_args__ = (
        CheckConstraint("role IN ('customer', 'staff', 'admin')", name="ck_users_role"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)
    email: Mapped[str] = mapped_column(String(255), unique=True, index=True)
    password_hash: Mapped[str] = mapped_column(String(255))
    role: Mapped[str] = mapped_column(String(20), default="customer")
    is_active: Mapped[bool] = mapped_column(default=True)
    created_at: Mapped[datetime] = mapped_column(server_default=func.now())


class RefreshToken(Base):
    """บัตรต่ออายุ 1 ใบ · เก็บแค่ hash ของบัตร ถ้า DB หลุด คนร้ายก็เอาไปใช้ไม่ได้"""

    __tablename__ = "refresh_tokens"

    id: Mapped[int] = mapped_column(primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id", ondelete="CASCADE"), index=True)
    token_hash: Mapped[str] = mapped_column(String(64), unique=True)
    created_at: Mapped[datetime] = mapped_column(server_default=func.now())
    last_used_at: Mapped[datetime]  # ใช้จับ idle timeout
    session_expires_at: Mapped[datetime]  # เพดานรวม (absolute) ส่งต่อให้บัตรใบใหม่ทุกครั้ง ไม่ขยาย
    revoked_at: Mapped[datetime | None]


class Product(Base):
    """สินค้า 1 รุ่น · ลบ = ปิดขาย (is_active) ไม่ลบจริง เพราะออเดอร์เก่ายังอ้างถึง"""

    __tablename__ = "products"
    __table_args__ = (CheckConstraint("price >= 0", name="ck_products_price_not_negative"),)

    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(200), index=True)
    description: Mapped[str | None] = mapped_column(Text)
    price: Mapped[Decimal] = mapped_column(Numeric(10, 2))  # เงินห้ามใช้ float (0.1 + 0.2 ≠ 0.3)

    details: Mapped[dict | None] = mapped_column(JSONB)  # ข้อมูลเฉพาะหมวด เช่น วัสดุ, ปริมาณ ml
    is_active: Mapped[bool] = mapped_column(default=True)
    created_at: Mapped[datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(server_default=func.now(), onupdate=func.now())

    images: Mapped[list["ProductImage"]] = relationship(
        # รูปรวม (color ว่าง) ขึ้นก่อน แล้วเรียงตามสี + ลำดับ
        back_populates="product",
        order_by=lambda: [ProductImage.color.nulls_first(), ProductImage.sort_order, ProductImage.id],
    )
    variants: Mapped[list["ProductVariant"]] = relationship(
        back_populates="product", order_by="ProductVariant.id"
    )


class ProductVariant(Base):
    """1 ตัวเลือกที่ขายจริง (เช่น เสื้อขาว M) · สต็อกนับที่ระดับนี้"""

    __tablename__ = "product_variants"
    __table_args__ = (CheckConstraint("stock >= 0", name="ck_variants_stock_not_negative"),)

    id: Mapped[int] = mapped_column(primary_key=True)
    product_id: Mapped[int] = mapped_column(ForeignKey("products.id"), index=True)
    sku: Mapped[str] = mapped_column(String(64), unique=True)
    size: Mapped[str | None] = mapped_column(String(20))
    color: Mapped[str | None] = mapped_column(String(40))
    stock: Mapped[int] = mapped_column(default=0)
    is_active: Mapped[bool] = mapped_column(default=True)

    product: Mapped[Product] = relationship(back_populates="variants")


class ProductImage(Base):
    """รูปสินค้าเก็บเป็น URL (ไม่เก็บไฟล์เอง) · color ว่าง = รูปรวมของรุ่น · มีค่า = รูปของสีนั้น
    ผูกกับสีไม่ผูกกับไซซ์ เพราะสีเปลี่ยนหน้าตาสินค้า แต่ไซซ์ดูจากรูปไม่ออก"""

    __tablename__ = "product_images"

    id: Mapped[int] = mapped_column(primary_key=True)
    product_id: Mapped[int] = mapped_column(ForeignKey("products.id"), index=True)
    color: Mapped[str | None] = mapped_column(String(40))
    url: Mapped[str] = mapped_column(String(500))
    sort_order: Mapped[int] = mapped_column(default=0)

    product: Mapped[Product] = relationship(back_populates="images")


class CartItem(Base):
    """ตะกร้า 1 แถว = user คนหนึ่ง × variant หนึ่ง · ไม่จองสต็อก (ตัดตอน checkout L-03)"""

    __tablename__ = "cart_items"
    __table_args__ = (
        UniqueConstraint("user_id", "variant_id", name="uq_cart_user_variant"),  # ใส่ซ้ำ = เพิ่มจำนวน ไม่ใช่เพิ่มแถว
        CheckConstraint("quantity > 0", name="ck_cart_quantity_positive"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id", ondelete="CASCADE"), index=True)
    variant_id: Mapped[int] = mapped_column(ForeignKey("product_variants.id"))
    quantity: Mapped[int]
    added_at: Mapped[datetime] = mapped_column(server_default=func.now())

    variant: Mapped[ProductVariant] = relationship()


ORDER_STATUSES = ("pending_payment", "awaiting_verification", "paid", "cancelled", "expired")


class Order(Base):
    """ออเดอร์ 1 ใบ · สถานะเดินแบบ ticket: รอชำระ → รอตรวจสลิป → ชำระแล้ว / ยกเลิก / หมดเวลา"""

    __tablename__ = "orders"
    __table_args__ = (
        CheckConstraint(f"status IN {ORDER_STATUSES}", name="ck_orders_status"),
        CheckConstraint("subtotal >= 0", name="ck_orders_subtotal_not_negative"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), index=True)
    status: Mapped[str] = mapped_column(String(30), default="pending_payment", index=True)
    subtotal: Mapped[Decimal] = mapped_column(Numeric(12, 2))
    shipping_name: Mapped[str] = mapped_column(String(120))
    shipping_phone: Mapped[str] = mapped_column(String(20))
    shipping_address: Mapped[str] = mapped_column(Text)
    note: Mapped[str | None] = mapped_column(Text)
    expires_at: Mapped[datetime]  # จองของถึงเวลานี้ ไม่จ่าย → L-07 ยกเลิก + คืนสต็อก
    paid_at: Mapped[datetime | None]
    created_at: Mapped[datetime] = mapped_column(server_default=func.now())

    items: Mapped[list["OrderItem"]] = relationship(back_populates="order", order_by="OrderItem.id")


class OrderItem(Base):
    """รายการในออเดอร์ · เก็บชื่อ/SKU/สี/ไซซ์/ราคา ณ ตอนสั่ง (snapshot) ราคาเปลี่ยนทีหลังไม่กระทบ"""

    __tablename__ = "order_items"
    __table_args__ = (CheckConstraint("quantity > 0", name="ck_order_items_quantity_positive"),)

    id: Mapped[int] = mapped_column(primary_key=True)
    order_id: Mapped[int] = mapped_column(ForeignKey("orders.id"), index=True)
    variant_id: Mapped[int] = mapped_column(ForeignKey("product_variants.id"))
    product_name: Mapped[str] = mapped_column(String(200))
    sku: Mapped[str] = mapped_column(String(64))
    size: Mapped[str | None] = mapped_column(String(20))
    color: Mapped[str | None] = mapped_column(String(40))
    unit_price: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    quantity: Mapped[int]
    line_total: Mapped[Decimal] = mapped_column(Numeric(12, 2))

    order: Mapped[Order] = relationship(back_populates="items")


class Payment(Base):
    """การจ่ายเงิน 1 ครั้ง (1 charge ของ Omise) · สถานะยึดตามที่ server ถาม Omise เอง ไม่เชื่อ browser"""

    __tablename__ = "payments"

    id: Mapped[int] = mapped_column(primary_key=True)
    order_id: Mapped[int] = mapped_column(ForeignKey("orders.id"), index=True)
    provider: Mapped[str] = mapped_column(String(20), default="omise")
    charge_id: Mapped[str] = mapped_column(String(64), unique=True)
    method: Mapped[str] = mapped_column(String(20), default="promptpay")
    amount: Mapped[Decimal] = mapped_column(Numeric(12, 2))
    currency: Mapped[str] = mapped_column(String(3), default="THB")
    status: Mapped[str] = mapped_column(String(20), default="pending")  # pending / successful / failed / expired
    qr_image_url: Mapped[str | None] = mapped_column(String(500))
    created_at: Mapped[datetime] = mapped_column(server_default=func.now())
    paid_at: Mapped[datetime | None]
