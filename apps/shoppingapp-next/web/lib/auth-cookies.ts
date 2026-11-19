// ชื่อ cookie + ตัวเลือกความปลอดภัย ใช้ร่วมกันทั้ง proxy และ Server Action
// (ไฟล์นี้ไม่ import next/headers จึงใช้ใน proxy ได้)

export const ACCESS_COOKIE = "access_token";
export const REFRESH_COOKIE = "refresh_token";

export type TokenPair = {
  access_token: string;
  access_expires_at: string;
  refresh_token: string;
  session_expires_at: string;
};

type CookieSetter = {
  set: (name: string, value: string, options: Record<string, unknown>) => unknown;
};

const base = {
  httpOnly: true, // JavaScript ใน browser อ่านไม่ได้ กันโดนขโมย token ผ่าน XSS
  sameSite: "lax" as const, // เว็บอื่นแอบยิงฟอร์มมาพร้อม cookie ไม่ได้
  secure: process.env.NODE_ENV === "production", // dev เป็น http ธรรมดา
  path: "/",
};

/** ฝัง token ลง cookie ให้หมดอายุพร้อมตัว token เอง */
export function writeTokenCookies(target: CookieSetter, tokens: TokenPair) {
  target.set(ACCESS_COOKIE, tokens.access_token, {
    ...base,
    expires: new Date(tokens.access_expires_at),
  });
  target.set(REFRESH_COOKIE, tokens.refresh_token, {
    ...base,
    expires: new Date(tokens.session_expires_at),
  });
}
