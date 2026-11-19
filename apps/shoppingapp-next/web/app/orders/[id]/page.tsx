import Link from "next/link";
import { notFound, redirect } from "next/navigation";

import { Notice } from "@/app/ui/notice";
import { PayPanel } from "@/app/ui/pay-panel";
import { formatBaht } from "@/lib/catalog";
import { STATUS_LABEL, orderNo, thaiTime, type Order } from "@/lib/orders";
import { apiWithAuth, getCurrentUser } from "@/lib/session";

export default async function OrderPage({ params, searchParams }: PageProps<"/orders/[id]">) {
  const { id } = await params;
  const { notice } = await searchParams;
  if (!(await getCurrentUser())) redirect(`/login?notice=login-required&next=/orders/${id}`);

  // ออเดอร์คนอื่น API ตอบ 404 เหมือนไม่มีอยู่จริง
  const res = await apiWithAuth(`/orders/${Number(id)}`);
  if (!res.ok) notFound();
  const order = (await res.json()) as Order;
  const status = STATUS_LABEL[order.status];
  const payRes = await apiWithAuth(`/orders/${order.id}/payment`);
  const payment = payRes.ok ? ((await payRes.json()) as { status: string; qr_image_url: string | null; paid_at: string | null } | null) : null;

  return (
    <main className="mx-auto w-full max-w-3xl p-6">
      <Notice code={notice} />
      <Link href="/orders" className="text-sm underline">← คำสั่งซื้อทั้งหมด</Link>

      <div className="my-4 flex items-center justify-between">
        <h1 className="text-2xl font-bold">คำสั่งซื้อ {orderNo(order.id)}</h1>
        <span className={`rounded px-3 py-1 text-sm ${status.tone}`}>{status.text}</span>
      </div>

      {order.status === "pending_payment" && (
        <div className="mb-6 rounded border border-amber-400 bg-amber-50 p-4">
          <p className="font-medium">จองสินค้าไว้ให้ถึง {thaiTime(order.expires_at)}</p>
          <p className="text-sm">ชำระภายในเวลานี้ ไม่เช่นนั้นคำสั่งซื้อจะถูกยกเลิกและคืนสินค้าเข้าสต็อก</p>
          {/* L-04: จ่ายผ่าน Omise (test mode) ด้วย PromptPay QR */}
          <PayPanel orderId={order.id} payment={payment} />
        </div>
      )}

      {order.status === "paid" && payment?.paid_at && (
        <p className="mb-6 rounded border border-green-400 bg-green-50 p-4 text-green-900">ชำระเงินแล้วเมื่อ {thaiTime(payment.paid_at)} ผ่าน PromptPay (Omise)</p>
      )}

      <ul className="divide-y rounded border">
        {order.items.map((i) => (
          <li key={i.variant_id} className="flex justify-between p-3 text-sm">
            <span>
              {i.product_name} <span className="text-gray-500">{[i.color, i.size].filter(Boolean).join(" ")} · {i.sku}</span>
              <span className="block text-gray-500">{formatBaht(i.unit_price)} × {i.quantity}</span>
            </span>
            <span className="font-medium">{formatBaht(i.line_total)}</span>
          </li>
        ))}
        <li className="flex justify-between p-3 font-bold"><span>ยอดชำระ</span><span>{formatBaht(order.subtotal)}</span></li>
      </ul>

      <section className="mt-6 text-sm">
        <h2 className="mb-1 font-bold">จัดส่งถึง</h2>
        {/* เจ้าของออเดอร์เห็นข้อมูลของตัวเองเต็ม (มาตรฐาน masking §8) */}
        <p>{order.shipping_name} · {order.shipping_phone}</p>
        <p className="whitespace-pre-line">{order.shipping_address}</p>
        {order.note && <p className="mt-1 text-gray-500">หมายเหตุ: {order.note}</p>}
        <p className="mt-2 text-gray-500">สั่งเมื่อ {thaiTime(order.created_at)}</p>
      </section>
    </main>
  );
}
