import Image from "next/image";
import SkillTabs from "./components/SkillTabs";
import ProjectTabs from "./components/ProjectTabs";
import Journey from "./components/Journey";
import ThemeToggle from "./components/ThemeToggle";

const education = [
  {
    degree: "Bachelor of Science (B.Sc.)",
    major: "Information Technology",
    institution: "Thai-Nichi Institute of Technology (TNI)",
    period: "2024 – 2026",
    gpa: "GPAX 3.68",
  },

  {
    degree: "High Vocational Certificate (ปวส.)",
    major: "Mechatronics",
    institution: "EEC Engineer Laemchabang College (TLC)",
    period: "2013 – 2017",
    gpa: "GPAX 3.57",
  },
];

const skills = [
  {
    category: "Web Development",
    items: [
      "Next.js (App Router, Server/Client Components), React (components, props, useState), JavaScript",
      "Frontend: Tailwind CSS, next/font, next/image — migrated this site from PHP to Next.js",
      "PHP 8 (PDO) & MySQL: relational design, transactions, row locking (SELECT … FOR UPDATE)",
      "REST API (PHP): JSON, HTTP status codes (2xx / 4xx / 5xx), stateless, input validation",
      "Web QA: testing against user manuals, bug reports, DevTools Network debugging",
      "Technical Writing: README, Architecture Decisions, Test Evidence",
    ],
  },

  {
    category: "Cloud & DevOps",
    items: [
      "AWS Multi-Account: Organizations, SCP, RAM Shared VPC, Transit Gateway",
      "Infrastructure as Code: Terraform / OpenTofu + Terragrunt (layered, per environment)",
      "CI/CD: GitLab CI (OIDC, plan → manual approve → apply), GitHub Actions",
      "Containers: AWS ECS on EC2 (awsvpc, per-task security groups), Docker & Docker Compose",
      "AWS Services: EC2, ALB, Aurora Serverless v2, S3, VPC Endpoints, Systems Manager",
      "Security: GuardDuty, Security Hub, Macie (delegated admin), checkov IaC scanning",
      "Monitoring: CloudWatch Alarms + SNS, Prometheus, Grafana, node_exporter, cAdvisor",
      "SBOM in CI (Trivy, CycloneDX); Kubernetes lab: k3s Deployment/Service, ArgoCD attempt + incident write-up",
      "Nginx Reverse Proxy: multi-domain HTTPS (Let\'s Encrypt / Certbot), path routing with runtime DNS resolve",
      "App security on deploy: least-privilege DB user, secrets in .env (not in git), nginx -t before reload",
      "Linux System Admin (Ubuntu), Git / GitHub / GitLab",
    ],
  },

  {
    category: "Mobile Development (Android)",
    items: [
      "Android: Kotlin, Jetpack Compose, Material3",
      "MVVM: ViewModel, StateFlow, SavedStateHandle",
      "Maps & Location: osmdroid (OpenStreetMap), Fused Location, Haversine",
    ],
  },

  {
    category: "OT & Industrial Instrumentation",
    items: [
      "FLUKE 789 (Process Loop Testing)",
      "FLUKE 1507 Insulation Tester",
      "HART Protocol Communicator",
      "Temperature & Pressure Calibrators",
      "QA/QC Loop Testing & P&ID Audit",
    ],
  },

  {
    category: "Telecom & Physical Network",
    items: [
      "Fusion Splicer & OTDR Testing",
      "VFL (Red Light) & Power Meter (-dBm)",
      "PON / OLT / Dropwire Infrastructure",
      "Cisco Network & TCP/IP",
      "Spanning Tree Protocol & Mesh WiFi",
    ],
  },

  {
    category: "Enterprise Low-Code & Analytics",
    items: [
      "Microsoft Power Apps & Power Automate",
      "Google Apps Script Automation",
      "Power BI (DAX, Data Modeling)",
      "Looker Studio Interactive Dashboards",
      "Python & Excel VBA Log Analytics ETL",
    ],
  },
];

