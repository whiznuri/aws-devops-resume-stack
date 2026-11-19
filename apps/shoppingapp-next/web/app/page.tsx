import Link from "next/link";

import { Chips } from "@/app/ui/chips";
import { Notice } from "@/app/ui/notice";
import { Thumb } from "@/app/ui/thumb";
import { formatBaht } from "@/lib/catalog";
import { SORTS, getProducts, toSort } from "@/lib/storefront";

// หน้าร้าน (L-01): ทุกคนเข้าได้ ไม่ต้องล็อกอิน
export default async function Home({ searchParams }: PageProps<"/">) {
  const { notice, q, sort } = await searchParams;
  const query = typeof q === "string" ? q : "";
  const sortKey = toSort(sort);
  const products = await getProducts(query, sortKey);

  return (
    <main className="mx-auto w-full max-w-6xl p-6">
      <Notice code={notice} />

      {/* ค้นหา + เรียง เป็นฟอร์ม GET: ผลอยู่ใน URL กด back ได้ แชร์ลิงก์ได้ */}
      <form className="mb-6 flex flex-wrap gap-2">
        <input name="q" defaultValue={query} placeholder="ค้นหาสินค้า เช่น เสื้อ, รองเท้า, ตุ๊กตา" className="min-w-60 flex-1 rounded border px-3 py-2" />
        <select name="sort" defaultValue={sortKey} className="rounded border px-3 py-2">
          {Object.entries(SORTS).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
        </select>
        <button className="rounded bg-black px-4 py-2 text-white">ค้นหา</button>
      </form>

      {query && <p className="mb-4 text-sm text-gray-600">ผลการค้นหา &quot;{query}&quot; · {products.length} รายการ</p>}

      <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        {products.map((p) => (
          <li key={p.id}>
            <Link href={`/products/${p.id}`} className="group block">
              <div className="relative">
                <Thumb src={p.cover_url} alt={p.name} className={`aspect-square w-full rounded ${p.sold_out ? "opacity-50" : ""}`} />
                {p.sold_out && <span className="absolute left-2 top-2 rounded bg-black px-2 py-0.5 text-xs text-white">สินค้าหมด</span>}
              </div>
              <p className="mt-2 font-medium group-hover:underline">{p.name}</p>
              <p className="text-sm">{formatBaht(p.price)}</p>
              <div className="mt-1 space-y-1">
                <Chips items={p.colors} />
                <Chips items={p.sizes} />
              </div>
            </Link>
          </li>
        ))}
      </ul>
      {products.length === 0 && <p className="text-gray-500">ไม่พบสินค้า ลองคำอื่นดูนะ</p>}
    </main>
  );
}
