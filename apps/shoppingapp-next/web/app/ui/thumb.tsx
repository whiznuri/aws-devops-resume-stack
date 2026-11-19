// รูปสินค้าจากลิงก์ภายนอก (ไม่เก็บไฟล์เอง) · ใช้ <img> ธรรมดา เพราะ staff ใส่ลิงก์ https จากเว็บไหนก็ได้
// next/image ต้องประกาศโดเมนที่อนุญาตไว้ล่วงหน้า จึงยังไม่ใช้ตอนนี้
export function Thumb({ src, alt, className = "" }: { src: string | null; alt: string; className?: string }) {
  if (!src) return <div className={`bg-gray-100 ${className}`} aria-label="ไม่มีรูป" />;
  // eslint-disable-next-line @next/next/no-img-element
  return <img src={src} alt={alt} loading="lazy" className={`object-cover ${className}`} />;
}
