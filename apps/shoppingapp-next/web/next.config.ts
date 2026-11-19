import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // standalone: build ออกมาเป็นโฟลเดอร์ที่มีแค่ไฟล์ที่ต้องใช้ตอนรัน → image เล็ก กิน RAM น้อย (แบบเดียวกับ web-resume)
  output: "standalone",
  turbopack: {
    rules: {
      "*.css": {
        loaders: ["@tailwindcss/turbopack"],
        as: "*.css",
      },
    },
  },
};

export default nextConfig;
