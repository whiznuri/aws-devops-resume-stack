"use client";

import Link from "next/link";
import { useActionState } from "react";

import { placeOrder } from "@/app/actions/orders";

const input = "mt-1 w-full rounded border px-3 py-2";

export function CheckoutForm() {
  const [state, action, pending] = useActionState(placeOrder, undefined);

  return (
    <form action={action} className="space-y-4">
      <label className="block">ชื่อผู้รับ<input name="shipping_name" required minLength={2} className={input} /></label>
      <label className="block">เบอร์โทร<input name="shipping_phone" required inputMode="tel" placeholder="0812345678" className={input} /></label>
      <label className="block">ที่อยู่จัดส่ง<textarea name="shipping_address" required minLength={10} rows={3} className={input} /></label>
      <label className="block">หมายเหตุ (ถ้ามี)<input name="note" maxLength={300} className={input} /></label>

      {state?.error && (
        <div className="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
          <p>{state.error}</p>
          {state.problems && (
            <ul className="mt-1 list-disc pl-5">
              {Object.entries(state.problems).map(([sku, why]) => <li key={sku}>{sku}: {why}</li>)}
            </ul>
          )}
          {state.problems && <Link href="/cart" className="underline">กลับไปแก้ตะกร้า</Link>}
        </div>
      )}

      {/* กดแล้วปุ่มล็อกทันที กันกดซ้ำ · ถึงกดซ้ำได้ API ก็กันไว้อีกชั้น (ตะกร้าว่าง → 409) */}
      <button disabled={pending} className="w-full rounded bg-black px-4 py-3 text-white disabled:opacity-50">
        {pending ? "กำลังสั่งซื้อ..." : "ยืนยันสั่งซื้อ"}
      </button>
      <p className="text-xs text-gray-500">กดยืนยันแล้วระบบจะจองสินค้าไว้ให้ 1 ชั่วโมง กรุณาชำระภายในเวลานั้น</p>
    </form>
  );
}
