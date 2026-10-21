-- Smart Links: route one link to several WhatsApp numbers (hours / country / source / weighted).
-- Run once on existing installs:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_003_smart.sql
ALTER TABLE links ADD COLUMN type VARCHAR(10) NOT NULL DEFAULT 'chat' AFTER domain;   -- chat | smart
ALTER TABLE clicks ADD COLUMN destination_id INT UNSIGNED NULL AFTER link_id;

CREATE TABLE IF NOT EXISTS destinations (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  link_id    INT UNSIGNED NOT NULL,
  name       VARCHAR(60)  NOT NULL DEFAULT '',
  phone      VARCHAR(16)  NOT NULL,
  message    TEXT         NOT NULL,                 -- empty = use the link's message
  rule_type  VARCHAR(10)  NOT NULL DEFAULT 'always', -- always | hours | country | source
  rule       TEXT         NOT NULL,                 -- JSON: {days:[1..7], from:"09:00", to:"18:00", tz:"Asia/Kuala_Lumpur"} | {countries:["MY","SG"]} | {sources:["instagram","tiktok"]}
  weight     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  sort       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  active     TINYINT(1)   NOT NULL DEFAULT 1,
  clicks     INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME     NOT NULL,
  INDEX idx_dest_link (link_id),
  CONSTRAINT fk_dest_link FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