const journey = [
  {
    id: "role-1",
    year: "Nov 2026 - Present",
    role: "Programmer",
    org: "Exellent Digital Development Co., Ltd.",
    note: "เริ่มงานสาย Developer เต็มตัว พัฒนาเว็บด้วย Next.js + FastAPI",
    details:
        {
          responsibilities: "พัฒนาเว็บแอปในทีม Project และทดสอบระบบเทียบกับคู่มือผู้ใช้",
          achievements: "TBD",
          tools: "Next.js, React, FastAPI, Tailwind CSS, Git",
        },
  },

  {
    id: "role-2",
    year: "Sep 2021 – Aug 2026",
    role: "Network Ops & Technical QA Specialist",
    org: "Wire & Wireless (True Group)",
    note: "ประสานงานข้ามทีม คุม SLA และทำ Workflow Automation ลดเวลา Audit ลง 50%",
    details: {
      responsibilities:
        "ทำหน้าที่เป็น Router กลาง ประสานงานระหว่าง Customer Experience (ฝั่งหน้าบ้าน) กับ Engineering Teams (ฝั่งหลังบ้าน) — เฝ้าระวัง real-time alarms (Dropwire/OLT), ทำ Alarm Triage และคุมมาตรฐานงานติดตั้งภาคสนามตาม SOPs",
      achievements:
        "พัฒนา Excel VBA ETL Tool สำหรับวิเคราะห์ Log อุปกรณ์กว่า 100+ รายการต่อวัน ช่วยลดเวลา Manual Audit ลง 50% พร้อมดูแลค่า Optical Power (-dBm) เพื่อป้องกันปัญหา Downtime และรักษา SLA อย่างเคร่งครัด",
      tools:
        "OTDR, VFL, Fusion Splicer, Optical Power Meter, Excel VBA ETL, Alarm Triage, Spanning Tree, Mesh WiFi, Cisco Commands",
    },
  },

  {
    id: "role-3",
    year: "2017 – 2021",
    role: "Project Planner & Technical Coordinator",
    org: "ABB Limited",
    note: "คุม Data Tracking, S-Curve และ Progress Billing โครงการ Field Instruments 1,000+ รายการ",
    details: {
      responsibilities:
        "บริหารจัดการ Data Tracking รายการเครื่องมือวัดภาคสนามกว่า 1,000+ รายการ (Transmitters, Primary Elements, Gauges, Control Valves) — ประสานงานข้ามทีมระหว่าง Engineering, QA/QC และ Site Teams พร้อมจัดทำ S-Curve และรายงาน Daily/Weekly/Monthly เสนอผู้บริหาร",
      achievements:
        "คุมระบบติดตามสถานะและทำเอกสารเบิกจ่าย (Progress Billing) ตามงวดงาน ช่วยให้โครงการส่งมอบได้ตรงตาม Master Schedule",
      tools:
        "MS Project, S-Curve Tracking, Advanced Excel, Progress Billing, P&ID Audit, Field Instruments Data Management",
    },
  },

  {
    id: "role-4",
    year: "2013 – 2017",
    role: "Instrument QA/QC Technician",
    org: "ABB Limited",
    note: "ทดสอบ Megger, Calibration และทำ Loop / Function Test เครื่องมือวัดภาคสนาม",
    details: {
      responsibilities:
        "ลงพื้นที่โรงงานอุตสาหกรรมทำการทดสอบ Continuity Test, Megger Test (Insulation Resistance) เพื่อเช็กสายรั่ว/สายขาด พร้อมทำ Calibration และ Loop Test เครื่องมือวัดภาคสนาม (Transmitters/Gauges) ตามมาตรฐาน HART Protocol และ P&ID Specifications",
      achievements:
        "จัดทำเอกสารบันทึกผลการทดสอบ (QA/QC Test Reports) และผ่านการทำ Post-Loop / Function Test ส่งมอบระบบเครื่องมือวัดกว่า 1,000+ รายการได้ถูกต้องและปลอดภัย 100%",
      tools:
        "FLUKE 789 (Loop Calibrator), FLUKE 1507 (Megger/Insulation Tester), HART Communicator, Pressure Hand Pump, P&ID Audit, QA/QC Documentation",
    },
  },

  {
    id: "role-5",
    year: "2011 – 2013",
    role: "Project Document Controller",
    org: "ABB Limited",
    note: "จุดเริ่มต้นชีวิตการทำงานตั้งแต่อายุ 18 (ส่งตัวเองเรียน) ดูแลเอกสารวิศวกรรมและจัดเก็บข้อมูลดิจิทัล",
    details: {
      responsibilities:
        "เริ่มทำงานตั้งแต่อายุ 18 ปีเพื่อส่งตัวเองเรียน — บริหารจัดการระบบรับ-ส่งเอกสารวิศวกรรม (Engineering Drawing, P&ID, Transmittal Forms) ให้แก่ทีมวิศวกรและโครงการตามมาตรฐานองค์กร",
      achievements:
        "เปลี่ยนระบบการจัดเก็บเอกสารแบบเดิมมาทำ Basic Digital Tracking ช่วยให้ทีมค้นหาแบบแปลนและรายงานวิศวกรรมได้สะดวก รวดเร็ว และเป็นหมวดหมู่",
      tools:
        "MS Office (Word, Excel), AutoCAD Viewer, Adobe Acrobat, Email Communication, Master Document Register (MDR)",
    },
  },
];

