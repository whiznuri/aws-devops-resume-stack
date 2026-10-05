# AWS End-to-End DevOps Resume & Monitoring Stack

A self-hosted, containerized resume and portfolio platform on AWS. I built it to practice infrastructure as code, CI/CD and observability on a real deployment, using a single EC2 instance on the AWS Free Tier.

Built by **Anuphan "Diff" Natee**, moving into DevOps after about 15 years in field instrumentation and fiber/network infrastructure.

Live: [diffan.dev](https://diffan.dev) · [lovelypet.diffan.dev](https://lovelypet.diffan.dev) · [grafana.diffan.dev](https://grafana.diffan.dev)

This is a learning project, not a production-grade setup: one instance, no high availability, and a list of known gaps at the end of this file.

---

## Architecture

- **Infrastructure as Code:** Terraform provisions the EC2 instance (t3.micro, Ubuntu), Security Group, Elastic IP and a 30 GB root volume. Single instance by design, to keep Free Tier costs predictable.
- **Runtime:** Docker Compose runs Nginx, two PHP apps, MySQL 8, Prometheus, Grafana, node_exporter and cAdvisor.
- **Reverse proxy and TLS:** Nginx routes three subdomains. HTTPS uses Let's Encrypt; Certbot (installed as a snap) runs on the host with the webroot challenge and renews through its systemd timer.
- **CI/CD:** GitHub Actions builds both app images on every push to `main`, pushes them to Docker Hub, then deploys to EC2 over SSH.
- **Observability:** Prometheus and Grafana, with `node_exporter` (host metrics) and `cAdvisor` (container metrics).

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

## Repository structure

```text
aws-devops-resume-stack/
├── .github/workflows/     # CI/CD: build, push to Docker Hub, deploy (both apps)
├── terraform/             # IaC: EC2, Security Group, Elastic IP
│   ├── main.tf
│   ├── variables.tf
│   └── outputs.tf
├── apps/
│   ├── web-resume/        # PHP web resume
│   └── lovelypet/         # PHP + MySQL app
├── nginx/                 # Reverse proxy config and subdomain routing
├── database/schema.sql    # MySQL schema for LovelyPet (loaded on first start)
├── monitoring/
│   └── prometheus.yml     # Scrape config (node_exporter, cAdvisor)
├── archive/               # k3s / ArgoCD lab manifests (not deployed)
└── docker-compose.yml
```

The Grafana dashboard is the community "Node Exporter Full", imported by hand. It is not provisioned as code yet.

## What is implemented

- [x] Terraform: EC2, Security Group, Elastic IP, 30 GB root volume
- [x] Dockerized PHP applications (web resume and LovelyPet)
- [x] CI/CD: push to `main` → build both images → Docker Hub → SSH deploy (`compose pull`, recreate, Nginx reload, image prune). There is no approval step, so every push to `main` deploys.
- [x] Nginx reverse proxy with multi-subdomain routing
- [x] HTTPS via Let's Encrypt / Certbot
- [x] Certificate renewal via Certbot's systemd timer (snap), webroot challenge on port 80
- [x] SBOM generation in CI: Trivy creates a CycloneDX SBOM for each image and uploads both as a GitHub Actions artifact (no vulnerability scan yet)
- [x] Prometheus and Grafana with `node_exporter` and `cAdvisor`

## Kubernetes lab (k3s + ArgoCD), 4 Oct 2026

**Goal:** try Kubernetes and pull-based CD (GitOps) with this stack.

**Setup:** single-node k3s installed on the same t3.micro (914 MiB RAM) that serves the site. That was a mistake I will not repeat: see the takeaways.

| Step | Outcome |
|---|---|
| k3s single-node cluster | Node `Ready` |
| `web-resume` as Deployment + Service (requests 50m / 64Mi, limits 100m / 128Mi) | `Running 1/1` after fixing the image reference |
| `ImagePullBackOff` on first apply | Events showed `pull access denied`. Cause: wrong Docker Hub username in the manifest. Fixed in commit `d30cb87` |
| ArgoCD install | **Not completed.** API server timeouts and pod restarts (13 to 18) under memory pressure. The Application resource was created but never reached `Synced` |

**Decision:** stop and disable k3s, delete the `argocd` namespace, and keep Docker Compose as the only runtime. The lab manifests are kept in `archive/`.

**Takeaways**
- The k3s server process alone used about 450 MB of the 914 MiB available. A control plane plus ArgoCD needs more headroom than this instance has.
- Experiments should run on a separate instance or locally, not on the host that serves the live site.
- Stopping a service is not the same as cleaning up its network state (see incident 4).
- ArgoCD sync is still unverified. I plan to retry on a larger temporary instance or a local cluster.
- The lab also left images and data on disk (root volume usage went from about 34% to about 50%). Cleanup with `k3s-uninstall.sh` is pending.

## Real incidents resolved

Problems hit and fixed during development, kept as a record of troubleshooting rather than a feature list.

1. **Nginx `502 Bad Gateway` after container recreation.** Docker assigns a new internal IP whenever a container is recreated. Nginx resolves upstream names when it loads its config, so it kept the stale IP. Fixed by reloading Nginx after every deployment. A more robust option (runtime DNS re-resolution in Nginx) is not implemented yet.
2. **EC2 disk full, causing Docker builds to hang.** The default 8 GB root volume filled up with Docker images. Cleared unused images with `docker image prune -f` (now run on every deploy) and increased the root volume to 30 GB in Terraform.
3. **CI/CD only building one of two apps.** The original pipeline built only `web-resume`, so `lovelypet` had no image on Docker Hub and the deployment failed. Restructured `deploy.yml` to build and push both apps and deploy only those two services with `--remove-orphans`.
4. **External HTTP/HTTPS timeouts after stopping k3s (4 Oct 2026).** All containers were `Up` and `curl localhost` worked, and the application ports were reachable from outside, but ports 80 and 443 timed out. Probable cause, inferred from the symptoms and the fix: network rules left behind by k3s (its bundled Traefik/ServiceLB publishes ports 80 and 443 on the host) were still intercepting traffic. Fixed by running `k3s-killall.sh`, restarting Docker and running `docker compose up -d`; the site was then reachable again. I did not capture packets, so the cause is not confirmed. Full write-up: [`docs/incidents/2026-10-04-k3s-iptables.md`](docs/incidents/2026-10-04-k3s-iptables.md).

## Known limitations and planned work

**Pipeline**
- No tests or vulnerability scanning (SBOMs are generated but not scanned or used as a gate). Tags `v1.0` and `latest` are overwritten on every run, so a rollback means reverting the commit and rebuilding. Planned: commit-SHA tags, a Trivy vulnerability scan with a failing threshold, an approval step, and rollback by redeploying an earlier tag.
- Push-based deploy over SSH. Third-party Actions are pinned by tag or branch (`appleboy/ssh-action@v1.0.3`, `aquasecurity/trivy-action@master`) rather than commit SHA. Planned: pull-based CD (ArgoCD) and pinning by SHA.

**Infrastructure**
- Terraform state is local, there are no modules or separate environments, and the AMI is selected with `most_recent`. `user_data` is empty, so software on the instance was installed by hand. Planned: remote state with locking, a pinned AMI, and cloud-init or Ansible for configuration.
- Single instance with about 80% RAM used (swap in use but stable). Planned: more headroom, or a multi-AZ design if it ever needed to scale.

**Security hardening**
- SSH is open to the internet because CI connects over SSH. Ports 3000, 8080 and 9091 are published although Nginx already serves these apps. Planned: SSM or pull-based deploy, close the redundant ports.
- IMDSv1 is still allowed and the root volume is not encrypted.
- `docker-compose.yml` contains sample credentials for the demo database. CI credentials are stored as GitHub Secrets. Planned: move database credentials to an untracked `.env` or a secret manager.

**Observability**
- No alert rules and no application-level metrics (error rate, latency). Planned: Grafana Alerting to Telegram/LINE, plus an external uptime check, since an outside-in check would have caught incident 4 immediately.
- `node_exporter` runs in a container without host mounts, so its root-filesystem and network panels describe the container, not the host. CPU, memory and vmstat panels are host-level.
- No log or trace pipeline. Planned: Loki and OpenTelemetry.

**TLS**
- Certificates renew automatically, but no Certbot deploy hook reloads Nginx afterwards. Nginx picks up a renewed certificate on the next deploy, which reloads it. Planned: a deploy hook that runs `docker exec nginx-proxy nginx -s reload`.

## Repository notes

- Terraform state files (`*.tfstate`) and `.terraform/` are excluded through `.gitignore`.
- Pushes to `main` trigger a deployment. Documentation-only commits can include `[skip ci]` in the message to avoid redeploying.

## Contact

- Email: anuphan.natee@hotmail.com
- Phone: 096-393-5939
- Location: Rayong, Thailand
