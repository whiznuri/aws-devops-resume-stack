"use client";

import { useActionState, useState } from "react";

import { createProduct } from "@/app/actions/products";

const input = "w-full rounded border px-2 py-1";

// ฟอร์มเพิ่มสินค้า · แถว variant เพิ่ม/ลบได้ทันทีในหน้าจอ จึงต้องเป็น Client Component
export function ProductCreateForm() {
  const [state, action, pending] = useActionState(createProduct, undefined);
  const [rows, setRows] = useState([0]);
  const [nextKey, setNextKey] = useState(1);

  return (
    <form action={action} className="space-y-4">
      <label className="block">ชื่อสินค้า<input name="name" required maxLength={200} className={input} /></label>
      <label className="block">ราคา (บาท)<input name="price" required inputMode="decimal" pattern="\d+(\.\d{1,2})?" className={input} /></label>
      <label className="block">รายละเอียด<textarea name="description" className={input} /></label>

      <fieldset className="rounded border p-3">
        <legend className="px-1 font-medium">ตัวเลือก (variant) — สต็อกใส่ได้ตอนสร้างเท่านั้น</legend>
        <div className="grid grid-cols-[2fr_1fr_1fr_1fr_auto] gap-2 text-xs text-gray-500">
          <span>SKU</span><span>ไซซ์</span><span>สี</span><span>สต็อกเริ่มต้น</span><span />
        </div>
        {rows.map((key) => (
          <div key={key} className="mt-1 grid grid-cols-[2fr_1fr_1fr_1fr_auto] gap-2">
            <input name="variant_sku" required placeholder="TEE-WHT-M" className={input} />
            <input name="variant_size" placeholder="M" className={input} />
            <input name="variant_color" placeholder="ขาว" className={input} />
            <input name="variant_stock" type="number" min={0} defaultValue={0} className={input} />
            <button
              type="button"
              disabled={rows.length === 1}
              onClick={() => setRows(rows.filter((k) => k !== key))}
              className="px-2 text-red-600 disabled:opacity-30"
            >
              ลบ
            </button>
          </div>
        ))}
        <button
          type="button"
          onClick={() => { setRows([...rows, nextKey]); setNextKey(nextKey + 1); }}
          className="mt-2 text-sm underline"
        >
          + เพิ่มตัวเลือก
        </button>
      </fieldset>

      {state?.error && <p className="text-sm text-red-600">{state.error}</p>}
      <button disabled={pending} className="rounded bg-black px-4 py-2 text-white disabled:opacity-50">
        {pending ? "กำลังบันทึก..." : "บันทึกสินค้า"}
      </button>
    </form>
  );
}
