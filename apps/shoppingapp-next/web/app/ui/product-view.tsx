"use client";

import Link from "next/link";
import { useActionState, useState } from "react";

import { addToCart } from "@/app/actions/cart";
import { Thumb } from "@/app/ui/thumb";
import { formatBaht, unique, type Availability, type ProductDetail } from "@/lib/catalog";

const LABEL: Record<Availability, string> = { in_stock: "มีของ", low_stock: "เหลือน้อย", sold_out: "หมด" };

// หน้าสินค้า: กดเลือกสี → รูปเปลี่ยนตามสี · ไซซ์โชว์สถานะของสีนั้น
// ต้องเป็น Client Component เพราะเปลี่ยนรูป/ปุ่มทันทีที่กด ไม่ต้องโหลดหน้าใหม่
export function ProductView({ product }: { product: ProductDetail }) {
  const colors = unique(product.variants.map((v) => v.color));
  const firstAvailable = product.variants.find((v) => v.availability !== "sold_out") ?? product.variants[0];

  const [color, setColor] = useState<string | null>(firstAvailable?.color ?? null);
  const [variantId, setVariantId] = useState<number | null>(null);
  const [imageIndex, setImageIndex] = useState(0);
  const [cartState, cartAction, adding] = useActionState(addToCart, undefined);

  // รูปของสีที่เลือก · ถ้าสีนั้นยังไม่มีรูป ใช้รูปรวมแทน
  const colorImages = product.images.filter((i) => i.color === color);
  const images = colorImages.length > 0 ? colorImages : product.images.filter((i) => i.color === null);
  const main = images[Math.min(imageIndex, images.length - 1)];

  const options = product.variants.filter((v) => v.color === color);
  const chosen = options.find((v) => v.id === variantId);

  function pickColor(c: string) {
    setColor(c);
    setVariantId(null); // เปลี่ยนสีแล้ว ไซซ์ที่เลือกไว้อาจไม่มีในสีใหม่
    setImageIndex(0);
  }

  return (
    <div className="mt-4 grid gap-8 md:grid-cols-2">
      <div>
        <Thumb src={main?.url ?? null} alt={`${product.name} ${color ?? ""}`} className="aspect-square w-full rounded" />
        {images.length > 1 && (
          <div className="mt-2 flex gap-2">
            {images.map((img, i) => (
              <button key={img.url} onClick={() => setImageIndex(i)} className={`rounded border-2 ${i === imageIndex ? "border-black" : "border-transparent"}`}>
                <Thumb src={img.url} alt={`รูปที่ ${i + 1}`} className="h-16 w-16 rounded" />
              </button>
            ))}
          </div>
        )}
      </div>

      <div className="space-y-5">
        <div>
          <h1 className="text-2xl font-bold">{product.name}</h1>
          <p className="text-xl">{formatBaht(product.price)}</p>
        </div>

        {colors.length > 0 && (
          <div>
            <p className="mb-1 text-sm text-gray-600">สี: <b>{color}</b></p>
            <div className="flex flex-wrap gap-2">
              {colors.map((c) => (
                <button key={c} onClick={() => pickColor(c)} className={`rounded border px-3 py-1 ${c === color ? "border-black bg-black text-white" : ""}`}>
                  {c}
                </button>
              ))}
            </div>
          </div>
        )}

        <div>
          <p className="mb-1 text-sm text-gray-600">{options.some((v) => v.size) ? "ไซซ์" : "ตัวเลือก"}</p>
          <div className="flex flex-wrap gap-2">
            {options.map((v) => (
              <button
                key={v.id}
                disabled={v.availability === "sold_out"}
                onClick={() => setVariantId(v.id)}
                className={`rounded border px-3 py-1 text-left disabled:cursor-not-allowed disabled:line-through disabled:opacity-40 ${v.id === variantId ? "border-black ring-2 ring-black" : ""}`}
              >
                {v.size ?? v.color ?? v.sku}
                <span className={`block text-xs ${v.availability === "low_stock" ? "text-amber-600" : "text-gray-500"}`}>{LABEL[v.availability]}</span>
              </button>
            ))}
          </div>
        </div>

        {/* ใส่ตะกร้า (L-02): ส่ง variant ที่เลือกไป Server Action · ยังไม่ล็อกอินจะถูกพาไป login แล้วกลับมาหน้านี้ */}
        <form action={cartAction}>
          <input type="hidden" name="variant_id" value={chosen?.id ?? ""} />
          <input type="hidden" name="product_id" value={product.id} />
          <button disabled={!chosen || adding} className="w-full rounded bg-black px-4 py-3 text-white disabled:opacity-40">
            {adding ? "กำลังใส่ตะกร้า..." : chosen ? `ใส่ตะกร้า · ${[chosen.color, chosen.size].filter(Boolean).join(" ")}` : "เลือกตัวเลือกก่อน"}
          </button>
        </form>
        {cartState?.error && <p className="text-sm text-red-600">{cartState.error}</p>}
        {cartState?.ok && (
          <p className="text-sm text-green-700">{cartState.ok} · <Link href="/cart" className="underline">ดูตะกร้า</Link></p>
        )}

        {product.description && <p className="text-sm text-gray-700">{product.description}</p>}
        {product.details && (
          <dl className="grid grid-cols-[auto_1fr] gap-x-4 text-sm">
            {Object.entries(product.details).map(([k, v]) => (
              <div key={k} className="contents"><dt className="text-gray-500">{k}</dt><dd>{String(v)}</dd></div>
            ))}
          </dl>
        )}
      </div>
    </div>
  );
}
