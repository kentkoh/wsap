-- Shop categories as real records (image + description), social links on shops.
-- Run once on existing installs:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_006_shop_categories.sql
CREATE TABLE IF NOT EXISTS categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  shop_id     INT UNSIGNED NOT NULL,
  name        VARCHAR(60)  NOT NULL,
  description VARCHAR(160) NOT NULL DEFAULT '',
  image       VARCHAR(255) NOT NULL DEFAULT '',
  sort        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at  DATETIME     NOT NULL,
  INDEX idx_cat_shop (shop_id),
  CONSTRAINT fk_cat_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE products ADD COLUMN category_id INT UNSIGNED NULL AFTER category;
ALTER TABLE shops ADD COLUMN instagram VARCHAR(255) NOT NULL DEFAULT '' AFTER accent;
ALTER TABLE shops ADD COLUMN facebook  VARCHAR(255) NOT NULL DEFAULT '' AFTER instagram;
ALTER TABLE shops ADD COLUMN tiktok    VARCHAR(255) NOT NULL DEFAULT '' AFTER facebook;
ALTER TABLE shops ADD COLUMN hours_text VARCHAR(120) NOT NULL DEFAULT '' AFTER tiktok;

-- migrate free-text categories into rows
INSERT INTO categories (shop_id, name, sort, created_at)
  SELECT DISTINCT shop_id, category, 0, NOW() FROM products WHERE category <> '';
UPDATE products p JOIN categories c ON c.shop_id = p.shop_id AND c.name = p.category SET p.category_id = c.id;
