// URL ของ FastAPI ที่ "Next.js server" ใช้เรียก (ฝั่ง server เท่านั้น ไม่ส่งไป browser)
// ใน Docker = http://api:8000 (ชื่อ service) · รันนอก Docker = http://localhost:8100
export const API_URL = process.env.API_URL ?? "http://localhost:8100";

export type Health = { api: string; db: string };

export async function getHealth(): Promise<Health | null> {
  try {
    const res = await fetch(`${API_URL}/health`);
    if (!res.ok) return null;
    return (await res.json()) as Health;
  } catch {
    // ต่อ API ไม่ติดเลย (container ยังไม่ขึ้น / URL ผิด) → ให้หน้าเว็บโชว์ว่าสายขาด แทนที่จะพังทั้งหน้า
    return null;
  }
}
