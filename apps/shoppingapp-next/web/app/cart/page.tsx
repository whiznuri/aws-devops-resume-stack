import Link from "next/link";
import { redirect } from "next/navigation";

import { removeFromCart, setCartQuantity } from "@/app/actions/cart";
import { Thumb } from "@/app/ui/thumb";
import { formatBaht } from "@/lib/catalog";
import { MAX_QUANTITY, getCart, type LineStatus } from "@/lib/cart";

const STATUS_TEXT: Record<Exclude<LineStatus, "ok">, string> = {
  insufficient: "สินค้ามีไม่พอ — ลดจำนวนลง",
  sold_out: "สินค้าหมดแล้ว — เอาออกจากตะกร้า",
  unavailable: "สินค้านี้ไม่มีจำหน่ายแล้ว — เอาออกจากตะกร้า",
};

export default async function CartPage() {
  const cart = await getCart();
  if (!cart) redirect("/login?notice=login-required&next=/cart");

  return (
    <main className="mx-auto w-full max-w-3xl p-6">
      <h1 className="mb-6 text-2xl font-bold">ตะกร้าสินค้า</h1>

      {cart.lines.length === 0 ? (
        <p className="text-gray-500">ยังไม่มีสินค้าในตะกร้า · <Link href="/" className="underline">ไปเลือกสินค้า</Link></p>
      ) : (
        <>
          <ul className="divide-y">
            {cart.lines.map((line) => (
              <li key={line.variant_id} className={`flex gap-4 py-4 ${line.status === "ok" ? "" : "bg-red-50"}`}>
                <Thumb src={line.image_url} alt={line.product_name} className="h-20 w-20 rounded" />
                <div className="flex-1">
                  <Link href={`/products/${line.product_id}`} className="font-medium underline">{line.product_name}</Link>
                  <p className="text-sm text-gray-600">{[line.color, line.size].filter(Boolean).join(" · ")} · {line.sku}</p>
                  <p className="text-sm">{formatBaht(line.unit_price)} / ชิ้น</p>
                  {line.status !== "ok" && <p className="text-sm text-red-600">{STATUS_TEXT[line.status]}</p>}
                </div>
                <div className="flex flex-col items-end gap-2">
                  {/* ปุ่ม − / + แต่ละปุ่มคือฟอร์มเล็ก ๆ เรียก Server Action (API ตรวจของ/จำนวนซ้ำเสมอ) */}
                  <div className="flex items-center gap-2">
                    <form action={setCartQuantity.bind(null, line.variant_id, line.quantity - 1)}>
                      <button className="h-8 w-8 rounded border" aria-label="ลดจำนวน">−</button>
                    </form>
                    <span className="w-6 text-center">{line.quantity}</span>
                    <form action={setCartQuantity.bind(null, line.variant_id, line.quantity + 1)}>
                      <button disabled={line.quantity >= MAX_QUANTITY || line.status !== "ok"} className="h-8 w-8 rounded border disabled:opacity-30" aria-label="เพิ่มจำนวน">+</button>
                    </form>
                  </div>
                  <p className="font-medium">{formatBaht(line.line_total)}</p>
                  <form action={removeFromCart.bind(null, line.variant_id)}>
                    <button className="text-xs text-red-600 underline">เอาออก</button>
                  </form>
                </div>
              </li>
            ))}
          </ul>

          <div className="mt-6 flex items-center justify-between border-t pt-4">
            <span>รวม {cart.item_count} ชิ้น</span>
            <span className="text-xl font-bold">{formatBaht(cart.subtotal)}</span>
          </div>
          {!cart.can_checkout && <p className="mt-2 text-sm text-red-600">มีบางรายการซื้อไม่ได้ แก้ไขก่อนสั่งซื้อ</p>}
          {cart.can_checkout ? (
            <Link href="/checkout" className="mt-4 block w-full rounded bg-black px-4 py-3 text-center text-white">
              ไปหน้าสั่งซื้อ
            </Link>
          ) : (
            <button disabled className="mt-4 w-full rounded bg-black px-4 py-3 text-white opacity-40">ไปหน้าสั่งซื้อ</button>
          )}
        </>
      )}
    </main>
  );
}