const projects = [
  {
    title: "AWS Multi-Account Landing Zone — Terraform + Terragrunt + GitLab CI",
    summary:
      "งานทดสอบ (take-home) ตำแหน่ง DevOps Engineer: สร้างโครงสร้าง AWS 5 บัญชีตามแผนภาพที่ได้รับ ด้วย IaC ทั้งหมด แล้ว apply จริงผ่าน pipeline ทดสอบ และ destroy กลับจนหมด พร้อมเอกสารบันทึกหลักฐานและข้อจำกัดอย่างตรงไปตรงมา",
    facts: [
      "5 บัญชีใน AWS Organizations: Management, Security, Network, Prod, Nonprod",
      "Shared VPC ผ่าน AWS RAM + Transit Gateway แยก prod / nonprod",
      "ECS on EC2 แบบ awsvpc: 10 แอปใน 6 security groups ตามแผนภาพ, ALB แยก path, Aurora Serverless v2",
      "GitLab CI แบบ OIDC (ไม่มี access key ค้าง): check → checkov scan → plan → อนุมัติด้วยมือ → apply",
      "CloudWatch Alarms 4 ชนิด ส่งอีเมลผ่าน SNS ทดสอบยิงจริงครบ ทั้ง ALARM และ OK",
      "หาสาเหตุ ECS ค้าง DRAINING ตอน destroy จาก log และแก้ด้วยลำดับ dependency ใน Terraform",
    ],
    links: [
      { label: "GitLab Repo", url: "https://gitlab.com/whiznuri/aws-landing-zone" },
      { label: "Demo Video", url: "https://youtu.be/_pETr658fA4" },
    ],
  },
  {
    title: "Diff's Journey — Android Portfolio App with Map",
    summary:
      "แอป Android แนะนำตัวผ่านตัวละครทามาก็อตจิที่เปลี่ยนรูปตาม 13 หมุดชีวิต แต่ละหมุดผูกกับสถานที่จริงบนแผนที่ในแอป วัดระยะจากตำแหน่งผู้ใช้ และส่งต่อไป Google Maps เพื่อนำทาง",
    facts: [
      "Kotlin + Jetpack Compose, สถาปัตยกรรม MVVM (ViewModel, StateFlow, SavedStateHandle)",
      "แผนที่ osmdroid (OpenStreetMap) ไม่ต้องใช้ API key: Marker 13 หมุด + เส้น timeline",
      "ขอเฉพาะตำแหน่งโดยประมาณ (least privilege) คำนวณระยะด้วย Haversine ปฏิเสธสิทธิ์แล้วยังใช้งานต่อได้",
      "ตรวจเครือข่ายก่อนเปิดแผนที่, รองรับจอเล็กและฟอนต์ใหญ่, state รอดการสร้าง Activity ใหม่",
      "เอกสาร Concept, Flow diagram และตารางผลทดสอบบน emulator",
    ],
    links: [
      { label: "GitLab Repo", url: "https://gitlab.com/whiznuri/DiffsJourney" },
      { label: "Demo Video", url: "https://youtu.be/bJqvqsokXXw" },
    ],
  },
  {
    title: "Lovely Pet — Full-Stack App on a Self-Managed AWS Cloud Stack",
    summary:
      "ระบบ Web Application บริหารจัดการข้อมูลสัตว์เลี้ยงแบบ Full-Stack รันอยู่บน AWS EC2 ที่ provision ด้วย Terraform (IaC) พร้อม CI/CD Pipeline (GitHub Actions → Docker Hub → automated deployment), Nginx Reverse Proxy พร้อม HTTPS หลาย subdomain และ Monitoring Stack ที่ทำงานอยู่จริง",
    facts: [
      "Infrastructure as Code: Terraform provisioning EC2, Security Group, Elastic IP",
      "CI/CD: Push code → Auto build & push image ขึ้น Docker Hub → Deploy อัตโนมัติ",
      "Nginx Reverse Proxy + Multi-domain HTTPS (Let's Encrypt)",
      "Monitoring: Prometheus + Grafana + node_exporter + cAdvisor",
      "Relational Database Design & Back-office Auth System",
      "SBOM ของทุก image ใน CI (Trivy, CycloneDX) และบันทึก incident จริง เช่น ทดลอง k3s แล้วพอร์ต 80/443 ใช้ไม่ได้",
    ],
    links: [
      { label: "Live Demo", url: "https://lovelypet.diffan.dev" },
      { label: "GitHub Repo", url: "https://github.com/whiznuri/aws-devops-resume-stack" },
      { label: "Docker Hub", url: "https://hub.docker.com/repositories/whiznuri363924an" },
    ],
  },
  {
    title: "Mini Shopping — PHP + MySQL E-commerce with Transactional Stock",
    summary:
      "ร้านค้าออนไลน์ขนาดเล็กเขียนด้วย PHP 8 (PDO) + MySQL มีทั้งหน้าร้าน (สมัครสมาชิก ตะกร้า สั่งซื้อ ประวัติออเดอร์) และหลังบ้าน (จัดการสินค้า สต็อก รายงานยอดขาย) เน้นความถูกต้องของข้อมูลเมื่อมีคนสั่งพร้อมกัน แล้ว deploy เป็นแอปที่ 3 ใน pipeline เดียวกับเว็บนี้",
    facts: [
      "ตะกร้า → 1 ออเดอร์หลายรายการ ออกแบบแบบ header/detail (orders / order_items) และเก็บราคา ณ วันที่สั่ง",
      "ตัดสต็อกใน transaction เดียวด้วย SELECT … FOR UPDATE ล็อกแถวเรียงตาม id ลดโอกาส deadlock — ชิ้นไหนไม่พอ rollback ทั้งออเดอร์",
      "หลังบ้าน: รับของเข้าแบบบวกเพิ่ม (stock = stock + ?) กัน lost update, ระงับการขายแทนการลบ (soft delete), รายงานยอดขาย LEFT JOIN + GROUP BY",
      "Security: prepared statements, password_hash (bcrypt), session_regenerate_id, escape output กัน XSS, DB user มีสิทธิ์แค่ SELECT / INSERT / UPDATE",
      "REST API แบบอ่านอย่างเดียว ตอบ JSON พร้อม status code ที่ถูกต้อง (200 / 400 / 404 / 405 / 500) ภาษาไทยอ่านได้ ไม่ใช้ session (stateless)",
      "Deploy: GitHub Actions → Docker Hub → EC2, Nginx resolve ชื่อ container ตอน request ถ้าแอปล่มจะกระทบแค่ /shop ไม่ลากเว็บอื่น",
    ],
    links: [
      { label: "Live Demo", url: "https://diffan.dev/shop/" },
      { label: "API (JSON)", url: "https://diffan.dev/shop/api/products.php" },
      { label: "Source", url: "https://github.com/whiznuri/aws-devops-resume-stack/tree/main/apps/ministock" },
    ],
  },
];

