# WSAP — WhatsApp link generator (wsap.me · wsap.my)

Walink-style SaaS: turn a WhatsApp number into a short `wsap.me/abc123` link + QR with a
pre-filled message. Free links are anonymous, never expire and cannot be edited. Premium
links (per link, per year) get a branded handle, editing, analytics, styled QR.

## Stack & hosting

- Plain PHP 8.1 + MySQL (PDO). No framework, no Composer. cPanel host (kuantan1).
- One install serves **both domains**: `wsap.me` (EN, USD) and `wsap.my` (BM, RM).
  Every code resolves on every domain; the link stores which domain it displays as.
- Auth: email + password, Google sign-in (OAuth code flow, no SDK), email verification
  and password reset via **Brevo** transactional API.
- Front-end: vanilla JS, Google Fonts (Instrument Serif + Plus Jakarta Sans + JetBrains Mono),
  QR rendered client-side with `qr-code-styling` from jsDelivr.

## Layout

```
config.php                 defaults (safe to commit)
config.local.php           SERVER-ONLY secrets (gitignored) — see config.local.example.php
lib/bootstrap.php          cfg(), db(), sessions, csrf, i18n t(), domain helpers   [web-blocked]
lib/auth.php               users, login, Google, tokens, plans, rate limits
lib/links.php              codes, validation, create/find, click logging, stats
lib/ua.php                 bot / in-app browser / device detection
lib/mail.php               Brevo transactional mail (+ templates)
lib/layout.php             page_start()/page_end() chrome
lang/en.php  lang/ms.php   UI strings
db/schema.sql              MySQL schema (production)        [web-blocked]
db/schema.sqlite.sql       SQLite schema (local dev only)
index.php                  homepage + free generator (POSTs to api/create.php)
go.php                     /{code} redirect: logs click, OG tags for bots, in-app opener
signup/login/logout/forgot/reset/verify.php, auth/google.php
dashboard.php link.php analytics.php qr.php upgrade.php pricing.php terms.php privacy.php
lib/premium.php lib/billplz.php  per-link Premium, Billplz API; payments/return.php + callback.php; cron.php
admin/index.php            users (plan/role), links, settings — first registered user is admin
dev/                       local server: php.ini, router.php, run.sh
```

## URL rules (.htaccess)

- `/login` → `login.php` (no `.php` in URLs), `/admin` → `admin/index.php`
- anything else `[A-Za-z0-9-]{3,30}` → `go.php?c=…` which resolves a link, then a form, then a shop (one shared namespace; `slug_taken()` enforces uniqueness). Old `/f/slug` and `/s/slug` redirect 301.
- Free codes: 6 chars from `abcdefghjkmnpqrstuvwxyz23456789` (no look-alikes) ≈ 887M combos.
- Custom handles (Premium): 3–30 chars `a-z 0-9 -`, reserved words in `lib/links.php`.

## Local development (SQLite, no MySQL needed)

```bash
bash dev/run.sh        # http://localhost:8080  (copies dev/config.local.dev.php → config.local.php)
```
Emails are written to `dev/mail.log` when no Brevo key is set.

## Production setup (once)

1. cPanel → MySQL: DB `kuantan1_wsap`, user `kuantan1_wsap`, all privileges.
2. cPanel → Domains: add **wsap.my as an alias (parked) of wsap.me** so both share the docroot. AutoSSL both.
3. Clone and configure:

```bash
cd /home/kuantan1/public_html && mv wsap.me wsap.me_old && git clone https://github.com/kentkoh/wsap.git wsap.me
cd /home/kuantan1/public_html/wsap.me
mysql -u kuantan1_wsap -p kuantan1_wsap < db/schema.sql
cp config.local.example.php config.local.php && nano config.local.php   # DB pass, ip_salt, support_whatsapp, brevo, google
```

4. Register the first account on the site → it becomes **admin** (`/admin`).
5. Google sign-in: Google Cloud Console → OAuth client (Web) → redirect URIs
   `https://wsap.me/auth/google.php` and `https://wsap.my/auth/google.php`.
6. Brevo: verify the sender `hello@wsap.me` (or change `brevo.from_email`).
7. Turn OFF the hosting "One moment, please" bot-check for this domain (it blocks WhatsApp/FB previews and Google).

## Billplz (Phase 2, per-link yearly Premium)

1. Billplz → Settings: copy **API Secret Key** and **X Signature Key**; Billing → Collections: create "WSAP Premium" and copy its **Collection ID**.
2. Put them in `config.local.php` under `billplz` (set `sandbox => true` while testing with billplz-sandbox.com keys).
3. Checkout flow: `/upgrade?id=LINK` → Billplz bill → `payments/return.php` (customer) + `payments/callback.php` (server, X-Signature verified, bill re-fetched via API). Paid bills extend `links.premium_until` by 1–3 years and apply the requested custom handle.
4. Daily cron (cPanel → Cron Jobs): `php /home/kuantan1/public_html/wsap.me/cron.php CRON_KEY` — expiry reminders (7 days before + on the day), re-sync pending bills, purge clicks older than 13 months. `cron_key` lives in config.local.php.
5. Until Billplz keys are set, the upgrade page shows a WhatsApp button and admin can grant plans manually.

