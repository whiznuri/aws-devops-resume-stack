# Web resume overhaul: PHP → Next.js

**Date:** 17 Nov 2026 · **App:** `apps/web-resume` (diffan.dev) · **Old version:** `archive/web-resume-php/`

## Why

- I started a developer role where the stack is Next.js + FastAPI, and my tech lead suggested building from my own existing work.
- The old resume was plain PHP includes with data in PHP arrays. It worked, but every interaction (the accordion, smooth scroll) was hand-written JavaScript in the footer.
- Goal: same content and look first (lift-and-shift), then improve the UI, then deploy it through the existing pipeline without touching DNS or the host.

## Before → after

| | PHP (before) | Next.js (after) |
|---|---|---|
| Page shell | `index.php` includes header / navbar / footer | `app/layout.js` wraps every page (`{children}`) |
| Home page | `pages/home.php` | `app/page.js` |
| Data | PHP arrays (`$journey`, `$skills_4d`, …) | JS arrays of objects in `page.js` |
| Loops | `foreach` | `.map()` with a `key` |
| Title / meta | `$page_title` in `header.php` | `export const metadata` |
| Fonts | Google Fonts `<link>` | `next/font/google` (self-hosted at build) |
| Images | `assets/Profile.jpg` | `public/Profile.jpg` + `<Image>` |
| Interactivity | inline JS in `footer.php` | client components with `useState` |
| Runtime | `php:8.2-apache`, port 80 | Node 24 Alpine, standalone server, port 3000, non-root |

## What changed

1. **Project:** `create-next-app` 16.4.6 — JavaScript, App Router, Tailwind v4, ESLint, Turbopack.
2. **CSS:** the old `style.css` was imported as-is in `layout.js` (lift-and-shift). Tailwind was added on top for new UI pieces.
3. **Fonts:** Montserrat (headings), Poppins (Latin body) and Noto Sans Thai (Thai), wired through the existing `--font-display` / `--font-body` CSS variables.
4. **Sections moved:** hero, journey, skills, education, showcase, contact; navbar and footer moved into `layout.js`.
5. **New UI:**
   - Skills and projects are tabs instead of long lists (`SkillTabs`, `ProjectTabs`).
   - The career timeline is an accordion (`Journey`).
   - A theme button cycles three themes — navy, espresso and cream — by switching a `data-theme` attribute; every colour comes from CSS variables in `theme.css`. The choice is kept in `localStorage`.
6. **Content:** added the current developer role; split "Software & Mobile" into *Mobile Development (Android)* and *Web Development*.

## Deployment changes

| File | Change | Why |
|---|---|---|
| `next.config.mjs` | `output: "standalone"` | Ships only the files the server needs; smaller image, less RAM |
| `apps/web-resume/Dockerfile` | new multi-stage build (deps → builder → runner) | Build tools stay out of the final image; runs as user `nextjs` |
| `docker-compose.yml` | `8080:80` → `8080:3000`, `mem_limit: 192m`, image tag via `WEB_RESUME_TAG` | Non-root Node can't bind port 80; cap memory on a t3.micro; allow rollback |
| `nginx/conf.d/default.conf` | `proxy_pass http://web-resume:3000` | Follow the new container port |
| `.github/workflows/deploy.yml` | extra image tag `${{ github.sha }}` for all apps | Every deploy keeps a versioned image to roll back to |

DNS, the Elastic IP and the Nginx server blocks are unchanged: Nginx still forwards to the service name `web-resume`, and Docker's internal DNS resolves it.

**Local test:** `docker build` then `docker run -p 8080:3000` — page served correctly, container used about **48 MB** RAM.

## Rollback

- **Whole migration:** `git revert` the migration commit and push. CI rebuilds the PHP image and restores the old Nginx/Compose settings.
- **A later Next.js release:** on the host, `WEB_RESUME_TAG=<commit sha> docker compose up -d web-resume`.

## Problems hit along the way

| Symptom | Cause | Fix |
|---|---|---|
| `Font loaders must be called and assigned to a const` | Variable named the same as the font function | Lower-case variable names |
| `Missing weight for Poppins` | Poppins is a static font | `weight: ["400","500","600"]` |
| `Invalid DOM property class` | HTML copied as-is | `className` |
| `Each child in a list should have a unique "key"` | Missing `key` in `.map()` | Unique key per item |
| Link went to `/%7Blink.url%7D` (404) | `href="{link.url}"` — quotes made it literal text | `href={link.url}` |
| Tailwind classes did nothing | `globals.css` (Tailwind import) was not imported | Import it before `style.css` |
| Footer never centred (also on the old site) | HTML used `footer-container`, CSS styled `site-footer__inner` | Styled both in `theme.css` |
| `docker build`: Dockerfile cannot be empty | File created but content not saved | Save the file |

## How it was built

I used an AI assistant (Claude) as a syntax guide and reviewer. The section-by-section PHP → JSX conversion was typed by hand; the tab, accordion and theme components, the theme colours and the Dockerfile were AI-assisted and reviewed by me.

## Next

- Move the data out of `page.js` into its own file.
- Refactor the old `style.css` into Tailwind step by step.
- Avoid the brief theme flash on first load (set the theme before React hydrates).
- Watch RAM in Grafana after deploy; close the redundant public ports (3000, 8080, 9091).
