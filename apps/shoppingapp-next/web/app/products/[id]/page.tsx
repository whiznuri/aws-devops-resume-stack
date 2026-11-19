import Link from "next/link";
import { notFound } from "next/navigation";

import { ProductView } from "@/app/ui/product-view";
import { getProduct } from "@/lib/storefront";

export default async function ProductPage({ params }: PageProps<"/products/[id]">) {
  const { id } = await params;
  const product = await getProduct(Number(id));
  if (!product) notFound(); // ปิดขาย / ไม่มีจริง → ลูกค้าเห็นเป็นหน้าไม่พบ

  return (
    <main className="mx-auto w-full max-w-5xl p-6">
      <Link href="/" className="text-sm underline">← กลับหน้าร้าน</Link>
      <ProductView product={product} />
    </main>
  );
}