## Products (walink parity)

| Product | URL | Price (default, admin-editable) |
|---|---|---|
| Free link | `wsap.me/abc123` | free, never expires, not editable |
| Premium link | `wsap.me/yourbrand` | per link / year — edit, analytics, styled QR, **Smart Link** routing (`/smart?id=`) |
| Form | `wsap.me/slug` | per form / year — builder `/form`, inbox `/responses`, public page `form_view.php` |
| Shop | `wsap.me/slug` | per shop / year — workspace `/store` (dashboard), `/products`, `/categories`, `/coupons`, `/customers`, `/orders`, `/shop` (settings); public page `shop_view.php`, cart → `api/shop_order.php` |

Smart Links: destinations with rules (always/weighted, business hours + timezone, country, traffic source); first matching rule wins, catch-all split by weight; routing only while the link is Premium. Forms: up to 20 questions of 11 types, step or single page, answers composed into the first WhatsApp message, optional saved responses + CSV, funnel counters.

Shops: categories, photos (uploaded to `assets/uploads`, re-encoded to WebP, PHP execution blocked), option groups with price extras (`Size: S, M, L (+2)`; `*` = multi-select), delivery methods, coupons, configurable checkout fields, open/closed switch, orders with statuses + 30-day metrics + CSV. Payment is arranged in WhatsApp (same as walink).

Upgrades to run once per existing install, in order: `db/upgrade_002_premium.sql`, `db/upgrade_003_smart.sql`, `db/upgrade_004_forms.sql`, `db/upgrade_005_shops.sql`, `db/upgrade_006_shop_categories.sql`, `db/upgrade_007_premium_slugs.sql` (seeds ~200 generic words as premium handles), `db/upgrade_008_demo.sql`.

Handle policy (`lib/blocklist.php`): min length (admin), reserved words, trademarks, EN/BM profanity, admin extra words, and **premium handles** (`premium_slugs` table: generic words sold for a one-time fee; refused unless assigned to the account). Buying is automatic: the refusal shows a **Buy this handle** button → `/upgrade?handle=word` → Billplz one-time fee → on payment the word is assigned to the buyer. On the link upgrade page a premium handle simply adds its fee to the yearly bill. Admin → Premium handles can also assign/release manually.

## Homepage

Sections (index.php, graphics in `lib/home_graphics.php`): hero + free generator → **How a WhatsApp link works** (tabs Links / Smart / Forms / Shops with numbered steps and an animated phone that taps the link and opens the chat, scenes cycle with the steps) → **Where to use** (9 illustrated tiles) → **Products** (4 animated flow diagrams: Premium Links, Smart Links, Forms, Shops) → Free vs Premium table → FAQ → CTA. Animations are CSS keyframes + a small scene loop; respects prefers-reduced-motion.

## Product pages & demos

- `/premium-links`, `/whatsapp-forms`, `/whatsapp-shop` — marketing pages. The shop page has six live feature mocks with Read-more modals, a tabbed dashboard tour using real screenshots in `assets/img/shop/*.webp` (regenerate: seed `dev/seed_dev.php`, screenshot with Playwright at 1280×860, convert with sharp), a comparison table and FAQ. Prices on all pages come from `price()` so admin changes apply instantly. Linked from the **Products** dropdown in the header, the homepage product row and the footer.
- **Demo mode** (`forms.demo` / `shops.demo`, upgrade_008; toggle in Admin → Forms/Shops): the last step shows a WhatsApp-styled preview popup of the message instead of opening WhatsApp. Nothing is sent or saved. Seeds: `db/seed_demo_forms.sql` (homestay, clinic, pickleball, workshop, tuition, catering → wsap.me/demo-*), `db/seed_demo_shop.sql` (wsap.me/bakery).

## Deploy (every update)

```bash
cd /home/kuantan1/public_html/wsap.me && git pull
```
`git pull` never runs SQL — apply new `db/upgrade_*.sql` manually when the commit message says so.

Installs created before Phase 2 (2026-10-22) need, once:

```bash
cd /home/kuantan1/public_html/wsap.me && mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_002_premium.sql
```

## Roadmap

- Phase 2 (done): Billplz yearly checkout per link, expiry reminder emails, admin payments tab.
- Phase 3 (done): Smart Links, Forms, Shops. Next ideas: landing page before chat, Pixel/GA events, Stripe for international cards, custom domains.
