import threading
from contextlib import asynccontextmanager

from fastapi import FastAPI

from app.auth.router import router as auth_router
from app.cart.router import router as cart_router
from app.catalog.router import router as catalog_router
from app.config import settings
from app.orders.expiry import start_background_job
from app.orders.router import router as orders_router
from app.payments.router import router as payments_router
from app.routers import admin_users, health
from app.storefront.router import router as storefront_router


@asynccontextmanager
async def lifespan(_: FastAPI):
    # เปิด API → เริ่มตัวจับเวลา L-07 · ปิด API → สั่งหยุด
    stop = threading.Event()
    if settings.expire_job_enabled:
        start_background_job(stop)
    yield
    stop.set()


app = FastAPI(title="Shopping App API", lifespan=lifespan)
app.include_router(health.router)
app.include_router(auth_router)
app.include_router(admin_users.router)
app.include_router(catalog_router)
app.include_router(storefront_router)
app.include_router(cart_router)
app.include_router(orders_router)
app.include_router(payments_router)
