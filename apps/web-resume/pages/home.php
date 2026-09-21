<?php
// ข้อมูล Timeline แบบจัดเต็มรายละเอียด 4 มิติ
$journey = [
  [
    'id' => 'role-1',
    'year' => 'Available Immediately',
    'role' => 'Hardware-to-Cloud Reliability Engineer',
    'org' => 'Open for Roles / Transition',
    'note' => 'ผสานประสบการณ์ OT/Network 15 ปี สู่ Enterprise Automation & AWS Infrastructure',
    'details' => [
      'responsibilities' => 'ออกแบบและปรับปรุงสถาปัตยกรรม Web Platform, Automation Workflow และ Cloud Deployment สำหรับระบบงานจริง',
      'achievements' => 'สร้าง Modular PHP Portfolio บน AWS EC2 คอนฟิก Clean Router ซ่อน Hash URL ด้วย Vanilla JS และวางระบบ Database-backed Architecture',
      'tools' => 'AWS EC2 (Ubuntu), Apache mod_rewrite, PHP 8+, Pure CSS, Git, Docker Container Basics'
    ]
  ],
  [
    'id' => 'role-2',
    'year' => 'Sep 2021 – Aug 2026',
    'role' => 'Network Ops & Technical QA Specialist',
    'org' => 'Wire & Wireless (True Group)',
    'note' => 'ประสานงานข้ามทีม คุม SLA และทำ Workflow Automation ลดเวลา Audit ลง 50%',
    'details' => [
      'responsibilities' => 'ทำหน้าที่เป็น Router กลาง ประสานงานระหว่าง Customer Experience (ฝั่งหน้าบ้าน) กับ Engineering Teams (ฝั่งหลังบ้าน) — เฝ้าระวัง real-time alarms (Dropwire/OLT), ทำ Alarm Triage และคุมมาตรฐานงานติดตั้งภาคสนามตาม SOPs',
      'achievements' => 'พัฒนา Excel VBA ETL Tool สำหรับวิเคราะห์ Log อุปกรณ์กว่า 100+ รายการต่อวัน ช่วยลดเวลา Manual Audit ลง 50% พร้อมดูแลค่า Optical Power (-dBm) เพื่อป้องกันปัญหา Downtime และรักษา SLA อย่างเคร่งครัด',
      'tools' => 'OTDR, VFL, Fusion Splicer, Optical Power Meter, Excel VBA ETL, Alarm Triage, Spanning Tree, Mesh WiFi, Cisco Commands'
    ]
  ],
  [
    'id' => 'role-3',
    'year' => '2017 – 2021',
    'role' => 'Project Planner & Technical Coordinator',
    'org' => 'ABB Limited',
    'note' => 'คุม Data Tracking, S-Curve และ Progress Billing โครงการ Field Instruments 1,000+ รายการ',
    'details' => [
      'responsibilities' => 'บริหารจัดการ Data Tracking รายการเครื่องมือวัดภาคสนามกว่า 1,000+ รายการ (Transmitters, Primary Elements, Gauges, Control Valves) — ประสานงานข้ามทีมระหว่าง Engineering, QA/QC และ Site Teams พร้อมจัดทำ S-Curve และรายงาน Daily/Weekly/Monthly เสนอผู้บริหาร',
      'achievements' => 'คุมระบบติดตามสถานะและทำเอกสารเบิกจ่าย (Progress Billing) ตามงวดงาน ช่วยให้โครงการส่งมอบได้ตรงตาม Master Schedule',
      'tools' => 'MS Project, S-Curve Tracking, Advanced Excel, Progress Billing, P&ID Audit, Field Instruments Data Management'
    ]
  ],
  [
    'id' => 'role-4',
    'year' => '2013 – 2017',
    'role' => 'Instrument QA/QC Technician',
    'org' => 'ABB Limited',
    'note' => 'ทดสอบ Megger, Calibration และทำ Loop / Function Test เครื่องมือวัดภาคสนาม',
    'details' => [
      'responsibilities' => 'ลงพื้นที่โรงงานอุตสาหกรรมทำการทดสอบ Continuity Test, Megger Test (Insulation Resistance) เพื่อเช็กสายรั่ว/สายขาด พร้อมทำ Calibration และ Loop Test เครื่องมือวัดภาคสนาม (Transmitters/Gauges) ตามมาตรฐาน HART Protocol และ P&ID Specifications',
      'achievements' => 'จัดทำเอกสารบันทึกผลการทดสอบ (QA/QC Test Reports) และผ่านการทำ Post-Loop / Function Test ส่งมอบระบบเครื่องมือวัดกว่า 1,000+ รายการได้ถูกต้องและปลอดภัย 100%',
      'tools' => 'FLUKE 789 (Loop Calibrator), FLUKE 1507 (Megger/Insulation Tester), HART Communicator, Pressure Hand Pump, P&ID Audit, QA/QC Documentation'
    ]
  ],
  [
    'id' => 'role-5',
    'year' => '2011 – 2013',
    'role' => 'Project Document Controller',
    'org' => 'ABB Limited',
    'note' => 'จุดเริ่มต้นชีวิตการทำงานตั้งแต่อายุ 18 (ส่งตัวเองเรียน) ดูแลเอกสารวิศวกรรมและจัดเก็บข้อมูลดิจิทัล',
    'details' => [
      'responsibilities' => 'เริ่มทำงานตั้งแต่อายุ 18 ปีเพื่อส่งตัวเองเรียน — บริหารจัดการระบบรับ-ส่งเอกสารวิศวกรรม (Engineering Drawing, P&ID, Transmittal Forms) ให้แก่ทีมวิศวกรและโครงการตามมาตรฐานองค์กร',
      'achievements' => 'เปลี่ยนระบบการจัดเก็บเอกสารแบบเดิมมาทำ Basic Digital Tracking ช่วยให้ทีมค้นหาแบบแปลนและรายงานวิศวกรรมได้สะดวก รวดเร็ว และเป็นหมวดหมู่',
      'tools' => 'MS Office (Word, Excel), AutoCAD Viewer, Adobe Acrobat, Email Communication, Master Document Register (MDR)'
    ]
  ]
];

