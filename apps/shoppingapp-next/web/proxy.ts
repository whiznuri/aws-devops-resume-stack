import { NextResponse, type NextRequest } from "next/server";

import { API_URL } from "@/lib/api";
import {
  ACCESS_COOKIE,
  REFRESH_COOKIE,
  writeTokenCookies,
  type TokenPair,
} from "@/lib/auth-cookies";

// ด่านหน้าประตู: ทุก request ผ่านตรงนี้ก่อน
// หน้าที่เดียวคือ "สายรัดข้อมือหมดอายุ แต่ยังมีบัตรต่ออายุ → ไปต่อที่เคาน์เตอร์ (API) ให้"
export async function proxy(request: NextRequest) {
  const hasAccess = request.cookies.has(ACCESS_COOKIE);
  const refreshToken = request.cookies.get(REFRESH_COOKIE)?.value;

  // ยังไม่หมดอายุ / ไม่เคยล็อกอิน / เป็นแค่ prefetch (ไม่ใช่คนกดจริง ไม่นับเป็น action)
  if (hasAccess || !refreshToken || request.headers.has("next-router-prefetch")) {
    return NextResponse.next();
  }

  const tokens = await refresh(refreshToken);

  if (!tokens) {
    // ต่ออายุไม่ได้ (idle / ครบ 8 ชม. / ถูกปิดบัญชี) → ล้าง cookie ให้กลายเป็นคนไม่ได้ล็อกอิน
    request.cookies.delete(REFRESH_COOKIE);
    const res = NextResponse.next({ request: { headers: request.headers } });
    res.cookies.delete(REFRESH_COOKIE);
    return res;
  }

  // ให้หน้าที่กำลังจะเรนเดอร์เห็น token ใหม่ทันที + ส่ง cookie ใหม่กลับไปให้ browser เก็บ
  request.cookies.set(ACCESS_COOKIE, tokens.access_token);
  request.cookies.set(REFRESH_COOKIE, tokens.refresh_token);
  const res = NextResponse.next({ request: { headers: request.headers } });
  writeTokenCookies(res.cookies, tokens);
  return res;
}

async function refresh(refreshToken: string): Promise<TokenPair | null> {
  try {
    const res = await fetch(`${API_URL}/auth/refresh`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ refresh_token: refreshToken }),
    });
    return res.ok ? ((await res.json()) as TokenPair) : null;
  } catch {
    return null;
  }
}

export const config = {
  // ไม่ต้องตรวจไฟล์ static / รูป / favicon
  matcher: ["/((?!_next/static|_next/image|favicon.ico).*)"],
};
