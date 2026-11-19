"""Data masking ตามมาตรฐานทีม (PM พี่ภูมิ 2026-11-19)
คนที่ไม่ใช่เจ้าของข้อมูล เห็นได้แค่บางส่วน · mask ที่ API ก่อนข้อมูลออกไป ไม่ใช่ไปซ่อนที่หน้าเว็บ"""


def mask_part(text: str) -> str:
    """เก็บตัวแรก + ตัวท้าย ตรงกลางเป็น * ยาวเท่าเดิม · ยาว 1–2 ตัว → ตัวแรก + *"""
    if len(text) <= 2:
        return text[:1] + "*"
    return text[0] + "*" * (len(text) - 2) + text[-1]


def mask_email(email: str) -> str:
    """admin@example.com → a***n@e*****e.com (เก็บนามสกุลโดเมนท้ายสุดไว้)"""
    local, _, domain = email.partition("@")
    name, dot, tld = domain.rpartition(".")
    masked_domain = f"{mask_part(name)}{dot}{tld}" if dot else mask_part(domain)
    return f"{mask_part(local)}@{masked_domain}"
