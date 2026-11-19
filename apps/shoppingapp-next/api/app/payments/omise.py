"""คุยกับ Omise (ระบบภายนอก) — แยกไว้ไฟล์เดียว เทสสลับเป็นตัวปลอมได้ (test double #19)"""

from dataclasses import dataclass

import httpx

from app.config import settings


@dataclass
class Charge:
    id: str
    status: str  # pending / successful / failed / expired
    amount_satang: int
    currency: str
    order_id: str | None
    qr_image_url: str | None


class OmiseError(Exception):
    """ติดต่อ Omise ไม่ได้ / Omise ปฏิเสธ — router แปลงเป็น 502"""


class OmiseClient:
    def __init__(self, secret_key: str, api_url: str):
        # Omise ใช้ secret key เป็น username ของ HTTP Basic Auth (password ว่าง)
        self._http = httpx.Client(base_url=api_url, auth=(secret_key, ""), timeout=15)

    def create_promptpay_charge(self, amount_satang: int, order_id: int) -> Charge:
        return self._call(
            "POST",
            "/charges",
            json={
                "amount": amount_satang,
                "currency": "thb",
                "source": {"type": "promptpay"},
                "metadata": {"order_id": str(order_id)},  # ไว้เทียบตอนตรวจ กันเอา charge ของออเดอร์อื่นมาสวม
            },
        )

    def get_charge(self, charge_id: str) -> Charge:
        return self._call("GET", f"/charges/{charge_id}")

    def _call(self, method: str, path: str, **kwargs) -> Charge:
        try:
            res = self._http.request(method, path, **kwargs)
        except httpx.HTTPError as exc:
            raise OmiseError("ติดต่อ Omise ไม่ได้") from exc
        body = res.json()
        if res.status_code >= 400 or body.get("object") == "error":
            raise OmiseError(body.get("message", "Omise ปฏิเสธคำขอ"))
        source = body.get("source") or {}
        image = (source.get("scannable_code") or {}).get("image") or {}
        return Charge(
            id=body["id"],
            status=body["status"],
            amount_satang=body["amount"],
            currency=body["currency"].upper(),
            order_id=(body.get("metadata") or {}).get("order_id"),
            qr_image_url=image.get("download_uri"),
        )


def get_omise() -> OmiseClient:
    """ใช้ผ่าน Depends — เทสแทนที่ด้วย FakeOmise ได้ด้วย dependency_overrides"""
    if not settings.omise_secret_key:
        raise OmiseError("ยังไม่ได้ตั้ง OMISE_SECRET_KEY")
    return OmiseClient(settings.omise_secret_key, settings.omise_api_url)
