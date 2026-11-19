import { redirect } from "next/navigation";

import { CheckoutForm } from "@/app/ui/checkout-form";
import { formatBaht } from "@/lib/catalog";
import { getCart } from "@/lib/cart";

export default async function CheckoutPage() {
  const cart = await getCart();
  if (!cart) redirect("/login?notice=login-required&next=/checkout");
  if (!cart.can_checkout) redirect("/cart"); // ตะกร้าว่าง / มีแถวที่ซื้อไม่ได้ → กลับไปแก้ก่อน

  return (
    <main className="mx-auto grid w-full max-w-4xl gap-8 p-6 md:grid-cols-2">
      <section>
        <h1 className="mb-4 text-2xl font-bold">ข้อมูลจัดส่ง</h1>
        <CheckoutForm />
      </section>

      <section className="rounded border p-4">
        <h2 className="mb-3 font-bold">สรุปคำสั่งซื้อ</h2>
        <ul className="space-y-2 text-sm">
          {cart.lines.map((l) => (
            <li key={l.variant_id} className="flex justify-between gap-2">
              <span>{l.product_name} <span className="text-gray-500">{[l.color, l.size].filter(Boolean).join(" ")} × {l.quantity}</span></span>
              <span>{formatBaht(l.line_total)}</span>
            </li>
          ))}
        </ul>
        <div className="mt-3 flex justify-between border-t pt-3 text-sm"><span>ค่าส่ง</span><span>ฟรี</span></div>
        <div className="mt-1 flex justify-between text-lg font-bold"><span>ยอดชำระ</span><span>{formatBaht(cart.subtotal)}</span></div>
        <p className="mt-2 text-xs text-gray-500">ราคานี้จะถูกล็อกตอนกดยืนยัน</p>
      </section>
    </main>
  );
}
