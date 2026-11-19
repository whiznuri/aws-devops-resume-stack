export type OrderStatus = "pending_payment" | "awaiting_verification" | "paid" | "cancelled" | "expired";

export type OrderItem = {
  variant_id: number;
  product_name: string;
  sku: string;
  size: string | null;
  color: string | null;
  unit_price: string;
  quantity: number;
  line_total: string;
};

export type Order = {
  id: number;
  status: OrderStatus;
  subtotal: string;
  shipping_name: string;
  shipping_phone: string;
  shipping_address: string;
  note: string | null;
  expires_at: string;
  created_at: string;
  items: OrderItem[];
};

// สถานะแบบ ticket: รอชำระ → รอตรวจสลิป → ชำระแล้ว / ยกเลิก / หมดเวลา
export const STATUS_LABEL: Record<OrderStatus, { text: string; tone: string }> = {
  pending_payment: { text: "รอชำระเงิน", tone: "bg-amber-100 text-amber-900" },
  awaiting_verification: { text: "รอตรวจสลิป", tone: "bg-blue-100 text-blue-900" },
  paid: { text: "ชำระแล้ว", tone: "bg-green-100 text-green-900" },
  cancelled: { text: "ยกเลิก", tone: "bg-gray-200 text-gray-700" },
  expired: { text: "หมดเวลาชำระ", tone: "bg-gray-200 text-gray-700" },
};

export function orderNo(id: number): string {
  return `#${String(id).padStart(6, "0")}`;
}

export function thaiTime(iso: string): string {
  return new Date(iso).toLocaleString("th-TH", { timeZone: "Asia/Bangkok", dateStyle: "medium", timeStyle: "short" });
}
