import Link from "next/link";

import { ProductCreateForm } from "@/app/ui/product-create-form";
import { requireStaff } from "@/lib/session";

export default async function NewProductPage() {
  await requireStaff();

  return (
    <main className="mx-auto max-w-2xl p-8">
      <Link href="/backoffice/products" className="text-sm underline">← กลับรายการสินค้า</Link>
      <h1 className="my-4 text-2xl font-bold">เพิ่มสินค้า</h1>
      <ProductCreateForm />
    </main>
  );
}
