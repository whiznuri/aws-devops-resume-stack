"use client";

import { useActionState } from "react";

import { checkPayment, startPayment } from "@/app/actions/payments";
import { Thumb } from "@/app/ui/thumb";

type Payment = { status: string; qr_image_url: string | null } | null;

// กล่องจ่ายเงิน: ยังไม่มี QR → ปุ่มจ่าย · มี QR → โชว์ QR + ปุ่มตรวจสถานะ
export function PayPanel({ orderId, payment }: { orderId: number; payment: Payment }) {
  const [startState, start, starting] = useActionState(startPayment.bind(null, orderId), undefined);
  const [checkState, check, checking] = useActionState(checkPayment.bind(null, orderId), undefined);
  const hasQr = payment?.status === "pending" && payment.qr_image_url;

  return (
    <div className="mt-3">
      {!hasQr ? (
        <form action={start}>
          <button disabled={starting} className="rounded bg-black px-4 py-2 text-white disabled:opacity-50">
            {starting ? "กำลังสร้าง QR..." : "จ่ายด้วย PromptPay"}
          </button>
        </form>
      ) : (
        <div className="space-y-2">
          <Thumb src={payment.qr_image_url} alt="QR PromptPay" className="h-64 w-64 rounded border bg-white object-contain" />
          <p className="text-sm">สแกนด้วยแอปธนาคาร แล้วกด &quot;ตรวจสถานะ&quot;</p>
          <form action={check}>
            <button disabled={checking} className="rounded border px-4 py-2 disabled:opacity-50">
              {checking ? "กำลังตรวจกับ Omise..." : "ตรวจสถานะการชำระเงิน"}
            </button>
          </form>
          <p className="text-xs text-gray-500">โหมดทดสอบ: ไม่มีเงินจริง · จำลองว่าลูกค้าจ่ายแล้วด้วย Omise API: POST /charges/:id/mark_as_paid (ใช้ได้เฉพาะ test mode)</p>
        </div>
      )}
      {startState?.error && <p className="mt-2 text-sm text-red-600">{startState.error}</p>}
      {checkState?.error && <p className="mt-2 text-sm text-red-600">{checkState.error}</p>}
      {checkState?.info && <p className="mt-2 text-sm text-amber-700">{checkState.info}</p>}
    </div>
  );
}