// ข้อมูล Skills 4 มิติ
$skills_4d = [
  'OT & Industrial Instrumentation' => [
    'FLUKE 789 (Process Loop Testing)', 'FLUKE 1507 Insulation Tester',
    'HART Protocol Communicator', 'Temperature & Pressure Calibrators',
    'QA/QC Loop Testing & P&ID Audit'
  ],
  'Telecom & Physical Network' => [
    'Fusion Splicer & OTDR Testing', 'VFL (Red Light) & Power Meter (-dBm)',
    'PON / OLT / Dropwire Infrastructure', 'Cisco Network & TCP/IP',
    'Spanning Tree Protocol & Mesh WiFi'
  ],
  'Enterprise Low-Code & Analytics' => [
    'Microsoft Power Apps & Power Automate', 'Google Apps Script Automation',
    'Power BI (DAX, Data Modeling)', 'Looker Studio Interactive Dashboards',
    'Python & Excel VBA Log Analytics ETL'
  ],
  'Cloud, DevOps & Software' => [
    'AWS EC2 & Linux System Admin (Ubuntu)',
    'Infrastructure as Code (Terraform) — EC2, Security Group, Elastic IP',
    'Docker Containerization (Dockerfile, Docker Compose)',
    'CI/CD Pipeline (GitHub Actions → Docker Hub → automated deployment)',
    'Nginx Reverse Proxy & Multi-domain Routing',
    'SSL/TLS Certificate Management (Let\'s Encrypt / Certbot)',
    'Monitoring & Observability (Prometheus, Grafana, node_exporter, cAdvisor)',
    'Modular PHP & MySQL Relational DB',
    'Git / GitHub Version Control Workflows'
  ]
];

// ข้อมูลการศึกษา
$education = [
  [
    'degree' => 'Bachelor of Science (B.Sc.)',
    'major' => 'Information Technology',
    'institution' => 'Thai-Nichi Institute of Technology (TNI)',
    'period' => '2024 – 2026',
    'gpa' => 'GPAX 3.68'
  ],
  [
    'degree' => 'High Vocational Certificate (ปวส.)',
    'major' => 'Mechatronics',
    'institution' => 'EEC Engineer Laemchabang College (TLC)',
    'period' => '2013 – 2017',
    'gpa' => 'GPAX 3.57'
  ]
];
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero__container">
    <div class="hero__inner">
      <span class="badge">Hardware to Cloud Engineer</span>
      <h1 class="hero__name">Anuphan "Diff" Natee</h1>
      <p class="hero__lede">
        ประสบการณ์ 15 ปีจากงาน Field Instrumentation, Fiber Infrastructure สู่การสร้าง Enterprise Automation และ Cloud Reliability
        หล่อหลอมให้ทุกระบบที่ออกแบบ เน้นความเสถียร ตรวจสอบได้ และใช้งานได้จริงในทางธุรกิจ
      </p>
      <a class="hero__cta" href="javascript:void(0)" data-target="work">ดูผลงานโปรเจกต์</a>
    </div>

    <div class="hero__image-wrapper">
      <img src="/assets/Profile.jpg" alt="Anuphan Natee" class="hero__image">
    </div>
  </div>
</section>

