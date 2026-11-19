import Link from "next/link";

import { logout } from "@/app/actions/auth";
import { BackofficeToggle } from "@/app/ui/backoffice-toggle";
import type { Cart } from "@/lib/cart";
import { apiWithAuth, canEnterBackoffice, getCurrentUser } from "@/lib/session";

// แถบบนทุกหน้า: ใครล็อกอินอยู่ + ตะกร้า + ปุ่ม Backoffice (เฉพาะ staff/admin) + ออกจากระบบ
export async function UserBar() {
  const user = await getCurrentUser();
  const cartCount = user ? await countCart() : 0;

  return (
    <header className="flex items-center justify-between border-b px-6 py-3">
      <Link href="/" className="font-bold">Shopping App</Link>
      {user ? (
        <div className="flex items-center gap-3 text-sm">
          <span>
            {user.email} <span className="rounded bg-gray-200 px-2 py-0.5 text-gray-800">{user.role}</span>
          </span>
          <Link href="/orders" className="underline">คำสั่งซื้อ</Link>
          <Link href="/cart" className="relative rounded border px-3 py-1">
            ตะกร้า
            {cartCount > 0 && (
              <span className="absolute -right-2 -top-2 rounded-full bg-red-600 px-1.5 text-xs text-white">{cartCount}</span>
            )}
          </Link>
          {/* ซ่อนปุ่ม = แค่หน้าตา · กุญแจจริงอยู่ที่หน้า /backoffice และที่ API */}
          {canEnterBackoffice(user) && <BackofficeToggle />}
          <form action={logout}>
            <button type="submit" className="underline">ออกจากระบบ</button>
          </form>
        </div>
      ) : (
        <Link href="/login" className="text-sm underline">เข้าสู่ระบบ</Link>
      )}
    </header>
  );
}

async function countCart(): Promise<number> {
  const res = await apiWithAuth("/cart");
  return res.ok ? ((await res.json()) as Cart).item_count : 0;
}
