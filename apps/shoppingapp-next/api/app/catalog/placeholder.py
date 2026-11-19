"""รูปจำลองตามสีจริงของสินค้า (ไม่เก็บไฟล์เอง ใช้ URL ของ placehold.co)
เสื้อขาว → พื้นขาว · เสื้อดำ → พื้นดำ — mock ที่ความหมายถูก แม้ยังไม่มีรูปจริง"""

from urllib.parse import quote_plus

# สี → (พื้นหลัง, ตัวหนังสือ) เป็นรหัส hex
SWATCHES: dict[str, tuple[str, str]] = {
    "ขาว": ("F5F5F5", "333333"),
    "ดำ": ("111111", "FFFFFF"),
    "ครีม": ("F3E9D2", "5B4636"),
    "กรม": ("1F2A44", "FFFFFF"),
    "ชมพู": ("F8C8DC", "7A2048"),
    "น้ำตาล": ("8B5E3C", "FFFFFF"),
    "เทา": ("9CA3AF", "111111"),
    "01 Nude": ("D8A48F", "3B2318"),
    "02 Rose": ("C76B7E", "FFFFFF"),
    "03 Coral": ("F88379", "FFFFFF"),
    "04 Red": ("C1121F", "FFFFFF"),
    "21": ("F1D3BC", "5B4636"),
    "23": ("E2B993", "5B4636"),
    "25": ("C99A6E", "FFFFFF"),
}
NEUTRAL = ("E5E7EB", "374151")

# ชื่อสีภาษาอังกฤษไว้พิมพ์บนรูป (ฟอนต์ของ placehold.co ไม่มีภาษาไทย)
COLOR_LABEL = {"ขาว": "White", "ดำ": "Black", "ครีม": "Cream", "กรม": "Navy", "ชมพู": "Pink", "น้ำตาล": "Brown", "เทา": "Gray"}


def placeholder_url(label: str, color: str | None = None, view: str | None = None) -> str:
    bg, fg = SWATCHES.get(color or "", NEUTRAL)
    words = [label, COLOR_LABEL.get(color or "", color or ""), view or ""]
    text = "\\n".join(w for w in words if w)
    return f"https://placehold.co/600x600/{bg}/{fg}/png?text={quote_plus(text)}"
