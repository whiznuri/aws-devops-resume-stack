import "server-only";

import { API_URL } from "@/lib/api";
import type { ProductCard, ProductDetail } from "@/lib/catalog";

// หน้าร้านไม่ต้องล็อกอิน → เรียก API ตรง ไม่ต้องแนบ token

export const SORTS = {
  new: "มาใหม่",
  price_asc: "ราคาต่ำ → สูง",
  price_desc: "ราคาสูง → ต่ำ",
} as const;
export type SortKey = keyof typeof SORTS;

export function toSort(value: unknown): SortKey {
  return typeof value === "string" && value in SORTS ? (value as SortKey) : "new";
}

export async function getProducts(q: string, sort: SortKey): Promise<ProductCard[]> {
  const params = new URLSearchParams({ sort, limit: "60" });
  if (q) params.set("q", q);
  try {
    const res = await fetch(`${API_URL}/products?${params}`);
    return res.ok ? ((await res.json()) as ProductCard[]) : [];
  } catch {
    return [];
  }
}

export async function getProduct(id: number): Promise<ProductDetail | null> {
  try {
    const res = await fetch(`${API_URL}/products/${id}`);
    return res.ok ? ((await res.json()) as ProductDetail) : null;
  } catch {
    return null;
  }
}
