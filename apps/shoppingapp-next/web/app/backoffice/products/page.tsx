import Link from "next/link";

import { Chips } from "@/app/ui/chips";
import { Thumb } from "@/app/ui/thumb";
import { formatBaht, totalStock, unique, type Product } from "@/lib/catalog";
import { apiWithAuth, requireStaff } from "@/lib/session";

export default async function ProductListPage({ searchParams }: PageProps<"/backoffice/products">) {
  await requireStaff();
  const { q } = await searchParams;
  const query = typeof q === "string" ? q : "";

  // กุญแจชั้น 2: API เช็ค role staff ซ้ำ
  const res = await apiWithAuth(`/admin/products?q=${encodeURIComponent(query)}&limit=100`);
  const products = res.ok ? ((await res.json()) as Product[]) : [];

  return (
    <main className="mx-auto max-w-5xl p-8">
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-bold">สินค้า <span className="text-sm text-gray-500">L-09</span></h1>
        <Link href="/backoffice/products/new" className="rounded bg-black px-4 py-2 text-white">+ เพิ่มสินค้า</Link>
      </div>

      {/* ค้นหาแบบ GET ธรรมดา: ผลค้นหาอยู่ใน URL แชร์ลิงก์ได้ ไม่ต้องใช้ JavaScript */}
      <form className="mb-4 flex gap-2">
        <input name="q" defaultValue={query} placeholder="ค้นจากชื่อ หรือ SKU" className="flex-1 rounded border px-3 py-2" />
        <button className="rounded border px-4">ค้นหา</button>
      </form>

      <table className="w-full text-left text-sm">
        <thead>
          <tr className="border-b text-gray-500">
            <th className="py-2">สินค้า</th><th>ราคา</th><th>ตัวเลือก</th><th>สต็อกรวม</th><th>สถานะ</th>
          </tr>
        </thead>
        <tbody>
          {products.map((p) => (
            <tr key={p.id} className="border-b align-top">
              <td className="flex gap-3 py-2">
                <Thumb src={p.images[0]?.url ?? null} alt={p.name} className="h-12 w-12 rounded" />
                <span className="space-y-1">
                  <Link href={`/backoffice/products/${p.id}`} className="font-medium underline">{p.name}</Link>
                  {/* ป้ายสรุปไซซ์ / สี ให้เห็นภาพรวมโดยไม่ต้องเปิดเข้าไป */}
                  <Chips items={unique(p.variants.map((v) => v.size))} />
                  <Chips items={unique(p.variants.map((v) => v.color))} />
                </span>
              </td>
              <td>{formatBaht(p.price)}</td>
              <td>{p.variants.length}</td>
              <td className={totalStock(p) === 0 ? "text-red-600" : ""}>{totalStock(p)}</td>
              <td>{p.is_active ? "ขายอยู่" : <span className="text-gray-400">ปิดขาย</span>}</td>
            </tr>
          ))}
        </tbody>
      </table>
      {products.length === 0 && <p className="mt-6 text-gray-500">ไม่พบสินค้า</p>}
    </main>
  );
}
