-- Run once on installs created before Phase 2:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_002_premium.sql
ALTER TABLE links ADD COLUMN premium_until DATETIME NULL AFTER active;

CREATE TABLE IF NOT EXISTS payments (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  link_id       INT UNSIGNED NULL,
  bill_id       VARCHAR(40)  NOT NULL UNIQUE,
  provider      VARCHAR(12)  NOT NULL DEFAULT 'billplz',
  amount        INT UNSIGNED NOT NULL COMMENT 'sen',
  currency      CHAR(3)      NOT NULL DEFAULT 'MYR',
  status        VARCHAR(10)  NOT NULL DEFAULT 'pending',
  handle        VARCHAR(30)  NOT NULL DEFAULT '',
  years         TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at    DATETIME     NOT NULL,
  paid_at       DATETIME     NULL,
  INDEX idx_pay_user (user_id),
  CONSTRAINT fk_pay_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_pay_link FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
