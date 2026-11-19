"""ของปลอมที่เทสหลายไฟล์ใช้ร่วมกัน (test double #19)"""

from app.payments.omise import Charge, OmiseError


class FakeOmise:
    """ทำตัวเหมือน OmiseClient · จำ charge ไว้ในตัว · ตั้งให้ 'จ่ายแล้ว' ได้เอง"""

    def __init__(self):
        self.charges: dict[str, Charge] = {}
        self.created = 0
        self.down = False  # True = จำลอง Omise ล่ม

    def create_promptpay_charge(self, amount_satang, order_id):
        self.created += 1
        charge = Charge(f"chrg_test_{self.created}", "pending", amount_satang, "THB", str(order_id), f"https://qr.example/{self.created}.png")
        self.charges[charge.id] = charge
        return charge

    def get_charge(self, charge_id):
        if self.down:
            raise OmiseError("จำลอง: ติดต่อ Omise ไม่ได้")
        return self.charges[charge_id]

    def pay(self, charge_id, **override):
        charge = self.charges[charge_id]
        charge.status = "successful"
        for key, value in override.items():
            setattr(charge, key, value)


