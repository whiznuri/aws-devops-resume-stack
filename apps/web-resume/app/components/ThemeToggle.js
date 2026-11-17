"use client";

import { useEffect, useState } from "react";

// รายชื่อธีมที่วนได้ — อยากเพิ่มธีมที่ 3 ก็เติมเข้าไปในลิสต์นี้ + ใส่สีใน theme.css
const THEMES = ["dark", "espresso", "light"];
const ICONS = { dark: "🌙", espresso: "☕", light: "☀️" };

export default function ThemeToggle() {
  const [theme, setTheme] = useState("dark");

  // ตอนเปิดเว็บครั้งแรก: อ่านธีมที่คนดูเคยเลือกไว้
  useEffect(() => {
    try {
      const saved = localStorage.getItem("theme");
      if (THEMES.includes(saved)) setTheme(saved);
    } catch {}
  }, []);

  // ทุกครั้งที่ธีมเปลี่ยน: แปะลงแท็ก <html> แล้วจำไว้ใน browser
  useEffect(() => {
    document.documentElement.dataset.theme = theme;
    try {
      localStorage.setItem("theme", theme);
    } catch {}
  }, [theme]);

  // กดแล้วไปธีมถัดไปในลิสต์ ถึงตัวสุดท้ายแล้ววนกลับตัวแรก
  function nextTheme() {
    const i = THEMES.indexOf(theme);
    setTheme(THEMES[(i + 1) % THEMES.length]);
  }

  return (
    <button
      type="button"
      onClick={nextTheme}
      aria-label="เปลี่ยนธีม"
      title="เปลี่ยนธีม"
      className="rounded-full border border-[var(--border)] px-3 py-1 text-sm transition-colors hover:bg-[var(--accent-soft)]"
    >
      {ICONS[theme]}
    </button>
  );
}