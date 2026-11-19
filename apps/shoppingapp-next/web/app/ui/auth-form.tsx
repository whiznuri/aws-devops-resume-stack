"use client";

import Link from "next/link";
import { useActionState } from "react";

import { login, register } from "@/app/actions/auth";

// ฟอร์มเดียวใช้ทั้ง login และสมัคร · ต้องเป็น Client Component เพราะโชว์ error/สถานะกำลังส่ง
export function AuthForm({ mode, next = "/" }: { mode: "login" | "register"; next?: string }) {
  const [state, action, pending] = useActionState(mode === "login" ? login : register, undefined);
  const isLogin = mode === "login";
  const carry = next === "/" ? "" : `?next=${encodeURIComponent(next)}`; // สลับหน้า login/สมัคร แล้วยังจำหน้าที่จะกลับไป

  return (
    <form action={action} className="space-y-4">
      {/* ล็อกอินเสร็จจะเด้งกลับหน้านี้ (server ตรวจซ้ำว่าเป็น path ในเว็บเราเท่านั้น) */}
      <input type="hidden" name="next" value={next} />
      <label className="block">
        <span className="text-sm">Email</span>
        <input name="email" type="email" required className="mt-1 w-full rounded border px-3 py-2" />
      </label>
      <label className="block">
        <span className="text-sm">รหัสผ่าน {isLogin ? "" : "(อย่างน้อย 8 ตัว)"}</span>
        <input
          name="password"
          type="password"
          required
          minLength={isLogin ? undefined : 8}
          className="mt-1 w-full rounded border px-3 py-2"
        />
      </label>

      {state?.error && <p className="text-sm text-red-600">{state.error}</p>}

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded bg-black px-4 py-2 text-white disabled:opacity-50"
      >
        {pending ? "กำลังส่ง..." : isLogin ? "เข้าสู่ระบบ" : "สมัครสมาชิก"}
      </button>

      <p className="text-center text-sm">
        {isLogin ? (
          <Link href={`/register${carry}`} className="underline">ยังไม่มีบัญชี? สมัครสมาชิก</Link>
        ) : (
          <Link href={`/login${carry}`} className="underline">มีบัญชีแล้ว? เข้าสู่ระบบ</Link>
        )}
      </p>
    </form>
  );
}
