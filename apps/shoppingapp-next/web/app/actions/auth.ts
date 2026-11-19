"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import { API_URL } from "@/lib/api";
import { safeNext } from "@/lib/nav";
import {
  ACCESS_COOKIE,
  REFRESH_COOKIE,
  writeTokenCookies,
  type TokenPair,
} from "@/lib/auth-cookies";

export type FormState = { error?: string } | undefined;

async function postJson(path: string, body: unknown): Promise<Response> {
  return fetch(`${API_URL}${path}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
}

function readForm(formData: FormData) {
  return {
    email: String(formData.get("email") ?? "").trim(),
    password: String(formData.get("password") ?? ""),
  };
}

async function loginAndSetCookies(email: string, password: string): Promise<FormState> {
  const res = await postJson("/auth/login", { email, password });
  if (res.status === 401) return { error: "email หรือรหัสผ่านไม่ถูกต้อง" };
  if (res.status === 422) return { error: "รูปแบบ email ไม่ถูกต้อง" };
  if (!res.ok) return { error: "ระบบขัดข้อง ลองใหม่อีกครั้ง" };

  writeTokenCookies(await cookies(), (await res.json()) as TokenPair);
  return undefined;
}

export async function login(_prev: FormState, formData: FormData): Promise<FormState> {
  const { email, password } = readForm(formData);
  const failed = await loginAndSetCookies(email, password);
  if (failed) return failed;
  redirect(safeNext(formData.get("next"))); // กลับไปหน้าที่ค้างไว้ เช่น หน้าสินค้าที่กดใส่ตะกร้า
}

export async function register(_prev: FormState, formData: FormData): Promise<FormState> {
  const { email, password } = readForm(formData);
  if (password.length < 8) return { error: "รหัสผ่านต้องยาวอย่างน้อย 8 ตัว" };

  const res = await postJson("/auth/register", { email, password });
  if (res.status === 409) return { error: "email นี้มีผู้ใช้แล้ว" };
  if (res.status === 422) return { error: "รูปแบบ email ไม่ถูกต้อง" };
  if (!res.ok) return { error: "ระบบขัดข้อง ลองใหม่อีกครั้ง" };

  // สมัครเสร็จ ล็อกอินให้เลย ไม่ต้องกรอกซ้ำ
  const failed = await loginAndSetCookies(email, password);
  if (failed) return failed;
  redirect(safeNext(formData.get("next")));
}

export async function logout(): Promise<void> {
  const store = await cookies();
  const refreshToken = store.get(REFRESH_COOKIE)?.value;
  if (refreshToken) {
    await postJson("/auth/logout", { refresh_token: refreshToken }).catch(() => undefined);
  }
  store.delete(ACCESS_COOKIE);
  store.delete(REFRESH_COOKIE);
  redirect("/");
}
