"use server";

import { refresh } from "next/cache";
import { redirect } from "next/navigation";

import { getCurrentUser, apiWithAuth } from "@/lib/session";

export type CartFormState = { error?: string; ok?: string } | undefined;

async function detail(res: Response): Promise<string> {
  try {
    const body = (await res.json()) as { detail?: unknown };
    if (typeof body.detail === "string") return body.detail; // ข้อความไทยจาก API เช่น "สินค้ามีไม่พอ"
  } catch {}
  return res.status === 422 ? "จำนวนไม่ถูกต้อง" : "ระบบขัดข้อง ลองใหม่อีกครั้ง";
}

export async function addToCart(_prev: CartFormState, fd: FormData): Promise<CartFormState> {
  const variantId = Number(fd.get("variant_id"));
  const productId = Number(fd.get("product_id"));

  // ยังไม่ล็อกอิน → ไป login แล้วเด้งกลับมาหน้าสินค้าเดิม (กติกา: ต้องรู้ ID ก่อน)
  if (!(await getCurrentUser())) {
    redirect(`/login?notice=login-required&next=${encodeURIComponent(`/products/${productId}`)}`);
  }
  if (!variantId) return { error: "เลือกสี / ไซซ์ก่อน" };

  const res = await apiWithAuth("/cart/items", { method: "POST", body: { variant_id: variantId, quantity: 1 } });
  if (!res.ok) return { error: await detail(res) };
  refresh(); // ให้ตัวเลขบนไอคอนตะกร้าอัปเดต
  return { ok: "ใส่ตะกร้าแล้ว" };
}

export async function setCartQuantity(variantId: number, quantity: number): Promise<void> {
  if (quantity < 1) return removeFromCart(variantId);
  await apiWithAuth(`/cart/items/${variantId}`, { method: "PATCH", body: { quantity } });
  refresh();
}

export async function removeFromCart(variantId: number): Promise<void> {
  await apiWithAuth(`/cart/items/${variantId}`, { method: "DELETE" });
  refresh();
}
