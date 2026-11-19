"use server";

import { redirect } from "next/navigation";

import { apiWithAuth } from "@/lib/session";

export type CheckoutState = { error?: string; problems?: Record<string, string> } | undefined;

const field = (fd: FormData, key: string) => String(fd.get(key) ?? "").trim();

export async function placeOrder(_prev: CheckoutState, fd: FormData): Promise<CheckoutState> {
  const note = field(fd, "note");
  const res = await apiWithAuth("/orders", {
    method: "POST",
    body: {
      shipping_name: field(fd, "shipping_name"),
      // ลูกค้าพิมพ์ 081-234-5678 ได้ ตัดขีด/ช่องว่างออกก่อนส่ง (API รับแค่ตัวเลข)
      shipping_phone: field(fd, "shipping_phone").replace(/[\s-]/g, ""),
      shipping_address: field(fd, "shipping_address"),
      note: note === "" ? null : note,
    },
  });

  if (res.status === 409) {
    const { detail } = (await res.json()) as { detail: string | { message: string; problems: Record<string, string> } };
    if (typeof detail === "string") return { error: detail }; // เช่น "ตะกร้าว่าง" (กดซ้ำ)
    return { error: detail.message, problems: detail.problems };
  }
  if (res.status === 422) return { error: "ตรวจข้อมูลจัดส่ง: ชื่อ ≥ 2 ตัว · เบอร์ 0xxxxxxxxx · ที่อยู่ ≥ 10 ตัว" };
  if (!res.ok) return { error: "ระบบขัดข้อง ลองใหม่อีกครั้ง" };

  const order = (await res.json()) as { id: number };
  redirect(`/orders/${order.id}?notice=order-placed`);
}
