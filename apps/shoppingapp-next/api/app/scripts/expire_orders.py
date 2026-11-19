"""รัน L-07 รอบเดียวด้วยมือ (ปกติ API รันเองทุก 60 วิ)

ใช้:  docker compose exec api python -m app.scripts.expire_orders
"""

from app.orders.expiry import run_once

if __name__ == "__main__":
    print(run_once())