export default function Home() {
  return (
    <>
      <section className="hero">
        <div className="hero__container">
          <div className="hero__inner">
            <span className="badge">Hardware to Developer</span>
            <h1 className="hero__name">Anuphan "Diff" Natee</h1>
            <p className="hero__lede">
              ประสบการณ์ 15 ปีจาก Hardware ถึง Cloud ผมออกแบบระบบให้เสถียร ตรวจสอบได้ และใช้ได้จริงในธุรกิจ
            </p>
            <a className="hero__cta" href="javascript:void(0)" data-target="work">ดูผลงานโปรเจกต์</a>
          </div>

          <div className="hero__image-wrapper">
            <Image
              src="/Profile.jpg"
              alt="Anuphan Natee"
              className="hero__image"
              width={768}
              height={960}
              preload
            />
          </div>
        </div>
      </section>

      <section id="journey" className="journey">
        <h2 className="section-title">Transition Journey</h2>
        <p className="section-subtitle">* คลิกที่ตำแหน่งงานเพื่อดูรายละเอียดเชิงลึก (Responsibilities & Impact)</p>
        <Journey journey={journey} />
      </section>

      <section id="skills" className="skills-section">
        <h2 className="section-title">Comprehensive Skill Matrix</h2>
        <SkillTabs skills={skills} />
      </section>

      <section id="education" className="education">
        <h2 className="section-title">Education</h2>
        <div className="education__grid">
          {education.map((edu) => (
            <div className="education__card" key={edu.degree}>
              <div className="education__header">
                <div>
                  <h3 className="education__degree">{edu.degree}</h3>
                  <p className="education__major">{edu.major}</p>
                  <p className="education__institution">{edu.institution}</p>
                </div>
                <div className="education__meta">
                  <span className="education__period">{edu.period}</span>
                  <span className="education__gpa">{edu.gpa}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>

      <section id="work" className="showcase">
        <h2 className="section-title">Project Showcase</h2>
        <ProjectTabs projects={projects} />
      </section>

      <section id="contact" className="contact">
        <h2 className="section-title">Get In Touch</h2>
        <div className="contact__container">
          <a href="mailto:anuphan.natee@hotmail.com" className="contact__item">anuphan.natee@hotmail.com</a>
          <span className="contact__dot">•</span>
          <a href="tel:0963935939" className="contact__item">096-393-5939</a>
          <span className="contact__dot">•</span>
          <span className="contact__item">Phayathai, Bangkok, Thailand</span>
        </div>
      </section>

    </>

  );
}
