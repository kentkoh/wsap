-- Forms: a branded questionnaire whose answers arrive pre-filled in WhatsApp.
-- Run once on existing installs:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_004_forms.sql
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

-- payments can now belong to a link OR a form (item_type/item_id); link_id kept for old rows
ALTER TABLE payments ADD COLUMN item_type VARCHAR(10) NOT NULL DEFAULT 'link' AFTER link_id;
ALTER TABLE payments ADD COLUMN item_id INT UNSIGNED NULL AFTER item_type;
UPDATE payments SET item_id = link_id WHERE item_id IS NULL;
