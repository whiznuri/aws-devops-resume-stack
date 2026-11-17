"use client";

import { useState } from "react";

export default function ProjectTabs({ projects }) {
  const [active, setActive] = useState(0);
  const project = projects[active];

  return (
    <div>
      {/* แถวปุ่มแท็บ: ใช้ชื่อสั้น = ข้อความก่อนเครื่องหมาย " — " */}
      <div className="mb-6 flex flex-wrap gap-2" role="tablist">
        {projects.map((p, index) => (
          <button
            key={p.title}
            type="button"
            role="tab"
            aria-selected={index === active}
            onClick={() => setActive(index)}
            className="tab-btn"
          >
            {p.title.split(" — ")[0]}
          </button>
        ))}
      </div>

      {/* การ์ดของโปรเจกต์ที่เลือก (ใช้ class เดิมจาก style.css) */}
      <article className="showcase__card">
        <div className="showcase__text">
          <h3>{project.title}</h3>
          <p>{project.summary}</p>
          <ul className="showcase__facts list-disc space-y-1 marker:text-[var(--accent)]">
            {project.facts.map((fact) => (
              <li key={fact}>{fact}</li>
            ))}
          </ul>
        </div>
        <div className="showcase__meta">
          {project.links.map((link) => (
            <a
              key={link.label}
              href={link.url}
              className="showcase__link"
              target="_blank"
              rel="noopener"
            >
              {link.label}
            </a>
          ))}
        </div>
      </article>
    </div>
  );
}