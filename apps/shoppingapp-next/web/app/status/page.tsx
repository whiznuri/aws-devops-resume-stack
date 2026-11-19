import { connection } from "next/server";

import { getHealth } from "@/lib/api";

function Lamp({ label, ok }: { label: string; ok: boolean }) {
  return (
    <li className="flex items-center gap-3 text-lg">
      <span className={`h-3 w-3 rounded-full ${ok ? "bg-green-500" : "bg-red-500"}`} />
      {label}: <b>{ok ? "OK" : "DOWN"}</b>
    </li>
  );
}

export default async function StatusPage() {
  // เช็คสถานะตอนมีคนเปิดหน้าจริง ไม่ใช่ตอน build
  await connection();
  const health = await getHealth();

  return (
    <main className="mx-auto max-w-md p-10">
      <h1 className="mb-6 text-2xl font-bold">Shopping App — loop test รางสายหลัก</h1>
      <ul className="space-y-2">
        <Lamp label="Next.js server" ok />
        <Lamp label="API (FastAPI)" ok={health?.api === "ok"} />
        <Lamp label="DB (PostgreSQL)" ok={health?.db === "ok"} />
      </ul>
    </main>
  );
}
