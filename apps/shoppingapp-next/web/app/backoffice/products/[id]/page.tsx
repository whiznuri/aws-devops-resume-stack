import Link from "next/link";
import { notFound } from "next/navigation";

import { deleteImage, setProductActive, setVariantActive } from "@/app/actions/products";
import { AddImageForm, AddVariantForm, ProductInfoForm } from "@/app/ui/product-edit-forms";
import { Thumb } from "@/app/ui/thumb";
import { formatBaht, totalStock, unique, type Product } from "@/lib/catalog";
import { apiWithAuth, requireStaff } from "@/lib/session";

export default async function EditProductPage({ params }: PageProps<"/backoffice/products/[id]">) {
  await requireStaff();
  const { id } = await params;

  const res = await apiWithAuth(`/admin/products/${Number(id)}`);
  if (!res.ok) notFound();
  const product = (await res.json()) as Product;

  return (
    <main className="mx-auto max-w-3xl p-8">
      <Link href="/backoffice/products" className="text-sm underline">← กลับรายการสินค้า</Link>

      <div className="my-4 flex items-center justify-between">
        <h1 className="text-2xl font-bold">
          {product.name} <span className="text-base font-normal">{formatBaht(product.price)}</span>
        </h1>
        {/* ไม่มีปุ่มลบ: ปิดขาย (suspend) แทน เปิดกลับได้เสมอ */}
        <form action={setProductActive.bind(null, product.id, !product.is_active)}>
          <button className={`rounded px-3 py-1 text-white ${product.is_active ? "bg-red-600" : "bg-green-700"}`}>
            {product.is_active ? "ปิดขายสินค้านี้" : "เปิดขายอีกครั้ง"}
          </button>
        </form>
      </div>
      {!product.is_active && <p className="mb-4 rounded bg-gray-100 p-2 text-sm">สินค้านี้ปิดขายอยู่ ลูกค้าจะไม่เห็น</p>}

      <ProductInfoForm product={product} />

      <h2 className="mt-8 text-lg font-bold">ตัวเลือก (variant) · สต็อกรวม {totalStock(product)}</h2>
      <p className="text-xs text-gray-500">เปลี่ยนสต็อกได้ที่เมนูเติมสต็อก (L-10) ซึ่งเก็บประวัติทุกครั้ง</p>
      <table className="mt-2 w-full text-left text-sm">
        <thead>
          <tr className="border-b text-gray-500"><th className="py-1">SKU</th><th>ไซซ์</th><th>สี</th><th>สต็อก</th><th>สถานะ</th><th /></tr>
        </thead>
        <tbody>
          {product.variants.map((v) => (
            <tr key={v.id} className={`border-b ${v.is_active ? "" : "text-gray-400"}`}>
              <td className="py-1 font-mono">{v.sku}</td>
              <td>{v.size ?? "—"}</td>
              <td>{v.color ?? "—"}</td>
              <td className={v.stock === 0 ? "text-red-600" : ""}>{v.stock}</td>
              <td>{v.is_active ? "ขายอยู่" : "ปิดขาย"}</td>
              <td>
                <form action={setVariantActive.bind(null, v.id, !v.is_active)}>
                  <button className="text-xs underline">{v.is_active ? "ปิดขาย" : "เปิดขาย"}</button>
                </form>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <AddVariantForm productId={product.id} />

      <ImagesByColor product={product} />
    </main>
  );
}

// รูปจัดกลุ่มตามสี: สีเปลี่ยนหน้าตาสินค้า แต่ไซซ์ดูจากรูปไม่ออก จึงไม่แยกรูปตามไซซ์
function ImagesByColor({ product }: { product: Product }) {
  const colors = unique(product.variants.map((v) => v.color));
  const groups = [null, ...colors].map((color) => ({
    color,
    images: product.images.filter((i) => i.color === color),
  }));

  return (
    <section className="mt-8">
      <h2 className="text-lg font-bold">รูปสินค้า (ตามสี)</h2>
      <p className="text-xs text-gray-500">เก็บเป็นลิงก์ https ไม่ได้อัปโหลดไฟล์ · ลบรูปได้ (รูปไม่ใช่ข้อมูลธุรกรรม)</p>
      {groups.map((g) => (
        <div key={g.color ?? "_all"} className="mt-3">
          <p className="text-sm font-medium">{g.color ?? "รูปรวม (ทุกสี)"}</p>
          <div className="mt-1 flex flex-wrap gap-2">
            {g.images.length === 0 && <span className="text-xs text-gray-400">ยังไม่มีรูป</span>}
            {g.images.map((img) => (
              <div key={img.id} className="relative">
                <Thumb src={img.url} alt={`${product.name} ${g.color ?? ""}`} className="h-24 w-24 rounded border" />
                <form action={deleteImage.bind(null, img.id)} className="absolute right-1 top-1">
                  <button className="rounded bg-white/80 px-1 text-xs text-red-600" aria-label="ลบรูป">✕</button>
                </form>
              </div>
            ))}
          </div>
        </div>
      ))}
      <AddImageForm productId={product.id} colors={colors} />
    </section>
  );
}
