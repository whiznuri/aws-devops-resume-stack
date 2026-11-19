import "server-only";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import { API_URL } from "@/lib/api";
import { ACCESS_COOKIE } from "@/lib/auth-cookies";

export type Role = "customer" | "staff" | "admin";
export type CurrentUser = { id: number; email: string; role: Role; is_active: boolean };

/** ใครล็อกอินอยู่ — ถาม API ทุกครั้ง (ไม่ถอด JWT เองฝั่งเว็บ ให้ API เป็นคนตัดสินคนเดียว) */
export async function getCurrentUser(): Promise<CurrentUser | null> {
  const token = (await cookies()).get(ACCESS_COOKIE)?.value;
  if (!token) return null;
  try {
    const res = await fetch(`${API_URL}/auth/me`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    return res.ok ? ((await res.json()) as CurrentUser) : null;
  } catch {
    return null;
  }
}

/** เรียก API แทน browser พร้อมแนบ token (BFF) */
export async function apiWithAuth(path: string, init: { method?: string; body?: unknown } = {}): Promise<Response> {
  const token = (await cookies()).get(ACCESS_COOKIE)?.value;
  const headers: Record<string, string> = token ? { Authorization: `Bearer ${token}` } : {};
  if (init.body !== undefined) headers["Content-Type"] = "application/json";
  return fetch(`${API_URL}${path}`, {
    method: init.method ?? "GET",
    headers,
    body: init.body === undefined ? undefined : JSON.stringify(init.body),
  });
}

export function canEnterBackoffice(user: CurrentUser | null): boolean {
  return user?.role === "staff" || user?.role === "admin";
}

/** กุญแจชั้น 1 ของทุกหน้าใน backoffice: ไม่ล็อกอิน → /login · ไม่ใช่ staff/admin → หน้าร้าน */
export async function requireStaff(): Promise<CurrentUser> {
  const user = await getCurrentUser();
  if (!user) redirect("/login?notice=login-required");
  if (!canEnterBackoffice(user)) redirect("/?notice=backoffice-denied");
  return user;
}
