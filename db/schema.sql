-- WSAP — MySQL schema. Apply once:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/schema.sql
-- All timestamps are UTC "Y-m-d H:i:s" strings written by PHP (now()).
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(190) NOT NULL UNIQUE,
  name          VARCHAR(80)  NOT NULL DEFAULT '',
  password_hash VARCHAR(255) NULL,
  google_sub    VARCHAR(64)  NULL UNIQUE,
  role          VARCHAR(10)  NOT NULL DEFAULT 'user',      -- user | admin
  plan          VARCHAR(12)  NOT NULL DEFAULT 'free',      -- free | premium | business
  plan_until    DATETIME     NULL,
  verified_at   DATETIME     NULL,
  created_at    DATETIME     NOT NULL,
  last_login_at DATETIME     NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS links (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(30)  NOT NULL UNIQUE,
  domain      VARCHAR(40)  NOT NULL DEFAULT 'wsap.me',    -- display domain; every domain resolves every code
  type        VARCHAR(10)  NOT NULL DEFAULT 'chat',       -- chat | smart
  user_id     INT UNSIGNED NULL,
  phone       VARCHAR(16)  NOT NULL,                      -- digits only, E.164 without +
  message     TEXT         NOT NULL,
  title       VARCHAR(80)  NOT NULL DEFAULT '',
  is_custom   TINYINT(1)   NOT NULL DEFAULT 0,
  active      TINYINT(1)   NOT NULL DEFAULT 1,
  premium_until DATETIME   NULL,                         -- link-level Premium (per link, per year)
  clicks      INT UNSIGNED NOT NULL DEFAULT 0,            -- human clicks (bots excluded)
  session_key VARCHAR(32)  NULL,                          -- anonymous creator; cleared on claim
  created_at  DATETIME     NOT NULL,
  updated_at  DATETIME     NOT NULL,
  INDEX idx_links_user (user_id),
  INDEX idx_links_session (session_key),
  CONSTRAINT fk_links_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clicks (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  link_id    INT UNSIGNED NOT NULL,
  destination_id INT UNSIGNED NULL,
  created_at DATETIME    NOT NULL,
  ip_hash    CHAR(32)    NOT NULL DEFAULT '',
  country    CHAR(2)     NOT NULL DEFAULT '',
  referrer   VARCHAR(60) NOT NULL DEFAULT 'direct',
  device     VARCHAR(10) NOT NULL DEFAULT '',
  os         VARCHAR(12) NOT NULL DEFAULT '',
  browser    VARCHAR(20) NOT NULL DEFAULT '',
  is_bot     TINYINT(1)  NOT NULL DEFAULT 0,
  is_qr      TINYINT(1)  NOT NULL DEFAULT 0,
  utm_source VARCHAR(40) NOT NULL DEFAULT '',
  INDEX idx_clicks_link_time (link_id, created_at),
  CONSTRAINT fk_clicks_link FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tokens (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  purpose    VARCHAR(10)  NOT NULL,                       -- verify | reset
  token_hash CHAR(64)     NOT NULL UNIQUE,
  expires_at DATETIME     NOT NULL,
  created_at DATETIME     NOT NULL,
  CONSTRAINT fk_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rate_limits (
  `key`        VARCHAR(80) NOT NULL PRIMARY KEY,
  hits         INT UNSIGNED NOT NULL DEFAULT 1,
  window_start DATETIME    NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  `key`   VARCHAR(60) NOT NULL PRIMARY KEY,
  `value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS payments (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  link_id       INT UNSIGNED NULL,
  item_type     VARCHAR(10)  NOT NULL DEFAULT 'link',       -- link | form | shop
  item_id       INT UNSIGNED NULL,
  bill_id       VARCHAR(40)  NOT NULL UNIQUE,
  provider      VARCHAR(12)  NOT NULL DEFAULT 'billplz',
  amount        INT UNSIGNED NOT NULL COMMENT 'sen',
  currency      CHAR(3)      NOT NULL DEFAULT 'MYR',
  status        VARCHAR(10)  NOT NULL DEFAULT 'pending',  -- pending | paid | failed
  handle        VARCHAR(30)  NOT NULL DEFAULT '',          -- custom handle requested at checkout
  years         TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at    DATETIME     NOT NULL,
  paid_at       DATETIME     NULL,
  INDEX idx_pay_user (user_id),
  CONSTRAINT fk_pay_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_pay_link FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS destinations (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  link_id    INT UNSIGNED NOT NULL,
  name       VARCHAR(60)  NOT NULL DEFAULT '',
  phone      VARCHAR(16)  NOT NULL,
  message    TEXT         NOT NULL,
  rule_type  VARCHAR(10)  NOT NULL DEFAULT 'always',
  rule       TEXT         NOT NULL,
  weight     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  sort       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  active     TINYINT(1)   NOT NULL DEFAULT 1,
  clicks     INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME     NOT NULL,
  INDEX idx_dest_link (link_id),
  CONSTRAINT fk_dest_link FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS forms (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NOT NULL,
  slug           VARCHAR(30)  NOT NULL UNIQUE,
  domain         VARCHAR(40)  NOT NULL DEFAULT 'wsap.me',
  title          VARCHAR(80)  NOT NULL,
  intro          TEXT         NOT NULL,
  phone          VARCHAR(16)  NOT NULL,
  opening_line   VARCHAR(200) NOT NULL DEFAULT '',
  mode           VARCHAR(8)   NOT NULL DEFAULT 'steps',   -- steps | single
  theme          TEXT         NOT NULL,                   -- JSON {bg,text,title,bar,button}
  logo           VARCHAR(255) NOT NULL DEFAULT '',        -- https URL
  questions      TEXT         NOT NULL,                   -- JSON array
  save_responses TINYINT(1)   NOT NULL DEFAULT 1,
  privacy_url    VARCHAR(255) NOT NULL DEFAULT '',
  active         TINYINT(1)   NOT NULL DEFAULT 1,
  premium_until  DATETIME     NULL,                       -- published while in the future
  views          INT UNSIGNED NOT NULL DEFAULT 0,
  starts         INT UNSIGNED NOT NULL DEFAULT 0,
  submits        INT UNSIGNED NOT NULL DEFAULT 0,
  created_at     DATETIME     NOT NULL,
  updated_at     DATETIME     NOT NULL,
  INDEX idx_forms_user (user_id),
  CONSTRAINT fk_forms_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS form_responses (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  form_id    INT UNSIGNED NOT NULL,
  created_at DATETIME     NOT NULL,
  answers    TEXT         NOT NULL,                       -- JSON [{q, a}]
  country    CHAR(2)      NOT NULL DEFAULT '',
  device     VARCHAR(10)  NOT NULL DEFAULT '',
  INDEX idx_resp_form (form_id, created_at),
  CONSTRAINT fk_resp_form FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE payments ADD COLUMN item_type VARCHAR(10) NOT NULL DEFAULT 'link' AFTER link_id;
ALTER TABLE payments ADD COLUMN item_id INT UNSIGNED NULL AFTER item_type;
UPDATE payments SET item_id = link_id WHERE item_id IS NULL;
