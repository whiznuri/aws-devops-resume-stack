"use client";

import { useState } from "react";

export default function Journey({ journey }) {
  // จำว่าตอนนี้กางอันไหนอยู่ (null = ยังไม่กางอันไหนเลย)
  const [openId, setOpenId] = useState(null);

  return (
    <div className="timeline-accordion">
      {journey.map((step) => {
        const isOpen = openId === step.id;

        return (
          <div
            className={`timeline-item ${isOpen ? "active" : ""}`}
            id={step.id}
            key={step.id}
          >
            <div
              className="timeline-header cursor-pointer"
              onClick={() => setOpenId(isOpen ? null : step.id)}
            >
              <span className="timeline-year">{step.year}</span>
              <div className="timeline-title-group">
                <h3 className="timeline-role">{step.role}</h3>
                <p className="timeline-org">{step.org}</p>
              </div>
              <span className="expand-icon">{isOpen ? "−" : "+"}</span>
            </div>

            <div className="timeline-body">
              <p className="timeline-note">{step.note}</p>
              <hr className="divider" />
              <div className="timeline-details">
                <p><strong>Responsibilities:</strong> {step.details.responsibilities}</p>
                <p><strong>Key Achievement:</strong> {step.details.achievements}</p>
                <p>
                  <strong>Tools &amp; Tech:</strong>{" "}
                  <span className="highlight-gold">{step.details.tools}</span>
                </p>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}