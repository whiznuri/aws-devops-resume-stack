# AWS End-to-End DevOps Resume & Monitoring Stack

A self-hosted, containerized resume and portfolio platform built to demonstrate practical DevOps skills — from infrastructure provisioning to CI/CD automation and observability — using a real production-style deployment on AWS.

Built by **Anuphan "Diff" Natee**, transitioning from 15 years of Network Operations / Field Instrumentation into DevOps & Cloud Infrastructure.

---

## 🏗️ Architecture Overview

- **Infrastructure as Code (IaC):** Terraform — provisions the EC2 instance, Security Group, and Elastic IP (single-instance by design, to keep AWS Free Tier costs predictable)
- **Hosting:** AWS EC2 (Ubuntu)
- **Containerization:** Docker & Docker Compose
- **Reverse Proxy & TLS:** Nginx reverse proxy routing multiple subdomains, HTTPS via Let's Encrypt / Certbot, with automated certificate renewal
- **CI/CD:** GitHub Actions — build → push to Docker Hub → automated deployment for both applications
- **Observability:** Prometheus & Grafana, with `node_exporter` (host metrics) and `cAdvisor` (container metrics)

```
                 ┌─────────────────┐
   git push  ──▶  │ GitHub Actions  │──▶ Build Image ──▶ Push to Docker Hub ──▶ Deploy to EC2
                 └─────────────────┘
                                                                    │
                                                                    ▼
                 ┌──────────────────────────────────────────────────────┐
                 │                    AWS EC2 (Ubuntu)                   │
                 │           Provisioned via Terraform (IaC)             │
                 │  ┌───────────────────────────────────────────────┐   │
                 │  │              Nginx Reverse Proxy (HTTPS)       │   │
                 │  └───────┬─────────────────┬─────────────────────┘   │
                 │          ▼                 ▼                         │
                 │   diffan.dev      lovelypet.diffan.dev   grafana.diffan.dev
                 │   (PHP Resume)    (PHP + MySQL App)      (Prometheus + Grafana)
                 └──────────────────────────────────────────────────────┘
```

---

## 📁 Directory Structure

```text
aws-devops-resume-stack/
├── .github/
│   └── workflows/          # CI/CD: build, push to Docker Hub, deploy (both apps)
├── terraform/               # IaC: EC2, Security Group, Elastic IP
│   ├── main.tf
│   ├── variables.tf
│   └── outputs.tf
├── apps/
│   ├── web-resume/          # PHP web resume (this site)
│   └── lovelypet/            # PHP + MySQL full-stack app
├── nginx/                   # Reverse proxy config, subdomain routing, SSL
├── docker-compose.yml
└── monitoring/
    ├── prometheus.yml        # Scrape config (node_exporter, cAdvisor)
    └── grafana/               # Dashboard provisioning
```

---

## ✅ What's Implemented

- [x] Infrastructure as Code: Terraform provisioning EC2, Security Group, Elastic IP
- [x] Dockerized PHP applications (web resume + Lovely Pet)
- [x] CI/CD pipeline: push to `main` → GitHub Actions builds & pushes both app images to Docker Hub → automated deployment
- [x] Nginx reverse proxy with multi-subdomain routing
- [x] HTTPS across all services via Let's Encrypt / Certbot
- [x] Automated SSL renewal via cron (checks and renews monthly, reloads Nginx automatically)
- [x] Prometheus + Grafana monitoring with `node_exporter` (host metrics) and `cAdvisor` (container metrics)

## 🧯 Real Incidents Resolved

Operational issues hit and fixed during development — kept here as a record of hands-on troubleshooting, not just a feature list:

- **Nginx `502 Bad Gateway` after container recreation** — Docker assigns a new internal IP each time a container is recreated, so the reverse proxy held a stale upstream IP. Fixed by reloading Nginx (`nginx -s reload`) immediately after every deployment to force a DNS re-resolution.
- **EC2 disk full, causing Docker builds to hang** — Cleared unused images/containers (`docker image prune -f`) to restore build capacity; a recurring maintenance step to watch on a small Free Tier instance.
- **CI/CD only building one of two apps** — The original pipeline only built `web-resume`, so `lovelypet` had no `:latest` image on Docker Hub and deployment failed. Restructured `deploy.yml` to build and push both apps explicitly, then deploy only those two services with `--remove-orphans`.

## 🛣️ Roadmap

- [ ] Grafana Alerting → Telegram/LINE notifications on high CPU/RAM or container downtime
- [ ] Multi-node infrastructure (ephemeral, spun up on demand) for orchestration practice

---

## 🎯 Why This Project

This stack isn't a tutorial clone — it's built to mirror how a real small-to-mid-size engineering team ships and operates software: infrastructure as code, containerized services, automated builds, and visibility into what's running. It's a deliberate bridge between 15 years of hands-on infrastructure/network operations and modern cloud-native DevOps practice.

---

## ⚠️ Repository Hygiene Notes

This repo intentionally excludes Terraform state files (`*.tfstate`), `.terraform/`, and any credential files — see `.gitignore`. State files contain live AWS account and resource identifiers and should never be committed to a public repository.

---

## 📬 Contact

- Email: anuphan.natee@hotmail.com
- Phone: 096-393-5939
- Location: Rayong, Thailand
