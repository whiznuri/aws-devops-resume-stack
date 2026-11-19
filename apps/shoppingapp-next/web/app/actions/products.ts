"use server";

import { refresh } from "next/cache";
import { redirect } from "next/navigation";

import { apiWithAuth } from "@/lib/session";

export type FormState = { error?: string; ok?: string } | undefined;

// แปลงรหัสตอบกลับของ API เป็นข้อความที่ staff อ่านรู้เรื่อง
async function explain(res: Response): Promise<string> {
  if (res.status === 409) return "SKU นี้มีอยู่แล้ว";
  if (res.status === 403) return "ไม่มีสิทธิ์ทำรายการนี้";
  if (res.status === 401) return "หมดเวลา กรุณาเข้าสู่ระบบใหม่";
  if (res.status === 422) return "ข้อมูลไม่ถูกต้อง: ตรวจราคา (≥ 0, ทศนิยมไม่เกิน 2 ตำแหน่ง), สต็อก (≥ 0) และช่องที่ต้องกรอก";
  return `ระบบขัดข้อง (${res.status}) ลองใหม่อีกครั้ง`;
}

const text = (fd: FormData, key: string) => {
  const v = String(fd.get(key) ?? "").trim();
  return v === "" ? null : v;
};

function readVariants(fd: FormData) {
  // ช่อง variant ส่งมาเป็นชุด: variant_sku[0], variant_size[0] ... เรียงตรงกันตามแถว
  const skus = fd.getAll("variant_sku").map(String);
  const sizes = fd.getAll("variant_size").map(String);
  const colors = fd.getAll("variant_color").map(String);
  const stocks = fd.getAll("variant_stock").map(String);
  return skus
    .map((sku, i) => ({
      sku: sku.trim(),
      size: sizes[i]?.trim() || null,
      color: colors[i]?.trim() || null,
      stock: Number(stocks[i] || 0),
    }))
    .filter((v) => v.sku !== "");
}

export async function createProduct(_prev: FormState, fd: FormData): Promise<FormState> {
  const variants = readVariants(fd);
  if (variants.length === 0) return { error: "ต้องมีอย่างน้อย 1 ตัวเลือก (variant) พร้อม SKU" };

  const res = await apiWithAuth("/admin/products", {
    method: "POST",
    body: {
      name: text(fd, "name") ?? "",
      description: text(fd, "description"),
      price: text(fd, "price") ?? "",
      variants,
    },
  });
  if (!res.ok) return { error: await explain(res) };

  const product = (await res.json()) as { id: number };
  redirect(`/backoffice/products/${product.id}`);
}

export async function updateProduct(productId: number, _prev: FormState, fd: FormData): Promise<FormState> {
  const res = await apiWithAuth(`/admin/products/${productId}`, {
    method: "PATCH",
    body: {
      name: text(fd, "name") ?? "",
      description: text(fd, "description"),
      price: text(fd, "price") ?? "",
    },
  });
  if (!res.ok) return { error: await explain(res) };
  refresh();
  return { ok: "บันทึกแล้ว" };
}

export async function setProductActive(productId: number, isActive: boolean): Promise<void> {
  await apiWithAuth(`/admin/products/${productId}`, { method: "PATCH", body: { is_active: isActive } });
  refresh();
}

export async function addVariant(productId: number, _prev: FormState, fd: FormData): Promise<FormState> {
  const [variant] = readVariants(fd);
  if (!variant) return { error: "กรอก SKU ก่อน" };
  const res = await apiWithAuth(`/admin/products/${productId}/variants`, { method: "POST", body: variant });
  if (!res.ok) return { error: await explain(res) };
  refresh();
  return { ok: `เพิ่ม ${variant.sku.toUpperCase()} แล้ว` };
}

export async function setVariantActive(variantId: number, isActive: boolean): Promise<void> {
  await apiWithAuth(`/admin/variants/${variantId}`, { method: "PATCH", body: { is_active: isActive } });
  refresh();
}

export async function addImage(productId: number, _prev: FormState, fd: FormData): Promise<FormState> {
  const url = text(fd, "url");
  if (!url) return { error: "ใส่ลิงก์รูปก่อน" };
  const res = await apiWithAuth(`/admin/products/${productId}/images`, {
    method: "POST",
    body: { url, color: text(fd, "color") },
  });
  if (res.status === 422) return { error: "ลิงก์รูปต้องขึ้นต้นด้วย https://" };
  if (!res.ok) return { error: await explain(res) };
  refresh();
  return { ok: "เพิ่มรูปแล้ว" };
}

export async function deleteImage(imageId: number): Promise<void> {
  await apiWithAuth(`/admin/images/${imageId}`, { method: "DELETE" });
  refresh();
}