<!-- Timeline Journey (Expandable Accordion) -->
<section id="journey" class="journey">
  <h2 class="section-title">Transition Journey</h2>
  <p class="section-subtitle">* คลิกที่ตำแหน่งงานเพื่อดูรายละเอียดเชิงลึก (Responsibilities & Impact)</p>

  <div class="timeline-accordion">
    <?php foreach ($journey as $step): ?>
    <div class="timeline-item" id="<?= $step['id'] ?>">
      <div class="timeline-header" onclick="toggleExpand('<?= $step['id'] ?>')">
        <span class="timeline-year"><?= htmlspecialchars($step['year']) ?></span>
        <div class="timeline-title-group">
          <h3 class="timeline-role"><?= htmlspecialchars($step['role']) ?></h3>
          <p class="timeline-org"><?= htmlspecialchars($step['org']) ?></p>
        </div>
        <span class="expand-icon">+</span>
      </div>

      <div class="timeline-body">
        <p class="timeline-note"><?= htmlspecialchars($step['note']) ?></p>
        <hr class="divider">
        <div class="timeline-details">
          <p><strong>Responsibilities:</strong> <?= htmlspecialchars($step['details']['responsibilities']) ?></p>
          <p><strong>Key Achievement:</strong> <?= htmlspecialchars($step['details']['achievements']) ?></p>
          <p><strong>Tools & Tech:</strong> <span class="highlight-gold"><?= htmlspecialchars($step['details']['tools']) ?></span></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 4D Skill Grid Section -->
<section id="skills" class="skills-section">
  <h2 class="section-title">Comprehensive Skill Matrix</h2>
  <div class="skills-grid">
    <?php foreach ($skills_4d as $category => $items): ?>
      <div class="skill-card">
        <h3 class="skill-card__title"><?= htmlspecialchars($category) ?></h3>
        <ul class="skill-card__list">
          <?php foreach ($items as $skill): ?>
            <li><?= htmlspecialchars($skill) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Education Section -->
<section id="education" class="education">
  <h2 class="section-title">Education</h2>
  <div class="education__grid">
    <?php foreach ($education as $edu): ?>
      <div class="education__card">
        <div class="education__header">
          <div>
            <h3 class="education__degree"><?= htmlspecialchars($edu['degree']) ?></h3>
            <p class="education__major"><?= htmlspecialchars($edu['major']) ?></p>
            <p class="education__institution"><?= htmlspecialchars($edu['institution']) ?></p>
          </div>
          <div class="education__meta">
            <span class="education__period"><?= htmlspecialchars($edu['period']) ?></span>
            <span class="education__gpa"><?= htmlspecialchars($edu['gpa']) ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Project Showcase — Live Demo ชี้ไปที่ Lovely Pet เท่านั้น (ไม่ชี้กลับมาหน้าตัวเอง) -->
<section id="work" class="showcase">
  <h2 class="section-title">Project Showcase</h2>
  <article class="showcase__card">
    <div class="showcase__text">
      <h3>Lovely Pet — Full-Stack App on a Self-Managed AWS Cloud Stack</h3>
      <p>
        ระบบ Web Application บริหารจัดการข้อมูลสัตว์เลี้ยงแบบ Full-Stack รันอยู่บน AWS EC2 ที่ provision
        ด้วย Terraform (IaC) พร้อม CI/CD Pipeline (GitHub Actions → Docker Hub → automated deployment),
        Nginx Reverse Proxy พร้อม HTTPS หลาย subdomain และ Monitoring Stack ที่ทำงานอยู่จริง
      </p>
      <ul class="showcase__facts">
        <li>Infrastructure as Code: Terraform provisioning EC2, Security Group, Elastic IP</li>
        <li>CI/CD: Push code → Auto build & push image ขึ้น Docker Hub → Deploy อัตโนมัติ</li>
        <li>Nginx Reverse Proxy + Multi-domain HTTPS (Let's Encrypt)</li>
        <li>Monitoring: Prometheus + Grafana + node_exporter + cAdvisor</li>
        <li>Relational Database Design & Back-office Auth System</li>
      </ul>
    </div>
    <div class="showcase__meta">
      <a href="https://lovelypet.diffan.dev" class="showcase__link" target="_blank">Live Demo</a>
      <a href="https://github.com/whiznuri/aws-devops-resume-stack" class="showcase__link" target="_blank">GitHub Repo</a>
      <a href="https://hub.docker.com/repositories/whiznuri363924an" class="showcase__link" target="_blank">Docker Hub</a>
    </div>
  </article>
</section>

<!-- Contact Section -->
<section id="contact" class="contact">
  <h2 class="section-title">Get In Touch</h2>
  <div class="contact__container">
    <a href="mailto:anuphan.natee@hotmail.com" class="contact__item">anuphan.natee@hotmail.com</a>
    <span class="contact__dot">•</span>
    <a href="tel:0963935939" class="contact__item">096-393-5939</a>
    <span class="contact__dot">•</span>
    <span class="contact__item">Rayong, Thailand</span>
  </div>
</section>

<script>
function toggleExpand(id) {
  const item = document.getElementById(id);
  if (!item) return;

  item.classList.toggle('active');

  const icon = item.querySelector('.expand-icon');
  if (icon) {
    icon.textContent = item.classList.contains('active') ? '−' : '+';
  }
}
</script>
