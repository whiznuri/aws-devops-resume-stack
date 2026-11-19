from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """ค่าตั้งทั้งหมดอ่านจาก environment / .env — เปลี่ยนเครื่องไม่ต้องแก้โค้ด"""

    model_config = SettingsConfigDict(env_file=".env", extra="ignore")

    # รันในเครื่อง (ไม่ผ่าน Docker) จะต่อ Postgres ที่ port 5433 ของเครื่อง
    database_url: str = "postgresql+psycopg://shop:shop@localhost:5433/shop"

    # --- JWT (Technical) ---
    # ค่า default ใช้ได้แค่ตอน dev · ขึ้นเครื่องจริงต้องตั้ง JWT_SECRET ใหม่เสมอ
    jwt_secret: str = "dev-only-change-me-0123456789abcdef0123456789"
    jwt_algorithm: str = "HS256"
    access_token_minutes: int = 15

    # --- อายุการล็อกอิน (Business: พี่ดิฟกำหนด 2026-11-19 · ปรับได้เหมือน setpoint) ---
    staff_idle_minutes: int = 30
    staff_absolute_hours: int = 8
    customer_absolute_days: int = 7

    # --- หน้าร้าน (Business: ค่าตั้งต้น ปรับได้) ---
    low_stock_threshold: int = 3  # เหลือไม่เกินเท่านี้ โชว์ "เหลือน้อย" (ไม่บอกตัวเลขจริง)
    cart_max_quantity: int = 10  # จำนวนสูงสุดต่อรายการในตะกร้า
    order_payment_window_minutes: int = 60  # จองของให้นานเท่านี้หลังสั่งซื้อ (พี่ดิฟกำหนด 1 ชม.)

    # --- Omise (test mode) · secret key อยู่ใน .env เท่านั้น ---
    omise_secret_key: str = ""
    omise_api_url: str = "https://api.omise.co"

    # --- L-07 ตัวยกเลิกออเดอร์ที่เกินเวลา ---
    expire_job_enabled: bool = True  # เทสปิดไว้ (conftest)
    expire_job_interval_seconds: int = 60


settings = Settings()
