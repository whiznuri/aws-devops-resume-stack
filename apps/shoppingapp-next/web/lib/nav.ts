/** path ที่จะเด้งกลับหลังล็อกอิน · รับเฉพาะ path ในเว็บเรา กันโดนหลอกเด้งไปเว็บอื่น (open redirect) */
export function safeNext(value: unknown): string {
  return typeof value === "string" && value.startsWith("/") && !value.startsWith("//") ? value : "/";
}
