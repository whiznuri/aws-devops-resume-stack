"use client";

import { useState } from "react";

export default function SkillTabs({ skills }) {
  // จำว่าตอนนี้เปิดแท็บที่เท่าไหร่ (เริ่มที่ 0 = หมวดแรก)
  const [active, setActive] = useState(0);
  const current = skills[active];

  return (
    <div>
      {/* แถวปุ่มแท็บ */}
      <div className="mb-6 flex flex-wrap gap-2" role="tablist">
        {skills.map((group, index) => (
          <button
            key={group.category}
            type="button"
            role="tab"
            aria-selected={index === active}
            onClick={() => setActive(index)}
            className="tab-btn"
          >
            {group.category}
          </button>
        ))}
      </div>

      {/* เนื้อหาของแท็บที่เลือก */}
      <div className="rounded border border-[var(--border)] bg-[var(--surface)] p-6 shadow-[0_6px_24px_var(--shadow)]">
        <h3 className="mb-3 text-lg font-semibold text-[var(--accent)]">
          {current.category}
        </h3>
        <ul className="list-disc space-y-1.5 pl-5 text-[var(--text-muted)] marker:text-[var(--accent)]">
          {current.items.map((item) => (
            <li key={item}>{item}</li>
          ))}
        </ul>
      </div>
    </div>
  );
}