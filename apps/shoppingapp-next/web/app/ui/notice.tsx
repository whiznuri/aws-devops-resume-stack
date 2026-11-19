// แถบแจ้งเตือนเมื่อถูกเด้งออกจากหน้าที่ไม่มีสิทธิ์ (แทน sweetalert)
// รหัสมากับ URL เช่น /?notice=backoffice-denied · รับเฉพาะรหัสที่รู้จัก ข้อความอื่นไม่แสดง

const MESSAGES: Record<string, string> = {
  "backoffice-denied": "ส่วนนี้สำหรับพนักงานเท่านั้น",
  "login-required": "กรุณาเข้าสู่ระบบก่อน",
  "order-placed": "สั่งซื้อสำเร็จ! จองสินค้าไว้ให้แล้ว กรุณาชำระเงินภายในเวลาที่กำหนด",
};

export function Notice({ code }: { code?: string | string[] }) {
  const message = typeof code === "string" ? MESSAGES[code] : undefined;
  if (!message) return null;

  return (
    <div role="alert" className="mb-6 rounded border border-amber-400 bg-amber-50 px-4 py-3 text-amber-900">
      {message}
    </div>
  );
}
