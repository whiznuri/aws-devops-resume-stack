import Link from "next/link";

import { apiWithAuth, requireStaff, type CurrentUser } from "@/lib/session";

type MenuItem = { loop: string; label: string; href?: string };

const STAFF_MENU: MenuItem[] = [
  { loop: "L-09", label: "สินค้า (เพิ่ม / แก้)", href: "/backoffice/products" },
  { loop: "L-10", label: "เติมสต็อก" },
  { loop: "L-05", label: "ออเดอร์ + ตรวจสลิป" },
  { loop: "L-11", label: "หมวดหมู่" },
];
const ADMIN_MENU: MenuItem[] = [{ loop: "L-12", label: "Dashboard" }];

export default async function BackofficePage() {
  // กุญแจชั้นที่ 1 (Next.js server): ไม่ใช่ staff/admin → เด้งออก ก่อนเรนเดอร์อะไรเลย
  const user = await requireStaff();

  const members = user.role === "admin" ? await loadMembers() : null;

  return (
    <main className="mx-auto max-w-3xl p-10">
      <h1 className="mb-6 text-2xl font-bold">Backoffice</h1>

      <ul className="grid grid-cols-2 gap-3">
        {[...STAFF_MENU, ...(user.role === "admin" ? ADMIN_MENU : [])].map((m) => (
          <li key={m.loop} className={`rounded border p-4 ${m.href ? "hover:bg-amber-50" : "opacity-60"}`}>
            <span className="text-xs text-gray-500">{m.loop} · {m.href ? "พร้อมใช้" : "ยังไม่ได้ทำ"}</span>
            {m.href ? (
              <Link href={m.href} className="block font-medium underline">{m.label}</Link>
            ) : (
              <p className="font-medium">{m.label}</p>
            )}
          </li>
        ))}
      </ul>

      {members && (
        <section className="mt-8">
          <h2 className="mb-2 text-lg font-bold">สมาชิกทั้งหมด (admin)</h2>
          <table className="w-full text-left text-sm">
            <thead>
              <tr className="border-b"><th>id</th><th>email</th><th>role</th><th>สถานะ</th></tr>
            </thead>
            <tbody>
              {members.map((m) => (
                <tr key={m.id} className="border-b">
                  <td>{m.id}</td><td>{m.email}</td><td>{m.role}</td><td>{m.is_active ? "ใช้งาน" : "ปิด"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </section>
      )}
    </main>
  );
}

async function loadMembers(): Promise<CurrentUser[]> {
  // กุญแจชั้นที่ 2 (API) เช็ค role ซ้ำอีกรอบ — ถ้าไม่ใช่ admin จะได้ 403
  const res = await apiWithAuth("/admin/users");
  return res.ok ? ((await res.json()) as CurrentUser[]) : [];
}
