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
dashboard.php link.php analytics.php qr.php pricing.php terms.php privacy.php
admin/index.php            users (plan/role), links, settings — first registered user is admin
dev/                       local server: php.ini, router.php, run.sh
```

## URL rules (.htaccess)

- `/login` → `login.php` (no `.php` in URLs), `/admin` → `admin/index.php`
- anything else `[A-Za-z0-9-]{3,30}` → `go.php?c=…`  (short links live at the root, no `/go/`)
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
cd /home/kuantan1 && git clone https://github.com/kentkoh/wsap.git wsap.me
cd /home/kuantan1/wsap.me
mysql -u kuantan1_wsap -p kuantan1_wsap < db/schema.sql
cp config.local.example.php config.local.php && nano config.local.php   # DB pass, ip_salt, support_whatsapp, brevo, google
```

4. Register the first account on the site → it becomes **admin** (`/admin`).
5. Google sign-in: Google Cloud Console → OAuth client (Web) → redirect URIs
   `https://wsap.me/auth/google.php` and `https://wsap.my/auth/google.php`.
6. Brevo: verify the sender `hello@wsap.me` (or change `brevo.from_email`).
7. Turn OFF the hosting "One moment, please" bot-check for this domain (it blocks WhatsApp/FB previews and Google).

## Deploy (every update)

```bash
cd /home/kuantan1/wsap.me && git pull
```
`git pull` never runs SQL — apply new `db/upgrade_*.sql` manually when the commit message says so.

## Roadmap

- Phase 2: Billplz yearly checkout per link, Premium auto-expiry emails, styled QR server-side PNG.
- Phase 3 (Business): landing page before chat, multi-agent routing (hours / rotation / weighted),
  **Forms** (walink-style questionnaire that pre-fills the chat), Pixel/GA events, bio page.
