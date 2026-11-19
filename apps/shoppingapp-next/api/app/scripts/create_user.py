"""สร้างบัญชี staff / admin คนแรก (สมัครหน้าเว็บได้แค่ customer)

ใช้:  docker compose exec api python -m app.scripts.create_user admin@example.com admin
"""

import argparse
from getpass import getpass

from app.auth.policy import ROLES
from app.auth.service import EmailTaken, create_user
from app.db import SessionLocal


def main() -> None:
    parser = argparse.ArgumentParser(description="สร้างผู้ใช้พร้อม role")
    parser.add_argument("email")
    parser.add_argument("role", choices=ROLES)
    args = parser.parse_args()

    password = getpass("รหัสผ่าน (อย่างน้อย 8 ตัว): ")  # ไม่รับทาง argument กันรหัสค้างใน history
    if len(password) < 8:
        raise SystemExit("รหัสผ่านสั้นเกินไป")

    with SessionLocal() as session:
        try:
            user = create_user(session, args.email, password, role=args.role)
        except EmailTaken:
            raise SystemExit(f"{args.email} มีอยู่แล้ว") from None
    print(f"สร้างแล้ว: id={user.id} {user.email} ({user.role})")


if __name__ == "__main__":
    main()
