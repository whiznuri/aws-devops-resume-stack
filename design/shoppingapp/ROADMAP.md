# Shopping App — Roadmap

Status as of **19 Nov 2026**. Work is planned as ordered phases rather than calendar dates; each item is one vertical slice (design diagram → code → automated tests → loop test on the running app) and only counts as done when all four are complete.

| Phase | Goal | Progress |
|---|---|---|
| 1. Core shopping flow | A customer can find a product, pay, and stock stays correct | **100%** |
| 2. Go-live | Running on `shopping.diffan.dev` through the CI/CD pipeline | **~60%** |
| 3. Store operations | Staff can run the shop day to day | **25%** |
| 4. Hardening | Abuse resistance, traceability, payment edge cases | **0%** |
| 5. Extensions | Nice-to-have features | **0%** |

**Overall (phases 1–4): ~55%**

---

## Phase 1 — Core shopping flow ✅ 100%

| ID | Slice | Status |
|---|---|---|
| L-00 | Health check / project skeleton (Docker, PostgreSQL, FastAPI, Next.js) | ✅ |
| L-08 | Sign-up, login, JWT + refresh rotation, RBAC, session policies | ✅ |
| L-09 | Back office: products, variants (size × colour), images by colour | ✅ |
| L-01 | Storefront: browse, search, sort, colour/size picker | ✅ |
| L-02 | Cart (no stock reservation, login required) | ✅ |
| L-03 | Checkout: row locking, all-or-nothing, price snapshot, 1-hour reservation | ✅ |
| L-04 | Payment: Omise PromptPay (test mode), server-side verification | ✅ |
| L-07 | Auto-expire unpaid orders, release stock (checks Omise first) | ✅ |

## Phase 2 — Go-live ⏳ ~60%

| Step | Status |
|---|---|
| Production images (Next.js standalone, FastAPI with migrations on start) + local smoke test | ✅ |
| Root `docker-compose.yml`: shopping-db / api / web replace LovelyPet, Mini Shopping and MySQL | ✅ |
| Nginx: `shopping.diffan.dev`, `/shop` redirect, runtime DNS resolve | ✅ |
| CI: test gate (pytest + PostgreSQL, type check, lint) → build → SBOM → deploy | ✅ written · ⬜ first green run |
| DNS `shopping.diffan.dev` → Elastic IP | ✅ |
| `.env` on EC2 (`SHOPPING_DB_PASSWORD`, `SHOPPING_JWT_SECRET`, `OMISE_SECRET_KEY`) | ⬜ |
| Re-issue TLS certificate (drop `lovelypet`, add `shopping`) + `certbot renew --dry-run` | ⬜ |
| First admin account + seed catalogue on the server | ⬜ |
| Verify live: full purchase in test mode, re-measure instance RAM | ⬜ |

## Phase 3 — Store operations ⏳ 25%

| ID | Slice | Why |
|---|---|---|
| L-09 | Products / variants / images | ✅ done (phase 1) |
| — | Back-office order list + status (shipping data masked for non-owners) | staff must see paid orders to ship them |
| L-10 | Restock with a stock-movement ledger (who, how many, when) | stock is editable only on creation today |
| L-11 | Categories | browsing by category |
| L-12 | Admin dashboard (sales, low stock) | admin role scope |

## Phase 4 — Hardening ⬜ 0%

| ID | Slice | Why |
|---|---|---|
| L-16 | Rate limiting (Nginx + login attempts) | brute-force protection — highest priority after go-live |
| L-15 | Audit log | who did what, when; prerequisite for L-17 |
| — | Omise webhooks (push confirmation) | replaces pull-based confirmation once a public URL exists |
| — | Late payment handling (paid after expiry → refund flow) | known gap in L-07 |
| L-17 | Admin show/hide for personal data (default hidden, every reveal audited) | privacy by default; needs L-15 |

## Phase 5 — Extensions ⬜ 0%

| ID | Slice |
|---|---|
| L-06 | Card payments (Omise.js + 3-D Secure) |
| L-14 | MFA for staff/admin (TOTP) |
| — | Guest cart merged on login |
| — | Customer notifications (e-mail / LINE) for paid / expired orders |

---

## Next up

1. Finish phase 2 (server `.env`, first green pipeline, certificate, seed data, live check).
2. L-16 rate limiting — before sharing the link widely.
3. Back-office order list — so a paid order can actually be fulfilled.

## Decisions log (business rules agreed so far)

Stock is deducted at checkout, not in the cart · cart requires login · 1-hour payment window · products are suspended, never deleted · stock tracked per variant, images per colour · storefront never shows exact stock · admin is a superuser · staff/admin sessions: 30-min idle, 8-hour max; customers: 7 days · payments via Omise instead of slip upload · personal data masked for non-owners (team standard).
