import "server-only";

import { apiWithAuth, getCurrentUser } from "@/lib/session";

export type LineStatus = "ok" | "insufficient" | "sold_out" | "unavailable";

export type CartLine = {
  variant_id: number;
  product_id: number;
  product_name: string;
  sku: string;
  size: string | null;
  color: string | null;
  image_url: string | null;
  unit_price: string;
  quantity: number;
  line_total: string;
  status: LineStatus;
};

export type Cart = { lines: CartLine[]; subtotal: string; item_count: number; can_checkout: boolean };

export const MAX_QUANTITY = 10; // ตรงกับ cart_max_quantity ฝั่ง API (API ตรวจซ้ำเสมอ)

/** ตะกร้าของคนที่ล็อกอินอยู่ · ยังไม่ล็อกอิน = null */
export async function getCart(): Promise<Cart | null> {
  if (!(await getCurrentUser())) return null;
  const res = await apiWithAuth("/cart");
  return res.ok ? ((await res.json()) as Cart) : null;
}

