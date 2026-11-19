"use server";

import { refresh } from "next/cache";

import { apiWithAuth } from "@/lib/session";

export type PayState = { error?: string; info?: string } | undefined;

async function detail(res: Response): Promise<string> {
  try {
    const body = (await res.json()) as { detail?: unknown };
    if (typeof body.detail === "string") return body.detail;
  } catch {}
  return "ระบบขัดข้อง ลองใหม่อีกครั้ง";
}

export async function startPayment(orderId: number): Promise<PayState> {
  const res = await apiWithAuth(`/orders/${orderId}/payment`, { method: "POST" });
  if (!res.ok) return { error: await detail(res) };
  refresh();
  return undefined;
}

export async function checkPayment(orderId: number): Promise<PayState> {
  // server ไปถาม Omise ตรง · หน้าเว็บแค่สั่งให้ไปถาม ไม่ได้บอกว่าจ่ายแล้ว
  const res = await apiWithAuth(`/orders/${orderId}/payment/check`, { method: "POST" });
  if (!res.ok) return { error: await detail(res) };
  const body = (await res.json()) as { order_status: string };
  refresh();
  return body.order_status === "paid" ? undefined : { info: "ยังไม่พบการชำระเงิน ลองใหม่อีกครั้งหลังสแกนจ่าย" };
}
