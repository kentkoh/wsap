-- WSAP — SQLite schema for LOCAL DEVELOPMENT only (dev/run.sh applies it). Production uses db/schema.sql.
CREATE TABLE IF NOT EXISTS users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  email         TEXT NOT NULL UNIQUE,
  name          TEXT NOT NULL DEFAULT '',
  password_hash TEXT NULL,
  google_sub    TEXT NULL UNIQUE,
  role          TEXT NOT NULL DEFAULT 'user',
  plan          TEXT NOT NULL DEFAULT 'free',
  plan_until    TEXT NULL,
  verified_at   TEXT NULL,
  notify_report INTEGER NOT NULL DEFAULT 1,
  created_at    TEXT NOT NULL,
  last_login_at TEXT NULL
);
CREATE TABLE IF NOT EXISTS links (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  code        TEXT NOT NULL UNIQUE,
  domain      TEXT NOT NULL DEFAULT 'wsap.me',
  type        TEXT NOT NULL DEFAULT 'chat',
  user_id     INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
  phone       TEXT NOT NULL,
  message     TEXT NOT NULL,
  title       TEXT NOT NULL DEFAULT '',
  is_custom   INTEGER NOT NULL DEFAULT 0,
  active      INTEGER NOT NULL DEFAULT 1,
  premium_until TEXT NULL,
  smart_until TEXT NULL,
  clicks      INTEGER NOT NULL DEFAULT 0,
  session_key TEXT NULL,
  created_at  TEXT NOT NULL,
  updated_at  TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_links_user ON links(user_id);
CREATE INDEX IF NOT EXISTS idx_links_session ON links(session_key);
CREATE TABLE IF NOT EXISTS clicks (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  link_id    INTEGER NOT NULL REFERENCES links(id) ON DELETE CASCADE,
  destination_id INTEGER NULL,
  created_at TEXT NOT NULL,
  ip_hash    TEXT NOT NULL DEFAULT '',
  country    TEXT NOT NULL DEFAULT '',
  referrer   TEXT NOT NULL DEFAULT 'direct',
  device     TEXT NOT NULL DEFAULT '',
  os         TEXT NOT NULL DEFAULT '',
  browser    TEXT NOT NULL DEFAULT '',
  is_bot     INTEGER NOT NULL DEFAULT 0,
  is_qr      INTEGER NOT NULL DEFAULT 0,
  utm_source TEXT NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS idx_clicks_link_time ON clicks(link_id, created_at);
CREATE TABLE IF NOT EXISTS tokens (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  purpose    TEXT NOT NULL,
  token_hash TEXT NOT NULL UNIQUE,
  expires_at TEXT NOT NULL,
  created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS rate_limits (
  `key`        TEXT PRIMARY KEY,
  hits         INTEGER NOT NULL DEFAULT 1,
  window_start TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS settings (
  `key`   TEXT PRIMARY KEY,
  `value` TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS payments (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  link_id    INTEGER NULL REFERENCES links(id) ON DELETE SET NULL,
  item_type  TEXT NOT NULL DEFAULT 'link',
  item_id    INTEGER NULL,
  bill_id    TEXT NOT NULL UNIQUE,
  provider   TEXT NOT NULL DEFAULT 'billplz',
  amount     INTEGER NOT NULL,
  currency   TEXT NOT NULL DEFAULT 'MYR',
  status     TEXT NOT NULL DEFAULT 'pending',
  handle     TEXT NOT NULL DEFAULT '',
  addons     TEXT NOT NULL DEFAULT '',
  years      INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL,
  paid_at    TEXT NULL
);
CREATE TABLE IF NOT EXISTS destinations (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  link_id    INTEGER NOT NULL REFERENCES links(id) ON DELETE CASCADE,
  name       TEXT NOT NULL DEFAULT '',
  phone      TEXT NOT NULL,
  message    TEXT NOT NULL,
  rule_type  TEXT NOT NULL DEFAULT 'always',
  rule       TEXT NOT NULL,
  weight     INTEGER NOT NULL DEFAULT 1,
  sort       INTEGER NOT NULL DEFAULT 0,
  active     INTEGER NOT NULL DEFAULT 1,
  clicks     INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_dest_link ON destinations(link_id);
CREATE TABLE IF NOT EXISTS forms (
  id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, slug TEXT NOT NULL UNIQUE, domain TEXT NOT NULL DEFAULT 'wsap.me',
  title TEXT NOT NULL, intro TEXT NOT NULL, phone TEXT NOT NULL, opening_line TEXT NOT NULL DEFAULT '', mode TEXT NOT NULL DEFAULT 'steps', theme TEXT NOT NULL, logo TEXT NOT NULL DEFAULT '',
  questions TEXT NOT NULL, save_responses INTEGER NOT NULL DEFAULT 1, privacy_url TEXT NOT NULL DEFAULT '', active INTEGER NOT NULL DEFAULT 1, demo INTEGER NOT NULL DEFAULT 0, premium_until TEXT NULL,
  views INTEGER NOT NULL DEFAULT 0, starts INTEGER NOT NULL DEFAULT 0, submits INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS form_responses (
  id INTEGER PRIMARY KEY AUTOINCREMENT, form_id INTEGER NOT NULL REFERENCES forms(id) ON DELETE CASCADE, created_at TEXT NOT NULL, answers TEXT NOT NULL, country TEXT NOT NULL DEFAULT '', device TEXT NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS idx_resp_form ON form_responses(form_id, created_at);
CREATE TABLE IF NOT EXISTS shops (
  id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, slug TEXT NOT NULL UNIQUE, domain TEXT NOT NULL DEFAULT 'wsap.me',
  name TEXT NOT NULL, tagline TEXT NOT NULL DEFAULT '', about TEXT NOT NULL, logo TEXT NOT NULL DEFAULT '', cover TEXT NOT NULL DEFAULT '', accent TEXT NOT NULL DEFAULT '#0b3d2e', instagram TEXT NOT NULL DEFAULT '', facebook TEXT NOT NULL DEFAULT '', tiktok TEXT NOT NULL DEFAULT '', hours_text TEXT NOT NULL DEFAULT '', phone TEXT NOT NULL, currency TEXT NOT NULL DEFAULT 'MYR',
  fields TEXT NOT NULL, shipping TEXT NOT NULL, coupons TEXT NOT NULL, payment_note TEXT NOT NULL DEFAULT '', is_open INTEGER NOT NULL DEFAULT 1, active INTEGER NOT NULL DEFAULT 1, demo INTEGER NOT NULL DEFAULT 0, premium_until TEXT NULL, views INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS products (
  id INTEGER PRIMARY KEY AUTOINCREMENT, shop_id INTEGER NOT NULL REFERENCES shops(id) ON DELETE CASCADE, name TEXT NOT NULL, description TEXT NOT NULL, price INTEGER NOT NULL DEFAULT 0, image TEXT NOT NULL DEFAULT '', category TEXT NOT NULL DEFAULT '', category_id INTEGER NULL, variants TEXT NOT NULL, active INTEGER NOT NULL DEFAULT 1, sort INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS orders (
  id INTEGER PRIMARY KEY AUTOINCREMENT, shop_id INTEGER NOT NULL REFERENCES shops(id) ON DELETE CASCADE, number TEXT NOT NULL, customer TEXT NOT NULL, items TEXT NOT NULL, subtotal INTEGER NOT NULL, shipping_name TEXT NOT NULL DEFAULT '', shipping INTEGER NOT NULL DEFAULT 0, coupon TEXT NOT NULL DEFAULT '', discount INTEGER NOT NULL DEFAULT 0, total INTEGER NOT NULL, status TEXT NOT NULL DEFAULT 'pending', created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_orders_shop ON orders(shop_id, created_at);
CREATE TABLE IF NOT EXISTS categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT, shop_id INTEGER NOT NULL REFERENCES shops(id) ON DELETE CASCADE, name TEXT NOT NULL, description TEXT NOT NULL DEFAULT '', image TEXT NOT NULL DEFAULT '', sort INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS premium_slugs (
  slug TEXT PRIMARY KEY, price_myr INTEGER NOT NULL DEFAULT 299, assigned_user_id INTEGER NULL, note TEXT NOT NULL DEFAULT '', created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS geo_cache (
  ip_hash TEXT PRIMARY KEY, country TEXT NOT NULL DEFAULT '', created_at TEXT NOT NULL
);
