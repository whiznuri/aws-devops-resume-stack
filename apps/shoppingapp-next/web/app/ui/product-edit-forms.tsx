"use client";

import { useActionState } from "react";

import { addImage, addVariant, updateProduct } from "@/app/actions/products";
import type { Product } from "@/lib/catalog";

const input = "w-full rounded border px-2 py-1";

function Message({ state }: { state: { error?: string; ok?: string } | undefined }) {
  if (state?.error) return <p className="text-sm text-red-600">{state.error}</p>;
  if (state?.ok) return <p className="text-sm text-green-700">{state.ok}</p>;
  return null;
}

export function ProductInfoForm({ product }: { product: Product }) {
  const [state, action, pending] = useActionState(updateProduct.bind(null, product.id), undefined);

  return (
    <form action={action} className="space-y-3">
      <label className="block">ชื่อสินค้า<input name="name" defaultValue={product.name} required className={input} /></label>
      <label className="block">ราคา (บาท)<input name="price" defaultValue={product.price} required inputMode="decimal" pattern="\d+(\.\d{1,2})?" className={input} /></label>
      <label className="block">รายละเอียด<textarea name="description" defaultValue={product.description ?? ""} className={input} /></label>
      <Message state={state} />
      <button disabled={pending} className="rounded bg-black px-4 py-2 text-white disabled:opacity-50">
        {pending ? "กำลังบันทึก..." : "บันทึกข้อมูลสินค้า"}
      </button>
    </form>
  );
}

export function AddVariantForm({ productId }: { productId: number }) {
  const [state, action, pending] = useActionState(addVariant.bind(null, productId), undefined);

  return (
    <form action={action} className="mt-3 grid grid-cols-[2fr_1fr_1fr_1fr_auto] items-end gap-2">
      <input name="variant_sku" required placeholder="SKU ใหม่" className={input} />
      <input name="variant_size" placeholder="ไซซ์" className={input} />
      <input name="variant_color" placeholder="สี" className={input} />
      <input name="variant_stock" type="number" min={0} defaultValue={0} className={input} />
      <button disabled={pending} className="rounded border px-3 py-1">+ เพิ่ม</button>
      <div className="col-span-5"><Message state={state} /></div>
    </form>
  );
}

export function AddImageForm({ productId, colors }: { productId: number; colors: string[] }) {
  const [state, action, pending] = useActionState(addImage.bind(null, productId), undefined);

  return (
    <form action={action} className="mt-3 grid grid-cols-[3fr_1fr_auto] items-end gap-2">
      <input name="url" type="url" required placeholder="https://… (ลิงก์รูป)" className={input} />
      <select name="color" className={input} defaultValue="">
        <option value="">รูปรวม (ทุกสี)</option>
        {colors.map((c) => <option key={c} value={c}>{c}</option>)}
      </select>
      <button disabled={pending} className="rounded border px-3 py-1">+ เพิ่มรูป</button>
      <div className="col-span-3"><Message state={state} /></div>
    </form>
  );
}
