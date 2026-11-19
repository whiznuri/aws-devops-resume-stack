import Link from "next/link";
import { redirect } from "next/navigation";

import { formatBaht } from "@/lib/catalog";
import { STATUS_LABEL, orderNo, thaiTime, type Order } from "@/lib/orders";
import { apiWithAuth, getCurrentUser } from "@/lib/session";

export default async function MyOrdersPage() {
  if (!(await getCurrentUser())) redirect("/login?notice=login-required&next=/orders");
  const res = await apiWithAuth("/orders");
  const orders = res.ok ? ((await res.json()) as Order[]) : [];

  return (
    <main className="mx-auto w-full max-w-3xl p-6">
      <h1 className="mb-6 text-2xl font-bold">คำสั่งซื้อของฉัน</h1>
      {orders.length === 0 && <p className="text-gray-500">ยังไม่มีคำสั่งซื้อ · <Link href="/" className="underline">ไปเลือกสินค้า</Link></p>}
      <ul className="divide-y">
        {orders.map((o) => (
          <li key={o.id} className="flex items-center justify-between py-3">
            <div>
              <Link href={`/orders/${o.id}`} className="font-medium underline">{orderNo(o.id)}</Link>
              <p className="text-sm text-gray-500">{thaiTime(o.created_at)} · {o.items.reduce((n, i) => n + i.quantity, 0)} ชิ้น</p>
            </div>
            <div className="text-right">
              <span className={`rounded px-2 py-0.5 text-xs ${STATUS_LABEL[o.status].tone}`}>{STATUS_LABEL[o.status].text}</span>
              <p className="font-medium">{formatBaht(o.subtotal)}</p>
            </div>
          </li>
        ))}
      </ul>
    </main>
  );
}
