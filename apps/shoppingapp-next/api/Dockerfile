FROM python:3.13-slim
# ตัว uv (ไฟล์เดียว) copy มาจาก image ทางการของ uv — ล็อกเวอร์ชันให้ตรงกับในเครื่อง
COPY --from=ghcr.io/astral-sh/uv:0.12.3 /uv /uvx /bin/

WORKDIR /app
# venv อยู่นอก /app — ตอน dev ที่ bind mount โค้ดทับ /app จะไม่ทับ library ที่ลงไว้
ENV UV_PROJECT_ENVIRONMENT=/opt/venv \
    UV_COMPILE_BYTECODE=1 \
    UV_LINK_MODE=copy \
    PATH="/opt/venv/bin:$PATH"

# ลง library ก่อน copy โค้ด → แก้โค้ดแล้ว build ใหม่ไม่ต้องลง library ใหม่ (Docker layer cache)
COPY pyproject.toml uv.lock .python-version ./
RUN uv sync --frozen --no-install-project

COPY . .
EXPOSE 8000
# production: อัปเดตโครงตาราง (migration) ให้ล่าสุดก่อน แล้วค่อยเปิด API · dev ใน compose ใช้ fastapi dev แทน
CMD ["sh", "-c", "alembic upgrade head && exec fastapi run app/main.py --host 0.0.0.0 --port 8000"]
