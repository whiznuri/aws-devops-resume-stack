// หน้าตาข้อมูลสินค้าที่ API ส่งมา (ตรงกับ schema ฝั่ง FastAPI)

// ---------- backoffice (ProductOut) ----------
export type Variant = {
  id: number;
  sku: string;
  size: string | null;
  color: string | null;
  stock: number;
  is_active: boolean;
};

export type ProductImage = { id: number; url: string; color: string | null; sort_order: number };

export type Product = {
  id: number;
  name: string;
  description: string | null;
  price: string; // เงินมาเป็นข้อความ "390.00" ไม่ใช่ number กันทศนิยมเพี้ยน
  details: Record<string, unknown> | null;
  is_active: boolean;
  variants: Variant[];
  images: ProductImage[];
};

// ---------- หน้าร้าน (ไม่มีจำนวนสต็อกจริง) ----------
export type Availability = "in_stock" | "low_stock" | "sold_out";

export type ProductCard = {
  id: number;
  name: string;
  price: string;
  cover_url: string | null;
  colors: string[];
  sizes: string[];
  sold_out: boolean;
};

export type PublicVariant = { id: number; sku: string; size: string | null; color: string | null; availability: Availability };

export type ProductDetail = {
  id: number;
  name: string;
  description: string | null;
  price: string;
  details: Record<string, unknown> | null;
  images: { url: string; color: string | null }[];
  variants: PublicVariant[];
};

// ---------- ตัวช่วย ----------
export function totalStock(p: Product): number {
  return p.variants.filter((v) => v.is_active).reduce((sum, v) => sum + v.stock, 0);
}

export function unique(values: (string | null)[]): string[] {
  return [...new Set(values.filter((v): v is string => Boolean(v)))];
}

export function formatBaht(price: string): string {
  return `฿${Number(price).toLocaleString("th-TH", { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
}
