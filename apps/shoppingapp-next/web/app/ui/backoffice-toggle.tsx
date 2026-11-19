"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

// ปุ่มสลับไปกลับ: อยู่หน้าร้าน → "Backoffice" · อยู่ใน backoffice → "กลับหน้าร้าน"
// ต้องเป็น Client Component เพราะต้องรู้ว่าตอนนี้อยู่หน้าไหน (usePathname)
// ส่วนการตัดสินว่า "ใครเห็นปุ่มนี้" ยังอยู่ฝั่ง server (user-bar.tsx)
export function BackofficeToggle() {
  const inBackoffice = usePathname().startsWith("/backoffice");

  return inBackoffice ? (
    <Link href="/" className="rounded bg-gray-700 px-3 py-1 text-white">กลับหน้าร้าน</Link>
  ) : (
    <Link href="/backoffice" className="rounded bg-amber-500 px-3 py-1 text-white">Backoffice</Link>
  );
}
